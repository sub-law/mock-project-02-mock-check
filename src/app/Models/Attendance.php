<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
    ];

    public function getStatusLabel()
    {
        if (!$this->clock_in) {
            return '勤務外';
        }

        if ($this->breaks()->whereNull('break_end')->exists()) {
            return '休憩中';
        }

        if (!$this->clock_out) {
            return '出勤中';
        }

        return '退勤済';
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
}
