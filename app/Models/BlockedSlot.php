<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'blocked_date',
        'start_time',
        'end_time',
        'reason',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}