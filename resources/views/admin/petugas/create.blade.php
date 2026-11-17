<x-admin-layout title="Tambah Petugas Lapangan Baru">
    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Breadcrumb / Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <div>
                <a href="{{ route('admin.petugas.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#0E7C86] hover:text-[#0b636b] mb-1">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span>Kembali ke Daftar Petugas</span>
                </a>
                <h1 class="text-2xl font-extrabold text-[#1F3A5F] tracking-tight">Tambah Petugas Lapangan</h1>
                <p class="text-sm text-slate-500 mt-1">Daftarkan akun petugas teknis baru untuk penugasan penanganan laporan kerusakan di lapangan.</p>
            </div>
        </div>

        <x-flash />

        <!-- Form Card (AC17-06, AC17-07, AC17-08, AC17-09, AC17-10) -->
        <x-card class="p-6 sm:p-8">
            <form method="POST" action="{{ route('admin.petugas.store') }}" class="space-y-6" dusk="form-create-petugas">
                @csrf

                <!-- Role & Status Notice (AC17-06) -->
                <div class="p-4 rounded-xl bg-sky-50/80 border border-sky-100 flex items-start gap-3 text-sky-900 text-xs">
                    <svg class="h-5 w-5 text-sky-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="space-y-1">
                        <p class="font-bold">Konfigurasi Akses Ditetapkan Server:</p>
                        <p>Akun yang didaftarkan melalui formulir ini secara otomatis diberikan hak akses <span class="font-semibold underline">Role: Petugas</span> dan berstatus <span class="font-semibold underline">Aktif</span> sehingga dapat langsung menerima disposisi tugas.</p>
                    </div>
                </div>

                <!-- Nama Lengkap -->
                <div class="space-y-1.5">
                    <label for="name" class="block text-sm font-bold text-slate-700">
                        Nama Lengkap Petugas <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        dusk="input-name"
                        value="{{ old('name') }}"
                        placeholder="Contoh: Budi Santoso"
                        class="w-full px-4 py-2.5 text-sm bg-white border @error('name') border-rose-300 ring-1 ring-rose-300 @else border-slate-200 @enderror rounded-xl focus:border-[#0E7C86] focus:ring-2 focus:ring-[#0E7C86]/20 transition-all text-slate-800"
                        required
                    >
                    @error('name')
                        <p class="text-xs text-rose-600 mt-1" dusk="error-name">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="space-y-1.5">
                    <label for="email" class="block text-sm font-bold text-slate-700">
                        Alamat Email Akun <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        dusk="input-email"
                        value="{{ old('email') }}"
                        placeholder="Contoh: budi.petugas@bandung.go.id"
                        class="w-full px-4 py-2.5 text-sm bg-white border @error('email') border-rose-300 ring-1 ring-rose-300 @else border-slate-200 @enderror rounded-xl focus:border-[#0E7C86] focus:ring-2 focus:ring-[#0E7C86]/20 transition-all text-slate-800"
                        required
                    >
                    <p class="text-xs text-slate-400">Email harus valid dan unik lintas semua role akun pengguna (AC17-08).</p>
                    @error('email')
                        <p class="text-xs text-rose-600 mt-1" dusk="error-email">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nomor Telepon (AC17-09) -->
                <div class="space-y-1.5">
                    <label for="phone" class="block text-sm font-bold text-slate-700">
                        Nomor Telepon / WhatsApp <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="phone"
                        id="phone"
                        dusk="input-phone"
                        value="{{ old('phone') }}"
                        placeholder="Contoh: 081234567890 atau +628123456789"
                        class="w-full px-4 py-2.5 text-sm bg-white border @error('phone') border-rose-300 ring-1 ring-rose-300 @else border-slate-200 @enderror rounded-xl focus:border-[#0E7C86] focus:ring-2 focus:ring-[#0E7C86]/20 transition-all text-slate-800"
                        required
                    >
                    <p class="text-xs text-slate-400">Disimpan sebagai teks dengan mempertahankan format nol awal (0) atau tanda tambah (+).</p>
                    @error('phone')
                        <p class="text-xs text-rose-600 mt-1" dusk="error-phone">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password & Confirmation (AC17-10) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                    <div class="space-y-1.5">
                        <label for="password" class="block text-sm font-bold text-slate-700">
                            Password Akun <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            dusk="input-password"
                            placeholder="Minimal 8 karakter"
                            class="w-full px-4 py-2.5 text-sm bg-white border @error('password') border-rose-300 ring-1 ring-rose-300 @else border-slate-200 @enderror rounded-xl focus:border-[#0E7C86] focus:ring-2 focus:ring-[#0E7C86]/20 transition-all text-slate-800"
                            required
                        >
                        @error('password')
                            <p class="text-xs text-rose-600 mt-1" dusk="error-password">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="password_confirmation" class="block text-sm font-bold text-slate-700">
                            Konfirmasi Password <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            dusk="input-password-confirmation"
                            placeholder="Ulangi password di atas"
                            class="w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:border-[#0E7C86] focus:ring-2 focus:ring-[#0E7C86]/20 transition-all text-slate-800"
                            required
                        >
                    </div>
                </div>

                <!-- Form Action Buttons (AC17-19: Batal tidak mengubah data) -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200">
                    <a
                        href="{{ route('admin.petugas.index') }}"
                        dusk="btn-cancel"
                        class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium rounded-xl transition-all"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        dusk="btn-submit"
                        class="px-6 py-2.5 bg-[#0E7C86] hover:bg-[#0b636b] text-white text-sm font-bold rounded-xl shadow-sm transition-all"
                    >
                        Simpan Akun Petugas
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-admin-layout>

