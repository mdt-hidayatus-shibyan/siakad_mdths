<?php

namespace App\Models\Ikhtibar;

use App\Models\TahunPelajaran;
use App\Models\Ustadz;
use App\Traits\HasEncryptedSensitiveData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PanitiaIkhtibar extends Model
{
    use HasFactory, HasEncryptedSensitiveData;

    protected $table = 'panitia_ikhtibars';

    protected $encryptedFields = ['nik'];
    protected $hashedFields = [];

    protected $fillable = [
        'kegiatan_ikhtibar_id',
        'tahun_pelajaran_id',
        'kelompok_jabatan',
        'jabatan',
        'divisi_ikhtibar_id',
        'tipe_personel',
        'ustadz_id',
        'nik',
        'nama_lengkap',
        'jenis_kelamin',
        'no_hp',
        'unsur_asal',
        'alamat',
        'foto',
        'tanda_tangan',
        'is_active',
        'keterangan',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function kegiatanIkhtibar()
    {
        return $this->belongsTo(KegiatanIkhtibar::class, 'kegiatan_ikhtibar_id');
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function divisi()
    {
        return $this->belongsTo(DivisiIkhtibar::class, 'divisi_ikhtibar_id');
    }

    public function ustadz()
    {
        return $this->belongsTo(Ustadz::class, 'ustadz_id');
    }

    /**
     * Nama Lengkap Pintar (Dari master Ustadz jika internal, atau kolom mandiri jika eksternal)
     */
    public function getNamaTampilAttribute(): string
    {
        if ($this->tipe_personel === 'ustadz' && $this->ustadz) {
            return $this->ustadz->nama_lengkap;
        }
        return $this->nama_lengkap ?? '-';
    }

    /**
     * Nomor Identitas (NIGM untuk Ustadz / NIK atau Unsur untuk Eksternal)
     */
    public function getIdentitasTampilAttribute(): string
    {
        if ($this->tipe_personel === 'ustadz' && $this->ustadz) {
            return 'NIGM: ' . ($this->ustadz->nigm ?? '-');
        }
        return 'Unsur: ' . ($this->unsur_asal ?? 'Eksternal');
    }

    /**
     * Nomor HP Pintar
     */
    public function getNoHpTampilAttribute(): ?string
    {
        return ($this->tipe_personel === 'ustadz' && $this->ustadz)
            ? $this->ustadz->no_hp
            : $this->no_hp;
    }

    /**
     * Foto Pintar
     */
    public function getFotoUtamaAttribute(): ?string
    {
        if ($this->tipe_personel === 'ustadz' && $this->ustadz && $this->ustadz->foto) {
            return $this->ustadz->foto;
        }
        return $this->foto;
    }

    /**
     * Tanda Tangan Pintar
     */
    public function getTtdUtamaAttribute(): ?string
    {
        if ($this->tipe_personel === 'ustadz' && $this->ustadz && $this->ustadz->tanda_tangan) {
            return $this->ustadz->tanda_tangan;
        }
        return $this->tanda_tangan;
    }

    /**
     * Periksa Apakah Personel Termasuk Jajaran BPH Inti
     */
    public function isBphInti(): bool
    {
        return $this->kelompok_jabatan === 'BPH' && in_array($this->jabatan, [
            'Ketua',
            'Wakil Ketua',
            'Sekretaris',
            'Wakil Sekretaris',
            'Bendahara',
            'Wakil Bendahara',
        ], true);
    }
}

