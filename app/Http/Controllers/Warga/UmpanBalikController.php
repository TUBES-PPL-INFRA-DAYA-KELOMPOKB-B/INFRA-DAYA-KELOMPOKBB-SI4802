<?php

namespace App\Http\Controllers\Warga;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warga\StoreUmpanBalikRequest;
use App\Models\LaporanKerusakan;
use App\Services\UmpanBalikService;
use Illuminate\Http\RedirectResponse;

class UmpanBalikController extends Controller
{
    /**
     * Simpan evaluasi rating dan ulasan kepuasan warga.
     */
    public function store(StoreUmpanBalikRequest $request, LaporanKerusakan $laporan, UmpanBalikService $service): RedirectResponse
    {
        $service->kirimUmpanBalik($laporan, $request->user(), $request->validated());

        return redirect()
            ->route('warga.laporan.show', $laporan)
            ->with('status', 'Terima kasih atas partisipasi Anda! Evaluasi dan ulasan kepuasan perbaikan berhasil disimpan.');
    }
}
