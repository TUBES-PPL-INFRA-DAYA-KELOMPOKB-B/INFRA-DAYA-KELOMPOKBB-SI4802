<?php

namespace App\Http\Requests\Warga;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUmpanBalikRequest extends FormRequest
{
    /**
     * Tentukan otorisasi permohonan.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'warga';
    }

    /**
     * Aturan validasi request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'komentar' => ['nullable', 'string', 'max:1000'],
            'kecepatan_penanganan' => ['nullable', 'integer', 'between:1,5'],
            'kualitas_perbaikan' => ['nullable', 'integer', 'between:1,5'],
        ];
    }

    /**
     * Pesan validasi kustom berbahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rating.required' => 'Silakan pilih bintang penilaian kepuasan (1-5).',
            'rating.integer' => 'Nilai rating harus berupa bilangan bulat.',
            'rating.between' => 'Rating kepuasan harus bernilai antara 1 sampai 5 bintang.',
            'komentar.max' => 'Ulasan saran maksimal 1000 karakter.',
            'kecepatan_penanganan.between' => 'Nilai kecepatan penanganan harus antara 1 sampai 5 bintang.',
            'kualitas_perbaikan.between' => 'Nilai kualitas perbaikan harus antara 1 sampai 5 bintang.',
        ];
    }
}
