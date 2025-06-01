<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne; // Import HasOne

class User extends Authenticatable
{
    use  HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
       protected $fillable = [
        'name', // Pastikan ini ada
        'email',
        'password', // Pastikan ini ada
        'phone_number',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password_hash', // Sembunyikan hash password dari output JSON
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        // 'password' => 'hashed', // Jangan pakai ini jika nama kolomnya password_hash
    ];

    // Override getAuthPasswordName untuk menggunakan kolom password_hash
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Get the patient record associated with the user.
     */
    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    /**
     * Get the doctor record associated with the user.
     */
    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    // Tambahkan relasi untuk staff jika kamu memiliki tabel staff_details
    // public function staff(): HasOne
    // {
    //     return $this->hasOne(Staff::class);
    // }
}