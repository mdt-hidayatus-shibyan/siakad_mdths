<?php

namespace App\Repositories;

use App\Models\Murid;
use App\Models\TahunPelajaran;
use App\Models\WaliMurid;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class MuridRuanganRepository
{
    protected $murid;

    public function __construct(Murid $murid)
    {
        $this->murid = $murid;
    }

    /**
     * Mengambil data murid berdasarkan ruangan dan tahun pelajaran, 
     * diurutkan berdasarkan jenis kelamin dan nama.
     *
     * @param int|string $ruangan_id
     * @param int|string $tahun_pelajaran_id
     * @param string|null $status
     * @param array $with
     * @return Collection
     */
    public function getMuridByRuanganAndTahun($ruangan_id, $tahun_pelajaran_id, $status = null, array $with = []): Collection
    {
        $query = $this->murid->whereHas('ruangans', function ($query) use ($ruangan_id, $tahun_pelajaran_id) {
            $query->where('ruangans.id', $ruangan_id)
                ->where('murid_ruangans.tahun_pelajaran_id', $tahun_pelajaran_id);
        });

        if (!empty($with)) {
            $query->with($with);
        }

        // Jika parameter $status diisi, tambahkan filter where
        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('jenis_kelamin', 'asc') // 1. Pisahkan L/P
            ->orderBy('nama_lengkap', 'asc')  // 2. Urutkan nama sesuai abjad
            ->get();
    }

    /**
     * Mengambil data murid aktif saja berdasarkan ruangan dan tahun pelajaran.
     *
     * @param int|string $ruangan_id
     * @param int|string $tahun_pelajaran_id
     * @param array $with
     * @return Collection
     */
    public function getMuridAktifByRuanganAndTahun($ruangan_id, $tahun_pelajaran_id, array $with = []): Collection
    {
        return $this->getMuridByRuanganAndTahun($ruangan_id, $tahun_pelajaran_id, 'Aktif', $with);
    }

    /**
     * Mengambil data wali murid aktif dikelompokkan berdasarkan kode kampung,
     * beserta data murid/murid aktif dan ruangan mereka di tahun ajaran tertentu.
     *
     * @param int|string|null $tahun_pelajaran_id
     * @param int|string|null $kampung_id
     * @return SupportCollection
     */
    public function getWaliMuridAktifGroupedByKampung($tahun_pelajaran_id = null, $kampung_id = null): SupportCollection
    {
        if (!$tahun_pelajaran_id) {
            $tahunAktif = TahunPelajaran::where('is_active', true)->first();
            $tahun_pelajaran_id = $tahunAktif?->id;
        }

        $query = WaliMurid::with([
            'kampung',
            'murids' => function ($q) use ($tahun_pelajaran_id) {
                $q->where('status', 'Aktif')
                    ->with(['ruangans' => function ($rq) use ($tahun_pelajaran_id) {
                        if ($tahun_pelajaran_id) {
                            $rq->where('murid_ruangans.tahun_pelajaran_id', $tahun_pelajaran_id);
                        }
                    }, 'ruanganMasuk'])
                    ->orderBy('jenis_kelamin', 'asc')
                    ->orderBy('nama_lengkap', 'asc');
            }
        ])
            ->where('is_active', true);

        if ($kampung_id) {
            $query->where('kampung_id', $kampung_id);
        }

        $walis = $query->get();

        return $walis->sortBy(function ($wali) {
            $kode = $wali->kampung ? str_pad($wali->kampung->kode, 4, '0', STR_PAD_LEFT) : '9999';
            return $kode . '_' . ($wali->nama_kepala_keluarga ?? '');
        })->groupBy(function ($wali) {
            if ($wali->kampung) {
                return $wali->kampung->kode . ' - ' . $wali->kampung->nama_kampung;
            }
            return 'Lainnya / Tanpa Kampung';
        });
    }

    /**
     * Mengambil satu data murid lengkap dengan relasi wali murid dan ruangan pada tahun pelajaran tertentu.
     *
     * @param int|string $id
     * @param int|string|null $tahun_pelajaran_id
     * @param array $extraWith
     * @return Murid|null
     */
    public function getMuridByIdAndTahun($id, $tahun_pelajaran_id = null, array $extraWith = []): ?Murid
    {
        if (!$tahun_pelajaran_id) {
            $tahunAktif = TahunPelajaran::where('is_active', true)->first();
            $tahun_pelajaran_id = $tahunAktif?->id;
        }

        $withRelations = array_merge([
            'waliMurid.kampung',
            'ruanganMasuk',
            'ruangans' => function ($q) use ($tahun_pelajaran_id) {
                if ($tahun_pelajaran_id) {
                    $q->where('murid_ruangans.tahun_pelajaran_id', $tahun_pelajaran_id);
                }
            }
        ], $extraWith);

        return $this->murid->with($withRelations)->find($id);
    }

    /**
     * Mengambil seluruh data murid aktif lengkap dengan ruangan pada tahun pelajaran tertentu.
     *
     * @param int|string|null $tahun_pelajaran_id
     * @param array $extraWith
     * @return Collection
     */
    public function getAllMuridAktifWithRuangan($tahun_pelajaran_id = null, array $extraWith = []): Collection
    {
        if (!$tahun_pelajaran_id) {
            $tahunAktif = TahunPelajaran::where('is_active', true)->first();
            $tahun_pelajaran_id = $tahunAktif?->id;
        }

        $withRelations = array_merge([
            'waliMurid.kampung',
            'ruanganMasuk',
            'ruangans' => function ($q) use ($tahun_pelajaran_id) {
                if ($tahun_pelajaran_id) {
                    $q->where('murid_ruangans.tahun_pelajaran_id', $tahun_pelajaran_id);
                }
            }
        ], $extraWith);

        return $this->murid->with($withRelations)
            ->where('status', 'Aktif')
            ->orderByRaw('CAST(nism AS UNSIGNED) ASC, nism ASC')
            ->get();
    }
}
