<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueEncrypted implements ValidationRule
{
    protected string $table;
    protected string $column;
    protected mixed $ignoreId;
    protected string $idColumn;
    protected ?string $customMessage;

    /**
     * @param string $table Nama tabel database
     * @param string $column Nama kolom hash (contoh: 'nik_hash', 'no_kk_hash')
     * @param mixed $ignoreId ID record yang diabaikan saat edit/update
     * @param string $idColumn Nama kolom primary key
     * @param string|null $customMessage Pesan error kustom
     */
    public function __construct(
        string $table,
        string $column = 'nik_hash',
        mixed $ignoreId = null,
        string $idColumn = 'id',
        ?string $customMessage = null
    ) {
        $this->table = $table;
        $this->column = $column;
        $this->ignoreId = $ignoreId;
        $this->idColumn = $idColumn;
        $this->customMessage = $customMessage;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || trim((string) $value) === '') {
            return;
        }

        $hash = hash_sensitive(trim((string) $value));

        $query = DB::table($this->table)->where($this->column, $hash);

        if ($this->ignoreId !== null) {
            $query->where($this->idColumn, '!=', $this->ignoreId);
        }

        if ($query->exists()) {
            if ($this->customMessage) {
                $fail($this->customMessage);
            } else {
                $namaField = strtoupper(str_replace('_', ' ', $attribute));
                $fail("{$namaField} sudah terdaftar di sistem.");
            }
        }
    }
}
