<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use App\Models\BlockedSlot;
use Carbon\Carbon;
use Log;

class PatientAppointmentController extends Controller
{
   public function index(Request $request) // Tambahkan Request jika Anda ingin menerima input untuk filter atau halaman
{
    $user = Auth::user();
    if (!$user || !$user->patient) {
        // Arahkan ke halaman lengkapi profil jika profil pasien tidak ada
        return redirect()->route('patient.profile.create')->with('warning', 'Silakan lengkapi profil Anda terlebih dahulu.');
    }

    // Atau jika Anda ingin mengecek kelengkapan profil yang lebih detail
    $user = Auth::user();
    if (!$user || !$user->patient) {
        return redirect()->route('patient.profile.create')->with('warning', 'Silakan lengkapi profil Anda terlebih dahulu.');
    }

    // Cek kelengkapan data profil (opsional)
    $patient = $user->patient;
    $isProfileComplete = $patient->phone && 
                        $patient->date_of_birth && 
                        $patient->address && 
                        $patient->gender;

    if (!$isProfileComplete) {
        return redirect()->route('patient.profile.edit')->with('warning', 'Silakan lengkapi data profil Anda.');
    }
    $patientId = $user->patient->id;

    $query = Appointment::with(['doctor.user', 'specialty']) // Pastikan relasi specialty ada di model Appointment
        ->where('patient_id', $patientId);

    // Contoh jika Anda ingin menambahkan filter status dari request
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    $appointments = $query->orderByDesc('appointment_date')
        ->orderBy('start_time')
        ->paginate(10); // Ganti get() dengan paginate(), angka 10 adalah jumlah item per halaman

    $doctors = Doctor::with('user')->get(); // Atau query yang lebih spesifik

    return view('patient.appointments.index', compact('appointments', 'doctors'));
}

    public function create()
    {
        $doctors = Doctor::with('user', 'specialty')->get();
        return view('patient.appointments.create', compact('doctors'));
    }

   public function store(Request $request)
{
    $validatedData = $request->validate([
        'doctor_id' => 'required|exists:doctors,id',
        'appointment_date' => 'required|date|after_or_equal:today', // Pastikan ini diterima dalam format YYYY-MM-DD
        'start_time_slot' => 'required|date_format:H:i:s',
        'reason' => 'nullable|string|max:255',
    ]);

    $user = Auth::user();
    if (!$user) {
        Log::error("PatientAppointmentController@store: User not authenticated.");
        return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
    }

    $patientProfile = $user->patient;
    if (!$patientProfile) {
        Log::error("PatientAppointmentController@store: User ID {$user->id} ({$user->email}) does not have patient profile.");
        return redirect()->back()->with('error', 'Profil pasien Anda tidak ditemukan. Harap lengkapi profil Anda atau hubungi administrator.')->withInput();
    }
    $patientId = $patientProfile->id;

    $doctorId = $validatedData['doctor_id'];
    $appointmentDate = $validatedData['appointment_date']; // Ini seharusnya "2025-05-03"
    $startTime = $validatedData['start_time_slot'];

    $doctor = Doctor::findOrFail($doctorId);

    // ---- TAMBAHKAN LOG DI SINI ----
    $dayOfWeek = Carbon::parse($appointmentDate)->format('l');
    Log::info("PatientAppointmentController@store - Mencari jadwal untuk:", [
        'doctor_id' => $doctorId,
        'input_appointment_date' => $appointmentDate, // Lihat format tanggal yang diterima
        'derived_day_of_week' => $dayOfWeek,         // Lihat hari yang di-derive
        'is_available_check' => true
    ]);
    // ---- AKHIR DARI LOG ----

    $availability = DoctorAvailability::where('doctor_id', $doctorId)
                        ->where('day_of_week', $dayOfWeek)
                        ->where('is_available', true)
                        ->first();

    // ---- TAMBAHKAN LOG HASIL QUERY ----
    if ($availability) {
        Log::info("PatientAppointmentController@store - Jadwal DITEMUKAN:", $availability->toArray());
    } else {
        Log::warning("PatientAppointmentController@store - Jadwal TIDAK DITEMUKAN. Querying for doctor_id: {$doctorId}, day_of_week: '{$dayOfWeek}', is_available: true");
        // Untuk debug lebih lanjut, kita bisa coba query tanpa is_available atau day_of_week
        $anyAvailabilityForDay = DoctorAvailability::where('doctor_id', $doctorId)
                                        ->where('day_of_week', $dayOfWeek)
                                        ->get();
        Log::info("PatientAppointmentController@store - Cek semua jadwal untuk doctor_id: {$doctorId} pada hari {$dayOfWeek}:", $anyAvailabilityForDay->toArray());

        $allDoctorAvailability = DoctorAvailability::where('doctor_id', $doctorId)->get();
        Log::info("PatientAppointmentController@store - Semua jadwal untuk doctor_id: {$doctorId}:", $allDoctorAvailability->toArray());
    }
    // ---- AKHIR DARI LOG HASIL QUERY ----


    if (!$availability) { // Kondisi ini tetap, tapi pesan error di log akan lebih detail
        return back()->withInput()->with('error', 'Jadwal dokter tidak ditemukan untuk hari yang dipilih. Pastikan dokter tersedia pada hari tersebut.');
    }

    if (empty($availability->slot_duration) || $availability->slot_duration <= 0) {
        Log::error("PatientAppointmentController@store: Durasi slot tidak valid untuk Doctor ID {$doctorId}, Availability ID {$availability->id}. Duration: {$availability->slot_duration}");
        return back()->withInput()->with('error', 'Durasi slot untuk jadwal dokter ini tidak valid.');
    }
    $slotDuration = $availability->slot_duration;
    $endTime = Carbon::parse($startTime)->addMinutes($slotDuration)->format('H:i:s');

    // ... (sisa kode untuk pengecekan konflik dan create appointment) ...
    // Cek tabrakan dengan janji temu lain
    $conflictAppointment = Appointment::where('doctor_id', $doctorId)
        ->whereDate('appointment_date', $appointmentDate)
        ->where(function ($q) use ($startTime, $endTime) {
            $q->where('start_time', '<', $endTime)
                ->where('end_time', '>', $startTime);
        })
        ->whereIn('status', ['pending', 'scheduled', 'confirmed', 'check-in', 'waiting', 'in-consultation'])
        ->exists();

    if ($conflictAppointment) {
            Log::warning("Store Appointment: Slot conflict (appointment exists) for Doctor ID {$doctorId}, Date {$appointmentDate}, Time {$startTime}-{$endTime}");
            return back()->withInput()->with('error', 'Maaf, slot waktu yang Anda pilih sudah dipesan. Silakan pilih slot lain.');
    }

    // Cek tabrakan dengan slot yang diblokir
    $blocked = BlockedSlot::where('doctor_id', $doctorId)
        ->where('blocked_date', $appointmentDate)
        ->where(function ($q) use ($startTime, $endTime) {
            $q->where('start_time', '<', $endTime)
                ->where('end_time', '>', $startTime);
        })
        ->exists();

    if ($blocked) {
        Log::warning("Store Appointment: Slot conflict (blocked slot) for Doctor ID {$doctorId}, Date {$appointmentDate}, Time {$startTime}-{$endTime}");
        return back()->withInput()->with('error', 'Maaf, slot waktu yang Anda pilih tidak tersedia (diblokir). Silakan pilih slot lain.');
    }
    //--- Akhir dari pengecekan ulang ---

    Appointment::create([
        'patient_id' => $patientId,
        'doctor_id' => $doctorId,
        'specialty_id' => $doctor->specialty_id,
        'appointment_date' => $appointmentDate,
        'start_time' => $startTime,
        'end_time' => $endTime,
        'reason' => $validatedData['reason'],
        'status' => 'pending',
    ]);
    Log::info("Appointment created successfully for Patient ID {$patientId}, Doctor ID {$doctorId}, Date {$appointmentDate}, Time {$startTime}-{$endTime}");

    return redirect()->route('appointments.index')->with('success', 'Janji temu berhasil diajukan.');
}

    public function edit(Appointment $appointment)
    {
        if ($appointment->patient_id !== Auth::user()->patient->id) {
            abort(403);
        }

        $doctors = Doctor::with('user', 'specialty')->get();
        return view('patient.appointments.edit', compact('appointment', 'doctors'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        if ($appointment->patient_id !== Auth::user()->patient->id) {
            abort(403);
        }

        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date',
            'start_time_slot' => 'required|date_format:H:i:s',
            'end_time_slot' => 'required|date_format:H:i:s',
            'reason' => 'nullable|string|max:255',
        ]);

        $start = $request->start_time_slot;
        $end = $request->end_time_slot;

        $conflict = Appointment::where('doctor_id', $request->doctor_id)
            ->whereDate('appointment_date', $request->appointment_date)
            ->where('id', '!=', $appointment->id)
            ->where(function ($q) use ($start, $end) {
                $q->where('start_time', '<', $end)->where('end_time', '>', $start);
            })
            ->whereIn('status', ['pending', 'scheduled', 'confirmed', 'check-in', 'waiting', 'in-consultation'])
            ->exists();

        if ($conflict) {
            return back()->withInput()->with('error', 'Slot waktu bertabrakan dengan janji temu lain.');
        }

        $blocked = BlockedSlot::where('doctor_id', $request->doctor_id)
            ->where('blocked_date', $request->appointment_date)
            ->where(function ($q) use ($start, $end) {
                $q->where('start_time', '<', $end)->where('end_time', '>', $start);
            })
            ->exists();

        if ($blocked) {
            return back()->withInput()->with('error', 'Slot waktu ini diblokir oleh dokter.');
        }

        $doctor = Doctor::find($request->doctor_id);

        $appointment->update([
            'doctor_id' => $request->doctor_id,
            'specialty_id' => $doctor->specialty_id,
            'appointment_date' => $request->appointment_date,
            'start_time' => $start,
            'end_time' => $end,
            'reason' => $request->reason,
        ]);

        return redirect()->route('patient.appointments.index')->with('success', 'Janji temu berhasil diperbarui.');
    }

    public function show(Appointment $appointment)
    {
        if ($appointment->patient_id !== Auth::user()->patient->id) {
            abort(403);
        }

        return view('patient.appointments.show', compact('appointment'));
    }

    public function cancel(Appointment $appointment)
    {
        $patient = Patient::where('user_id', Auth::id())->firstOrFail();
        if ($appointment->patient_id !== $patient->id) {
            return redirect()->route('appointments.index')->with('error', 'Akses ditolak.');
        }

        // Hanya boleh dibatalkan jika statusnya pending, confirmed, atau scheduled
        if (in_array($appointment->status, ['pending', 'confirmed', 'scheduled'])) {
            $appointment->status = 'cancelled';
            $appointment->save();
            return redirect()->route('appointments.index')->with('success', 'Janji temu berhasil dibatalkan.');
        }

        return redirect()->route('appointments.index')->with('error', 'Janji temu tidak dapat dibatalkan.');
    }
   
public function getAvailableSlots(Request $request)
{
    try {
        $validatedData = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date_format:Y-m-d',
        ]);

        $doctorId = $validatedData['doctor_id'];
        $date = $validatedData['date'];

        // Memanggil method statis dari model DoctorAvailability
        $slots = DoctorAvailability::getAvailableSlots($doctorId, $date);

        $formatted = [];
        if (is_array($slots)) {
            foreach ($slots as $slot) {
                if (is_array($slot) && isset($slot['start']) && isset($slot['end'])) {
                    $formatted[] = [
                        'start' => $slot['start'], // Format sudah H:i:s dari model
                        'end' => $slot['end'],     // Format sudah H:i:s dari model
                        'display' => Carbon::parse($slot['start'])->format('H:i') . ' - ' . Carbon::parse($slot['end'])->format('H:i'),
                    ];
                } else {
                    // Log jika ada struktur slot yang tidak valid dari model
                    \Log::warning("PatientAppointmentController: Struktur slot tidak valid diterima dari DoctorAvailability::getAvailableSlots. DoctorID: {$doctorId}, Date: {$date}. Data Slot: " . json_encode($slot));
                }
            }
        } else {
            // Log jika $slots bukan array
            \Log::error("PatientAppointmentController: Diharapkan array dari DoctorAvailability::getAvailableSlots. DoctorID: {$doctorId}, Date: {$date}. Diterima: " . gettype($slots));
            // $formatted akan tetap kosong, menghasilkan respons json([])
        }

        return response()->json($formatted);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Tangani error validasi secara eksplisit jika diperlukan,
        // atau biarkan Laravel yang mengirim respons 422.
        // Untuk debugging, log error validasi.
        \Log::error("PatientAppointmentController: Error validasi saat mengambil slot. DoctorID: {$request->input('doctor_id')}, Date: {$request->input('date')}. Errors: " . json_encode($e->errors()));
        // Kembalikan respons JSON dengan error validasi
        return response()->json(['message' => 'Data yang diberikan tidak valid.', 'errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        // Tangkap semua error lainnya
        \Log::error("PatientAppointmentController: Error di getAvailableSlots. DoctorID: {$request->input('doctor_id')}, Date: {$request->input('date')}: " . $e->getMessage() . " di " . $e->getFile() . ":" . $e->getLine());
        // Kembalikan array kosong agar frontend menampilkan "No available slots" atau pesan serupa,
        // dan tidak menampilkan "Failed to load slots" akibat error 500.
        return response()->json([]); // HTTP status default adalah 200 OK
        }
    }
}