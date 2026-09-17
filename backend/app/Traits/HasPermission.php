<?php

namespace App\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

trait HasPermission
{
    protected $abilities = [
        'show'               => 'read',
        'index'              => 'read',
        'create'             => 'create',
        'store'              => 'create',
        'edit'               => 'update',
        'update'             => 'update',
        'destroy'            => 'delete',
        'delete'             => 'delete',
        'import'             => 'create',
        'modalImport'        => 'create',
        'export'             => 'read',
        'exportExcel'        => 'read',
        'cetak'              => 'read',
        'toggleStatus'       => 'update',
        'signature'          => 'update',
        'updateSignature'    => 'update',
        'resendVerification' => 'update',
        'forceLogout'        => 'update',
        'resetPassword'      => 'update',
        'hubungiWhatsApp'    => 'read',
        'givePermissions'    => 'update',
        'syncRoles'          => 'update',
        'resetDirectPermissions' => 'update',
        'copyRolePermissions' => 'update',
        'updateOrder'        => 'update',
        'toggleActive'       => 'update',
        'formSetorTunai'     => 'create',
        'setorTunai'         => 'create',
        'formTarikTunai'     => 'create',
        'tarikTunai'         => 'create',
        'simpanPembayaran'   => 'create',
        'updatePembayaran'   => 'update',
        'destroyPembayaran'  => 'delete',
    ];

    public function callAction($method, $parameters)
    {
        $action = Arr::get($this->abilities, $method);

        if (!$action) {
            return parent::callAction($method, $parameters);
        }

        $user = Auth::user();
        if (!$user) {
            return parent::callAction($method, $parameters);
        }

        // Administrator (Super Admin) memiliki akses penuh ke seluruh modul
        if ($user->hasRole('administrator')) {
            return parent::callAction($method, $parameters);
        }

        $urlMenu = urlMenu();
        $routeName = request()->route()?->getName();
        $staticPath = trim(request()->route()?->getCompiled()?->getStaticPrefix() ?? '', '/');

        // Kumpulkan kandidat URL / Route identifier
        $candidates = [];

        if ($routeName) {
            $candidates[] = $routeName;
            $parts = explode('.', $routeName);
            if (count($parts) > 1) {
                $candidates[] = $parts[0] . '.index';
                $candidates[] = $parts[0];
                $candidates[] = implode('.', array_slice($parts, 0, -1)) . '.index';
                $candidates[] = implode('.', array_slice($parts, 0, -1));
            }
        }

        if ($staticPath) {
            $candidates[] = $staticPath;
            $candidates[] = $staticPath . '.index';
            $pathParts = explode('/', $staticPath);
            $candidates[] = $pathParts[0];
            $candidates[] = $pathParts[0] . '.index';
        }

        $candidates = array_unique(array_filter($candidates));

        // 1. Cari yang terdaftar di urlMenu()
        $matchedTarget = null;
        foreach ($candidates as $cand) {
            if (in_array($cand, $urlMenu)) {
                $matchedTarget = $cand;
                break;
            }
        }

        // 2. Jika tidak ada di urlMenu, cari kandidat yang permission-nya terdaftar di database
        if (!$matchedTarget) {
            foreach ($candidates as $cand) {
                if (\Spatie\Permission\Models\Permission::where('name', "$action $cand")->exists()) {
                    $matchedTarget = $cand;
                    break;
                }
            }
        }

        // 3. Jika target modul teridentifikasi, lakukan otorisasi
        if ($matchedTarget) {
            $hasPermission = $user->can("$action $matchedTarget");

            if (!$hasPermission) {
                // Cek fallback alternatif (dengan atau tanpa .index)
                $altTarget = str_ends_with($matchedTarget, '.index')
                    ? substr($matchedTarget, 0, -6)
                    : $matchedTarget . '.index';

                $hasPermission = $user->can("$action $altTarget");
            }

            if (!$hasPermission) {
                abort(403, "Akses ditolak! Anda tidak memiliki izin ($action) untuk modul $matchedTarget.");
            }
        }

        return parent::callAction($method, $parameters);
    }
}
