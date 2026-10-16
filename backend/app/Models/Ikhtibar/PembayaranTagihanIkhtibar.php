<?php

namespace App\Models\Ikhtibar;

use App\Models\TagihanWaliMurid;
use App\Models\TahunPelajaran;
use App\Models\WaliMurid;
use App\Models\User;
use App\Models\PembayaranTagihan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranTagihanIkhtibar extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_tagihan_ikhtibars';

    protected $fillable = [
        'tagihan_wali_murid_id',
        'kegiatan_ikhtibar_id',
        'tahun_pelajaran_id',
        'wali_murid_id',
        'no_transaksi',
        'metode_pembayaran',
        'tipe_pembayaran',
        'termin_ke',
        'nominal_tagihan',
        'nominal_bayar',
        'sisa_tagihan',
        'tanggal_bayar',
        'nomor_tabungan',
        'nama_nasabah',
        'ruangan_nasabah',
        'is_verified',
        'verified_at',
        'verified_by_id',
        'pembayaran_tagihan_id',
        'diterima_oleh_id',
        'nama_penyetor',
        'catatan',
    ];

    protected $casts = [
        'termin_ke'       => 'integer',
        'nominal_tagihan' => 'integer',
        'nominal_bayar'   => 'integer',
        'sisa_tagihan'    => 'integer',
        'tanggal_bayar'   => 'date',
        'is_verified'     => 'boolean',
        'verified_at'     => 'datetime',
    ];

    /**
     * Relasi ke Tagihan Wali Murid (KK)
     */
    public function tagihanWaliMurid()
    {
        return $this->belongsTo(TagihanWaliMurid::class, 'tagihan_wali_murid_id');
    }

    /**
     * Relasi ke Master Kegiatan Ikhtibar
     */
    public function kegiatanIkhtibar()
    {
        return $this->belongsTo(KegiatanIkhtibar::class, 'kegiatan_ikhtibar_id');
    }

    /**
     * Relasi ke Tahun Pelajaran
     */
    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    /**
     * Relasi ke Wali Murid (Kepala Keluarga)
     */
    public function waliMurid()
    {
        return $this->belongsTo(WaliMurid::class, 'wali_murid_id');
    }

    /**
     * Relasi ke User Petugas yang menerima
     */
    public function diterimaOleh()
    {
        return $this->belongsTo(User::class, 'diterima_oleh_id');
    }

    /**
     * Relasi ke User yang memverifikasi potong tabungan
     */
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }

    /**
     * Relasi ke Kwitansi Utama Pembayaran Tagihan
     */
    public function pembayaranTagihan()
    {
        return $this->belongsTo(PembayaranTagihan::class, 'pembayaran_tagihan_id');
    }

    /**
     * Label Termin
     */
    public function getLabelTerminAttribute(): string
    {
        if ($this->tipe_pembayaran === 'Lunas' && $this->termin_ke === 1) {
            return 'Lunas Sekaligus';
        }
        return "Cicilan ke-{$this->termin_ke} (Maks. 3x)";
    }
}

