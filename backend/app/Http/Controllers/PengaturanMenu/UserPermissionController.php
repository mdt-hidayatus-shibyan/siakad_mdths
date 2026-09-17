<?php

namespace App\Http\Controllers\PengaturanMenu;

use App\Http\Controllers\Controller;
use App\Models\KonfigurasiMenu\Menu;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserPermissionController extends Controller
{
    /**
     * TAMPILKAN MATRIKS HAK AKSES PENGGUNA (PER-USER RBAC)
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filterRole = $request->input('role');
        $onlineThreshold = Carbon::now()->subMinutes(3);
        $currentUser = auth()->user();

        // 1. Query Daftar Pengguna untuk Sidebar
        $usersQuery = User::with(['roles', 'tingkat', 'administrator', 'ustadz'])
            ->when($search, function ($q, $search) {
                return $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filterRole, function ($q, $filterRole) {
                return $q->whereHas('roles', function ($r) use ($filterRole) {
                    $r->where('name', $filterRole);
                });
            })
            ->when(!$currentUser->hasRole('administrator'), function ($q) {
                return $q->whereDoesntHave('roles', function ($r) {
                    $r->where('name', 'administrator');
                });
            });

        $users = $usersQuery->orderBy('name', 'asc')->get();

        // Urutkan koleksi agar user online berada di atas
        $users = $users->sortByDesc(function ($u) {
            return $u->isOnline();
        })->values();

        // 2. Tentukan Pengguna yang Sedang Aktif Dipilih
        $activeUser = null;
        $directPermissions = [];
        $rolePermissions = [];
        $effectivePermissions = [];
        $userRoleNames = [];

        if ($request->filled('user_id')) {
            $activeUser = User::with(['roles.permissions', 'permissions', 'tingkat', 'administrator', 'ustadz'])
                ->find($request->user_id);
        }

        // Fallback ke user pertama dalam daftar jika belum ada yang dipilih
        if (!$activeUser && $users->isNotEmpty()) {
            $activeUser = User::with(['roles.permissions', 'permissions', 'tingkat', 'administrator', 'ustadz'])
                ->find($users->first()->id);
        }

        if ($activeUser) {
            // Ambil izin yang diberikan secara langsung ke user (Direct)
            $directPermissions = $activeUser->getDirectPermissions()->pluck('name')->toArray();

            // Ambil izin yang diwarisi dari role (Inherited via Roles)
            $rolePermissions = $activeUser->getPermissionsViaRoles()->pluck('name')->toArray();

            // Ambil semua izin gabungan (Direct + Roles)
            $effectivePermissions = $activeUser->getAllPermissions()->pluck('name')->toArray();

            // Daftar nama role aktif user
            $userRoleNames = $activeUser->roles->pluck('name')->toArray();
        }

        // 3. Ambil Struktur Menu & Permissions untuk Matriks
        $matrixMenus = Menu::with(['permissions', 'subMenus.permissions'])
            ->whereNull('main_menu_id')
            ->orderBy('orders', 'ASC')
            ->get();

        // 4. Ambil Semua Role untuk Form Penetapan Role
        $allRoles = Role::orderBy('name', 'asc')->get();

        return view('pengaturan-menu.user-permissions.index', compact(
            'users',
            'activeUser',
            'matrixMenus',
            'directPermissions',
            'rolePermissions',
            'effectivePermissions',
            'userRoleNames',
            'allRoles',
            'search',
            'filterRole'
        ));
    }

    /**
     * AUTO-SAVE HAK AKSES LANGSUNG PENGGUNA (AJAX TOGGLE)
     */
    public function givePermissions(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Ambil array nama permission yang dikirim
        $permissions = $request->permissions ?? [];

        // Sinkronkan direct permissions khusus untuk user ini
        $user->syncPermissions($permissions);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => "Hak akses khusus untuk pengguna {$user->name} berhasil disimpan!",
                'direct_count' => count($permissions),
            ]);
        }

        return back()->with('success', "Hak akses khusus untuk pengguna {$user->name} berhasil disimpan!");
    }

    /**
     * SINKRONISASI ROLE PENGGUNA
     */
    public function syncRoles(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'roles'   => 'nullable|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $roles = $request->input('roles', []);

        // Cegah mencabut role administrator dari superadmin terakhir
        if ($user->hasRole('administrator') && !in_array('administrator', $roles)) {
            $adminCount = User::role('administrator')->count();
            if ($adminCount <= 1) {
                $msg = 'Tidak dapat mencabut wewenang Administrator dari akun admin terakhir.';
                return $request->ajax()
                    ? response()->json(['status' => 'error', 'message' => $msg], 422)
                    : back()->with('error', $msg);
            }
        }

        $user->syncRoles($roles);

        $msg = "Penetapan peran untuk pengguna {$user->name} berhasil diperbarui!";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * RESET DIRECT PERMISSIONS: Kembalikan murni ke default role
     */
    public function resetDirectPermissions(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Bersihkan seluruh direct permissions user
        $user->syncPermissions([]);

        $msg = "Hak akses khusus {$user->name} berhasil direset ke izin default peran!";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * SALIN IZIN DARI ROLE: Duplikat seluruh izin role menjadi direct permissions
     */
    public function copyRolePermissions(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $rolePerms = $user->getPermissionsViaRoles()->pluck('name')->toArray();
        $user->syncPermissions($rolePerms);

        $msg = "Seluruh izin dari peran aktif berhasil disalin menjadi hak akses khusus {$user->name}!";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => $msg,
                'count'   => count($rolePerms),
            ]);
        }

        return back()->with('success', $msg);
    }
}
