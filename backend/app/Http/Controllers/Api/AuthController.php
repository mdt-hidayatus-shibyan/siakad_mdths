<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SendOtpResetPasswordMail;
use App\Models\Ruangan;
use App\Models\TahunPelajaran;
use App\Models\User;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validasi Input Dasar
        $validator = Validator::make($request->all(), [
            'login_id' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Input tidak lengkap.',
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Deteksi Cerdas: Apakah ini Email atau Username?
        $login_type = filter_var($request->login_id, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $login_type => $request->login_id,
            'password' => $request->password
        ];

        // 3. Coba Autentikasi
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $user->load('ustadz');

            // ====================================================
            // GERBANG SERVER: Tolak Administrator dan Staff
            // ====================================================
            if ($user->hasAnyRole(['administrator', 'staff'])) {
                if ($user->currentAccessToken()) {
                    $user->currentAccessToken()->delete();
                }

                // Catat log penolakan akses
                ActivityLogService::recordFailedLogin(
                    $request,
                    'app_ustadz',
                    $request->login_id,
                    'Akses Ditolak! Role Administrator/Staff dilarang login di App Ustadz.',
                    $user->id
                );

                return response()->json([
                    'success' => false,
                    'message' => 'Akses Ditolak! Aplikasi seluler khusus untuk Ustadz.'
                ], 403);
            }

            // ====================================================
            // LOGIKA TAHUN PELAJARAN & WALI RUANGAN
            // ====================================================
            $tahunAktif = TahunPelajaran::where('is_active', true)->first();

            $isWaliRuangan = false;
            $namaRuangan = null;
            $ruanganId = null;

            if ($tahunAktif && $user->ustadz) {
                $ruangan = Ruangan::where('tahun_pelajaran_id', $tahunAktif->id)
                    ->where('ustadz_id', $user->ustadz->id)
                    ->first();

                if ($ruangan) {
                    $isWaliRuangan = true;
                    $namaRuangan = $ruangan->nama_ruangan;
                    $ruanganId = $ruangan->id;
                }
            }

            $roleName = $user->roles->first()->name ?? 'ustadz';
            $token = $user->createToken('MobileAppToken')->plainTextToken;

            // Update status online & last_seen_at
            $user->update([
                'last_seen_at' => Carbon::now(),
                'is_login'     => true,
                'is_logout'    => false,
            ]);

            // Catat riwayat login Ustadz ke ActivityLog
            ActivityLogService::recordLogin($request, $user, 'app_ustadz', "Login berhasil ke Aplikasi Mobile Ustadz ({$user->name})", [
                'ruangan_wali'    => $namaRuangan,
                'ruangan_wali_id' => $ruanganId,
                'kode_ustadz'     => $user->ustadz->kode_ustadz ?? null,
                'nigm'            => $user->ustadz->nigm ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login Berhasil',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'ustadz_id' => $user->ustadz->id ?? null,
                    'name' => $user->ustadz->nama_lengkap ?? $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'role' => $roleName,
                    'photo' => $user->ustadz && $user->ustadz->foto ? asset('storage/' . $user->ustadz->foto) : null,
                    'kode_ustadz' => $user->ustadz->kode_ustadz ?? null,
                    'nigm' => $user->ustadz->nigm ?? null,
                    'nik' => $user->ustadz->nik ?? null,
                    'jenis_kelamin' => $user->ustadz->jenis_kelamin ?? 'L',
                    'tempat_lahir' => $user->ustadz->tempat_lahir ?? null,
                    'tanggal_lahir' => $user->ustadz && $user->ustadz->tanggal_lahir ? Carbon::parse($user->ustadz->tanggal_lahir)->format('Y-m-d') : null,
                    'alamat' => $user->ustadz->alamat ?? null,
                    'no_hp' => $user->ustadz->no_hp ?? null,
                    'tahun_mulai_mengajar' => $user->ustadz->tahun_mulai_mengajar ?? null,
                    'tanda_tangan' => $user->ustadz && $user->ustadz->tanda_tangan ? asset('storage/' . $user->ustadz->tanda_tangan) : null,

                    // --- DATA TAHUN & RUANGAN ---
                    'tahun_pelajaran' => $tahunAktif ? $tahunAktif->nama_hijriyah . ' H | ' . $tahunAktif->nama_masehi . ' M' : 'Belum Diatur',
                    'is_wali_ruangan' => $isWaliRuangan,
                    'ruangan_wali' => $namaRuangan,
                    'ruangan_wali_id' => $ruanganId,
                ]
            ], 200);
        }

        // Catat percobaan login gagal
        ActivityLogService::recordFailedLogin(
            $request,
            'app_ustadz',
            $request->login_id,
            'Username/Email atau Kata Sandi salah.'
        );

        return response()->json([
            'success' => false,
            'message' => 'Username/Email atau Kata Sandi salah.'
        ], 401);
    }

    /**
     * Login Khusus Aplikasi Murid / Wali Murid (app_murid)
     * Autentikasi via No. Registrasi / No. KK / NISM Murid
     */
    public function loginWali(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Input No. Registrasi atau NISM salah satu anak wajib diisi.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $idInput = trim($request->identifier);
        $idHash = hash_sensitive($idInput);

        // 1. Cari berdasarkan No. Registrasi Wali Murid
        $wali = \App\Models\WaliMurid::with(['kampung', 'murids.ruangans'])
            ->where(function ($q) use ($idInput, $idHash) {
                $q->where('no_registrasi', $idInput)
                    ->orWhere('no_kk_hash', $idHash);
            })
            ->where('is_active', true)
            ->first();

        // 2. Jika tidak ketemu, cari berdasarkan NISM / NISN Murid (salah satu anak)
        if (!$wali) {
            $murid = \App\Models\Murid::where('nism', $idInput)
                ->orWhere('nisn', $idInput)
                ->orWhere('nik_hash', $idHash)
                ->first();

            if ($murid && $murid->wali_murid_id) {
                $wali = \App\Models\WaliMurid::with(['kampung', 'murids.ruangans'])
                    ->where('id', $murid->wali_murid_id)
                    ->where('is_active', true)
                    ->first();
            }
        }

        if (!$wali) {
            ActivityLogService::recordFailedLogin(
                $request,
                'app_murid',
                $idInput,
                'Data Wali/Murid tidak ditemukan atau status sedang nonaktif.'
            );

            return response()->json([
                'success' => false,
                'message' => 'Data Wali/Murid tidak ditemukan atau status sedang nonaktif. Pastikan No. Registrasi atau NISM anak sudah benar.'
            ], 404);
        }

        // Cek jika request hanya untuk validasi identitas (Step 1)
        if ($request->boolean('check_only')) {
            return response()->json([
                'success'    => true,
                'step'       => 'pin_required',
                'wali_name'  => $wali->nama_kepala_keluarga,
                'no_reg'     => $wali->no_registrasi,
                'total_anak' => $wali->murids->where('status', 'Aktif')->count(),
                'kampung'    => $wali->kampung->nama_kampung ?? '-',
            ], 200);
        }

        // Verifikasi PIN Keamanan Ter-Hash (PIN default: 112233)
        $inputPin = trim($request->input('pin', ''));

        if (empty($inputPin)) {
            return response()->json([
                'success' => false,
                'message' => 'PIN keamanan wajib diisi.',
                'step'    => 'pin_required'
            ], 422);
        }

        $isPinValid = false;
        $isPinChanged = (bool) $wali->is_pin_changed;

        if (!$isPinChanged) {
            // Pengguna belum pernah mengubah PIN -> izinkan PIN default 112233
            if ($inputPin === '112233') {
                $isPinValid = true;
                if (empty($wali->pin) || (!str_starts_with($wali->pin, '$2y$') && !str_starts_with($wali->pin, '$2a$') && !str_starts_with($wali->pin, '$2b$'))) {
                    $wali->pin = Hash::make('112233');
                    $wali->save();
                }
            } elseif (!empty($wali->pin)) {
                // Atau cek jika pin sudah terisi hash lain
                if (str_starts_with($wali->pin, '$2y$') || str_starts_with($wali->pin, '$2a$') || str_starts_with($wali->pin, '$2b$')) {
                    $isPinValid = Hash::check($inputPin, $wali->pin);
                } else {
                    $isPinValid = ($wali->pin === $inputPin);
                }
            }
        } else {
            // Pengguna SUDAH mengubah PIN -> WAJIB cocok dengan PIN kustom yang tersimpan
            if (!empty($wali->pin)) {
                if (str_starts_with($wali->pin, '$2y$') || str_starts_with($wali->pin, '$2a$') || str_starts_with($wali->pin, '$2b$')) {
                    try {
                        $isPinValid = Hash::check($inputPin, $wali->pin);
                    } catch (\Throwable $e) {
                        $isPinValid = false;
                    }
                } else {
                    if ($wali->pin === $inputPin) {
                        $wali->pin = Hash::make($inputPin);
                        $wali->save();
                        $isPinValid = true;
                    }
                }
            }
        }

        if (!$isPinValid) {
            $errorMessage = $isPinChanged
                ? 'PIN keamanan yang Anda masukkan salah.'
                : 'PIN keamanan yang Anda masukkan salah. (PIN default: 112233)';

            ActivityLogService::recordFailedLogin(
                $request,
                'app_murid',
                $idInput,
                $errorMessage
            );

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'step'    => 'pin_required'
            ], 401);
        }

        // Akun virtual untuk session token wali murid
        $user = \App\Models\User::firstOrCreate(
            ['username' => 'wali_' . $wali->no_registrasi],
            [
                'name'      => $wali->nama_kepala_keluarga,
                'email'     => 'wali_' . $wali->no_registrasi . '@mdthidayatusshibyan.sch.id',
                'password'  => Hash::make('wali_' . $wali->no_registrasi),
                'is_active' => true,
            ]
        );

        // Pastikan role 'wali-murid' telah ditetapkan ke akun user
        if (!$user->hasRole('wali-murid')) {
            $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'wali-murid', 'guard_name' => 'web']);
            $user->assignRole($role);
        }

        // Sinkronisasi nama jika kepala keluarga telah diubah di master data
        if ($user->name !== $wali->nama_kepala_keluarga) {
            $user->update(['name' => $wali->nama_kepala_keluarga]);
        }

        $token = $user->createToken('WaliAppToken')->plainTextToken;

        // Update status online & last_seen_at
        $user->update([
            'last_seen_at' => Carbon::now(),
            'is_login'     => true,
            'is_logout'    => false,
        ]);

        // Catat riwayat login Wali Murid ke ActivityLog
        ActivityLogService::recordLogin($request, $user, 'app_murid', "Login berhasil ke Aplikasi Wali Murid ({$wali->nama_kepala_keluarga})", [
            'wali_id'       => $wali->id,
            'no_registrasi' => $wali->no_registrasi,
            'no_kk'         => $wali->no_kk,
            'total_anak'    => $wali->murids->where('status', 'Aktif')->count(),
        ]);

        return response()->json([
            'success'        => true,
            'message'        => 'Login Berhasil',
            'token'          => $token,
            'role'           => $user->roles->first()->name ?? 'wali-murid',
            'is_first_login' => !(bool) $wali->is_pin_changed,
            'wali'           => [
                'id'                   => $wali->id,
                'no_registrasi'        => $wali->no_registrasi,
                'no_kk'                => $wali->no_kk,
                'nama_kepala_keluarga' => $wali->nama_kepala_keluarga,
                'kepala_keluarga'      => $wali->kepala_keluarga,
                'no_hp'                => $wali->no_hp,
                'alamat'               => $wali->alamat_detail,
                'kampung'              => $wali->kampung->nama_kampung ?? '-',
                'total_anak'           => $wali->murids->where('status', 'Aktif')->count(),
                'is_first_login'       => !(bool) $wali->is_pin_changed,
                'is_pin_changed'       => (bool) $wali->is_pin_changed,
            ]
        ], 200);
    }

    /**
     * Login Cepat Wali Murid via Pemindaian QR Code (Kartu Pelajar / Kartu Santri)
     */
    public function loginQr(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_data' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data QR Code tidak valid atau kosong.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $rawInput = trim($request->input('qr_data'));
        $wali = null;
        $murid = null;

        // 1. Coba decode jika QR berupa JSON payload
        $jsonData = json_decode($rawInput, true);
        if (is_array($jsonData)) {
            // Cek identifier dari JSON
            $noReg = $jsonData['no_registrasi'] ?? $jsonData['no_reg'] ?? null;
            $waliId = $jsonData['wali_id'] ?? null;
            $nism = $jsonData['nism'] ?? null;

            if ($noReg || $waliId) {
                $wali = \App\Models\WaliMurid::with(['kampung', 'murids.ruangans'])
                    ->where('is_active', true)
                    ->where(function ($q) use ($noReg, $waliId) {
                        if ($noReg) $q->where('no_registrasi', $noReg);
                        if ($waliId) $q->orWhere('id', $waliId);
                    })
                    ->first();
            }

            if (!$wali && $nism) {
                $murid = \App\Models\Murid::where('nism', $nism)->first();
                if ($murid && $murid->wali_murid_id) {
                    $wali = \App\Models\WaliMurid::with(['kampung', 'murids.ruangans'])
                        ->where('id', $murid->wali_murid_id)
                        ->where('is_active', true)
                        ->first();
                }
            }
        }

        // 2. Jika bukan JSON atau tidak ketemu via JSON, coba cari sebagai raw identifier (NISM, No Reg, No KK)
        if (!$wali) {
            $idHash = hash_sensitive($rawInput);

            // Cari via no_registrasi atau no_kk_hash
            $wali = \App\Models\WaliMurid::with(['kampung', 'murids.ruangans'])
                ->where('is_active', true)
                ->where(function ($q) use ($rawInput, $idHash) {
                    $q->where('no_registrasi', $rawInput)
                        ->orWhere('no_kk_hash', $idHash);
                })
                ->first();

            // Cari via NISM / NISN / NIK Murid
            if (!$wali) {
                $murid = \App\Models\Murid::where('nism', $rawInput)
                    ->orWhere('nisn', $rawInput)
                    ->orWhere('nik_hash', $idHash)
                    ->first();

                if ($murid && $murid->wali_murid_id) {
                    $wali = \App\Models\WaliMurid::with(['kampung', 'murids.ruangans'])
                        ->where('id', $murid->wali_murid_id)
                        ->where('is_active', true)
                        ->first();
                }
            }
        }

        if (!$wali) {
            ActivityLogService::recordFailedLogin(
                $request,
                'app_murid',
                substr($rawInput, 0, 50),
                'QR Code tidak dikenali atau akun Wali Murid sedang nonaktif.'
            );

            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau data Wali Murid tidak ditemukan. Pastikan Anda memindai Kartu Santri / Akses Resmi MDT Hidayatus Shibyan.'
            ], 404);
        }

        // Akun virtual untuk session token wali murid
        $user = \App\Models\User::firstOrCreate(
            ['username' => 'wali_' . $wali->no_registrasi],
            [
                'name'      => $wali->nama_kepala_keluarga,
                'email'     => 'wali_' . $wali->no_registrasi . '@mdthidayatusshibyan.sch.id',
                'password'  => Hash::make('wali_' . $wali->no_registrasi),
                'is_active' => true,
            ]
        );

        // Pastikan role 'wali-murid' telah ditetapkan ke akun user
        if (!$user->hasRole('wali-murid')) {
            $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'wali-murid', 'guard_name' => 'web']);
            $user->assignRole($role);
        }

        // Sinkronisasi nama jika kepala keluarga telah diubah di master data
        if ($user->name !== $wali->nama_kepala_keluarga) {
            $user->update(['name' => $wali->nama_kepala_keluarga]);
        }

        $token = $user->createToken('WaliAppToken')->plainTextToken;

        // Update status online & last_seen_at
        $user->update([
            'last_seen_at' => Carbon::now(),
            'is_login'     => true,
            'is_logout'    => false,
        ]);

        // Catat riwayat login Wali Murid ke ActivityLog
        ActivityLogService::recordLogin($request, $user, 'app_murid', "Login berhasil via QR Code Kartu Santri ke Aplikasi Wali Murid ({$wali->nama_kepala_keluarga})", [
            'wali_id'       => $wali->id,
            'no_registrasi' => $wali->no_registrasi,
            'no_kk'         => $wali->no_kk,
            'login_via'     => 'QR_CODE_SCAN',
            'total_anak'    => $wali->murids->where('status', 'Aktif')->count(),
        ]);

        return response()->json([
            'success'        => true,
            'message'        => 'Login via QR Code Berhasil',
            'token'          => $token,
            'role'           => $user->roles->first()->name ?? 'wali-murid',
            'is_first_login' => !(bool) $wali->is_pin_changed,
            'wali'           => [
                'id'                   => $wali->id,
                'no_registrasi'        => $wali->no_registrasi,
                'no_kk'                => $wali->no_kk,
                'nama_kepala_keluarga' => $wali->nama_kepala_keluarga,
                'kepala_keluarga'      => $wali->kepala_keluarga,
                'no_hp'                => $wali->no_hp,
                'alamat'               => $wali->alamat_detail,
                'kampung'              => $wali->kampung->nama_kampung ?? '-',
                'total_anak'           => $wali->murids->where('status', 'Aktif')->count(),
                'is_first_login'       => !(bool) $wali->is_pin_changed,
                'is_pin_changed'       => (bool) $wali->is_pin_changed,
            ]
        ], 200);
    }

    /**
     * Update PIN Keamanan Wali Murid
     */
    public function updatePinWali(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pin_lama' => 'required|string',
            'pin_baru' => 'required|string|min:6|max:6|regex:/^[0-9]+$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'PIN baru harus terdiri dari 6 digit angka.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        if (!str_starts_with($user->username ?? '', 'wali_')) {
            return response()->json([
                'success' => false,
                'message' => 'Akses hanya untuk akun Wali Murid.'
            ], 403);
        }

        $noReg = substr($user->username, 5);
        $wali = \App\Models\WaliMurid::where('no_registrasi', $noReg)->first();

        if (!$wali) {
            return response()->json([
                'success' => false,
                'message' => 'Data Wali Murid tidak ditemukan.'
            ], 404);
        }

        $currentPin = $wali->pin;
        $isOldPinValid = false;

        if (!empty($currentPin)) {
            if (str_starts_with($currentPin, '$2y$') || str_starts_with($currentPin, '$2a$') || str_starts_with($currentPin, '$2b$')) {
                try {
                    if (Hash::check($request->pin_lama, $currentPin)) {
                        $isOldPinValid = true;
                    }
                } catch (\Throwable $e) {
                    $isOldPinValid = false;
                }
            } else {
                if ($currentPin === $request->pin_lama) {
                    $isOldPinValid = true;
                }
            }
        } else {
            if ($request->pin_lama === '112233') {
                $isOldPinValid = true;
            }
        }

        if (!$isOldPinValid) {
            return response()->json([
                'success' => false,
                'message' => 'PIN lama yang Anda masukkan tidak sesuai.'
            ], 400);
        }

        // Simpan PIN baru dalam bentuk Bcrypt Hash dan tandai is_pin_changed = true
        $wali->update([
            'pin'            => Hash::make($request->pin_baru),
            'is_pin_changed' => true,
        ]);

        // Keamanan: Revoke token sesi di perangkat lain agar wajib login ulang dengan PIN baru
        if ($user && $user->currentAccessToken()) {
            $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'PIN keamanan berhasil diperbarui.'
        ], 200);
    }

    /**
     * 1.2 Lupa Password - Request Kode OTP Pemulihan
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan masukkan username atau email terdaftar.',
                'errors' => $validator->errors()
            ], 422);
        }

        $loginId = $request->login_id;
        $user = User::with('ustadz')
            ->where('email', $loginId)
            ->orWhere('username', $loginId)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Akun dengan email/username tersebut tidak ditemukan.'
            ], 404);
        }

        // Generate 6-Digit OTP Code
        $otp = (string) mt_rand(100000, 999999);

        // Simpan ke tabel password_reset_tokens (hashed)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($otp),
                'created_at' => Carbon::now(),
            ]
        );

        $namaUstadz = $user->ustadz->nama_lengkap ?? $user->name;

        // Kirim Email OTP via SMTP
        try {
            Mail::to($user->email)->send(new SendOtpResetPasswordMail($namaUstadz, $otp, 15));
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim email OTP reset password ke ' . $user->email . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirimkan email verifikasi OTP. Pastikan koneksi internet aktif atau hubungi admin.',
                'error_detail' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }

        // Obfuscate email for privacy (e.g. us***@madrasah.com)
        $parts = explode('@', $user->email);
        $namePart = $parts[0];
        $maskedName = strlen($namePart) > 2 ? substr($namePart, 0, 2) . str_repeat('*', strlen($namePart) - 2) : substr($namePart, 0, 1) . '***';
        $maskedEmail = $maskedName . '@' . ($parts[1] ?? 'madrasah.com');

        return response()->json([
            'success' => true,
            'message' => 'Kode verifikasi OTP 6-digit telah dikirimkan ke alamat email ' . $maskedEmail . '. Silakan periksa kotak masuk atau spam email Anda.',
            'data' => [
                'login_id' => $loginId,
                'email_masked' => $maskedEmail,
                'expired_in_minutes' => 15,
            ]
        ], 200);
    }

    /**
     * 1.2b Verifikasi Kode OTP Saja (Langkah 2)
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login_id' => 'required|string',
            'otp_code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Masukkan 6 digit kode OTP yang valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->login_id)
            ->orWhere('username', $request->login_id)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak ditemukan.'
            ], 404);
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Permintaan reset kata sandi tidak ditemukan atau sudah kadaluarsa.'
            ], 400);
        }

        // Cek masa berlaku OTP (15 menit)
        $createdAt = Carbon::parse($record->created_at);
        if ($createdAt->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP telah kadaluarsa. Silakan minta kode baru.'
            ], 400);
        }

        // Verifikasi Kode OTP
        if (!Hash::check($request->otp_code, $record->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP yang dimasukkan salah. Silakan periksa kembali email Anda.'
            ], 422);
        }

        // Buat reset_token sementara yang aman untuk mengotorisasi halaman Buat Password Baru
        $resetToken = Str::random(64);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($resetToken),
                'created_at' => Carbon::now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP terverifikasi! Silakan buat kata sandi baru Anda.',
            'data' => [
                'login_id' => $request->login_id,
                'reset_token' => $resetToken,
            ]
        ], 200);
    }

    /**
     * 1.3 Buat & Simpan Password Baru (Langkah 3 - Setelah Verifikasi OTP)
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login_id' => 'required|string',
            'reset_token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Input tidak lengkap atau konfirmasi kata sandi tidak cocok.',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->login_id)
            ->orWhere('username', $request->login_id)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak ditemukan.'
            ], 404);
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi perubahan kata sandi tidak ditemukan atau sudah kadaluarsa.'
            ], 400);
        }

        // Cek masa berlaku sesi (15 menit)
        $createdAt = Carbon::parse($record->created_at);
        if ($createdAt->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            return response()->json([
                'success' => false,
                'message' => 'Sesi perubahan kata sandi telah kadaluarsa. Silakan ulangi proses lupa kata sandi.'
            ], 400);
        }

        // Verifikasi reset_token
        if (!Hash::check($request->reset_token, $record->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Otorisasi perubahan kata sandi tidak valid. Silakan verifikasi OTP ulang.'
            ], 422);
        }

        // Update kata sandi baru
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Keamanan: Revoke SEMUA token sesi aktif karena reset password dari lupa sandi
        $user->tokens()->delete();

        // Hapus token reset yang sudah dipakai
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi baru berhasil disimpan! Silakan masuk dengan kata sandi baru Anda.'
        ], 200);
    }

    public function profile(Request $request)
    {
        $user = $request->user()->load('ustadz');
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();

        $isWaliRuangan = false;
        $namaRuangan = null;
        $ruanganId = null;

        if ($tahunAktif && $user->ustadz) {
            $ruangan = Ruangan::where('tahun_pelajaran_id', $tahunAktif->id)
                ->where('ustadz_id', $user->ustadz->id)
                ->first();

            if ($ruangan) {
                $isWaliRuangan = true;
                $namaRuangan = $ruangan->nama_ruangan;
                $ruanganId = $ruangan->id;
            }
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'ustadz_id' => $user->ustadz->id ?? null,
                'name' => $user->ustadz->nama_lengkap ?? $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->roles->first()->name ?? 'ustadz',
                'photo' => $user->ustadz && $user->ustadz->foto ? asset('storage/' . $user->ustadz->foto) : null,
                'kode_ustadz' => $user->ustadz->kode_ustadz ?? null,
                'nigm' => $user->ustadz->nigm ?? null,
                'nik' => $user->ustadz->nik ?? null,
                'jenis_kelamin' => $user->ustadz->jenis_kelamin ?? 'L',
                'tempat_lahir' => $user->ustadz->tempat_lahir ?? null,
                'tanggal_lahir' => $user->ustadz && $user->ustadz->tanggal_lahir ? Carbon::parse($user->ustadz->tanggal_lahir)->format('Y-m-d') : null,
                'alamat' => $user->ustadz->alamat ?? null,
                'no_hp' => $user->ustadz->no_hp ?? null,
                'tahun_mulai_mengajar' => $user->ustadz->tahun_mulai_mengajar ?? null,
                'tanda_tangan' => $user->ustadz && $user->ustadz->tanda_tangan ? asset('storage/' . $user->ustadz->tanda_tangan) : null,
                'tahun_pelajaran' => $tahunAktif ? $tahunAktif->nama_hijriyah . ' H | ' . $tahunAktif->nama_masehi . ' M' : 'Belum Diatur',
                'is_wali_ruangan' => $isWaliRuangan,
                'ruangan_wali' => $namaRuangan,
                'ruangan_wali_id' => $ruanganId,
            ]
        ], 200);
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi saat ini tidak cocok.'
            ], 422);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        // Keamanan: Revoke semua sesi token di perangkat lain demi keamanan, sisakan sesi perangkat ini
        if ($user->currentAccessToken()) {
            $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi berhasil diperbarui.'
        ], 200);
    }

    /**
     * Ambil rincian lengkap biodata Ustadz yang sedang login
     */
    public function getBiodata(Request $request)
    {
        $user = $request->user()->load('ustadz');
        $ustadz = $user->ustadz;

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'ustadz_id' => $ustadz->id ?? null,
                'kode_ustadz' => $ustadz->kode_ustadz ?? null,
                'nama_lengkap' => $ustadz->nama_lengkap ?? $user->name,
                'nigm' => $ustadz->nigm ?? null,
                'nik' => $ustadz->nik ?? null,
                'jenis_kelamin' => $ustadz->jenis_kelamin ?? 'L',
                'tempat_lahir' => $ustadz->tempat_lahir ?? null,
                'tanggal_lahir' => $ustadz && $ustadz->tanggal_lahir ? Carbon::parse($ustadz->tanggal_lahir)->format('Y-m-d') : null,
                'alamat' => $ustadz->alamat ?? null,
                'no_hp' => $ustadz->no_hp ?? null,
                'tahun_mulai_mengajar' => $ustadz->tahun_mulai_mengajar ?? null,
                'foto' => $ustadz && $ustadz->foto ? asset('storage/' . $ustadz->foto) : null,
                'tanda_tangan' => $ustadz && $ustadz->tanda_tangan ? asset('storage/' . $ustadz->tanda_tangan) : null,
            ]
        ], 200);
    }

    /**
     * Update Akun Login (Username & Email)
     */
    public function updateAccount(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50|alpha_dash|unique:users,username,' . $user->id,
            'email' => 'required|email|max:100|unique:users,email,' . $user->id,
        ], [
            'username.unique' => 'Username ini sudah digunakan oleh akun lain.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda strip, atau garis bawah.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update([
            'username' => $request->username,
            'email' => $request->email,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data akun login berhasil diperbarui.',
            'data' => [
                'username' => $user->username,
                'email' => $user->email,
            ]
        ], 200);
    }

    /**
     * Update Biodata Ustadz
     */
    public function updateBiodata(Request $request)
    {
        $user = $request->user();
        $ustadz = $user->ustadz;

        if (!$ustadz) {
            return response()->json([
                'success' => false,
                'message' => 'Data profil ustadz tidak ditemukan.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:100',
            'nik' => ['nullable', 'string', 'size:16', new \App\Rules\UniqueEncrypted('ustadzs', 'nik_hash', $ustadz->id)],
            'nigm' => 'nullable|string|max:30|unique:ustadzs,nigm,' . $ustadz->id,
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:50',
            'tanggal_lahir' => 'nullable|date',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:500',
            'tahun_mulai_mengajar' => 'nullable|integer|digits:4',
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nik.size' => 'NIK harus tepat 16 digit.',
            'nik.unique' => 'NIK ini sudah terdaftar pada ustadz lain.',
            'nigm.unique' => 'NIGM ini sudah terdaftar pada ustadz lain.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        $ustadz->update([
            'nama_lengkap' => $request->nama_lengkap,
            'nik' => $request->nik,
            'nigm' => $request->nigm,
            'jenis_kelamin' => $request->jenis_kelamin,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'no_hp' => $request->no_hp,
            'alamat' => $request->alamat,
            'tahun_mulai_mengajar' => $request->tahun_mulai_mengajar,
        ]);

        // Sinkronkan nama tampilan user
        $user->update(['name' => $request->nama_lengkap]);

        return response()->json([
            'success' => true,
            'message' => 'Biodata ustadz berhasil diperbarui.',
            'data' => [
                'nama_lengkap' => $ustadz->nama_lengkap,
                'nik' => $ustadz->nik,
                'nigm' => $ustadz->nigm,
                'jenis_kelamin' => $ustadz->jenis_kelamin,
                'tempat_lahir' => $ustadz->tempat_lahir,
                'tanggal_lahir' => $ustadz->tanggal_lahir,
                'no_hp' => $ustadz->no_hp,
                'alamat' => $ustadz->alamat,
                'tahun_mulai_mengajar' => $ustadz->tahun_mulai_mengajar,
            ]
        ], 200);
    }

    /**
     * Update Foto Profil Ustadz (Dibatasi hanya untuk Administrator)
     */
    public function updateFoto(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Akses ditolak: Pengubahan foto profil ustadz hanya dapat dilakukan oleh Administrator Madrasah.',
        ], 403);
    }

    /**
     * Update Tanda Tangan Ustadz (Dibatasi hanya untuk Administrator)
     */
    public function updateTandaTangan(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Akses ditolak: Pengubahan tanda tangan digital ustadz hanya dapat dilakukan oleh Administrator Madrasah.',
        ], 403);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $platform = $user->hasRole('wali-murid') ? 'app_murid' : 'app_ustadz';
            ActivityLogService::recordLogout($request, $user, $platform);

            if ($user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
            }

            // Set status offline pada database
            $user->update([
                'last_seen_at' => Carbon::now()->subMinutes(5),
                'is_login'     => false,
                'is_logout'    => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout.'
        ], 200);
    }
}
