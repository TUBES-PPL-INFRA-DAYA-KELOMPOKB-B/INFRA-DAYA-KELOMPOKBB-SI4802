<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePetugasRequest;
use App\Http\Requests\Admin\UpdatePetugasRequest;
use App\Services\PetugasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PetugasController extends Controller
{
    public function __construct(protected PetugasService $petugasService) {}

    /**
     * Menampilkan daftar petugas lapangan beserta fitur pencarian dan filter status (AC17-03, AC17-04, AC17-05).
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $petugasList = $this->petugasService->getPaginatedPetugas($search, $status);
        $hasAnyPetugas = $this->petugasService->hasAnyPetugas();
        $metrics = $this->petugasService->getMetrics();

        return view('admin.petugas.index', compact('petugasList', 'search', 'status', 'hasAnyPetugas', 'metrics'));
    }

    /**
     * Menampilkan formulir pendaftaran akun petugas lapangan baru (AC17-06, AC17-07).
     */
    public function create(): View
    {
        return view('admin.petugas.create');
    }

    /**
     * Menyimpan data petugas lapangan baru ke database (AC17-06, AC17-07, AC17-08, AC17-09, AC17-10).
     */
    public function store(StorePetugasRequest $request): RedirectResponse
    {
        $petugas = $this->petugasService->createPetugas($request->validated());

        Log::info("Admin ID ".auth()->id()." berhasil membuat akun petugas ID {$petugas->id} ({$petugas->name})");

        return redirect()
            ->route('admin.petugas.index')
            ->with('success', "Akun petugas lapangan {$petugas->name} berhasil ditambahkan dan siap ditugaskan.");
    }

    /**
     * Menampilkan detail profil dan riwayat penugasan petugas (AC17-18, AC17-21).
     */
    public function show(int $id): View
    {
        $petugas = $this->petugasService->findPetugas($id);

        return view('admin.petugas.show', compact('petugas'));
    }

    /**
     * Menampilkan formulir edit identitas petugas (nama, email, telepon saja) (AC17-11, AC17-21).
     */
    public function edit(int $id): View
    {
        $petugas = $this->petugasService->findPetugas($id);

        return view('admin.petugas.edit', compact('petugas'));
    }

    /**
     * Memperbarui data identitas petugas tanpa mengubah password, role, atau status aktif (AC17-11).
     */
    public function update(UpdatePetugasRequest $request, int $id): RedirectResponse
    {
        $petugas = $this->petugasService->findPetugas($id);

        $this->petugasService->updatePetugas($petugas, $request->validated());

        Log::info("Admin ID ".auth()->id()." memperbarui identitas petugas ID {$petugas->id}");

        return redirect()
            ->route('admin.petugas.show', $petugas->id)
            ->with('success', "Data identitas petugas {$petugas->name} berhasil diperbarui.");
    }

    /**
     * Mengubah status aktif/nonaktif akun petugas dengan konfirmasi dan pemeriksaan tugas berjalan (AC17-13, AC17-14, AC17-17).
     */
    public function toggleStatus(Request $request, int $id): RedirectResponse
    {
        $result = $this->petugasService->toggleStatus($id);

        if (! $result['success']) {
            $blockingTasks = $result['blocking_tasks'];
            $taskList = $blockingTasks->map(function ($task) {
                $tiket = $task->laporan->nomor_tiket ?? ('SPT #'.$task->nomor_surat_tugas);
                $judul = $task->laporan->judul ?? 'Laporan Teknis';
                return "{$tiket} ({$judul})";
            })->implode(', ');

            return redirect()
                ->back()
                ->with('error', "Gagal menonaktifkan petugas: masih terdapat tugas penugasan yang belum selesai: {$taskList}.")
                ->with('blocking_tasks', $blockingTasks);
        }

        return redirect()
            ->back()
            ->with('success', $result['message']);
    }
}

