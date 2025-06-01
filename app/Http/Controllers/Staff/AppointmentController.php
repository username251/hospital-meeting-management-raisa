<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\BlockedSlot;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Specialty;
use Illuminate\Http\Request;
use App\Models\DoctorAvailability;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;


class AppointmentController extends Controller
{
    /**
     * Display a listing of the appointments for staff.
     */
     public function index()
    {
        $appointments = Appointment::with(['doctor.user', 'patient.user', 'specialty'])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->paginate(10);

        return view('staff.appointment_summary.index', compact('appointments'));
    }

    /**
     * Show the form for creating a new appointment.
     */
    public function create(Request $request)
    {
        $doctors = Doctor::with('user', 'specialty')->get(); // Load specialty for doctor dropdown
        $patients = Patient::with('user')->get();

        // Tidak lagi memuat availableSlots di sini secara langsung
        // Karena akan dimuat via AJAX oleh JavaScript
        $selectedDoctorId = old('doctor_id'); // Ambil dari old input jika ada
        $appointmentDate = old('appointment_date'); // Ambil dari old input jika ada

        return view('staff.appointment_summary.create', compact('doctors', 'patients', 'selectedDoctorId', 'appointmentDate'));
    }

    /**
     * Store a newly created appointment in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date_format:Y-m-d',
            'start_time_slot' => 'required|date_format:H:i:s', // Ini adalah 'start' dari slot yang dipilih
            'end_time_slot' => 'required|date_format:H:i:s',   // Ini adalah 'end' dari slot yang dipilih
            'reason' => 'nullable|string|max:255',
            'status' => ['required', Rule::in(['pending','confirmed','scheduled', 'completed', 'cancelled', 'rescheduled', 'check-in', 'waiting', 'in-consultation'])],        ]);

        $start_time = $request->input('start_time_slot');
        $end_time = $request->input('end_time_slot');

        // Re-check for overlap (double-check security) dengan Appointment yang sudah ada
        $isAppointmentOverlap = Appointment::where('doctor_id', $request->doctor_id)
            ->whereDate('appointment_date', $request->appointment_date)
            ->where(function ($query) use ($start_time, $end_time) {
                // Check for overlap: (StartA < EndB) and (EndA > StartB)
                $query->where('start_time', '<', $end_time)
                      ->where('end_time', '>', $start_time);
            })
            // Hanya pertimbangkan status yang aktif dan belum selesai/dibatalkan
            ->whereIn('status', ['pending', 'confirmed', 'scheduled', 'check-in', 'waiting', 'in-consultation'])
            ->exists();

        if ($isAppointmentOverlap) {
            return back()->withInput()->with('error', 'Slot waktu yang dipilih sudah terisi oleh janji temu lain.');
        }

        // Check for overlap with blocked slots
        $isBlockedOverlap = BlockedSlot::where('doctor_id', $request->doctor_id)
            ->where('blocked_date', $request->appointment_date)
            ->where(function ($query) use ($start_time, $end_time) {
                // Check for overlap: (StartA < EndB) and (EndA > StartB)
                $query->where('start_time', '<', $end_time)
                      ->where('end_time', '>', $start_time);
            })
            ->exists();

        if ($isBlockedOverlap) {
            return back()->withInput()->with('error', 'Slot waktu ini diblokir oleh dokter.');
        }

        // Get specialty_id from the selected doctor
        $doctor = Doctor::find($request->doctor_id);
        $specialtyId = $doctor ? $doctor->specialty_id : null;

        Appointment::create([
            'patient_id' => $request->patient_id,
            'doctor_id' => $request->doctor_id,
            'specialty_id' => $specialtyId,
            'appointment_date' => $request->appointment_date,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'reason' => $request->reason,
            'status' => $request->status,
            'notes' => $request->notes ?? null, // Tambahkan notes jika ada di form, atau null
        ]);

        return redirect()->route('staff.appointments.index')->with('success', 'Janji temu berhasil dibuat.');
    }



    /**
     * Mengambil slot waktu yang tersedia untuk dokter dan tanggal tertentu.
     * Dipanggil melalui AJAX.
     */
      public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date_format:Y-m-d',
        ]);

        $doctorId = $request->input('doctor_id');
        $appointmentDate = $request->input('date');

        $slots = DoctorAvailability::getAvailableSlots($doctorId, $appointmentDate);

        // Format for AJAX response
        $formattedSlots = [];
        foreach ($slots as $slot) {
            $formattedSlots[] = [
                'start' => $slot['start'], // Waktu mulai slot
                'end' => $slot['end'],     // Waktu berakhir slot
                'display' => Carbon::parse($slot['start'])->format('H:i') . ' - ' . Carbon::parse($slot['end'])->format('H:i'),
            ];
        }

        return response()->json($formattedSlots);
    }

    /**
     * Display the specified appointment.
     */
    public function show(Appointment $appointment)
    {
        $appointment->load(['patient.user', 'doctor.user', 'specialty']);
        return view('staff.appointment_summary.show', compact('appointment'));
    }

    /**
     * Show the form for editing the specified appointment.
     */
    public function edit(Appointment $appointment)
    {
        $patients = Patient::with('user')->get();
        $doctors = Doctor::with('user', 'specialty')->get();
        $specialties = Specialty::all(); // Meskipun tidak digunakan di form, tetap di-pass jika ada kebutuhan lain

        return view('staff.appointment_summary.edit', compact('appointment', 'patients', 'doctors', 'specialties'));
    }

    /**
     * Update the specified appointment in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        // Validasi disesuaikan dengan input dari form yang baru (tanpa specialty_id dan end_time)
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|date_format:H:i', // Nama input disamakan
            'status' => 'required|in:scheduled,pending,confirmed,completed,cancelled,rescheduled,check-in,waiting,in-consultation,no-show', // Tambahkan status dari ENUM
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Hitung start_time dan end_time berdasarkan input appointment_time
        $requestedStartTimeStr = $request->appointment_time;
        $appointmentDateStr = $request->appointment_date;
        $requestedStartDateTime = Carbon::parse($appointmentDateStr . ' ' . $requestedStartTimeStr);
        $appointmentDurationMinutes = 30; // Durasi default janji temu
        $requestedEndTimeStr = $requestedStartDateTime->copy()->addMinutes($appointmentDurationMinutes)->format('H:i:s');

        // Validasi tambahan: cek ketersediaan dokter di tabel doctor_availabilities (sama seperti store)
        $doctorId = $request->doctor_id;
        $appointmentDayOfWeek = $requestedStartDateTime->englishDayOfWeek;

        $availability = DoctorAvailability::where('doctor_id', $doctorId)
            ->where('day_of_week', $appointmentDayOfWeek)
            ->where('start_time', '<=', $requestedStartTimeStr)
            ->where('end_time', '>=', $requestedEndTimeStr)
            ->first();

        if (!$availability) {
            return redirect()->back()->withInput()->withErrors(['appointment_time' => 'Dokter tidak tersedia pada waktu atau hari yang dipilih (di luar jadwal ketersediaan). Silakan pilih slot lain.']);
        }

        // Cek tumpang tindih jadwal dokter (kecuali janji temu yang sedang diedit)
        $overlappingAppointment = Appointment::where('doctor_id', $request->doctor_id)
            ->whereDate('appointment_date', $request->appointment_date)
            ->where('id', '!=', $appointment->id) // Abaikan janji temu yang sedang diedit
            ->where('status', '!=', 'cancelled') // Jangan cek yang sudah dibatalkan
            ->where(function($query) use ($requestedStartTimeStr, $requestedEndTimeStr) { // Gunakan variabel yang baru dihitung
                $query->where('start_time', '<', $requestedEndTimeStr)
                      ->where('end_time', '>', $requestedStartTimeStr);
            })
            ->first();

        if ($overlappingAppointment) {
            return redirect()->back()->withInput()->withErrors(['time_overlap' => 'Dokter sudah memiliki janji temu lain pada waktu tersebut.']);
        }

        // Update data janji temu
        $appointment->update([
            'patient_id' => $request->patient_id,
            'doctor_id' => $request->doctor_id,
            // specialty_id tidak lagi diambil dari form, ambil dari doctor yang dipilih
            'specialty_id' => Doctor::find($request->doctor_id)->specialty_id ?? null,
            'appointment_date' => $request->appointment_date,
            'start_time' => $requestedStartTimeStr, // Menggunakan waktu yang dihitung
            'end_time' => $requestedEndTimeStr,     // Menggunakan waktu yang dihitung
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
            'status' => 'required|in:pending,confirmed,completed,cancelled,rescheduled,check-in,waiting,in-consultation,no-show', // Tambahkan status dari ENUM
        ]);

        $appointment->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Status janji temu berhasil diperbarui!');
    }
}
