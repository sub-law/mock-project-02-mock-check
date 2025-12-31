<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Attendance;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function isClockedOutToday()
    {
        $attendance = $this->attendances()
            ->where('date', today())
            ->first();

        return $attendance && $attendance->status === Attendance::STATUS_DONE;
    }


    /**
     * User（1）- Attendance（多）
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * User（1）- StampCorrectionRequest（多）
     */
    public function stampCorrectionRequests()
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }
}
