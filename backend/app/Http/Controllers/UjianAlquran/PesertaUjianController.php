<?php

namespace App\Http\Controllers\UjianAlquran;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\UjianAlquran\PesertaUjianAlquran;
use App\Models\UjianAlquran\UjianAlquran;
use App\Services\UjianAlquranService;
use Illuminate\Http\Request;

class PesertaUjianController extends Controller
{
    protected UjianAlquranService $service;

    public function __construct(UjianAlquranService $service)
    {
        $this->service = $service;
    }

    /**
     * Daftar Peserta Ujian Al-Qur'an
     * Default: Filter berdasarkan Tahun Pelajaran Aktif & Dimulai dari Ruangan Level 5 IBT
     */
    public function index(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $tahunPelajaranId = $request->tahun_id ?? $tahunAktif?->id;

        // Ambil agenda ujian pada tahun pelajaran ini
        $ujian = UjianAlquran::with('tahunPelajaran')
            ->where('tahun_pelajaran_id', $tahunPelajaranId)
            ->orderBy('id', 'desc')
            ->first();

        // Ambil daftar ruangan: Urutan Level 5 IBT terlebih dahulu (5-A, 5-B, 5-C), lalu Level 6 IBT (6-A, 6-B)
        $level5Ids = Level::where('nama_level', 'LIKE', '%5%IBT%')->orWhere('urutan_level', 8)->pluck('id');
        $level6Ids = Level::where('nama_level', 'LIKE', '%6%IBT%')->orWhere('urutan_level', 9)->pluck('id');

        $ruangans5 = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->whereIn('level_id', $level5Ids)
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        $ruangans6 = Ruangan::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->whereIn('level_id', $level6Ids)
            ->orderBy('nama_ruangan', 'asc')
            ->get();

        $daftarRuangan = $ruangans5->concat($ruangans6);

        // Filter Ruangan: Default dimulai dari ruangan pertama Level 5 IBT jika user belum memilih filter
        $defaultRuanganId = $daftarRuangan->first()?->id;
        $selectedRuanganId = $request->has('ruangan_id') ? $request->ruangan_id : $defaultRuanganId;

        $pesertas = collect();
        $statistik = (object) [
            'total' => 0,
            'lulus' => 0,
            'tidak_lulus' => 0,
            'belum_diuji' => 0,
            'persen_lulus' => 0,
        ];

        if ($ujian) {
            $query = PesertaUjianAlquran::with(['murid.waliMurid.kampung', 'ruangan.level'])
                ->where('ujian_alquran_id', $ujian->id);

            if (!empty($selectedRuanganId)) {
                $query->where('ruangan_id', $selectedRuanganId);
            }

            if ($request->filled('status_kelulusan')) {
                $query->where('status_kelulusan', $request->status_kelulusan);
            }

            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_peserta', 'LIKE', "%{$search}%")
                        ->orWhereHas('murid', function ($mq) use ($search) {
                            $mq->where('nama_lengkap', 'LIKE', "%{$search}%")
                                ->orWhere('nism', 'LIKE', "%{$search}%");
                        });
                });
            }

            $pesertas = $query->orderBy('id', 'asc')->paginate(20)->withQueryString();
            $statistik = $ujian->statistik;
        }

        return view('ujian-alquran.peserta', compact(
            'ujian',
            'pesertas',
            'daftarRuangan',
            'daftarTahun',
            'tahunPelajaranId',
            'selectedRuanganId',
            'statistik'
        ));
    }

    /**
     * Mengambil kandidat murid untuk ruangan yang dipilih (AJAX)
     * Mengabaikan murid yang sudah terdaftar di ujian ini atau sudah lulus ujian Al-Qur'an.
     */
    public function getKandidatPeserta(Request $request)
    {
        $ujianId = $request->ujian_id;
        $ruanganId = $request->ruangan_id;
        $tahunId = $request->tahun_id;

        if (!$ujianId || !$ruanganId) {
            return response()->json([
                'success' => false,
                'message' => 'Agenda ujian dan ruangan wajib dipilih.',
                'data'    => [],
            ], 422);
        }

        try {
            $kandidats = $this->service->getKandidatMuridByRuangan((int) $ujianId, (int) $ruanganId, $tahunId ? (int) $tahunId : null);

            return response()->json([
                'success' => true,
                'count'   => $kandidats->count(),
                'data'    => $kandidats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat kandidat murid: ' . $e->getMessage(),
                'data'    => [],
            ], 500);
        }
    }

    /**
     * Simpan Murid yang dipilih ke Daftar Peserta Ujian Al-Qur'an
     */
    public function storePeserta(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'ujian_id'    => 'required|exists:ujian_alqurans,id',
            'ruangan_id'  => 'required|exists:ruangans,id',
            'murid_ids'   => 'required|array|min:1',
            'murid_ids.*' => 'exists:murids,id',
        ], [
            'murid_ids.required' => 'Pilih minimal satu murid untuk didaftarkan.',
            'murid_ids.min'      => 'Pilih minimal satu murid untuk didaftarkan.',
            'ujian_id.exists'    => 'Agenda Ujian Al-Qur\'an tidak valid.',
            'ruangan_id.exists'  => 'Ruangan tidak valid.',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors'  => $validator->errors(),
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $result = $this->service->tambahPeserta(
                (int) $request->ujian_id,
                (int) $request->ruangan_id,
                $request->murid_ids
            );

            $jumlah = $result['ditambahkan'];

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil mendaftarkan {$jumlah} murid sebagai peserta Ujian Al-Qur'an.",
                ]);
            }

            return redirect()->back()->with(
                'success',
                "Berhasil mendaftarkan {$jumlah} murid sebagai peserta Ujian Al-Qur'an."
            );
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan peserta: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->with('error', 'Gagal menambahkan peserta: ' . $e->getMessage());
        }
    }

    /**
     * Tarik Murid Otomatis (Seluruh 5 IBT Aktif & 6 IBT Belum Lulus)
     */
    public function tarikPeserta(Request $request)
    {
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id');
        $ujianId = $request->ujian_id ?? UjianAlquran::where('tahun_pelajaran_id', $tahunPelajaranId)->orderBy('id', 'desc')->value('id');

        if (!$ujianId) {
            return redirect()->back()->with('error', 'Belum ada agenda Ujian Al-Qur\'an pada tahun pelajaran ini. Silakan buat agenda terlebih dahulu.');
        }

        try {
            $result = $this->service->tarikPesertaOtomatis($ujianId);

            return redirect()->back()->with(
                'success',
                "Berhasil menarik peserta! {$result['ditambahkan']} murid baru ditambahkan ke daftar ujian Al-Qur'an."
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menarik peserta: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Peserta dari Ujian
     */
    public function destroy(Request $request, $pesertaId)
    {
        $peserta = PesertaUjianAlquran::with('murid')->findOrFail($pesertaId);
        $nama = $peserta->murid->nama_lengkap ?? 'Murid';
        $peserta->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Peserta {$nama} berhasil dihapus dari daftar ujian.",
            ]);
        }

        return redirect()->back()->with('success', "Peserta {$nama} berhasil dihapus dari daftar ujian.");
    }
}
