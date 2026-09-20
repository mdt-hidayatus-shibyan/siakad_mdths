<?php

namespace App\Models\Ujian;

use App\Models\Semester;
use App\Models\TahunPelajaran;
use Illuminate\Database\Eloquent\Model;

class Ujian extends Model
{
    protected $fillable = ['tahun_pelajaran_id', 'nama_ujian', 'semester_id', 'tipe_ujian', 'tanggal_mulai', 'tanggal_selesai'];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function semester_relasi()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function jadwalUjians()
    {
        return $this->hasMany(JadwalUjian::class, 'ujian_id');
    }

    public function presensiUjians()
    {
        return $this->hasMany(PresensiUjian::class, 'ujian_id');
    }

    public function dispensasiUjians()
    {
        return $this->hasMany(DispensasiUjian::class, 'ujian_id');
    }

    public function pengecualianUjians()
    {
        return $this->hasMany(PengecualianUjian::class, 'ujian_id');
    }
}
