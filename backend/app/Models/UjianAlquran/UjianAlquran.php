<?php

namespace App\Models\UjianAlquran;

use App\Models\TahunPelajaran;
use Illuminate\Database\Eloquent\Model;

class UjianAlquran extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_ujian' => 'date',
        'kkm_kelulusan' => 'float',
        'bobot_jali'    => 'float',
        'bobot_khofi'   => 'float',
    ];

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function juris()
    {
        return $this->hasMany(JuriUjianAlquran::class, 'ujian_alquran_id')->orderBy('urutan', 'asc');
    }

    public function pesertas()
    {
        return $this->hasMany(PesertaUjianAlquran::class, 'ujian_alquran_id');
    }

    /**
     * Hitung ringkasan statistik
     */
    public function getStatistikAttribute()
    {
        $total = $this->pesertas()->count();
        $lulus = $this->pesertas()->where('status_kelulusan', 'Lulus')->count();
        $tidakLulus = $this->pesertas()->where('status_kelulusan', 'Tidak Lulus')->count();
        $belumDiuji = $this->pesertas()->where('status_kelulusan', 'Belum Diuji')->count();
        $persenLulus = $total > 0 ? round(($lulus / $total) * 100, 1) : 0;

        return (object) [
            'total'        => $total,
            'lulus'        => $lulus,
            'tidak_lulus'  => $tidakLulus,
            'belum_diuji'  => $belumDiuji,
            'persen_lulus' => $persenLulus,
        ];
    }
}
