<?php

namespace App\Models\Ujian;

use App\Models\TahunPelajaran;
use App\Models\Ustadz;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengawasRuanganImni extends Model
{
    use HasFactory;

    protected $table = 'pengawas_ruangan_imnis';

    protected $fillable = [
        'tahun_pelajaran_id',
        'tanggal_ujian',
        'ruangan_imni_id',
        'ustadz_id',
        'nama_pengawas',
        'catatan',
    ];

    protected $casts = [
        'tanggal_ujian' => 'date',
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
     * Relasi ke Ustadz (Pengawas)
     */
    public function ustadz()
    {
        return $this->belongsTo(Ustadz::class, 'ustadz_id');
    }

    /**
     * Helper nama pengawas efektif
     */
    public function getNamaPengawasEfektifAttribute()
    {
        return $this->ustadz?->nama_lengkap ?? $this->nama_pengawas ?? '-';
    }
}
