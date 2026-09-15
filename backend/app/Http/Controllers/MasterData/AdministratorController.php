<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;

use App\Http\Requests\MasterData\AdministratorRequest;

use App\Models\Administrator;
use App\Models\Tingkat;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class AdministratorController extends Controller
{


    public function index(Request $request)
    {
        $search = $request->input('search');

        $administrators = Administrator::with('user.roles', 'tingkat')->when($search, function ($query, $search) {
            return $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', '%' . $search . '%')
                    ->orWhere('nik_hash', hash_sensitive($search));
            });
        })
            ->orderBy('nama_lengkap', 'asc')
            ->paginate(12)
            ->withQueryString();

        return view('administrator.index', compact('administrators'));
    }

    public function create()
    {
        $users = User::doesntHave('administrator')->orderBy('name', 'asc')->get();
        $tingkats = Tingkat::orderBy('urutan_tingkat', 'asc')->get();
        $roles = Role::whereIn('name', ['administrator', 'petugas-tabungan', 'staff', 'petugas-cetak'])->get();
        return view('administrator.form', compact('users', 'tingkats', 'roles'));
    }

    public function store(AdministratorRequest $request)
    {
        DB::beginTransaction();

        try {
            $selectedRole = $request->input('role', 'administrator');

            // 1. Ambil semua data KECUALI yang tidak ada di tabel administrators
            $data = $request->except(['username', 'email', 'role']);
            $data['is_active'] = $request->boolean('is_active', false);

            // Jika role administrator atau petugas-tabungan, hilangkan/set tingkat_id ke null
            if ($selectedRole === 'administrator' || $selectedRole === 'petugas-tabungan' || $selectedRole === 'petugas-tabungan') {
                $data['tingkat_id'] = null;
            } else {
                $data['tingkat_id'] = $request->tingkat_id;
            }

            $user = null; // Inisialisasi variabel user kosong

            // 2. CEK KONDISI: Jika username DAN email diisi, baru buatkan akun User
            if ($request->filled('username') && $request->filled('email')) {
                $user = User::create([
                    'name'       => $request->nama_lengkap,
                    'username'   => $request->username,
                    'email'      => $request->email,
                    'tingkat_id' => $data['tingkat_id'],
                    'password'   => Hash::make('madrasah123'), // Password default
                    'is_active'  => true,
                    'email_verified_at' => now(),
                ]);

                // Berikan role yang dipilih
                $user->syncRoles([$selectedRole]);

                // Tautkan ID user ke data profil Administrator
                $data['user_id'] = $user->id;
            } else {
                // Jika kosong, pastikan user_id bernilai null
                $data['user_id'] = null;
            }

            // 3. Proses Upload Foto Profil
            if ($request->hasFile('foto')) {
                $data['foto'] = $request->file('foto')->store('uploads/administrator/foto', 'public');
            }

            // 4. Proses Upload Tanda Tangan
            if ($request->hasFile('tanda_tangan')) {
                $data['tanda_tangan'] = $request->file('tanda_tangan')->store('uploads/administrator/ttd', 'public');
            }

            // 5. Simpan Profil Administrator
            Administrator::create($data);

            DB::commit();

            // 6. Penanganan Notifikasi & Redirect Berdasarkan Pembuatan Akun
            if ($user) {
                $pesanSukses = "Data Administrator berhasil ditambahkan! Akun login dibuat dengan username: {$user->username} (Peran: {$selectedRole})";
            } else {
                $pesanSukses = "Data Administrator berhasil ditambahkan (Tanpa akses login).";
            }

            return redirect()->route('administrator.index')->with('success', $pesanSukses);
        } catch (\Exception $e) {
            DB::rollBack();

            // Hapus file yang terlanjur ter-upload jika database transaction gagal/error
            if (isset($data['foto']) && Storage::disk('public')->exists($data['foto'])) {
                Storage::disk('public')->delete($data['foto']);
            }
            if (isset($data['tanda_tangan']) && Storage::disk('public')->exists($data['tanda_tangan'])) {
                Storage::disk('public')->delete($data['tanda_tangan']);
            }

            return back()->withInput()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function edit(Administrator $administrator)
    {
        // Ambil User yang belum terhubung, DITAMBAH User yang saat ini dimiliki oleh Administrator ini
        $users = User::whereDoesntHave('administrator')
            ->orWhere('id', $administrator->user_id)
            ->orderBy('name', 'asc')
            ->get();

        // Ambil data tingkat
        $tingkats = Tingkat::orderBy('urutan_tingkat', 'asc')->get();

        // Ambil data roles
        $roles = Role::whereIn('name', ['administrator', 'petugas-tabungan', 'staff', 'petugas-cetak'])->get();
        $currentRole = $administrator->user?->roles->first()?->name ?? ($administrator->tingkat_id ? 'staff' : 'administrator');

        return view('administrator.edit', compact('administrator', 'users', 'tingkats', 'roles', 'currentRole'));
    }


    public function update(Request $request, Administrator $administrator)
    {
        DB::beginTransaction();

        $formType = $request->input('form_type'); // Menangkap form mana yang diklik
        $newFotoPath = null;
        $newTtdPath = null;

        try {
            if ($formType === 'profil') {
                // ==========================================
                // JIKA YANG DIKLIK TOMBOL "PERBARUI PROFIL"
                // ==========================================
                $selectedRole = $request->input('role', 'administrator');
                $data = $request->except(['form_type', '_token', '_method', 'role']);

                // Jika role administrator atau petugas-tabungan, tingkat_id = null
                if ($selectedRole === 'administrator' || $selectedRole === 'petugas-tabungan') {
                    $data['tingkat_id'] = null;
                } else {
                    $data['tingkat_id'] = $request->tingkat_id;
                }

                // Proses Upload Foto Baru
                if ($request->hasFile('foto')) {
                    if ($administrator->foto && Storage::disk('public')->exists($administrator->foto)) {
                        Storage::disk('public')->delete($administrator->foto);
                    }
                    $newFotoPath = $request->file('foto')->store('uploads/administrator/foto', 'public');
                    $data['foto'] = $newFotoPath;
                }

                // Proses Upload Tanda Tangan Baru
                if ($request->hasFile('tanda_tangan')) {
                    if ($administrator->tanda_tangan && Storage::disk('public')->exists($administrator->tanda_tangan)) {
                        Storage::disk('public')->delete($administrator->tanda_tangan);
                    }
                    $newTtdPath = $request->file('tanda_tangan')->store('uploads/administrator/ttd', 'public');
                    $data['tanda_tangan'] = $newTtdPath;
                }

                // Update Tabel Administrator & Tingkat di User
                $administrator->update($data);
                if ($administrator->user) {
                    $administrator->user->update([
                        'name'       => $request->nama_lengkap,
                        'tingkat_id' => $data['tingkat_id'],
                    ]);
                    $administrator->user->syncRoles([$selectedRole]);
                }

                $pesanSukses = "Data Profil & Peran berhasil diperbarui!";
            } elseif ($formType === 'akun') {
                // ==========================================
                // JIKA YANG DIKLIK TOMBOL "PERBARUI AKUN"
                // ==========================================
                $user = $administrator->user;
                if ($user) {
                    $user->update([
                        'username' => $request->username,
                        'email'    => $request->email,
                        'email_verified_at' => now(),
                    ]);
                }

                // Update status aktif (ada di tabel administrator)
                $administrator->update([
                    'is_active' => $request->boolean('is_active', false)
                ]);

                $pesanSukses = "Pengaturan Akun Login berhasil diperbarui!";
            }

            DB::commit();

            return back()->with('success', $pesanSukses);
        } catch (\Exception $e) {
            DB::rollBack();

            if ($newFotoPath && Storage::disk('public')->exists($newFotoPath)) {
                Storage::disk('public')->delete($newFotoPath);
            }
            if ($newTtdPath && Storage::disk('public')->exists($newTtdPath)) {
                Storage::disk('public')->delete($newTtdPath);
            }

            return back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $administrator = Administrator::findOrFail($id);

        // Balikkan nilai statusnya (Jika true jadi false, jika false jadi true)
        $administrator->is_active = !$administrator->is_active;
        $administrator->save();

        // Wajib sinkronkan status ke tabel users agar akses login juga terputus/terhubung
        if ($administrator->user) {
            $administrator->user->is_active = $administrator->is_active;
            $administrator->user->save();
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Status Administrator berhasil diubah!',
            'is_active' => $administrator->is_active
        ]);
    }


    public function destroy(Request $request, Administrator $administrator)
    {
        DB::beginTransaction();

        try {

            if ($administrator->foto && Storage::disk('public')->exists($administrator->foto)) {
                Storage::disk('public')->delete($administrator->foto);
            }

            if ($administrator->tanda_tangan && Storage::disk('public')->exists($administrator->tanda_tangan)) {
                Storage::disk('public')->delete($administrator->tanda_tangan);
            }

            $user = $administrator->user;

            $administrator->delete();

            if ($user) {
                $user->delete();
            }
            DB::commit();
            if ($request->wantsJson()) {
                return response()->json([
                    'status'   => 'success',
                    'message'  => 'Data Administrator berhasil dihapus!',
                    'redirect' => route('administrator.index')
                ], 200);
            }

            return redirect()->route('administrator.index')->with('success', 'Data Administrator berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal menghapus data: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function resendVerification(Administrator $administrator)
    {
        $user = $administrator->user;

        // Pastikan user ada dan belum diverifikasi
        if ($user && !$user->hasVerifiedEmail()) {
            // Perintah bawaan Laravel untuk mengirim ulang email verifikasi
            $user->sendEmailVerificationNotification();

            return back()->with('success', 'Email verifikasi ulang berhasil dikirim ke ' . $user->email);
        }

        return back()->with('info', 'Akun ini sudah terverifikasi atau tidak valid.');
    }

    public function signature($id)
    {
        $administrator = Administrator::findOrFail($id);
        return view('administrator.signature', compact('administrator'));
    }

    public function updateSignature(Request $request, $id)
    {
        $administrator = Administrator::findOrFail($id);
        $request->validate([
            'tanda_tangan_base64' => 'required'
        ]);

        try {
            // Memproses string Base64 dari Canvas
            $image_parts = explode(";base64,", $request->tanda_tangan_base64);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);

            // Membuat nama file unik
            $fileName = 'ttd_' . uniqid() . '.' . $image_type;
            $newTtdPath = 'uploads/administrator/ttd/' . $fileName;

            // Hapus file TTD lama jika ada
            if ($administrator->tanda_tangan && Storage::disk('public')->exists($administrator->tanda_tangan)) {
                Storage::disk('public')->delete($administrator->tanda_tangan);
            }

            // Simpan gambar baru ke storage
            Storage::disk('public')->put($newTtdPath, $image_base64);

            // Update database
            $administrator->update(['tanda_tangan' => $newTtdPath]);

            return redirect()->route('administrator.index')->with('success', 'Tanda tangan digital untuk ' . $administrator->nama_lengkap . ' berhasil disimpan!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan tanda tangan: ' . $e->getMessage());
        }
    }
}
