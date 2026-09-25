<?php

namespace App\Services;

use App\Models\Ustadz;
use App\Repositories\Contracts\UstadzRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

readonly class UstadzService
{
    public function __construct(
        private UstadzRepositoryInterface $ustadzRepository
    ) {}

    public function getPaginatedUstadzs(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return $this->ustadzRepository->getPaginated($filters, $perPage);
    }

    public function getAvailableUsersForCreate(): Collection
    {
        return $this->ustadzRepository->getAvailableUsersForCreate();
    }

    public function getAvailableUsersForEdit(?int $currentUserId = null): Collection
    {
        return $this->ustadzRepository->getAvailableUsersForEdit($currentUserId);
    }

    public function findUstadz(int $id): Ustadz
    {
        return $this->ustadzRepository->findById($id);
    }

    public function createUstadz(
        array $data,
        ?UploadedFile $foto = null,
        ?UploadedFile $tandaTangan = null,
        ?string $usernameInput = null,
        ?string $emailInput = null,
        bool $isActive = false
    ): array {
        DB::beginTransaction();

        $uploadedFoto = null;
        $uploadedTtd = null;

        try {
            $data['is_active'] = $isActive;
            $user = null;

            if (!empty($usernameInput) && !empty($emailInput)) {
                $baseUsername = Str::slug($usernameInput, '.');
                $username = $baseUsername;
                $counter = 1;

                while ($this->ustadzRepository->findUserByUsername($username)) {
                    $username = $baseUsername . $counter;
                    $counter++;
                }

                $user = $this->ustadzRepository->createUserAccount([
                    'name'              => $data['nama_lengkap'],
                    'username'          => $username,
                    'email'             => $emailInput,
                    'password'          => Hash::make('madrasah123'),
                    'email_verified_at' => now(),
                ]);

                $data['user_id'] = $user->id;
            } else {
                $data['user_id'] = null;
            }

            if ($foto) {
                $uploadedFoto = $foto->store('uploads/ustadz/foto', 'public');
                $data['foto'] = $uploadedFoto;
            }

            if ($tandaTangan) {
                $uploadedTtd = $tandaTangan->store('uploads/ustadz/ttd', 'public');
                $data['tanda_tangan'] = $uploadedTtd;
            }

            $ustadz = $this->ustadzRepository->create($data);

            DB::commit();

            $pesan = $user
                ? "Data Ustadz berhasil ditambahkan! Username login: {$user->username}"
                : "Data Ustadz berhasil ditambahkan (Tanpa akses login).";

            return [
                'ustadz'  => $ustadz,
                'user'    => $user,
                'message' => $pesan,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            if ($uploadedFoto && Storage::disk('public')->exists($uploadedFoto)) {
                Storage::disk('public')->delete($uploadedFoto);
            }
            if ($uploadedTtd && Storage::disk('public')->exists($uploadedTtd)) {
                Storage::disk('public')->delete($uploadedTtd);
            }

            throw $e;
        }
    }

    public function updateUstadz(
        int $id,
        array $requestData,
        ?string $formType = null,
        ?UploadedFile $foto = null,
        ?UploadedFile $tandaTangan = null
    ): array {
        $ustadz = $this->ustadzRepository->findById($id);

        DB::beginTransaction();

        $newFotoPath = null;
        $newTtdPath = null;

        try {
            if ($formType === 'profil' || !$formType) {
                $data = $requestData;

                // Jika NIGM belum ada dan dikosongkan pada form, generate NIGM otomatis
                if (empty($data['nigm'])) {
                    if (empty($ustadz->nigm)) {
                        $data['nigm'] = Ustadz::generateNigm();
                    } else {
                        // Pertahankan NIGM lama jika field dikosongkan tidak sengaja atau set default
                        $data['nigm'] = $ustadz->nigm;
                    }
                }

                if ($foto) {
                    $newFotoPath = $foto->store('uploads/ustadz/foto', 'public');
                    $data['foto'] = $newFotoPath;
                }

                if ($tandaTangan) {
                    $newTtdPath = $tandaTangan->store('uploads/ustadz/ttd', 'public');
                    $data['tanda_tangan'] = $newTtdPath;
                }

                $oldFoto = $ustadz->foto;
                $oldTtd = $ustadz->tanda_tangan;

                $this->ustadzRepository->update($ustadz, $data);

                if ($ustadz->user) {
                    $this->ustadzRepository->updateUserAccount($ustadz->user, [
                        'name' => $data['nama_lengkap'] ?? $ustadz->nama_lengkap
                    ]);
                }

                if ($newFotoPath && $oldFoto && Storage::disk('public')->exists($oldFoto)) {
                    Storage::disk('public')->delete($oldFoto);
                }
                if ($newTtdPath && $oldTtd && Storage::disk('public')->exists($oldTtd)) {
                    Storage::disk('public')->delete($oldTtd);
                }

                $pesan = 'Data Profil Ustadz berhasil diperbarui!';
            } elseif ($formType === 'akun') {
                if ($ustadz->user_id && $ustadz->user) {
                    $this->ustadzRepository->updateUserAccount($ustadz->user, [
                        'username'          => $requestData['username'] ?? $ustadz->user->username,
                        'email'             => $requestData['email'] ?? $ustadz->user->email,
                        'email_verified_at' => now(),
                    ]);
                } else {
                    if (!empty($requestData['username']) && !empty($requestData['email'])) {
                        $baseUsername = Str::slug($requestData['username'], '.');
                        $username = $baseUsername;
                        $counter = 1;

                        while ($this->ustadzRepository->findUserByUsername($username)) {
                            $username = $baseUsername . $counter;
                            $counter++;
                        }

                        $newUser = $this->ustadzRepository->createUserAccount([
                            'name'              => $ustadz->nama_lengkap,
                            'username'          => $username,
                            'email'             => $requestData['email'],
                            'password'          => Hash::make('madrasah123'),
                            'email_verified_at' => now(),
                        ]);

                        $this->ustadzRepository->update($ustadz, ['user_id' => $newUser->id]);
                    }
                }

                if (array_key_exists('is_active', $requestData)) {
                    $this->ustadzRepository->update($ustadz, [
                        'is_active' => $requestData['is_active']
                    ]);
                }

                $pesan = 'Pengaturan Akun Ustadz berhasil diperbarui!';
            } else {
                $pesan = 'Data Ustadz berhasil diperbarui!';
            }

            DB::commit();

            return [
                'ustadz'  => $ustadz,
                'message' => $pesan,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            if ($newFotoPath && Storage::disk('public')->exists($newFotoPath)) {
                Storage::disk('public')->delete($newFotoPath);
            }
            if ($newTtdPath && Storage::disk('public')->exists($newTtdPath)) {
                Storage::disk('public')->delete($newTtdPath);
            }

            throw $e;
        }
    }

    public function toggleStatus(int $id): array
    {
        $ustadz = $this->ustadzRepository->findById($id);

        $newStatus = !$ustadz->is_active;
        $this->ustadzRepository->update($ustadz, ['is_active' => $newStatus]);

        if ($ustadz->user) {
            $this->ustadzRepository->updateUserAccount($ustadz->user, ['is_active' => $newStatus]);
        }

        return [
            'ustadz'    => $ustadz,
            'is_active' => $newStatus,
            'message'   => 'Status Ustadz berhasil diubah!',
        ];
    }

    public function deleteUstadz(Ustadz $ustadz): void
    {
        DB::beginTransaction();

        try {
            if ($ustadz->foto && Storage::disk('public')->exists($ustadz->foto)) {
                Storage::disk('public')->delete($ustadz->foto);
            }

            if ($ustadz->tanda_tangan && Storage::disk('public')->exists($ustadz->tanda_tangan)) {
                Storage::disk('public')->delete($ustadz->tanda_tangan);
            }

            $user = $ustadz->user;

            $this->ustadzRepository->delete($ustadz);

            if ($user) {
                $user->delete();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function importFromCsv(UploadedFile $file): array
    {
        $filePath = $file->path();
        $barisPertama = fgets(fopen($filePath, 'r'));
        $delimiter = strpos($barisPertama, ';') !== false ? ';' : ',';

        $handle = fopen($filePath, 'r');
        $header = fgetcsv($handle, 1000, $delimiter);

        $berhasil = 0;
        $gagalKolom = 0;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                $row = array_filter($row, fn($value) => $value !== null);

                if (count($row) < 11) {
                    $gagalKolom++;
                    continue;
                }

                $cleanNull = function ($value) {
                    $trimmed = trim($value);
                    if ($trimmed === '' || strtolower($trimmed) === 'null' || $trimmed === '-') {
                        return null;
                    }
                    return $trimmed;
                };

                $id           = $cleanNull($row[0]);
                $kodeUstadz   = $cleanNull($row[1]);
                $nigm         = $cleanNull($row[2]);
                $nik          = $cleanNull($row[3]);
                $namaLengkap  = $cleanNull($row[4]);

                $inputJk = strtoupper(trim($row[5]));
                $jenisKelamin = in_array($inputJk, ['L', 'LAKI-LAKI', 'PRIA']) ? 'L' : 'P';

                $tempatLahir  = $cleanNull($row[6]);
                $rawTanggal   = $cleanNull($row[7]);
                $tanggalLahir = null;

                if ($rawTanggal) {
                    $rawTanggal = str_replace('/', '-', $rawTanggal);
                    $tanggalLahir = date('Y-m-d', strtotime($rawTanggal));
                }

                $alamat             = $cleanNull($row[8]);
                $noHp               = $cleanNull($row[9]);
                $tahunMulaiMengajar = $cleanNull($row[10]);
                $isActive           = !in_array(strtolower(trim($row[11] ?? '')), ['0', 'false', 'tidak', 't', 'n']);

                if (empty($namaLengkap)) {
                    $gagalKolom++;
                    continue;
                }

                $ustadzData = [
                    'kode_ustadz'          => $kodeUstadz,
                    'nigm'                 => $nigm,
                    'nik'                  => $nik,
                    'nama_lengkap'         => $namaLengkap,
                    'jenis_kelamin'        => $jenisKelamin,
                    'tempat_lahir'         => $tempatLahir,
                    'tanggal_lahir'        => $tanggalLahir,
                    'alamat'               => $alamat,
                    'no_hp'                => $noHp,
                    'tahun_mulai_mengajar' => $tahunMulaiMengajar,
                    'is_active'            => $isActive,
                ];

                if (!empty($id)) {
                    $ustadz = $this->ustadzRepository->findById((int)$id);
                    if ($ustadz) {
                        $this->ustadzRepository->update($ustadz, $ustadzData);
                    } else {
                        $ustadzData['id'] = $id;
                        $this->ustadzRepository->create($ustadzData);
                    }
                } else {
                    $ustadz = $this->ustadzRepository->findByNigmOrNik($nigm, $nik);
                    if ($ustadz) {
                        $this->ustadzRepository->update($ustadz, $ustadzData);
                    } else {
                        $this->ustadzRepository->create($ustadzData);
                    }
                }

                $berhasil++;
            }

            DB::commit();
            fclose($handle);

            $pesan = "Import Selesai! Berhasil: {$berhasil} data Ustadz.";
            if ($gagalKolom > 0) {
                $pesan .= " Gagal/Format Salah: {$gagalKolom} baris.";
            }

            return [
                'berhasil'    => $berhasil,
                'gagal_kolom' => $gagalKolom,
                'message'     => $pesan,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            if (is_resource($handle)) {
                fclose($handle);
            }
            throw $e;
        }
    }

    public function resendVerification(Ustadz $ustadz): array
    {
        $user = $ustadz->user;

        if ($user && !$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
            return [
                'status'  => 'success',
                'message' => 'Email verifikasi ulang berhasil dikirim ke ' . $user->email,
            ];
        }

        return [
            'status'  => 'info',
            'message' => 'Akun ini sudah terverifikasi.',
        ];
    }

    public function updateSignature(int $id, string $base64Image): Ustadz
    {
        $ustadz = $this->ustadzRepository->findById($id);

        $imageParts = explode(';base64,', $base64Image);
        $imageTypeAux = explode('image/', $imageParts[0]);
        $imageType = $imageTypeAux[1] ?? 'png';
        $imageBase64 = base64_decode($imageParts[1]);

        $fileName = 'ttd_' . uniqid() . '.' . $imageType;
        $newTtdPath = 'uploads/ustadz/ttd/' . $fileName;

        if ($ustadz->tanda_tangan && Storage::disk('public')->exists($ustadz->tanda_tangan)) {
            Storage::disk('public')->delete($ustadz->tanda_tangan);
        }

        Storage::disk('public')->put($newTtdPath, $imageBase64);

        $this->ustadzRepository->update($ustadz, ['tanda_tangan' => $newTtdPath]);

        return $ustadz;
    }
}
