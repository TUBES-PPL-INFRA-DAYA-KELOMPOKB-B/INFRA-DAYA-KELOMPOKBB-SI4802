<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDisposisiRequest;
use App\Models\DisposisiPenugasan;
use App\Models\LaporanKerusakan;
use App\Services\DisposisiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class DisposisiController extends Controller
{
    public function __construct(
        protected DisposisiService $disposisiService,
    ) {}

    /**
     * Menampilkan daftar riwayat disposisi penugasan petugas.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $statusPenugasan = $request->query('status_penugasan');
        $petugasId = $request->query('petugas_id') ? (int) $request->query('petugas_id') : null;

        $disposisiList = $this->disposisiService->getPaginatedDisposisi(
            $search,
            $statusPenugasan,
            $petugasId
        );

        $petugasList = $this->disposisiService->getPetugasAktif();

        // Metrik ringkasan
        $totalDisposisi = DisposisiPenugasan::count();
        $totalDitugaskan = DisposisiPenugasan::where('status_penugasan', 'ditugaskan')->count();
        $totalSedangDikerjakan = DisposisiPenugasan::where('status_penugasan', 'sedang_dikerjakan')->count();
        $totalSelesai = DisposisiPenugasan::where('status_penugasan', 'selesai')->count();
        $totalSiapDisposisi = LaporanKerusakan::siapDisposisi()->count();

        return view('admin.disposisi.index', compact(
            'disposisiList',
            'petugasList',
            'search',
            'statusPenugasan',
            'petugasId',
            'totalDisposisi',
            'totalDitugaskan',
            'totalSedangDikerjakan',
            'totalSelesai',
            'totalSiapDisposisi'
        ));
    }

    /**
     * Form penerbitan Surat Perintah Tugas (Disposisi).
     */
    public function create(Request $request): View
    {
        $selectedLaporanId = $request->query('laporan_id') ? (int) $request->query('laporan_id') : null;
        $laporanList = $this->disposisiService->getLaporanSiapDisposisi();
        $petugasList = $this->disposisiService->getPetugasAktif();

        $selectedLaporan = null;
        if ($selectedLaporanId) {
            $selectedLaporan = LaporanKerusakan::with(['kelurahan.kecamatan', 'kategori', 'warga'])
                ->where('id', $selectedLaporanId)
                ->where('status', 'diproses')
                ->first();
        }

        return view('admin.disposisi.create', compact(
            'laporanList',
            'petugasList',
            'selectedLaporanId',
            'selectedLaporan'
        ));
    }

    /**
     * Simpan data disposisi penugasan baru.
     */
    public function store(StoreDisposisiRequest $request): RedirectResponse
    {
        try {
            $disposisi = $this->disposisiService->createDisposisi(
                (int) $request->validated('laporan_id'),
                (int) auth()->id(),
                (int) $request->validated('petugas_id'),
                $request->validated('tenggat_waktu'),
                $request->validated('catatan_instruksi'),
                $request->validated('tanggal_disposisi')
            );

            return redirect()
                ->route('admin.disposisi.show', $disposisi->id)
                ->with('success', "Surat Tugas {$disposisi->nomor_surat_tugas} berhasil diterbitkan dan ditugaskan kepada petugas.");
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Menampilkan detail Surat Perintah Tugas (SPT).
     */
    public function show(int $id): View
    {
        $disposisi = $this->disposisiService->findDisposisi($id);

        return view('admin.disposisi.show', compact('disposisi'));
    }

    /**
     * Unduh atau cetak dokumen resmi SPT dalam format PDF.
     */
    public function cetakPdf(int $id, Request $request): Response
    {
        $disposisi = $this->disposisiService->findDisposisi($id);

        $pdf = Pdf::loadView('admin.disposisi.pdf', compact('disposisi'))
            ->setPaper('a4', 'portrait');

        $filename = "SPT-{$disposisi->nomor_surat_tugas}.pdf";

        if ($request->query('download') === '1') {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}
