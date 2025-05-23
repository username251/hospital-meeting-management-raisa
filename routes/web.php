<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\DoctorDashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PatientDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffDashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // Tambahkan ini

// Route Home, bisa diakses tanpa login
Route::get('/', [HomeController::class, 'index'])->name('home.dashboard');

// Semua rute yang membutuhkan otentikasi (login)
Route::middleware('auth')->group(function () {

    // --- Rute Pengarah Dashboard Utama ---
    // Route ini akan menangkap semua redirect()->intended(route('dashboard')) dari Breeze
    // dan mengarahkan user ke dashboard spesifik role mereka setelah login.
    Route::get('/dashboard', function () {
        $user = Auth::user();
        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'doctor':
                return redirect()->route('doctor.dashboard');
            case 'staff':
                return redirect()->route('staff.dashboard');
            case 'patient':
            default: // Jika ada role lain atau sebagai fallback
                return redirect()->route('patient.dashboard');
        }
    })->name('dashboard'); // <<--- Pastikan nama route 'dashboard' ini ada!

    // Rute Profile (bawaan Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- Rute untuk Admin ---
    // Dilindungi oleh 'auth' (karena di dalam group middleware 'auth') DAN 'check.role:admin'
    Route::group(['prefix' => 'admin', 'middleware' => 'check.role:admin'], function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        // ... rute admin lainnya
    });

    // --- Rute untuk Dokter ---
    // Prefix 'doctor' + path '/dashboard' -> URL: /doctor/dashboard
    Route::group(['prefix' => 'doctor', 'middleware' => 'check.role:doctor'], function () {
        Route::get('/dashboard', [DoctorDashboardController::class, 'index'])->name('doctor.dashboard');
        // ... rute dokter lainnya
    });

    // --- Rute untuk Staf ---
    // Prefix 'staff' + path '/dashboard' -> URL: /staff/dashboard
    Route::group(['prefix' => 'staff', 'middleware' => 'check.role:staff'], function () {
        Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('staff.dashboard');
        // ... rute staf lainnya
    });

    // --- Rute untuk Pasien ---
    // Prefix 'patient' + path '/dashboard' -> URL: /patient/dashboard
    Route::group(['prefix' => 'patient', 'middleware' => 'check.role:patient'], function () {
        Route::get('/dashboard', [PatientDashboardController::class, 'index'])->name('patient.dashboard');
        Route::get('/dashboard/appointment', [PatientDashboardController::class, 'show'])->name('appointment');
        
    });
});

require __DIR__.'/auth.php'; // Rute otentikasi Breeze