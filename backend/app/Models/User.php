<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'username', 'email', 'password', 'is_active', 'is_login', 'is_logout', 'tingkat_id', 'last_seen_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_seen_at'      => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function administrator()
    {
        return $this->hasOne(Administrator::class);
    }
    public function tingkat()
    {
        return $this->belongsTo(Tingkat::class);
    }

    public function ustadz()
    {
        return $this->hasOne(Ustadz::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function isOnline()
    {
        if (!$this->last_seen_at) {
            return false;
        }
        return \Carbon\Carbon::parse($this->last_seen_at)->diffInMinutes(now()) <= 3;
    }

    // Tampilkan teks kapan terakhir dilihat (Misal: "5 menit yang lalu")
    public function lastSeenText()
    {
        if ($this->isOnline()) {
            return 'Online';
        }
        return $this->last_seen_at ? \Carbon\Carbon::parse($this->last_seen_at)->diffForHumans() : 'Belum pernah login';
    }
}
