<?php

namespace App\Http\Requests\UjianAlquran;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;


class UjianAlquranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajarans,id',
            'nama_ujian'         => 'required|string|max:255',
            'tanggal_ujian'      => 'required|date',
            'kkm_kelulusan'      => 'required|numeric|min:0|max:100',
            'bobot_jali'         => 'required|numeric|min:0|max:100',
            'bobot_khofi'        => 'required|numeric|min:0|max:100',
            'keterangan'         => 'nullable|string',
            'status'             => 'nullable|in:Draft,Draf,Berjalan,Selesai',
        ];
    }

    public function attributes(): array
    {
        return [
            'tahun_pelajaran_id' => 'Tahun Pelajaran',
            'nama_ujian'         => 'Nama Agenda Ujian',
            'tanggal_ujian'      => 'Tanggal Pelaksanaan',
            'kkm_kelulusan'      => 'Batas Minimal Lulus (KKM)',
            'bobot_jali'         => 'Bobot Minus Khotho\' Jali',
            'bobot_khofi'        => 'Bobot Minus Khotho\' Khofi',
            'keterangan'         => 'Keterangan',
            'status'             => 'Status Pelaksanaan',
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
