<x-admin-layout title="Kelola Pengguna">
    <div class="space-y-6">
        <!-- Header Page -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-[#1F3A5F] tracking-tight">Kelola Pengguna</h1>
                <p class="text-sm text-slate-500 mt-1">Daftar akun sistem INFRA-DAYA (Admin, Petugas Lapangan, Pimpinan, dan Warga).</p>
            </div>
            <div>
                <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#0E7C86] hover:bg-[#0b636b] text-white font-medium text-sm transition-all shadow-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Pengguna
                </a>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <x-card class="p-4">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        dusk="search-input"
                        placeholder="Cari berdasarkan nama, email, atau no. telepon..."
                        class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent"
                    >
                    <svg class="h-5 w-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <div class="w-full sm:w-48">
                    <select
                        name="role"
                        dusk="role-select"
                        onchange="this.form.submit()"
                        class="w-full py-2 px-3 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-[#0E7C86] focus:border-transparent text-slate-700"
                    >
                        <option value="">Semua Peran (Role)</option>
                        <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin DSDABM</option>
                        <option value="petugas" {{ $role === 'petugas' ? 'selected' : '' }}>Petugas Lapangan</option>
                        <option value="pimpinan" {{ $role === 'pimpinan' ? 'selected' : '' }}>Pimpinan</option>
                        <option value="warga" {{ $role === 'warga' ? 'selected' : '' }}>Warga</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" dusk="search-btn" class="px-4 py-2 bg-[#1F3A5F] hover:bg-[#162a45] text-white rounded-lg text-sm font-medium transition-colors">
                        Cari
                    </button>
                    @if($search || $role)
                        <a href="{{ route('admin.users.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-sm font-medium transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </x-card>

        <!-- Tabel Pengguna -->
        <x-card class="overflow-hidden p-0">
            @if($users->isEmpty())
                <div class="py-12 text-center">
                    <svg class="h-12 w-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <p class="text-sm font-medium text-slate-600">Tidak ada data pengguna ditemukan.</p>
                    <p class="text-xs text-slate-400 mt-1">Coba gunakan kata kunci pencarian atau filter lain.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3.5">Pengguna</th>
                                <th class="px-6 py-3.5">Peran</th>
                                <th class="px-6 py-3.5">Kontak</th>
                                <th class="px-6 py-3.5">Status Akun</th>
                                <th class="px-6 py-3.5">Tanggal Dibuat</th>
                                <th class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($users as $user)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            @if($user->avatar)
                                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="h-10 w-10 rounded-full object-cover border border-slate-200">
                                            @else
                                                <div class="h-10 w-10 rounded-full bg-[#1F3A5F] text-white flex items-center justify-center font-bold text-sm">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="font-semibold text-slate-900">{{ $user->name }}</div>
                                                <div class="text-xs text-slate-400">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($user->role === 'admin')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">Admin</span>
                                        @elseif($user->role === 'petugas')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Petugas</span>
                                        @elseif($user->role === 'pimpinan')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">Pimpinan</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">Warga</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-xs text-slate-500">
                                        {{ $user->phone ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <form method="POST" action="{{ route('admin.users.toggle-active', $user->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                dusk="toggle-active-btn-{{ $user->id }}"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium cursor-pointer transition-colors {{ $user->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}"
                                                title="Klik untuk mengubah status aktif"
                                            >
                                                <span class="h-1.5 w-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-slate-500 whitespace-nowrap">
                                        {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.users.show', $user->id) }}" class="p-1.5 text-slate-400 hover:text-[#1F3A5F] rounded-lg hover:bg-slate-100 transition-colors" title="Lihat Detail" dusk="show-btn-{{ $user->id }}">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="p-1.5 text-slate-400 hover:text-[#0E7C86] rounded-lg hover:bg-slate-100 transition-colors" title="Ubah Data">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                            @if(auth()->id() !== $user->id)
                                                <button
                                                    type="button"
                                                    onclick="confirmDelete('{{ route('admin.users.destroy', $user->id) }}', '{{ $user->name }}')"
                                                    class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100 transition-colors"
                                                    title="Hapus Akun"
                                                    dusk="delete-btn-{{ $user->id }}"
                                                >
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($users->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100">
                        {{ $users->links() }}
                    </div>
                @endif
            @endif
        </x-card>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div id="deleteModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">
            <div class="h-12 w-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-900">Konfirmasi Hapus Pengguna</h3>
                <p class="text-sm text-slate-500 mt-1">Apakah Anda yakin ingin menghapus akun <span id="deleteUserName" class="font-semibold text-slate-800"></span>? Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <form id="deleteForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-medium transition-colors">
                        Batal
                    </button>
                    <button type="submit" dusk="confirm-delete-btn" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-sm font-medium transition-colors">
                        Ya, Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function confirmDelete(url, name) {
            document.getElementById('deleteForm').action = url;
            document.getElementById('deleteUserName').textContent = name;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
    </script>
</x-admin-layout>
