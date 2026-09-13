<?php

namespace App\Http\Controllers\UjianAlquran;

use App\Http\Controllers\Controller;
use App\Models\TahunPelajaran;
use App\Models\Ustadz;
use App\Models\UjianAlquran\JuriUjianAlquran;
use App\Models\UjianAlquran\UjianAlquran;
use Illuminate\Http\Request;

class JuriAlquranController extends Controller
{
    /**
     * Konfigurasi Dewan Juri / Ustadz Penguji
     */
    public function index(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? $daftarTahun->first();
        $tahunPelajaranId = $request->tahun_id ?? $tahunAktif?->id;

        $ujian = UjianAlquran::with(['tahunPelajaran'])
            ->where('tahun_pelajaran_id', $tahunPelajaranId)
            ->orderBy('id', 'desc')
            ->first();

        $juris = collect();
        $availableUstadz = collect();

        if ($ujian) {
            $query = JuriUjianAlquran::with('ustadz')
                ->where('ujian_alquran_id', $ujian->id);

            // Filter Kategori (Khotho Jali / Khotho Khofi)
            if ($request->filled('kategori_juri')) {
                $query->where('kategori_juri', $request->kategori_juri);
            }

            // Filter Search (Nama / NIGM / Keterangan)
            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('keterangan', 'LIKE', "%{$search}%")
                        ->orWhere('peran_juri', 'LIKE', "%{$search}%")
                        ->orWhereHas('ustadz', function ($uq) use ($search) {
                            $uq->where('nama_lengkap', 'LIKE', "%{$search}%")
                                ->orWhere('nigm', 'LIKE', "%{$search}%");
                        });
                });
            }

            $juris = $query->orderBy('urutan', 'asc')->orderBy('id', 'asc')->get();

            $assignedUstadzIds = JuriUjianAlquran::where('ujian_alquran_id', $ujian->id)->pluck('ustadz_id');
            $availableUstadz = Ustadz::where('is_active', true)
                ->whereNotIn('id', $assignedUstadzIds)
                ->orderBy('nama_lengkap', 'asc')
                ->get();
        }

        return view('ujian-alquran.juri', compact('ujian', 'juris', 'availableUstadz', 'daftarTahun', 'tahunPelajaranId'));
    }

    /**
     * Tampilkan Modal Form Tambah Juri (AJAX)
     */
    public function create(Request $request)
    {
        $tahunPelajaranId = $request->tahun_id ?? TahunPelajaran::where('is_active', true)->value('id');
        $ujian = UjianAlquran::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->orderBy('id', 'desc')
            ->firstOrFail();

        $assignedUstadzIds = JuriUjianAlquran::where('ujian_alquran_id', $ujian->id)->pluck('ustadz_id');
        $availableUstadz = Ustadz::where('is_active', true)
            ->whereNotIn('id', $assignedUstadzIds)
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        return view('ujian-alquran.juri-form', compact('ujian', 'availableUstadz', 'tahunPelajaranId'));
    }

    /**
     * Simpan Penugasan Dewan Juri (AJAX / Standard)
     */
    public function simpanJuri(Request $request)
    {
        $request->validate([
            'ujian_id'            => 'required|exists:ujian_alqurans,id',
            'ustadz_id'           => 'required|exists:ustadzs,id',
            'kategori_juri'       => 'required|in:Khotho Jali,Khotho Khofi',
            'peran_juri'          => 'required|in:Juri 1,Juri 2',
            'is_penanggung_jawab' => 'nullable|boolean',
            'keterangan'          => 'nullable|string|max:255',
        ], [
            'ustadz_id.required'     => 'Pilih ustadz yang akan ditugaskan.',
            'kategori_juri.required' => 'Pilih kategori bidang juri.',
            'kategori_juri.in'       => 'Kategori juri hanya boleh Khotho Jali atau Khotho Khofi.',
            'peran_juri.required'    => 'Pilih peran juri.',
            'peran_juri.in'          => 'Peran juri hanya boleh Juri 1 atau Juri 2.',
        ]);

        $exists = JuriUjianAlquran::where('ujian_alquran_id', $request->ujian_id)
            ->where('ustadz_id', $request->ustadz_id)
            ->exists();

        if ($exists) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ustadz tersebut sudah ditugaskan sebagai Dewan Juri pada agenda ujian ini.',
                ], 422);
            }
            return redirect()->back()->with('error', 'Ustadz tersebut sudah ditugaskan sebagai Dewan Juri pada ujian ini.');
        }

        $urutan = $request->peran_juri === 'Juri 1' ? 1 : 2;
        $isPj = $request->boolean('is_penanggung_jawab');

        // Jika belum ada juri sama sekali atau juri ini ditandai PJ
        $existingPj = JuriUjianAlquran::where('ujian_alquran_id', $request->ujian_id)->where('is_penanggung_jawab', true)->exists();
        if ($isPj || !$existingPj) {
            JuriUjianAlquran::where('ujian_alquran_id', $request->ujian_id)->update(['is_penanggung_jawab' => false]);
            $isPj = true;
        }

        $juri = JuriUjianAlquran::create([
            'ujian_alquran_id'    => $request->ujian_id,
            'ustadz_id'           => $request->ustadz_id,
            'kategori_juri'       => $request->kategori_juri,
            'peran_juri'          => $request->peran_juri,
            'urutan'              => $urutan,
            'is_penanggung_jawab' => $isPj,
            'keterangan'          => $request->keterangan,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Dewan Juri berhasil ditugaskan.',
                'data'    => $juri,
            ]);
        }

        return redirect()->back()->with('success', 'Dewan Juri berhasil ditugaskan.');
    }

    /**
     * Tampilkan Modal Form Edit Juri (AJAX)
     */
    public function edit($juriId)
    {
        $juri = JuriUjianAlquran::with(['ustadz', 'ujianAlquran'])->findOrFail($juriId);
        $ujian = $juri->ujianAlquran;

        // Ambil ustadz aktif yang belum ditugaskan, ditambah ustadz juri saat ini
        $assignedUstadzIds = JuriUjianAlquran::where('ujian_alquran_id', $ujian->id)
            ->where('id', '!=', $juri->id)
            ->pluck('ustadz_id');

        $availableUstadz = Ustadz::where('is_active', true)
            ->whereNotIn('id', $assignedUstadzIds)
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        return view('ujian-alquran.juri-form', compact('juri', 'ujian', 'availableUstadz'));
    }

    /**
     * Update Penugasan Dewan Juri (AJAX / Standard)
     */
    public function updateJuri(Request $request, $juriId)
    {
        $juri = JuriUjianAlquran::findOrFail($juriId);

        $request->validate([
            'ustadz_id'           => 'required|exists:ustadzs,id',
            'kategori_juri'       => 'required|in:Khotho Jali,Khotho Khofi',
            'peran_juri'          => 'required|in:Juri 1,Juri 2',
            'is_penanggung_jawab' => 'nullable|boolean',
            'keterangan'          => 'nullable|string|max:255',
        ], [
            'ustadz_id.required'     => 'Pilih ustadz yang akan ditugaskan.',
            'kategori_juri.required' => 'Pilih kategori bidang juri.',
            'kategori_juri.in'       => 'Kategori juri hanya boleh Khotho Jali atau Khotho Khofi.',
            'peran_juri.required'    => 'Pilih peran juri.',
            'peran_juri.in'          => 'Peran juri hanya boleh Juri 1 atau Juri 2.',
        ]);

        $exists = JuriUjianAlquran::where('ujian_alquran_id', $juri->ujian_alquran_id)
            ->where('ustadz_id', $request->ustadz_id)
            ->where('id', '!=', $juri->id)
            ->exists();

        if ($exists) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ustadz tersebut sudah ditugaskan pada agenda ujian ini.',
                ], 422);
            }
            return redirect()->back()->with('error', 'Ustadz tersebut sudah ditugaskan pada ujian ini.');
        }

        $urutan = $request->peran_juri === 'Juri 1' ? 1 : 2;
        $isPj = $request->boolean('is_penanggung_jawab');

        if ($isPj) {
            JuriUjianAlquran::where('ujian_alquran_id', $juri->ujian_alquran_id)
                ->where('id', '!=', $juri->id)
                ->update(['is_penanggung_jawab' => false]);
        }

        $juri->update([
            'ustadz_id'           => $request->ustadz_id,
            'kategori_juri'       => $request->kategori_juri,
            'peran_juri'          => $request->peran_juri,
            'urutan'              => $urutan,
            'is_penanggung_jawab' => $isPj,
            'keterangan'          => $request->keterangan,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data Dewan Juri berhasil diperbarui.',
                'data'    => $juri,
            ]);
        }

        return redirect()->back()->with('success', 'Data Dewan Juri berhasil diperbarui.');
    }

    /**
     * Jadikan Juri Tertentu Sebagai Penanggung Jawab Ujian (AJAX / Quick Action)
     */
    public function setPenanggungJawab(Request $request, $juriId)
    {
        $juri = JuriUjianAlquran::with('ustadz')->findOrFail($juriId);

        // Reset semua juri di ujian ini
        JuriUjianAlquran::where('ujian_alquran_id', $juri->ujian_alquran_id)
            ->update(['is_penanggung_jawab' => false]);

        $juri->update(['is_penanggung_jawab' => true]);

        $nama = $juri->ustadz->nama_lengkap ?? 'Dewan Juri';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Ustadz {$nama} berhasil ditetapkan sebagai Penanggung Jawab Ujian (TTD SK & Ijazah).",
                'data'    => $juri,
            ]);
        }

        return redirect()->back()->with('success', "Ustadz {$nama} berhasil ditetapkan sebagai Penanggung Jawab Ujian (TTD SK & Ijazah).");
    }

    /**
     * Hapus Penugasan Dewan Juri (AJAX / Standard)
     */
    public function hapusJuri(Request $request, $juriId)
    {
        $juri = JuriUjianAlquran::with('ustadz')->findOrFail($juriId);
        $nama = $juri->ustadz->nama_lengkap ?? 'Dewan Juri';
        $wasPj = $juri->is_penanggung_jawab;
        $ujianId = $juri->ujian_alquran_id;

        $juri->delete();

        // Jika yang dihapus adalah Penanggung Jawab, jadikan juri lain yang tersisa sebagai Penanggung Jawab
        if ($wasPj) {
            $remainingJuri = JuriUjianAlquran::where('ujian_alquran_id', $ujianId)->orderBy('urutan', 'asc')->first();
            if ($remainingJuri) {
                $remainingJuri->update(['is_penanggung_jawab' => true]);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Dewan Juri {$nama} berhasil dihapus.",
            ]);
        }

        return redirect()->back()->with('success', 'Dewan Juri berhasil dihapus.');
    }
}
