<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DisposisiPenugasan;
use Illuminate\Http\Request;

class SuratTugasController extends Controller
{
    public function index(Request $request)
    {
        return app(DisposisiController::class)->index($request);
    }

    public function show(int $id)
    {
        $disposisi = DisposisiPenugasan::with(['petugas', 'laporan.kecamatan', 'laporan.kelurahan', 'laporan.kategori'])->findOrFail($id);

        if (view()->exists('admin.surat-tugas.show')) {
            return view('admin.surat-tugas.show', compact('disposisi'));
        }

        return app(DisposisiController::class)->show($id);
    }

    public function pdf(int $id, Request $request)
    {
        return app(DisposisiController::class)->cetakPdf($id, $request);
    }

    public function cetak(int $id, Request $request)
    {
        $disposisi = DisposisiPenugasan::with(['petugas', 'laporan.kecamatan', 'laporan.kelurahan', 'laporan.kategori'])->findOrFail($id);

        if (view()->exists('admin.surat-tugas.cetak')) {
            return view('admin.surat-tugas.cetak', compact('disposisi'));
        }

        return app(DisposisiController::class)->show($id);
    }
}
