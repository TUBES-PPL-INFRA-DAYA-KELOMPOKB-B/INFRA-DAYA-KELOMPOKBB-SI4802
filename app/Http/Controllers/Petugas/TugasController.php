<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Petugas\SelesaikanTugasRequest;
use App\Http\Requests\Petugas\UpdateProgresRequest;
use App\Services\PetugasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TugasController extends Controller
{
    public function __construct(
        protected PetugasService $petugasService,
    ) {}

    /**
     * Menampilkan daftar tugas penugasan lapangan milik petugas login.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');
        $sort = $request->query('sort');
        $petugasId = (int) auth()->id();

        $tugasList = $this->petugasService->getPaginatedTugas(
            $petugasId,
            $status === 'semua' ? null : $status,
            $search,
            $sort
        );

        $stats = $this->petugasService->getStatistikTugas($petugasId);

        return view('petugas.tugas.index', compact('tugasList', 'stats', 'status', 'search', 'sort'));
    }

    /**
     * Menampilkan lembar rincian tugas lapangan, peta koordinat, dan navigasi GPS.
     */
    public function show(int $id): View
    {
        $disposisi = $this->petugasService->getTugasDetail($id, (int) auth()->id());

        return view('petugas.tugas.show', compact('disposisi'));
    }

    /**
     * Menerima tugas penugasan oleh petugas lapangan (FR-19.8 / PBI-19).
     */
    public function acceptTask(int $id): RedirectResponse
    {
        $disposisi = $this->petugasService->acceptTask($id, (int) auth()->id());

        return redirect()
            ->route('petugas.tugas.show', $disposisi->id)
            ->with('success', 'Tugas perbaikan berhasil diterima. Status penugasan kini menjadi Diterima.');
    }

    /**
     * Memulai penanganan pekerjaan di lapangan.
     */
    public function mulai(int $id): RedirectResponse
    {
        $disposisi = $this->petugasService->mulaiPengerjaan($id, (int) auth()->id());

        return redirect()
            ->route('petugas.tugas.show', $disposisi->id)
            ->with('success', 'Status tugas diperbarui: Penanganan fisik di lokasi telah dimulai.');
    }

    /**
     * Memperbarui progres pekerjaan, catatan perbaikan, dan/atau foto hasil (PBI-20 / FR-20.1 - FR-20.10).
     */
    public function updateProgres(UpdateProgresRequest $request, int $id): RedirectResponse
    {
        $disposisi = $this->petugasService->updateProgres(
            $id,
            (int) auth()->id(),
            $request->validated('catatan_perbaikan'),
            $request->file('foto_bukti_sesudah'),
            $request->validated('status_penugasan')
        );

        return redirect()
            ->route('petugas.tugas.show', $disposisi->id)
            ->with('success', 'Progres penanganan tugas berhasil diperbarui.');
    }

    /**
     * Menyelesaikan tugas dengan mengunggah bukti perbaikan dan catatan teknis (PBI-21 / FR-21.1 - FR-21.7).
     */
    public function selesai(SelesaikanTugasRequest $request, int $id): RedirectResponse
    {
        $disposisi = $this->petugasService->selesaikanTugas(
            $id,
            (int) auth()->id(),
            $request->file('foto_bukti_sesudah'),
            $request->validated('catatan_perbaikan')
        );

        return redirect()
            ->route('petugas.tugas.show', $disposisi->id)
            ->with('success', 'Tugas perbaikan lapangan berhasil diselesaikan. Status laporan kini menjadi Selesai.');
    }

    /**
     * Aksi completeTask sesuai penamaan spesifikasi FR-21.1 pada PRD.
     */
    public function completeTask(SelesaikanTugasRequest $request, int $id): RedirectResponse
    {
        return $this->selesai($request, $id);
    }
}
