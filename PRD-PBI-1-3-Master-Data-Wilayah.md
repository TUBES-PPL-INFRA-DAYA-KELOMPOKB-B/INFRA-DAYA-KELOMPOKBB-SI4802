# PRD: PBI-1 sampai PBI-3, Master Data Wilayah (INFRA-DAYA)

| Atribut | Keterangan |
|---|---|
| Produk | INFRA-DAYA (Infrastruktur dan Daya Tanggap Kota Bandung) |
| Sprint | Sprint 1: Fondasi Sistem, Data Master, dan Pengajuan Laporan |
| Modul | Master Data Wilayah |
| PIC | Rakean Ahmad Zayyid Ardhi (NIM 102022400160) |
| Sumber | Proposal Pengembangan Produk PPL, Kelompok E, bagian 5.1 dan 5.2 |
| Versi dokumen | 0.1 (Draf) |
| Status | Draf, menunggu review |

---

## 1. Ringkasan

Dokumen ini mendefinisikan kebutuhan untuk tiga product backlog item (PBI) pertama pada Sprint 1: CRUD kecamatan, CRUD kelurahan (desa), dan dropdown wilayah berjenjang. Ketiganya membentuk data master wilayah 30 kecamatan dan 151 kelurahan di Kota Bandung, yang menjadi dasar pengajuan laporan warga di UC-02 dan pemetaan titik kerusakan.

## 2. Latar Belakang dan Masalah

Warga dan admin DSDABM perlu memilih lokasi kerusakan dari daftar wilayah yang baku. Tanpa master data yang terkelola, nama wilayah bisa ditulis berbeda-beda, sehingga laporan sulit dipetakan dan dihitung per kecamatan untuk dashboard SLA. Proposal (SKF-05 dan UC-03) menetapkan bahwa Admin DSDABM mengelola data kecamatan dan kelurahan, sedangkan SKF-02 menetapkan pemilihan wilayah secara berjenjang oleh warga.

## 3. Tujuan dan Non-Tujuan

**Tujuan:**
1. Admin DSDABM dapat menambah, melihat, mengubah, dan menghapus data kecamatan.
2. Admin DSDABM dapat menambah, melihat, mengubah, dan menghapus data kelurahan dan menautkannya ke kecamatan induk.
3. Formulir pelaporan dapat menampilkan pilihan kelurahan yang sesuai dengan kecamatan yang dipilih.
4. Data awal sebanyak 30 kecamatan dan 151 kelurahan tersedia di sistem.

**Non-tujuan (di luar PBI-1 sampai PBI-3):**
- Pemusatan peta ke kelurahan yang dipilih (bagian dari SKF-03 dan UC-02, dikerjakan setelah PBI ini).
- Master kategori infrastruktur, manajemen user, dan role (PBI-4 dan seterusnya).
- Import data wilayah dari berkas Excel atau sumber resmi lain.
- Riwayat perubahan data master (audit log per record).

## 4. Pengguna dan Hak Akses

| Peran | Akses |
|---|---|
| Admin DSDABM | CRUD kecamatan dan kelurahan |
| Warga | Hanya membaca dropdown wilayah saat membuat laporan (melalui fitur pelaporan, bukan halaman master data) |
| Petugas Lapangan dan Pimpinan | Tidak mengakses halaman master data |

Akses ke halaman master data dibatasi melalui RBAC (SKF-01). Pengguna selain Admin tidak dapat membuka halaman CRUD.

## 5. User Story

| ID | PBI | User story | Prioritas |
|---|---|---|---|
| US-01 | PBI-1 | Sebagai admin DSDABM, saya ingin menambah, mengubah, dan menghapus data kecamatan, agar daftar kecamatan selalu sesuai dengan wilayah administratif Kota Bandung. | Must |
| US-02 | PBI-2 | Sebagai admin DSDABM, saya ingin menambah, mengubah, dan menghapus data kelurahan pada kecamatan tertentu, agar setiap kelurahan tercatat di bawah kecamatan yang benar. | Must |
| US-03 | PBI-3 | Sebagai warga, saya ingin memilih kecamatan lalu kelurahan dari daftar berjenjang, agar lokasi kerusakan tercatat dengan wilayah yang tepat. | Must |

## 6. Kebutuhan Fungsional

### 6.1 PBI-1: CRUD Kecamatan

Sumber: SKF-05, tabel kecamatan (ERD 4.3).

| ID | Kebutuhan | Keterangan |
|---|---|---|
| FR-01-01 | Daftar kecamatan | Menampilkan seluruh kecamatan dengan kode, nama, dan jumlah kelurahan. Urut berdasarkan nama. |
| FR-01-02 | Tambah kecamatan | Form berisi kode_kecamatan (maks. 10 karakter) dan nama_kecamatan (maks. 100 karakter). |
| FR-01-03 | Ubah kecamatan | Mengubah kode dan nama kecamatan yang sudah ada. |
| FR-01-04 | Hapus kecamatan | Hanya dapat dihapus jika tidak memiliki kelurahan. Jika masih ada, sistem menolak dan menampilkan pesan. |
| FR-01-05 | Validasi | Kode dan nama wajib diisi. Kode kecamatan harus unik. |

### 6.2 PBI-2: CRUD Kelurahan (Desa)

Sumber: SKF-05, tabel kelurahan (ERD 4.3), kardinalitas 1:N kecamatan ke kelurahan.

| ID | Kebutuhan | Keterangan |
|---|---|---|
| FR-02-01 | Daftar kelurahan | Menampilkan kelurahan dengan kode, nama, dan nama kecamatan induk. Dapat difilter per kecamatan. |
| FR-02-02 | Tambah kelurahan | Form berisi kecamatan_id (wajib dipilih dari daftar kecamatan), kode_kelurahan (maks. 10 karakter), dan nama_kelurahan (maks. 100 karakter). |
| FR-02-03 | Ubah kelurahan | Mengubah kode, nama, dan kecamatan induk. |
| FR-02-04 | Hapus kelurahan | Penghapusan ditolak jika kelurahan sudah dipakai oleh laporan kerusakan. Pesan kesalahan ditampilkan. |
| FR-02-05 | Validasi | Kecamatan induk, kode, dan nama wajib diisi. Kode kelurahan harus unik dalam sistem. |

### 6.3 PBI-3: Dropdown Hierarkis

Sumber: SKF-02 dan UC-02 (Pilih Section Dropdown), serta item "Dropdown hierarkis" di Sprint 1.

| ID | Kebutuhan | Keterangan |
|---|---|---|
| FR-03-01 | Dropdown kecamatan | Menampilkan 30 kecamatan dari tabel kecamatan, diurutkan berdasarkan nama. |
| FR-03-02 | Dropdown kelurahan bergantung | Dropdown kelurahan menampilkan hanya kelurahan dari kecamatan yang dipilih. Dropdown kelurahan nonaktif sampai kecamatan dipilih. |
| FR-03-03 | Reset pilihan | Jika kecamatan diubah, pilihan kelurahan dikosongkan. |
| FR-03-04 | Penggunaan ulang | Komponen dropdown dapat dipakai pada formulir pelaporan (SKF-02) dan filter peta (SKF-04). |
| FR-03-05 | Respons | Daftar kelurahan muncul tanpa memuat ulang halaman. |

## 7. Alur Pengguna

**Admin mengelola kecamatan (PBI-1):**
1. Admin membuka menu Master Data, lalu Kecamatan.
2. Sistem menampilkan daftar kecamatan.
3. Admin memilih tambah, ubah, atau hapus.
4. Sistem memvalidasi input dan menyimpan perubahan.
5. Jika hapus ditolak karena masih ada kelurahan, sistem menampilkan pesan dan data tidak berubah.

**Admin mengelola kelurahan (PBI-2):** alur sama, dimulai dari menu Kelurahan dan memilih kecamatan induk.

**Warga memilih wilayah (PBI-3):**
1. Warga membuka formulir pelaporan.
2. Warga memilih kecamatan. Dropdown kelurahan aktif dan berisi kelurahan dari kecamatan tersebut.
3. Warga memilih kelurahan.
4. Jika warga mengganti kecamatan, pilihan kelurahan dikosongkan.

## 8. Kebutuhan Non-Fungsional

| ID | Kategori | Kebutuhan |
|---|---|---|
| NFR-01 | Kinerja | Halaman daftar master data dimuat maksimal 3 detik (NFR-01 proposal). Daftar kelurahan pada dropdown muncul maksimal 1 detik setelah kecamatan dipilih. |
| NFR-02 | Keamanan | Halaman CRUD hanya dapat diakses peran admin. Setiap permintaan ubah dan hapus dilindungi token CSRF. Input diproses dengan sanitasi untuk mencegah SQL injection. |
| NFR-03 | Kegunaan | Dropdown dan form dapat dipakai di ponsel dan desktop. |
| NFR-04 | Pengujian | Alur CRUD dan dropdown lolos pengujian E2E Laravel Dusk dengan tingkat kelulusan 100%. |

## 9. Model Data

| Tabel | Kolom | Tipe | Catatan |
|---|---|---|---|
| kecamatan | id | bigint (PK) | |
| | kode_kecamatan | varchar(10) | Unik |
| | nama_kecamatan | varchar(100) | |
| | created_at, updated_at | timestamp | |
| kelurahan | id | bigint (PK) | |
| | kecamatan_id | bigint (FK) | Relasi ke kecamatan, 1:N |
| | kode_kelurahan | varchar(10) | Unik |
| | nama_kelurahan | varchar(100) | |
| | created_at, updated_at | timestamp | |

**Data awal:** 30 kecamatan dan 151 kelurahan dimasukkan melalui seeder. Penghapusan kecamatan dibatasi oleh relasi, sehingga data awal tidak bisa dirusak tanpa menghapus kelurahan lebih dulu.

## 10. Asumsi dan Dependensi

**Asumsi:**
- Daftar 30 kecamatan dan 151 kelurahan sudah tersedia dari sumber resmi Pemkot Bandung.
- Kode kecamatan dan kelurahan disediakan oleh DSDABM dan tidak berubah sering.
- Admin DSDABM mengelola data wilayah secara manual, dan belum ada impor massal di versi ini.

**Dependensi:**
- Tabel users dan RBAC (PBI-4 sampai PBI-6) untuk membatasi akses admin. Sebelum RBAC siap, halaman CRUD dapat diuji dengan akun admin sementara.
- Migrasi database dan seeder MySQL 8.0.

## 11. Risiko

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Data wilayah awal tidak lengkap atau salah ketik | Laporan warga tidak bisa dipetakan dengan benar | Verifikasi data seeder dengan DSDABM sebelum rilis |
| Penghapusan tidak sengaja pada data yang sudah dipakai laporan | Riwayat laporan kehilangan wilayah | Penghapusan ditolak jika ada relasi (FR-02-04) |
| Dropdown lambat karena data kelurahan besar | Warga meninggalkan formulir | Batasi jumlah kelurahan yang dimuat sesuai kecamatan terpilih, bukan seluruh 151 kelurahan sekaligus |

## 12. Kriteria Penerimaan

- [ ] Admin dapat menambah, mengubah, dan menghapus kecamatan sesuai FR-01-01 sampai FR-01-05.
- [ ] Admin dapat menambah, mengubah, dan menghapus kelurahan sesuai FR-02-01 sampai FR-02-05.
- [ ] Penghapusan kecamatan dan kelurahan yang masih terpakai ditolak dengan pesan yang jelas.
- [ ] Dropdown kelurahan hanya berisi kelurahan dari kecamatan yang dipilih (FR-03-02).
- [ ] Seeder menghasilkan 30 kecamatan dan 151 kelurahan.
- [ ] Pengguna non-admin tidak dapat mengakses halaman CRUD.
- [ ] Pengujian E2E Laravel Dusk untuk seluruh alur di atas lulus 100%.

## 13. Pertanyaan Terbuka

| No | Pertanyaan | Pemutus |
|---|---|---|
| 1 | Apakah kode kecamatan dan kelurahan mengikuti kode resmi Kemendagri? | DSDABM |
| 2 | Apakah penghapusan kelurahan yang sudah dipakai laporan perlu opsi nonaktifkan, bukan hapus? | Product owner |
| 3 | Apakah admin perlu mengimpor data wilayah dari Excel di versi ini atau cukup di Sprint berikutnya? | Product owner |
| 4 | Apakah PBI-3 ikut mengatur tampilan dropdown di filter peta, atau hanya di formulir pelaporan? | Tim PPL |
