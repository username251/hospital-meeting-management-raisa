<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

  // app/Models/Patient.php
protected $fillable = [
    'user_id', // Pastikan user_id ada jika Patient belongsTo User
    'phone',
    'address',
    'date_of_birth',
    'gender',
    'medical_history', // Pastikan ini ada
    'allergies',       // Pastikan ini ada
    'current_medications', // Pastikan ini ada
    'blood_type',      // Pastikan ini ada
];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Get the user that owns the patient.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Anda bisa menambahkan relasi lain di sini, misal:
    // public function appointments()
    // {
    //     return $this->hasMany(Appointment::class);
    // }
}