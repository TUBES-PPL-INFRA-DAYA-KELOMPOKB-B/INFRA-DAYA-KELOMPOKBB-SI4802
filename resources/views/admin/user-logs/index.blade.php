<x-admin-layout title="Log Aktivitas Akun">
    <div class="space-y-6">
        <!-- Header Page -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-[#1F3A5F] tracking-tight">Log Aktivitas Akun</h1>
                <p class="text-sm text-slate-500 mt-1">Jejak audit seluruh perubahan akun, mutasi peran, status aktivasi, dan riwayat login (AC 6.4, 6.5, 6.6).</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Append-Only Audit Log
                </span>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <x-card class="p-4">
            <form method="GET" action="{{ route('admin.user-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Search user -->
                <div class="lg:col-span-2 relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        dusk="log-search-input"
                        placeholder="Cari nama atau email pengguna..."
                        class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent"
                    >
                    <svg class="h-5 w-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <!-- Action Type Filter -->
                <div>
                    <select
                        name="action"
                        dusk="log-action-select"
                        class="w-full py-2 px-3 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent text-slate-700"
                    >
                        <option value="">Semua Jenis Aksi</option>
                        @foreach($actions as $key => $label)
                            <option value="{{ $key }}" {{ $action === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range -->
                <div>
                    <input
                        type="date"
                        name="start_date"
                        value="{{ $startDate }}"
                        dusk="log-start-date"
                        title="Tanggal Mulai"
                        placeholder="Tanggal Mulai"
                        class="w-full py-2 px-3 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent text-slate-700"
                    >
                </div>

                <div class="flex items-center gap-2">
                    <input
                        type="date"
                        name="end_date"
                        value="{{ $endDate }}"
                        dusk="log-end-date"
                        title="Tanggal Selesai"
                        placeholder="Tanggal Selesai"
                        class="w-full py-2 px-3 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent text-slate-700"
                    >
                    <button type="submit" dusk="log-filter-btn" class="px-4 py-2 bg-[#1F3A5F] hover:bg-[#162a45] text-white rounded-lg text-sm font-medium transition-colors">
                        Filter
                    </button>
                    @if($search || $action || $startDate || $endDate)
                        <a href="{{ route('admin.user-logs.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-sm font-medium transition-colors" title="Reset filter">
                            ✕
                        </a>
                    @endif
                </div>
            </form>
        </x-card>

        <!-- Table Card -->
        <x-card class="overflow-hidden p-0 border border-slate-200 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600" dusk="user-logs-table">
                    <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3.5">Waktu</th>
                            <th class="px-6 py-3.5">Pelaku (Aktor)</th>
                            <th class="px-6 py-3.5">Target Pengguna</th>
                            <th class="px-6 py-3.5">Jenis Aksi</th>
                            <th class="px-6 py-3.5">Detail Perubahan</th>
                            <th class="px-6 py-3.5">Alasan / Catatan</th>
                            <th class="px-6 py-3.5">Alamat IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <!-- Timestamp -->
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-600">
                                    <div class="font-medium text-slate-900">{{ $log->created_at->format('d M Y') }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $log->created_at->format('H:i:s') }} WIB</div>
                                </td>

                                <!-- Aktor -->
                                <td class="px-6 py-4">
                                    @if($log->actor)
                                        <div class="font-medium text-slate-900">{{ $log->actor->name }}</div>
                                        <div class="text-xs text-slate-400">{{ $log->actor->email }}</div>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-600">
                                            Sistem / Publik
                                        </span>
                                    @endif
                                </td>

                                <!-- Target Pengguna -->
                                <td class="px-6 py-4">
                                    @if($log->targetUser)
                                        <div class="font-medium text-slate-900">{{ $log->targetUser->name }}</div>
                                        <div class="text-xs text-slate-400">{{ $log->targetUser->email }}</div>
                                    @elseif(isset($log->old_value['name']) || isset($log->new_value['name']))
                                        <div class="font-medium text-slate-700">{{ $log->old_value['name'] ?? $log->new_value['name'] }}</div>
                                        <div class="text-xs text-slate-400">{{ $log->old_value['email'] ?? $log->new_value['email'] ?? '-' }} (Dihapus)</div>
                                    @else
                                        <span class="text-slate-400 text-xs italic">-</span>
                                    @endif
                                </td>

                                <!-- Jenis Aksi Badge -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
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
                                        $style = $badgeStyles[$log->action] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                        $label = $actions[$log->action] ?? ucwords(str_replace('_', ' ', $log->action));
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $style }}">
                                        {{ $label }}
                                    </span>
                                </td>

                                <!-- Nilai Lama / Baru -->
                                <td class="px-6 py-4 text-xs">
                                    @if($log->action === 'role_changed')
                                        <span class="text-slate-500">Peran:</span>
                                        <span class="line-through text-slate-400 font-mono">{{ $log->old_value['role'] ?? '-' }}</span>
                                        <span class="mx-1 font-bold text-slate-600">→</span>
                                        <span class="font-semibold text-purple-700 font-mono">{{ $log->new_value['role'] ?? '-' }}</span>
                                    @elseif($log->action === 'account_activated' || $log->action === 'account_deactivated')
                                        <span class="font-semibold {{ $log->action === 'account_activated' ? 'text-emerald-700' : 'text-rose-700' }}">
                                            Status diubah ke {{ $log->action === 'account_activated' ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    @elseif($log->action === 'password_changed')
                                        <span class="font-medium text-amber-700">Password akun diperbarui</span>
                                    @elseif($log->action === 'login_success')
                                        <span class="text-emerald-700 font-medium">Autentikasi sesi berhasil</span>
                                    @elseif($log->action === 'login_failed')
                                        <span class="text-rose-600 font-medium">Percobaan login gagal</span>
                                    @elseif($log->old_value && $log->new_value)
                                        <div class="space-y-0.5 max-w-xs truncate">
                                            @foreach($log->new_value as $k => $v)
                                                @if(isset($log->old_value[$k]) && $log->old_value[$k] !== $v)
                                                    <div><span class="text-slate-400">{{ $k }}:</span> <span class="line-through text-slate-400">{{ is_bool($log->old_value[$k]) ? ($log->old_value[$k] ? 'true' : 'false') : $log->old_value[$k] }}</span> → <span class="font-semibold text-slate-700">{{ is_bool($v) ? ($v ? 'true' : 'false') : $v }}</span></div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @elseif($log->new_value)
                                        <span class="text-slate-600">Akun baru didaftarkan</span>
                                    @elseif($log->action === 'user_deleted')
                                        <span class="text-red-600 font-medium">Akun dihapus dari sistem</span>
                                    @else
                                        <span class="text-slate-400 italic">-</span>
                                    @endif
                                </td>

                                <!-- Alasan -->
                                <td class="px-6 py-4 text-xs text-slate-600 max-w-xs">
                                    {{ $log->reason ?? '-' }}
                                </td>

                                <!-- IP Address -->
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-mono text-slate-500">
                                    {{ $log->ip_address ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400 text-sm">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="h-8 w-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>Tidak ada riwayat log aktivitas yang ditemukan.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                    {{ $logs->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-admin-layout>

