<?php

namespace App\Models;

use App\Models\Kepengurusan\Anggota;
use App\Traits\HasEncryptedSensitiveData;
use Illuminate\Database\Eloquent\Model;

class Ustadz extends Model
{
    use HasEncryptedSensitiveData;

    protected $guarded = ['id'];
    protected $encryptedFields = ['nik'];

    protected static function boot()
    {
        parent::boot();

        // Event creating: Berjalan TEPAT SEBELUM data disimpan ke database
        static::creating(function ($ustadz) {
            // 1. Generate kode ustadz otomatis jika belum diisi
            if (empty($ustadz->kode_ustadz)) {
                $ustadz->kode_ustadz = static::generateKodeUstadz();
            }

            // 2. Generate NIGM otomatis jika belum diisi
            if (empty($ustadz->nigm)) {
                $ustadz->nigm = static::generateNigm();
            }
        });
    }

    /**
     * Generate Nomor Induk Guru Madrasah (NIGM) Otomatis Berurutan
     */
    public static function generateNigm(): string
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $prefixTahun = '1447';

        if ($tahunAktif && !empty($tahunAktif->nama_hijriyah)) {
            $parts = explode('-', $tahunAktif->nama_hijriyah);
            $cleanYear = preg_replace('/[^0-9]/', '', $parts[0] ?? '');
            if (!empty($cleanYear)) {
                $prefixTahun = $cleanYear;
            }
        }

        $prefix = $prefixTahun . '1';

        // Ambil NIGM numerik tertinggi dengan prefix yang sama
        $lastNigm = static::where('nigm', 'LIKE', $prefix . '%')
            ->orderByRaw('CAST(nigm AS UNSIGNED) DESC')
            ->value('nigm');

        if ($lastNigm && is_numeric($lastNigm)) {
            $nextNumber = (int) $lastNigm + 1;
            $candidate = (string) $nextNumber;
        } else {
            $candidate = $prefix . '001';
        }

        // Pastikan tidak tabrakan dengan data unik lain
        while (static::where('nigm', $candidate)->exists()) {
            $candidate = (string) (((int) $candidate) + 1);
        }

        return $candidate;
    }

    /**
     * Generate Kode Ustadz Alfabetis Otomatis (A, B, ... Z, AA, AB, dst)
     */
    public static function generateKodeUstadz(): string
    {
        $lastUstadz = static::orderBy('id', 'desc')->first();

        if (!$lastUstadz || empty($lastUstadz->kode_ustadz)) {
            return 'A';
        }

        $nextKode = $lastUstadz->kode_ustadz;
        $nextKode++;

        while (static::where('kode_ustadz', $nextKode)->exists()) {
            $nextKode++;
        }

        return $nextKode;
    }

    public static function getTandaTanganByWaliRuangan($ruangan_id)
    {
        return self::whereHas('ruangans', function ($query) use ($ruangan_id) {
            $query->where('id', $ruangan_id);
        })->first();
    }


    public function anggota()
    {
        return $this->hasOne(Anggota::class, 'ustadz_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ruangans()
    {
        return $this->hasMany(Ruangan::class, 'ustadz_id');
    }

    public function jadwalPelajarans()
    {
        return $this->belongsToMany(JadwalPelajaran::class, 'jadwal_pelajaran_ustadz', 'ustadz_id', 'jadwal_pelajaran_id')
            ->withPivot('peran')
            ->withTimestamps();
    }

    public function directJadwalPelajarans()
    {
        return $this->hasMany(JadwalPelajaran::class, 'ustadz_id');
    }

    public function tabungans()
    {
        return $this->hasMany(\App\Models\Tabungan\Tabungan::class, 'ustadz_id');
    }

    /**
     * Accessor alias nama agar kompatibel untuk pemanggilan $ustadz->nama
     */
    public function getNamaAttribute()
    {
        return $this->nama_lengkap ?? null;
    }

    /**
     * Accessor URL foto ustadz yang konsisten untuk Web & Mobile
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
