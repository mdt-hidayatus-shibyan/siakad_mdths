<?php

namespace App\Models\UjianAlquran;

use App\Models\Ustadz;
use Illuminate\Database\Eloquent\Model;

class JuriUjianAlquran extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_penanggung_jawab' => 'boolean',
    ];

    public function ujianAlquran()
    {
        return $this->belongsTo(UjianAlquran::class, 'ujian_alquran_id');
    }

    public function ustadz()
    {
        return $this->belongsTo(Ustadz::class, 'ustadz_id');
    }

    public function penilaians()
    {
        return $this->hasMany(PenilaianJuriAlquran::class, 'juri_id');
    }

    public function getLabelPeranAttribute()
    {
        return match ($this->peran_juri) {
            'juri_jali'  => "Juri Khotho' Jali (-5/kesalahan)",
            'juri_khofi' => "Juri Khotho' Khofi (-3/kesalahan)",
            'juri_utama' => "Juri Utama / Penguji Umum",
            default      => "Anggota Dewan Juri",
        };
    }
}
