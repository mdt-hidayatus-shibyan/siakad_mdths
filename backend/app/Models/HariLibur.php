<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
    protected $fillable = [
        'tahun_pelajaran_id',
        'keterangan',
        'tipe_libur',
        'jam_ke',
        'ruangan_id',
        'level_id',
        'tanggal_mulai',
        'tanggal_selesai'
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'jam_ke'          => 'array',
    ];

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    /**
     * Cek apakah pada tanggal, jam_ke, dan ruangan/level tertentu merupakan Libur / Bebas KBM
     *
     * @param string|Carbon $tanggal (Format: Y-m-d)
     * @param string|null $jamKe (Contoh: 'Nadzoman', '1', '2', 'Ekstra')
     * @param int|null $ruanganId
     * @param int|null $levelId
     * @return array ['is_libur' => bool, 'keterangan' => string|null, 'is_seharian' => bool, 'tipe_libur' => string, 'jam_ke' => array|null]
     */
    public static function checkBebasKbm($tanggal, $jamKe = null, $ruanganId = null, $levelId = null): array
    {
        $tglStr = Carbon::parse($tanggal)->format('Y-m-d');
        $namaHariInggris = Carbon::parse($tanggal)->format('l');

        // 1. Libur Rutin Mingguan Hari Jumat
        if ($namaHariInggris === 'Friday') {
            return [
                'is_libur'         => true,
                'keterangan'       => 'Libur Rutin Mingguan (Hari Jumat)',
                'is_seharian'      => true,
                'tipe_libur'       => 'Seharian',
                'jam_ke'           => null,
                'ruangan_id'       => null,
                'is_rutin_jumat'   => true
            ];
        }

        // 2. Query data Hari Libur / Bebas KBM pada tanggal ini
        $query = self::whereDate('tanggal_mulai', '<=', $tglStr)
            ->whereDate('tanggal_selesai', '>=', $tglStr);

        $liburs = $query->get();

        foreach ($liburs as $libur) {
            // Cek filter ruangan (jika dispesifikasikan)
            if ($libur->ruangan_id && $ruanganId && $libur->ruangan_id != $ruanganId) {
                continue;
            }

            // Cek filter level (jika dispesifikasikan)
            if ($libur->level_id && $levelId && $libur->level_id != $levelId) {
                continue;
            }

            // Jika tipe_libur Seharian
            if ($libur->tipe_libur === 'Seharian' || empty($libur->jam_ke)) {
                return [
                    'is_libur'       => true,
                    'keterangan'     => $libur->keterangan,
                    'is_seharian'    => true,
                    'tipe_libur'     => 'Seharian',
                    'jam_ke'         => null,
                    'ruangan_id'     => $libur->ruangan_id,
                    'is_rutin_jumat' => false
                ];
            }

            // Jika tipe_libur Sebagian Jam
            if ($libur->tipe_libur === 'Sebagian Jam' && is_array($libur->jam_ke)) {
                if ($jamKe !== null) {
                    if (in_array($jamKe, $libur->jam_ke)) {
                        return [
                            'is_libur'       => true,
                            'keterangan'     => $libur->keterangan,
                            'is_seharian'    => false,
                            'tipe_libur'     => 'Sebagian Jam',
                            'jam_ke'         => $libur->jam_ke,
                            'ruangan_id'     => $libur->ruangan_id,
                            'is_rutin_jumat' => false
                        ];
                    }
                } else {
                    // Jika tidak memeriksa jam spesifik, tandai ada sesi bebas KBM pada hari ini
                    return [
                        'is_libur'       => true,
                        'keterangan'     => $libur->keterangan,
                        'is_seharian'    => false,
                        'tipe_libur'     => 'Sebagian Jam',
                        'jam_ke'         => $libur->jam_ke,
                        'ruangan_id'     => $libur->ruangan_id,
                        'is_rutin_jumat' => false
                    ];
                }
            }
        }

        return [
            'is_libur'       => false,
            'keterangan'     => null,
            'is_seharian'    => false,
            'tipe_libur'     => null,
            'jam_ke'         => null,
            'ruangan_id'     => null,
            'is_rutin_jumat' => false
        ];
    }
}
