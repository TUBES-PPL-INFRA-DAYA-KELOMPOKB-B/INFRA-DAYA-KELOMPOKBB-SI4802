<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StorePetugasRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
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
                'unique:users,email',
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^\+?[0-9]{8,20}$/',
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8),
            ],
            'password_confirmation' => [
                'required',
                'string',
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
            'email.unique' => 'Alamat email sudah terdaftar dalam sistem.',
            'phone.required' => 'Nomor telepon petugas wajib diisi.',
            'phone.regex' => 'Format nomor telepon tidak valid. Gunakan format angka dengan awalan 0 atau + (8-20 digit).',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'password.required' => 'Password akun wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok dengan password yang dimasukkan.',
            'password.min' => 'Password minimal terdiri dari :min karakter.',
            'password_confirmation.required' => 'Konfirmasi password wajib diisi.',
        ];
    }
}

