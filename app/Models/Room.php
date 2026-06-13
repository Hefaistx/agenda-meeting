<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['nama', 'lokasi', 'keterangan'];

    public function meetings()
    {
        return $this->hasMany(Meeting::class, 'ruangan_id');
    }
}
