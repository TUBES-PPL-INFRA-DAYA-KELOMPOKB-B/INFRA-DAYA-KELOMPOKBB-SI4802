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
     * Get the validation rules that apply to the request (FR-21.2).
     * Minimal satu foto hasil perbaikan wajib ada (baik dari berkas yang diunggah saat ini atau yang sudah tersimpan sebelumnya).
     */
    public function rules(): array
    {
        $disposisiId = $this->route('id');
        $hasExistingPhoto = false;

        if ($disposisiId) {
            $disposisi = \App\Models\DisposisiPenugasan::with('hasilPerbaikan')->find($disposisiId);
            $hasExistingPhoto = ! empty($disposisi?->hasilPerbaikan?->foto_bukti_sesudah) || ! empty($disposisi?->foto_bukti_sesudah);
        }

        return [
            'foto_bukti_sesudah' => [$hasExistingPhoto ? 'nullable' : 'required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
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
