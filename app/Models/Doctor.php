<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo
use Illuminate\Database\Eloquent\Relations\HasMany;   // Import HasMany

class Doctor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'specialty_id',
        'license_number',
        'bio',
        'consultation_fee',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'consultation_fee' => 'decimal:2', // Pastikan biaya dikonversi ke desimal dengan 2 angka di belakang koma
    ];

    /**
     * Get the user that owns the Doctor.
     * Definisi relasi One-to-One atau One-to-Many terbalik.
     * Setiap dokter memiliki satu entri di tabel users.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the specialty associated with the Doctor.
     * Definisi relasi One-to-One atau One-to-Many terbalik.
     * Setiap dokter memiliki satu spesialisasi.
     */
    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    /**
     * Get the schedules for the Doctor.
     * Definisi relasi One-to-Many.
     * Satu dokter memiliki banyak jadwal kerja.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class); // Asumsikan kamu membuat model DoctorSchedule
    }

    /**
     * Get the appointments for the Doctor.
     * Definisi relasi One-to-Many.
     * Satu dokter memiliki banyak janji temu.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class); // Asumsikan kamu membuat model Appointment
    }

    /**
     * Get the blocked slots for the Doctor.
     * Definisi relasi One-to-Many.
     * Satu dokter memiliki banyak slot waktu yang diblokir.
     */
    public function blockedSlots(): HasMany
    {
        return $this->hasMany(BlockedSlot::class); // Asumsikan kamu membuat model BlockedSlot
    }
}