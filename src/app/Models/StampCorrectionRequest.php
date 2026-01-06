<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    // 申請ステータス 
    const STATUS_PENDING = 0; // 申請中 
    const STATUS_APPROVED = 1; // 承認 
    const STATUS_REJECTED = 2; // 却下

    protected $fillable = [
        'user_id',
        'attendance_id',
        'date',
        'requested_clock_in',
        'requested_clock_out',
        'note',
        'status',
        'admin_comment',
    ];

    protected $casts = [
        'requested_clock_in' => 'datetime',
        'requested_clock_out' => 'datetime',
        'date' => 'date',
    ];

    public function getStatusLabel()
    {
        return match ($this->status) {
            self::STATUS_PENDING => '申請中',
            self::STATUS_APPROVED => '承認',
            self::STATUS_REJECTED => '却下',
            default => '不明',
        };
    }
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
