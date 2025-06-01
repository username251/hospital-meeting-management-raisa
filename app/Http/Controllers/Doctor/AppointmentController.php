<?php

namespace App\Http\Controllers\doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Specialty;
use Illuminate\Http\Request;
use App\Models\DoctorAvailability;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // Tambahkan ini untuk mengakses user yang sedang login


class AppointmentController extends Controller
{
    /**
     * Display a listing of the appointments for doctor.
     */
    public function index(Request $request)
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Memastikan user yang login adalah seorang dokter dan mendapatkan ID dokternya
        // Asumsi: Model User memiliki relasi hasOne ke model Doctor
        if (!$user->doctor) {
            // Jika user bukan dokter atau profil dokter tidak ditemukan,
            // arahkan kembali atau tampilkan pesan error
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki profil dokter yang terkait.');
        }

        $doctorId = $user->doctor->id; // Dapatkan ID dokter yang sedang login

        // Bangun query untuk Appointment
        $query = Appointment::with(['patient.user', 'doctor.user', 'specialty']);

        // Filter appointments berdasarkan doctor_id dari dokter yang sedang login
        $query->where('doctor_id', $doctorId);

        // Tambahkan filter lain seperti tanggal dan status jika ada dalam request
        if ($request->has('date') && $request->date != '') {
            $query->whereDate('appointment_date', $request->date);
        }

        // Anda bisa menambahkan filter status khusus untuk antrean,
        // misalnya hanya menampilkan status yang relevan untuk antrean (check-in, waiting, in-consultation)
        // Jika Anda ingin semua status tetap bisa difilter oleh request, biarkan seperti ini:
        if ($request->has('status') && $request->status != '') 
        {
            $query->where('status', $request->status);
        } else {
                // Tambahkan 'pending' di sini jika Anda ingin dokter melihat janji temu pending secara default
                $query->whereIn('status', ['scheduled', 'pending', 'check-in', 'waiting', 'in-consultation']);
        }


        $appointments = $query->orderBy('appointment_date', 'desc')
                               ->orderBy('start_time', 'asc')
                               ->paginate(10);

        return view('doctor.appointment_summary.index', compact('appointments'));
    }

    /**
     * Show the form for creating a new appointment.
     */
    public function create()
    {
        $patients = Patient::with('user')->get();
        $doctors = Doctor::with('user', 'specialty')->get();
        $specialties = Specialty::all(); // Meskipun tidak digunakan di form, tetap di-pass jika ada kebutuhan lain

        return view('doctor.appointment_summary.create', compact('patients', 'doctors', 'specialties'));
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
            'status' => 'required|string|in:pending,confirmed,completed,cancelled,rescheduled,scheduled,check-in,waiting,in-consultation,no-show', // Sesuaikan dengan nilai ENUM di DB Anda
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
            // specialty_id tidak lagi diambil dari form, akan diisi otomatis jika ada relasi
            // atau diambil dari doctor yang dipilih jika diperlukan untuk disimpan di appointment
            'specialty_id' => Doctor::find($request->doctor_id)->specialty_id ?? null, // Ambil specialty_id dari dokter yang dipilih
            'appointment_date' => $request->appointment_date,
            'start_time' => $requestedStartTimeStr,
            'end_time' => $requestedEndTimeStr,
            'reason' => $request->reason,
            'status' => $request->status,
            'notes' => $request->notes, // Pastikan notes juga disimpan
        ]);

        return redirect()->route('doctor.appointments.index')->with('success', 'Janji temu berhasil dibuat.');
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
        return view('doctor.appointment_summary.show', compact('appointment'));
    }

    /**
     * Show the form for editing the specified appointment.
     */
    public function edit(Appointment $appointment)
    {
        $patients = Patient::with('user')->get();
        $doctors = Doctor::with('user', 'specialty')->get();
        $specialties = Specialty::all(); // Meskipun tidak digunakan di form, tetap di-pass jika ada kebutuhan lain

        return view('doctor.appointment_summary.edit', compact('appointment', 'patients', 'doctors', 'specialties'));
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
            'status' => 'required|in:pending,confirmed,completed,cancelled,rescheduled,check-in,waiting,in-consultation,no-show', // Tambahkan status dari ENUM
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

        return redirect()->route('doctor.appointments.index')->with('success', 'Janji temu berhasil diperbarui!');
    }

    /**
     * Remove the specified appointment from storage.
     */
    public function destroy(Appointment $appointment)
    {
        try {
            $appointment->delete();
            return redirect()->route('doctor.appointments.index')->with('success', 'Janji temu berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('doctor.appointments.index')->with('error', 'Terjadi kesalahan saat menghapus janji temu: ' . $e->getMessage());
        }
    }

    /**
     * Update the status of a specific appointment.
     * This is a common doctor function.
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