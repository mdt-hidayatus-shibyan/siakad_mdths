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

            // Jika kode belum diisi secara manual
            if (empty($ustadz->kode_ustadz)) {

                // Cari data ustadz terakhir berdasarkan urutan ID
                $lastustadz = static::orderBy('id', 'desc')->first();

                if (!$lastustadz || empty($lastustadz->kode_ustadz)) {
                    // Jika ini adalah ustadz pertama di madrasah, mulai dari A
                    $ustadz->kode_ustadz = 'A';
                } else {
                    // Ambil huruf terakhir, lalu increment (Contoh: 'Z' otomatis jadi 'AA')
                    $nextKode = $lastustadz->kode_ustadz;
                    $nextKode++;

                    $ustadz->kode_ustadz = $nextKode;
                }
            }
        });
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
