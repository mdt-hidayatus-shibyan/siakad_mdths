<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MuridRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $muridId = $this->route('murid') ?? $this->route('id') ?? $this->id;
        if (is_object($muridId)) $muridId = $muridId->id;

        return [
            // Relasi ke KK / Penanggung Jawab
            'wali_murid_id'  => 'required|exists:wali_murids,id',

            // Data Identitas & Negara
            'nism'           => ['required', 'string', 'max:20', Rule::unique('murids', 'nism')->ignore($muridId)],
            'nisn'           => ['nullable', 'string', 'max:20', Rule::unique('murids', 'nisn')->ignore($muridId)],
            'nik'            => ['nullable', 'string', 'size:16', new \App\Rules\UniqueEncrypted('murids', 'nik_hash', $muridId)],

            // Data Pribadi
            'nama_lengkap'   => 'required|string|max:100',
            'nama_panggilan' => 'nullable|string|max:50',
            'jenis_kelamin'  => 'required|in:L,P',
            'tempat_lahir'   => 'nullable|string|max:50',
            'tanggal_lahir'  => 'nullable|date',
            'anak_ke'        => 'nullable|integer|min:1',
            'hub_kel'        => 'required|in:Anak Kandung,Anak Tiri,Anak Angkat,Cucu,Lainnya',

            // --- DATA ORANG TUA KANDUNG (BARU) ---
            'nik_ayah'       => 'nullable|string|size:16', // TIDAK unique agar saudara kandung bisa daftar
            'nama_ayah'      => 'nullable|string|max:100',
            'status_ayah'    => 'required|in:Hidup,Meninggal',

            'nik_ibu'        => 'nullable|string|size:16', // TIDAK unique
            'nama_ibu'       => 'nullable|string|max:100',
            'status_ibu'     => 'required|in:Hidup,Meninggal',

            // Status & Berkas
            'status'         => 'required|in:Aktif,Lulus,Pindah,Berhenti,Meninggal',
            'foto'           => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            'tahun_masuk'         => 'required',
            'level_masuk'         => 'required',
        ];
    }
}
