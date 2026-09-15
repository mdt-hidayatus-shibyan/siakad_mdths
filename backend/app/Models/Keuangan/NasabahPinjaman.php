<?php

namespace App\Models\Keuangan;

use App\Models\User;
use App\Models\Ustadz;
use App\Models\WaliMurid;
use App\Traits\HasEncryptedSensitiveData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NasabahPinjaman extends Model
{
    use HasFactory, HasEncryptedSensitiveData;

    protected $table = 'nasabah_pinjamans';

    protected $encryptedFields = ['nik_ktp'];

    protected $fillable = [
        'kode_nasabah',
        'tipe_nasabah',
        'ustadz_id',
        'wali_murid_id',
        'user_id',
        'nama_lengkap',
        'nik_ktp',
        'no_hp',
        'alamat',
        'pekerjaan',
        'foto_ktp',
        'foto_nasabah',
        'catatan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function ustadz(): BelongsTo
    {
        return $this->belongsTo(Ustadz::class, 'ustadz_id');
    }

    public function waliMurid(): BelongsTo
    {
        return $this->belongsTo(WaliMurid::class, 'wali_murid_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pinjamans(): HasMany
    {
        return $this->hasMany(Pinjaman::class, 'nasabah_pinjaman_id');
    }

    public function pinjamanAktif(): HasMany
    {
        return $this->pinjamans()->whereIn('status', ['disetujui', 'dicairkan', 'macet']);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
