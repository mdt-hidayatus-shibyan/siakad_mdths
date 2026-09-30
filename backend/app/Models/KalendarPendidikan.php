<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class KalendarPendidikan extends Model
{
    protected $table = 'kalendar_pendidikans';

    protected $fillable = [
        'tahun_pelajaran_id',
        'nama_kegiatan',
        'kategori_kegiatan_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'tipe_presensi',
        'sesi_kegiatan',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'sesi_kegiatan'   => 'array',
    ];

    // Relasi ke Tahun Pelajaran
    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    // Relasi ke Kategori Kegiatan
    public function kategoriKegiatan()
    {
        return $this->belongsTo(KategoriKegiatan::class);
    }

    // Relasi ke Presensi Kegiatan Murid
    public function presensiMurids()
    {
        return $this->hasMany(PresensiKegiatanMurid::class, 'kalendar_pendidikan_id');
    }

    // Relasi ke Presensi Kegiatan Ustadz
    public function presensiUstadzs()
    {
        return $this->hasMany(PresensiKegiatanUstadz::class, 'kalendar_pendidikan_id');
    }

    /**
     * Cek apakah pada tanggal tertentu terdapat event/kegiatan khusus dengan presensi aktif
     *
     * @param string|Carbon $tanggal
     * @return KalendarPendidikan|null
     */
    public static function getActiveEventPresensi($tanggal): ?self
    {
        $tglStr = Carbon::parse($tanggal)->format('Y-m-d');

        return self::with(['kategoriKegiatan'])
            ->whereDate('tanggal_mulai', '<=', $tglStr)
            ->whereDate('tanggal_selesai', '>=', $tglStr)
            ->where('tipe_presensi', '!=', 'tidak_ada')
            ->orderBy('id', 'desc')
            ->first();
    }
}
