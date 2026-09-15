<?php

namespace App\Http\Requests\MasterData;

use App\Models\Ustadz;
use Illuminate\Foundation\Http\FormRequest;

class UstadzRequest extends FormRequest
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
        $ustadzId = $this->route('ustadz') ?? $this->route('id') ?? $this->id;
        if (is_object($ustadzId)) $ustadzId = $ustadzId->id;
        $userId = null;
        if ($ustadzId) {
            $ustadz = Ustadz::find($ustadzId);
            $userId = $ustadz ? $ustadz->user_id : null;
        }

        if ($this->input('form_type') === 'akun') {
            return [
                'username'  => ['nullable', 'string', 'max:50', \Illuminate\Validation\Rule::unique('users', 'username')->ignore($userId)],
                'email'     => ['nullable', 'email', 'max:100', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($userId)],
                'is_active' => 'nullable|boolean',
                'form_type' => 'required|in:akun,profil',
            ];
        }

        return [
            'nama_lengkap'         => 'required|string|max:100',
            'nigm'                 => ['nullable', 'string', 'max:30', \Illuminate\Validation\Rule::unique('ustadzs', 'nigm')->ignore($ustadzId)],
            'nik'                  => ['nullable', 'string', 'size:16', new \App\Rules\UniqueEncrypted('ustadzs', 'nik_hash', $ustadzId)],
            'jenis_kelamin'        => 'required|in:L,P',
            'tempat_lahir'         => 'nullable|string|max:50',
            'tanggal_lahir'        => 'nullable|date',
            'alamat'               => 'nullable|string|max:500',
            'no_hp'                => 'nullable|string|max:15',
            'tahun_mulai_mengajar' => 'nullable|digits:4|integer|min:1980|max:' . (date('Y') + 1),
            'is_active'            => 'nullable|boolean',
            // Validasi File
            'foto'                 => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'tanda_tangan'         => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'username'             => ['nullable', 'string', 'max:50', \Illuminate\Validation\Rule::unique('users', 'username')->ignore($userId)],
            'email'                => ['nullable', 'email', 'max:100', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($userId)],
            'form_type'            => 'nullable|in:akun,profil',
        ];
    }
}
