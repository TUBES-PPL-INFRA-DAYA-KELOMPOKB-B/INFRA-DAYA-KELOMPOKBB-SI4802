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
        $searchLaporan = $request->query('search_laporan', $search);
        $statusPenugasan = $request->query('status_penugasan');
        $petugasId = $request->query('petugas_id') ? (int) $request->query('petugas_id') : null;

        $disposisiList = $this->disposisiService->getPaginatedDisposisi(
            $search,
            $statusPenugasan,
            $petugasId
        );

        $laporanSiapDisposisi = $this->disposisiService->getLaporanSiapDisposisi($searchLaporan);
        $petugasList = $this->disposisiService->getPetugasAktif();

        // Metrik ringkasan
        $totalDisposisi = DisposisiPenugasan::count();
        $totalDitugaskan = DisposisiPenugasan::where('status_penugasan', 'ditugaskan')->count();
        $totalSedangDikerjakan = DisposisiPenugasan::where('status_penugasan', 'sedang_dikerjakan')->count();
        $totalSelesai = DisposisiPenugasan::where('status_penugasan', 'selesai')->count();
        $totalSiapDisposisi = LaporanKerusakan::siapDisposisi()->count();

        return view('admin.disposisi.index', compact(
            'disposisiList',
            'laporanSiapDisposisi',
            'petugasList',
            'search',
            'searchLaporan',
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
            // AC16-10: Input bisnis yang dipakai hanya laporan_id, petugas_id, dan catatan_instruksi.
            // AC16-11: Admin, waktu, dan status awal ditentukan server; manipulasi nilai klien diabaikan.
            $disposisi = $this->disposisiService->createDisposisi(
                (int) $request->input('laporan_id'),
                (int) auth()->id(),
                (int) $request->input('petugas_id'),
                $request->input('tenggat_waktu'),
                (string) $request->input('catatan_instruksi')
            );

            return redirect()
                ->route('admin.disposisi.show', $disposisi->id)
                ->with('success', "Surat Tugas {$disposisi->nomor_surat_tugas} berhasil diterbitkan dan ditugaskan kepada petugas.");
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan disposisi: ' . $e->getMessage());
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
        $waktuDokumen = \Illuminate\Support\Carbon::now();

        $pdf = Pdf::loadView('admin.surat-tugas.pdf', compact('disposisi', 'waktuDokumen'))
            ->setPaper('a4', 'portrait');

        $filename = "SPT-{$disposisi->nomor_surat_tugas}.pdf";

        if ($request->query('download') === '1') {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}
