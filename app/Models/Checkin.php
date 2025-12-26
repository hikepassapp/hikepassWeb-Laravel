<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkin extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_reservation',
        'item_list',
        'checkin_date',
    ];

    protected $casts = [
        'checkin_date' => 'date',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'id_reservation', 'id');
    }

    public function checkout()
    {
        return $this->hasOne(Checkout::class, 'id_checkin', 'id');
    }
}