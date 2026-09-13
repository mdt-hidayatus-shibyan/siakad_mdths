<?php

namespace App\Http\Requests\Keuangan;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
// use Illuminate\Validation\Rule;

class PengaturanTagihanRequest extends FormRequest
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
            'level_id'     => ['nullable', 'exists:levels,id'],
            'kode_tagihan' => ['required', 'string', 'max:10'],
            'nama_tagihan' => ['required', 'string', 'max:100'],
            'tipe'         => ['required', 'in:bulanan,semester,insidental'],
            'sasaran'      => ['nullable', 'in:murid,wali_murid'],
            'nominal'      => ['required', 'numeric', 'min:0'],
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
