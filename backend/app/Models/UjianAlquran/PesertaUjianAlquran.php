<?php

namespace App\Models\UjianAlquran;

use App\Models\Murid;
use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PesertaUjianAlquran extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'jumlah_khoto_jali'     => 'integer',
        'jumlah_khoto_khofi'    => 'integer',
        'poin_pengurangan_jali' => 'float',
        'poin_pengurangan_khofi' => 'float',
        'total_pengurangan'     => 'float',
        'nilai_akhir'           => 'float',
        'tanggal_lulus'         => 'date',
    ];

    public function ujianAlquran()
    {
        return $this->belongsTo(UjianAlquran::class, 'ujian_alquran_id');
    }

    public function murid()
    {
        return $this->belongsTo(Murid::class, 'murid_id');
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    public function penguji()
    {
        return $this->belongsTo(User::class, 'diuji_oleh');
    }

    public function penilaians()
    {
        return $this->hasMany(PenilaianJuriAlquran::class, 'peserta_id');
    }

    /**
     * Hitung Predikat Kelulusan Al-Qur'an
     */
    public function getPredikatAttribute(): string
    {
        if ($this->status_kelulusan !== 'Lulus') {
            return 'Kurang / Rosib (Tidak Lulus)';
        }

        $nilai = (float) $this->nilai_akhir;

        if ($nilai >= 90) {
            return 'Istimewa (Mumtaz)';
        } elseif ($nilai >= 80) {
            return 'Sangat Baik (Jayyid Jiddan)';
        } elseif ($nilai >= 70) {
            return 'Baik (Jayyid)';
        } elseif ($nilai > 55) {
            return 'Cukup (Maqbul)';
        } else {
            return 'Kurang (Rosib)';
        }
    }

    public function getPredikatArabAttribute(): string
    {
        if ($this->status_kelulusan !== 'Lulus') {
            return 'راسب';
        }

        $nilai = (float) $this->nilai_akhir;

        if ($nilai >= 90) {
            return 'ممتاز';
        } elseif ($nilai >= 80) {
            return 'جيد جدا';
        } elseif ($nilai >= 70) {
            return 'جيد';
        } elseif ($nilai > 55) {
            return 'مقبول';
        } else {
            return 'راسب';
        }
    }

    /**
     * Accessor Nomor Ijazah Al-Qur'an
     */
    public function getNomorIjazahAlquranAttribute(): ?string
    {
        if (!empty($this->no_ijazah)) {
            return $this->no_ijazah;
        }

        if ($this->status_kelulusan === 'Lulus') {
            $thn = $this->ujianAlquran?->tahunPelajaran?->nama_masehi ?? date('Y');
            return "IJZ.QURAN/MDT-HS/{$thn}/" . str_pad($this->id, 4, '0', STR_PAD_LEFT);
        }

        return null;
    }

    /**
     * Accessor Nomor SK Al-Qur'an
     */
    public function getNomorSkAlquranAttribute(): ?string
    {
        if (!empty($this->no_sk)) {
            return $this->no_sk;
        }

        $bulanRomawi = ['1' => 'I', '2' => 'II', '3' => 'III', '4' => 'IV', '5' => 'V', '6' => 'VI', '7' => 'VII', '8' => 'VIII', '9' => 'IX', '10' => 'X', '11' => 'XI', '12' => 'XII'];
        $bln = $bulanRomawi[date('n')];
        $thn = date('Y');

        return str_pad($this->id, 3, '0', STR_PAD_LEFT) . "/SK.QURAN/IBT/MDT-HS/{$bln}/{$thn}";
    }
}
