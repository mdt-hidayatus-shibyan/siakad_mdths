<?php

namespace App\Traits;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

trait HasEncryptedSensitiveData
{
    /**
     * Daftar default field sensitif yang akan dienkripsi jika ada di model.
     *
     * @return array
     */
    public function getEncryptedFields(): array
    {
        if (property_exists($this, 'encryptedFields') && is_array($this->encryptedFields)) {
            return $this->encryptedFields;
        }

        return ['nik', 'nik_ayah', 'nik_ibu', 'no_kk', 'nik_ktp'];
    }

    /**
     * Daftar field sensitif yang memiliki kolom blind index hash (_hash).
     *
     * @return array
     */
    public function getHashedFields(): array
    {
        if (property_exists($this, 'hashedFields') && is_array($this->hashedFields)) {
            return $this->hashedFields;
        }

        return ['nik', 'no_kk', 'nik_ktp'];
    }

    /**
     * Boot trait untuk memastikan sinkronisasi hash sebelum simpan.
     */
    public static function bootHasEncryptedSensitiveData(): void
    {
        static::saving(function ($model) {
            foreach ($model->getHashedFields() as $field) {
                if (array_key_exists($field, $model->attributes)) {
                    $raw = $model->attributes[$field];
                    $hashField = $field . '_hash';

                    if ($raw === null || $raw === '') {
                        $model->attributes[$field] = null;
                        $model->attributes[$hashField] = null;
                    }
                }
            }
        });
    }

    /**
     * Intercept getAttribute untuk mendekripsi field sensitif.
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->getEncryptedFields(), true) && !empty($value)) {
            try {
                return Crypt::decryptString($value);
            } catch (DecryptException $e) {
                // Fallback jika data masih plaintext lama sebelum migrasi
                return $value;
            } catch (\Throwable $e) {
                return $value;
            }
        }

        return $value;
    }

    /**
     * Intercept setAttribute untuk mengenkripsi field sensitif & mengisi hash.
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->getEncryptedFields(), true)) {
            $trimmed = is_string($value) ? trim($value) : $value;

            if ($trimmed === null || $trimmed === '') {
                $this->attributes[$key] = null;
                if (in_array($key, $this->getHashedFields(), true)) {
                    $this->attributes[$key . '_hash'] = null;
                }
                return $this;
            }

            // Enkripsi nilai plaintext menggunakan AES-256 (Crypt::encryptString)
            $this->attributes[$key] = Crypt::encryptString((string) $trimmed);

            // Simpan HMAC-SHA256 blind index hanya untuk kolom yang memiliki indeks hash
            if (in_array($key, $this->getHashedFields(), true)) {
                $this->attributes[$key . '_hash'] = hash_sensitive((string) $trimmed);
            }

            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Scope query untuk pencarian field terenkripsi via blind index hash.
     */
    public function scopeWhereEncrypted($query, string $field, ?string $value)
    {
        if ($value === null || $value === '') {
            return $query->whereNull($field . '_hash');
        }

        return $query->where($field . '_hash', hash_sensitive($value));
    }

    /**
     * Scope query OR untuk pencarian field terenkripsi via blind index hash.
     */
    public function scopeOrWhereEncrypted($query, string $field, ?string $value)
    {
        if ($value === null || $value === '') {
            return $query->orWhereNull($field . '_hash');
        }

        return $query->orWhere($field . '_hash', hash_sensitive($value));
    }

    /**
     * Scope query spesifik untuk NIK.
     */
    public function scopeWhereNik($query, ?string $nik)
    {
        return $this->scopeWhereEncrypted($query, 'nik', $nik);
    }

    /**
     * Scope query spesifik OR untuk NIK.
     */
    public function scopeOrWhereNik($query, ?string $nik)
    {
        return $this->scopeOrWhereEncrypted($query, 'nik', $nik);
    }

    /**
     * Scope query spesifik untuk No KK.
     */
    public function scopeWhereNoKk($query, ?string $noKk)
    {
        return $this->scopeWhereEncrypted($query, 'no_kk', $noKk);
    }

    /**
     * Scope query spesifik OR untuk No KK.
     */
    public function scopeOrWhereNoKk($query, ?string $noKk)
    {
        return $this->scopeOrWhereEncrypted($query, 'no_kk', $noKk);
    }

    /**
     * Accessor untuk NIK ter-masking (contoh: 3201************).
     */
    public function getMaskedNikAttribute(): string
    {
        return mask_nik($this->nik);
    }

    /**
     * Accessor untuk No KK ter-masking.
     */
    public function getMaskedNoKkAttribute(): string
    {
        return mask_kk($this->no_kk);
    }

    /**
     * Accessor untuk NIK Ayah ter-masking.
     */
    public function getMaskedNikAyahAttribute(): string
    {
        return mask_nik($this->nik_ayah);
    }

    /**
     * Accessor untuk NIK Ibu ter-masking.
     */
    public function getMaskedNikIbuAttribute(): string
    {
        return mask_nik($this->nik_ibu);
    }

    /**
     * Accessor untuk NIK KTP Nasabah ter-masking.
     */
    public function getMaskedNikKtpAttribute(): string
    {
        return mask_nik($this->nik_ktp);
    }
}
