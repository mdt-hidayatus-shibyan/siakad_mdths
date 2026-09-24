<?php

namespace App\Models;

use App\Models\JadwalPelajaran;
use App\Models\KasRuangan\PembayaranKasRuangan;
use App\Models\KasRuangan\PengaturanKasRuangan;
use App\Models\KasRuangan\SetoranKasRuangan;
use App\Models\Level;
use App\Models\Murid;
use App\Models\TahunPelajaran;
use App\Models\Ustadz;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Ruangan extends Model
{
    protected $guarded = ['id'];

    public function scopeBerdasarkanHakAkses(Builder $query)
    {
        $user = auth()->user();

        if (!$user) {
            return $query->whereRaw('0 = 1');
        }

        if ($user->hasAnyRole(['administrator', 'petugas-koperasi'])) {
            return $query;
        }
        if ($user->hasRole('staff')) {
            return $query->whereHas('level', function ($q) use ($user) {
                $q->where('tingkat_id', $user->tingkat_id);
            });
        }

        if ($user->hasRole('ustadz')) {
            $ustadz_id = $user->ustadz->id ?? null;

            $isWaliRuangan = Ruangan::where('ustadz_id', $ustadz_id)->exists();

            if ($isWaliRuangan) {
                return $query->where('ustadz_id', $ustadz_id);
            }

            return $query->whereHas('jadwalPelajarans', function ($q) use ($ustadz_id) {
                $q->where('ustadz_id', $ustadz_id);
            });
        }

        return $query->whereRaw('0 = 1');
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function gedung()
    {
        return $this->belongsTo(Gedung::class, 'gedung_id');
    }

    public function sarpras()
    {
        return $this->hasMany(Sarpras::class, 'ruangan_id');
    }

    public function waliRuangan()
    {
        return $this->belongsTo(Ustadz::class, 'ustadz_id');
    }

    public function ustadz()
    {
        return $this->belongsTo(Ustadz::class, 'ustadz_id');
    }

    public function murids()
    {
        return $this->belongsToMany(Murid::class, 'murid_ruangans', 'ruangan_id', 'murid_id')
            ->withTimestamps();
    }

    public function jadwalPelajarans()
    {
        return $this->hasMany(JadwalPelajaran::class, 'ruangan_id');
    }

    public function pengaturanKas()
    {
        return $this->hasOne(PengaturanKasRuangan::class);
    }

    // 1 Ruangan mencatat banyak cicilan pembayaran dari murid
    public function pembayaranKas()
    {
        return $this->hasMany(PembayaranKasRuangan::class);
    }

    // 1 Ruangan bisa berkali-kali menyetor ke Madrasah
    public function setoranKas()
    {
        return $this->hasMany(SetoranKasRuangan::class);
    }

    public function tabungans()
    {
        return $this->hasMany(\App\Models\Tabungan\Tabungan::class, 'ruangan_id');
    }

    public function presensiUjians()
    {
        return $this->hasMany(\App\Models\Ujian\PresensiUjian::class, 'ruangan_id');
    }
}
