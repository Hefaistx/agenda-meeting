<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonfigurasiWaktu extends Model
{
    protected $table = 'konfigurasi_waktu';

    protected $fillable = ['kategori', 'waktu_mulai_min', 'waktu_selesai_max'];
}
