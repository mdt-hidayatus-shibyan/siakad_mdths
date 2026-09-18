<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\Request;

class AppVersionController extends Controller
{
    /**
     * Tampilkan halaman formulir pengaturan versi & catatan rilis aplikasi
     */
    public function index(Request $request)
    {
        $appType = $request->query('app', 'ustadz');

        $version = AppVersion::getActiveVersion($appType);

        if (!$version) {
            $version = AppVersion::create(AppVersion::defaultUstadzData());
        }

        $allVersions = AppVersion::where('app_type', $appType)
            ->orWhere('app_type', 'all')
            ->latest('id')
            ->get();

        return view('pengaturan-versi.index', compact('version', 'allVersions', 'appType'));
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

        $payload = [
            'app_type'          => $appType,
            'app_title'         => $request->app_title,
            'app_subtitle'      => $request->app_subtitle,
            'version'           => $request->version,
            'build_number'      => $request->build_number,
            'release_date'      => $request->release_date,
            'release_subtitle'  => $request->release_subtitle ?? 'Rilis Perdana',
            'release_badge'     => $request->release_badge ?? 'Rilis Saat Ini',
            'status_badge'      => $request->status_badge ?? 'Versi Terbaru',
            'is_latest'         => $request->has('is_latest') ? true : false,
            'is_active'         => true,
            'new_features'      => $newFeatures,
            'improvements'      => $improvements,
            'dev_name'          => $request->dev_name,
            'dev_role'          => $request->dev_role ?? 'Lead Developer & Tim IT',
            'dev_institution'   => $request->dev_institution ?? 'MDT Hidayatus Shibyan',
            'dev_description'   => $request->dev_description,
            'dev_details'       => $devDetails,
            'tech_stacks'       => $techStacks,
            'copyright_year'    => $request->copyright_year ?? '2026',
            'copyright_owner'   => $request->copyright_owner ?? 'MDT Hidayatus Shibyan',
            'copyright_subtitle' => $request->copyright_subtitle ?? 'All Rights Reserved • SIAKAD MDTHS Mobile',
        ];

        if ($id) {
            $version = AppVersion::find($id);
            if ($version) {
                $version->update($payload);
            } else {
                $version = AppVersion::create($payload);
            }
        } else {
            $version = AppVersion::updateOrCreate(
                ['app_type' => $appType, 'version' => $request->version],
                $payload
            );
        }

        return redirect()->route('pengaturan-versi.index', ['app' => $appType])
            ->with('success', 'Versi ' . $version->version . ' & Catatan Pembaruan Aplikasi berhasil disimpan!');
    }
}
