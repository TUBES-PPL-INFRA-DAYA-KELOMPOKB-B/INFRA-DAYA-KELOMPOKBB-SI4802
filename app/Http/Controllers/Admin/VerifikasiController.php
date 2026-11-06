<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VerifikasiRequest;
use App\Models\KategoriInfrastruktur;
use App\Services\LaporanService;
use App\Services\WilayahService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifikasiController extends Controller
{
    public function __construct(
        protected LaporanService $laporanService,
        protected WilayahService $wilayahService,
    ) {}

    /**
     * Menampilkan daftar aduan kerusakan infrastruktur dengan filter multi-kriteria (PBI-13).
     */
    public function index(Request $request): View
    {
        $request->validate([
            'tanggal_mulai' => 'nullable|date',
            'tanggal_akhir' => 'nullable|date|after_or_equal:tanggal_mulai',
        ], [
            'tanggal_mulai.date' => 'Format rentang tanggal tidak valid.',
            'tanggal_akhir.date' => 'Format rentang tanggal tidak valid.',
            'tanggal_akhir.after_or_equal' => 'Tanggal awal tidak boleh lebih besar dari tanggal akhir.',
        ]);

        $search = $request->query('search');
        $status = $request->query('status');
        $kategoriId = $request->query('kategori_id');
        $kecamatanId = $request->query('kecamatan_id');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalAkhir = $request->query('tanggal_akhir');

        $laporanList = $this->laporanService->getPaginatedLaporanAdmin(
            $search,
            $status,
            $kategoriId ? (int) $kategoriId : null,
            $kecamatanId ? (int) $kecamatanId : null,
            $tanggalMulai,
            $tanggalAkhir
        );

        $kategoriList = KategoriInfrastruktur::orderBy('nama_kategori')->get();
        $kecamatanList = $this->wilayahService->getAllKecamatan();

        return view('admin.verifikasi.index', compact(
            'laporanList',
            'kategoriList',
            'kecamatanList',
            'search',
            'status',
            'kategoriId',
            'kecamatanId',
            'tanggalMulai',
            'tanggalAkhir'
        ));
    }

    /**
     * Menampilkan detail laporan kerusakan & bukti untuk verifikasi (PBI-14).
     */
    public function show(int $id): View
    {
        $laporan = $this->laporanService->findLaporan($id);

        return view('admin.verifikasi.show', compact('laporan'));
    }

    /**
     * Aksi terima aduan: validasi dan setujui laporan ke status diproses (PBI-14).
     */
    public function setujui(VerifikasiRequest $request, int $id): RedirectResponse
    {
        try {
            $laporan = $this->laporanService->setujuiLaporan(
                $id,
                auth()->id(),
                $request->validated('prioritas'),
                $request->validated('catatan_verifikator')
            );

            return redirect()
                ->route('admin.verifikasi.show', $id)
                ->with('success', "Laporan {$laporan->nomor_tiket} berhasil disetujui dan dialihkan ke status Diproses.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Aksi tolak aduan: menolak laporan dengan catatan alasan penolakan wajib (PBI-14).
     */
    public function tolak(VerifikasiRequest $request, int $id): RedirectResponse
    {
        try {
            $laporan = $this->laporanService->tolakLaporan(
                $id,
                auth()->id(),
                $request->validated('alasan_penolakan')
            );

            return redirect()
                ->route('admin.verifikasi.index')
                ->with('success', "Laporan {$laporan->nomor_tiket} berhasil ditolak dengan alasan yang telah dicatat.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
