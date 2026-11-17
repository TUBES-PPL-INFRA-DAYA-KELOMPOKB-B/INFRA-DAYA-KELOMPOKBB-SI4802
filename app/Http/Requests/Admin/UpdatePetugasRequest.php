<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePetugasRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Hanya admin DSDABM yang terautentikasi dan aktif (AC17-01).
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin() && auth()->user()->is_active;
    }

    /**
     * Get the validation rules that apply to the request.
     * Edit identitas hanya boleh memvalidasi nama, email, dan telepon (AC17-11).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $petugasId = $this->route('id') ?? $this->route('petuga');
        if (is_object($petugasId)) {
            $petugasId = $petugasId->id;
        }

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/\S+/',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($petugasId),
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^\+?[0-9]{8,20}$/',
            ],
        ];
    }

    /**
     * Get the custom validation error messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap petugas wajib diisi.',
            'name.regex' => 'Nama tidak boleh hanya berisi spasi atau karakter kosong.',
            'name.max' => 'Nama lengkap maksimal 100 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email sudah terdaftar untuk pengguna lain.',
            'phone.required' => 'Nomor telepon petugas wajib diisi.',
            'phone.regex' => 'Format nomor telepon tidak valid. Gunakan format angka dengan awalan 0 atau + (8-20 digit).',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
        ];
    }
}

