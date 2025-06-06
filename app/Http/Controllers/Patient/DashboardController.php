<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Specialty;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->patient) 
        {
            // Arahkan ke halaman lengkapi profil jika profil pasien tidak ada
            return redirect()->route('patient.profile.create')->with('warning', 'Silakan lengkapi profil Anda terlebih dahulu.');
        }

        // Cek kelengkapan data profil (opsional)
        $patient = $user->patient;
        $isProfileComplete = $patient->phone && 
                            $patient->date_of_birth && 
                            $patient->address && 
                            $patient->gender;

        if (!$isProfileComplete) 
        {
            return redirect()->route('patient.profile.edit')->with('warning', 'Silakan lengkapi data profil Anda.');
        }

        // Ambil data untuk dashboard
        $patientId = $patient->id;
        
        // Statistik umum
        $totalAppointments = Appointment::where('patient_id', $patientId)->count();
        $upcomingAppointments = Appointment::where('patient_id', $patientId)
            ->whereIn('status', ['confirmed', 'scheduled'])
            ->where('appointment_date', '>=', Carbon::today())
            ->count();
        $completedAppointments = Appointment::where('patient_id', $patientId)
            ->where('status', 'completed')
            ->count();
        $cancelledAppointments = Appointment::where('patient_id', $patientId)
            ->where('status', 'cancelled')
            ->count();

        // Janji temu mendatang (5 terdekat)
        $nextAppointments = Appointment::where('patient_id', $patientId)
            ->whereIn('status', ['confirmed', 'scheduled'])
            ->where('appointment_date', '>=', Carbon::today())
            ->with(['doctor.user', 'specialty'])
            ->orderBy('appointment_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->limit(5)
            ->get();

        // Janji temu hari ini
        $todayAppointments = Appointment::where('patient_id', $patientId)
            ->whereDate('appointment_date', Carbon::today())
            ->with(['doctor.user', 'specialty'])
            ->orderBy('start_time', 'asc')
            ->get();

        // Riwayat janji temu terakhir (5 terakhir yang completed)
        $recentCompletedAppointments = Appointment::where('patient_id', $patientId)
            ->where('status', 'completed')
            ->with(['doctor.user', 'specialty', 'feedback'])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->limit(5)
            ->get();

        // Statistik per bulan (6 bulan terakhir)
        $monthlyStats = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyStats[] = [
                'month' => $date->format('M Y'),
                'appointments' => Appointment::where('patient_id', $patientId)
                    ->whereYear('appointment_date', $date->year)
                    ->whereMonth('appointment_date', $date->month)
                    ->count(),
                'completed' => Appointment::where('patient_id', $patientId)
                    ->whereYear('appointment_date', $date->year)
                    ->whereMonth('appointment_date', $date->month)
                    ->where('status', 'completed')
                    ->count(),
            ];
        }

        // Dokter favorit (berdasarkan jumlah appointment)
        $favoriteDoctors = Appointment::where('patient_id', $patientId)
            ->with(['doctor.user', 'doctor.specialty'])
            ->selectRaw('doctor_id, COUNT(*) as appointment_count')
            ->groupBy('doctor_id')
            ->orderBy('appointment_count', 'desc')
            ->limit(3)
            ->get();

        return view('patient.index', compact(
            'totalAppointments',
            'upcomingAppointments', 
            'completedAppointments',
            'cancelledAppointments',
            'nextAppointments',
            'todayAppointments',
            'recentCompletedAppointments',
            'monthlyStats',
            'favoriteDoctors',
            'patient'
        ));
    }
}