<?php

namespace App\Http\Controllers\Spmb;

use App\Http\Controllers\Controller;
use App\Models\Administrator;
use App\Models\Kepengurusan\Pengurus;
use App\Models\Level;
use App\Models\Murid;
use App\Models\PendaftaranSpmb;
use App\Models\PengaturanTagihan;
use App\Models\Ruangan;
use App\Models\TagihanMurid;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpmbAdminController extends Controller
{
    /**
     * Tampilkan List Pendaftar SPMB di Admin Dashboard
     */
    public function index(Request $request)
    {
        $daftarTahun = TahunPelajaran::orderBy('id', 'desc')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $tahunId = $request->input('tahun_pelajaran_id', $tahunAktif?->id ?? $daftarTahun->first()?->id);

        $status = $request->input('status', 'Semua');
        $levelId = $request->input('level_id');
        $search = $request->input('search');

        // Query Pendaftar
        $pendaftarQuery = PendaftaranSpmb::with(['tahunPelajaran', 'level.tingkat', 'waliMurid.kampung', 'murid', 'verifier'])
            ->when($tahunId, function ($q) use ($tahunId) {
                return $q->where('tahun_pelajaran_id', $tahunId);
            })
            ->when($status !== 'Semua' && !empty($status), function ($q) use ($status) {
                return $q->where('status_pendaftaran', $status);
            })
            ->when($levelId, function ($q) use ($levelId) {
                return $q->where('level_id', $levelId);
            })
            ->when($search, function ($q) use ($search) {
                $searchHash = hash_sensitive($search);
                return $q->where(function ($query) use ($search, $searchHash) {
                    $query->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nomor_pendaftaran', 'like', "%{$search}%")
                        ->orWhere('nik_hash', $searchHash)
                        ->orWhereHas('waliMurid', function ($w) use ($search, $searchHash) {
                            $w->where('no_kk_hash', $searchHash)
                                ->orWhere('nama_kepala_keluarga', 'like', "%{$search}%");
                        });
                });
            });

        // Hitung Statistik
        $stats = [
            'total'     => (clone $pendaftarQuery)->count(),
            'menunggu'  => PendaftaranSpmb::when($tahunId, fn($q) => $q->where('tahun_pelajaran_id', $tahunId))->where('status_pendaftaran', 'Menunggu Verifikasi')->count(),
            'diterima'  => PendaftaranSpmb::when($tahunId, fn($q) => $q->where('tahun_pelajaran_id', $tahunId))->where('status_pendaftaran', 'Diterima')->count(),
            'ditolak'   => PendaftaranSpmb::when($tahunId, fn($q) => $q->where('tahun_pelajaran_id', $tahunId))->where('status_pendaftaran', 'Ditolak')->count(),
        ];

        $pendaftar = $pendaftarQuery->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        $levels = Level::with('tingkat')->orderBy('urutan_level', 'asc')->get();
        $ruangans = Ruangan::where('tahun_pelajaran_id', $tahunId)->get();

        // Hitung Next NISM Otomatis
        $maxMurid = Murid::orderByRaw('CAST(nism AS UNSIGNED) DESC')->first();
        $suggestedNism = $maxMurid && is_numeric($maxMurid->nism) ? (string)((int)$maxMurid->nism + 1) : '1001';

        return view('spmb-admin.index', compact(
            'pendaftar',
            'daftarTahun',
            'tahunId',
            'status',
            'levelId',
            'search',
            'stats',
            'levels',
            'ruangans',
            'suggestedNism'
        ));
    }

    /**
     * Ambil Detail Data Pendaftar (JSON untuk Modal Verifikasi)
     */
    public function getDetailJson($id)
    {
        $pendaftaran = PendaftaranSpmb::with(['tahunPelajaran', 'level.tingkat', 'waliMurid.kampung', 'murid', 'verifier'])
            ->findOrFail($id);

        $maxMurid = Murid::orderByRaw('CAST(nism AS UNSIGNED) DESC')->first();
        $suggestedNism = $maxMurid && is_numeric($maxMurid->nism) ? (string)((int)$maxMurid->nism + 1) : '1001';

        // Tagihan SPMB terkait
        $tagihans = PengaturanTagihan::where('tahun_pelajaran_id', $pendaftaran->tahun_pelajaran_id)
            ->where(function ($q) use ($pendaftaran) {
                $q->whereNull('level_id')->orWhere('level_id', $pendaftaran->level_id);
            })
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $pendaftaran,
            'suggested_nism' => $suggestedNism,
            'tagihans' => $tagihans
        ]);
    }

    /**
     * Proses Verifikasi & Penerimaan Calon Murid Baru (Penerbitan NISM)
     */
    public function verifikasi(Request $request, $id)
    {
        $pendaftaran = PendaftaranSpmb::with(['waliMurid', 'level'])->findOrFail($id);

        if ($pendaftaran->status_pendaftaran === 'Diterima' && $pendaftaran->murid_id) {
            return back()->with('warning', 'Pendaftar ini sudah berstatus Diterima dengan NISM ' . $pendaftaran->nism_diberikan);
        }

        // Tentukan NISM
        $maxMurid = Murid::orderByRaw('CAST(nism AS UNSIGNED) DESC')->first();
        $defaultNism = $maxMurid && is_numeric($maxMurid->nism) ? (string)((int)$maxMurid->nism + 1) : '1001';
        $nism = trim($request->input('nism', $defaultNism));

        // Validasi NISM
        if (empty($nism)) {
            $nism = $defaultNism;
        }

        $nismEksis = Murid::where('nism', $nism)->exists();
        if ($nismEksis) {
            return back()->with('error', 'Gagal! NISM "' . $nism . '" sudah digunakan oleh murid lain. Silakan gunakan nomor NISM berikutnya.');
        }

        DB::beginTransaction();

        try {
            // 1. Pastikan data Wali Murid Aktif
            if ($pendaftaran->wali_murid_id) {
                $wali = $pendaftaran->waliMurid;
                if ($wali && !$wali->is_active) {
                    $wali->is_active = true;
                    $wali->save();
                }
            }

            // 2. Buat Record Murid Resmi di tabel murids
            $murid = Murid::create([
                'wali_murid_id'  => $pendaftaran->wali_murid_id,
                'nism'           => $nism,
                'nisn'           => $pendaftaran->nisn,
                'nik'            => $pendaftaran->nik,
                'nama_lengkap'   => $pendaftaran->nama_lengkap,
                'nama_panggilan' => $pendaftaran->nama_panggilan,
                'jenis_kelamin'  => $pendaftaran->jenis_kelamin,
                'tempat_lahir'   => $pendaftaran->tempat_lahir,
                'tanggal_lahir'  => $pendaftaran->tanggal_lahir,
                'anak_ke'        => $pendaftaran->anak_ke,
                'hub_kel'        => $pendaftaran->hub_kel,
                'nik_ayah'       => $pendaftaran->nik_ayah,
                'nama_ayah'      => $pendaftaran->nama_ayah,
                'status_ayah'    => $pendaftaran->status_ayah,
                'nik_ibu'        => $pendaftaran->nik_ibu,
                'nama_ibu'       => $pendaftaran->nama_ibu,
                'status_ibu'     => $pendaftaran->status_ibu,
                'foto'           => $pendaftaran->foto,
                'status'         => 'Aktif',
                'tahun_masuk'    => $pendaftaran->tahun_pelajaran_id,
                'level_masuk'    => $pendaftaran->level_id,
                'ruangan_masuk'  => $request->ruangan_masuk ?: null,
            ]);

            // Jika admin memilih ruangan masuk langsung, tautkan ke tabel pivot murid_ruangans
            if ($request->filled('ruangan_masuk')) {
                $murid->ruangans()->attach($request->ruangan_masuk, [
                    'tahun_pelajaran_id' => $pendaftaran->tahun_pelajaran_id
                ]);
            }

            // 3. Terbitkan Tagihan SPMB / Tagihan Masuk jika ada
            if ($request->has('buat_tagihan') && $request->buat_tagihan == '1') {
                $tagihans = PengaturanTagihan::where('tahun_pelajaran_id', $pendaftaran->tahun_pelajaran_id)
                    ->where(function ($q) use ($pendaftaran) {
                        $q->whereNull('level_id')->orWhere('level_id', $pendaftaran->level_id);
                    })
                    ->get();

                $ruanganIdForTagihan = $request->ruangan_masuk ?: Ruangan::where('tahun_pelajaran_id', $pendaftaran->tahun_pelajaran_id)->value('id');

                if ($ruanganIdForTagihan) {
                    foreach ($tagihans as $tagihan) {
                        $isSpmbTagihan = str_contains(strtolower($tagihan->nama_tagihan), 'spmb') ||
                            str_contains(strtolower($tagihan->nama_tagihan), 'daftar') ||
                            str_contains(strtolower($tagihan->nama_tagihan), 'masuk') ||
                            str_contains(strtoupper($tagihan->kode_tagihan), 'SPMB');

                        if ($isSpmbTagihan || $tagihan->tipe === 'insidental') {
                            TagihanMurid::create([
                                'murid_id'              => $murid->id,
                                'ruangan_id'            => $ruanganIdForTagihan,
                                'pengaturan_tagihan_id' => $tagihan->id,
                                'nama_tagihan_spesifik' => $tagihan->nama_tagihan,
                                'nominal_tagihan'       => $tagihan->nominal,
                                'status_bayar'          => $request->status_pembayaran ?? 'Belum Lunas',
                            ]);
                        }
                    }
                }
            }

            // 4. Update Status Pendaftaran SPMB
            $pendaftaran->update([
                'status_pendaftaran' => 'Diterima',
                'murid_id'           => $murid->id,
                'nism_diberikan'     => $nism,
                'tanggal_verifikasi' => now(),
                'verified_by'        => auth()->id(),
                'status_pembayaran'  => $request->status_pembayaran ?? 'Belum Lunas',
                'catatan_admin'      => $request->catatan_admin,
            ]);

            DB::commit();

            return redirect()->route('spmb-admin.index', ['tahun_pelajaran_id' => $pendaftaran->tahun_pelajaran_id])
                ->with('success', "Verifikasi Sukses! Murid \"{$murid->nama_lengkap}\" resmi diterima dengan NISM: {$nism}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memverifikasi pendaftaran: ' . $e->getMessage());
        }
    }

    /**
     * Tolak Pendaftaran SPMB
     */
    public function tolak(Request $request, $id)
    {
        $request->validate([
            'catatan_admin' => 'required|string|max:500'
        ], [
            'catatan_admin.required' => 'Alasan penolakan wajib dicantumkan.'
        ]);

        $pendaftaran = PendaftaranSpmb::findOrFail($id);

        $pendaftaran->update([
            'status_pendaftaran' => 'Ditolak',
            'catatan_admin'      => $request->catatan_admin,
            'tanggal_verifikasi' => now(),
            'verified_by'        => auth()->id(),
        ]);

        return redirect()->back()->with('success', "Pendaftaran {$pendaftaran->nomor_pendaftaran} berhasil ditandai Ditolak.");
    }

    /**
     * Scan Barcode / QR Code Quick Lookup
     */
    public function scan(Request $request, $nomor)
    {
        $nomorClean = trim($nomor);

        // Jika nomor berupa URL, ekstrak nomor pendaftaran dari URI
        if (str_contains($nomorClean, '/spmb/bukti/')) {
            $parts = explode('/spmb/bukti/', $nomorClean);
            $nomorClean = end($parts);
        }

        $pendaftaran = PendaftaranSpmb::with(['tahunPelajaran', 'level.tingkat', 'waliMurid.kampung', 'murid', 'verifier'])
            ->where('nomor_pendaftaran', $nomorClean)
            ->first();

        if ($pendaftaran) {
            return response()->json([
                'status' => 'success',
                'data'   => $pendaftaran
            ]);
        }

        return response()->json([
            'status'  => 'not_found',
            'message' => "Nomor pendaftaran \"{$nomorClean}\" tidak ditemukan di sistem SPMB."
        ], 404);
    }

    /**
     * Cetak Surat / Tanda Bukti Penerimaan Murid Baru Resmi
     */
    public function cetakBuktiPenerimaan($id)
    {
        $pendaftaran = PendaftaranSpmb::with(['tahunPelajaran', 'level.tingkat', 'waliMurid.kampung', 'murid', 'verifier'])
            ->findOrFail($id);

        if ($pendaftaran->status_pendaftaran !== 'Diterima') {
            return back()->with('error', 'Surat Bukti Penerimaan hanya dapat dicetak untuk murid yang sudah diverifikasi dan diterima.');
        }

        // Ambil Tagihan SPMB murid yang terdaftar
        $tagihanList = collect();
        if ($pendaftaran->murid_id) {
            $tagihanList = TagihanMurid::where('murid_id', $pendaftaran->murid_id)
                ->where(function ($q) {
                    $q->where('nama_tagihan_spesifik', 'like', '%spmb%')
                        ->orWhere('nama_tagihan_spesifik', 'like', '%daftar%');
                })
                ->get();
        }

        if ($tagihanList->isEmpty()) {
            $tagihanList = PengaturanTagihan::where('tahun_pelajaran_id', $pendaftaran->tahun_pelajaran_id)
                ->where(function ($q) use ($pendaftaran) {
                    $q->whereNull('level_id')->orWhere('level_id', $pendaftaran->level_id);
                })
                ->where(function ($q) {
                    $q->where('nama_tagihan', 'like', '%spmb%')
                        ->orWhere('nama_tagihan', 'like', '%daftar%')
                        ->orWhere('nama_tagihan', 'like', '%masuk%')
                        ->orWhere('nama_tagihan', 'like', '%infaq%')
                        ->orWhere('kode_tagihan', 'like', '%SPMB%');
                })
                ->get();
        }

        $pengasuh = Pengurus::getAktifByJabatan('Pengasuh');
        $kepalaAdmin = Administrator::where('is_active', true)->whereNull('tingkat_id')->first()
            ?? Administrator::whereNull('tingkat_id')->first()
            ?? Administrator::getTandaTanganAdmin(null);

        return view('spmb-admin.cetak-tanda-diterima', compact('pendaftaran', 'pengasuh', 'kepalaAdmin', 'tagihanList'));
    }

    /**
     * Export Excel Data Pendaftar SPMB
     */
    public function exportExcel(Request $request)
    {
        $tahunId = $request->input('tahun_pelajaran_id');
        $tahun = TahunPelajaran::find($tahunId) ?? TahunPelajaran::where('is_active', true)->first();
        $status = $request->input('status', 'Semua');

        $pendaftar = PendaftaranSpmb::with(['tahunPelajaran', 'level.tingkat', 'waliMurid.kampung'])
            ->when($tahunId, fn($q) => $q->where('tahun_pelajaran_id', $tahunId))
            ->when($status !== 'Semua' && !empty($status), fn($q) => $q->where('status_pendaftaran', $status))
            ->orderBy('id', 'asc')
            ->get();

        $filename = "Data_Pendaftar_SPMB_" . ($tahun ? str_replace('/', '-', $tahun->nama_hijriyah) : 'Semua') . "_" . date('Ymd_His') . '.xls';

        $columns = [
            'No',
            'No Pendaftaran',
            'NISM Diberikan',
            'Nama Lengkap',
            'Gender',
            'NIK Murid',
            'No KK',
            'Nama Kepala Keluarga',
            'Zonasi / Kampung',
            'No HP / WA',
            'Kelas / Jenjang Masuk',
            'Nama Ayah',
            'Nama Ibu',
            'Status Pendaftaran',
            'Tanggal Daftar',
            'Tanggal Verifikasi'
        ];

        $callback = function () use ($pendaftar, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns, "\t");

            $no = 1;
            foreach ($pendaftar as $row) {
                fputcsv($file, [
                    $no++,
                    $row->nomor_pendaftaran,
                    $row->nism_diberikan ?? '-',
                    $row->nama_lengkap,
                    $row->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan',
                    $row->nik ?? '-',
                    $row->waliMurid->no_kk ?? '-',
                    $row->waliMurid->nama_kepala_keluarga ?? '-',
                    $row->waliMurid->kampung->nama_kampung ?? '-',
                    $row->waliMurid->no_hp ?? '-',
                    $row->level->nama_level ?? '-',
                    $row->nama_ayah ?? '-',
                    $row->nama_ibu ?? '-',
                    $row->status_pendaftaran,
                    $row->created_at->format('d/m/Y H:i'),
                    $row->tanggal_verifikasi ? $row->tanggal_verifikasi->format('d/m/Y H:i') : '-'
                ], "\t");
            }

            fclose($file);
        };

        $headers = [
            "Content-type"        => "application/vnd.ms-excel; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        return response()->stream($callback, 200, $headers);
    }
}
