<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use App\Models\BlockedSlot;
use Carbon\Carbon;

class PatientAppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with(['doctor.user'])
            ->where('patient_id', Auth::user()->patient->id)
            ->orderByDesc('appointment_date')
            ->orderBy('start_time')
            ->get();

        return view('patient.appointments.index', compact('appointments'));
    }

    public function create()
    {
        $doctors = Doctor::with('user', 'specialty')->get();
        return view('patient.appointments.create', compact('doctors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date',
            'start_time_slot' => 'required|date_format:H:i:s',
            'end_time_slot' => 'required|date_format:H:i:s',
            'reason' => 'nullable|string|max:255',
        ]);

        $start = $request->start_time_slot;
        $end = $request->end_time_slot;

        // Cek tabrakan dengan janji temu lain
        $conflict = Appointment::where('doctor_id', $request->doctor_id)
            ->whereDate('appointment_date', $request->appointment_date)
            ->where(function ($q) use ($start, $end) {
                $q->where('start_time', '<', $end)->where('end_time', '>', $start);
            })
            ->whereIn('status', ['pending', 'scheduled', 'confirmed', 'check-in', 'waiting', 'in-consultation'])
            ->exists();

        if ($conflict) {
            return back()->withInput()->with('error', 'Slot waktu tersebut sudah dipesan.');
        }

        // Cek tabrakan dengan slot yang diblokir
        $blocked = BlockedSlot::where('doctor_id', $request->doctor_id)
            ->where('blocked_date', $request->appointment_date)
            ->where(function ($q) use ($start, $end) {
                $q->where('start_time', '<', $end)->where('end_time', '>', $start);
            })
            ->exists();

        if ($blocked) {
            return back()->withInput()->with('error', 'Slot waktu ini tidak tersedia (diblokir oleh dokter).');
        }

        $doctor = Doctor::find($request->doctor_id);

        Appointment::create([
            'patient_id' => Auth::user()->patient->id,
            'doctor_id' => $request->doctor_id,
            'specialty_id' => $doctor->specialty_id,
            'appointment_date' => $request->appointment_date,
            'start_time' => $start,
            'end_time' => $end,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('patient.appointments.index')->with('success', 'Janji temu berhasil diajukan.');
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

    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date_format:Y-m-d',
        ]);

        $slots = DoctorAvailability::getAvailableSlots($request->doctor_id, $request->date);

        $formatted = [];
        foreach ($slots as $slot) {
            $formatted[] = [
                'start' => $slot['start'],
                'end' => $slot['end'],
                'display' => Carbon::parse($slot['start'])->format('H:i') . ' - ' . Carbon::parse($slot['end'])->format('H:i'),
            ];
        }

        return response()->json($formatted);
    }
}
