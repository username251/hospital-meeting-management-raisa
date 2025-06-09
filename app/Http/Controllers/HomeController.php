<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Feedback;
use App\Models\Specialty;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // $user = Auth::user();
        // if ($user->role === 'admin')
        // {
        //     return redirect()->route('admin.index');    
        // }elseif ($user->role === 'doctor')
        // {
        //     return redirect()->route('doctor.dashboard');
        // }elseif ($user->role === 'patient')
        // {
        //     return redirect()->route('patient.inde');
        // }elseif ($user->role === 'staff')
        // {
        //     return redirect()->route('staff.index');
        // }
        // Perbaikan query untuk mendapatkan top doctors dengan feedback
        $topDoctors = Doctor::with(['user', 'specialty', 'feedback'])
            ->withCount('feedback')
            ->withAvg('feedback', 'rating')
            ->having('feedback_count', '>', 0) // Hanya ambil doctor yang punya feedback
            ->orderByDesc('feedback_avg_rating')
            ->orderByDesc('feedback_count')
            ->limit(4)
            ->get();

        // Jika tidak ada doctor dengan feedback, ambil doctor biasa
        if ($topDoctors->isEmpty()) {
            $topDoctors = Doctor::with(['user', 'specialty'])
                ->withCount('feedback')
                ->withAvg('feedback', 'rating')
                ->limit(4)
                ->get();
        }

        $testimonials = Feedback::with(['patient.user', 'doctor.user'])
            ->where('rating', '>=', 4)
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->latest()
            ->take(6)
            ->get();

        $specialties = Specialty::orderBy('name')->take(6)->get();
        // Data statistik sederhana
        $totalPatients = Patient::count();
        $totalDoctors = Doctor::count();
        $consultedPatients = Patient::whereHas('appointments', function($query) {
            $query->where('status', 'completed');
        })->count();
        
        return view('home.index', compact(
            'topDoctors', 
            'testimonials', 
            'specialties',
            'totalPatients',
            'totalDoctors',
            'consultedPatients'
        ));
    }

    public function doctors(Request $request)
    {
        $query = Doctor::with(['user', 'specialty'])
            ->withCount('feedback')
            ->withAvg('feedback', 'rating');

        if ($request->has('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $doctors = $query->get();

        return view('doctors.index', compact('doctors'));
    }

    // Method untuk menampilkan detail pasien (opsional)
    public function patientStats()
    {
        $totalPatients = Patient::count();
        $activePatients = Patient::whereHas('appointments', function($query) {
            $query->where('created_at', '>=', now()->subMonths(6));
        })->count();

        $patientsBySpecialty = Patient::select('specialties.name', DB::raw('COUNT(patients.id) as total'))
            ->join('appointments', 'patients.id', '=', 'appointments.patient_id')
            ->join('doctors', 'appointments.doctor_id', '=', 'doctors.id')
            ->join('specialties', 'doctors.specialty_id', '=', 'specialties.id')
            ->where('appointments.status', 'completed')
            ->groupBy('specialties.id', 'specialties.name')
            ->orderByDesc('total')
            ->get();

        return response()->json([
            'totalPatients' => $totalPatients,
            'activePatients' => $activePatients,
            'patientsBySpecialty' => $patientsBySpecialty
        ]);
    }
}