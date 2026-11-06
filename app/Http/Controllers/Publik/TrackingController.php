<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\LaporanKerusakan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackingController extends Controller
{
    /**
     * Halaman publik untuk melacak status laporan berdasarkan nomor tiket.
     */
    public function index(Request $request, ?string $nomor_tiket = null): View
    {
        $nomorTiket = trim($nomor_tiket ?: (string) $request->query('nomor_tiket', ''));
        $laporan = null;
        $searched = false;

        if ($nomorTiket !== '') {
            $searched = true;

            $laporan = LaporanKerusakan::with([
                'kelurahan.kecamatan',
                'kategori',
                'riwayatStatus.user',
                'disposisi.petugas',
                'umpanBalik',
            ])
                ->where('nomor_tiket', $nomorTiket)
                ->first();
        }

        return view('publik.tracking', compact('laporan', 'nomorTiket', 'searched'));
    }
}
