<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\Request;

class AppVersionApiController extends Controller
{
    /**
     * Dapatkan informasi versi & catatan rilis aplikasi aktif
     */
    public function getAppVersion(Request $request)
    {
        $appType = $request->query('app', 'ustadz');

        $version = AppVersion::getActiveVersion($appType);

        if (!$version) {
            // Fallback ke data default jika database kosong
            $data = AppVersion::defaultUstadzData();
        } else {
            $data = [
                'id'                => $version->id,
                'app_type'          => $version->app_type,
                'app_title'         => $version->app_title,
                'app_subtitle'      => $version->app_subtitle,
                'version'           => $version->version,
                'build_number'      => $version->build_number,
                'release_date'      => $version->release_date,
                'release_subtitle'  => $version->release_subtitle,
                'release_badge'     => $version->release_badge,
                'status_badge'      => $version->status_badge,
                'is_latest'         => (bool) $version->is_latest,
                'is_active'         => (bool) $version->is_active,
                'new_features'      => $version->new_features ?? [],
                'improvements'      => $version->improvements ?? [],
                'dev_name'          => $version->dev_name,
                'dev_role'          => $version->dev_role,
                'dev_institution'   => $version->dev_institution,
                'dev_description'   => $version->dev_description,
                'dev_details'       => $version->dev_details ?? [],
                'tech_stacks'       => $version->tech_stacks ?? [],
                'copyright_year'    => $version->copyright_year,
                'copyright_owner'   => $version->copyright_owner,
                'copyright_subtitle' => $version->copyright_subtitle,
                'updated_at'        => $version->updated_at?->toIso8601String(),
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
        ], 200);
    }
}
