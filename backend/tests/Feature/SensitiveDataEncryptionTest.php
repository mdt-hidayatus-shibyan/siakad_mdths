<?php

namespace Tests\Feature;

use App\Models\Administrator;
use App\Models\Kampung;
use App\Models\Kepengurusan\Anggota;
use App\Models\Level;
use App\Models\Murid;
use App\Models\PendaftaranSpmb;
use App\Models\TahunPelajaran;
use App\Models\Tingkat;
use App\Models\User;
use App\Models\Ustadz;
use App\Models\WaliMurid;
use App\Rules\UniqueEncrypted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class SensitiveDataEncryptionTest extends TestCase
{
    use RefreshDatabase;

    protected Kampung $kampung;
    protected TahunPelajaran $tahun;
    protected Tingkat $tingkat;
    protected Level $level;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kampung = Kampung::create([
            'kode'         => 'K99',
            'nama_kampung' => 'Kampung Test Enkripsi'
        ]);

        $this->tahun = TahunPelajaran::create([
            'nama_hijriyah' => '1447-1448',
            'nama_masehi'   => '2026-2027',
            'is_active'     => true
        ]);

        $this->tingkat = Tingkat::create([
            'kode_tingkat'     => 'TPQ',
            'urutan_tingkat'   => 1,
            'nama_tingkat'     => 'TAMAN PENDIDIKAN ALQURAN',
            'kode_mdt_tingkat' => 'RA',
            'nama_mdt_tingkat' => 'RAUDLATUL ATHFAL',
            'kode_warna'       => '#10B981',
            'is_active'        => 1
        ]);

        $this->level = Level::create([
            'tingkat_id'   => $this->tingkat->id,
            'nama_level'   => '1 TPQ',
            'urutan_level' => 1
        ]);
    }

    public function test_nik_dan_kk_disimpan_dalam_bentuk_terenkripsi_di_database(): void
    {
        $plainNikMurid = '3526110101010001';
        $plainNikAyah  = '3526110101700001';
        $plainNikIbu   = '3526110101750002';
        $plainNoKk     = '3526110000000001';

        // 1. Buat Wali Murid
        $wali = WaliMurid::create([
            'no_registrasi'        => '50001',
            'no_kk'                => $plainNoKk,
            'kepala_keluarga'      => 'Ayah',
            'nama_kepala_keluarga' => 'BAPAK TEST ENKRIPSI',
            'kampung_id'           => $this->kampung->id,
            'is_active'            => true,
        ]);

        // 2. Buat Murid
        $murid = Murid::create([
            'wali_murid_id'  => $wali->id,
            'nism'           => '990001',
            'nama_lengkap'   => 'SANTRI TEST ENKRIPSI',
            'jenis_kelamin'  => 'L',
            'nik'            => $plainNikMurid,
            'nik_ayah'       => $plainNikAyah,
            'nik_ibu'        => $plainNikIbu,
            'status_ayah'    => 'Hidup',
            'status_ibu'     => 'Hidup',
            'status'         => 'Aktif',
            'tahun_masuk'    => $this->tahun->id,
            'level_masuk'    => $this->level->id,
            'hub_kel'        => 'Anak Kandung',
        ]);

        // 3. Inspeksi data RAW di Database (At Rest)
        $rawWali = DB::table('wali_murids')->where('id', $wali->id)->first();
        $rawMurid = DB::table('murids')->where('id', $murid->id)->first();

        // Pastikan nilai di kolom database BUKAN plaintext
        $this->assertNotEquals($plainNoKk, $rawWali->no_kk);
        $this->assertNotEquals($plainNikMurid, $rawMurid->nik);
        $this->assertNotEquals($plainNikAyah, $rawMurid->nik_ayah);
        $this->assertNotEquals($plainNikIbu, $rawMurid->nik_ibu);

        // Pastikan kolom hash HMAC-SHA256 terisi dan cocok
        $this->assertEquals(hash_sensitive($plainNoKk), $rawWali->no_kk_hash);
        $this->assertEquals(hash_sensitive($plainNikMurid), $rawMurid->nik_hash);

        // Pastikan data dapat didekripsi dengan kunci AES-256 Laravel
        $this->assertEquals($plainNoKk, Crypt::decryptString($rawWali->no_kk));
        $this->assertEquals($plainNikMurid, Crypt::decryptString($rawMurid->nik));
    }

    public function test_eloquent_mendekripsi_data_sensitif_secara_transparan(): void
    {
        $plainNik = '3526110705960003';

        $ustadz = Ustadz::create([
            'nama_lengkap'  => 'USTADZ AHMAD TEST',
            'nik'           => $plainNik,
            'nigm'          => 'NIGM-999',
            'jenis_kelamin' => 'L',
            'is_active'     => true,
        ]);

        $freshUstadz = Ustadz::find($ustadz->id);

        // Eloquent getAttribute harus mengembalikan plaintext
        $this->assertEquals($plainNik, $freshUstadz->nik);
    }

    public function test_helper_masking_dan_accessor_menghasilkan_format_tersamar(): void
    {
        $nik = '3201234567890001';
        $kk  = '3201998877660002';

        // Format yang diharapkan: 4 digit awal terbuka, 12 digit disamarkan dengan bintang (*)
        $this->assertEquals('3201************', mask_nik($nik));
        $this->assertEquals('3201************', mask_kk($kk));
        $this->assertEquals('-', mask_nik(null));
        $this->assertEquals('-', mask_kk(null));

        $admin = new Administrator(['nik' => $nik]);
        $this->assertEquals('3201************', $admin->masked_nik);
    }

    public function test_pencarian_via_scope_blind_index_berfungsi(): void
    {
        $plainNik = '3526118888880001';
        $plainNoKk = '3526117777770001';

        $wali = WaliMurid::create([
            'no_registrasi'        => '50002',
            'no_kk'                => $plainNoKk,
            'kepala_keluarga'      => 'Ibu',
            'nama_kepala_keluarga' => 'IBU CARI TEST',
            'kampung_id'           => $this->kampung->id,
            'is_active'            => true,
        ]);

        $ustadz = Ustadz::create([
            'nama_lengkap'  => 'USTADZ CARI TEST',
            'nik'           => $plainNik,
            'nigm'          => 'NIGM-888',
            'jenis_kelamin' => 'L',
            'is_active'     => true,
        ]);

        // Cari via scope query
        $foundWali = WaliMurid::whereNoKk($plainNoKk)->first();
        $foundUstadz = Ustadz::whereNik($plainNik)->first();

        $this->assertNotNull($foundWali);
        $this->assertEquals($wali->id, $foundWali->id);

        $this->assertNotNull($foundUstadz);
        $this->assertEquals($ustadz->id, $foundUstadz->id);

        // Cari dengan NIK yang salah
        $notFound = Ustadz::whereNik('0000000000000000')->first();
        $this->assertNull($notFound);
    }

    public function test_validasi_unique_encrypted_mencegah_duplikasi(): void
    {
        $plainNik = '3526119999990001';

        $admin = Administrator::create([
            'nama_lengkap'  => 'ADMIN UTAMA TEST',
            'nik'           => $plainNik,
            'jenis_kelamin' => 'L',
            'tempat_lahir'  => 'Bangkalan',
            'tanggal_lahir' => '1995-01-01',
            'alamat'        => 'Jl. Test',
            'no_hp'         => '081234567890',
            'is_active'     => true,
        ]);

        // Coba validasi NIK yang sama (harus fail)
        $validatorFail = Validator::make(
            ['nik' => $plainNik],
            ['nik' => [new UniqueEncrypted('administrators', 'nik_hash')]]
        );
        $this->assertTrue($validatorFail->fails());

        // Validasi NIK yang sama dengan ID yang diabaikan (harus pass saat edit diri sendiri)
        $validatorPass = Validator::make(
            ['nik' => $plainNik],
            ['nik' => [new UniqueEncrypted('administrators', 'nik_hash', $admin->id)]]
        );
        $this->assertTrue($validatorPass->passes());

        // Validasi NIK baru (harus pass)
        $validatorNew = Validator::make(
            ['nik' => '3526111111110001'],
            ['nik' => [new UniqueEncrypted('administrators', 'nik_hash')]]
        );
        $this->assertTrue($validatorNew->passes());
    }
}
