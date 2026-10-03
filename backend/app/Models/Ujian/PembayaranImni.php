<?php

namespace App\Models\Ujian;

use App\Models\Murid;
use App\Models\TahunPelajaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranImni extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_imnis';

    protected $fillable = [
        'tahun_pelajaran_id',
        'peserta_imni_id',
        'murid_id',
        'no_kwitansi',
        'nominal_tagihan',
        'nominal_bayar',
        'sisa_tagihan',
        'tanggal_bayar',
        'metode_pembayaran',
        'status_pembayaran',
        'diterima_oleh',
        'nama_penyetor',
        'keterangan',
    ];

    protected $casts = [
        'nominal_tagihan' => 'float',
        'nominal_bayar'   => 'float',
        'sisa_tagihan'    => 'float',
        'tanggal_bayar'   => 'date',
    ];

    /**
     * Relasi ke Tahun Pelajaran
     */
    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    /**
     * Relasi ke Data Peserta IMNI
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
     * Relasi ke Petugas / Bendahara yang Menerima Pembayaran
     */
    public function penerima()
    {
        return $this->belongsTo(User::class, 'diterima_oleh');
    }

    /**
     * Scope Pembayaran Berdasarkan Tahun
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
     * Scope Lunas
     */
    public function scopeLunas(Builder $query): Builder
    {
        return $query->where('status_pembayaran', 'Lunas');
    }

    /**
     * Scope Belum Lunas
     */
    public function scopeBelumLunas(Builder $query): Builder
    {
        return $query->where('status_pembayaran', 'Belum Lunas');
    }

    /**
     * Scope Dispensasi
     */
    public function scopeDispensasi(Builder $query): Builder
    {
        return $query->where('status_pembayaran', 'Dispensasi');
    }

    /**
     * Helper Generate Nomor Kwitansi Otomatis
     * Format: IMNI/4748/DDMMYYYY/nomor random(5)
     * Contoh: IMNI/4748/03102026/83921
     */
    public static function generateNoKwitansi($tahunPelajaran = null, $tanggal = null): string
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

        $dateStr = $tanggal ? \Carbon\Carbon::parse($tanggal)->format('dmY') : date('dmY');

        do {
            $random5 = str_pad((string) mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $noKwitansi = "IMNI/{$hijriyahCode}/{$dateStr}/{$random5}";
        } while (self::where('no_kwitansi', $noKwitansi)->exists());

        return $noKwitansi;
    }
}
