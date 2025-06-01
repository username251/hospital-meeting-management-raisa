<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'specialty_id',
        'phone_number',
        'license_number',
        'bio',
        'consultation_fee',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function specialty() // Pastikan relasi ini ada dan benar
    {
        return $this->belongsTo(Specialty::class);
    }

    public function availabilities()
    {
        return $this->hasMany(DoctorAvailability::class);
    }

    // Tambahkan ini jika Anda belum punya
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}