<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'requested_clock_in',
        'requested_clock_out',
        'note',
        'status',
        'admin_comment',
    ];

    /**
     * StampCorrectionRequest（多）- User（1）
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * StampCorrectionRequest（多）- Attendance（1）
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * StampCorrectionRequest（1）- StampCorrectionRequestBreak（多）
     */
    public function breaks()
    {
        return $this->hasMany(StampCorrectionRequestBreak::class);
    }
}
