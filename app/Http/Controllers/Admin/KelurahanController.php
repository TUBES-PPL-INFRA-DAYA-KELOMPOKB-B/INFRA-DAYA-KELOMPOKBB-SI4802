<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\KelurahanRequest;
use App\Services\WilayahService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KelurahanController extends Controller
{
    public function __construct(protected WilayahService $wilayahService) {}

    public function index(Request $request): View
    {
        $kecamatanId = $request->query('kecamatan_id') ? (int) $request->query('kecamatan_id') : null;
        $search = $request->query('search');
        $editId = $request->query('edit_id');
        $editingKelurahan = $editId ? $this->wilayahService->findKelurahan((int) $editId) : null;

        $kelurahanList = $this->wilayahService->getPaginatedKelurahan($kecamatanId, $search);
        $kecamatanList = $this->wilayahService->getAllKecamatan();

        return view('admin.wilayah.kelurahan.index', compact('kelurahanList', 'kecamatanList', 'kecamatanId', 'search', 'editingKelurahan'));
    }

    public function create(Request $request): View
    {
        $kecamatanId = $request->query('kecamatan_id') ? (int) $request->query('kecamatan_id') : null;
        $search = $request->query('search');
        $editingKelurahan = null;

        $kelurahanList = $this->wilayahService->getPaginatedKelurahan($kecamatanId, $search);
        $kecamatanList = $this->wilayahService->getAllKecamatan();

        return view('admin.wilayah.kelurahan.index', compact('kelurahanList', 'kecamatanList', 'kecamatanId', 'search', 'editingKelurahan'));
    }

    public function store(KelurahanRequest $request): RedirectResponse
    {
        $this->wilayahService->createKelurahan($request->validated());

        return redirect()
            ->route('admin.kelurahan.index')
            ->with('success', 'Data kelurahan baru berhasil ditambahkan.');
    }

    public function edit(int $id, Request $request): View
    {
        $kecamatanId = $request->query('kecamatan_id') ? (int) $request->query('kecamatan_id') : null;
        $search = $request->query('search');
        $editingKelurahan = $this->wilayahService->findKelurahan($id);

        $kelurahanList = $this->wilayahService->getPaginatedKelurahan($kecamatanId, $search);
        $kecamatanList = $this->wilayahService->getAllKecamatan();

        return view('admin.wilayah.kelurahan.index', compact('kelurahanList', 'kecamatanList', 'kecamatanId', 'search', 'editingKelurahan'));
    }

    public function update(KelurahanRequest $request, int $id): RedirectResponse
    {
        $this->wilayahService->updateKelurahan($id, $request->validated());

        return redirect()
            ->route('admin.kelurahan.index')
            ->with('success', 'Data kelurahan berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->wilayahService->deleteKelurahan($id);

            return redirect()
                ->route('admin.kelurahan.index')
                ->with('success', 'Data kelurahan berhasil dihapus.');
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('admin.kelurahan.index')
                ->with('error', $e->getMessage());
        }
    }
}
