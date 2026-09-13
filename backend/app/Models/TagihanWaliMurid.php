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
        'status_bayar',
        'pembayaran_tagihan_id',
        'keterangan',
    ];

    protected $casts = [
        'nominal_tagihan' => 'integer',
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
     * Scope Tagihan yang belum lunas
     */
    public function scopeBelumLunas($query)
    {
        return $query->where('status_bayar', 'Belum Lunas');
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
