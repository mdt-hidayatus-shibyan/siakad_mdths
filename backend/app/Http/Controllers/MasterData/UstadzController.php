<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;

use App\Http\Requests\MasterData\ImportUstadzRequest;
use App\Http\Requests\MasterData\UstadzRequest;
use App\Models\Ustadz;
use App\Services\UstadzService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UstadzController extends Controller
{
    public function __construct(
        private readonly UstadzService $ustadzService
    ) {}

    public function index(Request $request)
    {
        $ustadzs = $this->ustadzService->getPaginatedUstadzs(
            $request->only(['search', 'status']),
            12
        );

        return view('ustadz.index', compact('ustadzs'));
    }

    public function create()
    {
        $users = $this->ustadzService->getAvailableUsersForCreate();

        return view('ustadz.form', compact('users'));
    }

    public function store(UstadzRequest $request)
    {
        try {
            $data = $request->except(['foto', 'tanda_tangan', 'email', 'is_active', 'username']);
            $username = $request->input('username');
            $email = $request->input('email');
            $isActive = $request->boolean('is_active', false);
            $foto = $request->hasFile('foto') ? $request->file('foto') : null;
            $tandaTangan = $request->hasFile('tanda_tangan') ? $request->file('tanda_tangan') : null;

            $result = $this->ustadzService->createUstadz(
                $data,
                $foto,
                $tandaTangan,
                $username,
                $email,
                $isActive
            );

            return redirect()->route('ustadz.index')->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $ustadz = $this->ustadzService->findUstadz($id);
        $users = $this->ustadzService->getAvailableUsersForEdit($ustadz->user_id);

        return view('ustadz.edit', compact('ustadz', 'users'));
    }

    public function update(UstadzRequest $request, $id)
    {
        try {
            $formType = $request->input('form_type');
            $foto = $request->hasFile('foto') ? $request->file('foto') : null;
            $tandaTangan = $request->hasFile('tanda_tangan') ? $request->file('tanda_tangan') : null;

            if ($formType === 'profil' || !$formType) {
                $requestData = $request->except(['form_type', '_token', '_method', 'username', 'email']);
            } else {
                $requestData = [
                    'username'  => $request->input('username'),
                    'email'     => $request->input('email'),
                ];
                if ($request->has('is_active')) {
                    $requestData['is_active'] = $request->boolean('is_active');
                }
            }

            $result = $this->ustadzService->updateUstadz(
                $id,
                $requestData,
                $formType,
                $foto,
                $tandaTangan
            );

            if ($request->wantsJson()) {
                return response()->json(['status' => 'success', 'message' => $result['message']]);
            }

            return back()->with('success', $result['message']);
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Gagal: ' . $e->getMessage()], 500);
            }

            return back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $result = $this->ustadzService->toggleStatus($id);

        return response()->json([
            'status'    => 'success',
            'message'   => $result['message'],
            'is_active' => $result['is_active'],
        ]);
    }

    public function destroy(Request $request, Ustadz $ustadz)
    {
        try {
            $this->ustadzService->deleteUstadz($ustadz);

            if ($request->wantsJson()) {
                return response()->json([
                    'status'   => 'success',
                    'message'  => 'Data Ustadz berhasil dihapus!',
                    'redirect' => route('ustadz.index'),
                ], 200);
            }

            return redirect()->route('ustadz.index')->with('success', 'Data Ustadz berhasil dihapus!');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal menghapus data: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function modalImport()
    {
        return view('ustadz.import');
    }

    public function template()
    {
        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=template_import_asatidz.csv',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = [
            'id',
            'kode_ustadz',
            'nigm',
            'nik',
            'nama_lengkap',
            'jenis_kelamin',
            'tempat_lahir',
            'tanggal_lahir',
            'alamat',
            'no_hp',
            'tahun_mulai_mengajar',
            'is_active',
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            fputcsv($file, [
                '',
                'A',
                '1234567890',
                '3501234567890001',
                'Ust. Budi Santoso',
                'L',
                'Surabaya',
                '1990-01-01',
                'Jl. Merdeka No 1',
                '081234567890',
                '2020',
                '1',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(ImportUstadzRequest $request)
    {
        try {
            $result = $this->ustadzService->importFromCsv($request->file('file_import'));

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => $result['message'],
                ]);
            }

            return redirect()->route('ustadz.index')->with('success', $result['message']);
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->route('ustadz.index')->withErrors([
                'file_import' => 'Terjadi kesalahan sistem. Error: ' . $e->getMessage(),
            ]);
        }
    }

    public function resendVerification(Ustadz $ustadz)
    {
        $result = $this->ustadzService->resendVerification($ustadz);

        return back()->with($result['status'], $result['message']);
    }

    public function signature($id)
    {
        $ustadz = $this->ustadzService->findUstadz($id);

        return view('ustadz.signature', compact('ustadz'));
    }

    public function updateSignature(Request $request, $id)
    {
        $request->validate([
            'tanda_tangan_base64' => 'required',
        ]);

        try {
            $ustadz = $this->ustadzService->updateSignature($id, $request->input('tanda_tangan_base64'));

            return redirect()->route('ustadz.index')->with(
                'success',
                'Tanda tangan digital untuk ' . $ustadz->nama_lengkap . ' berhasil disimpan!'
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan tanda tangan: ' . $e->getMessage());
        }
    }

    public function modalUploadFoto($id)
    {
        $ustadz = $this->ustadzService->findUstadz($id);

        return view('ustadz.modal-upload-foto', compact('ustadz'));
    }

    public function updateFoto(Request $request, $id)
    {
        $ustadz = $this->ustadzService->findUstadz($id);

        // Jika opsi hapus foto dipilih (set null)
        if ($request->boolean('hapus_foto') || $request->input('hapus_foto') === 'true' || $request->input('hapus_foto') === '1' || $request->input('hapus_foto') === 1) {
            if ($ustadz->foto && Storage::disk('public')->exists($ustadz->foto)) {
                Storage::disk('public')->delete($ustadz->foto);
            }
            $ustadz->update(['foto' => null]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => 'Foto Ustadz ' . $ustadz->nama_lengkap . ' berhasil dihapus!',
                    'foto_url'  => null,
                    'ustadz_id' => $ustadz->id,
                ]);
            }

            return back()->with('success', 'Foto Ustadz berhasil dihapus!');
        }

        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:3072',
        ], [
            'foto.required' => 'File foto wajib diunggah.',
            'foto.image'    => 'File yang diunggah harus berupa gambar.',
            'foto.mimes'    => 'Format gambar harus JPG, JPEG, PNG, atau WEBP.',
            'foto.max'      => 'Ukuran foto maksimal 3MB.',
        ]);

        if ($ustadz->foto && Storage::disk('public')->exists($ustadz->foto)) {
            Storage::disk('public')->delete($ustadz->foto);
        }

        $ustadz->foto = $request->file('foto')->store('uploads/ustadz/foto', 'public');
        $ustadz->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'    => 'success',
                'message'   => 'Foto Ustadz ' . $ustadz->nama_lengkap . ' berhasil diperbarui!',
                'foto_url'  => asset('storage/' . $ustadz->foto),
                'ustadz_id' => $ustadz->id,
            ]);
        }

        return back()->with('success', 'Foto Ustadz berhasil diperbarui!');
    }

    public function deleteFoto(Request $request, $id)
    {
        $ustadz = $this->ustadzService->findUstadz($id);

        if ($ustadz->foto && Storage::disk('public')->exists($ustadz->foto)) {
            Storage::disk('public')->delete($ustadz->foto);
        }

        $ustadz->update(['foto' => null]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'    => 'success',
                'message'   => 'Foto Ustadz ' . $ustadz->nama_lengkap . ' berhasil dihapus!',
                'foto_url'  => null,
                'ustadz_id' => $ustadz->id,
            ]);
        }

        return back()->with('success', 'Foto Ustadz berhasil dihapus!');
    }
}
