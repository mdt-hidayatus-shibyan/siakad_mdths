<?php

namespace App\Models\Ujian;

use App\Models\TahunPelajaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengeluaranImni extends Model
{
    use HasFactory;

    protected $table = 'pengeluaran_imnis';

    protected $fillable = [
        'tahun_pelajaran_id',
        'kode_transaksi',
        'kategori',
        'judul_pengeluaran',
        'nominal',
        'tanggal_pengeluaran',
        'penerima_dana',
        'metode_pembayaran',
        'bukti_nota',
        'keterangan',
        'dicatat_oleh',
    ];

    protected $casts = [
        'nominal'             => 'float',
        'tanggal_pengeluaran' => 'date',
    ];

    /**
     * Relasi ke Tahun Pelajaran
     */
    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    /**
     * Relasi ke User / Bendahara yang Mencatat Pengeluaran
     */
    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    /**
     * Scope Filter Berdasarkan Tahun
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
     * Scope Filter Berdasarkan Kategori
     */
    public function scopeByKategori(Builder $query, $kategori): Builder
    {
        return $query->where('kategori', $kategori);
    }

    /**
     * Helper Generate Kode Transaksi Pengeluaran
     * Contoh: KAS-OUT/IMNI/2026/001
     */
    public static function generateKodeTransaksi($tahunPelajaran, $urutan): string
    {
        $tahun = $tahunPelajaran ? substr($tahunPelajaran->nama_masehi, 0, 4) : date('Y');
        $pad = str_pad($urutan, 4, '0', STR_PAD_LEFT);

        return "KAS-OUT/IMNI/{$tahun}/{$pad}";
    }
}
