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
    // --- DATA UNTUK STATISTIK BOX ---

    // 1. Total Pasien Terdaftar
    $totalPatients = Patient::count();

    // 2. Total Dokter Terdaftar
    $totalDoctors = Doctor::count();

    // 3. Janji Temu Hari Ini (Total)
    $appointmentsToday = Appointment::whereDate('appointment_date', Carbon::today())->count();
    
    // 4. Janji Temu Selesai Hari Ini
    $appointmentsTodayCompleted = Appointment::whereDate('appointment_date', Carbon::today())
                                            ->where('status', 'completed')
                                            ->count();
                                            
    // 5. Janji Temu yang masih berstatus 'pending' (belum check-in) untuk hari ini
    $appointmentsTodayPending = Appointment::whereDate('appointment_date', Carbon::today())
                                            ->where('status', 'pending')
                                            ->count();

    // 6. Pasien dalam antrean saat ini (sudah check-in dan menunggu atau sedang konsultasi)
    $currentQueue = Appointment::whereDate('appointment_date', Carbon::today())
                                ->whereIn('status', ['waiting', 'in-consultation', 'check-in']) // 'check-in' juga termasuk antrean
                                ->count();
    
    // 7. Total janji temu yang menunggu konfirmasi (overall, bukan hanya hari ini)
    $totalPendingAppointments = Appointment::whereIn('status', ['pending', 'waiting', 'check-in'])->count();


    // --- DATA BARU UNTUK TABEL DI DASHBOARD ---

    // 8. Ambil 5 janji temu berikutnya hari ini yang belum selesai
    $upcomingAppointments = Appointment::with(['patient', 'doctor'])
                                    ->whereDate('appointment_date', Carbon::today())
                                    ->whereIn('status', ['pending', 'waiting', 'check-in', 'in-consultation'])
                                    // DIUBAH: 'appointment_time' menjadi 'start_time' agar sesuai dengan skema database Anda
                                    ->orderBy('start_time', 'asc') 
                                    ->limit(5)
                                    ->get();


    return view('staff.index', compact(
        'totalPatients',
        'totalDoctors',
        'appointmentsToday',
        'appointmentsTodayCompleted',
        'appointmentsTodayPending',
        'currentQueue',
        'totalPendingAppointments',
        'upcomingAppointments'
    ));
}
}