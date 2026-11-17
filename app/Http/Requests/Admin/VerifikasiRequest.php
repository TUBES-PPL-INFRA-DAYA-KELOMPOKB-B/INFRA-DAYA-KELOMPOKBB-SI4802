<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class VerifikasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        // Setujui / Terima Aduan
        if ($this->routeIs('admin.verifikasi.setujui')) {
            return [
                'prioritas' => ['nullable', 'in:rendah,sedang,tinggi'],
                'catatan_verifikator' => ['nullable', 'string', 'max:1000'],
            ];
        }

        // Prioritas mandiri
        if ($this->routeIs('admin.verifikasi.prioritas')) {
            return [
                'prioritas' => ['required', 'in:rendah,sedang,tinggi'],
            ];
        }

        // admin.verifikasi.tolak
        return [
            'alasan_penolakan' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'prioritas.required' => 'Tingkat prioritas penanganan wajib dipilih.',
            'prioritas.in' => 'Prioritas harus salah satu dari: rendah, sedang, atau tinggi.',
            'catatan_verifikator.max' => 'Catatan verifikator maksimal 1000 karakter.',
            'alasan_penolakan.required' => 'Alasan penolakan wajib diisi agar warga dapat memahami.',
            'alasan_penolakan.min' => 'Alasan penolakan minimal 10 karakter agar informatif.',
            'alasan_penolakan.max' => 'Alasan penolakan maksimal 1000 karakter.',
        ];
    }
}
