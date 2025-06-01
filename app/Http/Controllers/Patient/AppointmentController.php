<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\BlockedSlot;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; 
use Log;// Pastikan Carbon sudah diimpor

class AppointmentController extends Controller
{
    /**
     * Display a listing of the appointments for the authenticated patient.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Memastikan user yang login adalah seorang pasien dan mendapatkan ID pasiennya
        // Asumsi: Model User memiliki relasi hasOne ke model Patient
        if (!$user->patient) {
            // Jika user bukan pasien atau profil pasien tidak ditemukan,
            // arahkan kembali atau tampilkan pesan error
            return redirect()->route('home.dashboard')->with('error', 'Anda tidak memiliki profil pasien yang terkait.');
        }

        $patientId = $user->patient->id; // Dapatkan ID pasien yang sedang login

        // Bangun query untuk Appointment
        $query = Appointment::with(['doctor.user', 'specialty']);

        // Filter appointments berdasarkan patient_id dari pasien yang sedang login
        $query->where('patient_id', $patientId);

        // Tambahkan filter lain seperti tanggal dan status jika ada dalam request
        // Pasien mungkin hanya ingin melihat janji temu yang akan datang, riwayat, atau yang dibatalkan
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        } else {
            // Default: tampilkan janji temu yang belum selesai atau yang akan datang
            $query->whereIn('status', ['pending', 'confirmed', 'scheduled', 'check-in', 'waiting']);
        }

        if ($request->has('date') && $request->date != '') {
            $query->whereDate('appointment_date', $request->date);
        }

        $appointments = $query->orderBy('appointment_date', 'desc')
                              ->orderBy('start_time', 'asc')
                              ->paginate(10); // Gunakan paginate seperti di controller dokter

        return view('patient.appointments.index', compact('appointments'));
    }

      public function create()
    {
        // Mendapatkan daftar dokter dan spesialisasi yang tersedia
        $doctors = Doctor::with('user', 'specialty')->get();
        $specialties = Specialty::all();

        return view('patient.appointments.create', compact('doctors', 'specialties'));
    }

    /**
     * Store a newly created appointment in storage.
     * Accessible by patients.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i', // Waktu mulai yang dipilih user
            'reason' => 'nullable|string|max:1000',
            // Status awal janji temu yang dibuat pasien biasanya 'pending' atau 'scheduled'
            'status' => 'nullable|string|in:pending,scheduled', // Pasien tidak bisa sembarang pilih status
        ]);

        // Mendapatkan user yang sedang login
        $user = Auth::user();
        if (!$user->patient) {
            return redirect()->back()->with('error', 'Profil pasien Anda tidak ditemukan. Harap lengkapi profil.');
        }

        $patientId = $user->patient->id; // ID pasien yang sedang login

        $requestedStartTimeStr = $request->appointment_time;
        $appointmentDateStr = $request->appointment_date;

        $requestedStartDateTime = Carbon::parse($appointmentDateStr . ' ' . $requestedStartTimeStr);
        $doctorId = $request->doctor_id;
        $appointmentDayOfWeek = $requestedStartDateTime->englishDayOfWeek;

        $appointmentDurationMinutes = 30; // Durasi janji temu default
        $requestedEndTimeStr = $requestedStartDateTime->copy()->addMinutes($appointmentDurationMinutes)->format('H:i:s');

        // 1. Cek ketersediaan dokter di tabel doctor_availabilities
        $availability = DoctorAvailability::where('doctor_id', $doctorId)
            ->where('day_of_week', $appointmentDayOfWeek)
            ->where('start_time', '<=', $requestedStartTimeStr)
            ->where('end_time', '>=', $requestedEndTimeStr)
            ->first();

        if (!$availability) {
            return redirect()->back()->withInput()->withErrors(['appointment_time' => 'Dokter tidak tersedia pada waktu atau hari yang dipilih (di luar jadwal ketersediaan). Silakan pilih slot lain.']);
        }

        // 2. Cek apakah ada janji temu lain yang tumpang tindih untuk dokter yang sama
        $overlappingAppointment = Appointment::where('doctor_id', $doctorId)
            ->where('appointment_date', $appointmentDateStr)
            ->where('status', '!=', 'cancelled') // Janji temu yang dibatalkan tidak dihitung
            ->whereRaw('`start_time` < ? AND `end_time` > ?', [
                $requestedEndTimeStr,
                $requestedStartTimeStr
            ])
            ->exists();

        if ($overlappingAppointment) {
            return redirect()->back()->withInput()->withErrors(['appointment_time' => 'Waktu janji temu yang dipilih sudah terisi untuk dokter ini (tumpang tindih dengan janji temu lain). Silakan pilih slot lain.']);
        }

        // Jika semua validasi lolos, buat janji temu
        // Pasien akan mengisi 'pending' atau 'scheduled' sebagai status awal
        Appointment::create([
            'patient_id' => $patientId, // Menggunakan ID pasien yang login
            'doctor_id' => $request->doctor_id,
            'specialty_id' => Doctor::find($request->doctor_id)->specialty_id ?? null,
            'appointment_date' => $request->appointment_date,
            'start_time' => $requestedStartTimeStr,
            'end_time' => $requestedEndTimeStr,
            'reason' => $request->reason,
            'status' => $request->status ?? 'pending', // Defaultkan ke 'pending' jika tidak disertakan
            'notes' => null, // Pasien tidak mengisi notes, dokter yang akan mengisinya
        ]);

        return redirect()->route('patient.appointments.index')->with('success', 'Janji temu berhasil diajukan! Menunggu konfirmasi.');
    }


    /**
     * Mengambil slot waktu yang tersedia untuk dokter dan tanggal tertentu.
     * Dipanggil melalui AJAX dari sisi pasien.
     */
    // ... dalam fungsi getAvailableSlotspublic function getAvailableSlots(Request $request)
public function getAvailableSlots(Request $request)
{
    $doctorId = $request->input('doctor_id');
    $date = $request->input('date');
    Log::info("getAvailableSlots - Start: doctor_id={$doctorId}, date={$date}");

    if (!$doctorId || !$date) {
        Log::warning("getAvailableSlots - Missing input");
        return response()->json([]);
    }

    $carbonDate = Carbon::parse($date);
    $dayOfWeek = $carbonDate->englishDayOfWeek;
    Log::info("getAvailableSlots - dayOfWeek: {$dayOfWeek}");

    $availabilities = DoctorAvailability::where('doctor_id', $doctorId)
        ->where('day_of_week', $dayOfWeek)
        ->where('is_available', 1)
        ->orderBy('start_time')
        ->get();
    Log::info("getAvailableSlots - availabilities: " . json_encode($availabilities));

    $availableSlots = [];
    $appointmentDurationMinutes = 30;
    Log::info("getAvailableSlots - appointmentDurationMinutes: {$appointmentDurationMinutes}");

    foreach ($availabilities as $availability) {
        $currentSlotTime = Carbon::parse($availability->start_time);
        $availabilityEndTime = Carbon::parse($availability->end_time);
        Log::info("getAvailableSlots - availability: " . json_encode($availability));

        while ($currentSlotTime->lt($availabilityEndTime)) {
            $slotStartTime = $currentSlotTime->copy();
            $slotEndTime = $currentSlotTime->copy()->addMinutes($appointmentDurationMinutes);
            Log::info("getAvailableSlots - slotStartTime: " . $slotStartTime->format('H:i:s') . ", slotEndTime: " . $slotEndTime->format('H:i:s'));

            if ($slotEndTime->gt($availabilityEndTime)) {
                Log::info("getAvailableSlots - slotEndTime > availabilityEndTime, breaking");
                break;
            }

            if ($carbonDate->isToday() && $slotEndTime->lt(Carbon::now())) {
                Log::info("getAvailableSlots - Slot in the past, skipping");
                $currentSlotTime->addMinutes($appointmentDurationMinutes);
                continue;
            }

            $isOverlapping = Appointment::where('doctor_id', $doctorId)
                ->where('appointment_date', $date)
                ->where('status', '!=', 'cancelled')
                ->where(function ($query) use ($slotStartTime, $slotEndTime) {
                    $query->where('start_time', '<', $slotEndTime->format('H:i:s'))
                        ->where('end_time', '>', $slotStartTime->format('H:i:s'));
                })
                ->exists();
            Log::info("getAvailableSlots - isOverlapping: " . ($isOverlapping ? 'true' : 'false'));

            $isBlocked = BlockedSlot::where('doctor_id', $doctorId)
                ->where('blocked_date', $date)
                ->where(function ($query) use ($slotStartTime, $slotEndTime) {
                    $query->where('start_time', '<', $slotEndTime->format('H:i:s'))
                        ->where('end_time', '>', $slotStartTime->format('H:i:s'));
                })
                ->exists();
            Log::info("getAvailableSlots - isBlocked: " . ($isBlocked ? 'true' : 'false'));

            if (!$isOverlapping && !$isBlocked) {
                $availableSlots[] = [
                    'time' => $slotStartTime->format('H:i'),
                    'display' => $slotStartTime->format('H:i') . ' - ' . $slotEndTime->format('H:i')
                ];
                Log::info("getAvailableSlots - Slot added: " . json_encode(end($availableSlots)));
            }
            $currentSlotTime->addMinutes($appointmentDurationMinutes);
        }
    }

    Log::info("getAvailableSlots - Final availableSlots: " . json_encode($availableSlots));
    return response()->json($availableSlots);
}

    /**
     * Display the specified appointment.
     *
     * @param \App\Models\Appointment $appointment
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Appointment $appointment)
    {
        // Pastikan janji temu ini milik pasien yang sedang login
        if ($appointment->patient_id !== Auth::user()->patient->id) {
            return redirect()->route('patient.appointments.index')->with('error', 'Anda tidak diizinkan melihat janji temu ini.');
        }

        $appointment->load(['patient.user', 'doctor.user', 'specialty']);
        return view('patient.appointments.show', compact('appointment'));
    }

    /**
     * Cancel the specified appointment.
     * Hanya boleh dilakukan jika statusnya 'pending' atau 'confirmed' dan belum terlalu dekat dengan jadwal.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Appointment $appointment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Request $request, Appointment $appointment)
    {
        // Pastikan janji temu ini milik pasien yang sedang login
        if ($appointment->patient_id !== Auth::user()->patient->id) {
            return redirect()->route('patient.appointments.index')->with('error', 'Anda tidak diizinkan membatalkan janji temu ini.');
        }

        // Cek status janji temu
        if (!in_array($appointment->status, ['pending', 'confirmed', 'scheduled'])) {
            return redirect()->back()->with('error', 'Janji temu tidak dapat dibatalkan pada status saat ini (' . ucfirst($appointment->status) . ').');
        }

        // Opsional: Batasi pembatalan jika sudah terlalu dekat dengan waktu janji temu
        $appointmentDateTime = Carbon::parse($appointment->appointment_date . ' ' . $appointment->start_time);
        if (Carbon::now()->addHours(24)->greaterThan($appointmentDateTime)) { // Contoh: Tidak bisa batalkan 24 jam sebelum jadwal
            return redirect()->back()->with('error', 'Janji temu tidak dapat dibatalkan karena sudah terlalu dekat dengan jadwal.');
        }

        try {
            $appointment->update(['status' => 'cancelled']);
            return redirect()->route('patient.appointments.index')->with('success', 'Janji temu berhasil dibatalkan!');
        } catch (\Exception $e) {
            return redirect()->route('patient.appointments.index')->with('error', 'Terjadi kesalahan saat membatalkan janji temu: ' . $e->getMessage());
        }
    }
}