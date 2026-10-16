<?php

namespace App\Models\Ikhtibar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenyaluranDanaIkhtibar extends Model
{
    use HasFactory;

    protected $table = 'penyaluran_dana_ikhtibars';

    protected $fillable = [
        'kegiatan_ikhtibar_id',
        'divisi_ikhtibar_id',
        'kode_penyaluran',
        'jenis_transaksi',
        'kategori_penarikan',
        'divisi_tujuan_realokasi_id',
        'termin_ke',
        'nominal',
        'tanggal_penyaluran',
        'metode_penyaluran',
        'penerima_panitia_id',
        'disalurkan_oleh_id',
        'disetujui_oleh_id',
        'bukti_serah_terima',
        'catatan',
    ];

    protected $casts = [
        'nominal'            => 'decimal:2',
        'tanggal_penyaluran' => 'date',
    ];

    public function kegiatanIkhtibar()
    {
        return $this->belongsTo(KegiatanIkhtibar::class, 'kegiatan_ikhtibar_id');
    }

    public function divisi()
    {
        return $this->belongsTo(DivisiIkhtibar::class, 'divisi_ikhtibar_id');
    }

    public function divisiTujuanRealokasi()
    {
        return $this->belongsTo(DivisiIkhtibar::class, 'divisi_tujuan_realokasi_id');
    }

    public function penerima()
    {
        return $this->belongsTo(PanitiaIkhtibar::class, 'penerima_panitia_id');
    }

    public function disalurkanOleh()
    {
        return $this->belongsTo(PanitiaIkhtibar::class, 'disalurkan_oleh_id');
    }

    public function disetujuiOleh()
    {
        return $this->belongsTo(PanitiaIkhtibar::class, 'disetujui_oleh_id');
    }

    /**
     * Generator Kode Transaksi Penyaluran
     */
    public static function generateKodePenyaluran($tahun = null): string
    {
        $tahun = $tahun ?? date('Y');
        $count = static::whereYear('tanggal_penyaluran', $tahun)->where('jenis_transaksi', 'Penyaluran Pagu')->count() + 1;
        return sprintf('DIST-IKH/%s/%04d', $tahun, $count);
    }

    /**
     * Generator Kode Transaksi Penarikan Sisa Pagu
     */
    public static function generateKodePenarikan($tahun = null): string
    {
        $tahun = $tahun ?? date('Y');
        $count = static::whereYear('tanggal_penyaluran', $tahun)->where('jenis_transaksi', '!=', 'Penyaluran Pagu')->count() + 1;
        return sprintf('TARIK-IKH/%s/%04d', $tahun, $count);
    }
}

