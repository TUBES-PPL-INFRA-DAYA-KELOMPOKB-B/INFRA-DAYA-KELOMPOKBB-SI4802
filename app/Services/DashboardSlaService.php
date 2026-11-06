<?php

namespace App\Services;

use App\Models\DisposisiPenugasan;
use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\LaporanKerusakan;
use App\Models\UmpanBalik;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardSlaService
{
    /**
     * Ambil seluruh metrik ringkasan SLA dan performa eksekutif.
     *
     * @return array<string, mixed>
     */
    public function getExecutiveMetrics(?int $days = null): array
    {
        $laporanQuery = LaporanKerusakan::query();

        if ($days !== null && $days > 0) {
            $laporanQuery->where('created_at', '>=', now()->subDays($days));
        }

        $total = (clone $laporanQuery)->count();
        $menunggu = (clone $laporanQuery)->where('status', 'menunggu_verifikasi')->count();
        $diproses = (clone $laporanQuery)->where('status', 'diproses')->count();
        $selesai = (clone $laporanQuery)->where('status', 'selesai')->count();
        $ditolak = (clone $laporanQuery)->where('status', 'ditolak')->count();

        $persentaseSelesai = $total > 0 ? round(($selesai / $total) * 100, 1) : 0;

        // 1. Rata-rata Waktu Verifikasi (Mean Time to Verify dalam Jam)
        // Dihitung dari created_at laporan sampai ada riwayat_status diproses atau ditolak
        $waktuVerifikasiSub = DB::table('riwayat_status')
            ->select('laporan_id', DB::raw('MIN(created_at) as verified_at'))
            ->whereIn('status', ['diproses', 'ditolak'])
            ->groupBy('laporan_id');

        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            $diffHourSql = 'EXTRACT(EPOCH FROM (rv.verified_at - l.created_at)) / 3600';
            $diffHariSql = 'EXTRACT(EPOCH FROM (selesai_at - created_at)) / 86400';
            $dateCheckSql = 'CAST(selesai_at AS DATE) <= tenggat_waktu';
        } elseif ($driver === 'sqlite') {
            $diffHourSql = '(strftime(\'%s\', rv.verified_at) - strftime(\'%s\', l.created_at)) / 3600';
            $diffHariSql = '(strftime(\'%s\', selesai_at) - strftime(\'%s\', created_at)) / 86400';
            $dateCheckSql = 'date(selesai_at) <= tenggat_waktu';
        } else {
            $diffHourSql = 'TIMESTAMPDIFF(HOUR, l.created_at, rv.verified_at)';
            $diffHariSql = 'TIMESTAMPDIFF(HOUR, created_at, selesai_at) / 24';
            $dateCheckSql = 'DATE(selesai_at) <= tenggat_waktu';
        }

        $avgVerifikasiJam = DB::table('laporan_kerusakan as l')
            ->joinSub($waktuVerifikasiSub, 'rv', function ($join) {
                $join->on('l.id', '=', 'rv.laporan_id');
            })
            ->when($days !== null && $days > 0, function ($q) use ($days) {
                $q->where('l.created_at', '>=', now()->subDays($days));
            })
            ->selectRaw("AVG({$diffHourSql}) as avg_jam")
            ->value('avg_jam');

        $avgVerifikasiJam = $avgVerifikasiJam !== null ? round((float) $avgVerifikasiJam, 1) : 0;

        // 2. Rata-rata Durasi Penyelesaian Fisik (Mean Time to Resolve dalam Hari)
        // Dihitung dari laporan dibuat sampai selesai_at
        $avgPenyelesaianHari = (clone $laporanQuery)
            ->where('status', 'selesai')
            ->whereNotNull('selesai_at')
            ->selectRaw("AVG({$diffHariSql}) as avg_hari")
            ->value('avg_hari');

        $avgPenyelesaianHari = $avgPenyelesaianHari !== null ? round((float) $avgPenyelesaianHari, 1) : 0;

        // 3. Kepatuhan Tenggat Waktu SLA (% On-Time Completion)
        // Disposisi selesai sebelum atau tepat pada tenggat_waktu
        $disposisiSelesaiQuery = DisposisiPenugasan::query()
            ->where('status_penugasan', 'selesai')
            ->when($days !== null && $days > 0, function ($q) use ($days) {
                $q->where('created_at', '>=', now()->subDays($days));
            });

        $totalDisposisiSelesai = (clone $disposisiSelesaiQuery)->count();
        $disposisiTepatWaktu = (clone $disposisiSelesaiQuery)
            ->whereHas('laporan', function ($q) use ($dateCheckSql) {
                $q->whereNotNull('selesai_at')
                    ->whereRaw($dateCheckSql);
            })
            ->count();

        $kepatuhanSla = $totalDisposisiSelesai > 0
            ? round(($disposisiTepatWaktu / $totalDisposisiSelesai) * 100, 1)
            : 100.0;

        // 4. Rating Kepuasan Warga
        $ratingQuery = UmpanBalik::query()
            ->when($days !== null && $days > 0, function ($q) use ($days) {
                $q->where('created_at', '>=', now()->subDays($days));
            });

        $totalReview = (clone $ratingQuery)->count();
        $avgRating = $totalReview > 0 ? round((float) (clone $ratingQuery)->avg('rating'), 1) : 0;

        return [
            'total_laporan' => $total,
            'menunggu' => $menunggu,
            'diproses' => $diproses,
            'selesai' => $selesai,
            'ditolak' => $ditolak,
            'persentase_selesai' => $persentaseSelesai,
            'avg_verifikasi_jam' => $avgVerifikasiJam,
            'avg_penyelesaian_hari' => $avgPenyelesaianHari,
            'kepatuhan_sla' => $kepatuhanSla,
            'disposisi_tepat_waktu' => $disposisiTepatWaktu,
            'total_disposisi_selesai' => $totalDisposisiSelesai,
            'avg_rating' => $avgRating,
            'total_review' => $totalReview,
        ];
    }

    /**
     * Dapatkan data tren laporan 6 bulan terakhir untuk Chart.js line chart.
     *
     * @return array{labels: string[], masuk: int[], selesai: int[]}
     */
    public function getMonthlyTrendChartData(): array
    {
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months->push([
                'key' => $date->format('Y-m'),
                'label' => $date->translatedFormat('M Y'),
                'start' => (clone $date)->startOfMonth(),
                'end' => (clone $date)->endOfMonth(),
            ]);
        }

        $masukData = [];
        $selesaiData = [];
        $labels = [];

        foreach ($months as $m) {
            $labels[] = $m['label'];

            $countMasuk = LaporanKerusakan::whereBetween('created_at', [$m['start'], $m['end']])->count();
            $countSelesai = LaporanKerusakan::where('status', 'selesai')
                ->whereBetween('selesai_at', [$m['start'], $m['end']])
                ->count();

            $masukData[] = $countMasuk;
            $selesaiData[] = $countSelesai;
        }

        return [
            'labels' => $labels,
            'masuk' => $masukData,
            'selesai' => $selesaiData,
        ];
    }

    /**
     * Dapatkan distribusi laporan per kategori untuk Chart.js doughnut chart.
     *
     * @return array{labels: string[], data: int[], colors: string[]}
     */
    public function getCategoryDistributionChartData(): array
    {
        $kategoriList = KategoriInfrastruktur::withCount('laporan')->get();

        $labels = [];
        $data = [];
        $palette = ['#1F3A5F', '#0E7C86', '#F2A93B', '#10B981', '#6366F1', '#EC4899', '#8B5CF6'];

        foreach ($kategoriList as $idx => $kat) {
            $labels[] = $kat->nama_kategori;
            $data[] = $kat->laporan_count;
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => array_slice($palette, 0, count($labels)),
        ];
    }

    /**
     * Dapatkan top 5 kecamatan dengan laporan terbanyak untuk Chart.js bar chart.
     *
     * @return array{labels: string[], data: int[], selesai: int[]}
     */
    public function getTopKecamatanChartData(int $limit = 6): array
    {
        $topKecamatan = Kecamatan::withCount(['laporan', 'laporan as selesai_count' => function ($q) {
            $q->where('status', 'selesai');
        }])
            ->orderByDesc('laporan_count')
            ->limit($limit)
            ->get();

        $labels = [];
        $data = [];
        $selesai = [];

        foreach ($topKecamatan as $kec) {
            $labels[] = $kec->nama_kecamatan;
            $data[] = $kec->laporan_count;
            $selesai[] = $kec->selesai_count;
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'selesai' => $selesai,
        ];
    }

    /**
     * Dapatkan sebaran kepuasan bintang 1-5 untuk Chart.js.
     *
     * @return array{labels: string[], data: int[]}
     */
    public function getRatingDistributionChartData(): array
    {
        $ratings = UmpanBalik::select('rating', DB::raw('count(*) as total'))
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->all();

        $labels = ['1 Bintang', '2 Bintang', '3 Bintang', '4 Bintang', '5 Bintang'];
        $data = [];

        for ($star = 1; $star <= 5; $star++) {
            $data[] = $ratings[$star] ?? 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Ringkasan performa seluruh kecamatan untuk tabel analitik eksekutif.
     */
    public function getKecamatanPerformanceSummary(): Collection
    {
        return Kecamatan::withCount([
            'laporan',
            'laporan as menunggu_count' => function ($q) {
                $q->where('status', 'menunggu_verifikasi');
            },
            'laporan as diproses_count' => function ($q) {
                $q->where('status', 'diproses');
            },
            'laporan as selesai_count' => function ($q) {
                $q->where('status', 'selesai');
            },
        ])
            ->orderByDesc('laporan_count')
            ->get()
            ->map(function ($kec) {
                $kec->persentase_selesai = $kec->laporan_count > 0
                    ? round(($kec->selesai_count / $kec->laporan_count) * 100, 1)
                    : 0;

                return $kec;
            });
    }
}
