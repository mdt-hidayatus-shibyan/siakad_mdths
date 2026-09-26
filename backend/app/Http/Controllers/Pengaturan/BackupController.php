<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class BackupController extends Controller
{

    public function index()
    {
        if (!auth()->user()?->hasRole('administrator')) {
            abort(403, 'Akses Ditolak. Hanya Administrator yang diizinkan.');
        }
        return view('backup.index');
    }

    /**
     * Proses eksekusi backup dan unduh file .sql
     */
    public function process()
    {
        // Proteksi ekstra ganda
        if (!auth()->user()?->hasRole('administrator')) {
            abort(403, 'Akses Ditolak. Hanya Administrator yang diizinkan.');
        }

        try {
            // 1. Siapkan nama file dan direktori penyimpanan sementara
            $filename = 'backup_madrasah_' . date('Y_m_d_His') . '.sql';
            $storagePath = storage_path('app/backups');
            $filePath = $storagePath . '/' . $filename;

            // Buat folder 'backups' di storage/app jika belum ada
            if (!File::exists($storagePath)) {
                File::makeDirectory($storagePath, 0755, true);
            }

            // 2. Ambil kredensial database dari config (aman saat config:cache aktif)
            $host = config('database.connections.mysql.host', '127.0.0.1');
            $username = config('database.connections.mysql.username', 'root');
            $password = config('database.connections.mysql.password', '');
            $database = config('database.connections.mysql.database', '');

            // 3. Susun perintah mysqldump (Sesuaikan path mysqldump jika di Windows/XAMPP)
            $mysqldumpPath = 'mysqldump';

            $command = sprintf(
                '%s --host=%s --user=%s --password=%s %s > %s',
                $mysqldumpPath,
                escapeshellarg($host),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($filePath)
            );

            // 4. Eksekusi perintah di server
            $output = null;
            $resultCode = null;
            exec($command, $output, $resultCode);

            // 5. Cek apakah backup berhasil dibuat
            if ($resultCode === 0 && File::exists($filePath)) {
                // Unduh file dan otomatis hapus dari server setelah selesai diunduh agar tidak memenuhi memori
                return Response::download($filePath, $filename, [
                    'Content-Type' => 'application/sql',
                ])->deleteFileAfterSend(true);
            } else {
                Log::error("Backup gagal. Code: $resultCode. Command: $command");
                return back()->with('error', 'Gagal mengeksekusi mysqldump. Pastikan fungsi exec() diizinkan di server Anda.');
            }
        } catch (\Exception $e) {
            Log::error("Error Backup: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem saat membuat cadangan data: ' . $e->getMessage());
        }
    }

    public function restore(Request $request)
    {
        // Proteksi role administrator
        if (!auth()->user()?->hasRole('administrator')) {
            abort(403, 'Akses Ditolak. Hanya Administrator yang diizinkan.');
        }

        // 1. Validasi file (harus berekstensi .sql atau tipe text)
        $request->validate([
            'file_sql' => 'required|file|max:51200', // Maksimal 50MB
        ], [
            'file_sql.required' => 'File database wajib diunggah.',
            'file_sql.file' => 'Format file tidak valid.',
            'file_sql.max' => 'Ukuran file maksimal adalah 50MB.',
        ]);

        $file = $request->file('file_sql');

        // Pastikan ekstensinya benar-benar .sql
        if ($file->getClientOriginalExtension() !== 'sql') {
            return back()->with('error', 'File harus berekstensi .sql!');
        }

        try {
            // 2. Baca isi file SQL
            $sql = File::get($file->getRealPath());

            // 3. Matikan sementara pengecekan Foreign Key agar proses drop/create table tidak bentrok
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // 4. Eksekusi seluruh query di dalam file
            DB::unprepared($sql);

            // 5. Nyalakan kembali pengecekan Foreign Key
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return back()->with('success', 'Database berhasil dipulihkan dari file cadangan!');
        } catch (\Exception $e) {
            // Nyalakan kembali jika terjadi error di tengah jalan
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            Log::error('Restore Database Error: ' . $e->getMessage());

            $errMsg = $e->getMessage();
            if (strlen($errMsg) > 200) {
                $errMsg = substr($errMsg, 0, 200) . '...';
            }

            return back()->with('error', 'Gagal memulihkan database: ' . $errMsg);
        }
    }
}
