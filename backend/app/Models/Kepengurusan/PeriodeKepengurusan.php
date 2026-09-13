<?php

namespace App\Models\Kepengurusan;

use Illuminate\Database\Eloquent\Model;

class PeriodeKepengurusan extends Model
{
    protected $table = 'periode_kepengurusan';

    protected $fillable = [
        'nama_periode',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_seumur_hidup',
        'status_aktif',
    ];

    // Casts digunakan agar Laravel otomatis mengubah format data saat ditarik dari database
    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_seumur_hidup' => 'boolean',
        'status_aktif' => 'boolean',
    ];

    /**
     * Scope untuk periode yang aktif atau seumur hidup
     */
    public function scopeAktifAtauSeumurHidup($query)
    {
        return $query->where('status_aktif', true)->orWhere('is_seumur_hidup', true);
    }

    /**
     * Format teks masa bakti periode
     */
    public function getMasaBaktiTextAttribute()
    {
        if ($this->is_seumur_hidup) {
            return 'Seumur Hidup' . ($this->tanggal_mulai ? ' (Mulai ' . $this->tanggal_mulai->translatedFormat('d M Y') . ')' : '');
        }

        $mulai = $this->tanggal_mulai ? $this->tanggal_mulai->translatedFormat('d M Y') : '?';
        $selesai = $this->tanggal_selesai ? $this->tanggal_selesai->translatedFormat('d M Y') : 'Sekarang';

        return "{$mulai} - {$selesai}";
    }

    // Relasi ke tabel pengurus
    public function pengurus()
    {
        return $this->hasMany(Pengurus::class, 'periode_id');
    }
}
