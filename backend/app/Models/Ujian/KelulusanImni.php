<?php

namespace App\Models\Ujian;

use App\Models\Level;
use App\Models\Murid;
use App\Models\TahunPelajaran;
use App\Models\Tingkat;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelulusanImni extends Model
{
    use HasFactory;

    protected $table = 'kelulusan_imnis';

    protected $fillable = [
        'tahun_pelajaran_id',
        'peserta_imni_id',
        'murid_id',
        'tingkat_id',
        'level_id',
        'rata_nilai_teori',
        'rata_imda_1',
        'nilai_hadir_1',
        'nilai_pelanggaran_1',
        'skor_sem1',
        'rata_imni_2',
        'nilai_hadir_2',
        'nilai_pelanggaran_2',
        'skor_sem2',
        'nilai_akhir',
        'detail_kalkulasi',
        'status_alquran',
        'nilai_alquran',
        'status_kelulusan',
        'nomor_ijazah',
        'nomor_sk_lulus',
        'catatan_yudisium',
        'is_locked',
        'locked_at',
        'locked_by',
    ];

    protected $casts = [
        'rata_nilai_teori'    => 'float',
        'rata_imda_1'         => 'float',
        'nilai_hadir_1'       => 'float',
        'nilai_pelanggaran_1' => 'float',
        'skor_sem1'           => 'float',
        'rata_imni_2'         => 'float',
        'nilai_hadir_2'       => 'float',
        'nilai_pelanggaran_2' => 'float',
        'skor_sem2'           => 'float',
        'nilai_akhir'         => 'float',
        'detail_kalkulasi'    => 'array',
        'nilai_alquran'       => 'float',
        'is_locked'           => 'boolean',
        'locked_at'           => 'datetime',
    ];

    /**
     * Relasi ke Tahun Pelajaran
     */
    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    /**
     * Relasi ke Peserta IMNI
     */
    public function peserta()
    {
        return $this->belongsTo(PesertaImni::class, 'peserta_imni_id');
    }

    /**
     * Relasi ke Data Murid
     */
    public function murid()
    {
        return $this->belongsTo(Murid::class, 'murid_id');
    }

    /**
     * Relasi ke Tingkat
     */
    public function tingkat()
    {
        return $this->belongsTo(Tingkat::class, 'tingkat_id');
    }

    /**
     * Relasi ke Level
     */
    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    /**
     * Relasi ke User yang Mengunci Sidang Yudisium
     */
    public function pengunci()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /**
     * Scope Filter Tahun
     */
    public function scopeByTahun(Builder $query, $tahunId = null): Builder
    {
        if ($tahunId) {
            return $query->where('tahun_pelajaran_id', $tahunId);
        }

        return $query->whereHas('tahunPelajaran', function ($q) {
            $q->where('is_active', true);
        });
    }

    /**
     * Helper Generate Nomor Ijazah Otomatis
     * Format: IJZ-IMNI/{TAHUN}/{KODE_TINGKAT}/{PAD_001}
     */
    public static function generateNomorIjazah($tahunPelajaran, $kodeTingkat, $urutan): string
    {
        $tahun = $tahunPelajaran ? substr($tahunPelajaran->nama_masehi, 0, 4) : date('Y');
        $kode = strtoupper($kodeTingkat ?? 'IMNI');
        $pad = str_pad($urutan, 3, '0', STR_PAD_LEFT);

        return "IJZ-IMNI/{$tahun}/{$kode}/{$pad}";
    }

    /**
     * Helper Generate Nomor SK Kelulusan Otomatis
     * Format: 421.1/SK-IMNI/MDT-HS/{TAHUN}/{PAD_001}
     */
    public static function generateNomorSk($tahunPelajaran, $urutan): string
    {
        $tahun = $tahunPelajaran ? substr($tahunPelajaran->nama_masehi, 0, 4) : date('Y');
        $pad = str_pad($urutan, 3, '0', STR_PAD_LEFT);

        return "421.1/SK-IMNI/MDT-HS/{$tahun}/{$pad}";
    }
}
