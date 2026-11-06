<x-guest-layout>
    <div x-data="{
        fillCredentials(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password';
        }
    }">
        <div class="mb-6 pb-4 border-b border-slate-100 text-center">
            <h1 class="text-xl font-bold text-[#0A2240] tracking-tight">Masuk ke Portal INFRA-DAYA</h1>
            <p class="text-xs text-slate-500 mt-1">
                Gunakan kredensial resmi terdaftar untuk mengelola pengaduan infrastruktur Kota Bandung
            </p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 tracking-wide mb-1">
                    Alamat Surel (Email)
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/></svg>
                    </div>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="nama@dsdabm.bandung.go.id"
                        class="block w-full rounded-xl border border-slate-300 pl-10 pr-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-[#0A2240] focus:ring-1 focus:ring-[#0A2240]"
                    >
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="password" class="block text-xs font-semibold text-slate-700 tracking-wide">
                        Kata Sandi (Password)
                    </label>
                    @if (Route::has('password.request'))
                        <a class="text-xs text-[#0E7C86] hover:text-[#0b626a] font-medium" href="{{ route('password.request') }}">
                            Lupa sandi?
                        </a>
                    @endif
                </div>

                <div class="relative" x-data="{ show: false }">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <input
                        id="password"
                        :type="show ? 'text' : 'password'"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="block w-full rounded-xl border border-slate-300 pl-10 pr-10 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-[#0A2240] focus:ring-1 focus:ring-[#0A2240]"
                    >
                    <button
                        type="button"
                        @click="show = !show"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none"
                    >
                        <svg x-show="!show" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg x-show="show" style="display: none;" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between pt-1">
                <label for="remember_me" class="inline-flex items-center cursor-pointer">
                    <input
                        id="remember_me"
                        type="checkbox"
                        class="rounded border-slate-300 text-[#0A2240] shadow-xs focus:ring-[#0A2240]"
                        name="remember"
                    >
                    <span class="ms-2 text-xs text-slate-600 font-medium">Ingat perangkat saya</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#0A2240] hover:bg-[#16375E] text-white px-5 py-3 text-sm font-bold shadow-sm transition-all focus:ring-2 focus:ring-[#0A2240] focus:ring-offset-2"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                <span>Masuk ke Sistem</span>
            </button>
        </form>

        <!-- Quick Demo Credentials Selector -->
        <div class="mt-8 pt-5 border-t border-slate-100">
            <div class="flex items-center justify-between mb-2.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pilih Akun Demo Cepat</span>
                <span class="text-[10px] text-slate-400">Password: <code class="font-mono bg-slate-100 px-1 py-0.5 rounded text-slate-700">password</code></span>
            </div>
            <div class="grid grid-cols-2 gap-2 text-left">
                <button
                    type="button"
                    @click="fillCredentials('admin@dsdabm.bandung.go.id')"
                    class="p-2 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 hover:border-slate-300 transition-colors text-left"
                >
                    <div class="text-[11px] font-bold text-[#0A2240]">Admin DSDABM</div>
                    <div class="text-[10px] text-slate-400 truncate">admin@dsdabm...</div>
                </button>

                <button
                    type="button"
                    @click="fillCredentials('pimpinan@dsdabm.bandung.go.id')"
                    class="p-2 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 hover:border-slate-300 transition-colors text-left"
                >
                    <div class="text-[11px] font-bold text-slate-800">Pimpinan Eksekutif</div>
                    <div class="text-[10px] text-slate-400 truncate">pimpinan@dsdabm...</div>
                </button>

                <button
                    type="button"
                    @click="fillCredentials('petugas1@dsdabm.bandung.go.id')"
                    class="p-2 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 hover:border-slate-300 transition-colors text-left"
                >
                    <div class="text-[11px] font-bold text-[#0E7C86]">Petugas Lapangan</div>
                    <div class="text-[10px] text-slate-400 truncate">petugas1@dsdabm...</div>
                </button>

                <button
                    type="button"
                    @click="fillCredentials('warga@bandung.go.id')"
                    class="p-2 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 hover:border-slate-300 transition-colors text-left"
                >
                    <div class="text-[11px] font-bold text-amber-700">Warga Pelapor</div>
                    <div class="text-[10px] text-slate-400 truncate">warga@bandung...</div>
                </button>
            </div>
        </div>

        <!-- Registration Link -->
        <div class="mt-5 text-center text-xs text-slate-500">
            Belum memiliki akun warga?
            <a href="{{ route('register') }}" class="font-bold text-[#0E7C86] hover:text-[#0b626a] underline ml-1">
                Daftar Akun Pelapor
            </a>
        </div>
    </div>
</x-guest-layout>
