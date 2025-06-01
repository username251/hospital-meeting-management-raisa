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
        'appointment_date',
        'start_time',
        'end_time',
        'reason',
        'status',
        'notes',
    ];

    // INI BAGIAN PALING PENTING UNTUK MENGATASI ERROR 'format() on string'
    protected $casts = [
        'appointment_date' => 'date',   // Mengonversi ke objek Carbon Date
        'start_time' => 'datetime',     // Mengonversi ke objek Carbon DateTime
        'end_time' => 'datetime',       // Mengonversi ke objek Carbon DateTime
    ];

    // Definisi relasi
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

   public function doctor()
{
    return $this->belongsTo(Doctor::class)->withDefault([
        'user' => (object)['name' => 'Dokter Tidak Ditemukan'],
        'specialty' => (object)['name' => 'Spesialisasi Tidak Ditemukan']
    ]);
}

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }
}