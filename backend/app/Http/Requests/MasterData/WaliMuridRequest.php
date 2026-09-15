<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WaliMuridRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $waliId = $this->route('wali_murid') ?? $this->route('wali-murid') ?? $this->route('id') ?? $this->id;
        if (is_object($waliId)) $waliId = $waliId->id;

        return [
            // Ubah dari 'required' menjadi 'nullable'
            'no_kk'                => ['nullable', 'string', 'size:16', new \App\Rules\UniqueEncrypted('wali_murids', 'no_kk_hash', $waliId)],
            'kepala_keluarga'      => 'required|in:Ayah,Ibu,Wali',
            'nama_kepala_keluarga' => 'required|string|max:100',
            'no_hp'                => 'nullable|string|max:15',
            'kampung_id'           => 'required|exists:kampungs,id',
            'alamat_detail'        => 'nullable|string|max:500'
        ];
    }
}
