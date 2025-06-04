<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    // INI BAGIAN PENTING UNTUK MENGATASI ERROR 'format() on string' SAAT EDIT
  

    protected $casts = [
        'day_of_week' => 'string',
        'is_available' => 'boolean',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

      /**
     * Get available time slots for a given doctor and date.
     *
     * @param int $doctorId
     * @param string $appointmentDate (YYYY-MM-DD)
     * @return array
     */
    public static function getAvailableSlots($doctorId, $appointmentDate)
    {
        $date = Carbon::parse($appointmentDate);
        $dayOfWeekInteger = $date->dayOfWeek; // 0 for Sunday, 1 for Monday, etc.

        // Mapping from Carbon's dayOfWeek integer to English day name string
        $daysMapping = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        // Get the doctor's availability for the specific day
        // We check for both integer and string representation of day_of_week if your data is mixed
        $availabilities = self::where('doctor_id', $doctorId)
            ->where(function ($query) use ($dayOfWeekInteger, $daysMapping) {
                $query->where('day_of_week', (string)$dayOfWeekInteger) // Check as string number '0', '1', etc.
                      ->orWhere('day_of_week', $daysMapping[$dayOfWeekInteger]); // Check as English day name 'Sunday', 'Monday', etc.
            })
            ->where('is_available', true)
            ->orderBy('start_time')
            ->get();

        $allGeneratedSlots = [];

        foreach ($availabilities as $availability) {
            $start = Carbon::parse($availability->start_time);
            $end = Carbon::parse($availability->end_time);
            $duration = $availability->slot_duration;

            // Generate slots within the availability range
            while ($start->copy()->addMinutes($duration)->lessThanOrEqualTo($end)) {
                $slotStart = $start->format('H:i:s'); // Use H:i:s as per your appointments table
                $slotEnd = $start->copy()->addMinutes($duration)->format('H:i:s'); // Use H:i:s
                $allGeneratedSlots[] = [
                    'start' => $slotStart,
                    'end' => $slotEnd,
                    'duration' => $duration,
                ];
                $start->addMinutes($duration);
            }
        }

        // --- Filter out booked slots ---
        // Get existing appointments for the selected doctor on the selected date
        $bookedAppointments = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $appointmentDate)
            ->whereIn('status', ['pending', 'confirmed', 'scheduled', 'check-in', 'waiting', 'in-consultation']) // Only consider actively booked slots
            ->get();

        $bookedSlots = [];
        foreach ($bookedAppointments as $appointment) {
            // Store the start and end time of booked appointments
            $bookedSlots[] = [
                'start' => Carbon::parse($appointment->start_time)->format('H:i:s'),
                'end' => Carbon::parse($appointment->end_time)->format('H:i:s'),
            ];
        }

        // --- Filter out blocked slots ---
        // Get explicitly blocked slots for the selected doctor on the selected date
        $blockedSlots = BlockedSlot::where('doctor_id', $doctorId)
            ->where('blocked_date', $appointmentDate)
            ->get();

        $blockedTimeRanges = [];
        foreach ($blockedSlots as $blocked) {
            $blockedTimeRanges[] = [
                'start' => Carbon::parse($blocked->start_time),
                'end' => Carbon::parse($blocked->end_time),
            ];
        }

        $filteredSlots = [];
        foreach ($allGeneratedSlots as $slot) {
            $slotStart = Carbon::parse($slot['start']);
            $slotEnd = Carbon::parse($slot['end']);

            $isBooked = false;
            foreach ($bookedSlots as $booked) {
                $bookedStart = Carbon::parse($booked['start']);
                $bookedEnd = Carbon::parse($booked['end']);

                // Check for overlap: (StartA < EndB) and (EndA > StartB)
                if ($slotStart->lt($bookedEnd) && $slotEnd->gt($bookedStart)) {
                    $isBooked = true;
                    break;
                }
            }

            if ($isBooked) {
                continue; // Skip this slot if it's booked
            }

            $isBlocked = false;
            foreach ($blockedTimeRanges as $blockedRange) {
                // Check for overlap with blocked slots
                if ($slotStart->lt($blockedRange['end']) && $slotEnd->gt($blockedRange['start'])) {
                    $isBlocked = true;
                    break;
                }
            }

            if (!$isBlocked) {
                $filteredSlots[] = $slot; // Add slot if not blocked and not booked
            }
        }

        return $filteredSlots;
    }
}