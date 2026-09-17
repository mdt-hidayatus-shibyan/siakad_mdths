<?php

namespace App\Models\Ujian;

use App\Models\Murid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class DispensasiUjian extends Model
{
    protected $fillable = ['ujian_id', 'murid_id', 'alasan_izin', 'diizinkan_oleh'];

    public function pemberiIzin()
    {
        return $this->belongsTo(User::class, 'diizinkan_oleh');
    }

    public function murid()
    {
        return $this->belongsTo(Murid::class, 'murid_id');
    }

    public function ujian()
    {
        return $this->belongsTo(Ujian::class, 'ujian_id');
    }
}
