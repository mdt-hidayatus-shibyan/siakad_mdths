<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresensiKegiatanMurid extends Model
{
    protected $table = 'presensi_kegiatan_murids';

    protected $fillable = [
        'kalendar_pendidikan_id',
        'tanggal',
        'sesi',
        'ruangan_id',
        'murid_id',
        'status',
        'catatan',
        'diinput_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function kalendarPendidikan()
    {
        return $this->belongsTo(KalendarPendidikan::class);
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class);
    }

    public function murid()
    {
        return $this->belongsTo(Murid::class);
    }

    public function diinputOleh()
    {
        return $this->belongsTo(User::class, 'diinput_oleh');
    }
}
