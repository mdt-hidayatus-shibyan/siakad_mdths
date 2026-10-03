<?php

namespace App\Http\Requests\Ujian;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class PanitiaImniRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $panitiaId = $this->route('panitia_imni') ?? $this->route('panitia') ?? $this->route('id') ?? $this->id;
        if (is_object($panitiaId)) {
            $panitiaId = $panitiaId->id;
        }
        $tahunId = $this->input('tahun_pelajaran_id');

        return [
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajarans,id',
            'ustadz_id'          => [
                'required',
                'exists:ustadzs,id',
                Rule::unique('panitia_imnis', 'ustadz_id')
                    ->where('tahun_pelajaran_id', $tahunId)
                    ->ignore($panitiaId),
            ],
            'jabatan'            => 'required|in:Ketua,Bendahara,Anggota',
            'no_sk'              => 'nullable|string|max:100',
            'keterangan'         => 'nullable|string|max:255',
            'is_active'          => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'tahun_pelajaran_id.required' => 'Tahun Pelajaran wajib dipilih.',
            'ustadz_id.required'          => 'Ustadz wajib dipilih.',
            'ustadz_id.unique'            => 'Ustadz ini sudah terdaftar dalam kepanitiaan IMNI pada Tahun Pelajaran tersebut.',
            'jabatan.required'            => 'Jabatan wajib dipilih.',
            'jabatan.in'                  => 'Jabatan hanya boleh Ketua, Bendahara, atau Anggota.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson() || $this->ajax()) {
            throw new HttpResponseException(response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422));
        }

        parent::failedValidation($validator);
    }
}
