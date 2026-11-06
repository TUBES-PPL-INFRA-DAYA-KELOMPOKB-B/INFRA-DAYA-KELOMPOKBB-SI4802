<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriInfrastruktur;
use App\Services\LaporanService;
use App\Services\WilayahService;
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
}
