<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanTagihan extends Model
{
    protected $table = 'pengaturan_tagihans';
    protected $fillable = [
        'tahun_pelajaran_id',
        'kode_tagihan',
        'level_id',
        'nama_tagihan',
        'tipe',
        'sasaran',
        'nominal'
    ];
    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }
    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id');
    }
    public function tagihanMurids()
    {
        return $this->hasMany(TagihanMurid::class, 'pengaturan_tagihan_id');
    }
    public function tagihanWaliMurids()
    {
        return $this->hasMany(TagihanWaliMurid::class, 'pengaturan_tagihan_id');
    }

    public function scopeWaliMurid($query)
    {
        return $query->where('sasaran', 'wali_murid');
    }

    public function scopeMurid($query)
    {
        return $query->where('sasaran', 'murid');
    }
}
