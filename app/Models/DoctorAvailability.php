<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DoctorAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_duration',
        'is_available',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'is_available' => 'boolean',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get available time slots for a doctor on a specific date
     * dengan filtering waktu yang sudah lewat
     */
    public static function getAvailableSlots($doctorId, $date)
    {
        $dayOfWeek = Carbon::parse($date)->format('l'); // Monday, Tuesday, etc.
        $now = Carbon::now();
        $requestedDate = Carbon::parse($date);
        
        // Get doctor's availability for the requested day
        $availability = self::where('doctor_id', $doctorId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_available', true)
            ->first();

        if (!$availability) {
            return [];
        }

        // Parse times
        $startTime = Carbon::parse($availability->start_time);
        $endTime = Carbon::parse($availability->end_time);
        $slotDuration = $availability->slot_duration;

        // Generate time slots
        $slots = [];
        $current = $startTime->copy();
        
        while ($current->lt($endTime)) {
            $slotStart = $current->format('H:i:s');
            $slotEnd = $current->copy()->addMinutes($slotDuration);
            
            // Pastikan slot tidak melebihi waktu selesai praktek
            if ($slotEnd->gt($endTime)) {
                break;
            }

            // FILTER WAKTU: Untuk hari ini, skip slot yang sudah lewat
            if ($requestedDate->isToday()) {
                $slotDateTime = Carbon::parse($date . ' ' . $slotStart);
                if ($slotDateTime->lte($now)) {
                    $current->addMinutes($slotDuration);
                    continue; // Skip slot yang sudah lewat
                }
            }

            // Check if slot is already booked
            $isBooked = \App\Models\Appointment::where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $date)
                ->where('start_time', '<=', $slotStart)
                ->where('end_time', '>', $slotStart)
                ->whereIn('status', ['pending', 'scheduled', 'confirmed', 'check-in', 'waiting', 'in-consultation'])
                ->exists();

            // Check if slot is blocked
            $isBlocked = \App\Models\BlockedSlot::where('doctor_id', $doctorId)
                ->where('blocked_date', $date)
                ->where('start_time', '<=', $slotStart)
                ->where('end_time', '>', $slotStart)
                ->exists();

            // Only add slot if it's not booked and not blocked
            if (!$isBooked && !$isBlocked) {
                $slots[] = [
                    'start' => $slotStart,
                    'end' => $slotEnd->format('H:i:s'),
                ];
            }

            $current->addMinutes($slotDuration);
        }

        return $slots;
    }

    /**
     * Check if a specific time slot is available
     * dengan validasi waktu yang sudah lewat
     */
    public static function isSlotAvailable($doctorId, $date, $startTime, $endTime)
    {
        $now = Carbon::now();
        $requestedDate = Carbon::parse($date);
        $slotDateTime = Carbon::parse($date . ' ' . $startTime);

        // VALIDASI: Cek apakah slot sudah lewat
        if ($slotDateTime->lte($now)) {
            return false; // Slot sudah lewat
        }

        $dayOfWeek = $requestedDate->format('l');
        
        // Check if doctor has availability on this day
        $availability = self::where('doctor_id', $doctorId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_available', true)
            ->where('start_time', '<=', $startTime)
            ->where('end_time', '>=', $endTime)
            ->first();

        if (!$availability) {
            return false;
        }

        // Check for conflicting appointments
        $hasConflict = \App\Models\Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
            })
            ->whereIn('status', ['pending', 'scheduled', 'confirmed', 'check-in', 'waiting', 'in-consultation'])
            ->exists();

        if ($hasConflict) {
            return false;
        }

        // Check for blocked slots
        $isBlocked = \App\Models\BlockedSlot::where('doctor_id', $doctorId)
            ->where('blocked_date', $date)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
            })
            ->exists();

        return !$isBlocked;
    }
}