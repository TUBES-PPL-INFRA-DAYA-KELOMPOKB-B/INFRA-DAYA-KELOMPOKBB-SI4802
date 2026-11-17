<?php

namespace App\Services;

use App\Models\DisposisiPenugasan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;

class SuratTugasService
{
    /**
     * Menghasilkan dokumen resmi Surat Perintah Tugas (SPT) dalam format PDF ukuran A4 portrait.
     */
    public function generatePdf(DisposisiPenugasan $disposisi, ?Carbon $waktuDokumen = null)
    {
        $waktuDokumen = $waktuDokumen ?? Carbon::now();

        return Pdf::loadView('admin.surat-tugas.pdf', compact('disposisi', 'waktuDokumen'))
            ->setPaper('a4', 'portrait');
    }
}
