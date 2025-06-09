<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\AutoCancelOverdueAppointments;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal auto-cancel appointments
// Jalankan setiap 5 menit dengan grace period 30 menit
Schedule::command(AutoCancelOverdueAppointments::class, ['--minutes=30'])
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/auto-cancel-appointments.log'))
    ->emailOutputOnFailure(['admin@example.com']) // Ganti dengan email admin
    ->onSuccess(function () {
        \Log::info('Auto-cancel appointments scheduled task completed successfully');
    })
    ->onFailure(function () {
        \Log::error('Auto-cancel appointments scheduled task failed');
    });

// Alternatif: Jalankan setiap 10 menit (lebih konservatif)
// Schedule::command(AutoCancelOverdueAppointments::class, ['--minutes=30'])
//     ->everyTenMinutes()
//     ->withoutOverlapping()
//     ->runInBackground()
//     ->appendOutputTo(storage_path('logs/auto-cancel-appointments.log'));

// Untuk testing: Jalankan setiap menit dengan dry-run
// Schedule::command(AutoCancelOverdueAppointments::class, ['--minutes=30', '--dry-run'])
//     ->everyMinute()
//     ->withoutOverlapping()
//     ->appendOutputTo(storage_path('logs/auto-cancel-dry-run.log'));