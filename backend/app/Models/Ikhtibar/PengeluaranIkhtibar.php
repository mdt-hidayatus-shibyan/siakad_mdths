<?php

namespace App\Models\Ikhtibar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengeluaranIkhtibar extends Model
{
    use HasFactory;

    protected $table = 'pengeluaran_ikhtibars';

    protected $fillable = [
        'kegiatan_ikhtibar_id',
        'divisi_ikhtibar_id',
        'kode_spj',
        'judul_pengeluaran',
        'rincian_item',
        'nominal',
        'tanggal_nota',
        'nama_toko_penerima',
        'metode_pembayaran',
        'bukti_nota',
        'diinput_oleh_id',
        'status_verifikasi',
        'diverifikasi_oleh_id',
        'catatan_verifikasi',
    ];

    protected $casts = [
        'nominal'      => 'decimal:2',
        'tanggal_nota' => 'date',
    ];

    public function kegiatanIkhtibar()
    {
        return $this->belongsTo(KegiatanIkhtibar::class, 'kegiatan_ikhtibar_id');
    }

    public function divisi()
    {
        return $this->belongsTo(DivisiIkhtibar::class, 'divisi_ikhtibar_id');
    }

    public function diinputOleh()
    {
        return $this->belongsTo(PanitiaIkhtibar::class, 'diinput_oleh_id');
    }

    public function diverifikasiOleh()
    {
        return $this->belongsTo(PanitiaIkhtibar::class, 'diverifikasi_oleh_id');
    }

    /**
     * Generator Kode Transaksi SPJ
     */
    public static function generateKodeSpj($kodeDivisi = 'BPH', $tahun = null): string
    {
        $tahun = $tahun ?? date('Y');
        $cleanDiv = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $kodeDivisi));
        $count = static::whereYear('tanggal_nota', $tahun)->count() + 1;
        return sprintf('SPJ-IKH/%s/%s/%04d', $tahun, $cleanDiv, $count);
    }
}

