<?php

namespace App\Models\Kepengurusan;

use App\Models\Ustadz;
use App\Traits\HasEncryptedSensitiveData;
use Illuminate\Database\Eloquent\Model;

class Anggota extends Model
{
    use HasEncryptedSensitiveData;

    protected $table = 'anggota';

    protected $encryptedFields = ['nik'];

    protected $fillable = [
        'nik',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'no_hp',
        'alamat',
        'foto',
        'tanda_tangan',
        'ustadz_id',
    ];

    // Relasi ke tabel ustadzs
    public function ustadz()
    {
        return $this->belongsTo(Ustadz::class, 'ustadz_id');
    }

    // Relasi ke tabel pengurus (1 Anggota bisa memiliki banyak riwayat kepengurusan)
    public function riwayatKepengurusan()
    {
        return $this->hasMany(Pengurus::class, 'anggota_id');
    }

    // --- ACCESOR UNTUK FOTO PINTAR ---
    public function getFotoUtamaAttribute()
    {
        if ($this->ustadz && $this->ustadz->foto) {
            return $this->ustadz->foto;
        }
        return $this->foto;
    }

    // --- ACCESOR UNTUK TTD PINTAR ---
    public function getTtdUtamaAttribute()
    {
        if ($this->ustadz && $this->ustadz->tanda_tangan) {
            return $this->ustadz->tanda_tangan;
        }
        return $this->tanda_tangan;
    }
}
