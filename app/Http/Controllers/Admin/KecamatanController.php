<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\KecamatanRequest;
use App\Services\WilayahService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KecamatanController extends Controller
{
    public function __construct(protected WilayahService $wilayahService) {}

    public function index(Request $request): View
    {
        $search = $request->query('search');
        $kecamatanList = $this->wilayahService->getPaginatedKecamatan($search);

        return view('admin.wilayah.kecamatan.index', compact('kecamatanList', 'search'));
    }

    public function create(): View
    {
        return view('admin.wilayah.kecamatan.create');
    }

    public function store(KecamatanRequest $request): RedirectResponse
    {
        $this->wilayahService->createKecamatan($request->validated());

        return redirect()
            ->route('admin.kecamatan.index')
            ->with('success', 'Data kecamatan baru berhasil ditambahkan.');
    }

    public function edit(int $id): View
    {
        $kecamatan = $this->wilayahService->findKecamatan($id);

        return view('admin.wilayah.kecamatan.edit', compact('kecamatan'));
    }

    public function update(KecamatanRequest $request, int $id): RedirectResponse
    {
        $this->wilayahService->updateKecamatan($id, $request->validated());

        return redirect()
            ->route('admin.kecamatan.index')
            ->with('success', 'Data kecamatan berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->wilayahService->deleteKecamatan($id);

            return redirect()
                ->route('admin.kecamatan.index')
                ->with('success', 'Data kecamatan berhasil dihapus.');
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('admin.kecamatan.index')
                ->with('error', $e->getMessage());
        }
    }
}
