# PROGRESS KERJA PROYEK INFRA-DAYA
**Sistem Pelaporan Kerusakan Infrastruktur Kota Bandung (SDG 9)**

Terakhir diperbarui: 2026-10-12 (Fase 1)

---

## Ringkasan Progres Fase

| Fase | Nama Fase | Status | Keterangan |
| :--- | :--- | :---: | :--- |
| **0** | **Setup Project, Breeze, Tailwind, Layout, Dusk** | **DONE** | Laravel 11, Tailwind Tokens, Layout Warga & Admin, Dusk terkonfigurasi |
| **1** | **Master Data Wilayah (Kecamatan & Kelurahan)** | **DONE** | CRUD Kecamatan & Kelurahan, API chained dropdown, 11/11 Dusk test PASS |
| **2** | **User & Role Management** | **DONE** | CRUD User, Aktivasi status akun, Avatar upload aman, Demo 4 Role seeder, 5/5 Dusk test PASS |
| **3** | **Master Kategori Infrastruktur** | **DONE** | CRUD Kategori + Bobot Prioritas (1-3), Seeder 4 Kategori default, 5/5 Dusk test PASS |
| **4** | **Pengajuan Laporan oleh Warga** | **DONE** | Form Leaflet.js Geotagging, Tiket Unik harian, Riwayat status log, IDOR Policy, 5/5 Dusk test PASS |
| **5** | **Verifikasi & Penilaian Prioritas (Sprint 1)** | **DONE** | PBI 13 (Antrean & Filter Tanggal), PBI 14 (Aksi Terima/Tolak), PBI 15 (Prioritas) |
| 6 | Disposisi Penugasan & Dokumen PDF | TODO | Surat Tugas Digital & PDF DomPDF |
| **7** | **Pelaksanaan Lapangan oleh Petugas (PBI-19, PBI-20, PBI-21)** | **DONE ✅** | PBI-19 (Daftar & Navigasi Peta), PBI-20 (Update Progres & Foto Hasil), PBI-21 (Aksi Tutup Tugas Selesai & Riwayat), 41/41 PHPUnit Feature PASS & 6/6 Dusk E2E PASS |
| 8 | Tracking Timeline & Peta Publik | TODO | Pelacakan Nomor Tiket & Leaflet Sebaran Terbuka |
| 9 | Umpan Balik & Rating Warga | TODO | Bintang 1-5 & Ulasan Pasca Perbaikan |
| 10 | Dashboard Eksekutif & Monitoring SLA | TODO | Metrik SLA, Tren Laporan Chart.js, Heatmap |
| 11 | Rekapitulasi Data & Ekspor (Excel & PDF) | TODO | Filter Multi-Parameter, Ekspor Excel & PDF |
| 12 | Hardening, Optimasi & Pengujian Menyeluruh | TODO | Keamanan, Eager Loading, Rangkaian Dusk 100% |

---

## Rincian Item PBI per Fase

### Fase 0: Setup & Fondasi Sistem (STATUS: DONE)
- [x] Inisialisasi Git repository dan branch `fase-00-setup`.
- [x] Setup proyek Laravel 11 dengan PHP 8.2+ dan MariaDB/MySQL.
- [x] Setup Laravel Breeze (Blade stack), Tailwind CSS 3, Vite, Alpine.js.
- [x] Konfigurasi Token Warna Tailwind (`navy #1F3A5F`, `teal #0E7C86`, `amber #F2A93B`, `bg #EEF3F6`).
- [x] Blade Components reusable: `card`, `button`, `badge-status`, `modal-confirm`, `flash`, `form-input`, `empty-state`.
- [x] Layout dasar per role: `x-admin-layout` (sidebar responsif) & `x-warga-layout` (top navbar publik).
- [x] Konfigurasi `.env.example` & `.env` dev (`infradaya_dev`).
- [x] Konfigurasi Laravel Dusk dengan database terpisah `.env.dusk.local` (`infradaya_dusk`).
- [x] Pengujian Dusk awal (`tests/Browser/SetupTest.php`) lulus 100%.

### Fase 1: Master Data Wilayah (STATUS: DONE ✅)
- [x] Migration & Model `kecamatan` dan `kelurahan`.
- [x] Seeder wilayah dari `database/data/wilayah_kota_bandung.csv` (dengan fallback/TODO bila belum lengkap).
- [x] CRUD Kecamatan (List, Search, Tambah, Edit, Hapus dengan modal konfirmasi).
- [x] CRUD Kelurahan (List, Filter Kecamatan, Tambah, Edit, Hapus).
- [x] Endpoint AJAX chained dropdown `GET /api/wilayah/kecamatan/{id}/kelurahan`.
- [x] Pengujian Dusk E2E Wilayah — **11/11 PASS**.

### Fase 2: User & Role (STATUS: DONE ✅)
- [x] Migration kolom `users` (`role`, `phone`, `avatar`, `is_active`).
- [x] Middleware `EnsureUserRole` untuk routing group per role (`admin`, `petugas`, `pimpinan`, `warga`).
- [x] CRUD User (Admin): Tambah admin/petugas/pimpinan/warga, aktivasi/toggle status akun, edit data.
- [x] Unggah foto profil avatar aman (nama acak, mime spesifik JPG/JPEG/PNG).
- [x] Demo users seeder (1 admin, 2 petugas, 1 pimpinan, 3 warga).
- [x] Pengujian Dusk E2E User & Role (`UserTest.php`) — **5/5 PASS** (Total suite Dusk **19/19 PASS**).

### Fase 3: Master Kategori Infrastruktur (STATUS: DONE ✅)
- [x] Migration & Model `kategori_infrastruktur` (`bobot_prioritas` 1-3).
- [x] Seeder 4 kategori default (Jalan Raya, Drainase, PJU, Jembatan).
- [x] CRUD Kategori (Admin) dengan modal konfirmasi aman.
- [x] Pengujian Dusk E2E Kategori (`KategoriTest.php`) — **5/5 PASS** (Total Dusk **24/24 PASS**).

### Fase 4: Pengajuan Laporan Warga (STATUS: DONE ✅)
- [x] Migration & Model `laporan_kerusakan` dan `riwayat_status`.
- [x] Generator nomor tiket unik harian (`INFRA-YYYYMMDD-XXXX`).
- [x] Form pengajuan laporan: Dropdown berjenjang + Leaflet interactive picker (draggable marker) + upload foto sebelum (maks 5MB).
- [x] Validasi batas wilayah Kota Bandung (Latitude -7.05 s/d -6.80 & Longitude 107.50 s/d 107.80).
- [x] Fitur ubah & batalkan laporan selama status `menunggu_verifikasi`.
- [x] Proteksi otorisasi Policy (`LaporanPolicy`) pencegah IDOR.
- [x] Pengujian Dusk E2E Lapor Warga (`LaporanWargaTest.php`) — **5/5 PASS** (Total Dusk **29/29 PASS**).

### Fase 5 / Sprint 1: Verifikasi & Penilaian Prioritas (STATUS: DONE ✅)
**Penanggung Jawab: Wahyu Wijaya (102022400125)**
- [x] **[IFD-14] PBI-13: Daftar Aduan & Filter Multi-Kriteria**
  - Antrean daftar aduan dengan pencarian kata kunci (nomor tiket/judul/pelapor).
  - Filter multi-kriteria: status, kategori infrastruktur, kecamatan, dan rentang periode tanggal.
  - Validasi rentang tanggal (tanggal awal tidak boleh lebih besar dari tanggal akhir).
  - Paginasi tabel aduan dan tombol detail pemeriksaan laporan.
- [x] **[IFD-15] PBI-14: Aksi Terima / Tolak Aduan**
  - Lembar kerja detail aduan: foto bukti kerusakan fisik, koordinat peta Leaflet, dan timeline riwayat status.
  - Aksi terima aduan valid mengubah status ke `diproses` serta audit log riwayat status.
  - Aksi tolak aduan tidak valid dengan catatan alasan penolakan wajib (minimal 10 karakter).
  - Proteksi sistem mencegah verifikasi ulang terhadap laporan yang sudah diproses atau ditolak.
- [x] **[IFD-16] PBI-15: Penetapan & Pembaruan Skala Prioritas**
  - Penetapan tingkat prioritas penanganan: Rendah, Sedang, Tinggi saat proses verifikasi.
  - Aksi pembaruan skala prioritas pada laporan aktif (`status = diproses`).
  - Proteksi sistem menolak perubahan prioritas pada laporan yang sudah selesai atau ditolak.
- [x] **Pengujian Otomatis Feature Test (`VerifikasiAdminTest.php`)** mencakup seluruh skenario TC01-01 s/d TC01-12 dan TC02-01 s/d TC02-14 — **18/18 PASS**.

### Fase 6: Disposisi Penugasan & Surat Tugas PDF (STATUS: TODO)
- [ ] Migration & Model `disposisi_penugasan`.
- [ ] Form disposisi admin: Pilih petugas aktif, instruksi teknis, tenggat waktu.
- [ ] Cetak Surat Perintah Tugas (SPT) format PDF via `barryvdh/laravel-dompdf`.
- [ ] Pengujian Dusk E2E Disposisi & PDF.

### Fase 7: Pengerjaan Petugas Lapangan (STATUS: DONE ✅)
**Penanggung Jawab: Baso Aditya Riski (102022400282) - PBI-19, PBI-20, PBI-21**
- [x] **[PBI-19] Daftar Tugas Lapangan & Navigasi Peta:**
  - Isolasi daftar penugasan hanya milik petugas yang login (`petugas_id = id`).
  - Kartu informasi tugas lengkap: nomor tiket, SPT, judul, kategori, wilayah, prioritas, tanggal disposisi, tenggat waktu, lencana status.
  - Filter status multi-kriteria (`ditugaskan`, `diterima`, `sedang_dikerjakan`, `selesai`) dan pengurutan default prioritas tertinggi lalu tanggal disposisi terlama.
  - Pencarian fleksibel kata kunci nomor tiket, judul, nomor SPT, dan wilayah.
  - Tampilan empty state informatif saat tidak ada tugas.
  - Lembar rincian tugas: peta geospasial Leaflet.js dengan marker koordinat kerusakan, instruksi verifikator, deskripsi laporan, foto bukti awal sebelum.
  - Tautan deep link navigasi rute ke Google Maps, Waze, dan OpenStreetMap.
  - Aksi `acceptTask` mengubah status `ditugaskan` menjadi `diterima` serta pencatatan riwayat status audit.
  - Proteksi otorisasi pencegahan IDOR (akses tugas petugas lain ditolak 403).
- [x] **[PBI-20] Update Progres Pengerjaan:**
  - Aksi mulai pengerjaan fisik di lokasi mengubah status menjadi `sedang_dikerjakan` dan audit status `diproses`.
- [x] **[PBI-21] Penutupan Tugas & Unggah Bukti Sesudah:**
  - Unggah foto bukti hasil sesudah (MIME aman jpg/jpeg/png, maks 5 MB) dan catatan perbaikan.
  - Penutupan tugas atomik (`DB::transaction`) mengubah status penugasan dan laporan menjadi `selesai`.
- [x] **Pengujian Otomatis:**
  - Backend Feature Test (`PBI19DaftarTugasNavigasiPetaTest.php` & `PetugasLapanganTest.php`) — **23/23 PASS (112 assertions)**.
  - End-to-End Browser UI Test (`tests/Browser/PetugasTest.php`) — **5/5 PASS (21 assertions)**.

### Fase 8: Tracking Timeline & Peta Publik (STATUS: TODO)
- [ ] Halaman publik lacak nomor tiket tanpa login.
- [ ] Visual timeline audit & perbandingan foto sebelum vs sesudah (Before/After).
- [ ] Peta publik sebaran Leaflet interaktif dengan filter kategori & status.
- [ ] Pengujian Dusk E2E Tracking & Peta.

### Fase 9: Umpan Balik & Evaluasi Warga (STATUS: TODO)
- [ ] Migration & Model `umpan_balik`.
- [ ] Form rating (1-5 bintang) & ulasan warga untuk laporan berstatus selesai.
- [ ] Proteksi 1 kali rating per laporan & Policy otorisasi warga pelapor.
- [ ] Pengujian Dusk E2E Umpan Balik.

### Fase 10: Dashboard Eksekutif & SLA (STATUS: TODO)
- [ ] Perhitungan metrik SLA via Service Class.
- [ ] Grafik tren & statistik Chart.js per kecamatan & kategori.
- [ ] Peta/Heatmap sebaran kepadatan kerusakan untuk pimpinan.
- [ ] Pengujian Dusk E2E Dashboard.

### Fase 11: Rekapitulasi Data & Ekspor (STATUS: TODO)
- [ ] Filter laporan multi-kriteria (tanggal, kecamatan, kategori, status).
- [ ] Ekspor Excel (`maatwebsite/excel`).
- [ ] Ekspor Laporan Resmi PDF (`barryvdh/laravel-dompdf`).
- [ ] Pengujian Dusk E2E Ekspor.

### Fase 12: Hardening & Finalisasi (STATUS: TODO)
- [ ] Audit keamanan (CSRF, Rate Limiting, MIME sanitize, Policy audit).
- [ ] Optimasi query (Eager loading, index composite).
- [ ] Halaman error ramah pengguna (403, 404, 500).
- [ ] Dokumentasi lengkap `README.md`.
- [ ] Seluruh rangkaian Dusk test 100% lulus & merge ke `main`.
