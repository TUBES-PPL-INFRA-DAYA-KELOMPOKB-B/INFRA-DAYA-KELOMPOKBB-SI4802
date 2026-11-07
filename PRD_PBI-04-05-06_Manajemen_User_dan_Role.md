# PRD — Manajemen User & Role (PBI 4, 5, 6)

**Produk:** INFRA-DAYA: Infrastruktur dan Daya Tanggap Kota Bandung
**Sprint:** Sprint 1 — Fondasi Sistem, Data Master, dan Pengerjaan Laporan (Minggu 4 / Minggu 9)
**Fitur:** Manajemen User & Role
**PIC:** Muhammad Daffa Fachrurozi (102022400151)
**Sumber:** Proposal Pengembangan Produk INFRA-DAYA, Kelompok E (5 Oktober 2026)
**Status:** Draft v0.1

---

## 1. Ringkasan

Fitur **Manajemen User & Role** adalah fondasi akses INFRA-DAYA. Fitur ini memungkinkan Admin DSDABM mengelola akun pengguna (CRUD), mengatur hak akses berbasis peran (*role-based access control*), serta mencatat aktivasi akun dan log aktivitas sehingga semua tindakan penting dapat diaudit.

Fitur ini menopang hampir semua fitur lain, karena setiap halaman dan aksi di sistem bergantung pada identitas dan peran pengguna.

### PBI dalam dokumen ini

| PBI | Nama | Ringkasan |
|---|---|---|
| 4 | CRUD user | Admin menambah, melihat, mengubah, dan menghapus akun pengguna |
| 5 | Mengatur role akses | Admin menetapkan dan mengubah peran pengguna serta membatasi akses fitur per peran |
| 6 | Pencatatan aktivasi + log | Sistem mencatat aktivasi/nonaktivasi akun dan jejak aktivitas terkait akun |

### Keterkaitan dengan proposal

| Referensi | Keterangan |
|---|---|
| SKF-01 | Manajemen Otentikasi dan Akun Pengguna (RBAC) |
| UC-01 | Registrasi & Login |
| NFR-02 | Keamanan (Bcrypt, CSRF, sanitasi input) |
| NFR-04 | Pengujian E2E Laravel Dusk, kelulusan 100% |
| Tabel `users` | Sudah dirancang di ERD (bagian 4.3) |

---

## 2. Latar Belakang & Masalah

Proposal menetapkan **4 peran**: Warga, Admin Dinas (DSDABM), Petugas Lapangan, dan Pimpinan. Masing-masing punya hak akses yang berbeda dan terisolasi. Tanpa manajemen user dan role yang baik:

- Admin tidak bisa membuat dan mengelola akun Petugas Lapangan atau Pimpinan.
- Hak akses tiap peran tidak bisa ditegakkan, sehingga data sensitif dan aksi administratif berisiko diakses pihak yang salah.
- Tidak ada jejak siapa mengubah atau menonaktifkan akun, sehingga tidak ada akuntabilitas.

---

## 3. Tujuan & Metrik Keberhasilan

| Tujuan | Indikator Keberhasilan |
|---|---|
| Admin dapat mengelola seluruh akun pengguna dari satu modul | Seluruh operasi CRUD user berfungsi dan lolos uji Dusk |
| Akses fitur dibatasi sesuai peran | Pengguna non-berwenang yang membuka halaman terlarang mendapat penolakan (403) |
| Setiap perubahan status akun dan aksi akun penting tercatat | Setiap aksi PBI 4-6 menghasilkan entri log yang dapat dilihat Admin |
| Keamanan kredensial terjamin | Password tersimpan Bcrypt, tidak pernah tampil di UI maupun log |

---

## 4. Ruang Lingkup

### 4.1 Dalam Cakupan (In Scope)

- Modul pengelolaan user oleh Admin: daftar, tambah, detail, ubah, hapus.
- Penetapan dan perubahan peran: `warga`, `admin`, `petugas`, `pimpinan`.
- Middleware pembatas akses berdasarkan peran.
- Pengaktifan dan penonaktifan akun.
- Pencatatan log aktivasi dan aktivitas akun serta halaman untuk melihatnya.

### 4.2 Di Luar Cakupan (Out of Scope)

- Registrasi mandiri Warga, login/logout, dan profil akun (dicakup UC-01 / SKF-01, di luar PBI 4-6; diasumsikan dikerjakan sebagai bagian autentikasi dasar).
- Notifikasi ke pengguna (PBI 28).
- Dashboard SLA dan Pimpinan (PBI 31-36).
- Integrasi login pihak ketiga (SSO, OAuth).
- Pemulihan password via email (belum ada di proposal).

---

## 5. Pengguna & Hak Akses

| Peran | Hak pada Fitur Ini |
|---|---|
| **Admin DSDABM** | Pengguna utama: CRUD user, atur role, aktifkan/nonaktifkan akun, lihat log |
| **Warga** | Tidak punya akses ke modul ini (hanya akun miliknya yang dikelola lewat profil) |
| **Petugas Lapangan** | Tidak punya akses ke modul ini |
| **Pimpinan** | Tidak punya akses ke modul ini (opsional: lihat-saja log, lihat pertanyaan terbuka) |

---

## 6. User Stories & Acceptance Criteria

### PBI 4 — CRUD User

**User story:** Sebagai **Admin DSDABM**, saya ingin menambah, melihat, mengubah, dan menghapus akun pengguna agar data pengguna sistem selalu akurat dan terkendali.

| ID | Kebutuhan | Acceptance Criteria |
|---|---|---|
| 4.1 | **Daftar user** | Admin melihat tabel user berisi nama, email, telepon, role, status akun, dan tanggal dibuat. Tersedia pencarian (nama/email) dan filter role. Data dibagi per halaman (pagination). |
| 4.2 | **Tambah user** | Admin mengisi nama, email, telepon, password, dan role. Email harus unik dan valid. Password minimal 8 karakter dan disimpan Bcrypt. Setelah sukses, data muncul di daftar dan sistem menampilkan pesan berhasil. |
| 4.3 | **Detail user** | Admin dapat membuka detail satu user (data akun, role, status, dan riwayat log akun tersebut). |
| 4.4 | **Ubah user** | Admin mengubah nama, email, telepon, dan role. Password hanya berubah bila diisi. Validasi sama dengan tambah user (email unik, mengecualikan user itu sendiri). |
| 4.5 | **Hapus user** | Admin menghapus user setelah dialog konfirmasi. Admin tidak dapat menghapus akunnya sendiri. User yang sudah memiliki data terkait (laporan, disposisi) tidak boleh dihapus permanen tanpa penanganan (lihat Catatan 9.1). |
| 4.6 | **Validasi & keamanan** | Semua form dilindungi token CSRF. Input disanitasi. Pesan error validasi tampil per field dalam Bahasa Indonesia. |

### PBI 5 — Mengatur Role Akses

**User story:** Sebagai **Admin DSDABM**, saya ingin menetapkan dan mengubah peran tiap pengguna agar setiap orang hanya dapat mengakses fitur sesuai kewenangannya.

| ID | Kebutuhan | Acceptance Criteria |
|---|---|---|
| 5.1 | **Empat role baku** | Sistem hanya mengenal role `warga`, `admin`, `petugas`, `pimpinan` (sesuai enum di tabel `users`). |
| 5.2 | **Ubah role user** | Admin dapat mengganti role user lewat form ubah user atau aksi khusus "Ubah Role". Perubahan berlaku pada permintaan berikutnya (tidak perlu menunggu sesi baru). |
| 5.3 | **Pembatasan akses (middleware)** | Rute dikelompokkan per role. Akses ke rute di luar kewenangan ditolak dengan HTTP 403 dan halaman pesan yang ramah. Pengguna belum login diarahkan ke halaman login. |
| 5.4 | **Menu dinamis** | Menu navigasi hanya menampilkan item sesuai role pengguna yang login. |
| 5.5 | **Perlindungan admin terakhir** | Sistem mencegah penurunan role atau penonaktifan akun jika itu adalah satu-satunya Admin aktif. |
| 5.6 | **Admin tidak mengubah role sendiri** | Admin tidak dapat mengubah role akunnya sendiri (mencegah lock-out tidak sengaja). |

**Matriks akses ringkas** (diturunkan dari "Karakteristik Pengguna", bagian 3.1.2 proposal):

| Area Fitur | Warga | Admin | Petugas | Pimpinan |
|---|:---:|:---:|:---:|:---:|
| Buat laporan, linimasa, rating | ✔ | | | |
| Peta publik sebaran | ✔ | ✔ | ✔ | ✔ |
| Master data, verifikasi, disposisi | | ✔ | | |
| **Manajemen user & role (PBI 4-6)** | | ✔ | | |
| Daftar tugas, update progres | | | ✔ | |
| Dashboard SLA & eksekutif | | ✔ | | ✔ |
| Ekspor laporan | | ✔ | | ✔ |

### PBI 6 — Pencatatan Aktivasi + Log

**User story:** Sebagai **Admin DSDABM**, saya ingin akun dapat diaktifkan atau dinonaktifkan dan semua perubahan penting tercatat agar ada akuntabilitas dan jejak audit.

| ID | Kebutuhan | Acceptance Criteria |
|---|---|---|
| 6.1 | **Aktivasi / nonaktivasi akun** | Admin dapat menonaktifkan dan mengaktifkan kembali akun. Akun nonaktif tidak dapat login dan menerima pesan yang jelas. |
| 6.2 | **Pencatatan aktivasi** | Setiap perubahan status aktif/nonaktif mencatat: siapa yang melakukan, akun target, status sebelum dan sesudah, waktu, dan alasan (opsional). |
| 6.3 | **Log aktivitas akun** | Sistem mencatat minimal: pembuatan akun, perubahan data, perubahan role, penghapusan, login berhasil, dan login gagal. |
| 6.4 | **Halaman log** | Admin dapat melihat daftar log dengan urutan terbaru, filter berdasarkan jenis aksi, user, dan rentang tanggal, serta pagination. |
| 6.5 | **Log tidak dapat diubah** | Log bersifat hanya-tambah (*append-only*): tidak ada fitur ubah atau hapus log dari UI. |
| 6.6 | **Data sensitif tidak dicatat** | Password (plain maupun hash) tidak pernah masuk ke log. |

---

## 7. Alur Pengguna (Ringkas)

**Admin menambah user baru (PBI 4 + 5 + 6)**

1. Admin membuka menu *Manajemen User* dan memilih *Tambah User*.
2. Admin mengisi nama, email, telepon, password, dan role.
3. Sistem memvalidasi data (email unik, format, panjang password).
4. Sistem menyimpan akun (password di-hash Bcrypt) dengan status aktif.
5. Sistem menulis entri log "akun dibuat" dan menampilkan pesan berhasil.

**Alur alternatif:** validasi gagal → sistem menampilkan pesan error per field dan data isian tetap terisi.

**Admin menonaktifkan akun (PBI 6)**

1. Admin membuka daftar user dan memilih aksi *Nonaktifkan* (dengan alasan opsional).
2. Sistem memeriksa bahwa akun bukan admin aktif terakhir dan bukan akun admin itu sendiri.
3. Sistem mengubah status, menulis log aktivasi, dan menampilkan konfirmasi.
4. Saat user tersebut mencoba login, sistem menolak dan memberi pesan akun nonaktif.

---

## 8. Kebutuhan Data

### 8.1 Tabel `users` (sesuai ERD proposal)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint (PK) | |
| name | varchar 255 | |
| email | varchar 255 | unik |
| password | varchar 255 | Bcrypt |
| phone | varchar 20 | |
| role | enum: `warga`, `admin`, `petugas`, `pimpinan` | |
| created_at, updated_at | timestamp | |

### 8.2 Usulan Penambahan (belum ada di ERD proposal — perlu disepakati tim)

Agar PBI 6 dapat dipenuhi, diperlukan penambahan berikut.

**Kolom baru di `users`**

| Kolom | Tipe | Keterangan |
|---|---|---|
| is_active | boolean, default true | Status aktivasi akun |
| deactivated_at | timestamp, nullable | Waktu terakhir dinonaktifkan |
| deleted_at | timestamp, nullable | Untuk *soft delete* (lihat Catatan 9.1) |

**Tabel baru `user_logs`**

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint (PK) | |
| actor_id | bigint (FK → users), nullable | Pelaku aksi (null untuk login gagal tanpa akun) |
| target_user_id | bigint (FK → users), nullable | Akun yang terdampak |
| action | varchar 50 | contoh: `user_created`, `role_changed`, `account_deactivated`, `login_failed` |
| old_value | json, nullable | Nilai sebelum (tanpa data sensitif) |
| new_value | json, nullable | Nilai sesudah (tanpa data sensitif) |
| reason | text, nullable | Alasan, bila ada |
| ip_address | varchar 45, nullable | |
| created_at | timestamp | |

---

## 9. Catatan Teknis & Asumsi

### 9.1 Asumsi

- **Soft delete:** user yang sudah punya laporan, disposisi, atau hasil perbaikan terkait sebaiknya di-*soft delete* agar relasi di tabel lain tidak rusak.
- **Pembuatan akun:** Warga mendaftar mandiri (SKF-01); akun `admin`, `petugas`, dan `pimpinan` dibuat oleh Admin lewat modul ini.
- **Pembagian pekerjaan:** halaman login/registrasi dikerjakan sebagai bagian autentikasi dasar, di luar PBI 4-6.
- **Atribut spesifik peran** pada Class Diagram (NIK untuk Warga, NIP untuk Admin, `idPetugas` dan `timUnit` untuk Petugas, `jabatan` untuk Pimpinan) belum ada di tabel `users`. Untuk Sprint 1, field tersebut ditunda kecuali tim memutuskan lain.

### 9.2 Implementasi (selaras bagian 3.3 proposal)

- PHP 8.2+, Laravel 10.x/11.x (MVC), MySQL 8.0, Blade + Tailwind CSS.
- Pembatasan akses lewat Laravel **Middleware** per role (mis. `role:admin`) dan atau *Gate/Policy*.
- Password: `Hash::make()` (Bcrypt). CSRF bawaan Laravel, validasi via *Form Request*.
- Log ditulis lewat *Observer* / *Event Listener* agar konsisten dan tidak tertinggal.

---

## 10. Kebutuhan Non-Fungsional

| ID | Kebutuhan | Target untuk Fitur Ini |
|---|---|---|
| NFR-01 | Kinerja | Halaman daftar user dan log termuat ≤ 3 detik (dengan pagination) |
| NFR-02 | Keamanan | Bcrypt, CSRF, sanitasi input, proteksi SQL injection (Eloquent/Query Builder) |
| NFR-03 | Usability | Tampilan responsif, tabel dapat digulir di layar kecil |
| NFR-04 | Keandalan | Skenario Dusk untuk PBI 4-6 lulus 100% |
| NFR-05 | Ketersediaan | Konsistensi data terjaga (transaksi DB untuk aksi yang menulis user + log sekaligus) |

---

## 11. Rencana Pengujian (Laravel Dusk — E2E)

| No | Skenario | Hasil yang Diharapkan |
|---|---|---|
| T-01 | Admin menambah user dengan data valid | User tersimpan, muncul di daftar, log "dibuat" tercatat |
| T-02 | Tambah user dengan email duplikat | Muncul error validasi, data tidak tersimpan |
| T-03 | Admin mengubah data user | Data berubah, log perubahan tercatat |
| T-04 | Admin menghapus user (dengan konfirmasi) | User hilang dari daftar, log tercatat |
| T-05 | Admin mencoba menghapus akunnya sendiri | Ditolak dengan pesan |
| T-06 | Admin mengubah role user | Role berubah dan akses menu ikut berubah |
| T-07 | Warga membuka URL modul user | Ditolak (403) |
| T-08 | Petugas dan Pimpinan membuka URL modul user | Ditolak (403) |
| T-09 | Pengguna belum login membuka modul user | Diarahkan ke halaman login |
| T-10 | Menonaktifkan akun lalu user mencoba login | Login ditolak, pesan akun nonaktif muncul |
| T-11 | Mengaktifkan kembali akun | User dapat login lagi, log aktivasi tercatat |
| T-12 | Menonaktifkan/menurunkan role admin aktif terakhir | Ditolak dengan pesan |
| T-13 | Filter dan pencarian di daftar user dan halaman log | Hasil sesuai filter |
| T-14 | Cek isi log | Tidak ada data password pada log |

---

## 12. Dependensi

| Dependensi | Keterangan |
|---|---|
| Autentikasi dasar (login/logout/registrasi Warga) | Prasyarat agar peran dan sesi dapat diuji |
| Tabel `users` & migrasi awal | Skema dasar dari ERD |
| Seeder akun Admin awal | Agar ada admin pertama untuk mengelola akun lain |
| Layout dasar (Blade + Tailwind) | Konsistensi tampilan antar-modul |

**Fitur yang bergantung pada PBI ini:** seluruh fitur lain yang memakai pembatasan role, terutama Master Data (PBI 1-3), Verifikasi (PBI 13-15), Disposisi (PBI 16-21), dan Dashboard (PBI 31-36).

---

## 13. Risiko

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Penghapusan user merusak relasi data | Data laporan atau disposisi yatim | Gunakan soft delete; cegah hapus permanen |
| Admin terkunci dari sistem | Tidak ada yang bisa mengelola akun | Proteksi admin terakhir + seeder admin awal |
| Skema `user_logs` belum disepakati tim | Konflik migrasi antar anggota | Sepakati ERD tambahan sebelum mulai coding |
| Log membengkak | Query lambat | Pagination, indeks pada `created_at` dan `action` |

---

## 14. Definition of Done

- [ ] Seluruh acceptance criteria PBI 4, 5, dan 6 terpenuhi.
- [ ] Migrasi dan seeder berjalan bersih di lingkungan baru.
- [ ] Skenario Dusk T-01 s.d. T-14 lulus 100%.
- [ ] Tidak ada data sensitif (password) pada tampilan maupun log.
- [ ] Tampilan responsif di layar desktop dan ponsel.
- [ ] Kode sudah di-merge ke repositori Git melalui *pull request* yang direview anggota tim.
- [ ] Demo di akhir Sprint 1 berhasil.

---

## 15. Pertanyaan Terbuka (untuk didiskusikan tim / dosen)

1. Apakah Pimpinan boleh melihat log secara *read-only*?
2. Apakah perlu field NIK/NIP/`idPetugas` di tabel `users` pada Sprint 1, atau cukup role dan data dasar?
3. Apakah kebijakan hapus user berupa soft delete, atau hard delete dengan pemeriksaan relasi?
4. Apakah penambahan kolom `is_active` dan tabel `user_logs` disetujui sebagai perubahan ERD?
5. Apakah log login (berhasil dan gagal) masuk lingkup PBI 6, atau hanya aktivasi dan perubahan data akun?
