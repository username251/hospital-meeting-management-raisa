<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Specialty;
use Illuminate\Http\Request;
use App\Models\DoctorAvailability;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class AppointmentController extends Controller
{
    /**
     * Display a listing of the appointments for staff.
     */
    public function index(Request $request)
    {
        // Logika untuk memfilter janji temu (opsional, bisa ditambahkan filter tanggal, dokter, dll.)
        // Ubah orderBy 'start_time'
        $query = Appointment::with(['patient.user', 'doctor.user', 'specialty']);

        // Contoh filter berdasarkan tanggal (jika diperlukan)
        if ($request->has('date') && $request->date != '') {
            $query->whereDate('appointment_date', $request->date);
        }

        // Contoh filter berdasarkan status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderBy('appointment_date', 'desc')
                              ->orderBy('start_time', 'asc') // Perbaikan: Gunakan start_time
                              ->paginate(10);

        // Perbaikan: Pastikan view yang benar
        return view('staff.appointment_summary.index', compact('appointments')); // Menggunakan staff.appointments.index
    }

    /**
     * Show the form for creating a new appointment.
     */
    public function create()
    {
        $patients = Patient::with('user')->get();
        $doctors = Doctor::with('user', 'specialty')->get();
        $specialties = Specialty::all();
        // Perbaikan: Pastikan view yang benar
        return view('staff.appointment_summary.create', compact('patients', 'doctors', 'specialties')); // Menggunakan staff.appointments.create
    }

    /**
     * Store a newly created appointment in storage.
     */
     public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i', // Ini adalah waktu mulai yang dipilih user
            'reason' => 'nullable|string|max:1000',
            'status' => 'required|string|in:pending,confirmed,completed,cancelled,rescheduled', // Sesuaikan dengan nilai ENUM di DB Anda
        ]);

        // Waktu mulai yang diminta dari form (akan disimpan ke start_time)
        $requestedStartTimeStr = $request->appointment_time;
        $appointmentDateStr = $request->appointment_date;

        $requestedStartDateTime = Carbon::parse($appointmentDateStr . ' ' . $requestedStartTimeStr);
        $doctorId = $request->doctor_id;
        $appointmentDayOfWeek = $requestedStartDateTime->englishDayOfWeek;

        // Asumsi durasi janji temu 30 menit
        $appointmentDurationMinutes = 30;

        // Hitung waktu akhir janji temu berdasarkan waktu mulai yang diminta dan durasi
        $requestedEndTimeStr = $requestedStartDateTime->copy()->addMinutes($appointmentDurationMinutes)->format('H:i:s');


        // 1. Cek ketersediaan dokter di tabel doctor_availabilities
        $availability = DoctorAvailability::where('doctor_id', $doctorId)
            ->where('day_of_week', $appointmentDayOfWeek)
            // Memastikan waktu mulai yang diminta berada dalam rentang ketersediaan dokter
            ->where('start_time', '<=', $requestedStartTimeStr)
            // Memastikan waktu akhir yang diminta berada dalam rentang ketersediaan dokter
            ->where('end_time', '>=', $requestedEndTimeStr)
            ->first();

        if (!$availability) {
            return redirect()->back()->withInput()->withErrors(['appointment_time' => 'Dokter tidak tersedia pada waktu atau hari yang dipilih (di luar jadwal ketersediaan). Silakan pilih slot lain.']);
        }

        // 2. Cek apakah ada janji temu lain yang tumpang tindih untuk dokter yang sama
        $overlappingAppointment = Appointment::where('doctor_id', $doctorId)
        ->where('appointment_date', $appointmentDateStr)
        ->where('status', '!=', 'cancelled')
        ->whereRaw('`start_time` < ? AND `end_time` > ?', [ // Gunakan backticks untuk nama kolom
            $requestedEndTimeStr,
            $requestedStartTimeStr
        ])
        ->exists();


        if ($overlappingAppointment) {
            return redirect()->back()->withInput()->withErrors(['appointment_time' => 'Waktu janji temu yang dipilih sudah terisi untuk dokter ini (tumpang tindih dengan janji temu lain). Silakan pilih slot lain.']);
        }

        // Jika semua validasi lolos, buat janji temu
        Appointment::create([
            'patient_id' => $request->patient_id,
            'doctor_id' => $request->doctor_id,
            'appointment_date' => $request->appointment_date,
            'start_time' => $requestedStartTimeStr, // Perbaikan: Simpan ke kolom start_time
            'end_time' => $requestedEndTimeStr,     // Perbaikan: Simpan ke kolom end_time
            'reason' => $request->reason,
            'status' => $request->status, // Ini yang bermasalah
        ]);

        return redirect()->route('staff.appointments.index')->with('success', 'Janji temu berhasil dibuat.');
    }


    /**
     * Mengambil slot waktu yang tersedia untuk dokter dan tanggal tertentu.
     * Dipanggil melalui AJAX.
     */
     public function getAvailableSlots(Request $request)
    {
        $doctorId = $request->input('doctor_id');
        $date = $request->input('date'); // Format 'YYYY-MM-DD'

        if (!$doctorId || !$date) {
            return response()->json([]); // Kembalikan array kosong jika input tidak lengkap
        }

        $carbonDate = Carbon::parse($date);
        $dayOfWeek = $carbonDate->englishDayOfWeek; // e.g., 'Monday', 'Tuesday'

        // Ambil ketersediaan dokter untuk hari yang dipilih
        $availabilities = DoctorAvailability::where('doctor_id', $doctorId)
                                            ->where('day_of_week', $dayOfWeek)
                                            ->orderBy('start_time')
                                            ->get();

        $availableSlots = [];
        $appointmentDurationMinutes = 30; // Durasi default janji temu

        foreach ($availabilities as $availability) {
            $currentSlotTime = Carbon::parse($availability->start_time);
            $availabilityEndTime = Carbon::parse($availability->end_time);

            while ($currentSlotTime->addMinutes($appointmentDurationMinutes)->lte($availabilityEndTime)) {
                $slotStartTime = $currentSlotTime->copy()->subMinutes($appointmentDurationMinutes);
                $slotEndTime = $currentSlotTime->copy(); // Ini adalah akhir slot yang akan dibuat

                // Cek apakah slot ini tumpang tindih dengan janji temu yang sudah ada pada tanggal yang sama
                $isOverlapping = Appointment::where('doctor_id', $doctorId)
                    ->where('appointment_date', $date)
                    ->where('status', '!=', 'cancelled') // Jangan cek yang sudah dibatalkan
                    ->where(function ($query) use ($slotStartTime, $slotEndTime) {
                        $query->where('start_time', '<', $slotEndTime->format('H:i:s'))
                              ->where('end_time', '>', $slotStartTime->format('H:i:s'));
                    })
                    ->exists();

                if (!$isOverlapping) {
                    $availableSlots[] = [
                        'time' => $slotStartTime->format('H:i'),
                        'display' => $slotStartTime->format('H:i') . ' - ' . $slotEndTime->format('H:i')
                    ];
                }
            }
        }

        return response()->json($availableSlots);
    }

    /**
     * Display the specified appointment.
     */
    public function show(Appointment $appointment)
    {
        $appointment->load(['patient.user', 'doctor.user', 'specialty']);
        // Perbaikan: Pastikan view yang benar
        return view('staff.appointment_summary.show', compact('appointment'));
    }

    /**
     * Show the form for editing the specified appointment.
     */
    public function edit(Appointment $appointment)
    {
        $patients = Patient::with('user')->get();
        $doctors = Doctor::with('user', 'specialty')->get();
        $specialties = Specialty::all();
        // Perbaikan: Pastikan view yang benar
        return view('staff.appointment_summary.edit', compact('appointment', 'patients', 'doctors', 'specialties'));
    }

    /**
     * Update the specified appointment in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        // Pastikan validasi menggunakan start_time dan end_time
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'specialty_id' => 'required|exists:specialties,id',
            'appointment_date' => 'required|date',
            'start_time' => 'required|date_format:H:i', // Perbaikan: Gunakan start_time
            'end_time' => 'required|date_format:H:i|after:start_time', // Perbaikan: Gunakan end_time
            'status' => 'required|in:pending,confirmed,completed,cancelled,rescheduled',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Validasi tambahan: pastikan dokter yang dipilih memang memiliki spesialisasi yang dipilih
        $doctor = Doctor::find($request->doctor_id);
        if (!$doctor || $doctor->specialty_id != $request->specialty_id) {
            return redirect()->back()->withInput()->withErrors(['specialty_id' => 'Dokter yang dipilih tidak memiliki spesialisasi yang sesuai.']);
        }

        // Cek tumpang tindih jadwal dokter (kecuali janji temu yang sedang diedit)
        $existingSchedule = Appointment::where('doctor_id', $request->doctor_id)
            ->whereDate('appointment_date', $request->appointment_date)
            ->where('id', '!=', $appointment->id) // Abaikan janji temu yang sedang diedit
            ->where(function($query) use ($request) {
                // Perbaikan: Gunakan start_time dan end_time
                $query->where('start_time', '<', $request->end_time)
                      ->where('end_time', '>', $request->start_time);
            })
            ->first();

        if ($existingSchedule) {
            return redirect()->back()->withInput()->withErrors(['time_overlap' => 'Dokter sudah memiliki janji temu lain pada waktu tersebut.']);
        }

        // Perbaikan: Update ke kolom start_time dan end_time
        $appointment->update([
            'patient_id' => $request->patient_id,
            'doctor_id' => $request->doctor_id,
            'specialty_id' => $request->specialty_id,
            'appointment_date' => $request->appointment_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => $request->status,
            'reason' => $request->reason,
            'notes' => $request->notes,
        ]);

        return redirect()->route('staff.appointments.index')->with('success', 'Janji temu berhasil diperbarui!');
    }

    /**
     * Remove the specified appointment from storage.
     */
    public function destroy(Appointment $appointment)
    {
        try {
            $appointment->delete();
            return redirect()->route('staff.appointments.index')->with('success', 'Janji temu berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('staff.appointments.index')->with('error', 'Terjadi kesalahan saat menghapus janji temu: ' . $e->getMessage());
        }
    }

    /**
     * Update the status of a specific appointment.
     * This is a common staff function.
     */
    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled,rescheduled',
        ]);

        $appointment->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Status janji temu berhasil diperbarui!');
    }
}