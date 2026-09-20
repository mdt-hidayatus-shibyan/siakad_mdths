<?php

namespace App\Models\Ujian;

use App\Models\Murid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PengecualianUjian extends Model
{
    protected $table = 'pengecualian_ujians';

    protected $fillable = [
        'ujian_id',
        'murid_id',
        'alasan',
        'ditandai_oleh',
    ];

    public function ujian()
    {
        return $this->belongsTo(Ujian::class, 'ujian_id');
    }

    public function murid()
    {
        return $this->belongsTo(Murid::class, 'murid_id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'ditandai_oleh');
    }
}
