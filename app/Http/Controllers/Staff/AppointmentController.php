<?php

namespace App\Http\Controllers\Staff; // Perhatikan namespace

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Carbon\Carbon; // Untuk bekerja dengan tanggal

class AppointmentController extends Controller
{
    /**
     * Display a listing of the appointments for staff.
     */
    public function index(Request $request)
    {
        // Logika untuk memfilter janji temu (opsional, bisa ditambahkan filter tanggal, dokter, dll.)
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
                              ->orderBy('start_time', 'asc')
                              ->paginate(10); // Paginasi untuk tampilan tabel

        return view('staff.appointment_summary.index', compact('appointments'));
    }

    /**
     * Show the form for creating a new appointment.
     */
    public function create()
    {
        $patients = Patient::with('user')->get();
        $doctors = Doctor::with('user', 'specialty')->get();
        $specialties = Specialty::all();
        return view('staff.appointment_summary.create', compact('patients', 'doctors', 'specialties'));
    }

    /**
     * Store a newly created appointment in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_id'       => 'required|exists:patients,id',
            'doctor_id'        => 'required|exists:doctors,id',
            'specialty_id'     => 'required|exists:specialties,id',
            'appointment_date' => 'required|date|after_or_equal:today', // Tanggal tidak boleh lampau
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i|after:start_time',
            'status'           => 'nullable|in:pending,confirmed,completed,cancelled,rescheduled',
            'reason'           => 'nullable|string|max:255',
            'notes'            => 'nullable|string',
        ]);

        // Validasi tambahan: pastikan dokter yang dipilih memang memiliki spesialisasi yang dipilih
        $doctor = Doctor::find($request->doctor_id);
        if (!$doctor || $doctor->specialty_id != $request->specialty_id) {
            return redirect()->back()->withInput()->withErrors(['specialty_id' => 'Dokter yang dipilih tidak memiliki spesialisasi yang sesuai.']);
        }

        // Cek tumpang tindih jadwal dokter
        $existingSchedule = Appointment::where('doctor_id', $request->doctor_id)
                                        ->whereDate('appointment_date', $request->appointment_date)
                                        ->where(function($query) use ($request) {
                                            $query->where('start_time', '<', $request->end_time)
                                                  ->where('end_time', '>', $request->start_time);
                                        })
                                        ->first();

        if ($existingSchedule) {
            return redirect()->back()->withInput()->withErrors(['time_overlap' => 'Dokter sudah memiliki janji temu lain pada waktu tersebut.']);
        }


        Appointment::create($request->all());

        return redirect()->route('staff.appointments.index')->with('success', 'Janji temu berhasil ditambahkan!');
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
        $specialties = Specialty::all();
        return view('staff.appointment_summary.edit', compact('appointment', 'patients', 'doctors', 'specialties'));
    }

    /**
     * Update the specified appointment in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        $request->validate([
            'patient_id'       => 'required|exists:patients,id',
            'doctor_id'        => 'required|exists:doctors,id',
            'specialty_id'     => 'required|exists:specialties,id',
            'appointment_date' => 'required|date',
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i|after:start_time',
            'status'           => 'required|in:pending,confirmed,completed,cancelled,rescheduled',
            'reason'           => 'nullable|string|max:255',
            'notes'            => 'nullable|string',
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
                                            $query->where('start_time', '<', $request->end_time)
                                                  ->where('end_time', '>', $request->start_time);
                                        })
                                        ->first();

        if ($existingSchedule) {
            return redirect()->back()->withInput()->withErrors(['time_overlap' => 'Dokter sudah memiliki janji temu lain pada waktu tersebut.']);
        }

        $appointment->update($request->all());

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