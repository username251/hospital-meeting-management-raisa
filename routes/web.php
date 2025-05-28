<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\DoctorManagementController;
use App\Http\Controllers\Admin\DoctorScheduleController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\SpecialtyController;
use App\Http\Controllers\DoctorDashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PatientDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\StaffDashboardController;
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
                return redirect()->route('admin.index');
            case 'doctor':
                return redirect()->route('doctor.dashboard');
            case 'staff':
                return redirect()->route('staff.index');
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
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.index');
        Route::get('/form', [AdminDashboardController::class, 'form'])->name( 'admin.form');
        Route::get('/table', [AdminDashboardController::class, 'table'])->name('admin.table');

        //doctor managment
        Route::get('/doctors/create', [DoctorManagementController::class, 'create'])->name('admin.doctors.create');
        Route::post('/doctors', [DoctorManagementController::class, 'store'])->name('admin.doctors.store');
        Route::get('/doctors/read', [DoctorManagementController::class, 'read'])->name('admin.doctors.read'); // Tetap seperti ini
        Route::get('/doctors/edit/{id}', [DoctorManagementController::class, 'edit'])->name('admin.doctors.edit'); // Perhatikan {id}
        Route::post('/doctors/update/{id}', [DoctorManagementController::class, 'update'])->name('admin.doctors.update'); // Perhatikan {id}
        Route::delete('/doctors/delete/{id}', [DoctorManagementController::class, 'destroy'])->name('admin.doctors.destroy'); // Menambahkan rute delete
         
        
        //speciality management
        Route::get('/specialties', [SpecialtyController::class, 'index'])->name('admin.specialties.index');
        Route::get('/specialties/create', [SpecialtyController::class, 'create'])->name('admin.specialties.create');
        Route::post('/specialties', [SpecialtyController::class, 'store'])->name('admin.specialties.store');
        Route::get('/specialties/edit/{id}', [SpecialtyController::class, 'edit'])->name('admin.specialties.edit');
        Route::put('/specialties/update/{id}', [SpecialtyController::class, 'update'])->name('admin.specialties.update');
        Route::delete('/specialties/delete/{id}', [SpecialtyController::class, 'destroy'])->name('admin.specialties.destroy');
       
        
        //patient management
        Route::get('/patients', [PatientController::class, 'index'])->name('admin.patients.index');
        Route::get('/patients/create', [PatientController::class, 'create'])->name('admin.patients.create');
        Route::post('/patients', [PatientController::class, 'store'])->name('admin.patients.store');
        Route::get('/patients/edit/{id}', [PatientController::class, 'edit'])->name('admin.patients.edit');
        Route::post('/patients/update/{id}', [PatientController::class, 'update'])->name('admin.patients.update');
        Route::post('/patients/delete/{id}', [PatientController::class, 'destroy'])->name('admin.patients.destroy');


        //appointment management
        Route::get('/appointments', [AppointmentController::class, 'index'])->name('admin.appointments.index');
        Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('admin.appointments.create');
        Route::post('/appointments', [AppointmentController::class, 'store'])->name('admin.appointments.store');
        Route::get('/appointments/show/{appointment}', [AppointmentController::class, 'show'])->name('admin.appointments.show'); // Gunakan {appointment} karena kita passing model
        Route::get('/appointments/edit/{appointment}', [AppointmentController::class, 'edit'])->name('admin.appointments.edit'); // Gunakan {appointment}
        Route::post('/appointments/update/{appointment}', [AppointmentController::class, 'update'])->name('admin.appointments.update'); // Gunakan {appointment}
        Route::post('/appointments/delete/{appointment}', [AppointmentController::class, 'destroy'])->name('admin.appointments.destroy'); // Gunakan {appointment}


        //management doctor schedule
        Route::get('/doctor-schedules', [DoctorScheduleController::class, 'index'])->name('admin.doctor_schedules.index');
        Route::get('/doctor-schedules/create', [DoctorScheduleController::class, 'create'])->name('admin.doctor_schedules.create');
        Route::post('/doctor-schedules', [DoctorScheduleController::class, 'store'])->name('admin.doctor_schedules.store');
        // Route::get('/doctor-schedules/{doctorSchedule}', [DoctorScheduleController::class, 'show'])->name('admin.doctor_schedules.show'); // Opsional
        Route::get('/doctor-schedules/{doctorSchedule}/edit', [DoctorScheduleController::class, 'edit'])->name('admin.doctor_schedules.edit');
        Route::put('/doctor-schedules/{doctorSchedule}', [DoctorScheduleController::class, 'update'])->name('admin.doctor_schedules.update');
        Route::delete('/doctor-schedules/{doctorSchedule}', [DoctorScheduleController::class, 'destroy'])->name('admin.doctor_schedules.destroy');

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
        Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('staff.index');
            

        //Appointment Summary Route
        Route::get('/appointments', [\App\Http\Controllers\Staff\AppointmentController::class, 'index'])->name('staff.appointments.index');
        Route::get('/appointments/create', [\App\Http\Controllers\Staff\AppointmentController::class, 'create'])->name('staff.appointments.create');
        Route::post('/appointments', [\App\Http\Controllers\Staff\AppointmentController::class, 'store'])->name('staff.appointments.store');
        Route::get('/appointments/{appointment}', [\App\Http\Controllers\Staff\AppointmentController::class, 'show'])->name('staff.appointments.show'); // Opsional: untuk detail
        Route::get('/appointments/{appointment}/edit', [\App\Http\Controllers\Staff\AppointmentController::class, 'edit'])->name('staff.appointments.edit');
        Route::put('/appointments/{appointment}', [\App\Http\Controllers\Staff\AppointmentController::class, 'update'])->name('staff.appointments.update'); // Gunakan PUT untuk update
        Route::delete('/appointments/{appointment}', [\App\Http\Controllers\Staff\AppointmentController::class, 'destroy'])->name('staff.appointments.destroy'); // Gunakan DELETE untuk destroy

        // Rute khusus untuk update status janji temu
        Route::patch('/appointments/{appointment}/status', [\App\Http\Controllers\Staff\AppointmentController::class, 'updateStatus'])->name('staff.appointments.updateStatus');
    });

    // --- Rute untuk Pasien ---
    // Prefix 'patient' + path '/dashboard' -> URL: /patient/dashboard
    Route::group(['prefix' => 'patient', 'middleware' => 'check.role:patient'], function () {
        Route::get('/dashboard', [PatientDashboardController::class, 'index'])->name('patient.dashboard');
        Route::get('/dashboard/appointment', [PatientDashboardController::class, 'show'])->name('appointment');
        
    });
});

require __DIR__.'/auth.php'; // Rute otentikasi Breeze