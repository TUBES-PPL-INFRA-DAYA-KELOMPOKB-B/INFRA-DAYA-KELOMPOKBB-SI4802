<?php

namespace App\Http\Controllers\Admin;

use App\Exports\LaporanRekapExport;
use App\Http\Controllers\Controller;
use App\Services\RekapService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RekapController extends Controller
{
    public function __construct(
        protected RekapService $rekapService
    ) {}

    /**
     * Menampilkan antarmuka rekapitulasi data dengan filter multi-kriteria dan pratinjau tabel.
     */
    public function index(Request $request): View
    {
        $filters = $this->extractFilters($request);

        $stats = $this->rekapService->getSummaryStats($filters);
        $laporanList = $this->rekapService->getPaginated($filters, 15);
        $filterOptions = $this->rekapService->getFilterOptions();

        $isPimpinan = $request->routeIs('pimpinan.*');
        $exportExcelRoute = $isPimpinan ? route('pimpinan.rekap.excel', $filters) : route('admin.rekap.excel', $filters);
        $exportPdfRoute = $isPimpinan ? route('pimpinan.rekap.pdf', $filters) : route('admin.rekap.pdf', $filters);
        $resetRoute = $isPimpinan ? route('pimpinan.rekap.index') : route('admin.rekap.index');

        return view('admin.rekap.index', array_merge([
            'filters' => $filters,
            'stats' => $stats,
            'laporanList' => $laporanList,
            'isPimpinan' => $isPimpinan,
            'exportExcelRoute' => $exportExcelRoute,
            'exportPdfRoute' => $exportPdfRoute,
            'resetRoute' => $resetRoute,
        ], $filterOptions));
    }

    /**
     * Mengunduh rekapitulasi data dalam format Microsoft Excel (.xlsx).
     */
    public function exportExcel(Request $request): BinaryFileResponse
    {
        $filters = $this->extractFilters($request);
        $timestamp = now()->format('Ymd_His');
        $filename = "rekap-laporan-dsdabm-{$timestamp}.xlsx";

        return Excel::download(new LaporanRekapExport($filters, $this->rekapService), $filename);
    }

    /**
     * Menghasilkan dokumen laporan resmi dalam format PDF (A4 Landscape).
     */
    public function exportPdf(Request $request): Response
    {
        $filters = $this->extractFilters($request);
        $laporanList = $this->rekapService->getCollection($filters);
        $stats = $this->rekapService->getSummaryStats($filters);

        $filterLabels = $this->resolveFilterLabels($filters);
        $tanggalCetak = now()->translatedFormat('d F Y');
        $timestamp = now()->format('Ymd_His');
        $filename = "rekap-laporan-dsdabm-{$timestamp}.pdf";

        $pdf = Pdf::loadView('admin.rekap.pdf', compact('laporanList', 'stats', 'filters', 'filterLabels', 'tanggalCetak'))
            ->setPaper('a4', 'landscape');

        if ($request->query('download') === '1') {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    /**
     * Ekstraksi dan sanitasi query filter request.
     *
     * @return array<string, mixed>
     */
    protected function extractFilters(Request $request): array
    {
        return [
            'tanggal_mulai' => $request->query('tanggal_mulai') ?: null,
            'tanggal_akhir' => $request->query('tanggal_akhir') ?: null,
            'kecamatan_id' => $request->query('kecamatan_id') ? (int) $request->query('kecamatan_id') : null,
            'kategori_id' => $request->query('kategori_id') ? (int) $request->query('kategori_id') : null,
            'status' => $request->query('status') ?: null,
            'prioritas' => $request->query('prioritas') ?: null,
            'search' => $request->query('search') ? trim($request->query('search')) : null,
        ];
    }

    /**
     * Menerjemahkan id filter ke nama teks yang ramah dicetak pada kop PDF.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, string>
     */
    protected function resolveFilterLabels(array $filters): array
    {
        $options = $this->rekapService->getFilterOptions();

        $namaKecamatan = 'Semua Kecamatan';
        if (! empty($filters['kecamatan_id'])) {
            $kec = $options['kecamatanList']->firstWhere('id', $filters['kecamatan_id']);
            if ($kec) {
                $namaKecamatan = $kec->nama_kecamatan;
            }
        }

        $namaKategori = 'Semua Kategori';
        if (! empty($filters['kategori_id'])) {
            $kat = $options['kategoriList']->firstWhere('id', $filters['kategori_id']);
            if ($kat) {
                $namaKategori = $kat->nama_kategori;
            }
        }

        $periode = 'Seluruh Periode';
        if (! empty($filters['tanggal_mulai']) && ! empty($filters['tanggal_akhir'])) {
            $periode = "{$filters['tanggal_mulai']} s.d. {$filters['tanggal_akhir']}";
        } elseif (! empty($filters['tanggal_mulai'])) {
            $periode = "Mulai {$filters['tanggal_mulai']}";
        } elseif (! empty($filters['tanggal_akhir'])) {
            $periode = "Hingga {$filters['tanggal_akhir']}";
        }

        return [
            'periode' => $periode,
            'kecamatan' => $namaKecamatan,
            'kategori' => $namaKategori,
            'status' => $filters['status'] ? ucfirst(str_replace('_', ' ', $filters['status'])) : 'Semua Status',
            'prioritas' => $filters['prioritas'] ? ucfirst($filters['prioritas']) : 'Semua Prioritas',
        ];
    }
}
