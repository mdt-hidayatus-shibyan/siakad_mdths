<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\Request;

class AppVersionController extends Controller
{
    /**
     * Tampilkan halaman utama (Riwayat Rilis / Changelog Web SIAKAD)
     */
    public function index(Request $request)
    {
        $search = $request->query('q');

        $query = AppVersion::query()->where('app_type', 'web');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('version', 'like', "%{$search}%")
                    ->orWhere('build_number', 'like', "%{$search}%")
                    ->orWhere('app_title', 'like', "%{$search}%")
                    ->orWhere('release_subtitle', 'like', "%{$search}%")
                    ->orWhere('dev_name', 'like', "%{$search}%");
            });
        }

        $versions = $query->orderBy('is_latest', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $latestVersion = AppVersion::where('app_type', 'web')->where('is_active', true)->latest('id')->first();
        $totalVersions = AppVersion::where('app_type', 'web')->count();

        return view('pengaturan-versi.riwayat', compact(
            'versions',
            'search',
            'latestVersion',
            'totalVersions'
        ));
    }

    /**
     * Tampilkan halaman formulir editor versi & catatan rilis Web SIAKAD
     */
    public function editor(Request $request, $id = null)
    {
        $editId = $id ?? $request->query('id');

        if ($editId) {
            $version = AppVersion::where('app_type', 'web')->findOrFail($editId);
        } else {
            $version = AppVersion::getActiveVersion('web');
            if (!$version) {
                $version = AppVersion::create(AppVersion::defaultWebData());
            }
        }

        $allVersions = AppVersion::where('app_type', 'web')
            ->latest('id')
            ->get();

        return view('pengaturan-versi.index', compact('version', 'allVersions'));
    }

    /**
     * Form tambah versi rilis baru Web SIAKAD
     */
    public function create()
    {
        $template = AppVersion::where('app_type', 'web')->latest('id')->first();
        $version = new AppVersion([
            'app_type'          => 'web',
            'app_title'         => 'SIAKAD Web MDTHS',
            'app_subtitle'      => 'MDT Hidayatus Shibyan',
            'version'           => '1.4.7',
            'build_number'      => date('Y.m.d'),
            'release_date'      => \Carbon\Carbon::now()->translatedFormat('F Y'),
            'release_subtitle'  => 'Pembaruan Fitur & Perbaikan Sistem',
            'release_badge'     => 'Rilis Baru',
            'status_badge'      => 'Versi Terbaru',
            'is_latest'         => true,
            'is_active'         => true,
            'new_features'      => [],
            'improvements'      => [],
            'dev_name'          => $template?->dev_name ?? 'Mikyal Adly Ghoffar Hasin',
            'dev_role'          => $template?->dev_role ?? 'Lead Developer & Tim IT',
            'dev_institution'   => $template?->dev_institution ?? 'MDT Hidayatus Shibyan',
            'dev_description'   => $template?->dev_description ?? 'Sistem Informasi Akademik berbasis web untuk mempermudah operasional dan manajemen pendidikan madrasah secara terintegrasi.',
            'tech_stacks'       => $template?->tech_stacks ?? ['Laravel', 'PHP', 'Blade', 'Tailwind CSS', 'Alpine.js', 'MySQL'],
            'copyright_year'    => date('Y'),
            'copyright_owner'   => 'MDT Hidayatus Shibyan',
            'copyright_subtitle' => 'All Rights Reserved • SIAKAD MDTHS',
        ]);

        $allVersions = AppVersion::where('app_type', 'web')->latest('id')->get();
        $isNew = true;

        return view('pengaturan-versi.index', compact('version', 'allVersions', 'isNew'));
    }

    /**
     * Simpan / Perbarui versi dan changelog Web SIAKAD
     */
    public function update(Request $request)
    {
        $request->validate([
            'version'           => 'required|string|max:30',
            'build_number'      => 'required|string|max:30',
            'release_date'      => 'required|string|max:50',
            'release_subtitle'  => 'nullable|string|max:100',
            'release_badge'     => 'nullable|string|max:50',
            'status_badge'      => 'nullable|string|max:50',
            'app_title'         => 'required|string|max:100',
            'app_subtitle'      => 'required|string|max:100',
            'dev_name'          => 'required|string|max:100',
            'dev_role'          => 'nullable|string|max:100',
            'dev_institution'   => 'nullable|string|max:100',
            'dev_description'   => 'nullable|string',
            'tech_stacks'       => 'nullable|string',
            'feature_titles'    => 'nullable|array',
            'feature_descs'     => 'nullable|array',
            'improve_titles'    => 'nullable|array',
            'improve_descs'     => 'nullable|array',
        ]);

        $appType = 'web';
        $id = $request->input('id');
        $isLatest = $request->has('is_latest');

        // Olah New Features
        $newFeatures = [];
        if ($request->has('feature_titles') && is_array($request->feature_titles)) {
            foreach ($request->feature_titles as $index => $title) {
                $desc = $request->feature_descs[$index] ?? '';
                if (!empty(trim($title))) {
                    $newFeatures[] = [
                        'title'       => trim($title),
                        'description' => trim($desc),
                    ];
                }
            }
        }

        // Olah Improvements
        $improvements = [];
        if ($request->has('improve_titles') && is_array($request->improve_titles)) {
            foreach ($request->improve_titles as $index => $title) {
                $desc = $request->improve_descs[$index] ?? '';
                if (!empty(trim($title))) {
                    $improvements[] = [
                        'title'       => trim($title),
                        'description' => trim($desc),
                    ];
                }
            }
        }

        // Olah Tech Stacks
        $techStacks = [];
        if (!empty($request->tech_stacks)) {
            $rawStacks = explode(',', $request->tech_stacks);
            foreach ($rawStacks as $st) {
                if (!empty(trim($st))) {
                    $techStacks[] = trim($st);
                }
            }
        } else {
            $techStacks = ['Laravel', 'PHP', 'Blade', 'Tailwind CSS', 'Alpine.js', 'MySQL'];
        }

        $devDetails = [
            [
                'icon'  => 'dns',
                'label' => 'Web Engine',
                'value' => 'Laravel 11 & PHP 8.2+',
            ],
            [
                'icon'  => 'database',
                'label' => 'Database',
                'value' => 'MySQL / MariaDB',
            ],
            [
                'icon'  => 'security',
                'label' => 'Keamanan Autentikasi',
                'value' => 'Bcrypt & Session Guard',
            ],
            [
                'icon'  => 'domain',
                'label' => 'Lembaga',
                'value' => $request->dev_institution ?? 'MDT Hidayatus Shibyan',
            ],
        ];

        // Jika versi ini ditandai sebagai latest, matikan is_latest pada versi web lain
        if ($isLatest) {
            AppVersion::where('app_type', 'web')
                ->when($id, fn($q) => $q->where('id', '!=', $id))
                ->update(['is_latest' => false]);
        }

        $payload = [
            'app_type'          => 'web',
            'app_title'         => $request->app_title,
            'app_subtitle'      => $request->app_subtitle,
            'version'           => $request->version,
            'build_number'      => $request->build_number,
            'release_date'      => $request->release_date,
            'release_subtitle'  => $request->release_subtitle ?? 'Rilis Pembaruan',
            'release_badge'     => $request->release_badge ?? 'Rilis Saat Ini',
            'status_badge'      => $request->status_badge ?? 'Versi Terbaru',
            'is_latest'         => $isLatest,
            'is_active'         => true,
            'new_features'      => $newFeatures,
            'improvements'      => $improvements,
            'dev_name'          => $request->dev_name,
            'dev_role'          => $request->dev_role ?? 'Lead Developer & Tim IT',
            'dev_institution'   => $request->dev_institution ?? 'MDT Hidayatus Shibyan',
            'dev_description'   => $request->dev_description,
            'dev_details'       => $devDetails,
            'tech_stacks'       => $techStacks,
            'copyright_year'    => $request->copyright_year ?? date('Y'),
            'copyright_owner'   => $request->copyright_owner ?? 'MDT Hidayatus Shibyan',
            'copyright_subtitle' => $request->copyright_subtitle ?? 'All Rights Reserved • SIAKAD MDTHS Web',
        ];

        if ($id) {
            $version = AppVersion::where('app_type', 'web')->find($id);
            if ($version) {
                $version->update($payload);
            } else {
                $version = AppVersion::create($payload);
            }
        } else {
            $version = AppVersion::create($payload);
        }

        return redirect()->route('pengaturan-versi.index')
            ->with('success', 'Versi Web ' . $version->version . ' & Catatan Rilis Pembaruan berhasil disimpan!');
    }

    /**
     * Set versi tertentu sebagai versi aktif / utama
     */
    public function setActive($id)
    {
        $version = AppVersion::where('app_type', 'web')->findOrFail($id);

        AppVersion::where('app_type', 'web')
            ->update(['is_latest' => false]);

        $version->update([
            'is_active' => true,
            'is_latest' => true,
        ]);

        return redirect()->back()
            ->with('success', "Versi Web {$version->version} berhasil diatur sebagai versi utama/terbaru!");
    }

    /**
     * Hapus riwayat versi aplikasi
     */
    public function destroy($id)
    {
        $version = AppVersion::where('app_type', 'web')->findOrFail($id);
        $vName = $version->version;

        $version->delete();

        // Jika versi yang dihapus adalah latest, angkat versi berikutnya
        $remaining = AppVersion::where('app_type', 'web')->latest('id')->first();
        if ($remaining && !AppVersion::where('app_type', 'web')->where('is_latest', true)->exists()) {
            $remaining->update(['is_latest' => true, 'is_active' => true]);
        }

        return redirect()->route('pengaturan-versi.index')
            ->with('success', "Riwayat rilis versi Web {$vName} berhasil dihapus.");
    }
}
