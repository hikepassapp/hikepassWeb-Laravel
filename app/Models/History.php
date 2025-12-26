<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_checkout',
    ];

    public function checkout()
    {
        return $this->belongsTo(Checkout::class, 'id_checkout', 'id');
    }

    public function checkin()
    {
        return $this->hasOneThrough(
            Checkin::class,
            Checkout::class,
            'id', // Foreign key di checkouts
            'id', // Foreign key di checkins
            'id_checkout', // Local key di histories
            'id_checkin' // Local key di checkouts
        );
    }

    public function reservation()
    {
        return $this->checkout->checkin->reservation ?? null;
    }
}