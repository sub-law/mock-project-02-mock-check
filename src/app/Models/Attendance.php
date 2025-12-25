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
        'break_in',
        'break_out',
        'status',
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
