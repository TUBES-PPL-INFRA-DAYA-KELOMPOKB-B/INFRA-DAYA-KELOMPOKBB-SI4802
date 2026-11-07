# PRD — Modul Pengajuan Laporan Kerusakan (PBI 7, 8, 9)

**Produk:** INFRA-DAYA: Infrastruktur dan Daya Tanggap Kota Bandung
**Sprint:** Sprint 1 (Minggu 10)
**PIC:** Maurellio Christopher Yonathan (102022430009)
**Tiket Jira:** IFD-11 (PBI-7), IFD-12 (PBI-8), IFD-13 (PBI-9)
**Status:** Draft v0.1
**Tenggat:** 3 November 2026
**Referensi:** Proposal Pengembangan Produk INFRA-DAYA (5 Oktober 2026)

---

## 1. Ringkasan

Modul ini adalah pintu masuk seluruh alur penanganan di INFRA-DAYA. Warga membuat laporan kerusakan infrastruktur secara fleksibel tanpa harus berada di lokasi dan tanpa bergantung pada GPS perangkat. Modul mencakup tiga PBI:

| PBI | Fitur | Jira | Ringkasan |
|---|---|---|---|
| PBI-7 | Form isian + foto bukti | IFD-11 | Warga mengisi judul, kategori, deskripsi, dan mengunggah foto bukti |
| PBI-8 | Pinpoint lokasi peta | IFD-12 | Warga menandai titik koordinat kerusakan di peta Leaflet.js |
| PBI-9 | Fitur ubah/batal laporan | IFD-13 | Warga dapat mengubah atau membatalkan laporan selama belum diverifikasi |

### 1.1 Latar Belakang Masalah
- Kanal aduan konvensional memberi lokasi berupa teks multitafsir tanpa koordinat presisi.
- Aplikasi yang mewajibkan GPS real-time menyulitkan warga yang melihat kerusakan saat berkendara atau cuaca buruk.
- Laporan tanpa foto terstandar sulit divalidasi Admin DSDABM.

### 1.2 Tujuan
1. Warga dapat membuat laporan lengkap (data + foto + koordinat) dari lokasi mana pun.
2. Setiap laporan menghasilkan data spasial dan bukti foto yang cukup untuk diverifikasi Admin.
3. Warga mengontrol laporannya (ubah/batal) selama masih berstatus *Menunggu Verifikasi*.

### 1.3 Non-Tujuan (di luar scope PBI ini)
- Dropdown hierarki Kecamatan–Kelurahan dan CRUD wilayah (PBI 1–3, PIC Rakean). PBI ini hanya **mengonsumsi** komponennya.
- Master kategori kerusakan (PBI 10–12, PIC I Nyoman). PBI ini hanya mengonsumsi daftar kategori aktif.
- Verifikasi/penolakan oleh admin (PBI 13–15).
- Linimasa status, notifikasi, rating (Sprint 2).
- Deteksi laporan duplikat dan upvote (PBI 40–41, Sprint 2).

---

## 2. Pengguna & Konteks

| Aspek | Detail |
|---|---|
| Aktor utama | **Warga (Pelapor)** — sudah login (UC-01) |
| Perangkat | Peramban ponsel dan desktop (responsif, NFR-03) |
| Use case terkait | UC-02 Buat Laporan Kerusakan (include: Geotagging, Pilih Section Dropdown) |
| Kebutuhan fungsional terkait | SKF-02, SKF-03 |
| Kebutuhan non-fungsional terkait | NFR-01, NFR-02, NFR-03, NFR-04 |

**Persona singkat:** Warga yang melihat lubang di jalan saat berkendara, lalu melapor dari rumah/kantor/kampus beberapa jam kemudian.

---

## 3. Alur Pengguna

```
[Menu Buat Laporan]
        │
        ▼
 PBI-7: Isi form (judul, kategori, deskripsi, foto)
        │  ── Simpan draf ──► (Draf tersimpan)
        ▼
 Pilih wilayah (Kecamatan → Kelurahan)   ← komponen PBI 3
        │
        ▼
 PBI-8: Peta terpusat ke kelurahan → geser pin → Konfirmasi titik
        │
        ▼
 Kirim laporan → validasi → simpan (status: Menunggu Verifikasi) → nomor tiket terbit
        │
        ▼
 PBI-9: Laporan Saya → Ubah / Batalkan (selama Menunggu Verifikasi)
```

---

## 4. Requirements per PBI

### 4.1 PBI-7 — Form Isian + Foto Bukti (IFD-11)

**User story:** Sebagai warga, saya ingin mengisi detail kerusakan dan mengunggah foto bukti agar admin dapat memverifikasi laporan saya.

#### Functional Requirements

| ID | Requirement | Prioritas |
|---|---|---|
| FR-7.1 | Form memuat field: **Judul** (wajib), **Kategori infrastruktur** (wajib, dropdown dari kategori aktif), **Deskripsi kondisi** (wajib), **Foto bukti** (wajib minimal 1) | Must |
| FR-7.2 | Kategori yang tampil adalah kategori berstatus Aktif (Jalan Raya/Trotoar, Drainase/Gorong-gorong, PJU, Jembatan/Tanggul) | Must |
| FR-7.3 | Unggah foto dengan format `.jpg`, `.jpeg`, `.png`, maksimal **5 MB** per foto (NFR-02) | Must |
| FR-7.4 | Validasi sisi klien (instan) dan sisi server (otoritatif) untuk tipe dan ukuran berkas | Must |
| FR-7.5 | Preview foto sebelum kirim dan opsi hapus foto | Should |
| FR-7.6 | Tombol **Lanjut: lokasi** membawa warga ke tahap penentuan lokasi (PBI-8) dengan data form tetap terjaga | Must |
| FR-7.7 | Tombol **Simpan draf** menyimpan isian sementara tanpa mengirim ke admin | Could |
| FR-7.8 | Pesan error per field berbahasa Indonesia dan jelas | Must |
| FR-7.9 | Saat pengiriman final, sistem menerbitkan **nomor tiket unik** (format usulan: `INFRA-YYYYMMDD-XXXX`) dan status awal `Menunggu Verifikasi` | Must |
| FR-7.10 | Panduan pelaporan (sidebar) menjelaskan syarat foto dan larangan menampilkan data pribadi | Could |

#### Aturan Validasi

| Field | Aturan |
|---|---|
| Judul | Wajib, 5–255 karakter |
| Kategori | Wajib, harus ID kategori aktif yang valid |
| Deskripsi | Wajib, minimal 20 karakter (usulan) |
| Foto | Wajib, jpg/jpeg/png, ≤ 5 MB per berkas, nama berkas disanitasi dan di-rename acak saat disimpan |

#### Acceptance Criteria
- **AC-7.1** Given warga login, when membuka *Buat Laporan*, then form tampil dengan semua field wajib bertanda `*`.
- **AC-7.2** Given field wajib kosong, when menekan *Lanjut*, then muncul pesan error per field dan proses tidak lanjut.
- **AC-7.3** Given foto berformat `.gif` atau berukuran > 5 MB, when diunggah, then ditolak dengan pesan yang menyebut batasan format/ukuran.
- **AC-7.4** Given semua data valid, when *Lanjut*, then berpindah ke langkah lokasi dan data form tidak hilang.
- **AC-7.5** Given laporan berhasil dikirim, then tiket unik terbit dan status = `Menunggu Verifikasi`.

---

### 4.2 PBI-8 — Pinpoint Lokasi Peta (IFD-12)

**User story:** Sebagai warga, saya ingin menandai titik kerusakan di peta tanpa harus berada di lokasi, agar tim teknis menemukan lokasi dengan presisi.

#### Functional Requirements

| ID | Requirement | Prioritas |
|---|---|---|
| FR-8.1 | Peta interaktif Leaflet.js + OpenStreetMap | Must |
| FR-8.2 | Memilih Kecamatan lalu Kelurahan (dropdown hierarki, PBI 3) otomatis memusatkan peta ke wilayah tersebut | Must |
| FR-8.3 | Kelurahan dinonaktifkan sampai kecamatan dipilih; mengganti kecamatan mengosongkan kelurahan dan memusatkan ulang peta | Must |
| FR-8.4 | Marker dapat digeser (*draggable*) dan peta dapat diklik untuk memindahkan marker | Must |
| FR-8.5 | Latitude dan longitude terisi otomatis dan ditampilkan ke warga saat marker bergerak | Must |
| FR-8.6 | Tombol **Gunakan lokasi saya** bersifat **opsional**; jika izin GPS ditolak, tampil pesan "pilih wilayah dan geser pin secara manual" — alur tidak boleh terblokir | Should |
| FR-8.7 | Field **Patokan lokasi** (teks bebas, mis. "dekat persimpangan") | Should |
| FR-8.8 | Tombol **Konfirmasi titik** hanya aktif setelah marker ditempatkan | Must |
| FR-8.9 | Validasi koordinat berada dalam batas wilayah Kota Bandung (bounding box); di luar batas → ditolak | Should |
| FR-8.10 | Simpan latitude `decimal(10,8)` dan longitude `decimal(11,8)` sesuai ERD | Must |
| FR-8.11 | Peta dan form dapat dioperasikan nyaman di layar ponsel (NFR-03) | Must |

#### Acceptance Criteria
- **AC-8.1** Given kecamatan "Antapani" dan kelurahan "Antapani Kidul" dipilih, then peta berpindah dan memusatkan ke area tersebut.
- **AC-8.2** Given marker digeser, then nilai latitude/longitude ikut berubah.
- **AC-8.3** Given izin GPS ditolak, when menekan *Gunakan lokasi saya*, then muncul pesan fallback dan warga tetap bisa memilih manual.
- **AC-8.4** Given koordinat belum ditentukan, when mengirim laporan, then ditolak dengan pesan "Tentukan titik lokasi kerusakan".
- **AC-8.5** Given koordinat di luar Kota Bandung, when konfirmasi, then ditolak dengan pesan yang jelas.
- **AC-8.6** Laporan tersimpan memuat `kelurahan_id`, `latitude`, `longitude` yang konsisten dengan pilihan warga.

---

### 4.3 PBI-9 — Fitur Ubah/Batal Laporan (IFD-13)

**User story:** Sebagai warga, saya ingin mengubah atau membatalkan laporan yang salah selama belum diverifikasi, agar data yang masuk ke admin tetap akurat.

#### Functional Requirements

| ID | Requirement | Prioritas |
|---|---|---|
| FR-9.1 | Dari menu **Laporan Saya**, warga dapat membuka detail laporannya sendiri | Must |
| FR-9.2 | Tombol **Simpan perubahan** dan **Batalkan laporan** hanya aktif pada status `Menunggu Verifikasi` (dan `Draf`) | Must |
| FR-9.3 | Field yang dapat diubah: judul, kategori, deskripsi, foto bukti, titik lokasi/wilayah | Must |
| FR-9.4 | Setelah status berubah dari `Menunggu Verifikasi`, tombol ubah/batal **dinonaktifkan disertai penjelasan** | Must |
| FR-9.5 | Pembatalan memerlukan **konfirmasi** dan **alasan pembatalan** | Must |
| FR-9.6 | Laporan yang dibatalkan **tidak dihapus**; statusnya menjadi `Dibatalkan` dan riwayat tetap tersimpan | Must |
| FR-9.7 | Laporan `Dibatalkan` tidak diteruskan ke admin/petugas dan tidak tampil di peta publik | Must |
| FR-9.8 | Warga hanya dapat mengakses laporan miliknya (otorisasi per pemilik) | Must |
| FR-9.9 | Setiap perubahan dan pembatalan dicatat ke log/audit (siapa, kapan, apa) | Should |
| FR-9.10 | Pengecekan status dilakukan **di server saat submit**, bukan hanya di UI, untuk mencegah *race condition* (admin sudah memproses saat warga masih membuka form) | Must |

#### Acceptance Criteria
- **AC-9.1** Given laporan berstatus `Menunggu Verifikasi`, when warga mengubah judul dan menyimpan, then perubahan tersimpan dan `updated_at` diperbarui.
- **AC-9.2** Given status sudah `Diproses`, then tombol ubah/batal nonaktif dan tampil penjelasan.
- **AC-9.3** Given warga menekan *Batalkan laporan*, when alasan kosong, then konfirmasi ditolak.
- **AC-9.4** Given pembatalan dikonfirmasi, then status = `Dibatalkan`, data tetap ada, dan tidak muncul di antrean verifikasi admin.
- **AC-9.5** Given status berubah menjadi `Diproses` saat form ubah terbuka, when warga submit, then server menolak dengan pesan status laporan sudah berubah.
- **AC-9.6** Given warga B mencoba membuka/ubah laporan warga A (URL langsung), then akses ditolak (403/404).

---

## 5. Aturan Status Laporan (relevan modul ini)

| Status | Dapat diubah warga | Dapat dibatalkan warga | Keterangan |
|---|---|---|---|
| Draf *(usulan)* | Ya | Ya | Belum dikirim ke admin |
| Menunggu Verifikasi | Ya | Ya | Status awal setelah kirim |
| Diproses | Tidak | Tidak | Setelah disetujui admin |
| Selesai | Tidak | Tidak | Dikerjakan petugas |
| Ditolak | Tidak | Tidak | Ditolak admin dengan alasan |
| Dibatalkan *(usulan)* | Tidak | — | Dibatalkan warga |

> `Draf` dan `Dibatalkan` belum tertulis di proposal; perlu disepakati dengan tim (lihat Bagian 9).

---

## 6. Kebutuhan Data

Tabel utama: `laporan_kerusakan` (sesuai ERD proposal).

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint PK | |
| nomor_tiket | varchar(50) | unik |
| warga_id | bigint FK → users | pemilik laporan |
| kelurahan_id | bigint FK → kelurahan | |
| kategori_id | bigint FK → kategori_infrastruktur | |
| judul | varchar(255) | |
| deskripsi_kondisi | text | |
| latitude | decimal(10,8) | |
| longitude | decimal(11,8) | |
| foto_bukti_sebelum | varchar(255) | path berkas |
| status | enum | tambah `draf`, `dibatalkan` |
| prioritas | enum | diisi admin (PBI 15) |
| alasan_penolakan | text | diisi admin |
| created_at / updated_at | timestamp | |

**Usulan perubahan skema (perlu dibahas):**
- `alasan_pembatalan` (text, nullable) dan `dibatalkan_pada` (timestamp, nullable) untuk FR-9.5/9.6.
- `patokan_lokasi` (varchar 255, nullable) untuk FR-8.7.
- Foto lebih dari satu: ERD saat ini hanya satu kolom `foto_bukti_sebelum`. Opsi: (a) batasi 1 foto di Sprint 1, atau (b) buat tabel `laporan_foto` (laporan_id, path, urutan).
- Penyimpanan berkas: *Local Asset Storage Directory* (storage Laravel, `php artisan storage:link`).

---

## 7. Kebutuhan Non-Fungsional

| Aspek | Target |
|---|---|
| Kinerja | Halaman form ≤ 3 detik pada 10 Mbps; peta/marker ≤ 2 detik (NFR-01) |
| Keamanan | CSRF token di semua form; validasi dan sanitasi input; validasi tipe MIME berkas di server; nama berkas di-rename; otorisasi pemilik (Policy Laravel); query lewat Eloquent/parameter binding (NFR-02) |
| Usability | Responsif mobile-first, tombol dan peta nyaman disentuh (NFR-03) |
| Keandalan | Skenario utama lolos E2E Laravel Dusk 100% (NFR-04) |
| Privasi | Foto tidak boleh menampilkan data pribadi (panduan di UI) |

---

## 8. Rencana Pengujian

### 8.1 Skenario Laravel Dusk (E2E)

| ID | Skenario | Hasil yang diharapkan |
|---|---|---|
| T-01 | Warga mengisi form valid + foto 1 MB, lanjut ke lokasi | Berpindah ke langkah peta |
| T-02 | Submit form kosong | Error per field tampil |
| T-03 | Unggah `.pdf` / berkas > 5 MB | Ditolak dengan pesan |
| T-04 | Pilih kecamatan → kelurahan | Peta terpusat ke kelurahan |
| T-05 | Geser marker | Koordinat berubah |
| T-06 | Kirim tanpa titik koordinat | Ditolak |
| T-07 | Kirim laporan lengkap | Tiket terbit, status Menunggu Verifikasi, data tersimpan |
| T-08 | Ubah laporan berstatus Menunggu Verifikasi | Perubahan tersimpan |
| T-09 | Buka ubah/batal pada status Diproses | Tombol nonaktif |
| T-10 | Batalkan dengan alasan | Status Dibatalkan, tidak masuk antrean admin |
| T-11 | Batalkan tanpa alasan | Ditolak |
| T-12 | Warga B mengakses laporan warga A | Akses ditolak |

### 8.2 Pengujian Lain
- **Unit/Feature test:** validasi `FormRequest`, policy kepemilikan, aturan transisi status.
- **Manual:** uji di ponsel (Chrome Android / Safari iOS), GPS ditolak, koneksi lambat.

---

## 9. Dependensi, Risiko, dan Pertanyaan Terbuka

### 9.1 Dependensi
| Dependensi | Pemilik | Dampak |
|---|---|---|
| Tabel & seeder `kecamatan`, `kelurahan` + dropdown hierarki (PBI 3) | Rakean | Diperlukan PBI-8 |
| Tabel & seeder `kategori_infrastruktur` (PBI 10) | I Nyoman | Diperlukan PBI-7 |
| Auth + role Warga (PBI 4–5) | Daffa Fachrurozi | Diperlukan semua PBI |
| Tabel `users` dan `laporan_kerusakan` (migrasi) | Maurellio | Dimiliki modul ini |
| Verifikasi/antrean admin (PBI 13–14) | Wahyu Wijaya | Membaca data & status dari modul ini |

**Mitigasi:** gunakan seeder dummy sementara untuk kecamatan, kelurahan, dan kategori bila PBI terkait belum merge; sepakati kontrak status enum lebih awal.

### 9.2 Risiko
| Risiko | Mitigasi |
|---|---|
| Dropdown/kategori belum siap saat pengembangan | Seeder dummy + antarmuka data disepakati |
| Tile OpenStreetMap lambat/terblokir | Pantau kinerja; pertimbangkan tile provider cadangan |
| Race condition ubah vs verifikasi admin | Cek status di server dalam transaksi DB (FR-9.10) |
| Unggahan berbahaya | Validasi MIME, rename berkas, simpan di luar direktori eksekusi |
| Tenggat 3 Nov sudah dekat | Prioritaskan *Must*, tunda *Could* (draf, preview) |

### 9.3 Pertanyaan Terbuka (perlu diputuskan tim)
1. Apakah status `Draf` dan `Dibatalkan` ditambahkan ke enum status? (mockup PBI 07 dan 09 sudah menampilkannya)
2. Satu foto atau banyak foto per laporan? (ERD: satu kolom; mockup: "usulan batas")
3. Apakah ada batas minimal panjang deskripsi dan jumlah maksimum foto?
4. Format final nomor tiket? (mockup: `INFRA-CONTOH-001`)
5. Apakah warga dapat mengubah lokasi (kelurahan) saat edit, atau hanya teks dan foto?
6. Siapa yang menyediakan batas koordinat Kota Bandung untuk validasi (FR-8.9)?

---

## 10. Definition of Done

- [ ] Semua requirement **Must** PBI-7, 8, 9 terimplementasi
- [ ] Acceptance criteria terpenuhi
- [ ] Skenario Dusk T-01 s.d. T-12 lolos
- [ ] Validasi server-side dan otorisasi pemilik berjalan
- [ ] Tampilan responsif diuji di ponsel dan desktop
- [ ] Migrasi dan seeder tersedia dan dapat dijalankan ulang
- [ ] Code review oleh minimal 1 anggota tim, merge ke branch utama
- [ ] Tiket IFD-11, IFD-12, IFD-13 berstatus Done

---

## 11. Rujukan Mockup

- PBI 07 · Laporkan kerusakan
- PBI 08 · Tentukan titik kerusakan
- PBI 09 · Ubah atau batalkan laporan
- PBI 03 · Pilih wilayah kerusakan (komponen dropdown yang dikonsumsi)

## 12. Riwayat Dokumen

| Versi | Tanggal | Penulis | Perubahan |
|---|---|---|---|
| 0.1 | 6 Nov 2026 | Maurellio Christopher Yonathan | Draf awal |
