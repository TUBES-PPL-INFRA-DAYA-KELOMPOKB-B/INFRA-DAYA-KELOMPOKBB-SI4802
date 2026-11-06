<?php

namespace App\Http\Controllers\Warga;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warga\LaporanKerusakanRequest;
use App\Models\LaporanKerusakan;
use App\Services\KategoriService;
use App\Services\LaporanService;
use App\Services\WilayahService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function __construct(
        protected LaporanService $laporanService,
        protected WilayahService $wilayahService,
        protected KategoriService $kategoriService,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $laporanList = $this->laporanService->getPaginatedLaporanWarga(auth()->id(), $status);

        return view('warga.laporan.index', compact('laporanList', 'status'));
    }

    public function create(): View
    {
        $kecamatanList = $this->wilayahService->getAllKecamatan();
        $kategoriList = $this->kategoriService->getAllKategori();

        return view('warga.laporan.create', compact('kecamatanList', 'kategoriList'));
    }

    public function store(LaporanKerusakanRequest $request): RedirectResponse
    {
        $laporan = $this->laporanService->createLaporan(
            auth()->id(),
            $request->validated(),
            $request->file('foto_bukti_sebelum')
        );

        return redirect()
            ->route('warga.laporan.show', $laporan->id)
            ->with('success', "Laporan kerusakan berhasil dikirim dengan Nomor Tiket: {$laporan->nomor_tiket}.");
    }

    public function show(LaporanKerusakan $laporan): View
    {
        Gate::authorize('view', $laporan);

        $laporan->load([
            'kelurahan.kecamatan',
            'kategori',
            'riwayatStatus.user',
            'disposisi.petugas',
            'umpanBalik',
        ]);

        return view('warga.laporan.show', compact('laporan'));
    }

    public function edit(LaporanKerusakan $laporan): View
    {
        Gate::authorize('update', $laporan);

        $kecamatanList = $this->wilayahService->getAllKecamatan();
        $kategoriList = $this->kategoriService->getAllKategori();

        return view('warga.laporan.edit', compact('laporan', 'kecamatanList', 'kategoriList'));
    }

    public function update(LaporanKerusakanRequest $request, LaporanKerusakan $laporan): RedirectResponse
    {
        Gate::authorize('update', $laporan);

        $this->laporanService->updateLaporan(
            $laporan,
            $request->validated(),
            $request->file('foto_bukti_sebelum')
        );

        return redirect()
            ->route('warga.laporan.show', $laporan->id)
            ->with('success', 'Perubahan laporan kerusakan berhasil disimpan.');
    }

    public function destroy(LaporanKerusakan $laporan): RedirectResponse
    {
        Gate::authorize('delete', $laporan);

        $nomorTiket = $laporan->nomor_tiket;
        $this->laporanService->deleteLaporan($laporan);

        return redirect()
            ->route('warga.laporan.saya')
            ->with('success', "Laporan {$nomorTiket} berhasil dibatalkan.");
    }
}
