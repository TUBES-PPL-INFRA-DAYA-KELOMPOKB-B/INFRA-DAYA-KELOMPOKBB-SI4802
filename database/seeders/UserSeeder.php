<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        // 1 Admin DSDABM
        User::firstOrCreate(
            ['email' => 'admin@dsdabm.bandung.go.id'],
            [
                'name' => 'Admin DSDABM Kota Bandung',
                'phone' => '081122334455',
                'role' => 'admin',
                'is_active' => true,
                'password' => $password,
            ]
        );

        // 2 Petugas Lapangan
        User::firstOrCreate(
            ['email' => 'petugas1@dsdabm.bandung.go.id'],
            [
                'name' => 'Asep Sunandar (Petugas Jalan & Trotoar)',
                'phone' => '081234567891',
                'role' => 'petugas',
                'is_active' => true,
                'password' => $password,
            ]
        );

        User::firstOrCreate(
            ['email' => 'petugas2@dsdabm.bandung.go.id'],
            [
                'name' => 'Cecep Supriatna (Petugas Drainase & PJU)',
                'phone' => '081234567892',
                'role' => 'petugas',
                'is_active' => true,
                'password' => $password,
            ]
        );

        // 1 Pimpinan Eksekutif
        User::firstOrCreate(
            ['email' => 'pimpinan@dsdabm.bandung.go.id'],
            [
                'name' => 'Kepala Dinas DSDABM',
                'phone' => '081199887766',
                'role' => 'pimpinan',
                'is_active' => true,
                'password' => $password,
            ]
        );

        // 3 Warga Kota Bandung
        User::firstOrCreate(
            ['email' => 'warga@bandung.go.id'],
            [
                'name' => 'Warga Pelapor',
                'phone' => '085612340000',
                'role' => 'warga',
                'is_active' => true,
                'password' => $password,
            ]
        );

        User::firstOrCreate(
            ['email' => 'kelompok9@gmail.com'],
            [
                'name' => 'Kelompok 9 Admin',
                'phone' => '081298765432',
                'role' => 'admin',
                'is_active' => true,
                'password' => $password,
            ]
        );

        User::firstOrCreate(
            ['email' => 'warga1@gmail.com'],
            [
                'name' => 'Daffa Pratama (Warga Coblong)',
                'phone' => '085612341111',
                'role' => 'warga',
                'is_active' => true,
                'password' => $password,
            ]
        );

        User::firstOrCreate(
            ['email' => 'warga2@gmail.com'],
            [
                'name' => 'Siti Nurhaliza (Warga Lengkong)',
                'phone' => '085612342222',
                'role' => 'warga',
                'is_active' => true,
                'password' => $password,
            ]
        );
    }
}
