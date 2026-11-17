<x-admin-layout title="Edit Identitas Petugas Lapangan">
    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Breadcrumb / Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <div>
                <a href="{{ route('admin.petugas.show', $petugas->id) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#0E7C86] hover:text-[#0b636b] mb-1">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span>Kembali ke Detail Petugas</span>
                </a>
                <h1 class="text-2xl font-extrabold text-[#1F3A5F] tracking-tight">Edit Identitas Petugas</h1>
                <p class="text-sm text-slate-500 mt-1">Perbarui data nama, email, atau nomor telepon petugas <span class="font-bold text-slate-700">{{ $petugas->name }}</span>.</p>
            </div>
        </div>

        <x-flash />

        <!-- Form Card (AC17-08, AC17-09, AC17-11, AC17-21) -->
        <x-card class="p-6 sm:p-8">
            <form method="POST" action="{{ route('admin.petugas.update', $petugas->id) }}" class="space-y-6" dusk="form-edit-petugas">
                @csrf
                @method('PUT')

                <!-- Guardrails Notice (AC17-11, AC17-21) -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-start gap-3 text-slate-600 text-xs">
                    <svg class="h-5 w-5 text-slate-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="space-y-1">
                        <p class="font-bold text-slate-800">Batasan Pembaruan Identitas:</p>
                        <p>Sesuai aturan keamanan sistem, pembaruan data hanya diizinkan untuk <span class="font-semibold text-slate-700">Nama, Email, dan Telepon</span>. Password, role pengguna, status keaktifan akun, serta riwayat penugasan tidak dapat diubah melalui formulir ini.</p>
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
                        value="{{ old('name', $petugas->name) }}"
                        class="w-full px-4 py-2.5 text-sm bg-white border @error('name') border-rose-300 ring-1 ring-rose-300 @else border-slate-200 @enderror rounded-xl focus:border-[#0E7C86] focus:ring-2 focus:ring-[#0E7C86]/20 transition-all text-slate-800"
                        required
                    >
                    @error('name')
                        <p class="text-xs text-rose-600 mt-1" dusk="error-name">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email (AC17-08: Own email allowed) -->
                <div class="space-y-1.5">
                    <label for="email" class="block text-sm font-bold text-slate-700">
                        Alamat Email Akun <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        dusk="input-email"
                        value="{{ old('email', $petugas->email) }}"
                        class="w-full px-4 py-2.5 text-sm bg-white border @error('email') border-rose-300 ring-1 ring-rose-300 @else border-slate-200 @enderror rounded-xl focus:border-[#0E7C86] focus:ring-2 focus:ring-[#0E7C86]/20 transition-all text-slate-800"
                        required
                    >
                    <p class="text-xs text-slate-400">Email harus unik lintas seluruh pengguna, namun akun ini diperbolehkan mempertahankan alamat email miliknya sendiri.</p>
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
                        value="{{ old('phone', $petugas->phone) }}"
                        class="w-full px-4 py-2.5 text-sm bg-white border @error('phone') border-rose-300 ring-1 ring-rose-300 @else border-slate-200 @enderror rounded-xl focus:border-[#0E7C86] focus:ring-2 focus:ring-[#0E7C86]/20 transition-all text-slate-800"
                        required
                    >
                    <p class="text-xs text-slate-400">Disimpan sebagai teks dengan mempertahankan format nol awal (0) atau tanda tambah (+).</p>
                    @error('phone')
                        <p class="text-xs text-rose-600 mt-1" dusk="error-phone">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Metadata Baca-Saja (Role & Status) -->
                <div class="grid grid-cols-2 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Role Pengguna</span>
                        <p class="text-sm font-bold text-[#1F3A5F] mt-0.5 capitalize">{{ $petugas->role }}</p>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Status Keaktifan</span>
                        <p class="text-sm font-bold mt-0.5 {{ $petugas->is_active ? 'text-emerald-600' : 'text-slate-600' }}">
                            {{ $petugas->is_active ? 'Aktif' : 'Nonaktif' }}
                        </p>
                    </div>
                </div>

                <!-- Form Action Buttons (AC17-19: Batal tidak mengubah data) -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200">
                    <a
                        href="{{ route('admin.petugas.show', $petugas->id) }}"
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
                        Perbarui Identitas
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-admin-layout>

