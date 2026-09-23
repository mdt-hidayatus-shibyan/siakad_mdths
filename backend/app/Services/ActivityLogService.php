<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ActivityLogService
{
    /**
     * Catat log aktivitas umum secara aman (failsafe).
     */
    public static function record(array $data): ?ActivityLog
    {
        try {
            return ActivityLog::create($data);
        } catch (\Throwable $e) {
            Log::warning('Gagal mencatat ActivityLog: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data'  => $data,
            ]);
            return null;
        }
    }

    /**
     * Catat peristiwa LOGIN berhasil.
     */
    public static function recordLogin(
        Request $request,
        User $user,
        string $platform = 'web',
        ?string $description = null,
        array $extra = []
    ): ?ActivityLog {
        $parsed = self::parseUserAgent($request->userAgent(), $request);

        $defaultDesc = match ($platform) {
            'app_ustadz' => "Login berhasil ke Aplikasi Mobile Ustadz ({$user->name})",
            'app_murid'  => "Login berhasil ke Aplikasi Wali Murid ({$user->name})",
            default      => "Login berhasil ke Web Admin ({$user->name})",
        };

        $properties = array_merge([
            'login_id' => $user->username ?? $user->email,
            'role'     => $user->roles->first()->name ?? 'user',
            'tingkat'  => $user->tingkat->nama_tingkat ?? null,
        ], $extra);

        return self::record([
            'user_id'     => $user->id,
            'event'       => 'login',
            'platform'    => $platform,
            'ip_address'  => self::getClientIp($request),
            'user_agent'  => $request->userAgent(),
            'device'      => $parsed['device'],
            'browser'     => $parsed['browser'],
            'os'          => $parsed['os'],
            'status'      => 'success',
            'description' => $description ?? $defaultDesc,
            'properties'  => $properties,
        ]);
    }

    /**
     * Catat peristiwa LOGOUT.
     */
    public static function recordLogout(
        Request $request,
        User $user,
        string $platform = 'web',
        ?string $description = null
    ): ?ActivityLog {
        $parsed = self::parseUserAgent($request->userAgent(), $request);

        $defaultDesc = match ($platform) {
            'app_ustadz' => "Logout dari Aplikasi Mobile Ustadz ({$user->name})",
            'app_murid'  => "Logout dari Aplikasi Wali Murid ({$user->name})",
            default      => "Logout dari Web Admin ({$user->name})",
        };

        return self::record([
            'user_id'     => $user->id,
            'event'       => 'logout',
            'platform'    => $platform,
            'ip_address'  => self::getClientIp($request),
            'user_agent'  => $request->userAgent(),
            'device'      => $parsed['device'],
            'browser'     => $parsed['browser'],
            'os'          => $parsed['os'],
            'status'      => 'success',
            'description' => $description ?? $defaultDesc,
            'properties'  => [
                'login_id' => $user->username ?? $user->email,
                'role'     => $user->roles->first()->name ?? 'user',
            ],
        ]);
    }

    /**
     * Catat peristiwa LOGIN GAGAL / DITOLAK.
     */
    public static function recordFailedLogin(
        Request $request,
        string $platform = 'web',
        ?string $loginId = null,
        string $reason = 'Kredensial salah',
        ?int $userId = null
    ): ?ActivityLog {
        $parsed = self::parseUserAgent($request->userAgent(), $request);

        return self::record([
            'user_id'     => $userId,
            'event'       => 'failed_login',
            'platform'    => $platform,
            'ip_address'  => self::getClientIp($request),
            'user_agent'  => $request->userAgent(),
            'device'      => $parsed['device'],
            'browser'     => $parsed['browser'],
            'os'          => $parsed['os'],
            'status'      => 'failed',
            'description' => "Percobaan login gagal [{$platform}]: {$reason}",
            'properties'  => [
                'attempted_login_id' => $loginId,
                'reason'             => $reason,
            ],
        ]);
    }

    /**
     * Dapatkan IP Client Asli (Mendukung Proxy/Cloudflare/Nginx Load Balancer)
     */
    public static function getClientIp(Request $request): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
        ];

        foreach ($headers as $header) {
            $ip = $request->server($header);
            if (!empty($ip)) {
                $parts = explode(',', $ip);
                $cleanIp = trim($parts[0]);
                if (filter_var($cleanIp, FILTER_VALIDATE_IP)) {
                    return $cleanIp;
                }
            }
        }

        return $request->ip() ?: '127.0.0.1';
    }

    /**
     * Parsing User-Agent untuk deteksi OS, Browser, dan Perangkat.
     */
    public static function parseUserAgent(?string $userAgent, Request $request = null): array
    {
        $ua = $userAgent ?? '';
        $os = 'Unknown OS';
        $browser = 'Unknown Browser';
        $device = 'PC / Desktop';

        // 1. Cek Header Khusus Mobile Client jika tersedia
        if ($request) {
            $customDevice = $request->header('X-Device-Model') ?? $request->header('X-Device-Name');
            $customOs = $request->header('X-Device-OS');
            if ($customDevice) {
                $device = $customDevice;
            }
            if ($customOs) {
                $os = $customOs;
            }
        }

        // 2. Deteksi Operating System
        if (preg_match('/android/i', $ua)) {
            $os = 'Android';
            $device = 'Perangkat Android';
            if (preg_match('/android\s+([0-9\.]+)/i', $ua, $matches)) {
                $os = 'Android ' . $matches[1];
            }
            if (preg_match('/;\s*([A-Za-z0-9\s\-_]+)\s+Build/i', $ua, $matches)) {
                $device = trim($matches[1]);
            }
        } elseif (preg_match('/iphone/i', $ua)) {
            $os = 'iOS (iPhone)';
            $device = 'Apple iPhone';
        } elseif (preg_match('/ipad/i', $ua)) {
            $os = 'iOS (iPad)';
            $device = 'Apple iPad';
        } elseif (preg_match('/windows nt 10\.0/i', $ua)) {
            $os = 'Windows 10/11';
            $device = 'PC / Laptop (Windows)';
        } elseif (preg_match('/windows nt 6\.3/i', $ua)) {
            $os = 'Windows 8.1';
            $device = 'PC / Laptop (Windows)';
        } elseif (preg_match('/windows nt 6\.1/i', $ua)) {
            $os = 'Windows 7';
            $device = 'PC / Laptop (Windows)';
        } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
            $os = 'macOS';
            $device = 'Apple Mac';
        } elseif (preg_match('/linux/i', $ua)) {
            $os = 'Linux';
            $device = 'PC / Linux';
        }

        // 3. Deteksi Browser / App Client
        if (preg_match('/dart|flutter/i', $ua)) {
            $browser = 'Flutter Mobile App';
            if ($device === 'PC / Desktop') {
                $device = 'Mobile Device (Flutter)';
            }
        } elseif (preg_match('/edg/i', $ua)) {
            $browser = 'Microsoft Edge';
        } elseif (preg_match('/chrome|crios/i', $ua) && !preg_match('/opr|opera/i', $ua)) {
            $browser = 'Google Chrome';
        } elseif (preg_match('/firefox|fxios/i', $ua)) {
            $browser = 'Mozilla Firefox';
        } elseif (preg_match('/safari/i', $ua) && !preg_match('/chrome|crios/i', $ua)) {
            $browser = 'Apple Safari';
        } elseif (preg_match('/opr|opera/i', $ua)) {
            $browser = 'Opera';
        } elseif (preg_match('/postman/i', $ua)) {
            $browser = 'Postman API Client';
        }

        return [
            'os'      => $os,
            'browser' => $browser,
            'device'  => $device,
        ];
    }
}
