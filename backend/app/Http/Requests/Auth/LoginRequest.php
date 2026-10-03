<?php

namespace App\Http\Requests\Auth;

// use App\Models\Ruangan;
// use App\Models\TahunPelajaran;
// use App\Models\Ustadz;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // 1. Ambil inputan dari user
        $loginInput = $this->input('login');

        // 2. DETEKSI OTOMATIS: Jika formatnya valid email, maka cari di kolom 'email'. 
        // Jika tidak, cari di kolom 'username'
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $fieldType => $loginInput,
            'password' => $this->input('password'),
            'is_active' => 1 // Pastikan akun aktif
        ];

        // 3. Proses pengecekan ke database
        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            \App\Services\ActivityLogService::recordFailedLogin(
                $this,
                'web',
                $loginInput,
                'Kredensial yang diberikan salah atau akun sedang nonaktif'
            );

            throw ValidationException::withMessages([
                'login' => trans('Kredensial yang diberikan salah atau akun Anda tidak aktif.'),
            ]);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasAnyRole(['administrator', 'staff', 'petugas-tabungan', 'petugas-koperasi'])) {
            Auth::logout();
            RateLimiter::hit($this->throttleKey());

            \App\Services\ActivityLogService::recordFailedLogin(
                $this,
                'web',
                $loginInput,
                'Akses Web ditolak! Akun khusus Aplikasi Mobile.',
                $user->id
            );

            throw ValidationException::withMessages([
                'login' => 'Akses ditolak! Akun Anda hanya dapat digunakan melalui Aplikasi Mobile MDT.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login')) . '|' . $this->ip());
    }

    /**
     * Tentukan route tujuan redirect setelah login berhasil berdasarkan role
     */
    public function redirectRoute(): string
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && $user->hasRole('petugas-tabungan') && !$user->hasRole('administrator')) {
            return route('tabungan.dashboard', absolute: false);
        }

        if ($user && $user->hasRole('petugas-koperasi') && !$user->hasRole('administrator')) {
            return route('koperasi.dashboard', absolute: false);
        }

        return route('dashboard', absolute: false);
    }
}
