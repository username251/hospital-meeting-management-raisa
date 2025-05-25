<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Carbon\Carbon; 

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Mengambil janji temu dengan relasi yang dimuat, diurutkan berdasarkan tanggal dan waktu, lalu dipaginasi
         $appointments = Appointment::with(['patient.user', 'doctor.user', 'specialty'])->paginate(10);
        return view('admin.appointments.index', compact('appointments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $patients = Patient::with('user')->get(); // Mengambil semua pasien beserta data user terkait
        $doctors = Doctor::with('user')->get();   // Mengambil semua dokter beserta data user terkait
        $specialties = Specialty::all();
        return view('admin.appointments.create', compact('patients', 'doctors', 'specialties'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'specialty_id' => 'nullable|exists:specialties,id', 
            'appointment_date' => 'required|date',              
            'start_time' => 'required|date_format:H:i',         
            'end_time' => 'required|date_format:H:i|after:start_time', 
            'reason' => 'nullable|string|max:1000',             
            'status' => 'required|in:pending,confirmed,completed,cancelled,rescheduled', 
            'notes' => 'nullable|string|max:1000',               
        ]);

        Appointment::create($request->all());

        return redirect()->route('admin.appointments.index')
                         ->with('success', 'Janji temu berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
   public function show(Appointment $appointment) // Menggunakan Route Model Binding
    {
        // PENTING: Muat ulang (reload) relasi 'patient.user', 'doctor.user', dan 'specialty'
        // untuk memastikan data user terkait pasien dan dokter tersedia di view show
        $appointment->load(['patient.user', 'doctor.user', 'specialty']);

        return view('admin.appointments.show', compact('appointment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Appointment $appointment)
    {
        $patients = Patient::with('user')->get();
        $doctors = Doctor::with('user')->get();
        $specialties = Specialty::all();
        return view('admin.appointments.edit', compact('appointment', 'patients', 'doctors', 'specialties'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'specialty_id' => 'nullable|exists:specialties,id',
            'appointment_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'nullable|string|max:1000',
            'status' => 'required|in:pending,confirmed,completed,cancelled,rescheduled',
            'notes' => 'nullable|string|max:1000',
        ]);

        $appointment->update($request->all());

        return redirect()->route('admin.appointments.index')
                         ->with('success', 'Janji temu berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()->route('admin.appointments.index')
                         ->with('success', 'Janji temu berhasil dihapus.');
    }
}