<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_checkin',
        'item_list',
        'checkout_date',
    ];

    protected $casts = [
        'checkout_date' => 'date',
    ];

    public function checkin()
    {
        return $this->belongsTo(Checkin::class, 'id_checkin', 'id');
    }

    public function reservation()
    {
        return $this->hasOneThrough(
            Reservation::class,
            Checkin::class,
            'id', // Foreign key di checkins
            'id', // Foreign key di reservations
            'id_checkin', // Local key di checkouts
            'id_reservation' // Local key di checkins
        );
    }
}