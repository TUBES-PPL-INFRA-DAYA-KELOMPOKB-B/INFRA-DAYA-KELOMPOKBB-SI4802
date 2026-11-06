<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Pengaduan Infrastruktur - DSDABM Kota Bandung</title>
    <style>
        @page {
            margin: 12mm 15mm 15mm 15mm;
            size: a4 landscape;
        }
        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            line-height: 1.35;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .kop-text {
            text-align: center;
        }
        .kop-instansi {
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #000;
            margin: 0;
        }
        .kop-dinas {
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #1F3A5F;
            margin: 2px 0;
        }
        .kop-alamat {
            font-size: 7.5pt;
            color: #4b5563;
            margin: 1px 0;
        }
        .garis-kop {
            border-top: 2px solid #000;
            border-bottom: 0.8px solid #000;
            height: 1.5px;
            margin-top: 4px;
            margin-bottom: 12px;
        }
        .judul-dokumen {
            text-align: center;
            margin-bottom: 10px;
        }
        .judul-dokumen h2 {
            font-size: 11pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #111827;
        }
        .judul-dokumen p {
            font-size: 8pt;
            color: #4b5563;
            margin: 3px 0 0 0;
        }
        .info-filter-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8pt;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .info-filter-table td {
            padding: 4px 8px;
            vertical-align: top;
        }
        .stat-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 7.5pt;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 7.5pt;
        }
        .table-data th {
            background-color: #1F3A5F;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7pt;
            letter-spacing: 0.3px;
            padding: 6px 4px;
            border: 1px solid #0f172a;
            text-align: center;
        }
        .table-data td {
            padding: 5px 4px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }
        .table-data tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: monospace; }
        .font-bold { font-weight: bold; }
        
        .badge-status {
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
        }
        .status-selesai { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .status-diproses { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .status-menunggu { background-color: #e0f2fe; color: #075985; border: 1px solid #bae6fd; }
        .status-ditolak { background-color: #ffe4e6; color: #9f1239; border: 1px solid #fecdd3; }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .signature-table td {
            vertical-align: top;
            font-size: 8pt;
        }
    </style>
</head>
<body>
    <!-- Kop Resmi Pemerintah Kota Bandung -->
    <table class="header-table">
        <tr>
            <td style="width: 70px; text-align: center;">
                <div style="width: 55px; height: 55px; line-height: 55px; border-radius: 8px; background-color: #1F3A5F; color: #fff; font-weight: bold; font-size: 16pt; margin: 0 auto; text-align: center;">
                    BDG
                </div>
            </td>
            <td class="kop-text">
                <div class="kop-instansi">Pemerintah Kota Bandung</div>
                <div class="kop-dinas">Dinas Sumber Daya Air dan Bina Marga (DSDABM)</div>
                <div class="kop-alamat">
                    Jl. Cianjur No. 34, Kota Bandung, Jawa Barat 40271 | Telp: (022) 7278809 | Pos-el: dsdabm@bandung.go.id
                </div>
                <div class="kop-alamat">
                    Sistem Pelaporan Kerusakan Infrastruktur Terintegrasi (INFRA-DAYA)
                </div>
            </td>
            <td style="width: 70px; text-align: right; font-size: 7pt; color: #64748b;">
                Lembar: 1<br>
                A4 Landscape
            </td>
        </tr>
    </table>

    <div class="garis-kop"></div>

    <!-- Judul Dokumen -->
    <div class="judul-dokumen">
        <h2>Laporan Rekapitulasi Pengaduan Kerusakan Infrastruktur</h2>
        <p>Dokumen resmi rekapitulasi data pengaduan masyarakat yang terverifikasi dan ditangani oleh DSDABM Kota Bandung</p>
    </div>

    <!-- Informasi Parameter & Ringkasan Metrik Filter -->
    <table class="info-filter-table">
        <tr>
            <td style="width: 15%; font-weight: bold; color: #475569;">Periode Laporan:</td>
            <td style="width: 35%; font-weight: bold; color: #1e293b;">{{ $filterLabels['periode'] }}</td>
            <td style="width: 15%; font-weight: bold; color: #475569;">Total Pengaduan:</td>
            <td style="width: 35%; font-weight: bold; color: #1F3A5F;">{{ number_format($stats['total']) }} Laporan</td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #475569;">Wilayah Kecamatan:</td>
            <td>{{ $filterLabels['kecamatan'] }}</td>
            <td style="font-weight: bold; color: #475569;">Selesai Ditangani:</td>
            <td style="color: #065f46; font-weight: bold;">
                {{ number_format($stats['selesai']) }} Laporan ({{ $stats['total'] > 0 ? round(($stats['selesai'] / $stats['total']) * 100) : 0 }}%)
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #475569;">Kategori / Status:</td>
            <td>{{ $filterLabels['kategori'] }} • {{ $filterLabels['status'] }}</td>
            <td style="font-weight: bold; color: #475569;">Dalam Proses / Menunggu:</td>
            <td>{{ number_format($stats['diproses']) }} Diproses / {{ number_format($stats['menunggu']) }} Menunggu</td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #475569;">Tingkat Prioritas:</td>
            <td>{{ $filterLabels['prioritas'] }}</td>
            <td style="font-weight: bold; color: #475569;">Indeks Kepuasan:</td>
            <td style="font-weight: bold; color: #d97706;">{{ $stats['avg_rating'] ?: '0.0' }} / 5.0</td>
        </tr>
    </table>

    <!-- Tabel Data Rekapitulasi -->
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 75px;">No. Tiket</th>
                <th style="width: 55px;">Tgl Lapor</th>
                <th style="width: 85px;">Pelapor</th>
                <th style="width: 75px;">Kategori</th>
                <th>Judul & Lokasi Kerusakan</th>
                <th style="width: 75px;">Wilayah</th>
                <th style="width: 45px;">Prioritas</th>
                <th style="width: 60px;">Status</th>
                <th style="width: 80px;">Petugas Lapangan</th>
                <th style="width: 55px;">Tgl Selesai</th>
                <th style="width: 35px;">Rating</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporanList as $index => $item)
                @php
                    $statusClass = match($item->status) {
                        'selesai' => 'status-selesai',
                        'diproses' => 'status-diproses',
                        'menunggu_verifikasi' => 'status-menunggu',
                        'ditolak' => 'status-ditolak',
                        default => 'status-menunggu',
                    };
                    $statusLabel = match($item->status) {
                        'selesai' => 'Selesai',
                        'diproses' => 'Diproses',
                        'menunggu_verifikasi' => 'Menunggu',
                        'ditolak' => 'Ditolak',
                        default => ucfirst($item->status),
                    };
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-mono text-center font-bold" style="color: #0E7C86;">
                        {{ $item->nomor_tiket }}
                    </td>
                    <td class="text-center">
                        {{ $item->created_at->format('d/m/Y') }}
                    </td>
                    <td>
                        <div class="font-bold">{{ $item->warga?->name ?? 'Anonim' }}</div>
                        <div style="font-size: 6.5pt; color: #64748b;">{{ $item->warga?->phone ?? $item->warga?->email ?? '-' }}</div>
                    </td>
                    <td>{{ $item->kategori?->nama_kategori ?? '-' }}</td>
                    <td>
                        <div class="font-bold">{{ $item->judul }}</div>
                        <div style="font-size: 6.5pt; color: #64748b; margin-top: 1px;">
                            {{ \Illuminate\Support\Str::limit($item->deskripsi_kondisi, 80) }}
                        </div>
                    </td>
                    <td>
                        <div>Kel. {{ $item->kelurahan?->nama_kelurahan ?? '-' }}</div>
                        <div style="font-size: 6.5pt; color: #64748b;">Kec. {{ $item->kelurahan?->kecamatan?->nama_kecamatan ?? '-' }}</div>
                    </td>
                    <td class="text-center font-bold" style="font-size: 7pt;">
                        {{ $item->prioritas ? ucfirst($item->prioritas) : '-' }}
                    </td>
                    <td class="text-center">
                        <span class="badge-status {{ $statusClass }}">{{ $statusLabel }}</span>
                    </td>
                    <td>
                        @if($item->disposisi && $item->disposisi->petugas)
                            <div class="font-bold">{{ $item->disposisi->petugas->name }}</div>
                            <div style="font-size: 6pt; color: #64748b;">{{ $item->disposisi->nomor_surat_tugas }}</div>
                        @else
                            <span style="color: #94a3b8; font-style: italic;">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        {{ $item->selesai_at ? $item->selesai_at->format('d/m/Y') : '-' }}
                    </td>
                    <td class="text-center font-bold">
                        {{ $item->umpanBalik ? "{$item->umpanBalik->rating}★" : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="padding: 18px; color: #64748b; font-style: italic;">
                        Tidak ada catatan laporan kerusakan infrastruktur yang sesuai dengan filter yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan Pengesahan Pejabat -->
    <table class="signature-table">
        <tr>
            <td style="width: 65%;">
                <div style="font-size: 7pt; color: #64748b; margin-top: 10px;">
                    * Dokumen rekapitulasi ini diekspor secara digital melalui Sistem INFRA-DAYA Kota Bandung.<br>
                    * Dicetak pada: {{ now()->format('d/m/Y H:i:s') }} WIB
                </div>
            </td>
            <td style="width: 35%; text-align: center;">
                <div>Bandung, {{ $tanggalCetak }}</div>
                <div style="font-weight: bold; margin-top: 2px;">
                    Kepala Dinas Sumber Daya Air dan Bina Marga<br>
                    Kota Bandung
                </div>
                <div style="height: 55px;"></div>
                <div style="font-weight: bold; text-decoration: underline;">
                    Ir. H. Didi Ruswandi, M.T.
                </div>
                <div style="font-size: 7.5pt; color: #475569;">
                    NIP. 19680514 199403 1 005
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
