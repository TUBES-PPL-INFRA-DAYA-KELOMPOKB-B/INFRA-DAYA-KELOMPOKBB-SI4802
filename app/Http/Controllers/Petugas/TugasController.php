<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Petugas\SelesaikanTugasRequest;
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
        $petugasId = (int) auth()->id();

        $tugasList = $this->petugasService->getPaginatedTugas(
            $petugasId,
            $status === 'semua' ? null : $status,
            $search
        );

        $stats = $this->petugasService->getStatistikTugas($petugasId);

        return view('petugas.tugas.index', compact('tugasList', 'stats', 'status', 'search'));
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
     * Menyelesaikan tugas dengan mengunggah bukti perbaikan dan catatan teknis.
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
}
