<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\KecamatanRequest;
use App\Services\WilayahService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KecamatanController extends Controller
{
    public function __construct(protected WilayahService $wilayahService) {}

    public function index(Request $request): View
    {
        $search = $request->query('search');
        $editId = $request->query('edit_id');
        $editingKecamatan = $editId ? $this->wilayahService->findKecamatan((int) $editId) : null;
        $kecamatanList = $this->wilayahService->getPaginatedKecamatan($search);

        return view('admin.wilayah.kecamatan.index', compact('kecamatanList', 'search', 'editingKecamatan'));
    }

    public function create(Request $request): View
    {
        $search = $request->query('search');
        $editingKecamatan = null;
        $kecamatanList = $this->wilayahService->getPaginatedKecamatan($search);

        return view('admin.wilayah.kecamatan.index', compact('kecamatanList', 'search', 'editingKecamatan'));
    }

    public function store(KecamatanRequest $request): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request) {
                $this->wilayahService->createKecamatan($request->validated());
            });

            return redirect()
                ->route('admin.kecamatan.index')
                ->with('success', 'Data kecamatan baru berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.kecamatan.index')
                ->with('error', 'Gagal menyimpan data kecamatan: ' . $e->getMessage());
        }
    }

    public function edit(int $id, Request $request): View
    {
        $search = $request->query('search');
        $editingKecamatan = $this->wilayahService->findKecamatan($id);
        $kecamatanList = $this->wilayahService->getPaginatedKecamatan($search);

        return view('admin.wilayah.kecamatan.index', compact('kecamatanList', 'search', 'editingKecamatan'));
    }

    public function update(KecamatanRequest $request, int $id): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $id) {
                $this->wilayahService->updateKecamatan($id, $request->validated());
            });

            return redirect()
                ->route('admin.kecamatan.index')
                ->with('success', 'Data kecamatan berhasil diperbarui.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404);
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.kecamatan.index')
                ->with('error', 'Gagal memperbarui data kecamatan: ' . $e->getMessage());
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            DB::transaction(function () use ($id) {
                $this->wilayahService->deleteKecamatan($id);
            });

            return redirect()
                ->route('admin.kecamatan.index')
                ->with('success', 'Data kecamatan berhasil dihapus.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404);
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('admin.kecamatan.index')
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.kecamatan.index')
                ->with('error', 'Gagal menghapus kecamatan: ' . $e->getMessage());
        }
    }
}
