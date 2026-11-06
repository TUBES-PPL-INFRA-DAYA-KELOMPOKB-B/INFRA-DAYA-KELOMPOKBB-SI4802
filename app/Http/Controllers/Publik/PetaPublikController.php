<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\LaporanKerusakan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PetaPublikController extends Controller
{
    /**
     * Halaman publik peta persebaran kerusakan infrastruktur Kota Bandung.
     */
    public function index(Request $request): View
    {
        $kategoriList = KategoriInfrastruktur::orderBy('nama_kategori')->get();
        $kecamatanList = Kecamatan::orderBy('nama_kecamatan')->get();

        $stats = [
            'total' => LaporanKerusakan::count(),
            'menunggu' => LaporanKerusakan::where('status', 'menunggu_verifikasi')->count(),
            'diproses' => LaporanKerusakan::where('status', 'diproses')->count(),
            'selesai' => LaporanKerusakan::where('status', 'selesai')->count(),
        ];

        return view('publik.peta', compact('kategoriList', 'kecamatanList', 'stats'));
    }

    /**
     * Endpoint API JSON untuk data titik marker peta sebaran.
     */
    public function markers(Request $request): JsonResponse
    {
        $kategoriId = $request->query('kategori_id');
        $status = $request->query('status');
        $kecamatanId = $request->query('kecamatan_id');
        $search = $request->query('search');

        $query = LaporanKerusakan::with(['kelurahan.kecamatan', 'kategori']);

        if ($kategoriId && is_numeric($kategoriId)) {
            $query->where('kategori_id', (int) $kategoriId);
        }

        if ($status && in_array($status, ['menunggu_verifikasi', 'diproses', 'selesai', 'ditolak'], true)) {
            $query->where('status', $status);
        }

        if ($kecamatanId && is_numeric($kecamatanId)) {
            $query->whereHas('kelurahan', function ($q) use ($kecamatanId) {
                $q->where('kecamatan_id', (int) $kecamatanId);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_tiket', 'like', "%{$search}%")
                    ->orWhere('judul', 'like', "%{$search}%")
                    ->orWhere('deskripsi_kondisi', 'like', "%{$search}%");
            });
        }

        // Batasi maksimal 500 titik untuk performa peta yang optimal
        $markers = $query->latest()
            ->limit(500)
            ->get()
            ->map(function ($lap) {
                return [
                    'id' => $lap->id,
                    'nomor_tiket' => $lap->nomor_tiket,
                    'judul' => e($lap->judul),
                    'latitude' => (float) $lap->latitude,
                    'longitude' => (float) $lap->longitude,
                    'status' => $lap->status,
                    'prioritas' => $lap->prioritas,
                    'kategori' => e($lap->kategori->nama_kategori ?? '-'),
                    'kelurahan' => e($lap->kelurahan->nama_kelurahan ?? '-'),
                    'kecamatan' => e($lap->kelurahan->kecamatan->nama_kecamatan ?? '-'),
                    'foto_sebelum' => $lap->foto_bukti_sebelum ? asset('storage/'.$lap->foto_bukti_sebelum) : null,
                    'foto_sesudah' => $lap->foto_bukti_sesudah ? asset('storage/'.$lap->foto_bukti_sesudah) : null,
                    'tanggal' => $lap->created_at ? $lap->created_at->translatedFormat('d M Y') : '-',
                    'url_lacak' => route('tracking.publik', ['nomor_tiket' => $lap->nomor_tiket]),
                ];
            });

        return response()->json([
            'success' => true,
            'count' => $markers->count(),
            'data' => $markers,
        ]);
    }
}
