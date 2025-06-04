<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Support\Facades\Auth;
use Request;

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

        // Atau jika Anda ingin mengecek kelengkapan profil yang lebih detail
        $user = Auth::user();
        if (!$user || !$user->patient) 
        {
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
            return view('patient.index');
        }
}