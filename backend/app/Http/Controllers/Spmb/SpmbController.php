<?php

namespace App\Http\Controllers\Spmb;

use App\Http\Controllers\Controller;

use App\Models\Kampung;
use App\Models\Level;
use App\Models\PendaftaranSpmb;
use App\Models\PengaturanTagihan;
use App\Models\TahunPelajaran;
use App\Models\WaliMurid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SpmbController extends Controller
{
    /**
     * Halaman 1: Pengecekan Nomor Kartu Keluarga (KK)
     */
    public function index()
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? TahunPelajaran::latest()->first();

        return view('spmb.index', compact('tahunAktif'));
    }

    /**
     * Alias untuk index
     */
    public function form()
    {
        return $this->index();
    }

    /**
     * Proses Pengecekan Nomor KK
     */
    public function checkKk(Request $request)
    {
        $request->validate([
            'no_kk' => 'required|string|size:16',
        ], [
            'no_kk.required' => 'Nomor Kartu Keluarga (KK) wajib diisi.',
            'no_kk.size'     => 'Nomor Kartu Keluarga (KK) harus terdiri dari 16 digit angka.',
        ]);

        $noKk = trim($request->no_kk);
        $wali = WaliMurid::whereNoKk($noKk)->first();

        if ($wali) {
            // Wali murid sudah terdaftar -> Langsung ke pendaftaran calon murid dengan ID wali murid
            return redirect()->route('spmb.daftar-murid', ['wali_murid_id' => $wali->id])
                ->with('info', "Keluarga {$wali->nama_kepala_keluarga} ditemukan! Silakan lengkapi data calon murid.");
        }

        // Wali murid belum terdaftar -> Arahkan ke form registrasi keluarga baru
        return redirect()->route('spmb.daftar-wali', ['no_kk' => $noKk])
            ->with('info', 'Nomor KK belum tercatat. Silakan lengkapi data profil keluarga terlebih dahulu.');
    }

    /**
     * Halaman 2A: Form Pendaftaran Profil Keluarga / Wali Murid Baru (Jika belum terdaftar)
     */
    public function formDaftarWali(Request $request)
    {
        $noKk = trim($request->query('no_kk'));
        if (empty($noKk) || strlen($noKk) !== 16) {
            return redirect()->route('spmb.index')->with('error', 'Silakan masukkan 16 digit Nomor KK terlebih dahulu.');
        }

        // Cek jika ternyata sudah ada
        $existingWali = WaliMurid::whereNoKk($noKk)->first();
        if ($existingWali) {
            return redirect()->route('spmb.daftar-murid', ['wali_murid_id' => $existingWali->id]);
        }

        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? TahunPelajaran::latest()->first();
        $kampungs = Kampung::orderBy('nama_kampung', 'asc')->get();

        return view('spmb.daftar-wali', compact('noKk', 'tahunAktif', 'kampungs'));
    }

    /**
     * Simpan Data Profil Keluarga Baru lalu Lanjut ke Form Murid
     */
    public function storeWali(Request $request)
    {
        $request->validate([
            'no_kk'                => ['required', 'string', 'size:16', new \App\Rules\UniqueEncrypted('wali_murids', 'no_kk_hash')],
            'kepala_keluarga'      => 'required|in:Ayah,Ibu,Wali',
            'nama_kepala_keluarga' => 'required|string|max:100',
            'no_hp'                => 'required|string|max:20',
            'kampung_id'           => 'required|exists:kampungs,id',
            'alamat_detail'        => 'nullable|string',
        ], [
            'no_kk.required'       => 'Nomor Kartu Keluarga (KK) wajib diisi.',
            'no_kk.size'           => 'Nomor KK harus 16 digit.',
            'no_kk.unique'         => 'Nomor KK sudah terdaftar di sistem.',
            'nama_kepala_keluarga.required' => 'Nama kepala keluarga wajib diisi.',
            'no_hp.required'       => 'Nomor HP/WhatsApp aktif wajib diisi.',
            'kampung_id.required'  => 'Pilihan dusun/kampung zonasi wajib dipilih.',
        ]);

        $wali = DB::transaction(function () use ($request) {
            $attempts = 0;
            while ($attempts < 5) {
                try {
                    return WaliMurid::create([
                        'no_registrasi'        => WaliMurid::generateNoRegistrasi(),
                        'no_kk'                => $request->no_kk,
                        'kepala_keluarga'      => $request->kepala_keluarga,
                        'nama_kepala_keluarga' => strtoupper($request->nama_kepala_keluarga),
                        'no_hp'                => $request->no_hp,
                        'alamat_detail'        => $request->alamat_detail,
                        'kampung_id'           => $request->kampung_id,
                        'is_active'            => true,
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    if ($e->errorInfo[1] == 1062 && str_contains($e->getMessage(), 'no_registrasi')) {
                        $attempts++;
                        continue;
                    }
                    throw $e;
                }
            }
            throw new \Exception("Gagal menghasilkan nomor registrasi unik. Silakan coba lagi.");
        });

        return redirect()->route('spmb.daftar-murid', ['wali_murid_id' => $wali->id])
            ->with('success', 'Data profil keluarga berhasil disimpan! Sekarang silakan isi data calon murid yang akan didaftarkan.');
    }

    /**
     * Halaman 2B: Form Pendaftaran Calon Murid (Menggunakan ID Wali Murid yang sudah ada)
     */
    public function formDaftarMurid($wali_murid_id)
    {
        $wali = WaliMurid::with('kampung')->findOrFail($wali_murid_id);

        $tahunAktif = TahunPelajaran::where('is_active', true)->first() ?? TahunPelajaran::latest()->first();

        $levels = Level::with('tingkat')
            ->orderBy('urutan_level', 'asc')
            ->get();

        // Ambil info biaya tagihan terkait SPMB / Pendaftaran pada tahun ajaran aktif
        $biayaSpmb = collect();
        if ($tahunAktif) {
            $biayaSpmb = PengaturanTagihan::where('tahun_pelajaran_id', $tahunAktif->id)
                ->where(function ($q) {
                    $q->where('nama_tagihan', 'like', '%spmb%')
                        ->orWhere('nama_tagihan', 'like', '%daftar%')
                        ->orWhere('nama_tagihan', 'like', '%masuk%')
                        ->orWhere('kode_tagihan', 'like', '%SPMB%')
                        ->orWhere('kode_tagihan', 'like', '%PDF%');
                })
                ->get();

            if ($biayaSpmb->isEmpty()) {
                $biayaSpmb = PengaturanTagihan::where('tahun_pelajaran_id', $tahunAktif->id)->get();
            }
        }

        return view('spmb.daftar-murid', compact('wali', 'tahunAktif', 'levels', 'biayaSpmb'));
    }

    /**
     * Simpan Formulir Pendaftaran Calon Murid
     */
    public function storeMurid(Request $request)
    {
        $request->validate([
            'wali_murid_id'        => 'required|exists:wali_murids,id',
            'tahun_pelajaran_id'   => 'required|exists:tahun_pelajarans,id',
            'level_id'             => 'required|exists:levels,id',

            // Data Murid
            'nama_lengkap'         => 'required|string|max:100',
            'nama_panggilan'       => 'nullable|string|max:50',
            'jenis_kelamin'        => 'required|in:L,P',
            'nik'                  => 'nullable|string|size:16',
            'nisn'                 => 'nullable|string|max:20',
            'tempat_lahir'         => 'nullable|string|max:50',
            'tanggal_lahir'        => 'nullable|date',
            'anak_ke'              => 'nullable|integer|min:1',
            'hub_kel'              => 'required|in:Anak Kandung,Anak Tiri,Anak Angkat,Cucu,Lainnya',

            // Data Ortu
            'nik_ayah'             => 'nullable|string|size:16',
            'nama_ayah'            => 'nullable|string|max:100',
            'status_ayah'          => 'required|in:Hidup,Meninggal',
            'nik_ibu'              => 'nullable|string|size:16',
            'nama_ibu'             => 'nullable|string|max:100',
            'status_ibu'           => 'required|in:Hidup,Meninggal',

            // Foto
            'foto'                 => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'wali_murid_id.required' => 'Data wali murid tidak valid.',
            'nama_lengkap.required'  => 'Nama lengkap calon murid wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'level_id.required'      => 'Pilihan kelas/jenjang masuk wajib ditentukan.',
            'foto.max'               => 'Ukuran foto maksimal 2MB.',
        ]);

        DB::beginTransaction();

        try {
            // Hitung estimasi nominal biaya pendaftaran
            $nominalBiaya = 0;
            $tagihans = PengaturanTagihan::where('tahun_pelajaran_id', $request->tahun_pelajaran_id)
                ->where(function ($q) use ($request) {
                    $q->whereNull('level_id')->orWhere('level_id', $request->level_id);
                })
                ->where(function ($q) {
                    $q->where('nama_tagihan', 'like', '%spmb%')
                        ->orWhere('nama_tagihan', 'like', '%daftar%')
                        ->orWhere('nama_tagihan', 'like', '%masuk%')
                        ->orWhere('kode_tagihan', 'like', '%SPMB%');
                })
                ->get();

            if ($tagihans->isNotEmpty()) {
                $nominalBiaya = $tagihans->sum('nominal');
            }

            // Upload foto jika ada
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('uploads/spmb', 'public');
            }

            // Simpan pendaftaran SPMB dengan retry loop jika terjadi benturan nomor pendaftaran simultan
            $pendaftaran = null;
            $attempts = 0;
            while ($attempts < 5) {
                try {
                    $nomorPendaftaran = PendaftaranSpmb::generateNomorPendaftaran($request->tahun_pelajaran_id);

                    $pendaftaran = PendaftaranSpmb::create([
                        'nomor_pendaftaran'   => $nomorPendaftaran,
                        'tahun_pelajaran_id'  => $request->tahun_pelajaran_id,
                        'level_id'            => $request->level_id,
                        'wali_murid_id'       => $request->wali_murid_id,
                        'nama_lengkap'        => strtoupper($request->nama_lengkap),
                        'nama_panggilan'      => $request->nama_panggilan ? ucwords($request->nama_panggilan) : null,
                        'jenis_kelamin'       => $request->jenis_kelamin,
                        'nik'                 => $request->nik,
                        'nisn'                => $request->nisn,
                        'tempat_lahir'        => $request->tempat_lahir ? strtoupper($request->tempat_lahir) : null,
                        'tanggal_lahir'       => $request->tanggal_lahir,
                        'anak_ke'             => $request->anak_ke,
                        'hub_kel'             => $request->hub_kel,
                        'nik_ayah'            => $request->nik_ayah,
                        'nama_ayah'           => $request->nama_ayah ? strtoupper($request->nama_ayah) : null,
                        'status_ayah'         => $request->status_ayah ?? 'Hidup',
                        'nik_ibu'             => $request->nik_ibu,
                        'nama_ibu'            => $request->nama_ibu ? strtoupper($request->nama_ibu) : null,
                        'status_ibu'          => $request->status_ibu ?? 'Hidup',
                        'foto'                => $fotoPath,
                        'status_pendaftaran'  => 'Menunggu Verifikasi',
                        'nominal_biaya'       => $nominalBiaya,
                        'status_pembayaran'   => 'Belum Lunas',
                    ]);
                    break;
                } catch (\Illuminate\Database\QueryException $e) {
                    if ($e->errorInfo[1] == 1062 && str_contains($e->getMessage(), 'nomor_pendaftaran')) {
                        $attempts++;
                        continue;
                    }
                    throw $e;
                }
            }

            if (!$pendaftaran) {
                throw new \Exception("Gagal menghasilkan nomor pendaftaran unik. Silakan ulangi pengisian formulir.");
            }

            DB::commit();

            return redirect()->route('spmb.bukti', $pendaftaran->nomor_pendaftaran)
                ->with('success', 'Pendaftaran Berhasil! Silakan simpan / cetak bukti pendaftaran dan bawa barcode untuk verifikasi ke Admin Madrasah.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memproses pendaftaran: ' . $e->getMessage());
        }
    }

    /**
     * Backward compatibility store method
     */
    public function store(Request $request)
    {
        return $this->storeMurid($request);
    }

    /**
     * Cari Nomor KK via AJAX (Publik)
     */
    public function searchKk(Request $request)
    {
        $noKk = trim($request->query('no_kk'));

        if (empty($noKk)) {
            return response()->json(['status' => 'error', 'message' => 'Nomor KK wajib diisi.'], 400);
        }

        $wali = WaliMurid::with('kampung')->whereNoKk($noKk)->first();

        if ($wali) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id'                   => $wali->id,
                    'no_kk'                => $wali->no_kk,
                    'nama_kepala_keluarga' => $wali->nama_kepala_keluarga,
                    'kepala_keluarga'      => $wali->kepala_keluarga,
                    'no_hp'                => $wali->no_hp ?? '',
                    'alamat_detail'        => $wali->alamat_detail ?? '',
                    'kampung_id'           => $wali->kampung_id,
                    'nama_kampung'         => $wali->kampung->nama_kampung ?? '-',
                ]
            ]);
        }

        return response()->json(['status' => 'not_found', 'message' => 'Nomor KK belum terdaftar.']);
    }

    /**
     * Halaman Bukti Pendaftaran dengan Barcode / QR Code
     */
    public function bukti($nomor_pendaftaran)
    {
        $pendaftaran = PendaftaranSpmb::with(['tahunPelajaran', 'level.tingkat', 'waliMurid.kampung'])
            ->where('nomor_pendaftaran', $nomor_pendaftaran)
            ->firstOrFail();

        $tagihans = PengaturanTagihan::where('tahun_pelajaran_id', $pendaftaran->tahun_pelajaran_id)
            ->where(function ($q) use ($pendaftaran) {
                $q->whereNull('level_id')->orWhere('level_id', $pendaftaran->level_id);
            })
            ->get();

        return view('spmb.bukti', compact('pendaftaran', 'tagihans'));
    }

    /**
     * Cetak Kartu Bukti Pendaftaran SPMB
     */
    public function cetakBukti($nomor_pendaftaran)
    {
        $pendaftaran = PendaftaranSpmb::with(['tahunPelajaran', 'level.tingkat', 'waliMurid.kampung'])
            ->where('nomor_pendaftaran', $nomor_pendaftaran)
            ->firstOrFail();

        $tagihans = PengaturanTagihan::where('tahun_pelajaran_id', $pendaftaran->tahun_pelajaran_id)
            ->where(function ($q) use ($pendaftaran) {
                $q->whereNull('level_id')->orWhere('level_id', $pendaftaran->level_id);
            })
            ->get();

        return view('spmb.cetak-bukti', compact('pendaftaran', 'tagihans'));
    }

    /**
     * Cek Status Pendaftaran Calon Murid
     */
    public function cekStatus(Request $request)
    {
        $keyword = $request->query('keyword');
        $hasil = collect();

        if ($keyword) {
            $keywordHash = hash_sensitive($keyword);

            $hasil = PendaftaranSpmb::with(['tahunPelajaran', 'level.tingkat', 'waliMurid'])
                ->where('nomor_pendaftaran', 'like', "%{$keyword}%")
                ->orWhere('nik_hash', $keywordHash)
                ->orWhere('nama_lengkap', 'like', "%{$keyword}%")
                ->orWhereHas('waliMurid', function ($q) use ($keywordHash) {
                    $q->where('no_kk_hash', $keywordHash);
                })
                ->latest()
                ->get();
        }

        return view('spmb.cek-status', compact('keyword', 'hasil'));
    }
}
