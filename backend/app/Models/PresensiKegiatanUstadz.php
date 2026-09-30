<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresensiKegiatanUstadz extends Model
{
    protected $table = 'presensi_kegiatan_ustadzs';

    protected $fillable = [
        'kalendar_pendidikan_id',
        'tanggal',
        'sesi',
        'ustadz_id',
        'status',
        'waktu_checkin',
        'keterangan',
        'diinput_oleh_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_checkin' => 'datetime',
    ];

    public function kalendarPendidikan()
    {
        return $this->belongsTo(KalendarPendidikan::class);
    }

    public function ustadz()
    {
        return $this->belongsTo(Ustadz::class);
    }

    public function diinputOleh()
    {
        return $this->belongsTo(User::class, 'diinput_oleh_id');
    }
}
