<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EncryptSensitiveDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:encrypt-sensitive-data {--force : Jalankan proses enkripsi tanpa prompt konfirmasi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enkripsi data sensitif (NIK dan No. KK) ke AES-256 dan isi blind index hash untuk pengamanan At Rest';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai proses enkripsi data sensitif (Field-Level Encryption AES-256)...');

        $this->processTable('administrators', ['nik']);
        $this->processTable('ustadzs', ['nik']);
        $this->processTable('murids', ['nik', 'nik_ayah', 'nik_ibu']);
        $this->processTable('wali_murids', ['no_kk']);
        $this->processTable('anggota', ['nik']);
        $this->processTable('pendaftaran_spmbs', ['nik', 'nik_ayah', 'nik_ibu']);
        $this->processTable('nasabah_pinjamans', ['nik_ktp']);

        $this->newLine();
        $this->info('✓ Seluruh data sensitif berhasil dienkripsi dan disinkronkan dengan blind index hash.');

        return Command::SUCCESS;
    }

    protected function processTable(string $table, array $fields): void
    {
        if (!Schema::hasTable($table)) {
            $this->warn("Tabel {$table} tidak ditemukan, dilewati.");
            return;
        }

        $records = DB::table($table)->get();
        $count = $records->count();

        $this->line("Memproses tabel <comment>{$table}</comment> ({$count} data)...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $updatedCount = 0;

        foreach ($records as $row) {
            $updates = [];

            foreach ($fields as $field) {
                if (!Schema::hasColumn($table, $field)) {
                    continue;
                }

                $raw = $row->{$field} ?? null;
                if ($raw === null || $raw === '') {
                    continue;
                }

                $plainValue = $this->getPlainValue($raw);
                $hashField = $field . '_hash';

                $encryptedValue = Crypt::encryptString($plainValue);
                $updates[$field] = $encryptedValue;

                if (Schema::hasColumn($table, $hashField)) {
                    $updates[$hashField] = hash_sensitive($plainValue);
                }
            }

            if (!empty($updates)) {
                DB::table($table)->where('id', $row->id)->update($updates);
                $updatedCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->line("  ✓ {$updatedCount} data pada tabel {$table} berhasil diperbarui.");
    }

    protected function getPlainValue(string $value): string
    {
        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            return $value;
        } catch (\Throwable $e) {
            return $value;
        }
    }
}
