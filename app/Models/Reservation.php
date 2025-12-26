<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'id_mountain',
        'start_date',
        'name',
        'nik',
        'gender',
        'phone_number',
        'address',
        'citizen',
        'id_card',
        'price',
    ];

    protected $casts = [
        'start_date' => 'date',
    ];

    public function mountain()
    {
        return $this->belongsTo(Mountain::class, 'id_mountain', 'id');
    }

    public function checkin()
    {
        return $this->hasOne(Checkin::class, 'id_reservation', 'id');
    }
}