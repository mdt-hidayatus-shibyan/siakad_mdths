<?php

namespace App\Models\Ujian;

use App\Models\Murid;
use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PresensiUjian extends Model
{
    protected $table = 'presensi_ujians';

    protected $fillable = [
        'ujian_id',
        'jadwal_ujian_id',
        'ruangan_id',
        'ruangan_imni_id',
        'tanggal_ujian',
        'murid_id',
        'status',
        'catatan',
        'diinput_oleh',
    ];

    protected $casts = [
        'tanggal_ujian' => 'date',
    ];

    public function ujian()
    {
        return $this->belongsTo(Ujian::class, 'ujian_id');
    }

    public function jadwalUjian()
    {
        return $this->belongsTo(JadwalUjian::class, 'jadwal_ujian_id');
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    public function ruanganImni()
    {
        return $this->belongsTo(RuanganImni::class, 'ruangan_imni_id');
    }

    public function murid()
    {
        return $this->belongsTo(Murid::class, 'murid_id');
    }

    public function penginput()
    {
        return $this->belongsTo(User::class, 'diinput_oleh');
    }
}
