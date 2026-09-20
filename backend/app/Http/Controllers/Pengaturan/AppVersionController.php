<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\Request;

class AppVersionController extends Controller
{
    /**
     * Tampilkan halaman riwayat rilis / changelog seluruh versi aplikasi
     */
    public function riwayat(Request $request)
    {
        $appType = $request->query('app', 'all');
        $search = $request->query('q');

        $query = AppVersion::query();

        if ($appType && $appType !== 'all') {
            $query->where('app_type', $appType);
        }

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

        $latestVersion = AppVersion::where('is_active', true)->latest('id')->first();
        $totalVersions = AppVersion::count();
        $ustadzCount = AppVersion::where('app_type', 'ustadz')->count();
        $muridCount = AppVersion::where('app_type', 'murid')->count();
        $webCount = AppVersion::where('app_type', 'web')->count();

        return view('pengaturan-versi.riwayat', compact(
            'versions',
            'appType',
            'search',
            'latestVersion',
            'totalVersions',
            'ustadzCount',
            'muridCount',
            'webCount'
        ));
    }

    /**
     * Tampilkan halaman formulir pengaturan versi & catatan rilis aplikasi
     */
    public function index(Request $request)
    {
        $appType = $request->query('app', 'ustadz');
        $editId = $request->query('id');

        if ($editId) {
            $version = AppVersion::findOrFail($editId);
            $appType = $version->app_type;
        } else {
            $version = AppVersion::getActiveVersion($appType);
            if (!$version) {
                $defaultData = match ($appType) {
                    'murid' => AppVersion::defaultMuridData(),
                    'web'   => AppVersion::defaultWebData(),
                    default => AppVersion::defaultUstadzData(),
                };
                $version = AppVersion::create($defaultData);
            }
        }

        $allVersions = AppVersion::where('app_type', $appType)
            ->orWhere('app_type', 'all')
            ->latest('id')
            ->get();

        return view('pengaturan-versi.index', compact('version', 'allVersions', 'appType'));
    }

    /**
     * Form tambah versi rilis baru
     */
    public function create(Request $request)
    {
        $appType = $request->query('app', 'ustadz');

        $template = AppVersion::where('app_type', $appType)->latest('id')->first();
        $version = new AppVersion([
            'app_type'          => $appType,
            'app_title'         => $appType === 'ustadz' ? 'Ustadz - MDTHS' : ($appType === 'murid' ? 'Santri - MDTHS' : 'SIAKAD Web MDTHS'),
            'app_subtitle'      => 'MDT Hidayatus Shibyan',
            'version'           => '1.0.1',
            'build_number'      => date('Y.m'),
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
            'dev_description'   => $template?->dev_description ?? 'Aplikasi ini dirancang dan dikembangkan untuk mendukung digitalisasi tata kelola madrasah, presensi KBM, evaluasi catatan murid, serta transparansi pelaporan terpadu.',
            'tech_stacks'       => $template?->tech_stacks ?? ['Flutter', 'Dart', 'Laravel', 'REST API', 'MySQL'],
            'copyright_year'    => date('Y'),
            'copyright_owner'   => 'MDT Hidayatus Shibyan',
            'copyright_subtitle' => 'All Rights Reserved • SIAKAD MDTHS',
        ]);

        $allVersions = AppVersion::where('app_type', $appType)->latest('id')->get();
        $isNew = true;

        return view('pengaturan-versi.index', compact('version', 'allVersions', 'appType', 'isNew'));
    }

    /**
     * Simpan / Perbarui versi dan changelog aplikasi
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

        $appType = $request->input('app_type', 'ustadz');
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
            $techStacks = ['Flutter', 'Dart', 'Laravel', 'REST API', 'MySQL', 'Provider'];
        }

        $devDetails = [
            [
                'icon'  => 'developer_mode',
                'label' => 'Framework & Bahasa',
                'value' => 'Flutter (Dart)',
            ],
            [
                'icon'  => 'dns',
                'label' => 'Backend & Server',
                'value' => 'Laravel REST API & MySQL',
            ],
            [
                'icon'  => 'security',
                'label' => 'Keamanan Autentikasi',
                'value' => 'Laravel Sanctum Token',
            ],
            [
                'icon'  => 'domain',
                'label' => 'Lembaga',
                'value' => $request->dev_institution ?? 'MDT Hidayatus Shibyan',
            ],
        ];

        // Jika versi ini ditandai sebagai latest, matikan is_latest pada versi lain dengan app_type sama
        if ($isLatest) {
            AppVersion::where('app_type', $appType)
                ->when($id, fn($q) => $q->where('id', '!=', $id))
                ->update(['is_latest' => false]);
        }

        $payload = [
            'app_type'          => $appType,
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
            'copyright_subtitle' => $request->copyright_subtitle ?? 'All Rights Reserved • SIAKAD MDTHS',
        ];

        if ($id) {
            $version = AppVersion::find($id);
            if ($version) {
                $version->update($payload);
            } else {
                $version = AppVersion::create($payload);
            }
        } else {
            $version = AppVersion::create($payload);
        }

        return redirect()->route('pengaturan-versi.riwayat', ['app' => $appType])
            ->with('success', 'Versi ' . $version->version . ' & Catatan Rilis Pembaruan berhasil disimpan!');
    }

    /**
     * Set versi tertentu sebagai versi aktif / utama
     */
    public function setActive($id)
    {
        $version = AppVersion::findOrFail($id);

        AppVersion::where('app_type', $version->app_type)
            ->update(['is_latest' => false]);

        $version->update([
            'is_active' => true,
            'is_latest' => true,
        ]);

        return redirect()->back()
            ->with('success', "Versi {$version->version} ({$version->app_type}) berhasil diatur sebagai versi utama/terbaru!");
    }

    /**
     * Hapus riwayat versi aplikasi
     */
    public function destroy($id)
    {
        $version = AppVersion::findOrFail($id);
        $appType = $version->app_type;
        $vName = $version->version;

        $version->delete();

        // Jika versi yang dihapus adalah latest, angkat versi berikutnya
        $remaining = AppVersion::where('app_type', $appType)->latest('id')->first();
        if ($remaining && !AppVersion::where('app_type', $appType)->where('is_latest', true)->exists()) {
            $remaining->update(['is_latest' => true, 'is_active' => true]);
        }

        return redirect()->route('pengaturan-versi.riwayat', ['app' => $appType])
            ->with('success', "Riwayat rilis versi {$vName} berhasil dihapus.");
    }
}
