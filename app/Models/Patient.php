<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address',
        'date_of_birth',
        'gender',
        'medical_history', // Jika Anda menambahkannya di migration
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