<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KategoriInfrastrukturRequest;
use App\Services\KategoriService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriController extends Controller
{
    public function __construct(protected KategoriService $kategoriService) {}

    public function index(Request $request): View
    {
        $search = $request->query('search');
        $keparahan = $request->query('keparahan') ?? $request->query('tingkat_keparahan');

        $kategoriList = $this->kategoriService->getPaginatedKategori($search, $keparahan);

        return view('admin.kategori.index', compact('kategoriList', 'search', 'keparahan'));
    }

    public function create(): View
    {
        return view('admin.kategori.create');
    }

    public function store(KategoriInfrastrukturRequest $request): RedirectResponse
    {
        $this->kategoriService->createKategori($request->validated());

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', 'Kategori infrastruktur baru berhasil ditambahkan.');
    }

    public function edit(int $id): View
    {
        $kategori = $this->kategoriService->findKategori($id);

        return view('admin.kategori.edit', compact('kategori'));
    }

    public function update(KategoriInfrastrukturRequest $request, int $id): RedirectResponse
    {
        $this->kategoriService->updateKategori($id, $request->validated());

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', 'Data kategori infrastruktur berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->kategoriService->deleteKategori($id);

            return redirect()
                ->route('admin.kategori.index')
                ->with('success', 'Kategori infrastruktur berhasil dihapus.');
        } catch (\DomainException $e) {
            return redirect()
                ->route('admin.kategori.index')
                ->with('error', $e->getMessage());
        }
    }
}
