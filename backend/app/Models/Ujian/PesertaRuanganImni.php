<?php

namespace App\Models\Ujian;

use App\Models\Murid;
use App\Models\TahunPelajaran;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesertaRuanganImni extends Model
{
    use HasFactory;

    protected $table = 'peserta_ruangan_imnis';

    protected $fillable = [
        'tahun_pelajaran_id',
        'ruangan_imni_id',
        'peserta_imni_id',
        'murid_id',
        'tanggal_ujian',
        'nomor_meja',
        'catatan',
    ];

    protected $casts = [
        'tanggal_ujian' => 'date',
        'nomor_meja'    => 'integer',
    ];

    /**
     * Relasi ke Tahun Pelajaran
     */
    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    /**
     * Relasi ke Ruangan IMNI
     */
    public function ruanganImni()
    {
        return $this->belongsTo(RuanganImni::class, 'ruangan_imni_id');
    }

    /**
     * Relasi ke Peserta IMNI
     */
    public function pesertaImni()
    {
        return $this->belongsTo(PesertaImni::class, 'peserta_imni_id');
    }

    /**
     * Relasi ke Murid
     */
    public function murid()
    {
        return $this->belongsTo(Murid::class, 'murid_id');
    }
}
