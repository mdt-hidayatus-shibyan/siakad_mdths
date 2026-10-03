<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TahunPelajaran extends Model
{
    protected $guarded = ['id'];

    public function scopeByTahun($query, $tahunId = null)
    {
        if ($tahunId) {
            return $query->where('tahun_pelajaran_id', $tahunId);
        }
        $activeTahun = TahunPelajaran::where('is_active', true)->value('id');
        return $query->where('tahun_pelajaran_id', $activeTahun);
    }

    public function scopeTahunAktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }


    public function semesters()
    {
        return $this->hasMany(Semester::class);
    }
    public function bulanHijriyahs()
    {
        return $this->hasMany(BulanHijriyah::class, 'tahun_pelajaran_id');
    }
    public function pengaturanAkademik()
    {
        return $this->hasOne(PengaturanAkademik::class);
    }

    public function panitiaImnis()
    {
        return $this->hasMany(\App\Models\Ujian\PanitiaImni::class, 'tahun_pelajaran_id');
    }
}
