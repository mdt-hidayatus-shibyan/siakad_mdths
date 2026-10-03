<?php

namespace App\Models\Ujian;

use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuanganImni extends Model
{
    use HasFactory;

    protected $table = 'ruangan_imnis';

    protected $fillable = [
        'tahun_pelajaran_id',
        'nama_ruangan_imni',
        'nama_ruangan',
        'kode_ruangan',
        'ruangan_id',
        'penanggung_jawab_ruangan_id',
        'kapasitas',
        'urutan',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'kapasitas' => 'integer',
        'urutan'    => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke Tahun Pelajaran
     */
    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    /**
     * Relasi ke Master Ruangan Fisik Madrasah
     */
    public function ruanganFisik()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    /**
     * Relasi ke Panitia IMNI sebagai Penanggung Jawab Ruangan
     */
    public function penanggungJawabRuangan()
    {
        return $this->belongsTo(PanitiaImni::class, 'penanggung_jawab_ruangan_id');
    }

    /**
     * Alias Relasi ke Panitia IMNI
     */
    public function panitiaImni()
    {
        return $this->belongsTo(PanitiaImni::class, 'penanggung_jawab_ruangan_id');
    }

    /**
     * Relasi ke Data Pivot Peserta Ruangan IMNI
     */
    public function pesertaRuangans()
    {
        return $this->hasMany(PesertaRuanganImni::class, 'ruangan_imni_id');
    }

    /**
     * Relasi ke Pengawas Ruangan IMNI
     */
    public function pengawasRuangans()
    {
        return $this->hasMany(PengawasRuanganImni::class, 'ruangan_imni_id');
    }

    /**
     * Scope Ruangan Aktif
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope Filter Berdasarkan Tahun Pelajaran
     */
    public function scopeByTahun(Builder $query, $tahunId): Builder
    {
        return $query->where('tahun_pelajaran_id', $tahunId);
    }
}
