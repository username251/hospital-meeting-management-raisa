<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\DoctorManagementController;
use App\Http\Controllers\Admin\DoctorScheduleController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\SpecialtyController;
use App\Http\Controllers\Doctor\DashboardController;
use App\Http\Controllers\DoctorDashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Patient\FeedbackController;
use App\Http\Controllers\Patient\PatientAppointmentController;
use App\Http\Controllers\Patient\PatientProfileController;
use App\Http\Controllers\PatientDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\DoctorAvailabilityController;
use App\Http\Controllers\Staff\DoctorController;
use App\Http\Controllers\Staff\QueueController;
use App\Http\Controllers\Staff\StaffDashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // Tambahkan ini

// Route Home, bisa diakses tanpa login
Route::get('/', [HomeController::class, 'index'])->name('home.dashboard');

// Ini adalah rute yang akan menjadi target RouteServiceProvider::HOME atau pengalihan default setelah login.
Route::get('/dashboard', function () {
    $user = Auth::user();

    if (!$user) {
        // Jika somehow tidak ada user (meskipun harusnya sudah di-middleware 'auth'), redirect ke login
        return redirect()->route('login');
    }

    // Logika Pengalihan Berdasarkan Role dan Kelengkapan Profil Pasien
    if ($user->role === 'patient') {
        // Jika user adalah pasien, periksa apakah profil pasien sudah lengkap
        if (!$user->patient) { // Asumsi relasi 'patient' di model User sudah ada
            // Jika belum ada profil pasien, arahkan untuk membuat profil
            return redirect()->route('patient.profile.create');
        } else {
            // Jika sudah ada profil pasien, arahkan ke dashboard pasien
            return redirect()->route('patient.index');
        }
    }
    // Tambahkan logika pengalihan untuk role lain:
    elseif ($user->role === 'admin') {
        return redirect()->route('admin.index'); // Ganti dengan rute dashboard admin Anda
    }
    elseif ($user->role === 'staff') {
        return redirect()->route('staff.index'); // Ganti dengan rute dashboard staff Anda
    }
    elseif ($user->role === 'doctor') {
        return redirect()->route('doctor.dashboard'); // Ganti dengan rute dashboard doctor Anda
    }

        // Fallback jika role tidak ditemukan atau tidak ada rute spesifik
        return view('home.dashboard'); // Ini bisa jadi dashboard umum atau halaman error/info
    })->middleware(['auth'])->name('dashboard'); // Pastikan rute ini dilindungi oleh middleware 'auth'


// --- Rute Spesifik untuk Role Pasien ---
// Rute Dashboard Pasien (akses setelah profil lengkap)
Route::get('/patient/dashboard', function () {
    return view('patient.dashboard'); // Pastikan Anda memiliki view ini
})->name('patient.dashboard')->middleware(['auth', 'check.role:patient']);

    // Rute Profile (bawaan Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- Rute untuk Admin ---
    // Dilindungi oleh 'auth' (karena di dalam group middleware 'auth') DAN 'check.role:admin'
    Route::group(['prefix' => 'admin', 'middleware' => 'check.role:admin'], function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.index');
        
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

        Route::get('doctor_schedules', [App\Http\Controllers\Admin\DoctorScheduleController::class, 'index'])->name('doctor_schedules.index');
        Route::get('doctor_schedules/create', [App\Http\Controllers\Admin\DoctorScheduleController::class, 'create'])->name('doctor_schedules.create');
        Route::post('doctor_schedules', [App\Http\Controllers\Admin\DoctorScheduleController::class, 'store'])->name('doctor_schedules.store');
        Route::get('doctor_schedules/{doctorSchedule}/edit', [App\Http\Controllers\Admin\DoctorScheduleController::class, 'edit'])->name('doctor_schedules.edit');
        Route::put('doctor_schedules/{doctorSchedule}', [App\Http\Controllers\Admin\DoctorScheduleController::class, 'update'])->name('doctor_schedules.update');
        Route::delete('doctor_schedules/{doctorSchedule}', [App\Http\Controllers\Admin\DoctorScheduleController::class, 'destroy'])->name('doctor_schedules.destroy');
        Route::patch('doctor_schedules/{doctorSchedule}/toggle-availability', [
            App\Http\Controllers\Admin\DoctorScheduleController::class, 
            'toggleAvailability'
        ])->name('doctor_schedules.toggle_availability');
        
        Route::get('doctor_schedules/calendar/view', [
            App\Http\Controllers\Admin\DoctorScheduleController::class, 
            'calendar'
        ])->name('doctor_schedules.calendar');
    });

    // --- Rute untuk Dokter ---
    // Prefix 'doctor' + path '/dashboard' -> URL: /doctor/dashboard
    Route::group(['prefix' => 'doctor', 'middleware' => 'check.role:doctor'], function () {
         Route::get('/dashboard', [DashboardController::class, 'index'])->name('doctor.dashboard');
    
        // Today's Appointments
        Route::get('/appointments/today', [DashboardController::class, 'todayAppointments'])->name('appointments.today');
        
        // Update Appointment Status
        Route::patch('/appointments/{appointment}/status', [DashboardController::class, 'updateAppointmentStatus'])->name('appointments.update-status');
        
        // AJAX Endpoints for real-time updates
        Route::get('/api/upcoming-appointments', [DashboardController::class, 'getUpcomingAppointments'])->name('api.upcoming-appointments');
        Route::get('/api/dashboard-stats', [DashboardController::class, 'getDashboardStats'])->name('api.dashboard-stats');
    


        // Rute untuk Profil Dokter
        Route::get('/profile', [App\Http\Controllers\Doctor\ProfileController::class, 'edit'])->name('doctor.profile.edit');
        Route::patch('/profile', [App\Http\Controllers\Doctor\ProfileController::class, 'update'])->name('doctor.profile.update');
        
        // Rute untuk Ringkasan Janji Temu Hari Ini di dashboard dokter
        Route::get('/appointments/today', [DashboardController::class, 'todayAppointments'])->name('doctor.appointments.today');
        Route::patch('/appointments/{appointment}/status', [DashboardController::class, 'updateAppointmentStatus'])->name('doctor.appointments.updateStatus');

        // Rute untuk Manajemen Jadwal Ketersediaan Dokter (Doctor Availability)
        Route::get('/availabilities', [\App\Http\Controllers\Doctor\DoctorAvailabilityController::class, 'index'])->name('doctor.availability.index');
        Route::get('/availabilities/create', [\App\Http\Controllers\Doctor\DoctorAvailabilityController::class, 'create'])->name('doctor.availability.create');
        Route::post('/availabilities', [\App\Http\Controllers\Doctor\DoctorAvailabilityController::class, 'store'])->name('doctor.availability.store');
        Route::get('/availabilities/{doctorAvailability}/edit', [\App\Http\Controllers\Doctor\DoctorAvailabilityController::class, 'edit'])->name('doctor.availability.edit');
        Route::put('/availabilities/{doctorAvailability}', [\App\Http\Controllers\Doctor\DoctorAvailabilityController::class, 'update'])->name('doctor.availability.update');
        Route::delete('/availabilities/{doctorAvailability}', [\App\Http\Controllers\Doctor\DoctorAvailabilityController::class, 'destroy'])->name('doctor.availability.destroy');
        Route::patch('/availabilities/{doctorAvailability}/toggle', [\App\Http\Controllers\Doctor\DoctorAvailabilityController::class, 'toggleAvailability'])->name('doctor.availability.toggle');

        // Rute untuk Manajemen Antrean Pasien
        Route::get('/queue', [\App\Http\Controllers\Doctor\QueueController::class, 'index'])->name('doctor.queue.index');
        Route::patch('/queue/{appointment}/status', [\App\Http\Controllers\Doctor\QueueController::class, 'updateStatus'])->name('doctor.queue.updateStatus');
        Route::post('/queue/{appointment}/call', [\App\Http\Controllers\Doctor\QueueController::class, 'callPatient'])->name('doctor.queue.callPatient');
        Route::get('/queue/search', [\App\Http\Controllers\Doctor\QueueController::class, 'search'])->name('doctor.queue.search'); // Rute untuk pencarian

        //Appointment Summary Route
        Route::get('/appointments', [\App\Http\Controllers\Doctor\AppointmentController::class, 'index'])->name('doctor.appointments.index');
        Route::get('/appointments/create', [\App\Http\Controllers\Doctor\AppointmentController::class, 'create'])->name('doctor.appointments.create');
        Route::post('/appointments', [\App\Http\Controllers\Doctor\AppointmentController::class, 'store'])->name('doctor.appointments.store');
        Route::get('/appointments/{appointment}', [\App\Http\Controllers\Doctor\AppointmentController::class, 'show'])->name('doctor.appointments.show'); // Opsional: untuk detail
        Route::get('/appointments/{appointment}/edit', [\App\Http\Controllers\Doctor\AppointmentController::class, 'edit'])->name('doctor.appointments.edit');
        Route::put('/appointments/{appointment}', [\App\Http\Controllers\Doctor\AppointmentController::class, 'update'])->name('doctor.appointments.update'); // Gunakan PUT untuk update
        Route::delete('/appointments/{appointment}', [\App\Http\Controllers\Doctor\AppointmentController::class, 'destroy'])->name('doctor.appointments.destroy'); // Gunakan DELETE untuk destroy
        // Rute khusus untuk update status janji temu
        Route::patch('/appointments/{appointment}/status', [\App\Http\Controllers\Staff\AppointmentController::class, 'updateStatus'])->name('doctor.appointments.updateStatus');

        
    });

    // --- Rute untuk Staf ---
    // Prefix 'staff' + path '/dashboard' -> URL: /staff/dashboard
    Route::group(['prefix' => 'staff', 'middleware' => 'check.role:staff'], function () {
        Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('staff.index');
            
        Route::get('/appointments/get-available-slots', [\App\Http\Controllers\Staff\AppointmentController::class, 'getAvailableSlots'])->name('staff.appointments.getAvailableSlots');

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


        //Route untuk antrean 
        Route::get('/queue', [QueueController::class, 'index'])->name('staff.queue.index');
        Route::patch('/queue/{appointment}/status', [QueueController::class, 'updateStatus'])->name('staff.queue.updateStatus');
        Route::post('/queue/{appointment}/call', [QueueController::class, 'callPatient'])->name('staff.queue.callPatient');
        Route::get('/queue/search', [QueueController::class, 'search'])->name('staff.queue.search'); // Rute untuk pencarian
        
        // Menampilkan daftar semua ketersediaan dokter yang bisa dikelola staff
        Route::get('/staff/doctor-availabilities', [DoctorAvailabilityController::class, 'index'])->name('staff.doctor_availabilities.index');
        Route::get('/staff/doctor-availabilities/create', [DoctorAvailabilityController::class, 'create'])->name('staff.doctor_availabilities.create');
        Route::post('/staff/doctor-availabilities', [DoctorAvailabilityController::class, 'store'])->name('staff.doctor_availabilities.store');
        Route::get('/staff/doctor-availabilities/{doctorAvailability}/edit', [DoctorAvailabilityController::class, 'edit'])->name('staff.doctor_availabilities.edit');
        Route::put('/staff/doctor-availabilities/{doctorAvailability}', [DoctorAvailabilityController::class, 'update'])->name('staff.doctor_availabilities.update');
        Route::delete('/staff/doctor-availabilities/{doctorAvailability}', [DoctorAvailabilityController::class, 'destroy'])->name('staff.doctor_availabilities.destroy');
        Route::patch('/staff/doctor-availabilities/{doctorAvailability}/toggle', [DoctorAvailabilityController::class, 'toggleAvailability'])->name('staff.doctor_availabilities.toggle');


        //Route Untuk Manajemen Dokter
        Route::get('/doctors', [DoctorController::class, 'index'])->name('doctors.index');
        Route::get('/doctors/create', [DoctorController::class, 'create'])->name('doctors.create');
        Route::post('/doctors', [DoctorController::class, 'store'])->name('doctors.store');
        Route::get('/doctors/{doctor}/show', [DoctorController::class, 'show'])->name('doctors.show'); // Tambahkan show jika diperlukan
        Route::get('/doctors/{doctor}/edit', [DoctorController::class, 'edit'])->name('doctors.edit');
        Route::put('/doctors/{doctor}', [DoctorController::class, 'update'])->name('doctors.update');
        Route::delete('/doctors/{doctor}', [DoctorController::class, 'destroy'])->name('doctors.destroy');


        //Route untuk pasien
        Route::resource('patients', \App\Http\Controllers\Staff\PatientController::class)->names([
            'index' => 'staff.patients.index',
            'create' => 'staff.patients.create',
            'store' => 'staff.patients.store',
            'show' => 'staff.patients.show',
            'edit' => 'staff.patients.edit',
            'update' => 'staff.patients.update',
            'destroy' => 'staff.patients.destroy',
        ]);
        
    });


    // Rute untuk mengambil slot janji temu yang tersedia oleh pasien
    // Diletakkan di sini agar URL-nya /api/available-slots
    // Pastikan middleware 'auth' dan 'check.role:patient' sesuai dengan kebutuhan Anda
    Route::get('/api/available-slots', [PatientAppointmentController::class, 'getAvailableSlots'])
        ->middleware(['auth', 'check.role:patient']) // Sesuaikan middleware jika diperlukan
        ->name('api.patient.available-slots');

  // --- Rute untuk Pasien (yang sudah Anda berikan sebelumnya) ---
Route::group(['prefix' => 'patient', 'middleware' => ['auth', 'check.role:patient']], function () {
    Route::get('/dashboard', [App\Http\Controllers\Patient\DashboardController::class, 'index'])->name('patient.index'); // Sebelumnya name: patient.index

    // Menggunakan PatientAppointmentController untuk konsistensi pada fitur appointment pasien
    Route::get('/appointments', [PatientAppointmentController::class, 'index'])->name('appointments.index'); // Mengubah nama rute agar lebih deskriptif
    Route::get('/appointments/create', [PatientAppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [PatientAppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/appointments/{appointment}', [PatientAppointmentController::class, 'show'])->name('patient.appointments.show');
    Route::patch('/appointments/{appointment}/cancel', [PatientAppointmentController::class, 'cancel'])->name('patient.appointments.cancel');

    

    //Route untuk membuat profil pasien
     Route::get('/patient/profile/create', [PatientProfileController::class, 'create'])
         ->name('patient.profile.create');
    Route::post('/patient/profile', [PatientProfileController::class, 'store'])
         ->name('patient.profile.store');
    // Route untuk edit profil pasien (jika sudah ada tapi belum lengkap)
    Route::get('/patient/profile/edit', [PatientProfileController::class, 'edit'])
         ->name('patient.profile.edit');
    Route::put('/patient/profile', [PatientProfileController::class, 'update'])
         ->name('patient.profile.update');
    // Rute get-available-slots yang lama bisa dihapus jika sudah digantikan oleh /api/available-slots
    // Route::get('/appointments/get-available-slots', [PatientAppointmentController::class, 'getAvailableSlots'])->name('appointments.get-available-slots');

        //route untuk feedback
         Route::get('/appointments/{appointment}/feedback/create', [FeedbackController::class, 'create'])->name('feedback.create');
    Route::post('/appointments/{appointment}/feedback', [FeedbackController::class, 'store'])->name('feedback.store');


    });
require __DIR__.'/auth.php'; // Rute otentikasi Breeze