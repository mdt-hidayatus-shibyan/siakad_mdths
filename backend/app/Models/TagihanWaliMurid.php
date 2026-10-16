<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagihanWaliMurid extends Model
{
    use HasFactory;

    protected $table = 'tagihan_wali_murids';

    protected $fillable = [
        'wali_murid_id',
        'tahun_pelajaran_id',
        'pengaturan_tagihan_id',
        'nama_tagihan_spesifik',
        'nominal_tagihan',
        'total_terbayar',
        'status_bayar',
        'pembayaran_tagihan_id',
        'keterangan',
    ];

    protected $casts = [
        'nominal_tagihan' => 'integer',
        'total_terbayar'  => 'integer',
    ];

    /**
     * Relasi ke Wali Murid (Kepala Keluarga)
     */
    public function waliMurid()
    {
        return $this->belongsTo(WaliMurid::class, 'wali_murid_id');
    }

    /**
     * Relasi ke Tahun Pelajaran
     */
    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    /**
     * Relasi ke Master Pengaturan Tagihan
     */
    public function pengaturanTagihan()
    {
        return $this->belongsTo(PengaturanTagihan::class, 'pengaturan_tagihan_id');
    }

    /**
     * Relasi ke Transaksi Pembayaran / Kwitansi
     */
    public function pembayaranTagihan()
    {
        return $this->belongsTo(PembayaranTagihan::class, 'pembayaran_tagihan_id');
    }

    /**
     * Relasi ke Riwayat Transaksi Ikhtibar (Tunai, Potong Tabungan Manual, dan Cicilan Maks 3x)
     */
    public function pembayaranIkhtibars()
    {
        return $this->hasMany(\App\Models\Ikhtibar\PembayaranTagihanIkhtibar::class, 'tagihan_wali_murid_id')->orderBy('termin_ke', 'asc');
    }

    /**
     * Total Terbayar / Nominal Bayar (Alias untuk total_terbayar)
     */
    public function getNominalBayarAttribute(): int
    {
        return (int) ($this->total_terbayar ?? 0);
    }

    /**
     * Sisa Tagihan (Nominal Tagihan dikurangi Total Terbayar)
     */
    public function getSisaTagihanAttribute(): int
    {
        if ($this->status_bayar === 'Lunas') {
            return 0;
        }
        $terbayar = $this->total_terbayar ?? 0;
        return max(0, $this->nominal_tagihan - $terbayar);
    }

    /**
     * Jumlah Termin Cicilan yang sudah dicatat
     */
    public function getJumlahCicilanAttribute(): int
    {
        if ($this->relationLoaded('pembayaranIkhtibars')) {
            return $this->pembayaranIkhtibars->count();
        }
        return $this->pembayaranIkhtibars()->count();
    }

    /**
     * Termin Berikutnya (Maksimal 3)
     */
    public function getTerminBerikutnyaAttribute(): int
    {
        return min(3, $this->jumlah_cicilan + 1);
    }

    /**
     * Scope Tagihan yang sedang dalam status Cicilan
     */
    public function scopeCicilan($query)
    {
        return $query->where('status_bayar', 'Cicilan');
    }

    /**
     * Scope Tagihan yang belum lunas (termasuk Cicilan dan Belum Lunas)
     */
    public function scopeBelumLunas($query)
    {
        return $query->whereIn('status_bayar', ['Belum Lunas', 'Cicilan']);
    }

    /**
     * Scope Tagihan yang lunas
     */
    public function scopeLunas($query)
    {
        return $query->where('status_bayar', 'Lunas');
    }

    /**
     * Scope berdasarkan Tahun Pelajaran
     */
    public function scopeTahun($query, $tahunId)
    {
        return $query->where('tahun_pelajaran_id', $tahunId);
    }
}
