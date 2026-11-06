<?php

namespace App\Http\Requests\Warga;

use Illuminate\Foundation\Http\FormRequest;

class LaporanKerusakanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isWarga();
    }

    /**
     * Sanitasi input form sebelum validasi untuk mencegah stored XSS (NFR-02).
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('judul')) {
            $this->merge([
                'judul' => $this->sanitizeInput($this->input('judul')),
            ]);
        }

        if ($this->has('deskripsi_kondisi')) {
            $this->merge([
                'deskripsi_kondisi' => $this->sanitizeInput($this->input('deskripsi_kondisi')),
            ]);
        }
    }

    private function sanitizeInput(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        // Hapus elemen script & style beserta isinya
        $clean = preg_replace('#<script\b[^>]*>(.*?)<\/script>#is', '', $value);
        $clean = preg_replace('#<style\b[^>]*>(.*?)<\/style>#is', '', $clean);

        // Hapus seluruh tag HTML lainnya
        $clean = strip_tags($clean);

        return trim($clean);
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'judul' => ['required', 'string', 'max:150'],
            'kategori_id' => ['required', 'exists:kategori_infrastruktur,id'],
            'kelurahan_id' => ['nullable', 'exists:kelurahan,id'],
            'deskripsi_kondisi' => ['required', 'string', 'max:2000'],
            'latitude' => ['nullable', 'numeric', 'between:-7.05000000,-6.80000000'],
            'longitude' => ['nullable', 'numeric', 'between:107.50000000,107.80000000'],
            'foto_bukti_sebelum' => [
                $isUpdate ? 'nullable' : 'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:5120', // Maksimal 5MB sesuai standar keamanan NFR-02
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'judul.required' => 'Judul laporan kerusakan wajib diisi.',
            'judul.max' => 'Judul maksimal 150 karakter.',
            'kategori_id.required' => 'Kategori infrastruktur wajib dipilih.',
            'kategori_id.exists' => 'Kategori infrastruktur yang dipilih tidak valid.',
            'kelurahan_id.required' => 'Wilayah kelurahan wajib dipilih.',
            'kelurahan_id.exists' => 'Kelurahan yang dipilih tidak valid.',
            'deskripsi_kondisi.required' => 'Deskripsi kondisi kerusakan wajib diisi.',
            'deskripsi_kondisi.max' => 'Deskripsi maksimal 2000 karakter.',
            'latitude.required' => 'Titik koordinat latitude wajib ditentukan pada peta.',
            'latitude.between' => 'Koordinat berada di luar rentang administratif Kota Bandung.',
            'longitude.required' => 'Titik koordinat longitude wajib ditentukan pada peta.',
            'longitude.between' => 'Koordinat berada di luar rentang administratif Kota Bandung.',
            'foto_bukti_sebelum.required' => 'Foto bukti kondisi kerusakan wajib diunggah.',
            'foto_bukti_sebelum.image' => 'File bukti harus berupa gambar.',
            'foto_bukti_sebelum.mimes' => 'Format foto harus berupa JPG, JPEG, atau PNG.',
            'foto_bukti_sebelum.max' => 'Ukuran foto maksimal 5MB.',
        ];
    }
}
