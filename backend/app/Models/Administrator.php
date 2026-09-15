<?php

namespace App\Models;


use App\Traits\HasEncryptedSensitiveData;
use Illuminate\Database\Eloquent\Model;

class Administrator extends Model
{
    use HasEncryptedSensitiveData;

    protected $guarded = ['id'];
    protected $encryptedFields = ['nik'];
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'tanggal_lahir' => 'date',
        'is_active'     => 'boolean',
    ];


    public static function getTandaTanganAdmin($tingkat_id = null)
    {
        // Cari admin berdasarkan tingkat
        $admin = self::where('is_active', true)
            ->when($tingkat_id, function ($query, $tingkat_id) {
                $query->where('tingkat_id', $tingkat_id);
            }, function ($query) {
                $query->whereNull('tingkat_id');
            })
            ->first();
        if (!$admin && $tingkat_id) {
            $admin = self::where('is_active', true)->whereNull('tingkat_id')->first();
        }
        return $admin;
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tingkat()
    {
        return $this->belongsTo(Tingkat::class);
    }
}
