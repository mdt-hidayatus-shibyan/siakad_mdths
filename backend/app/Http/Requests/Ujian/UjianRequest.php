<?php

namespace App\Http\Requests\Ujian;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UjianRequest extends FormRequest
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
        return [
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajarans,id',
            'nama_ujian'         => 'required|string|max:255',
            'semester_id'        => 'required|exists:semesters,id', // Validasi ke tabel semesters
            'tingkat_id'         => 'nullable|exists:tingkats,id', // Sasaran Tingkat (Opsional/Global)
            'tipe_ujian'         => 'required|in:IMDA 1,IMDA 2,IMDA 3', // Khusus Ujian IMDA
            'tanggal_mulai'      => 'nullable|date',
            'tanggal_selesai'    => 'nullable|date|after_or_equal:tanggal_mulai',
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
