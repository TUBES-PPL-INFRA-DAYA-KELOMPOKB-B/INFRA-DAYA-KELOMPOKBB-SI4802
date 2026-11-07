<x-admin-layout title="Ubah Pengguna">
    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between pb-2 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-[#1F3A5F] tracking-tight">Ubah Data Pengguna</h1>
                <p class="text-sm text-slate-500 mt-1">Perbarui informasi profil, hak akses peran, atau password.</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                Kembali
            </a>
        </div>

        <!-- Form Card -->
        <x-card class="p-6">
            <form method="POST" action="{{ route('admin.users.update', $user->id) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name', $user->name) }}"
                        required
                        class="w-full px-3.5 py-2.5 border rounded-xl text-sm transition-all focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent @error('name') border-rose-300 bg-rose-50/30 @else border-slate-300 @enderror"
                    >
                    @error('name')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat Email <span class="text-rose-500">*</span></label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email', $user->email) }}"
                        required
                        class="w-full px-3.5 py-2.5 border rounded-xl text-sm transition-all focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent @error('email') border-rose-300 bg-rose-50/30 @else border-slate-300 @enderror"
                    >
                    @error('email')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-slate-700 mb-1.5">Nomor Telepon / WhatsApp</label>
                        <input
                            type="text"
                            name="phone"
                            id="phone"
                            value="{{ old('phone', $user->phone) }}"
                            class="w-full px-3.5 py-2.5 border rounded-xl text-sm transition-all focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent @error('phone') border-rose-300 bg-rose-50/30 @else border-slate-300 @enderror"
                        >
                        @error('phone')
                            <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-semibold text-slate-700 mb-1.5">Peran (Role) <span class="text-rose-500">*</span></label>
                        <select
                            name="role"
                            id="role"
                            required
                            class="w-full px-3.5 py-2.5 border rounded-xl text-sm transition-all focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent @error('role') border-rose-300 bg-rose-50/30 @else border-slate-300 @enderror"
                        >
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin DSDABM</option>
                            <option value="petugas" {{ old('role', $user->role) === 'petugas' ? 'selected' : '' }}>Petugas Lapangan</option>
                            <option value="pimpinan" {{ old('role', $user->role) === 'pimpinan' ? 'selected' : '' }}>Pimpinan Eksekutif</option>
                            <option value="warga" {{ old('role', $user->role) === 'warga' ? 'selected' : '' }}>Warga</option>
                        </select>
                        @if(auth()->id() === $user->id)
                            <p class="text-xs text-amber-600 mt-1.5 flex items-center gap-1">
                                <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>Akun Anda sendiri: Peran akun terkunci demi keamanan sistem (*anti self-lockout*).</span>
                            </p>
                        @endif
                        @error('role')
                            <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Ubah Password (Opsional)</div>
                    <p class="text-xs text-slate-400">Kosongkan kolom password di bawah jika Anda tidak ingin mengubah password saat ini.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">Password Baru</label>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="w-full px-3 py-2 border rounded-lg text-sm bg-white focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent @error('password') border-rose-300 @else border-slate-300 @enderror"
                                placeholder="Minimal 8 karakter"
                            >
                            @error('password')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1">Konfirmasi Password Baru</label>
                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent"
                                placeholder="Ulangi password baru"
                            >
                        </div>
                    </div>
                </div>

                <div>
                    <label for="avatar" class="block text-sm font-semibold text-slate-700 mb-1.5">Ganti Foto Profil / Avatar</label>
                    <div class="flex items-center gap-4 mb-2">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="h-12 w-12 rounded-full object-cover border border-slate-200">
                        @else
                            <div class="h-12 w-12 rounded-full bg-[#1F3A5F] text-white flex items-center justify-center font-bold text-base">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="flex-1">
                            <input
                                type="file"
                                name="avatar"
                                id="avatar"
                                accept="image/png,image/jpeg,image/jpg"
                                class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-[#1F3A5F] hover:file:bg-slate-200 cursor-pointer"
                            >
                            <p class="text-xs text-slate-400 mt-1">Maksimal 5MB. Format yang didukung: JPG, JPEG, PNG.</p>
                        </div>
                    </div>
                    @error('avatar')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 transition-colors">
                        Batal
                    </a>
                    <button type="submit" dusk="submit-btn" class="px-5 py-2.5 bg-[#0E7C86] hover:bg-[#0b636b] text-white font-medium text-sm rounded-xl transition-all shadow-sm">
                        Perbarui Pengguna
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-admin-layout>
