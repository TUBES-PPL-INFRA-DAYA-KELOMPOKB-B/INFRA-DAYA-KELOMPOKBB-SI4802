<div class="spt-document font-serif text-slate-900 bg-white leading-relaxed">
    <style>
        .spt-document {
            font-family: 'DejaVu Sans', 'Times New Roman', Times, serif;
            color: #111827;
            line-height: 1.5;
        }
        .spt-header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 18px;
        }
        .spt-header h3 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .spt-header h2 {
            font-size: 16pt;
            font-weight: bold;
            color: #1F3A5F;
            margin: 2px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .spt-header p {
            font-size: 8.5pt;
            color: #4b5563;
            margin: 2px 0 0 0;
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }
        .spt-title {
            text-align: center;
            margin-bottom: 20px;
        }
        .spt-title h1 {
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .spt-title p {
            font-size: 10pt;
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
            margin: 4px 0 0 0;
            color: #1f2937;
        }
        .spt-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10.5pt;
        }
        .spt-table td {
            vertical-align: top;
            padding: 3px 0;
        }
        .spt-table .label {
            width: 140px;
            font-weight: normal;
        }
        .spt-table .colon {
            width: 18px;
            text-align: center;
        }
        .spt-section-title {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            letter-spacing: 1.5px;
            margin: 14px 0 8px 0;
        }
        .spt-instruksi-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 3px solid #0E7C86;
            padding: 10px 14px;
            font-size: 10pt;
            margin: 6px 0;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: pre-wrap;
            line-height: 1.5;
            page-break-inside: auto;
        }
        .spt-badge {
            display: inline-block;
            font-size: 8.5pt;
            font-weight: bold;
            padding: 1px 6px;
            border: 1px solid #374151;
            text-transform: uppercase;
            border-radius: 3px;
        }
        .spt-list {
            margin: 0;
            padding-left: 20px;
        }
        .spt-list li {
            margin-bottom: 8px;
            text-align: justify;
        }
        .spt-ttd {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
            page-break-inside: avoid;
        }
        .spt-ttd td {
            vertical-align: top;
            font-size: 10.5pt;
        }
        .spt-footer {
            margin-top: 30px;
            padding-top: 8px;
            border-top: 1px dashed #cbd5e1;
            font-size: 8pt;
            color: #64748b;
            text-align: center;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            page-break-inside: avoid;
        }
    </style>

    <!-- KOP SURAT RESMI PEMKOT BANDUNG -->
    <div class="spt-header">
        <h3>Pemerintah Daerah Kota Bandung</h3>
        <h2>Dinas Sumber Daya Air dan Bina Marga</h2>
        <p>Jl. Cianjur No. 34, Kel. Kacapiring, Kec. Batununggal, Kota Bandung 40272</p>
        <p>Telepon: (022) 7278853 | Faksimile: (022) 7278853 | Pos-el: dsdabm@bandung.go.id</p>
    </div>

    <!-- JUDUL SURAT (AC18-08, AC18-09) -->
    <div class="spt-title">
        <h1>Surat Perintah Tugas (SPT)</h1>
        <p dusk="nomor-surat">Nomor: {{ $disposisi->nomor_surat_tugas }}</p>
    </div>

    <!-- DASAR PENUGASAN (AC18-08) -->
    <table class="spt-table">
        <tr>
            <td class="label"><strong>Dasar Penugasan</strong></td>
            <td class="colon">:</td>
            <td>
                1. Dokumen Laporan Pengaduan Kerusakan Infrastruktur No. Tiket <strong>{{ $disposisi->laporan->nomor_tiket ?? '-' }}</strong> tertanggal {{ $disposisi->laporan->created_at ? $disposisi->laporan->created_at->translatedFormat('d F Y') : '-' }}.<br>
                2. Hasil Verifikasi Lapangan dan Penilaian Skala Prioritas oleh Administrator DSDABM Kota Bandung.
            </td>
        </tr>
    </table>

    <div class="spt-section-title">MEMERINTAHKAN:</div>

    <!-- KEPADA (AC18-08) -->
    <table class="spt-table">
        <tr>
            <td class="label"><strong>Kepada</strong></td>
            <td class="colon">:</td>
            <td>
                <strong dusk="petugas-nama">{{ $disposisi->petugas->name ?? '-' }}</strong>
                @if($disposisi->petugas && ! $disposisi->petugas->is_active)
                    <span class="text-xs text-slate-500 font-sans italic ml-1">(Petugas Nonaktif)</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Kontak / Pos-el</td>
            <td class="colon">:</td>
            <td dusk="petugas-kontak">{{ $disposisi->petugas->phone ?: '-' }} / {{ $disposisi->petugas->email ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Jabatan</td>
            <td class="colon">:</td>
            <td>Petugas Teknis Lapangan DSDABM Kota Bandung</td>
        </tr>
    </table>

    <!-- UNTUK (AC18-08, AC18-10, AC18-13) -->
    <table class="spt-table">
        <tr>
            <td class="label"><strong>Untuk</strong></td>
            <td class="colon">:</td>
            <td>
                <ol class="spt-list">
                    <li>
                        Melaksanakan peninjauan fisik dan penanganan perbaikan terhadap kerusakan infrastruktur:
                        <br>
                        <strong dusk="laporan-judul">{{ $disposisi->laporan->judul ?? '-' }}</strong>
                        <br>
                        <span class="text-xs font-sans">
                            Kategori: <em dusk="laporan-kategori">{{ $disposisi->laporan->kategori->nama_kategori ?? 'Infrastruktur Publik' }}</em>
                            &nbsp;|&nbsp;
                            Prioritas: <span class="spt-badge" dusk="laporan-prioritas">{{ ucfirst($disposisi->laporan->prioritas ?? '-') }}</span>
                        </span>
                    </li>
                    <li>
                        <strong>Lokasi Sasaran Pekerjaan:</strong>
                        <br>
                        <span dusk="laporan-lokasi">
                            Kelurahan {{ $disposisi->laporan->kelurahan->nama_kelurahan ?? '-' }},
                            Kecamatan {{ $disposisi->laporan->kelurahan->kecamatan->nama_kecamatan ?? '-' }}, Kota Bandung.
                        </span>
                        @if($disposisi->laporan->lokasi_spesifik)
                            <br><span class="text-xs text-slate-600 font-sans">Patokan: {{ $disposisi->laporan->lokasi_spesifik }}</span>
                        @endif
                        <br>
                        <span class="text-xs font-mono font-bold" dusk="laporan-koordinat">
                            Koordinat GPS: {{ $disposisi->laporan->latitude }}, {{ $disposisi->laporan->longitude }}
                        </span>
                    </li>
                    <li>
                        <strong>Tanggal Penugasan Disposisi:</strong>
                        <br>
                        <span dusk="tanggal-disposisi">{{ $disposisi->tanggal_disposisi ? \Illuminate\Support\Carbon::parse($disposisi->tanggal_disposisi)->translatedFormat('d F Y') : '-' }}</span>
                        @if($disposisi->tenggat_waktu)
                            <br>
                            <span class="text-xs font-sans text-slate-600">
                                Tenggat Waktu Penyelesaian: <strong>{{ \Illuminate\Support\Carbon::parse($disposisi->tenggat_waktu)->translatedFormat('l, d F Y') }}</strong>
                            </span>
                        @endif
                    </li>
                    <li>
                        <strong>Instruksi Khusus & Arahan Teknis:</strong>
                        <div class="spt-instruksi-box" dusk="catatan-instruksi">
                            {{ $disposisi->catatan_instruksi }}
                        </div>
                    </li>
                    <li>
                        Mengunggah dokumentasi foto bukti sesudah perbaikan (<em>after</em>) dan memperbarui progres pengerjaan lapangan secara berkala melalui sistem <strong>INFRA-DAYA</strong>.
                    </li>
                </ol>
            </td>
        </tr>
    </table>

    <!-- TANDA TANGAN RESMI (AC18-08, AC18-10, AC18-13) -->
    <table class="spt-ttd">
        <tr>
            <td style="width: 52%;"></td>
            <td style="width: 48%; text-align: left;">
                Dikeluarkan di Bandung<br>
                Pada tanggal: <strong>{{ $disposisi->tanggal_disposisi ? \Illuminate\Support\Carbon::parse($disposisi->tanggal_disposisi)->translatedFormat('d F Y') : '-' }}</strong><br>
                <br>
                <strong>An. KEPALA DINAS SUMBER DAYA AIR<br>DAN BINA MARGA KOTA BANDUNG</strong><br>
                Pejabat Pembuat Disposisi,
                <br><br><br><br>
                <strong dusk="admin-nama"><u>{{ $disposisi->admin->name ?? 'Administrator DSDABM' }}</u></strong><br>
                NIP. 19850314 201001 1 008<br>
                Admin DSDABM Kota Bandung
            </td>
        </tr>
    </table>

    <!-- FOOTER RESMI DOKUMEN & WAKTU CETAK (AC18-10) -->
    <div class="spt-footer">
        Dokumen resmi Surat Perintah Tugas ini diterbitkan melalui Sistem Informasi INFRA-DAYA Kota Bandung.<br>
        Nomor SPT: {{ $disposisi->nomor_surat_tugas }} | ID Laporan: {{ $disposisi->laporan->nomor_tiket ?? '-' }} |
        <span dusk="waktu-dokumen">Waktu Pembuatan Dokumen: {{ $waktuDokumen->translatedFormat('d F Y, H:i') }} WIB</span>
    </div>
</div>

