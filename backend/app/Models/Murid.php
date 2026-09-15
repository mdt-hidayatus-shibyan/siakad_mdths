<?php

namespace App\Models;

use App\Models\KasRuangan\PembayaranKasRuangan;
use App\Traits\HasEncryptedSensitiveData;
use Illuminate\Database\Eloquent\Model;

class Murid extends Model
{
    use HasEncryptedSensitiveData;

    protected $guarded = ['id'];
    protected $encryptedFields = ['nik', 'nik_ayah', 'nik_ibu'];

    // Relasi balik ke Wali Murid
    public function waliMurid()
    {
        return $this->belongsTo(WaliMurid::class, 'wali_murid_id');
    }
    public function ruangans()
    {
        return $this->belongsToMany(Ruangan::class, 'murid_ruangans', 'murid_id', 'ruangan_id')
            ->withPivot('tahun_pelajaran_id')
            ->withTimestamps();
    }

    /**
     * Accessor untuk mendapatkan nama ruangan/kelas aktif murid
     */
    public function getNamaRuanganAktifAttribute()
    {
        if ($this->relationLoaded('ruangans') && $this->ruangans->isNotEmpty()) {
            return $this->ruangans->pluck('nama_ruangan')->implode(', ');
        }
        return $this->ruanganMasuk->nama_ruangan ?? '-';
    }
    /**
     * Relasi ke Tahun Pelajaran saat pertama masuk
     */
    public function tahunMasuk()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_masuk');
    }

    /**
     * Relasi ke Jenjang/Level saat pertama masuk
     */
    public function levelMasuk()
    {
        return $this->belongsTo(Level::class, 'level_masuk');
    }

    /**
     * Relasi ke Ruangan tempat belajar saat ini
     */
    public function ruanganMasuk()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_masuk');
    }

    public function pembayaranKas()
    {
        return $this->hasMany(PembayaranKasRuangan::class, 'murid_id');
    }

    public function presensiUjians()
    {
        return $this->hasMany(\App\Models\Ujian\PresensiUjian::class, 'murid_id');
    }

    public function tabungans()
    {
        return $this->hasMany(\App\Models\Tabungan\Tabungan::class, 'murid_id');
    }

    public function tabunganUtama()
    {
        return $this->hasOne(\App\Models\Tabungan\Tabungan::class, 'murid_id')->oldestOfMany();
    }

    public function pesertaUjianAlqurans()
    {
        return $this->hasMany(\App\Models\UjianAlquran\PesertaUjianAlquran::class, 'murid_id');
    }

    /**
     * Accessor URL foto murid yang konsisten untuk Web & Mobile
     */
    public function getFotoUrlAttribute()
    {
        if (!$this->foto) {
            return null;
        }

        if (str_starts_with($this->foto, 'http://') || str_starts_with($this->foto, 'https://')) {
            return $this->foto;
        }

        if (str_starts_with($this->foto, 'storage/')) {
            return asset($this->foto);
        }

        return asset('storage/' . $this->foto);
    }
}
