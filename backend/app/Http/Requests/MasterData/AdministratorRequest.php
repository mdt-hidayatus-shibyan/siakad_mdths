<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdministratorRequest extends FormRequest
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
        $administratorId = $this->route('administrator') ?? $this->route('id') ?? $this->id;
        if (is_object($administratorId)) $administratorId = $administratorId->id;
        $userId = null;
        if ($administratorId) {
            $administrator = \App\Models\Administrator::find($administratorId);
            $userId = $administrator ? $administrator->user_id : null;
        }

        if ($this->input('form_type') === 'akun') {
            return [
                'username'   => ['nullable', 'string', 'max:50', Rule::unique('users', 'username')->ignore($userId)],
                'email'      => ['nullable', 'email', 'max:100', Rule::unique('users', 'email')->ignore($userId)],
                'is_active'  => 'nullable|boolean',
                'form_type'  => 'required|in:akun,profil',
            ];
        }

        return [
            'nama_lengkap'         => 'required|string|max:100',
            'nik'                  => ['required', 'string', 'size:16', Rule::unique('administrators', 'nik')->ignore($administratorId)],
            'jenis_kelamin'        => 'required|in:L,P',
            'tempat_lahir'         => 'required|string|max:50',
            'tanggal_lahir'        => 'required|date',
            'alamat'               => 'required|string|max:500',
            'no_hp'                => 'required|string|max:15',
            'role'                 => 'nullable|string|in:administrator,petugas-tabungan,petugas-koperasi,staff',
            'tingkat_id'           => 'nullable|required_if:role,staff',
            'is_active'            => 'nullable|boolean',
            'foto'                 => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'tanda_tangan'         => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'username'             => ['nullable', 'string', 'max:50', Rule::unique('users', 'username')->ignore($userId)],
            'email'                => ['nullable', 'email', 'max:100', Rule::unique('users', 'email')->ignore($userId)],
            'form_type'            => 'nullable|in:akun,profil',
        ];
    }

    public function messages(): array
    {
        return [
            'tingkat_id.required_if' => 'Area Tingkat wajib dipilih jika Peran / Role adalah Staff.',
            'nik.required'           => 'NIK wajib diisi 16 digit.',
            'nik.size'               => 'NIK harus berjumlah 16 digit.',
            'nik.unique'             => 'NIK ini sudah terdaftar.',
            'nama_lengkap.required'  => 'Nama lengkap wajib diisi.',
        ];
    }
}
