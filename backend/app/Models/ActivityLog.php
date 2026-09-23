<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'event',
        'platform',
        'ip_address',
        'user_agent',
        'device',
        'browser',
        'os',
        'status',
        'description',
        'properties',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke User pemilik sesi/aktivitas
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope Filter Platform (web, app_ustadz, app_murid)
     */
    public function scopePlatform($query, $platform)
    {
        if ($platform && $platform !== 'all') {
            return $query->where('platform', $platform);
        }
        return $query;
    }

    /**
     * Scope Filter Event (login, logout, failed_login)
     */
    public function scopeEvent($query, $event)
    {
        if ($event && $event !== 'all') {
            return $query->where('event', $event);
        }
        return $query;
    }

    /**
     * Scope Filter Status (success, failed, blocked)
     */
    public function scopeStatus($query, $status)
    {
        if ($status && $status !== 'all') {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Scope Pencarian Teks
     */
    public function scopeSearch($query, $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('ip_address', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('device', 'like', "%{$search}%")
                ->orWhere('browser', 'like', "%{$search}%")
                ->orWhere('os', 'like', "%{$search}%")
                ->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
        });
    }

    /**
     * Label Nama Platform yang Bersih
     */
    public function getPlatformLabelAttribute(): string
    {
        return match ($this->platform) {
            'app_ustadz' => 'App Ustadz (Mobile)',
            'app_murid'  => 'App Wali Murid',
            'web'        => 'Web Admin',
            'system'     => 'Sistem Server',
            default      => ucfirst(str_replace('_', ' ', $this->platform ?? 'Unknown')),
        };
    }

    /**
     * Ikon Perangkat Bootstrap Icons
     */
    public function getDeviceIconAttribute(): string
    {
        $os = strtolower($this->os ?? '');
        $device = strtolower($this->device ?? '');

        if (str_contains($os, 'android') || str_contains($device, 'android')) {
            return 'bi-android2 text-emerald-500';
        }
        if (str_contains($os, 'ios') || str_contains($device, 'iphone') || str_contains($device, 'ipad') || str_contains($os, 'mac')) {
            return 'bi-apple text-zinc-800 dark:text-zinc-200';
        }
        if (str_contains($os, 'windows')) {
            return 'bi-windows text-blue-500';
        }
        if (str_contains($os, 'linux')) {
            return 'bi-ubuntu text-amber-500';
        }
        if ($this->platform === 'app_ustadz' || $this->platform === 'app_murid') {
            return 'bi-phone-fill text-emerald-600 dark:text-emerald-400';
        }

        return 'bi-laptop text-zinc-500';
    }

    /**
     * Ikon & Warna Event
     */
    public function getEventBadgeAttribute(): array
    {
        if ($this->status === 'failed' || $this->event === 'failed_login') {
            return [
                'label' => 'Login Gagal',
                'icon'  => 'bi-shield-x',
                'class' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
            ];
        }

        return match ($this->event) {
            'login' => [
                'label' => 'Login Berhasil',
                'icon'  => 'bi-box-arrow-in-right',
                'class' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
            ],
            'logout' => [
                'label' => 'Logout',
                'icon'  => 'bi-box-arrow-right',
                'class' => 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border-zinc-500/20',
            ],
            'password_change' => [
                'label' => 'Ubah Password',
                'icon'  => 'bi-key-fill',
                'class' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
            ],
            default => [
                'label' => ucfirst(str_replace('_', ' ', $this->event)),
                'icon'  => 'bi-activity',
                'class' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
            ],
        };
    }
}
