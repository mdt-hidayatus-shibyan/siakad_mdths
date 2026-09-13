<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PembayaranTagihan extends Model
{
    protected $guarded = [];

    public function tagihanMurids()
    {
        return $this->hasMany(TagihanMurid::class, 'pembayaran_tagihan_id');
    }

    public function tagihanWaliMurids()
    {
        return $this->hasMany(TagihanWaliMurid::class, 'pembayaran_tagihan_id');
    }

    public function pengaturanTagihan()
    {
        return $this->belongsTo(PengaturanTagihan::class, 'pengaturan_tagihan_id');
    }
}
