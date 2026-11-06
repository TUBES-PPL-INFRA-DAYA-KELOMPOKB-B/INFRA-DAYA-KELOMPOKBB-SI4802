# INFRA-DAYA — Sistem Pelaporan Kerusakan Infrastruktur Kota Bandung

<div align="center">

![INFRA-DAYA Banner](https://img.shields.io/badge/SDG%209-Industry%2C%20Innovation%20%26%20Infrastructure-orange?style=for-the-badge)
![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-CSS-06B6D4?style=for-the-badge&logo=tailwindcss)
![Tests](https://img.shields.io/badge/Feature%20Tests-111%2F111%20PASS-emerald?style=for-the-badge)
![Dusk](https://img.shields.io/badge/Dusk%20E2E-48%2F48%20PASS-brightgreen?style=for-the-badge)

<p align="center">
  <b>Platform terintegrasi pelaporan, verifikasi cepat, disposisi digital, dan monitoring kinerja penanganan kerusakan infrastruktur sipil (Jalan, Jembatan, Trotoar, Drainase, dan PJU) di lingkungan Dinas Sumber Daya Air dan Bina Marga (DSDABM) Pemerintah Kota Bandung.</b>
</p>

</div>

---

## 📌 Daftar Isi

1. [Tentang Sistem & Relevansi SDG 9](#-tentang-sistem--relevansi-sdg-9)
2. [Fitur Unggulan Sistem](#-fitur-unggulan-sistem)
3. [Arsitektur & Tumpukan Teknologi](#-arsitektur--tumpukan-teknologi)
4. [Struktur Pengguna & Hak Akses (RBAC)](#-struktur-pengguna--hak-akses-rbac)
5. [Alur Siklus Laporan (Lifecycle)](#-alur-siklus-laporan-lifecycle)
6. [Instalasi & Panduan Menjalankan Sistem](#-instalasi--panduan-menjalankan-sistem)
7. [Akun Bawaan Sistem (Demo Credentials)](#-akun-bawaan-sistem-demo-credentials)
8. [Pengujian Otomatis (Feature & Dusk E2E)](#-pengujian-otomatis-feature--dusk-e2e)
9. [Struktur Direktori Proyek](#-struktur-direktori-proyek)
10. [Audit Keamanan & Kepatuhan Standar](#-audit-keamanan--kepatuhan-standar)

---

## 🏛 Tentang Sistem & Relevansi SDG 9

**INFRA-DAYA** dikembangkan untuk mempercepat respon pemerintah daerah dalam merawat dan memulihkan infrastruktur publik Kota Bandung. Berorientasi pada pencapaian **Sustainable Development Goal 9 (Industri, Inovasi, dan Infrastruktur)**, sistem ini menghubungkan warga masyarakat secara transparan dengan petugas lapangan dan pimpinan eksekutif dalam satu ekosistem digital.

### Nilai Tambah Utama:
- **Transparansi Tanpa Hambatan**: Warga dapat melacak tahapan perbaikan nomor tiket mereka secara publik tanpa perlu login atau birokrasi tatap muka.
- **Efisiensi Lapangan**: Penugasan otomatis berbasis Surat Perintah Tugas (SPT) digital, lengkap dengan navigasi GPS langsung (Google Maps / Waze) dan verifikasi foto bukti sesudah pengerjaan (*Before & After*).
- **Akuntabilitas & SLA Berbasis Data**: Dashboard eksekutif menghitung durasi respon verifikasi (jam), rata-rata durasi pengerjaan lapangan (hari), dan indeks kepatuhan tenggat waktu (*Service Level Agreement*).

---

## 🚀 Fitur Unggulan Sistem

### 1. Pelaporan Warga Berbasis Geospasial
- Penandaan titik lokasi kerusakan interaktif menggunakan Leaflet.js dan koordinat GPS.
- Unggah foto kondisi fisik kerusakan sebelum perbaikan dengan sanitasi server MIME.
- Validasi otomatis nomor kontak dan alamat kelurahan/kecamatan.

### 2. Antrean Verifikasi & Penilaian Skala Prioritas
- Validasi keabsahan laporan oleh Admin DSDABM.
- Algoritma pembobotan otomatis tingkat urgensi (*Rendah, Sedang, Tinggi*).
- Riwayat audit lengkap (*Audit Trail*) mencatat setiap persetujuan atau alasan penolakan.

### 3. Disposisi Petugas & Penerbitan SPT PDF Resmi
- Penugasan laporan yang disetujui kepada petugas teknis lapangan aktif.
- Penetapan instruksi teknis dan tenggat waktu perbaikan (*due date*).
- Penerbitan otomatis Surat Perintah Tugas (SPT) PDF berformat kop dinas resmi Pemkot Bandung (`barryvdh/laravel-dompdf`).

### 4. Lembar Kerja Mobile-First Petugas Lapangan
- Antarmuka responsif ramah layar gawai untuk petugas di lapangan.
- Tombol navigasi rute langsung ke titik koordinat via Google Maps & Waze.
- Aksi mulai pengerjaan (`sedang_dikerjakan`) dan unggah bukti foto sesudah (`selesai_at`) beserta catatan teknis perbaikan.

### 5. Tracking Publik & Peta Sebaran Pan-Bandung
- Lacak status laporan secara instan via `/lacak-tiket/{nomor_tiket}`.
- Komparasi visual foto *Before vs After*, log tahapan riwayat status, dan informasi petugas pelaksana.
- Peta sebaran publik interaktif (`/peta-publik`) dilengkapi filter kategori infrastruktur, status, kecamatan, dan pencarian kata kunci.

### 6. Umpan Balik & Evaluasi Warga
- Penilaian kepuasan warga pelapor setelah laporan dinyatakan selesai (Rating 1-5 bintang, kecepatan respon, dan kualitas fisik perbaikan).
- Rating transparansi yang otomatis tampil pada halaman pelacakan publik.

### 7. Dashboard Eksekutif & Pemantauan SLA
- 6 Kartu KPI: Total Laporan, Selesai, Waktu Respon Verifikasi (Jam), Durasi Pengerjaan (Hari), % Kepatuhan SLA On-Time, dan Rata-rata Rating.
- 4 Grafik interaktif Chart.js: Tren pengaduan bulanan, sebaran kategori infrastruktur, volume laporan per kecamatan, dan distribusi bintang kepuasan.
- Matriks kinerja per wilayah kecamatan dan filter rentang periode dinamis (Semua, 30 hari, 90 hari, 365 hari).

### 8. Rekapitulasi Data & Ekspor Resmi (Excel / PDF)
- Filter laporan multi-kriteria: rentang tanggal lapor, kecamatan, kategori, status penanganan, prioritas, dan kata kunci.
- Ekspor Microsoft Excel (`.xlsx`) berstandar tabular resmi dengan styling kop instansi DSDABM via `maatwebsite/excel`.
- Ekspor Dokumen Laporan Rekapitulasi PDF berformat A4 Landscape lengkap dengan kop dinas Pemkot Bandung, ringkasan metrik KPI, dan tanda tangan pengesahan Kepala Dinas.

---

## 🛠 Arsitektur & Tumpukan Teknologi

- **Backend Framework**: [Laravel 11.x](https://laravel.com) (PHP 8.2+)
- **Frontend & UI**: Blade Templates, [Tailwind CSS 3.x](https://tailwindcss.com), Alpine.js
- **Database**: MySQL 8.0 / MariaDB 10.4+
- **Peta & Geospasial**: [Leaflet.js](https://leafletjs.com) & OpenStreetMap
- **Visualisasi Grafik**: [Chart.js 4.x](https://www.chartjs.org)
- **Ekspor Dokumen**:
  - Spreadsheet: `maatwebsite/excel` (^3.1)
  - PDF Generator: `barryvdh/laravel-dompdf` (^3.1)
- **Pengujian Otomatis**:
  - Unit & Feature Test: PHPUnit 10.x
  - End-to-End Browser Test: Laravel Dusk (Chromium Headless)
- **Code Styling**: Laravel Pint (PSR-12 Conformance)

---

## 👥 Struktur Pengguna & Hak Akses (RBAC)

| Peran (Role) | Ruang Lingkup Hak Akses | Prefix URL Utama |
| :--- | :--- | :--- |
| **Warga** | Membuat pengaduan kerusakan, memantau laporan pribadi, memberikan rating & ulasan kepuasan. | `/warga/*` |
| **Petugas** | Melihat penugasan aktif di lapangan, mulai pengerjaan, navigasi GPS, unggah bukti fisik perbaikan sesudah. | `/petugas/*` |
| **Admin DSDABM** | Verifikasi & pembobotan prioritas, disposisi SPT, kelola master wilayah, kelola user, rekapitulasi data. | `/admin/*` |
| **Pimpinan** | Akses dashboard eksekutif, analisis metrik SLA, heatmap sebaran masalah, unduh laporan eksekutif. | `/pimpinan/*` |
| **Publik** | Lacak tiket laporan via nomor tiket, telusuri peta publik sebaran infrastruktur tanpa login. | `/lacak-tiket`, `/peta-publik` |

---

## 🔄 Alur Siklus Laporan (Lifecycle)

```
[Warga Mengadu]
       │ (Upload foto sebelum + Titik GPS)
       ▼
[Menunggu Verifikasi]
       │
   ┌───┴───────────────────────┐
   ▼                           ▼
[Ditolak]                 [Disetujui]
(Audit alasan)                 │ (Bobot Prioritas Ditetapkan)
                               ▼
                          [Disposisi]
                               │ (Terbit SPT PDF & Ditugaskan)
                               ▼
                     [Sedang Dikerjakan]
                               │ (Petugas bekerja di lokasi)
                               ▼
                           [Selesai]
                               │ (Unggah foto sesudah + catatan teknis)
                               ▼
                     [Umpan Balik Warga]
                     (Rating 1-5 Bintang & Ulasan)
```

---

## 💻 Instalasi & Panduan Menjalankan Sistem

### 1. Kebutuhan Lingkungan (Prerequisites)
- PHP >= 8.2 (dengan ekstensi: `gd`, `zip`, `pdo_mysql`, `curl`, `mbstring`, `fileinfo`)
- Composer >= 2.5
- Node.js >= 18.x & NPM
- MySQL >= 8.0 atau MariaDB >= 10.4

### 2. Kloning & Pengaturan Lingkungan
```bash
# Kloning repositori
git clone https://github.com/username/INFRA-DAYA.git
cd INFRA-DAYA

# Pasang pustaka dependensi PHP & JavaScript
composer install
npm install

# Konfigurasi berkas .env
cp .env.example .env
php artisan key:generate
```

### 3. Konfigurasi Database & Migrasi
Sesuaikan kredensial koneksi database pada `.env`:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=infra_daya
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan migrasi tabel beserta data pemula (*seeder*):
```bash
php artisan migrate --seed
php artisan storage:link
```

### 4. Menjalankan Aplikasi
```bash
# Terminal 1: Kompilasi frontend asset
npm run build
# atau npm run dev saat tahap pengembangan

# Terminal 2: Jalankan web server Laravel
php artisan serve
```
Akses aplikasi melalui browser di alamat: `http://localhost:8000`.

---

## 🔑 Akun Bawaan Sistem (Demo Credentials)

Seluruh akun menggunakan kata sandi bawaan: `password`

| Peran | Alamat Surel (Email) | Deskripsi Akun |
| :--- | :--- | :--- |
| **Admin DSDABM** | `admin@dsdabm.bandung.go.id` | Pengelola utama verifikasi, master data & disposisi |
| **Pimpinan Eksekutif** | `pimpinan@dsdabm.bandung.go.id` | Kepala Dinas / Pejabat Pemantau Dashboard SLA |
| **Petugas Lapangan 1** | `petugas1@dsdabm.bandung.go.id` | Petugas Teknis Jalan & Trotoar |
| **Petugas Lapangan 2** | `petugas2@dsdabm.bandung.go.id` | Petugas Teknis Drainase & PJU |
| **Warga Pelapor** | `warga@bandung.go.id` | Warga masyarakat pelapor pengaduan |

---

## 🧪 Pengujian Otomatis (Feature & Dusk E2E)

Proyek ini menerapkan cakupan pengujian komprehensif pada lapisan **Feature Integration** dan **End-to-End Browser Testing** dengan hasil kelulusan **100% PASS**:

```bash
# Menjalankan seluruh Unit & Feature Tests (111 Tests, 435 Assertions)
php artisan test --testsuite=Feature

# Menjalankan End-to-End Browser Tests via Laravel Dusk (48 Tests, 162 Assertions)
php artisan dusk
```

### Ringkasan Hasil Pengujian:
- **Feature Tests**: `111/111 PASS (100%)`
- **Dusk E2E Tests**: `48/48 PASS (100%)`
- **PSR-12 Pint Formatter**: `0 warnings (Clean)`

---

## 📁 Struktur Direktori Proyek

```
INFRA-DAYA/
├── app/
│   ├── Exports/                     # Kelas Ekspor Excel (LaporanRekapExport)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/               # Verifikasi, Disposisi, Rekap, Wilayah, Kategori, User
│   │   │   ├── Petugas/             # Lembar Kerja & Penyelesaian Lapangan
│   │   │   ├── Publik/              # Lacak Tiket & Peta Publik Interaktif
│   │   │   └── Warga/               # Pelaporan & Umpan Balik Kepuasan
│   │   ├── Middleware/              # Proteksi Role (EnsureUserRole) & Session Invalidate
│   │   └── Requests/                # Validasi Berkas & MIME Form Requests
│   ├── Models/                      # Model Eloquent (Laporan, Disposisi, UmpanBalik, dll.)
│   └── Services/                    # Layer Bisnis (DisposisiService, PetugasService, DashboardSlaService, RekapService)
├── database/
│   ├── migrations/                  # Skema database & composite indexes
│   └── seeders/                     # Wilayah Bandung, Kategori, dan Akun Default
├── resources/
│   ├── views/
│   │   ├── admin/                   # Antarmuka Admin & Rekapitulasi Data
│   │   ├── errors/                  # Halaman Error Ramah Pengguna (403, 404, 500)
│   │   ├── petugas/                 # Antarmuka Mobile-First Petugas
│   │   ├── publik/                  # Halaman Lacak Tiket & Peta Publik
│   │   └── warga/                   # Form Lapor Geospasial & Riwayat Warga
├── tests/
│   ├── Browser/                     # Pengujian E2E Dusk (12 Test Suites)
│   └── Feature/                     # Pengujian Fitur & Integrasi Controller/Service
└── routes/                          # Rute Web Terotentikasi & Publik
```

---

## 🔒 Audit Keamanan & Kepatuhan Standar

1. **Proteksi Akses Akun Nonaktif**: Akun yang dinonaktifkan oleh administrator secara otomatis diputus sesinya (*session invalidate*) dan ditolak saat mencoba login kembali.
2. **Pencegahan Upload File Spoofing**: Unggahan foto diverifikasi berdasarkan *Server MIME Detection* (`guessExtension()`), mencegah manipulasi ekstensi berkas.
3. **Perlindungan Terhadap IDOR (*Insecure Direct Object Reference*)**: Petugas lapangan hanya dapat memperbarui data pada surat tugas yang sah dialokasikan kepadanya. Warga hanya dapat mengedit laporan dalam status `menunggu_verifikasi` milik sendiri.
4. **Rate Limiting**: Pembatasan laju permintaan pada registrasi akun (5 req/menit) dan pelaporan kerusakan (10 req/menit) untuk mencegah serangan *spamming* / DoS.
5. **Sanitasi XSS Peta**: Escape data string dinamis pada marker popup Leaflet untuk mencegah injeksi script berbahaya.
6. **Optimasi Indeks Komposit**: Indeks gabungan `(status, created_at)` dan `(kategori_id, status)` untuk respon query cepat pada database skala besar.

---

<div align="center">
  <sub>Dikembangkan dengan dedikasi untuk kemajuan infrastruktur cerdas Kota Bandung.</sub><br>
  <b>Pemerintah Kota Bandung • Dinas Sumber Daya Air dan Bina Marga (DSDABM)</b>
</div>
