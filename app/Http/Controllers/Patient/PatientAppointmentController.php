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
   public function index(Request $request)
{
    $user = Auth::user();
    if (!$user || !$user->patient) {
        return redirect()->route('patient.profile.create')->with('warning', 'Silakan lengkapi profil Anda terlebih dahulu.');
    }

    $user = Auth::user();
    if (!$user || !$user->patient) {
        return redirect()->route('patient.profile.create')->with('warning', 'Silakan lengkapi profil Anda terlebih dahulu.');
    }

    $patient = $user->patient;
    $isProfileComplete = $patient->phone && 
                        $patient->date_of_birth && 
                        $patient->address && 
                        $patient->gender;

    if (!$isProfileComplete) {
        return redirect()->route('patient.profile.edit')->with('warning', 'Silakan lengkapi data profil Anda.');
    }
    $patientId = $user->patient->id;

    $query = Appointment::with(['doctor.user', 'specialty'])
        ->where('patient_id', $patientId);

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    $appointments = $query->orderByDesc('appointment_date')
        ->orderBy('start_time')
        ->paginate(10);

    $doctors = Doctor::with('user')->get();

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
        'appointment_date' => 'required|date|after_or_equal:today',
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
    $appointmentDate = $validatedData['appointment_date'];
    $startTime = $validatedData['start_time_slot'];

    // PERBAIKAN: Pastikan format waktu yang benar
    Log::info("PatientAppointmentController@store - Data yang diterima:", [
        'doctor_id' => $doctorId,
        'appointment_date' => $appointmentDate,
        'start_time_slot' => $startTime,
        'start_time_type' => gettype($startTime),
        'start_time_length' => strlen($startTime)
    ]);

    $doctor = Doctor::findOrFail($doctorId);

    $dayOfWeek = Carbon::parse($appointmentDate)->format('l');
    Log::info("PatientAppointmentController@store - Mencari jadwal untuk:", [
        'doctor_id' => $doctorId,
        'input_appointment_date' => $appointmentDate,
        'derived_day_of_week' => $dayOfWeek,
        'is_available_check' => true
    ]);

    $availability = DoctorAvailability::where('doctor_id', $doctorId)
                        ->where('day_of_week', $dayOfWeek)
                        ->where('is_available', true)
                        ->first();

    if ($availability) {
        Log::info("PatientAppointmentController@store - Jadwal DITEMUKAN:", $availability->toArray());
    } else {
        Log::warning("PatientAppointmentController@store - Jadwal TIDAK DITEMUKAN. Querying for doctor_id: {$doctorId}, day_of_week: '{$dayOfWeek}', is_available: true");
        $anyAvailabilityForDay = DoctorAvailability::where('doctor_id', $doctorId)
                                        ->where('day_of_week', $dayOfWeek)
                                        ->get();
        Log::info("PatientAppointmentController@store - Cek semua jadwal untuk doctor_id: {$doctorId} pada hari {$dayOfWeek}:", $anyAvailabilityForDay->toArray());

        $allDoctorAvailability = DoctorAvailability::where('doctor_id', $doctorId)->get();
        Log::info("PatientAppointmentController@store - Semua jadwal untuk doctor_id: {$doctorId}:", $allDoctorAvailability->toArray());
    }

    if (!$availability) {
        return back()->withInput()->with('error', 'Jadwal dokter tidak ditemukan untuk hari yang dipilih. Pastikan dokter tersedia pada hari tersebut.');
    }

    if (empty($availability->slot_duration) || $availability->slot_duration <= 0) {
        Log::error("PatientAppointmentController@store: Durasi slot tidak valid untuk Doctor ID {$doctorId}, Availability ID {$availability->id}. Duration: {$availability->slot_duration}");
        return back()->withInput()->with('error', 'Durasi slot untuk jadwal dokter ini tidak valid.');
    }
    
    $slotDuration = $availability->slot_duration;

    // PERBAIKAN UTAMA: Pastikan parsing waktu yang benar
    try {
        // Jika startTime sudah dalam format H:i:s, gunakan langsung
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $startTime)) {
            // Format sudah benar H:i:s
            $startTimeCarbon = Carbon::createFromFormat('H:i:s', $startTime);
        } else {
            // Jika format lain, coba parse
            $startTimeCarbon = Carbon::parse($startTime);
        }
        
        $endTime = $startTimeCarbon->copy()->addMinutes($slotDuration)->format('H:i:s');
        
        Log::info("PatientAppointmentController@store - Waktu yang diproses:", [
            'original_start_time' => $startTime,
            'parsed_start_time' => $startTimeCarbon->format('H:i:s'),
            'calculated_end_time' => $endTime,
            'slot_duration' => $slotDuration
        ]);
        
    } catch (\Exception $e) {
        Log::error("PatientAppointmentController@store - Error parsing waktu: " . $e->getMessage(), [
            'start_time_input' => $startTime,
            'slot_duration' => $slotDuration
        ]);
        return back()->withInput()->with('error', 'Format waktu tidak valid. Silakan coba lagi.');
    }

    // Gunakan format waktu yang sudah diperbaiki untuk pengecekan konflik
    $finalStartTime = $startTimeCarbon->format('H:i:s');

    // Cek tabrakan dengan janji temu lain
    $conflictAppointment = Appointment::where('doctor_id', $doctorId)
        ->whereDate('appointment_date', $appointmentDate)
        ->where(function ($q) use ($finalStartTime, $endTime) {
            $q->where('start_time', '<', $endTime)
                ->where('end_time', '>', $finalStartTime);
        })
        ->whereIn('status', ['pending', 'scheduled', 'confirmed', 'check-in', 'waiting', 'in-consultation'])
        ->exists();

    if ($conflictAppointment) {
        Log::warning("Store Appointment: Slot conflict (appointment exists) for Doctor ID {$doctorId}, Date {$appointmentDate}, Time {$finalStartTime}-{$endTime}");
        return back()->withInput()->with('error', 'Maaf, slot waktu yang Anda pilih sudah dipesan. Silakan pilih slot lain.');
    }

    // Cek tabrakan dengan slot yang diblokir
    $blocked = BlockedSlot::where('doctor_id', $doctorId)
        ->where('blocked_date', $appointmentDate)
        ->where(function ($q) use ($finalStartTime, $endTime) {
            $q->where('start_time', '<', $endTime)
                ->where('end_time', '>', $finalStartTime);
        })
        ->exists();

    if ($blocked) {
        Log::warning("Store Appointment: Slot conflict (blocked slot) for Doctor ID {$doctorId}, Date {$appointmentDate}, Time {$finalStartTime}-{$endTime}");
        return back()->withInput()->with('error', 'Maaf, slot waktu yang Anda pilih tidak tersedia (diblokir). Silakan pilih slot lain.');
    }

    // Buat appointment dengan waktu yang sudah diperbaiki
    Appointment::create([
        'patient_id' => $patientId,
        'doctor_id' => $doctorId,
        'specialty_id' => $doctor->specialty_id,
        'appointment_date' => $appointmentDate,
        'start_time' => $finalStartTime,
        'end_time' => $endTime,
        'reason' => $validatedData['reason'],
        'status' => 'pending',
    ]);
    
    Log::info("Appointment created successfully for Patient ID {$patientId}, Doctor ID {$doctorId}, Date {$appointmentDate}, Time {$finalStartTime}-{$endTime}");

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

        // VALIDASI WAKTU untuk update
        $appointmentDateTime = Carbon::parse($request->appointment_date . ' ' . $request->start_time_slot);
        $now = Carbon::now();
        
        if ($appointmentDateTime->isPast()) {
            return back()->withInput()->with('error', 'Tidak dapat mengubah janji temu ke waktu yang sudah berlalu.');
        }

        if ($appointmentDateTime->isToday() && $appointmentDateTime->lt($now)) {
            return back()->withInput()->with('error', 'Waktu yang dipilih sudah berlalu hari ini.');
        }

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

        // VALIDASI: Cek apakah tanggal yang diminta sudah lewat
        $requestedDate = Carbon::parse($date);
        $today = Carbon::today();
        
        if ($requestedDate->lt($today)) {
            Log::info("getAvailableSlots: Requested past date {$date}");
            return response()->json([]); // Return empty slots untuk tanggal yang sudah lewat
        }

        $slots = DoctorAvailability::getAvailableSlots($doctorId, $date);

        $formatted = [];
        $now = Carbon::now();
        
        if (is_array($slots)) {
            foreach ($slots as $slot) {
                if (is_array($slot) && isset($slot['start']) && isset($slot['end'])) {
                    // FILTER SLOT: Untuk hari ini, hanya tampilkan slot yang belum lewat
                    if ($requestedDate->isToday()) {
                        $slotDateTime = Carbon::parse($date . ' ' . $slot['start']);
                        if ($slotDateTime->lte($now)) {
                            // Skip slot yang sudah lewat atau sedang berlangsung
                            continue;
                        }
                    }
                    
                    $formatted[] = [
                        'start' => $slot['start'],
                        'end' => $slot['end'],
                        'display' => Carbon::parse($slot['start'])->format('H:i') . ' - ' . Carbon::parse($slot['end'])->format('H:i'),
                    ];
                } else {
                    \Log::warning("PatientAppointmentController: Struktur slot tidak valid diterima dari DoctorAvailability::getAvailableSlots. DoctorID: {$doctorId}, Date: {$date}. Data Slot: " . json_encode($slot));
                }
            }
        } else {
            \Log::error("PatientAppointmentController: Diharapkan array dari DoctorAvailability::getAvailableSlots. DoctorID: {$doctorId}, Date: {$date}. Diterima: " . gettype($slots));
        }

        return response()->json($formatted);

    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error("PatientAppointmentController: Error validasi saat mengambil slot. DoctorID: {$request->input('doctor_id')}, Date: {$request->input('date')}. Errors: " . json_encode($e->errors()));
        return response()->json(['message' => 'Data yang diberikan tidak valid.', 'errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        \Log::error("PatientAppointmentController: Error di getAvailableSlots. DoctorID: {$request->input('doctor_id')}, Date: {$request->input('date')}: " . $e->getMessage() . " di " . $e->getFile() . ":" . $e->getLine());
        return response()->json([]);
        }
    }
}