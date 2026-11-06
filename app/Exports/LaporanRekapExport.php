<?php

namespace App\Exports;

use App\Models\LaporanKerusakan;
use App\Services\RekapService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanRekapExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected array $filters;

    protected RekapService $rekapService;

    protected int $rowNumber = 0;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(array $filters = [], ?RekapService $rekapService = null)
    {
        $this->filters = $filters;
        $this->rekapService = $rekapService ?? app(RekapService::class);
    }

    /**
     * @return Collection<int, LaporanKerusakan>
     */
    public function collection(): Collection
    {
        return $this->rekapService->getCollection($this->filters);
    }

    /**
     * Judul sheet pada file Excel.
     */
    public function title(): string
    {
        return 'Rekap Laporan Infrastruktur';
    }

    /**
     * Baris heading kolom Excel.
     *
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'No',
            'Nomor Tiket',
            'Tanggal Lapor',
            'Nama Pelapor',
            'Kontak Pelapor',
            'Kategori Infrastruktur',
            'Judul Laporan',
            'Kelurahan',
            'Kecamatan',
            'Koordinat',
            'Status',
            'Prioritas',
            'Petugas Lapangan',
            'Tanggal Selesai',
            'Rating Kepuasan',
            'Ulasan Warga',
        ];
    }

    /**
     * Transformasi setiap baris model LaporanKerusakan ke data kolom Excel.
     *
     * @param  LaporanKerusakan  $laporan
     * @return array<int, mixed>
     */
    public function map($laporan): array
    {
        $this->rowNumber++;

        $statusLabels = [
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'diproses' => 'Diproses',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
        ];

        $prioritasLabels = [
            'rendah' => 'Rendah',
            'sedang' => 'Sedang',
            'tinggi' => 'Tinggi',
            'darurat' => 'Darurat',
        ];

        $koordinat = ($laporan->latitude && $laporan->longitude)
            ? "{$laporan->latitude}, {$laporan->longitude}"
            : '-';

        $petugasName = $laporan->disposisi?->petugas?->name ?? '-';
        $tanggalSelesai = $laporan->selesai_at ? $laporan->selesai_at->format('d/m/Y H:i') : '-';
        $rating = $laporan->umpanBalik ? "{$laporan->umpanBalik->rating} / 5" : '-';
        $ulasan = $laporan->umpanBalik?->komentar ?? '-';

        return [
            $this->rowNumber,
            $laporan->nomor_tiket,
            $laporan->created_at ? $laporan->created_at->format('d/m/Y H:i') : '-',
            $laporan->warga?->name ?? 'Anonim',
            $laporan->warga?->phone ?? $laporan->warga?->email ?? '-',
            $laporan->kategori?->nama_kategori ?? '-',
            $laporan->judul,
            $laporan->kelurahan?->nama_kelurahan ?? '-',
            $laporan->kelurahan?->kecamatan?->nama_kecamatan ?? '-',
            $koordinat,
            $statusLabels[$laporan->status] ?? ucfirst($laporan->status),
            $prioritasLabels[$laporan->prioritas] ?? ($laporan->prioritas ? ucfirst($laporan->prioritas) : '-'),
            $petugasName,
            $tanggalSelesai,
            $rating,
            $ulasan,
        ];
    }

    /**
     * Styling spreadsheet (Header styling, borders, alignment).
     */
    public function styles(Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Style Baris Header (Row 1)
        $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F3A5F'], // Brand Navy DSDABM
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(32);

        // Border & Alignment untuk seluruh sel data
        if ($highestRow > 1) {
            $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CBD5E1'],
                    ],
                ],
            ]);

            // Center align kolom No, Nomor Tiket, Tanggal Lapor, Koordinat, Status, Prioritas, Tgl Selesai, Rating
            $sheet->getStyle("A2:C{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J2:L{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("N2:O{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return [];
    }
}
