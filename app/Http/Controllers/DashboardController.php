<?php

namespace App\Http\Controllers;

use App\Models\LaporanKerusakan;
use App\Services\DashboardSlaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardSlaService $dashboardSlaService
    ) {}

    /**
     * Tampilkan dashboard utama sesuai peran pengguna (Warga, Petugas, Admin, atau Pimpinan).
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // 1. Dashboard Khusus Warga
        if ($user->isWarga()) {
            $stats = [
                'total' => LaporanKerusakan::where('warga_id', $user->id)->count(),
                'menunggu' => LaporanKerusakan::where('warga_id', $user->id)->where('status', 'menunggu_verifikasi')->count(),
                'diproses' => LaporanKerusakan::where('warga_id', $user->id)->where('status', 'diproses')->count(),
                'selesai' => LaporanKerusakan::where('warga_id', $user->id)->where('status', 'selesai')->count(),
            ];

            $recentReports = LaporanKerusakan::with(['kategori', 'kelurahan.kecamatan'])
                ->where('warga_id', $user->id)
                ->latest()
                ->limit(5)
                ->get();

            return view('dashboard', compact('stats', 'recentReports'));
        }

        // 2. Petugas dialihkan langsung ke antrean tugas lapangan jika mengakses dashboard
        if ($user->isPetugas()) {
            return redirect()->route('petugas.tugas.index');
        }

        // 3. Dashboard Eksekutif & Monitoring SLA (Admin & Pimpinan)
        $filterDays = $request->query('days');
        $days = is_numeric($filterDays) ? (int) $filterDays : null;

        $metrics = $this->dashboardSlaService->getExecutiveMetrics($days);
        $monthlyTrend = $this->dashboardSlaService->getMonthlyTrendChartData();
        $categoryDistribution = $this->dashboardSlaService->getCategoryDistributionChartData();
        $topKecamatan = $this->dashboardSlaService->getTopKecamatanChartData();
        $ratingDistribution = $this->dashboardSlaService->getRatingDistributionChartData();
        $kecamatanSummary = $this->dashboardSlaService->getKecamatanPerformanceSummary();

        $recentReports = LaporanKerusakan::with(['warga', 'kategori', 'kelurahan.kecamatan', 'disposisi.petugas'])
            ->latest()
            ->limit(8)
            ->get();

        return view('dashboard', compact(
            'metrics',
            'monthlyTrend',
            'categoryDistribution',
            'topKecamatan',
            'ratingDistribution',
            'kecamatanSummary',
            'recentReports',
            'days'
        ));
    }
}
