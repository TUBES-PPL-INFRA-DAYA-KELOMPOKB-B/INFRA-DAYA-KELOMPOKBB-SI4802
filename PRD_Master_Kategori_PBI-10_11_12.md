# PRD: Master Kategori Kerusakan — INFRA-DAYA

| Atribut | Keterangan |
|---|---|
| **Produk** | INFRA-DAYA: Infrastruktur dan Daya Tanggap Kota Bandung |
| **Fitur** | Master Kategori |
| **Cakupan PBI** | PBI-10 CRUD Kategori Kerusakan · PBI-11 Tingkat Keparahan Default · PBI-12 Filter Jenis Kategori |
| **Sprint** | Sprint 1: Fondasi Sistem, Data Master, dan Pengerjaan Laporan (Minggu 5 / Minggu 10) |
| **PIC** | I Nyoman Aditya Wahyu Nughraha (102022400202) |
| **Kelompok** | Kelompok E |
| **Sumber** | Proposal Pengembangan Produk INFRA-DAYA V01 (5 Oktober 2026) |
| **Status** | Draft v0.1 |

---

## 1. Ringkasan

Fitur **Master Kategori** menyediakan pengelolaan klasifikasi jenis kerusakan infrastruktur publik yang menjadi dasar pelaporan warga, verifikasi admin, penetapan prioritas, peta sebaran, dan rekapitulasi laporan. Fitur ini terdiri dari tiga PBI:

| PBI | Nama | Ringkasan |
|---|---|---|
| PBI-10 | CRUD Kategori Kerusakan | Admin DSDABM menambah, melihat, mengubah, dan menghapus kategori kerusakan. |
| PBI-11 | Tingkat Keparahan Default | Setiap kategori memiliki tingkat keparahan default yang menjadi nilai awal prioritas laporan. |
| PBI-12 | Filter Jenis Kategori | Pengguna dapat menyaring daftar berdasarkan jenis kategori. |

## 2. Latar Belakang dan Masalah

Pada proses existing, aduan kerusakan tidak memiliki format data terstruktur sehingga Admin DSDABM kesulitan memvalidasi dan memprioritaskan penanganan. INFRA-DAYA memerlukan master data kategori yang baku agar:

1. Warga dapat memilih jenis kerusakan secara konsisten pada formulir laporan (SKF-02).
2. Admin dapat menetapkan skala prioritas dengan acuan awal yang jelas (SKF-05, SKF-07).
3. Peta publik, daftar aduan, dan rekap laporan dapat disaring per kategori (SKF-04, SKF-12).

Fitur ini berkaitan dengan SDGs 9 (Industri, Inovasi, dan Infrastruktur), Target 9.1.

## 3. Tujuan dan Metrik Keberhasilan

### 3.1 Tujuan
- Menyediakan modul master kategori yang dapat dikelola mandiri oleh Admin DSDABM tanpa perubahan kode.
- Menyediakan tingkat keparahan default per kategori sebagai dasar prioritas laporan.
- Mempermudah pencarian dan penyaringan data kategori.

### 3.2 Metrik Keberhasilan

| Metrik | Target |
|---|---|
| Seluruh skenario Laravel Dusk untuk PBI-10, 11, 12 lulus | 100% (NFR-04) |
| Waktu muat halaman daftar kategori | ≤ 3 detik pada koneksi ≥ 10 Mbps (NFR-01) |
| Empat kategori awal tersedia setelah seeding | 4 dari 4 |
| Akses oleh peran selain Admin ke fungsi tulis (create/update/delete) | Ditolak 100% (HTTP 403) |

## 4. Ruang Lingkup

### 4.1 In Scope
- Halaman kelola kategori kerusakan (daftar, tambah, ubah, hapus) untuk Admin DSDABM.
- Atribut tingkat keparahan default pada setiap kategori dan keterkaitannya dengan `bobot_prioritas`.
- Filter dan pencarian pada daftar kategori.
- Seeder data awal empat kategori infrastruktur.
- Penyediaan data kategori (hanya baca) untuk dropdown pada formulir laporan.
- Pengujian otomatis Laravel Dusk.

### 4.2 Out of Scope
- Penetapan prioritas final oleh admin saat verifikasi (PBI-15, PIC Wahyu Wijaya). PBI-11 hanya menyediakan nilai default.
- Filter pada daftar aduan (PBI-13) dan filter peta (PBI-23). Fitur tersebut dapat memakai data kategori dari fitur ini.
- Rekapitulasi per kategori (PBI-39) dan perhitungan skor prioritas (PBI-42).
- Pengelolaan master data wilayah (PBI-1, 2, 3) dan manajemen user (PBI-4, 5, 6).

## 5. Pengguna dan Hak Akses

| Peran | Hak Akses pada Fitur Ini |
|---|---|
| **Admin DSDABM** | CRUD kategori, mengatur tingkat keparahan default, menggunakan filter. |
| **Warga** | Hanya melihat daftar kategori aktif sebagai pilihan di formulir laporan. Tidak memiliki akses ke halaman kelola. |
| **Petugas Lapangan** | Hanya melihat nama kategori pada detail tugas. Tidak ada akses kelola. |
| **Pimpinan** | Hanya melihat kategori sebagai dimensi filter pada dashboard dan peta. Tidak ada akses kelola. |

Pembatasan akses menggunakan Role-Based Access Control (SKF-01).

## 6. Rincian Kebutuhan per PBI

### 6.1 PBI-10: CRUD Kategori Kerusakan

**User story**
> Sebagai Admin DSDABM, saya ingin menambah, melihat, mengubah, dan menghapus kategori kerusakan agar pilihan jenis kerusakan pada sistem selalu sesuai dengan kebutuhan dinas.

**Kebutuhan fungsional**

| ID | Kebutuhan |
|---|---|
| KF-10.1 | Sistem menampilkan daftar kategori dalam tabel berisi nama kategori, deskripsi, tingkat keparahan default, bobot prioritas, jumlah laporan terkait, dan aksi. |
| KF-10.2 | Admin dapat menambah kategori baru melalui formulir (nama, deskripsi, tingkat keparahan default). |
| KF-10.3 | Admin dapat mengubah data kategori yang sudah ada. |
| KF-10.4 | Admin dapat menghapus kategori setelah konfirmasi. Kategori yang sudah dipakai oleh laporan tidak dapat dihapus, dan sistem menampilkan pesan penjelasan. |
| KF-10.5 | Nama kategori harus unik (tidak sensitif huruf besar/kecil). |
| KF-10.6 | Daftar kategori dipaginasi (10 data per halaman). |
| KF-10.7 | Perubahan nama kategori otomatis tercermin pada laporan terkait karena relasi menggunakan `kategori_id`. |
| KF-10.8 | Data awal empat kategori tersedia melalui seeder: Jalan Raya/Trotoar, Saluran Drainase/Gorong-gorong, Penerangan Jalan Umum (PJU), dan Jembatan/Tanggul Air. |

**Aturan validasi**

| Field | Aturan |
|---|---|
| `nama_kategori` | Wajib, string, maksimal 100 karakter, unik. |
| `deskripsi` | Opsional, teks, maksimal 500 karakter. |
| `tingkat_keparahan_default` | Wajib, salah satu dari nilai yang didefinisikan pada PBI-11. |

**Kriteria penerimaan**

- **Given** Admin berada di halaman tambah kategori, **when** mengisi data valid dan menyimpan, **then** kategori muncul di daftar dan pesan sukses ditampilkan.
- **Given** nama kategori sudah ada, **when** Admin menyimpan, **then** sistem menolak dan menampilkan pesan "Nama kategori sudah digunakan".
- **Given** kategori sudah dipakai oleh minimal satu laporan, **when** Admin menekan hapus, **then** penghapusan ditolak dengan pesan penjelasan dan data tetap ada.
- **Given** kategori belum pernah dipakai laporan, **when** Admin mengonfirmasi hapus, **then** kategori hilang dari daftar.
- **Given** pengguna berperan Warga, Petugas, atau Pimpinan, **when** mengakses URL kelola kategori, **then** sistem mengembalikan HTTP 403.

### 6.2 PBI-11: Tingkat Keparahan Default

**User story**
> Sebagai Admin DSDABM, saya ingin setiap kategori memiliki tingkat keparahan default agar penetapan prioritas laporan memiliki nilai awal yang konsisten dan lebih cepat.

**Definisi tingkat keparahan** *(asumsi, lihat bagian 11)*

| Tingkat Keparahan | Nilai `bobot_prioritas` | Contoh Penggunaan |
|---|---|---|
| Rendah | 1 | Kerusakan kecil yang tidak mengganggu keselamatan. |
| Sedang | 2 | Mengganggu kenyamanan namun belum membahayakan. |
| Tinggi | 3 | Berpotensi membahayakan pengguna atau memicu kemacetan. |
| Kritis | 4 | Ancaman keselamatan langsung atau banjir/longsor. |

**Kebutuhan fungsional**

| ID | Kebutuhan |
|---|---|
| KF-11.1 | Setiap kategori wajib memiliki satu tingkat keparahan default yang dipilih dari daftar tingkat di atas. |
| KF-11.2 | Pilihan tingkat keparahan pada formulir kategori berupa dropdown dengan label berwarna (badge) agar mudah dibedakan. |
| KF-11.3 | Sistem menyimpan `bobot_prioritas` secara otomatis sesuai tingkat keparahan yang dipilih. |
| KF-11.4 | Saat laporan baru dibuat, sistem mengisi `prioritas` awal laporan dengan tingkat keparahan default dari kategori yang dipilih warga. |
| KF-11.5 | Admin masih dapat mengubah prioritas laporan saat verifikasi (PBI-15). Perubahan tingkat keparahan default pada kategori hanya berlaku untuk laporan baru dan tidak mengubah laporan yang sudah ada. |
| KF-11.6 | Tingkat keparahan default ditampilkan pada daftar kategori. |

**Kriteria penerimaan**

- **Given** Admin menambah kategori PJU dengan tingkat keparahan "Sedang", **when** disimpan, **then** `bobot_prioritas` bernilai 2 dan badge "Sedang" tampil di daftar.
- **Given** kategori Jembatan/Tanggul Air memiliki default "Kritis", **when** warga membuat laporan pada kategori tersebut, **then** prioritas awal laporan bernilai "Kritis".
- **Given** Admin mengubah default kategori dari "Sedang" ke "Tinggi", **when** disimpan, **then** laporan lama tidak berubah dan laporan baru memakai nilai "Tinggi".
- **Given** formulir kategori dikirim tanpa tingkat keparahan, **when** disimpan, **then** sistem menolak dan menampilkan pesan validasi.

### 6.3 PBI-12: Filter Jenis Kategori

**User story**
> Sebagai Admin DSDABM, saya ingin menyaring dan mencari data kategori berdasarkan jenis dan tingkat keparahan agar saya dapat menemukan data dengan cepat.

**Kebutuhan fungsional**

| ID | Kebutuhan |
|---|---|
| KF-12.1 | Halaman daftar kategori menyediakan kolom pencarian berdasarkan nama kategori. |
| KF-12.2 | Halaman daftar menyediakan filter dropdown berdasarkan tingkat keparahan default (Semua, Rendah, Sedang, Tinggi, Kritis). |
| KF-12.3 | Filter dan pencarian dapat dikombinasikan. |
| KF-12.4 | Hasil filter diperbarui tanpa kehilangan paginasi, dan parameter filter tetap tersimpan pada URL (query string) agar dapat dibagikan atau dimuat ulang. |
| KF-12.5 | Tersedia tombol "Reset" untuk menghapus seluruh filter. |
| KF-12.6 | Jika tidak ada data yang cocok, sistem menampilkan pesan "Data kategori tidak ditemukan". |
| KF-12.7 | Komponen filter kategori dibuat sebagai komponen Blade yang dapat dipakai ulang oleh modul lain (daftar aduan, peta sebaran, rekapitulasi) dengan sumber data kategori yang sama. |

**Kriteria penerimaan**

- **Given** terdapat empat kategori, **when** Admin mengetik "drainase" pada kolom pencarian, **then** hanya kategori yang namanya mengandung kata tersebut yang tampil.
- **Given** Admin memilih filter "Kritis", **when** filter diterapkan, **then** hanya kategori dengan tingkat keparahan default "Kritis" yang tampil.
- **Given** Admin memakai pencarian dan filter sekaligus, **when** diterapkan, **then** hasil memenuhi kedua kondisi.
- **Given** Admin menekan "Reset", **then** seluruh filter dihapus dan daftar penuh ditampilkan.
- **Given** hasil filter kosong, **then** pesan "Data kategori tidak ditemukan" muncul.

## 7. Rancangan Data

Fitur ini memakai tabel `kategori_infrastruktur` dari ERD proposal (bagian 4.3) dengan satu penambahan kolom.

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint, PK | Identitas kategori. |
| `nama_kategori` | varchar(100), unique | Nama kategori. |
| `deskripsi` | text, nullable | Penjelasan singkat kategori. |
| `tingkat_keparahan_default` | enum('rendah','sedang','tinggi','kritis') | **Kolom baru** untuk PBI-11. |
| `bobot_prioritas` | int | Sudah ada pada ERD. Diisi otomatis dari tingkat keparahan (1 sampai 4). |
| `created_at`, `updated_at` | timestamp | Sudah ada pada ERD. |

**Relasi:** `kategori_infrastruktur` 1 : N `laporan_kerusakan` melalui `kategori_id`. Foreign key menggunakan `ON DELETE RESTRICT` agar kategori yang dipakai tidak dapat terhapus.

## 8. Rancangan Antarmuka dan Alur

### 8.1 Halaman dan Rute

| Halaman | Rute (usulan) | Akses |
|---|---|---|
| Daftar kategori + filter | `GET /admin/kategori` | Admin |
| Formulir tambah | `GET /admin/kategori/create` | Admin |
| Simpan | `POST /admin/kategori` | Admin |
| Formulir ubah | `GET /admin/kategori/{id}/edit` | Admin |
| Perbarui | `PUT /admin/kategori/{id}` | Admin |
| Hapus | `DELETE /admin/kategori/{id}` | Admin |

### 8.2 Alur Utama
1. Admin login lalu membuka menu **Master Data → Kategori Kerusakan**.
2. Sistem menampilkan daftar kategori dengan kolom pencarian dan filter tingkat keparahan.
3. Admin menekan **Tambah Kategori**, mengisi nama, deskripsi, dan tingkat keparahan default, lalu menyimpan.
4. Sistem memvalidasi, menyimpan, mengisi `bobot_prioritas`, dan kembali ke daftar dengan pesan sukses.
5. Admin dapat mengubah atau menghapus kategori dari kolom aksi.

### 8.3 Alur Alternatif
- Validasi gagal: formulir ditampilkan kembali dengan pesan kesalahan per field dan input sebelumnya dipertahankan.
- Penghapusan kategori yang sedang dipakai: ditolak dengan pesan penjelasan.

## 9. Kebutuhan Non-Fungsional

| ID | Kebutuhan | Penerapan pada Fitur Ini |
|---|---|---|
| NFR-01 | Kinerja | Daftar kategori dimuat ≤ 3 detik, memakai paginasi dan query ber-indeks pada `nama_kategori`. |
| NFR-02 | Keamanan | Seluruh formulir dilindungi token CSRF, input disanitasi, query memakai Eloquent ORM (pencegahan SQL Injection), dan akses dibatasi middleware peran Admin. |
| NFR-03 | Usability | Halaman responsif dan dapat dioperasikan di ponsel maupun desktop (Tailwind CSS). |
| NFR-04 | Pengujian | Skenario E2E Laravel Dusk lulus 100% (lihat bagian 10). |
| NFR-05 | Ketersediaan | Perubahan skema dilakukan melalui migration agar konsisten dan dapat di-rollback. |

## 10. Rencana Pengujian (Laravel Dusk)

| ID | Skenario | Hasil yang Diharapkan |
|---|---|---|
| T-10.1 | Admin menambah kategori valid | Data tampil di daftar, pesan sukses muncul. |
| T-10.2 | Admin menambah kategori dengan nama duplikat | Ditolak dengan pesan validasi. |
| T-10.3 | Admin mengubah nama dan deskripsi kategori | Perubahan tersimpan dan tampil di daftar. |
| T-10.4 | Admin menghapus kategori yang belum dipakai | Kategori hilang dari daftar. |
| T-10.5 | Admin menghapus kategori yang sudah dipakai laporan | Ditolak, data tetap ada. |
| T-10.6 | Warga mengakses halaman kelola kategori | Ditolak (HTTP 403). |
| T-11.1 | Admin menyimpan kategori tanpa tingkat keparahan | Ditolak dengan pesan validasi. |
| T-11.2 | Admin memilih tingkat "Tinggi" | `bobot_prioritas` tersimpan 3 dan badge sesuai. |
| T-11.3 | Warga membuat laporan pada kategori berdefault "Kritis" | Prioritas awal laporan "Kritis". |
| T-11.4 | Default kategori diubah setelah laporan dibuat | Laporan lama tidak berubah. |
| T-12.1 | Pencarian berdasarkan nama | Hanya data yang cocok tampil. |
| T-12.2 | Filter tingkat keparahan | Hanya data dengan tingkat terpilih tampil. |
| T-12.3 | Kombinasi pencarian dan filter | Hasil memenuhi kedua kondisi. |
| T-12.4 | Reset filter dan hasil kosong | Daftar penuh kembali, pesan kosong muncul saat tidak ada hasil. |

## 11. Asumsi, Dependensi, dan Risiko

### 11.1 Asumsi
1. Proposal belum mendefinisikan nilai "tingkat keparahan". Dokumen ini mengusulkan empat tingkat (Rendah, Sedang, Tinggi, Kritis) yang dipetakan ke `bobot_prioritas` 1 sampai 4. **Perlu dikonfirmasi tim/dosen.**
2. ERD proposal belum memuat kolom `tingkat_keparahan_default`. Kolom ini ditambahkan, dan ERD serta class diagram `KategoriInfrastruktur` perlu diperbarui.
3. "Kategori kerusakan" pada PBI sama dengan entitas `kategori_infrastruktur` pada ERD.
4. "Filter jenis kategori" pada PBI-12 diterapkan pada halaman kelola kategori, dengan komponen yang dirancang agar dapat dipakai ulang oleh modul lain.
5. Autentikasi dan role Admin (PBI-4, PBI-5) tersedia atau dibuat sementara melalui middleware sederhana pada Sprint 1.

### 11.2 Dependensi

| Dependensi | Keterangan |
|---|---|
| Autentikasi dan role (PBI-4, 5) | Diperlukan untuk pembatasan akses Admin. |
| Pengajuan laporan (PBI-7) | Memakai data kategori pada dropdown dan pengisian prioritas awal. |
| Daftar aduan, penetapan prioritas (PBI-13, 15) | Memakai filter kategori dan nilai default. |
| Peta, rekap kategori (PBI-23, 39) | Memakai data dan filter kategori. |

### 11.3 Risiko

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Definisi tingkat keparahan berubah | Perubahan skema dan data | Gunakan enum yang mudah diubah lewat migration dan pusatkan pemetaan bobot pada satu konstanta/enum class. |
| Integrasi dengan PBI-7 dan PBI-15 oleh PIC berbeda | Ketidaksesuaian antarmuka data | Sepakati nama field dan nilai enum sejak awal Sprint 1. |
| Penghapusan kategori yang sudah dipakai | Kehilangan integritas data | Foreign key `RESTRICT` dan validasi di sisi aplikasi. |

## 12. Definition of Done

- [ ] Migration dan seeder (empat kategori awal) berjalan tanpa galat.
- [ ] Seluruh kriteria penerimaan PBI-10, 11, 12 terpenuhi.
- [ ] Akses fungsi kelola hanya untuk Admin DSDABM.
- [ ] Semua skenario Laravel Dusk pada bagian 10 lulus.
- [ ] Tampilan responsif pada desktop dan ponsel.
- [ ] Kode di-merge ke branch utama melalui Git setelah ditinjau anggota tim.
- [ ] ERD dan class diagram diperbarui sesuai penambahan kolom.

## 13. Jadwal

| Item | Waktu |
|---|---|
| Sprint | Sprint 1 |
| Periode | Minggu 5 (Minggu 10 kalender) |
| PIC | I Nyoman Aditya Wahyu Nughraha |
| Urutan pengerjaan | PBI-10 → PBI-11 → PBI-12 |

## 14. Referensi Kebutuhan Proposal

| Referensi | Keterkaitan |
|---|---|
| SKF-02 | Kategori kerusakan pada formulir laporan. |
| SKF-04 | Filter peta berdasarkan kategori. |
| SKF-05 | Manajemen master data jenis infrastruktur dan bobot prioritas. |
| SKF-07 | Penetapan skala prioritas. |
| SKF-12 | Rekap dan ekspor per kategori. |
| UC-03 | Kelola Master Data Wilayah dan Kategori. |
| NFR-01 sampai NFR-05, TEK-01 sampai TEK-06 | Batasan kualitas dan teknologi. |
