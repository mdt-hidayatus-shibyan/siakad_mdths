<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CatatanUstadz;
use App\Models\Murid;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CatatanUstadzController extends Controller
{
    /**
     * Helper untuk mengambil profil Ustadz yang sedang login
     */
    private function resolveUstadz(Request $request)
    {
        $user = $request->user();
        $ustadz = $user->ustadz;

        if (!$ustadz) {
            abort(response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Profil ustadz tidak ditemukan pada akun ini.'
            ], 403));
        }

        return $ustadz;
    }

    /**
     * Dapatkan daftar catatan ustadz (Hanya milik ustadz yang bersangkutan)
     */
    public function index(Request $request)
    {
        $ustadz = $this->resolveUstadz($request);

        $kategori = $request->query('kategori');
        $targetTipe = $request->query('target_tipe');
        $search = $request->query('search');

        $query = CatatanUstadz::with(['murid', 'ruangan', 'tahunPelajaran'])
            ->where('ustadz_id', $ustadz->id)
            ->when($kategori, fn($q) => $q->where('kategori', $kategori))
            ->when($targetTipe, fn($q) => $q->where('target_tipe', $targetTipe))
            ->when($search, fn($q) => $q->search($search))
            ->latest();

        $catatans = $query->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Daftar catatan ustadz berhasil diambil.',
            'data'    => $catatans->items(),
            'meta'    => [
                'current_page' => $catatans->currentPage(),
                'last_page'    => $catatans->lastPage(),
                'total'        => $catatans->total(),
                'per_page'     => $catatans->perPage(),
            ]
        ], 200);
    }

    /**
     * Dapatkan detail satu catatan ustadz
     */
    public function show($id, Request $request)
    {
        $ustadz = $this->resolveUstadz($request);

        $catatan = CatatanUstadz::with(['murid', 'ruangan', 'tahunPelajaran'])
            ->where('ustadz_id', $ustadz->id)
            ->find($id);

        if (!$catatan) {
            return response()->json([
                'success' => false,
                'message' => 'Catatan tidak ditemukan atau Anda tidak memiliki hak akses.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $catatan
        ], 200);
    }

    /**
     * Simpan catatan baru oleh Ustadz
     */
    public function store(Request $request)
    {
        $ustadz = $this->resolveUstadz($request);

        $validator = Validator::make($request->all(), [
            'judul'           => 'required|string|max:200',
            'kategori'        => 'required|string|max:100',
            'target_tipe'     => 'required|in:murid,madrasah,umum',
            'isi_catatan'     => 'required|string',
            'tingkat_urgensi' => 'required|in:Rendah,Sedang,Tinggi,Penting / Mendesak',
            'murid_id'        => 'nullable|exists:murids,id',
            'ruangan_id'      => 'nullable|exists:ruangans,id',
            'foto'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ], [
            'judul.required'           => 'Judul catatan wajib diisi.',
            'kategori.required'        => 'Kategori catatan wajib dipilih.',
            'isi_catatan.required'     => 'Isi catatan wajib diisi.',
            'tingkat_urgensi.required' => 'Tingkat urgensi wajib ditentukan.',
            'foto.image'               => 'File lampiran harus berupa gambar (JPG, PNG, WEBP).',
            'foto.max'                 => 'Ukuran foto maksimal 3MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        $tahunAktif = TahunPelajaran::where('is_active', true)->first();

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('uploads/catatan_ustadz', 'public');
        }

        $ruanganId = $request->ruangan_id;
        if ($request->target_tipe === 'murid' && $request->murid_id && !$ruanganId) {
            $murid = Murid::with(['ruangans' => function ($q) use ($tahunAktif) {
                if ($tahunAktif) {
                    $q->where('murid_ruangans.tahun_pelajaran_id', $tahunAktif->id);
                }
            }])->find($request->murid_id);
            $ruanganId = $murid?->ruangans?->first()?->id ?? $murid?->ruangan_masuk;
        }

        $catatan = CatatanUstadz::create([
            'ustadz_id'          => $ustadz->id,
            'user_id'            => $request->user()->id,
            'tahun_pelajaran_id' => $tahunAktif?->id,
            'kategori'           => $request->kategori,
            'target_tipe'        => $request->target_tipe,
            'murid_id'           => $request->target_tipe === 'murid' ? $request->murid_id : null,
            'ruangan_id'         => $ruanganId,
            'judul'              => $request->judul,
            'isi_catatan'        => $request->isi_catatan,
            'tingkat_urgensi'    => $request->tingkat_urgensi,
            'lampiran_foto'      => $fotoPath,
        ]);

        $catatan->load(['murid', 'ruangan', 'tahunPelajaran']);

        return response()->json([
            'success' => true,
            'message' => 'Catatan ustadz berhasil disimpan.',
            'data'    => $catatan
        ], 201);
    }

    /**
     * Edit / Update catatan milik Ustadz
     */
    public function update($id, Request $request)
    {
        $ustadz = $this->resolveUstadz($request);

        $catatan = CatatanUstadz::where('ustadz_id', $ustadz->id)->find($id);

        if (!$catatan) {
            return response()->json([
                'success' => false,
                'message' => 'Catatan tidak ditemukan atau Anda tidak memiliki hak untuk mengedit catatan ini.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'judul'           => 'required|string|max:200',
            'kategori'        => 'required|string|max:100',
            'target_tipe'     => 'required|in:murid,madrasah,umum',
            'isi_catatan'     => 'required|string',
            'tingkat_urgensi' => 'required|in:Rendah,Sedang,Tinggi,Penting / Mendesak',
            'murid_id'        => 'nullable|exists:murids,id',
            'ruangan_id'      => 'nullable|exists:ruangans,id',
            'foto'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        $fotoPath = $catatan->lampiran_foto;
        if ($request->hasFile('foto')) {
            // Hapus file lama jika ada
            if ($fotoPath && Storage::disk('public')->exists($fotoPath)) {
                Storage::disk('public')->delete($fotoPath);
            }
            $fotoPath = $request->file('foto')->store('uploads/catatan_ustadz', 'public');
        }

        $ruanganId = $request->ruangan_id;
        if ($request->target_tipe === 'murid' && $request->murid_id && !$ruanganId) {
            $murid = Murid::with(['ruangans' => function ($q) use ($catatan) {
                if ($catatan->tahun_pelajaran_id) {
                    $q->where('murid_ruangans.tahun_pelajaran_id', $catatan->tahun_pelajaran_id);
                }
            }])->find($request->murid_id);
            $ruanganId = $murid?->ruangans?->first()?->id ?? $murid?->ruangan_masuk;
        }

        $catatan->update([
            'kategori'        => $request->kategori,
            'target_tipe'     => $request->target_tipe,
            'murid_id'        => $request->target_tipe === 'murid' ? $request->murid_id : null,
            'ruangan_id'      => $ruanganId,
            'judul'           => $request->judul,
            'isi_catatan'     => $request->isi_catatan,
            'tingkat_urgensi' => $request->tingkat_urgensi,
            'lampiran_foto'   => $fotoPath,
        ]);

        $catatan->load(['murid', 'ruangan', 'tahunPelajaran']);

        return response()->json([
            'success' => true,
            'message' => 'Catatan ustadz berhasil diperbarui.',
            'data'    => $catatan
        ], 200);
    }

    /**
     * Hapus catatan milik Ustadz
     */
    public function destroy($id, Request $request)
    {
        $ustadz = $this->resolveUstadz($request);

        $catatan = CatatanUstadz::where('ustadz_id', $ustadz->id)->find($id);

        if (!$catatan) {
            return response()->json([
                'success' => false,
                'message' => 'Catatan tidak ditemukan atau Anda tidak memiliki hak untuk menghapus catatan ini.'
            ], 403);
        }

        // Hapus foto jika ada
        if ($catatan->lampiran_foto && Storage::disk('public')->exists($catatan->lampiran_foto)) {
            Storage::disk('public')->delete($catatan->lampiran_foto);
        }

        $catatan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Catatan ustadz berhasil dihapus.'
        ], 200);
    }

    /**
     * Ambil data referensi pendukung untuk Form Catatan Ustadz (Daftar Murid & Ruangan)
     */
    public function getOptions(Request $request)
    {
        $this->resolveUstadz($request);

        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunId = $tahunAktif?->id;

        $ruangans = Ruangan::leftJoin('levels', 'ruangans.level_id', '=', 'levels.id')
            ->orderBy('levels.urutan_level', 'asc')
            ->orderBy('ruangans.nama_ruangan', 'asc')
            ->select('ruangans.id', 'ruangans.nama_ruangan')
            ->get();

        // Ambil murid aktif
        $murids = Murid::with(['ruangans' => function ($q) use ($tahunId) {
            if ($tahunId) {
                $q->where('murid_ruangans.tahun_pelajaran_id', $tahunId);
            }
        }])
            ->where('status', 'Aktif')
            ->orderBy('nama_lengkap', 'asc')
            ->get()
            ->map(function ($m) {
                $ruanganAktif = $m->ruangans->first();
                return [
                    'id'           => $m->id,
                    'nism'         => $m->nism,
                    'nama_lengkap' => $m->nama_lengkap,
                    'ruangan'      => $m->nama_ruangan_aktif,
                    'ruangan_id'   => $ruanganAktif?->id ?? $m->ruangan_masuk,
                ];
            });

        $kategoriList = [
            'Keluhan Murid',
            'KBM & Perkembangan Akademik',
            'Fasilitas Madrasah',
            'Evaluasi & Saran',
            'Lainnya',
        ];

        $urgensiList = [
            'Rendah',
            'Sedang',
            'Tinggi',
            'Penting / Mendesak',
        ];

        return response()->json([
            'success' => true,
            'data'    => [
                'ruangans'      => $ruangans,
                'murids'        => $murids,
                'kategori_list' => $kategoriList,
                'urgensi_list'  => $urgensiList,
            ]
        ], 200);
    }
}
