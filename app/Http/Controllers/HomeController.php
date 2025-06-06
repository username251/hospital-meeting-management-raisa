<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Feedback;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
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

        $specialties = Specialty::all();
        
        return view('home.index', compact('topDoctors', 'testimonials', 'specialties'));
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
}