<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPelajaran extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class);
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    /**
     * Ustadz utama / default (kolom ustadz_id)
     */
    public function ustadz()
    {
        return $this->belongsTo(Ustadz::class, 'ustadz_id');
    }

    /**
     * Semua Ustadz pengampu (Multi-Ustadz / Team Teaching)
     */
    public function ustadzs()
    {
        return $this->belongsToMany(Ustadz::class, 'jadwal_pelajaran_ustadz', 'jadwal_pelajaran_id', 'ustadz_id')
            ->withPivot(['is_utama', 'urutan', 'peran'])
            ->withTimestamps()
            ->orderByPivot('is_utama', 'desc')
            ->orderByPivot('urutan', 'asc');
    }

    /**
     * Scope jadwal untuk ustadz tertentu (baik sebagai ustadz utama maupun pengampu bersama)
     */
    public function scopeForUstadz($query, $ustadzId)
    {
        return $query->where(function ($q) use ($ustadzId) {
            $q->where('jadwal_pelajarans.ustadz_id', $ustadzId)
                ->orWhereHas('ustadzs', fn($sq) => $sq->where('ustadzs.id', $ustadzId));
        });
    }

    /**
     * Mengambil daftar ustadz pengampu (gabungan dari relasi pivot atau fallback ustadz utama)
     */
    public function getDaftarUstadzAttribute()
    {
        if ($this->relationLoaded('ustadzs') && $this->ustadzs->isNotEmpty()) {
            return $this->ustadzs;
        }

        $pivots = $this->ustadzs;
        if ($pivots && $pivots->isNotEmpty()) {
            return $pivots;
        }

        if ($this->ustadz) {
            return collect([$this->ustadz]);
        }

        return collect();
    }

    /**
     * String nama semua pengampu
     */
    public function getDaftarNamaPengampuAttribute()
    {
        $list = $this->daftar_ustadz;
        if ($list->isEmpty()) {
            return 'Belum Diatur';
        }
        return $list->pluck('nama_lengkap')->join(' • ');
    }
}
