<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Perintah Tugas - {{ $disposisi->nomor_surat_tugas }}</title>
    <style>
        @page {
            margin: 18mm 20mm 20mm 20mm;
            size: a4 portrait;
        }
        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.45;
            color: #1a1a1a;
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
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #000;
            margin: 0;
        }
        .kop-dinas {
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #1F3A5F;
            margin: 2px 0;
        }
        .kop-alamat {
            font-size: 8.5pt;
            color: #444;
            margin: 2px 0;
        }
        .garis-kop {
            border-top: 2.5px solid #000;
            border-bottom: 1px solid #000;
            height: 2px;
            margin-top: 6px;
            margin-bottom: 18px;
        }
        .judul-surat {
            text-align: center;
            margin-bottom: 16px;
        }
        .judul-surat h2 {
            font-size: 12.5pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .judul-surat p {
            font-size: 10pt;
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
            margin: 3px 0 0 0;
            color: #333;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .content-table td {
            vertical-align: top;
            padding: 3px 0;
            font-size: 10.5pt;
        }
        .label-col {
            width: 130px;
            font-weight: normal;
        }
        .colon-col {
            width: 15px;
            text-align: center;
        }
        .section-title {
            font-weight: bold;
            font-size: 11pt;
            text-align: center;
            margin: 14px 0 8px 0;
            letter-spacing: 1px;
        }
        .instruksi-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 3px solid #0E7C86;
            padding: 8px 12px;
            font-size: 10pt;
            margin: 4px 0;
            line-height: 1.4;
        }
        .numbered-list {
            margin: 0;
            padding-left: 20px;
        }
        .numbered-list li {
            margin-bottom: 6px;
            text-align: justify;
        }
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        .ttd-table td {
            vertical-align: top;
            font-size: 10.5pt;
        }
        .footer-note {
            margin-top: 25px;
            padding-top: 8px;
            border-top: 1px dashed #cbd5e1;
            font-size: 7.5pt;
            color: #64748b;
            text-align: center;
        }
        .badge-prioritas {
            display: inline-block;
            font-size: 9pt;
            font-weight: bold;
            padding: 1px 6px;
            border: 1px solid #333;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI -->
    <table class="header-table">
        <tr>
            <td class="kop-text">
                <div class="kop-instansi">PEMERINTAH DAERAH KOTA BANDUNG</div>
                <div class="kop-dinas">DINAS SUMBER DAYA AIR DAN BINA MARGA</div>
                <div class="kop-alamat">
                    Jl. Cianjur No. 34, Kel. Kacapiring, Kec. Batununggal, Kota Bandung 40272<br>
                    Telepon: (022) 7278853 | Faksimile: (022) 7278853 | Pos-el: dsdabm@bandung.go.id | Laman: dsdabm.bandung.go.id
                </div>
            </td>
        </tr>
    </table>
    <div class="garis-kop"></div>

    <!-- JUDUL SURAT -->
    <div class="judul-surat">
        <h2>SURAT PERINTAH TUGAS (SPT)</h2>
        <p>Nomor: {{ $disposisi->nomor_surat_tugas }}</p>
    </div>

    <!-- DASAR PENUGASAN -->
    <table class="content-table">
        <tr>
            <td class="label-col"><strong>Dasar Penugasan</strong></td>
            <td class="colon-col">:</td>
            <td>
                1. Dokumen Pelaporan Kerusakan No. Tiket <strong>{{ $disposisi->laporan->nomor_tiket }}</strong> tertanggal {{ $disposisi->laporan->created_at->translatedFormat('d F Y') }}.<br>
                2. Verifikasi Teknis dan Penilaian Prioritas Admin DSDABM Kota Bandung.
            </td>
        </tr>
    </table>

    <div class="section-title">MEMERINTAHKAN:</div>

    <!-- KEPADA -->
    <table class="content-table">
        <tr>
            <td class="label-col"><strong>Kepada</strong></td>
            <td class="colon-col">:</td>
            <td>
                <strong>{{ $disposisi->petugas->name }}</strong>
            </td>
        </tr>
        <tr>
            <td class="label-col">Kontak / Pos-el</td>
            <td class="colon-col">:</td>
            <td>{{ $disposisi->petugas->phone ?: '-' }} / {{ $disposisi->petugas->email }}</td>
        </tr>
        <tr>
            <td class="label-col">Jabatan</td>
            <td class="colon-col">:</td>
            <td>Petugas Teknis Lapangan DSDABM Kota Bandung</td>
        </tr>
    </table>

    <!-- UNTUK -->
    <table class="content-table">
        <tr>
            <td class="label-col"><strong>Untuk</strong></td>
            <td class="colon-col">:</td>
            <td>
                <ol class="numbered-list">
                    <li>
                        Melaksanakan peninjauan lokasi dan pekerjaan perbaikan teknis terhadap infrastruktur:
                        <br>
                        <strong>{{ $disposisi->laporan->judul }}</strong>
                        <br>
                        Kategori: <em>{{ $disposisi->laporan->kategori->nama_kategori ?? 'Infrastruktur Jalan/Drainase' }}</em> &nbsp;|&nbsp;
                        Prioritas: <span class="badge-prioritas">{{ ucfirst($disposisi->laporan->prioritas) }}</span>
                    </li>
                    <li>
                        <strong>Lokasi Pekerjaan:</strong>
                        <br>
                        Kelurahan {{ $disposisi->laporan->kelurahan->nama_kelurahan ?? '-' }},
                        Kecamatan {{ $disposisi->laporan->kelurahan->kecamatan->nama_kecamatan ?? '-' }}, Kota Bandung.
                        <br>
                        Koordinat GPS: <code>{{ $disposisi->laporan->latitude }}, {{ $disposisi->laporan->longitude }}</code>
                    </li>
                    <li>
                        <strong>Tenggat Waktu Pelaksanaan:</strong>
                        <br>
                        Paling lambat diselesaikan pada hari <strong>{{ $disposisi->tenggat_waktu->translatedFormat('l, d F Y') }}</strong>.
                    </li>
                    <li>
                        <strong>Instruksi Khusus & Arahan Teknis:</strong>
                        <div class="instruksi-box">
                            {{ $disposisi->catatan_instruksi }}
                        </div>
                    </li>
                    <li>
                        Mendokumentasikan foto hasil pekerjaan sesudah perbaikan (<em>after</em>) dan memperbarui status pengerjaan melalui sistem <strong>INFRA-DAYA</strong>.
                    </li>
                </ol>
            </td>
        </tr>
    </table>

    <!-- TANDA TANGAN -->
    <table class="ttd-table">
        <tr>
            <td style="width: 55%;"></td>
            <td style="width: 45%; text-align: left;">
                Dikeluarkan di Bandung<br>
                Pada tanggal: {{ $disposisi->tanggal_disposisi->translatedFormat('d F Y') }}<br>
                <br>
                <strong>An. KEPALA DINAS SUMBER DAYA AIR<br>DAN BINA MARGA KOTA BANDUNG</strong><br>
                Pejabat Pembuat Disposisi,
                <br><br><br><br>
                <strong><u>{{ $disposisi->admin->name }}</u></strong><br>
                NIP. 19850314 201001 1 008<br>
                Admin DSDABM Kota Bandung
            </td>
        </tr>
    </table>

    <!-- FOOTER RESMI -->
    <div class="footer-note">
        Dokumen ini diterbitkan secara sah melalui Sistem Informasi INFRA-DAYA Kota Bandung (SDG 9: Industri, Inovasi & Infrastruktur)<br>
        Nomor Surat: {{ $disposisi->nomor_surat_tugas }} | ID Laporan: {{ $disposisi->laporan->nomor_tiket }} | Tanggal Cetak: {{ now()->translatedFormat('d F Y H:i') }} WIB
    </div>

</body>
</html>
