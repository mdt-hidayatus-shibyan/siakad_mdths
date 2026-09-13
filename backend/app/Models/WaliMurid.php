<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WaliMurid extends Model
{

    protected $guarded = ['id'];

    protected $hidden = [
        'pin',
    ];

    protected $casts = [
        'is_pin_changed' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->no_registrasi)) {
                $model->no_registrasi = self::generateNoRegistrasi();
            }
        });
    }

    /**
     * Generator nomor registrasi keluarga/wali murid unik & tahan benturan
     */
    public static function generateNoRegistrasi(): string
    {
        $maxNo = DB::table('wali_murids')
            ->whereRaw("no_registrasi REGEXP '^[0-9]+$'")
            ->max(DB::raw('CAST(no_registrasi AS UNSIGNED)'));

        $nextNo = $maxNo ? ($maxNo + 1) : 50001;

        while (self::where('no_registrasi', (string) $nextNo)->exists()) {
            $nextNo++;
        }

        return (string) $nextNo;
    }

    // Relasi ke tabel Kampung
    public function kampung()
    {
        return $this->belongsTo(Kampung::class, 'kampung_id');
    }

    // Relasi ke tabel Murid
    public function murids()
    {
        return $this->hasMany(Murid::class, 'wali_murid_id');
    }

    // Relasi ke tabel Tagihan Wali Murid (Per KK)
    public function tagihanWaliMurids()
    {
        return $this->hasMany(TagihanWaliMurid::class, 'wali_murid_id');
    }

    /**
     * Scope untuk Wali Murid yang memiliki murid berstatus aktif di tahun pelajaran tertentu
     */
    public function scopeAktifDiTahun($query, $tahunPelajaranId)
    {
        return $query->where('is_active', true)
            ->whereHas('murids', function ($q) use ($tahunPelajaranId) {
                $q->where('status', 'Aktif')
                    ->whereHas('ruangans', function ($rq) use ($tahunPelajaranId) {
                        if ($tahunPelajaranId) {
                            $rq->where('murid_ruangans.tahun_pelajaran_id', $tahunPelajaranId);
                        }
                    });
            });
    }
}
