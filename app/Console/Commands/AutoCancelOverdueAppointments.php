<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Notifications\AppointmentCancelledNotification;

class AutoCancelOverdueAppointments extends Command
{
    protected $signature = 'appointments:auto-cancel 
                           {--minutes=30 : Minutes after appointment time to auto-cancel}
                           {--dry-run : Show what would be cancelled without actually cancelling}';

    protected $description = 'Automatically cancel overdue appointments that have not been updated';

    public function handle()
    {
        $graceMinutes = $this->option('minutes');
        $dryRun = $this->option('dry-run');
        
        $cutoffTime = Carbon::now()->subMinutes($graceMinutes);
        
        $this->info("Checking for overdue appointments...");
        $this->info("Cutoff time: {$cutoffTime->format('Y-m-d H:i:s')}");
        $this->info("Grace period: {$graceMinutes} minutes");
        
        $overdueAppointments = Appointment::with(['patient.user', 'doctor.user'])
            ->whereIn('status', ['pending', 'confirmed', 'scheduled'])
            ->where('appointment_date', '<=', $cutoffTime->toDateString())
            ->whereRaw("TIME(end_time) < ?", [$cutoffTime->toTimeString()])
            ->get();

        if ($overdueAppointments->isEmpty()) {
            $this->info('No overdue appointments found.');
            return 0;
        }

        $this->info("Found {$overdueAppointments->count()} overdue appointments:");
        
        $cancelledCount = 0;
        
        foreach ($overdueAppointments as $appointment) {
            $appointmentEndTime = Carbon::parse($appointment->appointment_date . ' ' . $appointment->end_time);
            if (!$appointmentEndTime->isValid()) {
                $this->error("  ✗ Invalid date/time format for Appointment ID {$appointment->id}: {$appointment->appointment_date} {$appointment->end_time}");
                Log::error("Invalid date/time format for Appointment ID {$appointment->id}: {$appointment->appointment_date} {$appointment->end_time}");
                continue;
            }

            $patientName = $appointment->patient->user->name ?? 'Unknown';
            $doctorName = $appointment->doctor->user->name ?? 'Unknown';
            
            $this->line("- ID: {$appointment->id} | Patient: {$patientName} | Doctor: {$doctorName}");
            $this->line("  Date: {$appointment->appointment_date} | Time: {$appointment->start_time}-{$appointment->end_time}");
            $this->line("  Status: {$appointment->status} | End time: {$appointmentEndTime->format('Y-m-d H:i:s')}");
            
            if (!$dryRun) {
                try {
                    $appointment->update([
                        'status' => 'cancelled',
                        'cancellation_reason' => 'Auto-cancelled: Appointment overdue',
                        'cancelled_at' => Carbon::now(),
                        'cancelled_by' => 'system'
                    ]);
                    
                   
                    
                    $cancelledCount++;
                    $this->line("  ✓ Cancelled");
                    Log::info("Auto-cancelled overdue appointment", ['appointment_id' => $appointment->id]);
                } catch (\Exception $e) {
                    $this->error("  ✗ Failed to cancel: " . $e->getMessage());
                    Log::error("Failed to auto-cancel appointment {$appointment->id}: " . $e->getMessage());
                }
            } else {
                $this->line("  → Would be cancelled (dry-run mode)");
            }
            $this->line("");
        }
        
        if ($dryRun) {
            $this->warn("DRY RUN MODE: No appointments were actually cancelled.");
        } else {
            $this->info("Successfully cancelled {$cancelledCount} out of {$overdueAppointments->count()} overdue appointments.");
        }
        
        return 0;
    }
}