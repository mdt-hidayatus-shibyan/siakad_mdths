<?php

namespace App\Models;

use App\Traits\HasEncryptedSensitiveData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PendaftaranSpmb extends Model
{
    use HasFactory, HasEncryptedSensitiveData;

    protected $table = 'pendaftaran_spmbs';

    protected $guarded = ['id'];

    protected $encryptedFields = ['nik', 'nik_ayah', 'nik_ibu'];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_verifikasi' => 'datetime',
    ];

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    public function waliMurid()
    {
        return $this->belongsTo(WaliMurid::class, 'wali_murid_id');
    }

    public function murid()
    {
        return $this->belongsTo(Murid::class, 'murid_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Accessor Foto URL
     */
    public function getFotoUrlAttribute()
    {
        if (!$this->foto) {
            return null;
        }

        if (str_starts_with($this->foto, 'http://') || str_starts_with($this->foto, 'https://')) {
            return $this->foto;
        }

        if (str_starts_with($this->foto, 'storage/')) {
            return asset($this->foto);
        }

        return asset('storage/' . $this->foto);
    }

    /**
     * Helper generator nomor pendaftaran otomatis yang tahan benturan
     */
    public static function generateNomorPendaftaran($tahunId = null)
    {
        $tahun = TahunPelajaran::find($tahunId) ?? TahunPelajaran::where('is_active', true)->first();
        $tahunStr = date('Y');

        if ($tahun && $tahun->nama_masehi) {
            $parts = explode('-', $tahun->nama_masehi);
            $tahunStr = trim($parts[0]);
        }

        $prefix = 'SPMB-' . $tahunStr . '-';

        $latest = self::where('nomor_pendaftaran', 'LIKE', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING(nomor_pendaftaran, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
            ->first();

        $lastNumber = 0;
        if ($latest) {
            $lastNumber = (int) substr($latest->nomor_pendaftaran, strlen($prefix));
        }

        $nextNumber = $lastNumber + 1;
        $nomorPendaftaran = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        while (self::where('nomor_pendaftaran', $nomorPendaftaran)->exists()) {
            $nextNumber++;
            $nomorPendaftaran = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        }

        return $nomorPendaftaran;
    }
}
