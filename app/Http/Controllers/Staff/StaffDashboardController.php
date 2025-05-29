<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StaffDashboardController extends Controller
{
      public function index()
    {
        // 1. Total Pasien Terdaftar
        $totalPatients = Patient::count();

        // 2. Total Dokter Terdaftar
        $totalDoctors = Doctor::count(); // Jika ada model Doctor

        // 3. Janji Temu Hari Ini (Appointment Today)
        $appointmentsToday = Appointment::whereDate('appointment_date', Carbon::today())->count();
        $appointmentsTodayCompleted = Appointment::whereDate('appointment_date', Carbon::today())
                                                ->where('status', 'completed')
                                                ->count();
        $appointmentsTodayPending = Appointment::whereDate('appointment_date', Carbon::today())
                                                ->where('status', 'pending')
                                                ->count();
        $appointmentsTodayWaiting = Appointment::whereDate('appointment_date', Carbon::today())
                                                ->where('status', 'waiting') // Pastikan status 'waiting' sudah ada di ENUM
                                                ->count();
        // 4. Janji Temu yang Tertunda/Belum Ditangani (Bisa dihitung dari status 'pending' atau 'waiting' secara keseluruhan)
        $pendingAppointments = Appointment::whereIn('status', ['pending', 'waiting', 'check-in'])->count();


        // 5. Anda bisa menambahkan metrik lain seperti:
        //    - Pasien baru minggu ini: Patient::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        //    - Janji temu yang dibatalkan: Appointment::where('status', 'cancelled')->count();
        //    - Antrean saat ini (Appointment dengan status 'waiting' atau 'in-consultation' pada hari ini)
        $currentQueue = Appointment::whereDate('appointment_date', Carbon::today())
                                    ->whereIn('status', ['waiting', 'in-consultation'])
                                    ->count();


        return view('staff.index', compact(
            'totalPatients',
            'totalDoctors',
            'appointmentsToday',
            'appointmentsTodayCompleted',
            'appointmentsTodayPending',
            'appointmentsTodayWaiting',
            'pendingAppointments',
            'currentQueue'
        ));
    }
}