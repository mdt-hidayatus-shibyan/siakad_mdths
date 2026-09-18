<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatatanUstadz extends Model
{
    use HasFactory;

    protected $table = 'catatan_ustadzs';

    protected $guarded = ['id'];

    protected $casts = [
        'ustadz_id'          => 'integer',
        'user_id'            => 'integer',
        'tahun_pelajaran_id' => 'integer',
        'murid_id'           => 'integer',
        'ruangan_id'         => 'integer',
        'is_dibaca_admin'    => 'boolean',
        'dibaca_admin_pada'  => 'datetime',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
    ];

    /**
     * Relasi ke Ustadz pembuat catatan
     */
    public function ustadz()
    {
        return $this->belongsTo(Ustadz::class, 'ustadz_id');
    }

    /**
     * Relasi ke Akun User ustadz
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke Murid yang dilaporkan / dicatat (opsional)
     */
    public function murid()
    {
        return $this->belongsTo(Murid::class, 'murid_id');
    }

    /**
     * Relasi ke Ruangan / Kelas (opsional)
     */
    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    /**
     * Relasi ke Tahun Pelajaran saat catatan dibuat
     */
    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    /**
     * Accessor URL lampiran foto yang aman untuk Web & Mobile
     */
    public function getLampiranFotoUrlAttribute(): ?string
    {
        if (!$this->lampiran_foto) {
            return null;
        }

        if (str_starts_with($this->lampiran_foto, 'http://') || str_starts_with($this->lampiran_foto, 'https://')) {
            return $this->lampiran_foto;
        }

        return asset('storage/' . $this->lampiran_foto);
    }

    /**
     * Scope untuk memfilter catatan berdasarkan Ustadz tertentu
     */
    public function scopeMilikUstadz($query, $ustadzId)
    {
        return $query->where('ustadz_id', $ustadzId);
    }

    /**
     * Scope untuk pencarian berdasarkan judul, isi, nama murid, atau ustadz
     */
    public function scopeSearch($query, $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('judul', 'like', "%{$term}%")
                ->orWhere('isi_catatan', 'like', "%{$term}%")
                ->orWhereHas('murid', function ($mq) use ($term) {
                    $mq->where('nama_lengkap', 'like', "%{$term}%")
                        ->orWhere('nism', 'like', "%{$term}%");
                })
                ->orWhereHas('ustadz', function ($uq) use ($term) {
                    $uq->where('nama_lengkap', 'like', "%{$term}%")
                        ->orWhere('kode_ustadz', 'like', "%{$term}%");
                });
        });
    }
}
