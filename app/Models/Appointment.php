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
        'specialty_id', // Pastikan ini ada jika Anda menggunakannya
        'appointment_date',
        'start_time',   // <-- TAMBAHKAN ATAU PASTIKAN INI ADA
        'end_time',     // <-- TAMBAHKAN ATAU PASTIKAN INI ADA
        'reason',       // Pastikan ini ada
        'status',       // Pastikan ini ada
        'notes',        // Pastikan ini ada
    ];

    // Relasi ke model Patient
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    // Relasi ke model Doctor
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    // Relasi ke model Specialty (jika ada dan ingin ditampilkan)
    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }
}