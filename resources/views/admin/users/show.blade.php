<x-admin-layout title="Detail Pengguna">
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-[#1F3A5F] tracking-tight">Detail Pengguna</h1>
                <p class="text-sm text-slate-500 mt-1">Informasi lengkap akun, hak akses peran, dan ringkasan aktivitas.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    Kembali
                </a>
                <a href="{{ route('admin.users.edit', $user->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-[#0E7C86] hover:bg-[#0b636b] rounded-xl transition-all shadow-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Ubah Data
                </a>
            </div>
        </div>

        <!-- Profil Utama -->
        <x-card class="p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 pb-6 border-b border-slate-100">
                @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="h-24 w-24 rounded-2xl object-cover border-2 border-slate-200 shadow-sm">
                @else
                    <div class="h-24 w-24 rounded-2xl bg-[#1F3A5F] text-white flex items-center justify-center font-bold text-3xl shadow-sm">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <div class="space-y-2 flex-1">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h2 class="text-xl font-bold text-slate-900">{{ $user->name }}</h2>
                        @if($user->role === 'admin')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">Admin DSDABM</span>
                        @elseif($user->role === 'petugas')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Petugas Lapangan</span>
                        @elseif($user->role === 'pimpinan')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">Pimpinan Eksekutif</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">Warga</span>
                        @endif

                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                            {{ $user->is_active ? 'Akun Aktif' : 'Akun Nonaktif' }}
                        </span>
                    </div>
                    <p class="text-sm text-slate-500">{{ $user->email }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 pt-6">
                <div>
                    <span class="block text-xs font-medium uppercase tracking-wider text-slate-400">Nomor Telepon</span>
                    <span class="text-sm font-semibold text-slate-800 mt-1 block">{{ $user->phone ?? '-' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-medium uppercase tracking-wider text-slate-400">Tanggal Dibuat</span>
                    <span class="text-sm font-semibold text-slate-800 mt-1 block">{{ $user->created_at ? $user->created_at->format('d M Y, H:i') : '-' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-medium uppercase tracking-wider text-slate-400">Terakhir Diperbarui</span>
                    <span class="text-sm font-semibold text-slate-800 mt-1 block">{{ $user->updated_at ? $user->updated_at->format('d M Y, H:i') : '-' }}</span>
                </div>
            </div>
        </x-card>

        <!-- Ringkasan Keterkaitan Data -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-card class="p-4 bg-slate-50/70 border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-medium text-slate-500">Laporan Diajukan</span>
                        <div class="text-2xl font-bold text-slate-900 mt-1">{{ $user->laporanKerusakan()->count() }}</div>
                    </div>
                    <div class="p-2.5 bg-blue-100/60 text-blue-700 rounded-xl">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                </div>
            </x-card>

            <x-card class="p-4 bg-slate-50/70 border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-medium text-slate-500">Disposisi Ditugaskan</span>
                        <div class="text-2xl font-bold text-slate-900 mt-1">{{ $user->penugasanPetugas()->count() }}</div>
                    </div>
                    <div class="p-2.5 bg-amber-100/60 text-amber-700 rounded-xl">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                </div>
            </x-card>

            <x-card class="p-4 bg-slate-50/70 border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-medium text-slate-500">Surat Tugas Diterbitkan</span>
                        <div class="text-2xl font-bold text-slate-900 mt-1">{{ $user->disposisiDibuat()->count() }}</div>
                    </div>
                    <div class="p-2.5 bg-teal-100/60 text-teal-700 rounded-xl">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Aksi Cepat Ubah Role (AC 5.2) -->
        <x-card class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Ubah Hak Akses Peran (Role)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Tetapkan peran pengguna. Hak akses fitur akan langsung berlaku pada permintaan berikutnya.</p>
                </div>
            </div>
            @if(auth()->id() === $user->id)
                <div class="mt-4 p-4 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-center gap-3">
                    <svg class="h-5 w-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Anda tidak dapat mengubah peran akun Anda sendiri untuk mencegah terkuncinya akses sistem (*anti self-lockout*).</span>
                </div>
            @else
                <form method="POST" action="{{ route('admin.users.update-role', $user->id) }}" class="mt-4 flex flex-col sm:flex-row items-center gap-3">
                    @csrf
                    @method('PATCH')
                    <div class="w-full sm:w-64">
                        <select
                            name="role"
                            dusk="quick-role-select"
                            class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent text-slate-700"
                        >
                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin DSDABM</option>
                            <option value="petugas" {{ $user->role === 'petugas' ? 'selected' : '' }}>Petugas Lapangan</option>
                            <option value="pimpinan" {{ $user->role === 'pimpinan' ? 'selected' : '' }}>Pimpinan Eksekutif</option>
                            <option value="warga" {{ $user->role === 'warga' ? 'selected' : '' }}>Warga</option>
                        </select>
                    </div>
                    <button type="submit" dusk="quick-role-submit" class="w-full sm:w-auto px-5 py-2 bg-[#1F3A5F] hover:bg-[#162a45] text-white rounded-xl text-sm font-medium transition-colors shadow-sm">
                        Simpan Peran
                    </button>
                </form>
            @endif
        </x-card>

        <!-- Section Riwayat Log Aktivitas (AC 4.3 & PBI-6) -->
        <x-card class="p-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Riwayat Log Aktivitas Pengguna</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Jejak audit perubahan data akun, pergantian peran, status aktivasi, dan login terkait pengguna ini.</p>
                </div>
                <a href="{{ route('admin.user-logs.index', ['search' => $user->email]) }}" class="text-xs font-semibold text-[#0E7C86] hover:underline flex items-center gap-1">
                    <span>Lihat Semua Log</span>
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            @php
                $logs = $user->userLogs()->with('actor')->take(10)->get();
                $badgeStyles = [
                    'account_activated' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'account_deactivated' => 'bg-rose-50 text-rose-700 border-rose-200',
                    'role_changed' => 'bg-purple-50 text-purple-700 border-purple-200',
                    'password_changed' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'user_created' => 'bg-blue-50 text-blue-700 border-blue-200',
                    'user_updated' => 'bg-sky-50 text-sky-700 border-sky-200',
                    'user_deleted' => 'bg-red-50 text-red-700 border-red-200',
                    'login_success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'login_failed' => 'bg-rose-50 text-rose-700 border-rose-200',
                ];
                $actionLabels = [
                    'user_created' => 'Pembuatan Akun',
                    'user_updated' => 'Pembaruan Data',
                    'role_changed' => 'Perubahan Role',
                    'password_changed' => 'Perubahan Password',
                    'account_activated' => 'Aktivasi Akun',
                    'account_deactivated' => 'Nonaktivasi Akun',
                    'user_deleted' => 'Penghapusan Akun',
                    'login_success' => 'Login Berhasil',
                    'login_failed' => 'Login Gagal',
                ];
            @endphp

            @if($logs->isNotEmpty())
                <div class="overflow-x-auto -mx-6 -mb-6">
                    <table class="w-full text-left text-xs text-slate-600" dusk="user-detail-logs-table">
                        <thead class="bg-slate-50 border-y border-slate-100 font-semibold text-slate-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3">Waktu</th>
                                <th class="px-6 py-3">Pelaku</th>
                                <th class="px-6 py-3">Aksi</th>
                                <th class="px-6 py-3">Perubahan</th>
                                <th class="px-6 py-3">Alasan / Catatan</th>
                                <th class="px-6 py-3">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($logs as $log)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-6 py-3 whitespace-nowrap text-slate-500">
                                        {{ $log->created_at->format('d M Y H:i') }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap font-medium text-slate-800">
                                        {{ $log->actor ? $log->actor->name : 'Sistem' }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $badgeStyles[$log->action] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                                            {{ $actionLabels[$log->action] ?? $log->action }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3">
                                        @if($log->action === 'role_changed')
                                            <span class="font-mono text-slate-400 line-through">{{ $log->old_value['role'] ?? '-' }}</span> → <span class="font-mono font-bold text-purple-700">{{ $log->new_value['role'] ?? '-' }}</span>
                                        @elseif($log->action === 'account_activated' || $log->action === 'account_deactivated')
                                            <span>Status diubah ke <strong>{{ $log->action === 'account_activated' ? 'Aktif' : 'Nonaktif' }}</strong></span>
                                        @elseif($log->action === 'password_changed')
                                            <span class="text-amber-700 font-medium">Password diperbarui</span>
                                        @elseif($log->action === 'user_created')
                                            <span class="text-blue-700 font-medium">Akun didaftarkan</span>
                                        @elseif($log->action === 'login_success')
                                            <span class="text-emerald-700">Autentikasi berhasil</span>
                                        @elseif($log->action === 'login_failed')
                                            <span class="text-rose-600">Percobaan login ditolak</span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-slate-500 max-w-xs truncate">
                                        {{ $log->reason ?? '-' }}
                                    </td>
                                    <td class="px-6 py-3 font-mono text-slate-400 whitespace-nowrap">
                                        {{ $log->ip_address ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-8 text-center bg-slate-50/60 rounded-xl border border-dashed border-slate-200">
                    <p class="text-xs text-slate-500">Belum ada riwayat aktivitas tercatat untuk akun pengguna ini.</p>
                </div>
            @endif
        </x-card>
    </div>
</x-admin-layout>

