<?php

namespace App\Models\Ikhtibar;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DivisiIkhtibar extends Model
{
    use HasFactory;

    protected $table = 'divisi_ikhtibars';

    protected $fillable = [
        'kegiatan_ikhtibar_id',
        'kode_divisi',
        'nama_divisi',
        'deskripsi_tugas',
        'nominal_pagu',
        'status_pagu',
        'disetujui_oleh',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'nominal_pagu' => 'decimal:2',
        'is_active'    => 'boolean',
    ];

    public function kegiatanIkhtibar()
    {
        return $this->belongsTo(KegiatanIkhtibar::class, 'kegiatan_ikhtibar_id');
    }

    public function disetujuiOlehUser()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function panitias()
    {
        return $this->hasMany(PanitiaIkhtibar::class, 'divisi_ikhtibar_id');
    }

    public function koordinator()
    {
        return $this->hasOne(PanitiaIkhtibar::class, 'divisi_ikhtibar_id')
            ->where('jabatan', 'Koordinator Divisi')
            ->where('is_active', true);
    }

    /**
     * Penanggung Jawab / Koordinator Unit atau Pos BPH (Ketua, Sekretaris, Bendahara)
     */
    public function getKoordinatorTampilAttribute()
    {
        // 1. Cek personel langsung yang terikat ke divisi ini
        $personel = PanitiaIkhtibar::where('divisi_ikhtibar_id', $this->id)
            ->where('is_active', true)
            ->with('ustadz')
            ->orderByRaw("FIELD(jabatan, 'Ketua', 'Wakil Ketua', 'Sekretaris', 'Wakil Sekretaris', 'Bendahara', 'Wakil Bendahara', 'Koordinator Divisi', 'Anggota Divisi') ASC")
            ->first();

        if ($personel) {
            return $personel;
        }

        // 2. Jika pos ini adalah pos BPH, otomatis tautkan dengan panitia BPH terkait
        $kegiatanId = $this->kegiatan_ikhtibar_id;
        $kode = strtoupper($this->kode_divisi);
        $nama = strtolower($this->nama_divisi);

        if (str_contains($kode, 'KETUA') || str_contains($nama, 'ketua') || str_contains($nama, 'pimpinan')) {
            return PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatanId)
                ->where('kelompok_jabatan', 'BPH')
                ->whereIn('jabatan', ['Ketua', 'Wakil Ketua'])
                ->where('is_active', true)
                ->with('ustadz')
                ->orderByRaw("FIELD(jabatan, 'Ketua', 'Wakil Ketua') ASC")
                ->first();
        }

        if (str_contains($kode, 'SEKRE') || str_contains($nama, 'sekretaris') || str_contains($nama, 'kesekretariatan')) {
            return PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatanId)
                ->where('kelompok_jabatan', 'BPH')
                ->whereIn('jabatan', ['Sekretaris', 'Wakil Sekretaris'])
                ->where('is_active', true)
                ->with('ustadz')
                ->orderByRaw("FIELD(jabatan, 'Sekretaris', 'Wakil Sekretaris') ASC")
                ->first();
        }

        if (str_contains($kode, 'BENDA') || str_contains($nama, 'bendahara') || str_contains($nama, 'kebendaharaan')) {
            return PanitiaIkhtibar::where('kegiatan_ikhtibar_id', $kegiatanId)
                ->where('kelompok_jabatan', 'BPH')
                ->whereIn('jabatan', ['Bendahara', 'Wakil Bendahara'])
                ->where('is_active', true)
                ->with('ustadz')
                ->orderByRaw("FIELD(jabatan, 'Bendahara', 'Wakil Bendahara') ASC")
                ->first();
        }

        return $this->koordinator;
    }

    /**
     * Cek apakah pos ini merupakan Pos BPH Inti (Ketua, Sekretaris, Bendahara)
     */
    public function getIsBphAttribute(): bool
    {
        $kode = strtoupper($this->kode_divisi);
        $nama = strtolower($this->nama_divisi);

        return str_starts_with($kode, 'BPH')
            || str_contains($kode, 'KETUA') || str_contains($nama, 'ketua') || str_contains($nama, 'pimpinan')
            || str_contains($kode, 'SEKRE') || str_contains($nama, 'sekretaris') || str_contains($nama, 'kesekretariatan')
            || str_contains($kode, 'BENDA') || str_contains($nama, 'bendahara') || str_contains($nama, 'kebendaharaan');
    }

    /**
     * Label Kategori Pos: BPH Inti vs Divisi
     */
    public function getKategoriTampilAttribute(): string
    {
        return $this->is_bph ? 'BPH Inti' : 'Divisi';
    }

    /**
     * Warna badge status pagu
     */
    public function getStatusPaguBadgeColorAttribute(): string
    {
        return match ($this->status_pagu) {
            'Disetujui' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20',
            'Dikunci'   => 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border border-zinc-500/20',
            default     => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20',
        };
    }

    public function penyaluranDanas()
    {
        return $this->hasMany(PenyaluranDanaIkhtibar::class, 'divisi_ikhtibar_id');
    }

    public function pengeluarans()
    {
        return $this->hasMany(PengeluaranIkhtibar::class, 'divisi_ikhtibar_id');
    }

    /**
     * Total Dana Pagu yang sudah disalurkan/dicairkan ke divisi
     */
    public function getTotalTersalurkanAttribute(): float
    {
        return (float) $this->penyaluranDanas()
            ->where('jenis_transaksi', 'Penyaluran Pagu')
            ->sum('nominal');
    }

    /**
     * Total Dana Sisa Kas yang sudah ditarik kembali ke kas utama
     */
    public function getTotalDitarikKembaliAttribute(): float
    {
        return (float) $this->penyaluranDanas()
            ->where('jenis_transaksi', 'Penarikan Sisa Kas')
            ->sum('nominal');
    }

    /**
     * Total Realisasi Pengeluaran SPJ yang sudah dicatat
     */
    public function getTotalRealisasiSpjAttribute(): float
    {
        return (float) $this->pengeluarans()->sum('nominal');
    }

    /**
     * Sisa Pagu Belum Dicairkan (Plafon Pagu - Dana Tersalurkan)
     */
    public function getSisaPaguBelumCairAttribute(): float
    {
        return max(0, (float) $this->nominal_pagu - $this->total_tersalurkan);
    }

    /**
     * Sisa Kas Fisik di Tangan Divisi ((Tersalurkan - Ditarik) - Realisasi SPJ)
     */
    public function getSisaKasDiDivisiAttribute(): float
    {
        $danaBersihDiDivisi = $this->total_tersalurkan - $this->total_ditarik_kembali;
        return max(0, $danaBersihDiDivisi - $this->total_realisasi_spj);
    }
}

