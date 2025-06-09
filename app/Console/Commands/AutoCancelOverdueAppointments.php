<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Notifications\AppointmentCancelledNotification;
use Illuminate\Support\Facades\DB;

class AutoCancelOverdueAppointments extends Command
{
    protected $signature = 'appointments:auto-cancel 
                           {--minutes=30 : Minutes after appointment time to auto-cancel}
                           {--dry-run : Show what would be cancelled without actually cancelling}';

    protected $description = 'Automatically cancel overdue appointments that have not been updated';

    // Default values as class constants
    private const DEFAULT_SLOT_DURATION = 30;
    private const MIN_GRACE_PERIOD = 15;
    private const GRACE_PERIOD_MULTIPLIER = 0.5;

    public function handle()
    {
        $graceMinutes = (int) $this->option('minutes');
        $dryRun = $this->option('dry-run');
        
        $now = Carbon::now();
        
        $this->info("=== Auto Cancel Overdue Appointments ===");
        $this->info("Current time: " . $now->format('Y-m-d H:i:s'));
        $this->info("Grace period: {$graceMinutes} minutes");
        $this->info("Dry run mode: " . ($dryRun ? 'YES' : 'NO'));
        $this->line("");
        
        // Query untuk mendapatkan appointment yang perlu dicek
        $potentialOverdueAppointments = $this->getOverdueAppointments($now);

        if ($potentialOverdueAppointments->isEmpty()) {
            $this->info('✓ No potentially overdue appointments found.');
            return 0;
        }

        $this->info("Checking {$potentialOverdueAppointments->count()} potentially overdue appointments:");
        $this->line("");
        
        $stats = $this->processAppointments($potentialOverdueAppointments, $now, $graceMinutes, $dryRun);
        
        $this->displaySummary($stats, $dryRun, $graceMinutes, $now);
        
        return $stats['errors'] > 0 ? 1 : 0;
    }

    /**
     * Get potentially overdue appointments
     */
    private function getOverdueAppointments(Carbon $now)
    {
        return Appointment::with([
            'patient.user', 
            'doctor.user', 
            'doctor.availabilities' => function($query) {
                $query->where('is_available', true);
            }
        ])
        ->whereIn('status', ['pending', 'confirmed', 'scheduled'])
        ->whereRaw("CONCAT(appointment_date, ' ', end_time) < ?", [$now->format('Y-m-d H:i:s')])
        ->get();
    }

    /**
     * Process appointments for cancellation
     */
    private function processAppointments($appointments, Carbon $now, int $graceMinutes, bool $dryRun): array
    {
        $stats = [
            'cancelled' => 0,
            'errors' => 0,
            'skipped' => 0,
            'total' => $appointments->count()
        ];

        foreach ($appointments as $appointment) {
            $result = $this->processSingleAppointment($appointment, $now, $graceMinutes, $dryRun);
            $stats[$result]++;
        }

        return $stats;
    }

    /**
     * Process a single appointment
     */
    private function processSingleAppointment(Appointment $appointment, Carbon $now, int $graceMinutes, bool $dryRun): string
    {
        // Validasi format datetime
        try {
            $appointmentEndTime = Carbon::parse($appointment->appointment_date . ' ' . $appointment->end_time);
        } catch (\Exception $e) {
            $this->error("  ✗ Invalid date/time format for Appointment ID {$appointment->id}");
            $this->error("    Date: {$appointment->appointment_date}, End Time: {$appointment->end_time}");
            Log::error("Invalid date/time format for Appointment ID {$appointment->id}: {$appointment->appointment_date} {$appointment->end_time}");
            return 'errors';
        }

        // Dapatkan durasi slot dokter
        $doctorSlotDuration = $this->getDoctorSlotDuration($appointment);
        
        // Hitung grace period dinamis
        $dynamicGracePeriod = $this->calculateGracePeriod($graceMinutes, $doctorSlotDuration);
        
        // Waktu batas untuk pembatalan
        $cancellationCutoff = $appointmentEndTime->copy()->addMinutes($dynamicGracePeriod);
        
        // Cek apakah appointment benar-benar overdue
        if ($now->lt($cancellationCutoff)) {
            $this->displaySkippedAppointment($appointment, $appointmentEndTime, $dynamicGracePeriod, $doctorSlotDuration, $cancellationCutoff);
            return 'skipped';
        }

        // Appointment overdue, proses pembatalan
        return $this->handleOverdueAppointment($appointment, $appointmentEndTime, $now, $doctorSlotDuration, $dynamicGracePeriod, $dryRun);
    }

    /**
     * Get doctor slot duration with fallback logic
     */
    private function getDoctorSlotDuration(Appointment $appointment): int
    {
        $appointmentDayOfWeek = Carbon::parse($appointment->appointment_date)->format('l');
        
        // Cari availability yang sesuai dengan hari appointment
        $relevantAvailability = $appointment->doctor->availabilities->first(function ($availability) use ($appointmentDayOfWeek) {
            return $availability->day_of_week === $appointmentDayOfWeek && 
                   $availability->is_available && 
                   $availability->slot_duration > 0;
        });
        
        if ($relevantAvailability) {
            return $relevantAvailability->slot_duration;
        }
        
        // Fallback: gunakan slot_duration dari availability manapun
        $fallbackAvailability = $appointment->doctor->availabilities->first(function ($availability) {
            return $availability->slot_duration > 0;
        });
        
        if ($fallbackAvailability) {
            $this->line("⚠️  Using fallback slot duration for Doctor ID {$appointment->doctor_id}");
            return $fallbackAvailability->slot_duration;
        }
        
        // Default jika tidak ada data
        $this->line("⚠️  Using default slot duration for Doctor ID {$appointment->doctor_id}");
        return self::DEFAULT_SLOT_DURATION;
    }

    /**
     * Calculate dynamic grace period
     */
    private function calculateGracePeriod(int $baseGracePeriod, int $slotDuration): int
    {
        $dynamicGracePeriod = max(
            $baseGracePeriod, 
            (int) ($slotDuration * self::GRACE_PERIOD_MULTIPLIER)
        );
        
        return max($dynamicGracePeriod, self::MIN_GRACE_PERIOD);
    }

    /**
     * Display skipped appointment info
     */
    private function displaySkippedAppointment(Appointment $appointment, Carbon $endTime, int $gracePeriod, int $slotDuration, Carbon $cutoff): void
    {
        $this->line("⏳ Appointment ID {$appointment->id} - Still within grace period");
        $this->line("   End time: {$endTime->format('Y-m-d H:i:s')}");
        $this->line("   Grace period: {$gracePeriod} minutes (slot: {$slotDuration} min)");
        $this->line("   Cutoff time: {$cutoff->format('Y-m-d H:i:s')}");
        $this->line("   → Skipped (not overdue yet)");
        $this->line("");
    }

    /**
     * Handle overdue appointment
     */
    private function handleOverdueAppointment(Appointment $appointment, Carbon $endTime, Carbon $now, int $slotDuration, int $gracePeriod, bool $dryRun): string
    {
        $patientName = $appointment->patient->user->name ?? 'Unknown Patient';
        $doctorName = $appointment->doctor->user->name ?? 'Unknown Doctor';
        
        $this->line("🚨 OVERDUE - Appointment ID: {$appointment->id}");
        $this->line("   Patient: {$patientName}");
        $this->line("   Doctor: {$doctorName} (slot: {$slotDuration} min)");
        $this->line("   Scheduled: {$appointment->appointment_date} {$appointment->start_time}-{$appointment->end_time}");
        $this->line("   Status: {$appointment->status}");
        $this->line("   End time: {$endTime->format('Y-m-d H:i:s')}");
        $this->line("   Grace period used: {$gracePeriod} minutes");
        $this->line("   Overdue by: " . $endTime->diffForHumans($now, true));
        
        if (!$dryRun) {
            return $this->cancelAppointment($appointment, $slotDuration, $gracePeriod);
        }
        
        $this->line("   → Would be cancelled (dry-run mode)");
        $this->line("");
        return 'cancelled';
    }

    /**
     * Cancel appointment with transaction
     */
    private function cancelAppointment(Appointment $appointment, int $slotDuration, int $gracePeriod): string
    {
        try {
            DB::transaction(function () use ($appointment, $slotDuration, $gracePeriod) {
                $appointment->update([
                    'status' => 'cancelled',
                    'cancellation_reason' => 'Auto-cancelled: Appointment overdue (system)',
                    'cancelled_at' => Carbon::now(),
                    'cancelled_by' => 'system'
                ]);
                
                // Log untuk audit trail
                Log::info("Auto-cancelled overdue appointment", [
                    'appointment_id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                    'doctor_id' => $appointment->doctor_id,
                    'doctor_slot_duration' => $slotDuration,
                    'grace_period_used' => $gracePeriod,
                    'original_datetime' => $appointment->appointment_date . ' ' . $appointment->start_time . '-' . $appointment->end_time,
                    'cancelled_at' => Carbon::now()->toDateTimeString()
                ]);
            });
            
            // Kirim notifikasi (jika diinginkan)
            // $this->sendCancellationNotifications($appointment);
            
            $this->line("   ✓ Successfully cancelled");
            $this->line("");
            return 'cancelled';
            
        } catch (\Exception $e) {
            $this->error("   ✗ Failed to cancel: " . $e->getMessage());
            Log::error("Failed to auto-cancel appointment {$appointment->id}: " . $e->getMessage(), [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->line("");
            return 'errors';
        }
    }

    /**
     * Display summary of processing results
     */
    private function displaySummary(array $stats, bool $dryRun, int $graceMinutes, Carbon $now): void
    {
        $this->line("=== SUMMARY ===");
        
        if ($dryRun) {
            $this->warn("DRY RUN MODE: No appointments were actually cancelled.");
            $this->info("Total checked: {$stats['total']} appointments");
            $this->info("Would cancel: {$stats['cancelled']} appointments");
            $this->info("Skipped (still in grace): {$stats['skipped']} appointments");
        } else {
            $this->info("Total checked: {$stats['total']} appointments");
            $this->info("Successfully cancelled: {$stats['cancelled']} appointments");
            $this->info("Skipped (still in grace): {$stats['skipped']} appointments");
            if ($stats['errors'] > 0) {
                $this->error("Failed to cancel: {$stats['errors']} appointments");
            }
        }
        
        // Log summary
        Log::info("Auto-cancel appointments completed", [
            'total_checked' => $stats['total'],
            'successfully_cancelled' => $stats['cancelled'],
            'skipped_in_grace' => $stats['skipped'],
            'errors' => $stats['errors'],
            'dry_run' => $dryRun,
            'base_grace_minutes' => $graceMinutes,
            'current_time' => $now->toDateTimeString()
        ]);
    }
    
    /**
     * Kirim notifikasi pembatalan (opsional)
     */
    private function sendCancellationNotifications(Appointment $appointment): void
    {
        try {
            
            $this->line("   📧 Notifications sent");
        } catch (\Exception $e) {
            $this->line("   ⚠️  Failed to send notifications: " . $e->getMessage());
            Log::warning("Failed to send cancellation notifications for appointment {$appointment->id}: " . $e->getMessage());
        }
    }
}