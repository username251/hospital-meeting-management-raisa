<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_available', // Tambahkan is_available
    ];

    protected $casts = [
        'day_of_week'  => 'integer', // Pastikan day_of_week di-cast sebagai integer
        'is_available' => 'boolean', // Pastikan is_available di-cast sebagai boolean
    ];

    /**
     * Get the doctor that owns the schedule.
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}