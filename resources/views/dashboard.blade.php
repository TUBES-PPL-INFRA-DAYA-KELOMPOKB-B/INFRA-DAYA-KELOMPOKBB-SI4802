@if (auth()->user()->isWarga())
    <x-warga-layout title="Dashboard Warga">
        <div class="space-y-8">
            <!-- Welcome Banner -->
            <div class="relative overflow-hidden rounded-xl bg-[#0A2240] border border-[#16375E] p-8 text-white shadow-sm">
                <!-- Subtle architectural grid texture -->
                <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 20px 20px;"></div>
                <div class="relative z-10 max-w-2xl">
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1 text-xs font-semibold border border-white/15 text-[#F59E0B]">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#F59E0B]"></span>
                        Portal Partisipasi Masyarakat
                    </span>
                    <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                        Selamat Datang, {{ auth()->user()->name }}!
                    </h1>
                    <p class="mt-2 text-sm text-slate-300 leading-relaxed">
                        Bersama menjaga kenyamanan Kota Bandung. Laporkan setiap kerusakan jalan, saluran air tersumbat, atau penerangan jalan langsung kepada DSDABM Kota Bandung.
                    </p>
                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <x-button variant="amber" :href="route('warga.laporan.create', [], false)">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>Buat Laporan Baru</span>
                        </x-button>
                        <x-button variant="outline" class="border-white/30 text-white hover:bg-white/10" :href="route('peta.publik', [], false)">
                            Lihat Peta Masalah Kota
                        </x-button>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <x-card class="border-l-4 border-l-brand-navy">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Laporan Dikirim</p>
                    <p class="mt-2 text-3xl font-extrabold text-brand-navy">{{ $stats['total'] }}</p>
                    <p class="mt-1 text-xs text-slate-400">Total laporan yang Anda ajukan</p>
                </x-card>

                <x-card class="border-l-4 border-l-slate-400">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Menunggu Verifikasi</p>
                    <p class="mt-2 text-3xl font-extrabold text-slate-700">{{ $stats['menunggu'] }}</p>
                    <p class="mt-1 text-xs text-slate-400">Sedang ditinjau admin DSDABM</p>
                </x-card>

                <x-card class="border-l-4 border-l-brand-amber">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sedang Dikerjakan</p>
                    <p class="mt-2 text-3xl font-extrabold text-brand-amber-dark">{{ $stats['diproses'] }}</p>
                    <p class="mt-1 text-xs text-slate-400">Dalam penanganan petugas</p>
                </x-card>

                <x-card class="border-l-4 border-l-emerald-500">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tuntas Diperbaiki</p>
                    <p class="mt-2 text-3xl font-extrabold text-emerald-600">{{ $stats['selesai'] }}</p>
                    <p class="mt-1 text-xs text-slate-400">Selesai diperbaiki & siap dirating</p>
                </x-card>
            </div>

            <!-- Recent Reports -->
            <x-card title="Laporan Kerusakan Terbaru Anda" subtitle="Daftar laporan terkini beserta status progresnya">
                @if($recentReports->isEmpty())
                    <x-empty-state
                        title="Belum Ada Laporan Terkirim"
                        description="Anda belum mengajukan laporan kerusakan infrastruktur. Mulai laporkan fasilitas yang rusak di sekitar lingkungan Anda."
                        action-text="Buat Laporan Pertama"
                        :action-url="route('warga.laporan.create', [], false)"
                    />
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($recentReports as $lap)
                            <div class="py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-mono text-xs font-bold text-[#1F3A5F]">{{ $lap->nomor_tiket }}</span>
                                        <x-badge-status :status="$lap->status" />
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-900">{{ $lap->judul }}</h3>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        {{ $lap->kategori->nama_kategori ?? '-' }} &bull; Kel. {{ $lap->kelurahan->nama_kelurahan ?? '-' }}, Kec. {{ $lap->kelurahan->kecamatan->nama_kecamatan ?? '-' }}
                                    </p>
                                </div>
                                <a href="{{ route('warga.laporan.show', $lap->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition w-fit">
                                    <span>Lihat Detail</span>
                                    <span>&rarr;</span>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>
        </div>
    </x-warga-layout>
@else
    <x-admin-layout title="Dashboard Eksekutif & Monitoring SLA" header="Dashboard Eksekutif">
        <div class="space-y-8">
            <!-- Header Eksekutif & Filter Periode -->
            <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="rounded-md bg-blue-50 border border-blue-200 px-2 py-0.5 text-xs font-bold text-blue-700 tracking-wider">
                            Role: {{ strtoupper(auth()->user()->role) }}
                        </span>
                        <span class="text-xs text-slate-400">&bull;</span>
                        <span class="text-xs font-semibold text-blue-600">Monitoring Pelayanan Publik DSDABM</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900">
                        Dashboard Eksekutif &amp; Kinerja SLA
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Pemantauan real-time respon verifikasi, kecepatan pengerjaan lapangan, dan indeks kepuasan warga Kota Bandung.
                    </p>
                </div>

                <!-- Form Filter Periode -->
                <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <label for="filter_days" class="text-xs font-bold text-slate-500 whitespace-nowrap">Periode:</label>
                    <select
                        name="days"
                        id="filter_days"
                        dusk="filter-periode-days"
                        onchange="this.form.submit()"
                        class="text-xs rounded-xl border-slate-300 focus:ring-2 focus:ring-blue-600 py-2 px-3 bg-slate-50 font-semibold text-slate-700"
                    >
                        <option value="" {{ empty($days) ? 'selected' : '' }}>Semua Data Historis</option>
                        <option value="30" {{ $days === 30 ? 'selected' : '' }}>30 Hari Terakhir</option>
                        <option value="90" {{ $days === 90 ? 'selected' : '' }}>90 Hari Terakhir</option>
                        <option value="365" {{ $days === 365 ? 'selected' : '' }}>1 Tahun Terakhir</option>
                    </select>
                </form>
            </div>

            <!-- 6 KARTU INDIKATOR KINERJA UTAMA (KPI CARDS) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <!-- 1. Total Laporan Masuk -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm space-y-2">
                    <span class="text-xs font-bold tracking-wider text-slate-500">Total Laporan</span>
                    <p class="text-3xl font-black text-slate-900">{{ $metrics['total_laporan'] }}</p>
                    <div class="text-[11px] text-slate-400 flex items-center justify-between">
                        <span>Menunggu: <strong>{{ $metrics['menunggu'] }}</strong></span>
                        <span>Aktif: <strong>{{ $metrics['diproses'] }}</strong></span>
                    </div>
                </div>

                <!-- 2. Tingkat Penyelesaian -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm space-y-2">
                    <span class="text-xs font-bold tracking-wider text-emerald-700">Tingkat Tuntas</span>
                    <p class="text-3xl font-black text-emerald-600">{{ $metrics['persentase_selesai'] }}%</p>
                    <div class="text-[11px] text-slate-400">
                        {{ $metrics['selesai'] }} dari {{ $metrics['total_laporan'] }} masalah pulih
                    </div>
                </div>

                <!-- 3. Kepatuhan SLA Tenggat Waktu -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm space-y-2">
                    <span class="text-xs font-bold tracking-wider text-blue-600">Kepatuhan SLA</span>
                    <p class="text-3xl font-black text-blue-600">{{ $metrics['kepatuhan_sla'] }}%</p>
                    <div class="text-[11px] text-slate-400">
                        {{ $metrics['disposisi_tepat_waktu'] }} tugas tepat tenggat
                    </div>
                </div>

                <!-- 4. Kecepatan Verifikasi -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm space-y-2">
                    <span class="text-xs font-bold tracking-wider text-slate-500">Respon Verifikasi</span>
                    <p class="text-3xl font-black text-slate-800">
                        {{ $metrics['avg_verifikasi_jam'] }} <span class="text-xs font-bold text-slate-400">jam</span>
                    </p>
                    <div class="text-[11px] text-slate-400">
                        Standar SPM &le; 24 jam
                    </div>
                </div>

                <!-- 5. Durasi Penanganan Fisik -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm space-y-2">
                    <span class="text-xs font-bold tracking-wider text-amber-600">Durasi Perbaikan</span>
                    <p class="text-3xl font-black text-amber-600">
                        {{ $metrics['avg_penyelesaian_hari'] }} <span class="text-xs font-bold text-slate-400">hari</span>
                    </p>
                    <div class="text-[11px] text-slate-400">
                        Rata-rata tuntas fisik
                    </div>
                </div>

                <!-- 6. Rating Kepuasan Warga -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm space-y-2">
                    <span class="text-xs font-bold tracking-wider text-amber-500">Kepuasan Warga</span>
                    <div class="flex items-baseline gap-1">
                        <p class="text-3xl font-black text-amber-500">{{ $metrics['avg_rating'] }}</p>
                        <span class="text-xs font-bold text-slate-400">/ 5.0</span>
                    </div>
                    <div class="text-[11px] text-slate-400">
                        Dari {{ $metrics['total_review'] }} ulasan warga
                    </div>
                </div>
            </div>

            <!-- GRAFIK VISUALISASI DATA (CHART.JS) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Grafik 1: Tren Laporan Bulanan (Line Chart) -->
                <x-card class="p-5 sm:p-6 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Tren Laporan Bulanan</h2>
                            <p class="text-xs text-slate-400">Perbandingan laporan masuk vs tuntas diperbaiki (6 bulan)</p>
                        </div>
                    </div>
                    <div class="h-64 sm:h-72 relative">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </x-card>

                <!-- Grafik 2: Distribusi Kerusakan per Kategori (Doughnut Chart) -->
                <x-card class="p-5 sm:p-6 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Distribusi Kategori Fasilitas</h2>
                            <p class="text-xs text-slate-400">Proporsi kerusakan berdasarkan jenis infrastruktur</p>
                        </div>
                    </div>
                    <div class="h-64 sm:h-72 relative flex items-center justify-center">
                        <canvas id="categoryDistributionChart"></canvas>
                    </div>
                </x-card>

                <!-- Grafik 3: Beban Laporan Terbanyak per Kecamatan (Bar Chart) -->
                <x-card class="p-5 sm:p-6 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Kecamatan Beban Tertinggi</h2>
                            <p class="text-xs text-slate-400">Sebaran wilayah dengan laporan kerusakan terbanyak</p>
                        </div>
                    </div>
                    <div class="h-64 sm:h-72 relative">
                        <canvas id="topKecamatanChart"></canvas>
                    </div>
                </x-card>

                <!-- Grafik 4: Sebaran Rating Kepuasan Bintang (Bar Chart) -->
                <x-card class="p-5 sm:p-6 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Sebaran Indeks Kepuasan Warga</h2>
                            <p class="text-xs text-slate-400">Frekuensi penilaian bintang 1 hingga 5 dari warga</p>
                        </div>
                    </div>
                    <div class="h-64 sm:h-72 relative">
                        <canvas id="ratingDistributionChart"></canvas>
                    </div>
                </x-card>
            </div>

            <!-- TABEL ANALITIK PERFORMA WILAYAH KECAMATAN -->
            <x-card class="p-5 sm:p-6 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Matriks Kinerja per Wilayah Kecamatan</h2>
                        <p class="text-xs text-slate-400">Rincian beban kerja dan tingkat penyelesaian kerusakan per wilayah</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4 font-bold">Kecamatan</th>
                                <th class="py-3 px-4 font-bold text-center">Total Masalah</th>
                                <th class="py-3 px-4 font-bold text-center">Menunggu</th>
                                <th class="py-3 px-4 font-bold text-center">Pengerjaan</th>
                                <th class="py-3 px-4 font-bold text-center">Tuntas</th>
                                <th class="py-3 px-4 font-bold text-center">% Penyelesaian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($kecamatanSummary as $kec)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 font-bold text-slate-900">{{ $kec->nama_kecamatan }}</td>
                                    <td class="py-3 px-4 text-center font-extrabold text-[#1F3A5F]">{{ $kec->laporan_count }}</td>
                                    <td class="py-3 px-4 text-center text-slate-500">{{ $kec->menunggu_count }}</td>
                                    <td class="py-3 px-4 text-center text-amber-600 font-semibold">{{ $kec->diproses_count }}</td>
                                    <td class="py-3 px-4 text-center text-emerald-600 font-bold">{{ $kec->selesai_count }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <div class="w-16 bg-slate-100 rounded-full h-2 overflow-hidden">
                                                <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $kec->persentase_selesai }}%"></div>
                                            </div>
                                            <span class="font-extrabold text-slate-800">{{ $kec->persentase_selesai }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-slate-400">Belum ada data wilayah.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>

            <!-- DAFTAR LAPORAN TERKINI MEMERLUKAN TINDAK LANJUT -->
            <x-card class="p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Aktivitas Laporan Terkini</h2>
                        <p class="text-xs text-slate-400">Laporan terbaru yang masuk ke dalam sistem</p>
                    </div>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.verifikasi.index') }}" class="text-xs font-bold text-[#0E7C86] hover:underline">
                            Buka Antrean Verifikasi &rarr;
                        </a>
                    @endif
                </div>

                @if($recentReports->isEmpty())
                    <x-empty-state
                        title="Belum Ada Laporan Aktif"
                        description="Seluruh laporan saat ini telah terselesaikan atau belum ada laporan baru yang masuk ke antrean."
                    />
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="py-2.5 px-3 font-bold">Tiket &amp; Tanggal</th>
                                    <th class="py-2.5 px-3 font-bold">Judul &amp; Kategori</th>
                                    <th class="py-2.5 px-3 font-bold">Wilayah</th>
                                    <th class="py-2.5 px-3 font-bold">Status</th>
                                    <th class="py-2.5 px-3 font-bold">Prioritas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($recentReports as $rep)
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-2.5 px-3">
                                            <span class="font-mono font-bold text-[#1F3A5F] block">{{ $rep->nomor_tiket }}</span>
                                            <span class="text-[11px] text-slate-400">{{ $rep->created_at->format('d/m/Y H:i') }}</span>
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <span class="font-bold text-slate-800 block">{{ $rep->judul }}</span>
                                            <span class="text-[11px] text-slate-500">{{ $rep->kategori->nama_kategori ?? '-' }}</span>
                                        </td>
                                        <td class="py-2.5 px-3 text-slate-600">
                                            Kec. {{ $rep->kelurahan->kecamatan->nama_kecamatan ?? '-' }}
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <x-badge-status :status="$rep->status" />
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full {{ $rep->prioritas === 'tinggi' ? 'bg-rose-100 text-rose-700' : ($rep->prioritas === 'sedang' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                                {{ ucfirst($rep->prioritas) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        </div>

        @push('scripts')
            <!-- Chart.js CDN -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    if (typeof Chart === 'undefined') return;

                    // Chart 1: Monthly Trend (Line)
                    const trendData = @js($monthlyTrend);
                    const ctxTrend = document.getElementById('monthlyTrendChart');
                    if (ctxTrend) {
                        new Chart(ctxTrend, {
                            type: 'line',
                            data: {
                                labels: trendData.labels,
                                datasets: [
                                    {
                                        label: 'Laporan Masuk',
                                        data: trendData.masuk,
                                        borderColor: '#1F3A5F',
                                        backgroundColor: 'rgba(31, 58, 95, 0.1)',
                                        fill: true,
                                        tension: 0.35,
                                        borderWidth: 2.5,
                                    },
                                    {
                                        label: 'Laporan Selesai',
                                        data: trendData.selesai,
                                        borderColor: '#10B981',
                                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                        fill: true,
                                        tension: 0.35,
                                        borderWidth: 2.5,
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } }
                                },
                                scales: {
                                    y: { beginAtZero: true, ticks: { precision: 0 } }
                                }
                            }
                        });
                    }

                    // Chart 2: Category Distribution (Doughnut)
                    const catData = @js($categoryDistribution);
                    const ctxCat = document.getElementById('categoryDistributionChart');
                    if (ctxCat) {
                        new Chart(ctxCat, {
                            type: 'doughnut',
                            data: {
                                labels: catData.labels,
                                datasets: [{
                                    data: catData.data,
                                    backgroundColor: catData.colors,
                                    borderWidth: 2,
                                    borderColor: '#ffffff',
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
                                },
                                cutout: '65%'
                            }
                        });
                    }

                    // Chart 3: Top Kecamatan (Bar)
                    const kecData = @js($topKecamatan);
                    const ctxKec = document.getElementById('topKecamatanChart');
                    if (ctxKec) {
                        new Chart(ctxKec, {
                            type: 'bar',
                            data: {
                                labels: kecData.labels,
                                datasets: [
                                    {
                                        label: 'Total Laporan',
                                        data: kecData.data,
                                        backgroundColor: '#0E7C86',
                                        borderRadius: 6,
                                    },
                                    {
                                        label: 'Tuntas',
                                        data: kecData.selesai,
                                        backgroundColor: '#10B981',
                                        borderRadius: 6,
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } }
                                },
                                scales: {
                                    y: { beginAtZero: true, ticks: { precision: 0 } }
                                }
                            }
                        });
                    }

                    // Chart 4: Rating Distribution (Bar)
                    const ratingData = @js($ratingDistribution);
                    const ctxRating = document.getElementById('ratingDistributionChart');
                    if (ctxRating) {
                        new Chart(ctxRating, {
                            type: 'bar',
                            data: {
                                labels: ratingData.labels,
                                datasets: [{
                                    label: 'Jumlah Ulasan',
                                    data: ratingData.data,
                                    backgroundColor: ['#ef4444', '#f97316', '#f59e0b', '#3b82f6', '#10b981'],
                                    borderRadius: 6,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false }
                                },
                                scales: {
                                    y: { beginAtZero: true, ticks: { precision: 0 } }
                                }
                            }
                        });
                    }
                });
            </script>
        @endpush
    </x-admin-layout>
@endif
