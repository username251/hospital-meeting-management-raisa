<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'specialty_id',
        'appointment_date', // Perubahan: dari appointment_time
        'start_time',       // Perubahan: kolom baru
        'end_time',         // Perubahan: kolom baru
        'reason',           // Perubahan: kolom baru
        'status',
        'notes',            // Perubahan: kolom baru
    ];

    // Relasi dengan model Patient
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    // Relasi dengan model Doctor
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    // Relasi dengan model Specialty
    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    // Jika Anda ingin mengelola casting tipe data secara otomatis
    protected $casts = [
        'appointment_date' => 'date',
        'start_time' => 'datetime', // Akan di-cast menjadi objek Carbon
        'end_time' => 'datetime',   // Akan di-cast menjadi objek Carbon
    ];
}