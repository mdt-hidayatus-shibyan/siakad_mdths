<?php

namespace App\Models\Ujian;

use App\Models\TahunPelajaran;
use App\Models\Ustadz;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PanitiaImni extends Model
{
    use HasFactory;

    protected $table = 'panitia_imnis';

    protected $fillable = [
        'tahun_pelajaran_id',
        'ustadz_id',
        'jabatan',
        'no_sk',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
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
     * Relasi ke Data Ustadz
     */
    public function ustadz()
    {
        return $this->belongsTo(Ustadz::class, 'ustadz_id');
    }

    /**
     * Scope Panitia Aktif
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope Tahun Pelajaran Tertentu / Aktif
     */
    public function scopeByTahun(Builder $query, $tahunPelajaranId = null): Builder
    {
        if ($tahunPelajaranId) {
            return $query->where('tahun_pelajaran_id', $tahunPelajaranId);
        }

        return $query->whereHas('tahunPelajaran', function ($q) {
            $q->where('is_active', true);
        });
    }

    /**
     * Scope Jabatan Ketua
     */
    public function scopeKetua(Builder $query): Builder
    {
        return $query->where('jabatan', 'Ketua');
    }

    /**
     * Scope Jabatan Bendahara
     */
    public function scopeBendahara(Builder $query): Builder
    {
        return $query->where('jabatan', 'Bendahara');
    }

    /**
     * Scope Jabatan Anggota
     */
    public function scopeAnggota(Builder $query): Builder
    {
        return $query->where('jabatan', 'Anggota');
    }

    /**
     * Dapatkan data Ketua Panitia IMNI untuk Tahun Pelajaran tertentu / aktif
     */
    public static function getKetua($tahunPelajaranId = null)
    {
        return static::with('ustadz')
            ->byTahun($tahunPelajaranId)
            ->aktif()
            ->ketua()
            ->first();
    }

    /**
     * Dapatkan data Bendahara Panitia IMNI untuk Tahun Pelajaran tertentu / aktif
     */
    public static function getBendahara($tahunPelajaranId = null)
    {
        return static::with('ustadz')
            ->byTahun($tahunPelajaranId)
            ->aktif()
            ->bendahara()
            ->first();
    }

    /**
     * Periksa apakah Ustadz tertentu adalah anggota Panitia IMNI
     */
    public static function getPanitiaByUstadz($ustadzId, $tahunPelajaranId = null)
    {
        return static::with(['ustadz', 'tahunPelajaran'])
            ->byTahun($tahunPelajaranId)
            ->aktif()
            ->where('ustadz_id', $ustadzId)
            ->first();
    }
}
