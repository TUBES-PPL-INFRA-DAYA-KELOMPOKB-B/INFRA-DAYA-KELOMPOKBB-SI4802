<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KategoriInfrastrukturRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('tingkat_keparahan_default') && ! empty($this->input('tingkat_keparahan_default'))) {
            $this->merge([
                'bobot_prioritas' => \App\Models\KategoriInfrastruktur::getBobotFromTingkat((string) $this->input('tingkat_keparahan_default')),
            ]);
        } elseif ($this->has('bobot_prioritas') && ! empty($this->input('bobot_prioritas'))) {
            $this->merge([
                'tingkat_keparahan_default' => \App\Models\KategoriInfrastruktur::getTingkatFromBobot((int) $this->input('bobot_prioritas')),
            ]);
        }
    }

    public function rules(): array
    {
        $kategoriId = $this->route('kategori');
        if (is_object($kategoriId)) {
            $kategoriId = $kategoriId->id;
        }

        return [
            'nama_kategori' => [
                'required',
                'string',
                'max:100',
                Rule::unique('kategori_infrastruktur', 'nama_kategori')->ignore($kategoriId),
                function (string $attribute, mixed $value, \Closure $fail) use ($kategoriId): void {
                    $exists = \App\Models\KategoriInfrastruktur::query()
                        ->whereRaw('LOWER(nama_kategori) = ?', [mb_strtolower(trim((string) $value))])
                        ->when($kategoriId, fn ($q) => $q->where('id', '!=', $kategoriId))
                        ->exists();

                    if ($exists) {
                        $fail('Nama kategori sudah digunakan.');
                    }
                },
            ],
            'deskripsi' => ['nullable', 'string', 'max:500'],
            'tingkat_keparahan_default' => [
                'required',
                'string',
                Rule::in(['rendah', 'sedang', 'tinggi', 'kritis']),
            ],
            'bobot_prioritas' => ['nullable', 'integer', 'in:1,2,3,4'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique' => 'Nama kategori sudah digunakan.',
            'nama_kategori.max' => 'Nama kategori maksimal 100 karakter.',
            'deskripsi.max' => 'Deskripsi maksimal 500 karakter.',
            'tingkat_keparahan_default.required' => 'Tingkat keparahan default wajib dipilih.',
            'tingkat_keparahan_default.in' => 'Pilihan tingkat keparahan default tidak valid (rendah, sedang, tinggi, kritis).',
            'bobot_prioritas.required' => 'Bobot prioritas wajib dipilih.',
            'bobot_prioritas.in' => 'Pilihan bobot prioritas tidak valid (1: Rendah, 2: Sedang, 3: Tinggi, 4: Kritis).',
        ];
    }
}
