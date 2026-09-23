<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Models\Tingkat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * TAMPILKAN DAFTAR PENGGUNA & MONITORING SESI
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filterRole = $request->input('role');
        $filterStatus = $request->input('status');
        $filterOnline = $request->input('online');
        $filterTingkat = $request->input('tingkat_id');
        $currentUser = auth()->user();

        // 1. Hitung Ringkasan Metrik Akun Pengguna
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $inactiveUsers = User::where('is_active', false)->count();
        $onlineThreshold = Carbon::now()->subMinutes(3);
        $onlineUsers = User::where('last_seen_at', '>=', $onlineThreshold)->count();

        // 2. Query Data Pengguna
        $query = User::with(['roles', 'tingkat', 'administrator', 'ustadz'])
            ->when($search, function ($q, $search) {
                return $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            // Filter Role
            ->when($filterRole, function ($q, $filterRole) {
                return $q->whereHas('roles', function ($r) use ($filterRole) {
                    $r->where('name', $filterRole);
                });
            })
            // Filter Status Aktif/Nonaktif
            ->when($filterStatus !== null && $filterStatus !== '', function ($q) use ($filterStatus) {
                return $q->where('is_active', $filterStatus == '1');
            })
            // Filter Online / Offline
            ->when($filterOnline === 'online', function ($q) use ($onlineThreshold) {
                return $q->where('last_seen_at', '>=', $onlineThreshold);
            })
            ->when($filterOnline === 'offline', function ($q) use ($onlineThreshold) {
                return $q->where(function ($sub) use ($onlineThreshold) {
                    $sub->whereNull('last_seen_at')
                        ->orWhere('last_seen_at', '<', $onlineThreshold);
                });
            })
            // Filter Tingkat
            ->when($filterTingkat, function ($q, $filterTingkat) {
                return $q->where('tingkat_id', $filterTingkat);
            })
            // Sembunyikan superadmin jika user yang login bukan superadmin
            ->when(!$currentUser->hasRole('administrator'), function ($q) {
                return $q->whereDoesntHave('roles', function ($r) {
                    $r->where('name', 'administrator');
                });
            });

        // 3. Sorting & Pagination (Online diutamakan, lalu nama alfabetis)
        $users = $query->orderBy('name', 'asc')
            ->paginate(15)
            ->withQueryString();

        // Urutkan koleksi halaman aktif agar user online berada di atas
        $sortedItems = $users->getCollection()->sortByDesc(function ($user) {
            return $user->isOnline();
        })->values();
        $users->setCollection($sortedItems);

        // 4. Data Master untuk Filter & Form
        $roles = Role::orderBy('name', 'asc')->get();
        $tingkats = Tingkat::orderBy('urutan_tingkat', 'asc')->get();

        // 5. Response AJAX untuk Refresh Grid
        if ($request->ajax() && !$request->has('modal')) {
            return response()->json([
                'html' => view('user.list', compact('users'))->render(),
                'metrics' => [
                    'total'    => $totalUsers,
                    'online'   => $onlineUsers,
                    'active'   => $activeUsers,
                    'inactive' => $inactiveUsers,
                ]
            ]);
        }

        return view('user.index', compact(
            'users',
            'roles',
            'tingkats',
            'totalUsers',
            'onlineUsers',
            'activeUsers',
            'inactiveUsers',
            'search',
            'filterRole',
            'filterStatus',
            'filterOnline',
            'filterTingkat'
        ));
    }

    /**
     * FORM MODAL: TAMBAH PENGGUNA BARU
     */
    public function create(Request $request)
    {
        $roles = Role::orderBy('name', 'asc')->get();
        $tingkats = Tingkat::orderBy('urutan_tingkat', 'asc')->get();
        $user = new User();

        if ($request->ajax()) {
            return view('user.form-modal', compact('user', 'roles', 'tingkats'));
        }

        return view('user.form', compact('user', 'roles', 'tingkats'));
    }

    /**
     * SIMPAN PENGGUNA BARU
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:150',
            'username'              => 'required|string|max:50|alpha_dash|unique:users,username',
            'email'                 => 'nullable|email|max:150|unique:users,email',
            'role'                  => 'required|exists:roles,name',
            'password'              => 'required|string|min:6|confirmed',
            'tingkat_id'            => 'nullable|exists:tingkats,id',
            'is_active'             => 'nullable|boolean',
        ], [
            'name.required'         => 'Nama lengkap wajib diisi.',
            'username.required'     => 'Username wajib diisi.',
            'username.unique'       => 'Username sudah digunakan oleh akun lain.',
            'username.alpha_dash'   => 'Username hanya boleh berisi huruf, angka, strip, dan garis bawah.',
            'email.email'           => 'Format email tidak valid.',
            'email.unique'          => 'Email sudah terdaftar di sistem.',
            'role.required'         => 'Peran/Role wajib dipilih.',
            'password.required'     => 'Password wajib diisi.',
            'password.min'          => 'Password minimal 6 karakter.',
            'password.confirmed'    => 'Konfirmasi password tidak cocok.',
        ]);

        $selectedRole = $request->input('role');
        $tingkatId = in_array($selectedRole, ['administrator', 'petugas-tabungan', 'bendahara'])
            ? null
            : $request->input('tingkat_id');

        $user = User::create([
            'name'              => strtoupper($request->input('name')),
            'username'          => strtolower(trim($request->input('username'))),
            'email'             => $request->filled('email') ? strtolower(trim($request->input('email'))) : null,
            'password'          => Hash::make($request->input('password')),
            'tingkat_id'        => $tingkatId,
            'is_active'         => $request->boolean('is_active', true),
            'email_verified_at' => now(),
        ]);

        $user->syncRoles([$selectedRole]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Akun pengguna {$user->name} berhasil ditambahkan!",
            ]);
        }

        return redirect()->route('pengguna.index')
            ->with('success', "Akun pengguna {$user->name} berhasil ditambahkan!");
    }

    /**
     * FORM MODAL: EDIT PENGGUNA
     */
    public function edit(Request $request, $id)
    {
        $user = User::with(['roles', 'tingkat'])->findOrFail($id);
        $roles = Role::orderBy('name', 'asc')->get();
        $tingkats = Tingkat::orderBy('urutan_tingkat', 'asc')->get();

        if ($request->ajax()) {
            return view('user.form-modal', compact('user', 'roles', 'tingkats'));
        }

        return view('user.form', compact('user', 'roles', 'tingkats'));
    }

    /**
     * UPDATE DATA PENGGUNA
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'                  => 'required|string|max:150',
            'username'              => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email'                 => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'role'                  => 'required|exists:roles,name',
            'password'              => 'nullable|string|min:6|confirmed',
            'tingkat_id'            => 'nullable|exists:tingkats,id',
            'is_active'             => 'nullable|boolean',
        ], [
            'name.required'         => 'Nama lengkap wajib diisi.',
            'username.required'     => 'Username wajib diisi.',
            'username.unique'       => 'Username sudah digunakan oleh akun lain.',
            'username.alpha_dash'   => 'Username hanya boleh berisi huruf, angka, strip, dan garis bawah.',
            'email.email'           => 'Format email tidak valid.',
            'email.unique'          => 'Email sudah terdaftar di sistem.',
            'role.required'         => 'Peran/Role wajib dipilih.',
            'password.min'          => 'Password minimal 6 karakter.',
            'password.confirmed'    => 'Konfirmasi password baru tidak cocok.',
        ]);

        $selectedRole = $request->input('role');
        $tingkatId = in_array($selectedRole, ['administrator', 'petugas-tabungan', 'bendahara'])
            ? null
            : $request->input('tingkat_id');

        $updateData = [
            'name'       => strtoupper($request->input('name')),
            'username'   => strtolower(trim($request->input('username'))),
            'email'      => $request->filled('email') ? strtolower(trim($request->input('email'))) : null,
            'tingkat_id' => $tingkatId,
            'is_active'  => $request->boolean('is_active', true),
        ];

        // Update password jika diisi
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->input('password'));
            $updateData['remember_token'] = null;
            // Bersihkan sesi aktif user jika password diubah
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        $user->update($updateData);
        $user->syncRoles([$selectedRole]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Data akun {$user->name} berhasil diperbarui!",
            ]);
        }

        return redirect()->route('pengguna.index')
            ->with('success', "Data akun {$user->name} berhasil diperbarui!");
    }

    /**
     * HAPUS PENGGUNA
     */
    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Keamanan 1: Cegah user menghapus akunnya sendiri
        if (auth()->id() === $user->id) {
            $msg = 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.';
            return $request->ajax()
                ? response()->json(['success' => false, 'message' => $msg], 403)
                : back()->with('error', $msg);
        }

        // Keamanan 2: Cegah menghapus superadmin terakhir
        if ($user->hasRole('administrator')) {
            $adminCount = User::role('administrator')->count();
            if ($adminCount <= 1) {
                $msg = 'Tidak dapat menghapus Administrator utama terakhir dari sistem.';
                return $request->ajax()
                    ? response()->json(['success' => false, 'message' => $msg], 403)
                    : back()->with('error', $msg);
            }
        }

        $userName = $user->name;

        // Bersihkan sesi database
        DB::table('sessions')->where('user_id', $user->id)->delete();
        $user->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Akun pengguna {$userName} berhasil dihapus dari sistem!",
            ]);
        }

        return redirect()->route('pengguna.index')
            ->with('success', "Akun pengguna {$userName} berhasil dihapus dari sistem!");
    }

    /**
     * TOGGLE STATUS AKTIF / NONAKTIF (AJAX INSTAN)
     */
    public function toggleStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Cegah menonaktifkan akun sendiri
        if (auth()->id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.'
            ], 403);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        // Jika dinonaktifkan, putus sesi aktifnya secara instan
        if (!$user->is_active) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
            $user->update([
                'remember_token' => null,
                'is_login'       => false,
                'is_logout'      => true,
            ]);
        }

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return response()->json([
            'success'   => true,
            'is_active' => $user->is_active,
            'message'   => "Akun {$user->name} berhasil {$statusText}!"
        ]);
    }

    /**
     * RESET PASSWORD PENGGUNA
     */
    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.required'  => 'Password baru wajib diisi.',
            'password.min'       => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user->update([
            'password'       => Hash::make($request->input('password')),
            'remember_token' => null,
        ]);

        // Bersihkan sesi aktif pengguna agar wajib login ulang
        DB::table('sessions')->where('user_id', $user->id)->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Password untuk akun {$user->name} berhasil direset!",
            ]);
        }

        return redirect()->route('pengguna.index')
            ->with('success', "Password untuk akun {$user->name} berhasil direset!");
    }

    /**
     * FORCE LOGOUT: Memutus sesi pengguna secara paksa
     */
    public function forceLogout(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (auth()->id() === $user->id) {
            $msg = 'Anda tidak dapat me-logout akun Anda sendiri dari sini.';
            return $request->ajax()
                ? response()->json(['success' => false, 'message' => $msg], 403)
                : back()->with('error', $msg);
        }

        // Hapus paksa sesi di database session (Web)
        DB::table('sessions')->where('user_id', $user->id)->delete();

        // Cabut seluruh token API Sanctum (Mobile App Ustadz & Wali)
        $user->tokens()->delete();

        // Hancurkan remember_token & set status offline
        $user->update([
            'remember_token' => null,
            'is_login'       => false,
            'is_logout'      => true,
            'last_seen_at'   => Carbon::now()->subMinutes(5)
        ]);

        $msg = "Sesi login {$user->name} berhasil diputus paksa!";

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * HUBUNGI WHATSAPP
     */
    public function hubungiWhatsApp($id)
    {
        $user = User::with(['administrator', 'ustadz'])->findOrFail($id);
        $nomorHp = null;

        if ($user->administrator && $user->administrator->no_hp) {
            $nomorHp = $user->administrator->no_hp;
        } elseif ($user->ustadz && $user->ustadz->no_hp) {
            $nomorHp = $user->ustadz->no_hp;
        }

        if (empty($nomorHp)) {
            return back()->with('error', "Nomor WhatsApp belum didaftarkan untuk profil {$user->name}.");
        }

        $nomorHp = preg_replace('/[^0-9]/', '', $nomorHp);
        if (str_starts_with($nomorHp, '0')) {
            $nomorHp = '62' . substr($nomorHp, 1);
        }

        $pesan = urlencode("Assalamu'alaikum {$user->name}, kami dari Admin MDT Hidayatus Shibyan ingin menginformasikan sesuatu.");

        return redirect()->away("https://wa.me/{$nomorHp}?text={$pesan}");
    }
}
