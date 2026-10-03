<?php

namespace App\Models\Ujian;

use App\Models\Level;
use App\Models\Murid;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\Tingkat;
use App\Models\UjianAlquran\PesertaUjianAlquran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesertaImni extends Model
{
    use HasFactory;

    protected $table = 'peserta_imnis';

    protected $fillable = [
        'tahun_pelajaran_id',
        'murid_id',
        'tingkat_id',
        'level_id',
        'ruangan_asal_id',
        'ruangan_ujian_id',
        'nomor_peserta',
        'nomor_meja',
        'status_kelayakan',
        'is_active',
        'catatan',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'nomor_meja'  => 'integer',
    ];

    /**
     * Relasi ke Tahun Pelajaran
     */
    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    /**
     * Relasi ke Murid
     */
    public function murid()
    {
        return $this->belongsTo(Murid::class, 'murid_id');
    }

    /**
     * Relasi ke Tingkat (TPQ, IBT, TSA)
     */
    public function tingkat()
    {
        return $this->belongsTo(Tingkat::class, 'tingkat_id');
    }

    /**
     * Relasi ke Level (3 TPQ, 6 IBT, 3 TSA)
     */
    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    /**
     * Relasi ke Ruangan Kelas Asal (Harian)
     */
    public function ruanganAsal()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_asal_id');
    }

    /**
     * Relasi ke Ruangan Ujian IMNI (Plotting)
     */
    public function ruanganUjian()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_ujian_id');
    }

    /**
     * Relasi ke Data Peserta Ujian Al-Qur'an (jika terdaftar)
     */
    public function pesertaAlquran()
    {
        return $this->hasOne(PesertaUjianAlquran::class, 'murid_id', 'murid_id');
    }

    /**
     * Relasi ke Pembayaran IMNI
     */
    public function pembayaran()
    {
        return $this->hasOne(PembayaranImni::class, 'peserta_imni_id');
    }

    /**
     * Relasi ke Putusan Kelulusan IMNI
     */
    public function kelulusan()
    {
        return $this->hasOne(KelulusanImni::class, 'peserta_imni_id');
    }

    /**
     * Relasi ke Pivot Peserta Ruangan IMNI (Plotting Harian / Default)
     */
    public function pesertaRuanganImnis()
    {
        return $this->hasMany(PesertaRuanganImni::class, 'peserta_imni_id');
    }

    /**
     * Scope Peserta Aktif
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope Filter Berdasarkan Tahun Pelajaran
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
     * Scope Filter Berdasarkan Tingkat
     */
    public function scopeByTingkat(Builder $query, $tingkatId): Builder
    {
        return $query->where('tingkat_id', $tingkatId);
    }

    /**
     * Scope Filter Berdasarkan Ruangan Ujian
     */
    public function scopeByRuanganUjian(Builder $query, $ruanganId): Builder
    {
        return $query->where('ruangan_ujian_id', $ruanganId);
    }

    /**
     * Generate Format Nomor Peserta Otomatis IMNI
     * Format: IMNI-tahun pelajaran hijriyah (4748)-KODE_TINGKAT-NISM
     * Contoh: IMNI-4748-TPQ-00123 atau IMNI-4748-IBT-00456
     */
    public static function generateNomorPeserta($tahunPelajaran, $tingkatKode, $nism): string
    {
        $hijriyahCode = '4748';
        if ($tahunPelajaran && !empty($tahunPelajaran->nama_hijriyah)) {
            preg_match_all('/\d+/', $tahunPelajaran->nama_hijriyah, $matches);
            if (!empty($matches[0])) {
                if (count($matches[0]) >= 2) {
                    $hijriyahCode = substr($matches[0][0], -2) . substr($matches[0][1], -2);
                } else {
                    $hijriyahCode = substr($matches[0][0], -2);
                }
            }
        }

        $rawKode = strtoupper(trim($tingkatKode ?? ''));
        if (str_contains($rawKode, 'TPQ')) {
            $kodeTingkat = 'TPQ';
        } elseif (str_contains($rawKode, 'TSA')) {
            $kodeTingkat = 'TSA';
        } elseif (str_contains($rawKode, 'IBT')) {
            $kodeTingkat = 'IBT';
        } else {
            $kodeTingkat = $rawKode ?: 'IBT';
        }

        $nismClean = trim((string) ($nism ?? '000'));
        if ($nismClean === '' || $nismClean === '-') {
            $nismClean = '000';
        }

        return "IMNI-{$hijriyahCode}-{$kodeTingkat}-{$nismClean}";
    }
}
