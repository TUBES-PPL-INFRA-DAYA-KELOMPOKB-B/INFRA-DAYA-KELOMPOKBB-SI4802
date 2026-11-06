<?php

namespace App\Http\Requests\Petugas;

use Illuminate\Foundation\Http\FormRequest;

class SelesaikanTugasRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isPetugas();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'foto_bukti_sesudah' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'catatan_perbaikan' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'foto_bukti_sesudah' => 'foto bukti perbaikan (sesudah)',
            'catatan_perbaikan' => 'catatan teknis perbaikan',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'foto_bukti_sesudah.required' => 'Wajib mengunggah foto bukti fisik hasil perbaikan infrastruktur.',
            'foto_bukti_sesudah.image' => 'Berkas bukti perbaikan harus berupa format gambar valid.',
            'foto_bukti_sesudah.mimes' => 'Format foto bukti yang diizinkan hanya JPG, JPEG, atau PNG.',
            'foto_bukti_sesudah.max' => 'Ukuran foto bukti tidak boleh melebihi 5 MB.',
            'catatan_perbaikan.required' => 'Catatan teknis perbaikan wajib diisi.',
            'catatan_perbaikan.min' => 'Catatan teknis perbaikan minimal berisi :min karakter penjelasan pekerjaan.',
            'catatan_perbaikan.max' => 'Catatan teknis perbaikan maksimal :max karakter.',
        ];
    }
}
