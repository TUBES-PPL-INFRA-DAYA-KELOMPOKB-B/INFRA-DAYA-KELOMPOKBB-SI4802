<?php

namespace App\Http\Requests\Petugas;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProgresRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isPetugas();
    }

    /**
     * Get the validation rules that apply to the request (FR-20.2, FR-20.3).
     */
    public function rules(): array
    {
        return [
            'status_penugasan' => ['nullable', 'string', 'in:diterima,sedang_dikerjakan,dalam_pengerjaan'],
            'catatan_perbaikan' => ['nullable', 'string', 'max:2000'],
            'foto_bukti_sesudah' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'status_penugasan' => 'status pengerjaan',
            'catatan_perbaikan' => 'catatan progres perbaikan',
            'foto_bukti_sesudah' => 'foto hasil perbaikan',
        ];
    }

    /**
     * Get custom messages for validator errors (FR-20.4).
     */
    public function messages(): array
    {
        return [
            'foto_bukti_sesudah.image' => 'Berkas harus berupa gambar yang valid.',
            'foto_bukti_sesudah.mimes' => 'Format foto yang diizinkan hanya .jpg, .jpeg, atau .png.',
            'foto_bukti_sesudah.max' => 'Ukuran berkas foto maksimal 5 MB.',
            'status_penugasan.in' => 'Status pengerjaan yang dipilih tidak valid.',
        ];
    }
}

