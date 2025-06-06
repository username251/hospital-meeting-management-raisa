<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Specialty;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Total counts
        $totalPatients = Patient::count();
        $totalDoctors = Doctor::count();
        $totalAppointments = Appointment::count();
        $totalSpecialties = Specialty::count();
        
        // Today's statistics
        $todayAppointments = Appointment::whereDate('appointment_date', Carbon::today())->count();
        $pendingAppointments = Appointment::where('status', 'pending')->count();
        $completedAppointments = Appointment::where('status', 'completed')->count();
        
        // Monthly statistics
        $monthlyAppointments = Appointment::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        
        $monthlyNewPatients = Patient::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        
        // Recent appointments
        $recentAppointments = Appointment::with(['patient.user', 'doctor.user', 'specialty'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Appointment status distribution
        $appointmentsByStatus = Appointment::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');
        
        // Doctor ratings
        $doctorRatings = Doctor::with(['user'])
            ->leftJoin('feedback', 'doctors.id', '=', 'feedback.doctor_id')
            ->select('doctors.*', DB::raw('AVG(feedback.rating) as avg_rating'), DB::raw('COUNT(feedback.id) as total_reviews'))
            ->groupBy('doctors.id')
            ->orderBy('avg_rating', 'desc')
            ->limit(5)
            ->get();
        
        // Monthly appointment trend (last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = Appointment::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
            $monthlyTrend[] = [
                'month' => $date->format('M Y'),
                'count' => $count
            ];
        }
        
        // Recent feedback
        $recentFeedback = Feedback::with(['patient.user', 'doctor.user', 'appointment'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('admin.index', compact(
            'totalPatients',
            'totalDoctors', 
            'totalAppointments',
            'totalSpecialties',
            'todayAppointments',
            'pendingAppointments',
            'completedAppointments',
            'monthlyAppointments',
            'monthlyNewPatients',
            'recentAppointments',
            'appointmentsByStatus',
            'doctorRatings',
            'monthlyTrend',
            'recentFeedback'
        ));
    }
}