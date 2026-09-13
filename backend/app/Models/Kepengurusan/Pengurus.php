<?php

namespace App\Models\Kepengurusan;

use App\Models\Tingkat;
use Illuminate\Database\Eloquent\Model;

class Pengurus extends Model
{
    protected $table = 'pengurus';

    protected $fillable = [
        'anggota_id',
        'jabatan_id',
        'periode_id',
        'tingkat_id',
        'no_sk',
    ];

    // Relasi kembali ke Anggota
    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'anggota_id');
    }

    // Relasi kembali ke Jabatan
    public function jabatan()
    {
        return $this->belongsTo(JabatanPengurus::class, 'jabatan_id');
    }

    // Relasi kembali ke Periode
    public function periode()
    {
        return $this->belongsTo(PeriodeKepengurusan::class, 'periode_id');
    }

    public function tingkat()
    {
        return $this->belongsTo(Tingkat::class, 'tingkat_id');
    }

    public static function getAktifByJabatan($keyword, $tingkat_id = null)
    {
        return self::with(['anggota.ustadz', 'jabatan', 'periode', 'tingkat'])
            ->whereHas('periode', function ($query) {
                $query->where(function ($sub) {
                    $sub->where('status_aktif', 1)->orWhere('is_seumur_hidup', 1);
                });
            })
            ->whereHas('jabatan', function ($query) use ($keyword) {
                $query->where('nama_jabatan', 'LIKE', '%' . $keyword . '%');
            })
            ->when($tingkat_id, function ($query, $tingkat_id) {
                $query->where('tingkat_id', $tingkat_id);
            }, function ($query) {
                $query->whereNull('tingkat_id');
            })
            ->first();
    }
}
