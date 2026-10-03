<?php

namespace App\Models\Ujian;

use App\Models\Semester;
use App\Models\TahunPelajaran;
use App\Models\Tingkat;
use Illuminate\Database\Eloquent\Model;

class Ujian extends Model
{
    protected $fillable = [
        'tahun_pelajaran_id',
        'nama_ujian',
        'semester_id',
        'tingkat_id',
        'tipe_ujian',
        'tanggal_mulai',
        'tanggal_selesai'
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function tingkat()
    {
        return $this->belongsTo(Tingkat::class, 'tingkat_id');
    }

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

    public static function isKelasAkhir(string|null $levelNama): bool
    {
        return in_array($levelNama, ['3 TPQ', '6 IBT', '3 TSA']);
    }

    public function scopeBerlakuUntukLevel($query, $level)
    {
        if (is_numeric($level)) {
            $level = \App\Models\Level::find($level);
        }
        if (!$level) {
            return $query;
        }

        $levelNama = $level->nama_level ?? '';
        $isKelasAkhir = self::isKelasAkhir($levelNama);

        $query->where('tipe_ujian', '!=', 'IMNI');

        if ($isKelasAkhir) {
            $query->where('tipe_ujian', 'IMDA 1');
        } else {
            $query->whereIn('tipe_ujian', ['IMDA 1', 'IMDA 2', 'IMDA 3']);
        }

        if ($level->tingkat_id) {
            $query->where(function ($q) use ($level) {
                $q->whereNull('tingkat_id')
                  ->orWhere('tingkat_id', $level->tingkat_id);
            });
        }

        return $query;
    }

    public function isBerlakuUntukLevel($level): bool
    {
        if (is_numeric($level)) {
            $level = \App\Models\Level::find($level);
        }
        if (!$level) {
            return true;
        }

        if ($this->tipe_ujian === 'IMNI') {
            return false;
        }

        $levelNama = $level->nama_level ?? '';
        $isKelasAkhir = self::isKelasAkhir($levelNama);

        if ($isKelasAkhir && in_array($this->tipe_ujian, ['IMDA 2', 'IMDA 3'])) {
            return false;
        }

        if ($this->tingkat_id !== null && $level->tingkat_id !== null && (int)$this->tingkat_id !== (int)$level->tingkat_id) {
            return false;
        }

        return true;
    }

    public function pengecualianUjians()
    {
        return $this->hasMany(PengecualianUjian::class, 'ujian_id');
    }
}
