<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequestBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'stamp_correction_request_id',
        'break_start',
        'break_end',
    ];

    protected $casts = [
        'break_start' => 'datetime',
        'break_end' => 'datetime',
    ];

    /**
     * StampCorrectionRequestBreak（多）- StampCorrectionRequest（1）
     */
    public function stampCorrectionRequest()
    {
        return $this->belongsTo(StampCorrectionRequest::class);
    }
}
