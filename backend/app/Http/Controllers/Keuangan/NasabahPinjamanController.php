<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Keuangan\NasabahPinjamanRequest;
use App\Models\Keuangan\NasabahPinjaman;
use App\Models\User;
use App\Models\Ustadz;
use App\Models\WaliMurid;
use App\Services\Keuangan\PinjamanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NasabahPinjamanController extends Controller
{
    public function __construct(
        protected PinjamanService $pinjamanService
    ) {}

    public function index(Request $request)
    {
        $search = $request->input('search');
        $tipe = $request->input('tipe_nasabah');

        $query = NasabahPinjaman::with(['pinjamans', 'ustadz', 'waliMurid'])
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('kode_nasabah', 'like', "%{$search}%")
                        ->orWhere('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nik_ktp_hash', hash_sensitive($search))
                        ->orWhere('no_hp', 'like', "%{$search}%");
                });
            })
            ->when($tipe, function ($q, $tipe) {
                $q->where('tipe_nasabah', $tipe);
            });

        $nasabahs = (clone $query)->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $totalNasabah = NasabahPinjaman::count();
        $totalPeminjamAktif = NasabahPinjaman::whereHas('pinjamans', function ($q) {
            $q->whereIn('status', ['disetujui', 'dicairkan', 'macet']);
        })->count();
        $totalUstadzNasabah = NasabahPinjaman::where('tipe_nasabah', 'ustadz')->count();
        $totalWaliNasabah = NasabahPinjaman::where('tipe_nasabah', 'wali_murid')->count();

        if ($request->ajax() && $request->has('table_only')) {
            return view('keuangan.nasabah.list', compact('nasabahs'));
        }

        return view('keuangan.nasabah.index', compact(
            'nasabahs',
            'totalNasabah',
            'totalPeminjamAktif',
            'totalUstadzNasabah',
            'totalWaliNasabah'
        ));
    }

    public function create(Request $request)
    {
        $ustadzs = Ustadz::where('is_active', true)->orderBy('nama_lengkap', 'asc')->get();
        $waliMurids = WaliMurid::orderBy('nama_kepala_keluarga', 'asc')->limit(100)->get();

        if ($request->ajax()) {
            return view('keuangan.nasabah.form-modal', compact('ustadzs', 'waliMurids'));
        }

        return view('keuangan.nasabah.create', compact('ustadzs', 'waliMurids'));
    }

    public function store(NasabahPinjamanRequest $request)
    {
        $data = $request->validated();
        $data['kode_nasabah'] = $data['kode_nasabah'] ?? $this->pinjamanService->generateKodeNasabah();
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('foto_ktp')) {
            $data['foto_ktp'] = $request->file('foto_ktp')->store('keuangan/ktp', 'public');
        }
        if ($request->hasFile('foto_nasabah')) {
            $data['foto_nasabah'] = $request->file('foto_nasabah')->store('keuangan/nasabah', 'public');
        }

        $nasabah = NasabahPinjaman::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Data Nasabah Peminjam berhasil didaftarkan!',
            'data' => $nasabah
        ]);
    }

    public function show(Request $request, $id)
    {
        $nasabah = NasabahPinjaman::with([
            'pinjamans' => function ($q) {
                $q->with(['jaminans', 'angsurans', 'tahunPelajaran'])->orderBy('id', 'desc');
            },
            'ustadz',
            'waliMurid',
            'user'
        ])->findOrFail($id);

        if ($request->ajax()) {
            return view('keuangan.nasabah.show-modal', compact('nasabah'));
        }

        return view('keuangan.nasabah.show', compact('nasabah'));
    }

    public function edit(Request $request, $id)
    {
        $nasabah = NasabahPinjaman::findOrFail($id);
        $ustadzs = Ustadz::where('is_active', true)->orderBy('nama_lengkap', 'asc')->get();
        $waliMurids = WaliMurid::orderBy('nama_lengkap', 'asc')->limit(100)->get();

        if ($request->ajax()) {
            return view('keuangan.nasabah.form-modal', compact('nasabah', 'ustadzs', 'waliMurids'));
        }

        return view('keuangan.nasabah.edit', compact('nasabah', 'ustadzs', 'waliMurids'));
    }

    public function update(NasabahPinjamanRequest $request, $id)
    {
        $nasabah = NasabahPinjaman::findOrFail($id);
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('foto_ktp')) {
            if ($nasabah->foto_ktp && Storage::disk('public')->exists($nasabah->foto_ktp)) {
                Storage::disk('public')->delete($nasabah->foto_ktp);
            }
            $data['foto_ktp'] = $request->file('foto_ktp')->store('keuangan/ktp', 'public');
        }

        if ($request->hasFile('foto_nasabah')) {
            if ($nasabah->foto_nasabah && Storage::disk('public')->exists($nasabah->foto_nasabah)) {
                Storage::disk('public')->delete($nasabah->foto_nasabah);
            }
            $data['foto_nasabah'] = $request->file('foto_nasabah')->store('keuangan/nasabah', 'public');
        }

        $nasabah->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Data Nasabah Peminjam berhasil diperbarui!'
        ]);
    }

    public function destroy($id)
    {
        $nasabah = NasabahPinjaman::findOrFail($id);

        if ($nasabah->pinjamans()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data nasabah tidak dapat dihapus karena memiliki riwayat pinjaman!'
            ], 422);
        }

        if ($nasabah->foto_ktp) {
            Storage::disk('public')->delete($nasabah->foto_ktp);
        }
        if ($nasabah->foto_nasabah) {
            Storage::disk('public')->delete($nasabah->foto_nasabah);
        }

        $nasabah->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data Nasabah berhasil dihapus!'
        ]);
    }

    public function ajaxLookup(Request $request)
    {
        $type = $request->input('type');
        $id = $request->input('id');

        if ($type === 'ustadz' && $id) {
            $ustadz = Ustadz::find($id);
            if ($ustadz) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'nama_lengkap' => $ustadz->nama_lengkap,
                        'nik_ktp' => $ustadz->nik ?? '',
                        'no_hp' => $ustadz->nomor_hp ?? $ustadz->no_hp ?? '',
                        'alamat' => $ustadz->alamat ?? '',
                        'pekerjaan' => 'Ustadz / Tenaga Pendidik',
                    ]
                ]);
            }
        } elseif ($type === 'wali_murid' && $id) {
            $wali = WaliMurid::find($id);
            if ($wali) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'nama_lengkap' => $wali->nama_kepala_keluarga,
                        'nik_ktp' => $wali->nik ?? '',
                        'no_hp' => $wali->nomor_hp ?? $wali->no_hp ?? '',
                        'alamat' => $wali->alamat ?? '',
                        'pekerjaan' => $wali->pekerjaan ?? 'Wali Murid',
                    ]
                ]);
            }
        }

        return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan']);
    }
}
