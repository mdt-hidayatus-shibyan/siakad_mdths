<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Table administrators
        if (Schema::hasTable('administrators')) {
            Schema::table('administrators', function (Blueprint $table) {
                if (Schema::hasColumn('administrators', 'nik')) {
                    try {
                        $table->dropUnique(['nik']);
                    } catch (\Throwable $e) {
                        try {
                            $table->dropUnique('administrators_nik_unique');
                        } catch (\Throwable $e2) {
                        }
                    }
                    $table->text('nik')->nullable()->change();
                }
                if (!Schema::hasColumn('administrators', 'nik_hash')) {
                    $table->string('nik_hash', 64)->nullable()->unique()->after('nik');
                }
            });
        }

        // 2. Table ustadzs
        if (Schema::hasTable('ustadzs')) {
            Schema::table('ustadzs', function (Blueprint $table) {
                if (Schema::hasColumn('ustadzs', 'nik')) {
                    try {
                        $table->dropUnique(['nik']);
                    } catch (\Throwable $e) {
                        try {
                            $table->dropUnique('ustadzs_nik_unique');
                        } catch (\Throwable $e2) {
                        }
                    }
                    $table->text('nik')->nullable()->change();
                }
                if (!Schema::hasColumn('ustadzs', 'nik_hash')) {
                    $table->string('nik_hash', 64)->nullable()->unique()->after('nik');
                }
            });
        }

        // 3. Table murids
        if (Schema::hasTable('murids')) {
            Schema::table('murids', function (Blueprint $table) {
                if (Schema::hasColumn('murids', 'nik')) {
                    try {
                        $table->dropUnique(['nik']);
                    } catch (\Throwable $e) {
                        try {
                            $table->dropUnique('murids_nik_unique');
                        } catch (\Throwable $e2) {
                        }
                    }
                    $table->text('nik')->nullable()->change();
                }
                if (Schema::hasColumn('murids', 'nik_ayah')) {
                    $table->text('nik_ayah')->nullable()->change();
                }
                if (Schema::hasColumn('murids', 'nik_ibu')) {
                    $table->text('nik_ibu')->nullable()->change();
                }
                if (!Schema::hasColumn('murids', 'nik_hash')) {
                    $table->string('nik_hash', 64)->nullable()->unique()->after('nik');
                }
            });
        }

        // 4. Table wali_murids
        if (Schema::hasTable('wali_murids')) {
            Schema::table('wali_murids', function (Blueprint $table) {
                if (Schema::hasColumn('wali_murids', 'no_kk')) {
                    try {
                        $table->dropUnique(['no_kk']);
                    } catch (\Throwable $e) {
                        try {
                            $table->dropUnique('wali_murids_no_kk_unique');
                        } catch (\Throwable $e2) {
                        }
                    }
                    $table->text('no_kk')->nullable()->change();
                }
                if (!Schema::hasColumn('wali_murids', 'no_kk_hash')) {
                    $table->string('no_kk_hash', 64)->nullable()->unique()->after('no_kk');
                }
            });
        }

        // 5. Table anggota (kepengurusan)
        if (Schema::hasTable('anggota')) {
            Schema::table('anggota', function (Blueprint $table) {
                if (Schema::hasColumn('anggota', 'nik')) {
                    try {
                        $table->dropUnique(['nik']);
                    } catch (\Throwable $e) {
                        try {
                            $table->dropUnique('anggota_nik_unique');
                        } catch (\Throwable $e2) {
                        }
                    }
                    $table->text('nik')->nullable()->change();
                }
                if (!Schema::hasColumn('anggota', 'nik_hash')) {
                    $table->string('nik_hash', 64)->nullable()->unique()->after('nik');
                }
            });
        }

        // 6. Table pendaftaran_spmbs
        if (Schema::hasTable('pendaftaran_spmbs')) {
            Schema::table('pendaftaran_spmbs', function (Blueprint $table) {
                if (Schema::hasColumn('pendaftaran_spmbs', 'nik')) {
                    $table->text('nik')->nullable()->change();
                }
                if (Schema::hasColumn('pendaftaran_spmbs', 'nik_ayah')) {
                    $table->text('nik_ayah')->nullable()->change();
                }
                if (Schema::hasColumn('pendaftaran_spmbs', 'nik_ibu')) {
                    $table->text('nik_ibu')->nullable()->change();
                }
                if (!Schema::hasColumn('pendaftaran_spmbs', 'nik_hash')) {
                    $table->string('nik_hash', 64)->nullable()->index()->after('nik');
                }
                if (!Schema::hasColumn('pendaftaran_spmbs', 'no_kk_hash')) {
                    $table->string('no_kk_hash', 64)->nullable()->index()->after('nik_hash');
                }
            });
        }

        // 7. Table nasabah_pinjamans
        if (Schema::hasTable('nasabah_pinjamans')) {
            Schema::table('nasabah_pinjamans', function (Blueprint $table) {
                if (Schema::hasColumn('nasabah_pinjamans', 'nik_ktp')) {
                    $table->text('nik_ktp')->nullable()->change();
                }
                if (!Schema::hasColumn('nasabah_pinjamans', 'nik_ktp_hash')) {
                    $table->string('nik_ktp_hash', 64)->nullable()->index()->after('nik_ktp');
                }
            });
        }

        // 8. Migrasi data lama (enkripsi data plaintext yang sudah ada)
        $this->encryptExistingData();
    }

    /**
     * Enkripsi data sensitif plaintext yang sudah ada di database.
     */
    protected function encryptExistingData(): void
    {
        // Administrators
        if (Schema::hasTable('administrators') && Schema::hasColumn('administrators', 'nik')) {
            $records = DB::table('administrators')->whereNotNull('nik')->get();
            foreach ($records as $row) {
                $val = $this->decryptIfAlreadyEncrypted($row->nik);
                if ($val !== null && $val !== '') {
                    DB::table('administrators')->where('id', $row->id)->update([
                        'nik'      => Crypt::encryptString($val),
                        'nik_hash' => hash_sensitive($val),
                    ]);
                }
            }
        }

        // Ustadzs
        if (Schema::hasTable('ustadzs') && Schema::hasColumn('ustadzs', 'nik')) {
            $records = DB::table('ustadzs')->whereNotNull('nik')->get();
            foreach ($records as $row) {
                $val = $this->decryptIfAlreadyEncrypted($row->nik);
                if ($val !== null && $val !== '') {
                    DB::table('ustadzs')->where('id', $row->id)->update([
                        'nik'      => Crypt::encryptString($val),
                        'nik_hash' => hash_sensitive($val),
                    ]);
                }
            }
        }

        // Murids
        if (Schema::hasTable('murids')) {
            $records = DB::table('murids')->get();
            foreach ($records as $row) {
                $updates = [];
                if (!empty($row->nik)) {
                    $val = $this->decryptIfAlreadyEncrypted($row->nik);
                    $updates['nik'] = Crypt::encryptString($val);
                    $updates['nik_hash'] = hash_sensitive($val);
                }
                if (!empty($row->nik_ayah)) {
                    $val = $this->decryptIfAlreadyEncrypted($row->nik_ayah);
                    $updates['nik_ayah'] = Crypt::encryptString($val);
                }
                if (!empty($row->nik_ibu)) {
                    $val = $this->decryptIfAlreadyEncrypted($row->nik_ibu);
                    $updates['nik_ibu'] = Crypt::encryptString($val);
                }
                if (!empty($updates)) {
                    DB::table('murids')->where('id', $row->id)->update($updates);
                }
            }
        }

        // Wali Murids
        if (Schema::hasTable('wali_murids') && Schema::hasColumn('wali_murids', 'no_kk')) {
            $records = DB::table('wali_murids')->whereNotNull('no_kk')->get();
            foreach ($records as $row) {
                $val = $this->decryptIfAlreadyEncrypted($row->no_kk);
                if ($val !== null && $val !== '') {
                    DB::table('wali_murids')->where('id', $row->id)->update([
                        'no_kk'      => Crypt::encryptString($val),
                        'no_kk_hash' => hash_sensitive($val),
                    ]);
                }
            }
        }

        // Anggota
        if (Schema::hasTable('anggota') && Schema::hasColumn('anggota', 'nik')) {
            $records = DB::table('anggota')->whereNotNull('nik')->get();
            foreach ($records as $row) {
                $val = $this->decryptIfAlreadyEncrypted($row->nik);
                if ($val !== null && $val !== '') {
                    DB::table('anggota')->where('id', $row->id)->update([
                        'nik'      => Crypt::encryptString($val),
                        'nik_hash' => hash_sensitive($val),
                    ]);
                }
            }
        }

        // Pendaftaran SPMB
        if (Schema::hasTable('pendaftaran_spmbs')) {
            $records = DB::table('pendaftaran_spmbs')->get();
            foreach ($records as $row) {
                $updates = [];
                if (!empty($row->nik)) {
                    $val = $this->decryptIfAlreadyEncrypted($row->nik);
                    $updates['nik'] = Crypt::encryptString($val);
                    $updates['nik_hash'] = hash_sensitive($val);
                }
                if (!empty($row->nik_ayah)) {
                    $val = $this->decryptIfAlreadyEncrypted($row->nik_ayah);
                    $updates['nik_ayah'] = Crypt::encryptString($val);
                }
                if (!empty($row->nik_ibu)) {
                    $val = $this->decryptIfAlreadyEncrypted($row->nik_ibu);
                    $updates['nik_ibu'] = Crypt::encryptString($val);
                }
                if (!empty($updates)) {
                    DB::table('pendaftaran_spmbs')->where('id', $row->id)->update($updates);
                }
            }
        }

        // Nasabah Pinjaman
        if (Schema::hasTable('nasabah_pinjamans') && Schema::hasColumn('nasabah_pinjamans', 'nik_ktp')) {
            $records = DB::table('nasabah_pinjamans')->whereNotNull('nik_ktp')->get();
            foreach ($records as $row) {
                $val = $this->decryptIfAlreadyEncrypted($row->nik_ktp);
                if ($val !== null && $val !== '') {
                    DB::table('nasabah_pinjamans')->where('id', $row->id)->update([
                        'nik_ktp'      => Crypt::encryptString($val),
                        'nik_ktp_hash' => hash_sensitive($val),
                    ]);
                }
            }
        }
    }

    /**
     * Ambil nilai string, jika sudah terenkripsi dekripsi dulu agar tidak double encrypt.
     */
    protected function decryptIfAlreadyEncrypted(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return $value;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('administrators') && Schema::hasColumn('administrators', 'nik_hash')) {
            Schema::table('administrators', function (Blueprint $table) {
                $table->dropColumn('nik_hash');
            });
        }
        if (Schema::hasTable('ustadzs') && Schema::hasColumn('ustadzs', 'nik_hash')) {
            Schema::table('ustadzs', function (Blueprint $table) {
                $table->dropColumn('nik_hash');
            });
        }
        if (Schema::hasTable('murids') && Schema::hasColumn('murids', 'nik_hash')) {
            Schema::table('murids', function (Blueprint $table) {
                $table->dropColumn('nik_hash');
            });
        }
        if (Schema::hasTable('wali_murids') && Schema::hasColumn('wali_murids', 'no_kk_hash')) {
            Schema::table('wali_murids', function (Blueprint $table) {
                $table->dropColumn('no_kk_hash');
            });
        }
        if (Schema::hasTable('anggota') && Schema::hasColumn('anggota', 'nik_hash')) {
            Schema::table('anggota', function (Blueprint $table) {
                $table->dropColumn('nik_hash');
            });
        }
        if (Schema::hasTable('pendaftaran_spmbs')) {
            Schema::table('pendaftaran_spmbs', function (Blueprint $table) {
                if (Schema::hasColumn('pendaftaran_spmbs', 'nik_hash')) {
                    $table->dropColumn('nik_hash');
                }
                if (Schema::hasColumn('pendaftaran_spmbs', 'no_kk_hash')) {
                    $table->dropColumn('no_kk_hash');
                }
            });
        }
        if (Schema::hasTable('nasabah_pinjamans') && Schema::hasColumn('nasabah_pinjamans', 'nik_ktp_hash')) {
            Schema::table('nasabah_pinjamans', function (Blueprint $table) {
                $table->dropColumn('nik_ktp_hash');
            });
        }
    }
};
