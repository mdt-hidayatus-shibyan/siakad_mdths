<?php

namespace App\Http\Requests\Akademik;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
// use Illuminate\Validation\Rule;

class AgendaRequest extends FormRequest
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
        $rules = [
            'jenis_agenda'       => 'required|in:libur,ujian,kegiatan',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajarans,id',
            'nama_agenda'        => 'required|string|max:255',
            'tanggal_mulai'      => 'required|date',
            'tanggal_selesai'    => 'required|date|after_or_equal:tanggal_mulai',
        ];

        // 2. Validasi Kondisional berdasarkan jenis agenda
        if ($this->jenis_agenda === 'libur') {
            $rules['tipe_libur'] = 'nullable|in:Seharian,Sebagian Jam';
            $rules['jam_ke']     = 'nullable|array';
            $rules['jam_ke.*']   = 'string';
            $rules['ruangan_id'] = 'nullable|exists:ruangans,id';
            $rules['level_id']   = 'nullable|exists:levels,id';
        } elseif ($this->jenis_agenda === 'ujian') {
            $rules['semester_id'] = 'required|exists:semesters,id';
            $rules['tingkat_id']  = 'nullable|exists:tingkats,id';
            $rules['tipe_ujian']  = 'required|in:IMDA 1,IMDA 2,IMDA 3,IMNI';
        } elseif ($this->jenis_agenda === 'kegiatan') {
            $rules['kategori_kegiatan_id'] = 'required|exists:kategori_kegiatans,id';
            $rules['tipe_presensi']        = 'nullable|in:tidak_ada,harian,multi_sesi';
            $rules['sesi_kegiatan']        = 'nullable|array';
            $rules['sesi_kegiatan.*']      = 'string';
            $rules['keterangan']           = 'nullable|string|max:500';
        }

        return $rules;
    }
    public function attributes(): array
    {
        return [
            'jenis_agenda'         => 'jenis agenda',
            'tahun_pelajaran_id'   => 'tahun pelajaran',
            'nama_agenda'          => 'nama agenda atau keterangan',
            'tanggal_mulai'        => 'tanggal mulai',
            'tanggal_selesai'      => 'tanggal selesai',
            'semester_id'          => 'semester',
            'tipe_ujian'           => 'tipe ujian',
            'kategori_kegiatan_id' => 'kategori kegiatan',
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
