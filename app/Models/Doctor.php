<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
        'profile_picture',
        'is_active'
    ];

    protected $casts = [
        'consultation_fee' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // Relasi
    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function availabilities()
    {
        return $this->hasMany(DoctorAvailability::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    // Accessor untuk profile picture URL
   public function getProfilePictureUrlAttribute()
{
    // Debug: Log untuk melihat apa yang terjadi
    \Log::info('Doctor Profile Picture Debug', [
        'doctor_id' => $this->id,
        'profile_picture' => $this->profile_picture,
        'user_name' => $this->user->name ?? 'No name'
    ]);
    
    // Cek apakah ada profile picture
    if ($this->profile_picture) {
        // Path ke file
        $storagePath = 'profile_pictures/doctors/' . $this->profile_picture;
        $publicPath = 'storage/profile_pictures/doctors/' . $this->profile_picture;
        
        // Cek di storage Laravel
        if (\Storage::disk('public')->exists($storagePath)) {
            return asset($publicPath);
        }
        
        // Cek langsung di public path
        if (file_exists(public_path($publicPath))) {
            return asset($publicPath);
        }
        
        // Debug: Log jika file tidak ditemukan
        \Log::warning('Doctor profile picture not found', [
            'storage_path' => $storagePath,
            'public_path' => $publicPath,
            'storage_exists' => \Storage::disk('public')->exists($storagePath),
            'file_exists' => file_exists(public_path($publicPath))
        ]);
    }
    
    // Fallback ke avatar user jika ada
    if ($this->user && $this->user->avatar) {
        $avatarPath = 'storage/profile_pictures/patients/' . $this->user->avatar;
        
        if (file_exists(public_path($avatarPath))) {
            return asset($avatarPath);
        }
    }
    
    // Default: return gambar default atau placeholder
    $defaultImage = asset('img/default-doctor.jpg');
    
    // Cek apakah file default ada
    if (file_exists(public_path('img/default-doctor.jpg'))) {
        return $defaultImage;
    }
    
    // Terakhir: gunakan placeholder
    $doctorName = $this->user->name ?? 'Doctor';
    return 'https://ui-avatars.com/api/?name=' . urlencode($doctorName) . '&size=250&background=007bff&color=ffffff';
}
    // Accessor untuk nama lengkap doctor
    public function getFullNameAttribute()
    {
        return 'Dr. ' . ($this->user->name ?? 'Unknown');
    }

    // Accessor untuk rata-rata rating
    public function getAverageRatingAttribute()
    {
        return $this->feedback()->avg('rating') ?? 0;
    }

    // Accessor untuk jumlah feedback
    public function getFeedbackCountAttribute()
    {
        return $this->feedback()->count();
    }

    // Accessor untuk status online/offline
    public function getIsOnlineAttribute()
    {
        // Logika untuk menentukan apakah doctor sedang online
        // Misalnya berdasarkan last_activity atau availability
        return $this->availabilities()
                   ->where('day_of_week', now()->dayOfWeek)
                   ->where('start_time', '<=', now()->format('H:i:s'))
                   ->where('end_time', '>=', now()->format('H:i:s'))
                   ->exists();
    }

    // Scope untuk dokter dengan rating tertinggi
    public function scopeTopRated($query, $limit = 4)
    {
        return $query->withCount('feedback')
                    ->withAvg('feedback', 'rating')
                    ->having('feedback_count', '>', 0)
                    ->orderByDesc('feedback_avg_rating')
                    ->orderByDesc('feedback_count')
                    ->limit($limit);
    }

    // Scope untuk dokter aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->whereHas('user', function($q) {
                        $q->where('is_active', true);
                    });
    }

    // Scope untuk pencarian dokter
    public function scopeSearch($query, $search)
    {
        return $query->whereHas('user', function($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%');
        })->orWhereHas('specialty', function($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%');
        })->orWhere('bio', 'like', '%' . $search . '%');
    }

    // Method untuk mendapatkan availability hari ini
    public function getTodayAvailability()
    {
        return $this->availabilities()
                   ->where('day_of_week', now()->dayOfWeek)
                   ->first();
    }

    // Method untuk mengecek apakah doctor tersedia sekarang
    public function isAvailableNow()
    {
        $availability = $this->getTodayAvailability();
        
        if (!$availability) {
            return false;
        }

        $now = now()->format('H:i:s');
        return $now >= $availability->start_time && $now <= $availability->end_time;
    }
}