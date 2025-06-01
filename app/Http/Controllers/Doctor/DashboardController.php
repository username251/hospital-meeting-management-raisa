<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use Carbon\Carbon; // Untuk bekerja dengan tanggal dan waktu

class DashboardController extends Controller
{
    /**
     * Display the doctor's dashboard with today's appointments.
     */
   public function index()
{
    $doctor = Auth::user()->doctor;

    if (!$doctor) {
        return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki profil dokter.');
    }

    // Ambil janji temu hari ini menggunakan 'appointment_date' dan 'start_time'
    $todayAppointments = Appointment::with(['patient.user'])
        ->where('doctor_id', $doctor->id)
        ->whereDate('appointment_date', Carbon::today()) // Ganti 'date' menjadi 'appointment_date'
        ->orderBy('start_time') // Ganti 'time' menjadi 'start_time'
        ->get();

    return view('doctor.dashboard', compact('doctor', 'todayAppointments'));
}

public function todayAppointments()
{
    $doctor = Auth::user()->doctor;

    $todayAppointments = Appointment::with(['patient.user'])
        ->where('doctor_id', $doctor->id)
        ->whereDate('appointment_date', Carbon::today()) // Ganti 'date' menjadi 'appointment_date'
        ->orderBy('start_time') // Ganti 'time' menjadi 'start_time'
        ->get();

    return view('doctor.appointments.today', compact('todayAppointments'));
}

    /**
     * Display a dedicated page for today's appointments (if needed, or integrate into index).
     */
  
    /**
     * Update the status of an appointment.
     */
    public function updateAppointmentStatus(Request $request, Appointment $appointment)
    {
        // Pastikan dokter yang login adalah dokter yang memiliki janji temu ini
        if ($appointment->doctor_id !== Auth::user()->doctor->id) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengubah janji temu ini.');
        }

        $request->validate([
            'status' => ['required', 'string', 'in:confirmed,completed,cancelled,missed,in_consultation'], // Tambahkan status 'in_consultation'
        ]);

        $appointment->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Status janji temu berhasil diperbarui.');
    }
}