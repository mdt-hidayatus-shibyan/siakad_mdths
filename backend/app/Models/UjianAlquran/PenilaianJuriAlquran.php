<?php

namespace App\Models\UjianAlquran;

use App\Models\Ustadz;
use Illuminate\Database\Eloquent\Model;

class PenilaianJuriAlquran extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'jumlah_kesalahan' => 'integer',
        'poin_pengurangan' => 'float',
    ];

    public function peserta()
    {
        return $this->belongsTo(PesertaUjianAlquran::class, 'peserta_id');
    }

    public function juri()
    {
        return $this->belongsTo(JuriUjianAlquran::class, 'juri_id');
    }

    public function ustadz()
    {
        return $this->belongsTo(Ustadz::class, 'ustadz_id');
    }
}
