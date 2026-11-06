<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KecamatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        $kecamatanId = $this->route('kecamatan')?->id ?? $this->route('kecamatan');

        return [
            'kode_kecamatan' => [
                'required',
                'string',
                'max:10',
                Rule::unique('kecamatan', 'kode_kecamatan')->ignore($kecamatanId),
            ],
            'nama_kecamatan' => [
                'required',
                'string',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'kode_kecamatan.required' => 'Kode kecamatan wajib diisi.',
            'kode_kecamatan.unique' => 'Kode kecamatan tersebut sudah terdaftar.',
            'kode_kecamatan.max' => 'Kode kecamatan maksimal 10 karakter.',
            'nama_kecamatan.required' => 'Nama kecamatan wajib diisi.',
            'nama_kecamatan.max' => 'Nama kecamatan maksimal 100 karakter.',
        ];
    }
}
