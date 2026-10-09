<?php

namespace App\Models\KasRuangan;

use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengeluaranKasRuangan extends Model
{
    use HasFactory;

    protected $table = 'pengeluaran_kas_ruangans';

    protected $fillable = [
        'ruangan_id',
        'judul',
        'kategori',
        'nominal',
        'tanggal_pengeluaran',
        'keterangan',
        'bukti_nota',
        'diinput_oleh',
    ];

    protected $casts = [
        'tanggal_pengeluaran' => 'date',
        'nominal' => 'integer',
    ];

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'diinput_oleh');
    }
}

