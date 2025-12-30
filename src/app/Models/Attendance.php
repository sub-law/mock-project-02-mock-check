<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Attendance extends Model
{
    use HasFactory;

    // 勤怠ステータス 
    const STATUS_NONE = 0; // 勤務外（未出勤） 
    const STATUS_WORKING = 1; // 出勤中 
    const STATUS_BREAK = 2; // 休憩中 
    const STATUS_DONE = 3; // 退勤済み

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'note',
    ];

    public function getStatusLabel()
    {
        return match ($this->status) {
            self::STATUS_NONE => '勤務外',
            self::STATUS_WORKING => '出勤中',
            self::STATUS_BREAK => '休憩中',
            self::STATUS_DONE => '退勤済',
            default => '不明',
        };
    }

    public function getTotalBreakMinutes()
    {
        return $this->breaks->sum(function ($break) {
            if (!$break->break_end) {
                return 0;
            }
            return $break->break_start->diffInMinutes($break->break_end);
        });
    }

    public function getTotalWorkMinutes()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return 0;
        }

        $workMinutes = $this->clock_in->diffInMinutes($this->clock_out);
        return $workMinutes - $this->getTotalBreakMinutes();
    }

    protected $casts = [
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'date' => 'date',
    ];

    /**
     * User（1）- Attendance（多）
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Attendance（1）- BreakTime（多）
     */
    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }

    /**
     * Attendance（1）- StampCorrectionRequest（多）
     */
    public function stampCorrectionRequests()
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }

    public function correction_request()
    {
        return $this->hasOne(StampCorrectionRequest::class)->latestOfMany();
    }
}
