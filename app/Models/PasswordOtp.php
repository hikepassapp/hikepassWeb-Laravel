<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordOtp extends Model
{
    protected $fillable = [
        'email',
        'otp_hash',
        'expired_at',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
    ];
}
