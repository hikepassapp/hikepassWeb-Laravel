<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_pelapor',
        'tanggal_kejadian',
        'lokasi_kejadian',
        'deskripsi_kejadian',
        'foto_bukti'
    ];

    protected $casts = [
        'tanggal_kejadian' => 'date'
    ];
}
