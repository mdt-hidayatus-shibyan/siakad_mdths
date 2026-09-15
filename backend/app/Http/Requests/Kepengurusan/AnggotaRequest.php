<?php

namespace App\Http\Requests\Kepengurusan;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class AnggotaRequest extends FormRequest
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
        $anggotaId = $this->route('anggota') ?? $this->route('id') ?? $this->id;
        if (is_object($anggotaId)) $anggotaId = $anggotaId->id;
        return [
            'ustadz_id'     => 'nullable|exists:ustadzs,id',
            'nik'           => [
                'nullable',
                'string',
                'max:16',
                new \App\Rules\UniqueEncrypted('anggota', 'nik_hash', $anggotaId)
            ],
            'nama_lengkap'  => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir'  => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'no_hp'         => 'nullable|string|max:20',
            'alamat'        => 'nullable|string',

            // Validasi khusus untuk file gambar (Maks 2MB)
            'foto'          => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'tanda_tangan'  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Data tidak valid',
            'errors'  => $validator->errors()
        ], 422));
    }
}
