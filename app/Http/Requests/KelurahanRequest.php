<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KelurahanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        $kelurahanId = $this->route('kelurahan')?->id ?? $this->route('kelurahan');

        return [
            'kecamatan_id' => [
                'required',
                'exists:kecamatan,id',
            ],
            'kode_kelurahan' => [
                'required',
                'string',
                'max:10',
                Rule::unique('kelurahan', 'kode_kelurahan')->ignore($kelurahanId),
            ],
            'nama_kelurahan' => [
                'required',
                'string',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'kecamatan_id.required' => 'Pilihan kecamatan wajib dipilih.',
            'kecamatan_id.exists' => 'Kecamatan yang dipilih tidak valid.',
            'kode_kelurahan.required' => 'Kode kelurahan wajib diisi.',
            'kode_kelurahan.unique' => 'Kode kelurahan tersebut sudah terdaftar.',
            'kode_kelurahan.max' => 'Kode kelurahan maksimal 10 karakter.',
            'nama_kelurahan.required' => 'Nama kelurahan wajib diisi.',
            'nama_kelurahan.max' => 'Nama kelurahan maksimal 100 karakter.',
        ];
    }
}
