<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman form profil.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Memperbarui data profil pengguna (Nama, Username/Email).
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        // Jika ada perubahan pada email/username, reset verifikasi (opsional)
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        // Redirect kembali dengan pesan sukses
        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Menghapus akun pengguna.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }


    /**
     * Memperbarui biodata administrator yang sedang login.
     */
    public function updateAdministrator(Request $request)
    {
        $user = $request->user();
        $administrator = $user->administrator;

        // Pastikan user ini benar-benar memiliki data administrator
        if (!$administrator) {
            return back()->with('error', 'Profil Administrator tidak ditemukan untuk akun ini.');
        }

        // 1. Validasi Input
        $validated = $request->validate([
            'nik'           => ['nullable', 'string', 'size:16', new \App\Rules\UniqueEncrypted('administrators', 'nik_hash', $administrator->id)],
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir'  => 'nullable|string|max:50',
            'tanggal_lahir' => 'nullable|date',
            'no_hp'         => 'nullable|string|max:15',
            'alamat'        => 'nullable|string|max:500',
            'foto'          => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'tanda_tangan'  => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ], [
            'nik.unique' => 'NIK ini sudah digunakan oleh akun lain.',
            'nik.size'   => 'NIK harus tepat 16 digit.',
            'foto.max'   => 'Ukuran foto maksimal 2MB.',
        ]);

        DB::beginTransaction();

        $newFotoPath = null;
        $newTtdPath = null;

        try {
            // 2. Proses Upload Foto (Hapus yang lama jika ada)
            if ($request->hasFile('foto')) {
                if ($administrator->foto && Storage::disk('public')->exists($administrator->foto)) {
                    Storage::disk('public')->delete($administrator->foto);
                }
                $newFotoPath = $request->file('foto')->store('uploads/administrator/foto', 'public');
                $validated['foto'] = $newFotoPath;
            }

            // 3. Proses Upload Tanda Tangan (Hapus yang lama jika ada)
            if ($request->hasFile('tanda_tangan')) {
                if ($administrator->tanda_tangan && Storage::disk('public')->exists($administrator->tanda_tangan)) {
                    Storage::disk('public')->delete($administrator->tanda_tangan);
                }
                $newTtdPath = $request->file('tanda_tangan')->store('uploads/administrator/ttd', 'public');
                $validated['tanda_tangan'] = $newTtdPath;
            }

            // 4. Update data ke database
            $administrator->update($validated);

            DB::commit();

            // Kembalikan ke halaman sebelumnya dengan pesan sukses
            return back()->with('success', 'Biodata berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();

            // Hapus file baru jika transaksi gagal
            if ($newFotoPath && Storage::disk('public')->exists($newFotoPath)) {
                Storage::disk('public')->delete($newFotoPath);
            }
            if ($newTtdPath && Storage::disk('public')->exists($newTtdPath)) {
                Storage::disk('public')->delete($newTtdPath);
            }

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
