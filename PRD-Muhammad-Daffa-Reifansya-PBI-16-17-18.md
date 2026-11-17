# Product Requirements Document (PRD)
# INFRA-DAYA — PBI 16, 17, dan 18

| Informasi | Keterangan |
| --- | --- |
| Penanggung jawab | Muhammad Daffa Reifansya |
| Sprint | Sprint 1 |
| Versi | 2.0 — dokumen gabungan |
| Tanggal penyusunan | 6 November 2026 |
| Sumber kebutuhan | Deskripsi Jira yang telah diketahui dari pembacaan sebelumnya |
| Status dokumen | Spesifikasi kebutuhan untuk implementasi dan penerimaan |

## 1. Ringkasan produk

Ketiga PBI mendukung proses penugasan penanganan laporan kerusakan pada INFRA-DAYA. Admin DSDABM mengelola akun petugas lapangan, menerbitkan disposisi untuk laporan yang telah disetujui, lalu menyiapkan surat tugas dari disposisi tersebut.

Dokumen ini menggabungkan seluruh kebutuhan milik Muhammad Daffa Reifansya dalam satu PRD. Kriteria penerimaan merupakan ringkasan deskripsi Jira yang telah dibaca sebelumnya, bukan salinan verbatim dan bukan klaim bahwa seluruh pengujian telah lulus. Tidak dilakukan pembacaan ulang status Jira untuk penyusunan versi gabungan ini.

### 1.1 Pemetaan PBI dan Jira

| PBI | Nama fitur | Issue Jira |
| --- | --- | --- |
| 16 | Buat Lembar Disposisi | [IFD-19](https://tubes-ppl.atlassian.net/browse/IFD-19) |
| 17 | Kelola Petugas Lapangan | [IFD-18](https://tubes-ppl.atlassian.net/browse/IFD-18) |
| 18 | Pencetakan Surat Tugas | [IFD-17](https://tubes-ppl.atlassian.net/browse/IFD-17) |

Nomor issue Jira berbeda dari nomor PBI. Penomoran PBI tetap 16, 17, dan 18 sesuai penugasan.

### 1.2 Tujuan

- Menetapkan petugas penanggung jawab dan instruksi untuk setiap laporan yang layak didisposisikan.
- Menjaga identitas, status akses, dan kelayakan penugasan petugas tanpa menghilangkan riwayat.
- Menyediakan surat tugas yang lengkap, konsisten, dapat diunduh sebagai PDF, dan dapat dicetak.
- Menjaga integritas data saat terjadi pengulangan permintaan, perubahan status akun, atau kegagalan proses.

## 2. Aktor, prasyarat, dan batas ruang lingkup

| Aktor | Peran dalam kebutuhan |
| --- | --- |
| Admin DSDABM | Pengguna utama ketiga fitur; harus terautentikasi dan berwenang |
| Petugas lapangan | Penerima penugasan; status aktif menentukan akses dan kelayakan menerima tugas baru |
| Warga, pimpinan, dan pengguna lain | Tidak memiliki akses ke fungsi admin dalam ketiga PBI ini |
| Tamu | Harus autentikasi sebelum mengakses fitur |

Prasyarat: aplikasi telah memiliki autentikasi, role, data laporan, hasil verifikasi laporan, serta referensi kategori dan wilayah. Laporan yang sudah disetujui untuk proses ini ditandai dengan status `diproses`.

Ruang lingkup gabungan mencakup pengelolaan petugas, penerbitan disposisi, dan surat tugas. Verifikasi laporan, pelaporan progres lapangan, pengalihan tugas, reset password, penghapusan permanen petugas, tanda tangan elektronik, serta pengelolaan printer berada di luar ruang lingkup.

## 3. Alur bisnis terintegrasi

1. Admin memastikan petugas yang akan ditugaskan telah terdaftar dan aktif melalui PBI 17.
2. Admin memilih laporan berstatus `diproses` yang belum memiliki disposisi melalui PBI 16.
3. Admin memilih petugas aktif dan memasukkan instruksi. Sistem menetapkan identitas admin, waktu, serta status awal penugasan.
4. Disposisi disimpan tepat satu kali untuk laporan tersebut dan tersedia pada halaman detail.
5. Admin membuka pratinjau, mengunduh PDF, atau mencetak surat berdasarkan disposisi melalui PBI 18.
6. Jika petugas akan dinonaktifkan, sistem memastikan tidak ada tugas yang belum selesai. Riwayat dan akses admin ke surat historis tetap dipertahankan.

Urutan alur operasional ini tidak mengubah nomor PBI. Kebutuhan pengelolaan petugas dan disposisi saling terhubung; surat tugas bergantung pada disposisi yang telah tersimpan.

## 4. PBI 16 — Buat Lembar Disposisi

### 4.1 Masalah, tujuan, dan user story

Admin DSDABM membutuhkan penanggung jawab dan instruksi yang jelas untuk laporan kerusakan yang sudah disetujui. Sebagai admin, saya ingin membuat disposisi digital dengan memilih petugas lapangan dan mengisi instruksi agar laporan dapat ditindaklanjuti oleh petugas yang tepat.

Hasil yang diharapkan: setiap laporan yang layak memiliki paling banyak satu disposisi, penanggung jawab aktif, instruksi tersimpan, serta identitas admin dan waktu penerbitan yang dapat ditelusuri.

### 4.2 Ruang lingkup fitur

Aktor utama adalah admin DSDABM yang terautentikasi dan aktif. Warga, petugas, pimpinan, dan tamu tidak memiliki hak menjalankan fitur admin ini.

Termasuk daftar laporan siap disposisi, pencarian, formulir, penyimpanan, detail, validasi, dan pencegahan duplikasi. Laporan harus berstatus `diproses` dan belum mempunyai disposisi.

Di luar lingkup: verifikasi laporan, perubahan isi/prioritas/status laporan, pembaruan progres oleh petugas, pengalihan atau penghapusan disposisi, tenggat waktu, serta pencetakan surat (PBI 18). Ketersediaan akun aktif menggunakan data petugas yang dikelola PBI 17.

### 4.3 Alur pengguna

1. Admin membuka daftar laporan siap disposisi dan dapat mencari nomor tiket atau judul.
2. Admin memilih **Buat Disposisi**. Sistem menampilkan rincian laporan secara baca-saja.
3. Admin memilih petugas aktif dan mengisi instruksi. Jika tidak tersedia petugas aktif, sistem menjelaskan kebutuhan penyediaan petugas dan mencegah penyimpanan.
4. Saat disimpan, server memeriksa ulang petugas, kelayakan laporan, dan keberadaan disposisi.
5. Sistem menyimpan secara atomik dan mengarahkan admin ke detail disposisi. Laporan tidak lagi muncul pada daftar siap disposisi.
6. Jika admin membatalkan, sistem kembali ke daftar tanpa menyimpan. Jika gagal, tampilkan pesan dan jangan menampilkan keberhasilan palsu.

### 4.4 Data dan aturan bisnis

| Data | Aturan kebutuhan |
| --- | --- |
| Laporan | Wajib ada, status `diproses`, dan belum memiliki disposisi |
| Petugas | Wajib ada, ber-role petugas, dan aktif saat penyimpanan |
| Instruksi | Wajib mengandung sekurangnya satu karakter nonspasi |
| Admin pembuat | Diambil dari sesi pengguna, tidak dipercaya dari input klien |
| Tanggal disposisi | Dibuat server saat penyimpanan; tipe DATETIME |
| Status awal | Ditetapkan server menjadi `ditugaskan` |

Data disposisi mencakup ID, referensi laporan, admin, petugas, tanggal disposisi, instruksi, status penugasan, dan timestamps. Referensi laporan harus unik untuk menjamin satu disposisi per laporan.

Validasi kelayakan harus diulang saat penyimpanan, termasuk ketika form telah lama terbuka. Seluruh proses simpan bersifat atomik: kegagalan tidak boleh meninggalkan data parsial. Penerbitan disposisi tidak mengubah isi, prioritas, status, atau pemilik laporan. Tidak terdapat kebutuhan input tenggat waktu dalam deskripsi ini.

### 4.5 Kriteria penerimaan

| ID | Hasil yang harus dipenuhi |
| --- | --- |
| AC16-01 | Admin yang berwenang dapat mengakses daftar, form, detail, dan penyimpanan; role lain ditolak. |
| AC16-02 | Tamu wajib autentikasi sebelum mengakses endpoint disposisi. |
| AC16-03 | Daftar hanya berisi laporan berstatus diproses yang belum memiliki disposisi. |
| AC16-04 | Daftar memuat tiket, judul, kategori, kelurahan/kecamatan, prioritas, status, dan aksi Buat Disposisi. |
| AC16-05 | Pencarian mendukung nomor tiket atau judul; menghapus pencarian mengembalikan daftar awal. |
| AC16-06 | Daftar kosong menampilkan “Belum ada laporan yang dapat didisposisikan”; pencarian kosong menampilkan “Laporan tidak ditemukan”. |
| AC16-07 | Form menampilkan detail, foto, lokasi, dan prioritas laporan secara baca-saja. |
| AC16-08 | Petugas wajib dipilih dari petugas aktif; ID tidak ada, role salah, atau akun nonaktif ditolak server. |
| AC16-09 | Instruksi wajib berisi karakter nonspasi; instruksi satu karakter diterima. |
| AC16-10 | Input bisnis yang dipakai hanya laporan_id, petugas_id, dan catatan_instruksi. |
| AC16-11 | Admin, waktu, dan status awal ditentukan server; manipulasi nilai klien tidak menggantikannya. |
| AC16-12 | Satu laporan hanya mempunyai satu disposisi, termasuk ketika permintaan diulang atau datang bersamaan. |
| AC16-13 | Kelayakan laporan diperiksa kembali ketika menyimpan sehingga form lama tidak melewati aturan. |
| AC16-14 | Sukses mengarah ke detail; data tetap setelah reload dan laporan keluar dari daftar siap disposisi. |
| AC16-15 | Pembuatan disposisi tidak mengubah isi, prioritas, status, atau kepemilikan laporan. |
| AC16-16 | Batal kembali ke daftar tanpa menyimpan. |
| AC16-17 | Kegagalan penyimpanan membatalkan seluruh transaksi dan menampilkan pesan kegagalan. |
| AC16-18 | Detail memuat laporan, petugas, admin, tanggal, instruksi, dan status penugasan. |

## 5. PBI 17 — Kelola Petugas Lapangan

### 5.1 Masalah, tujuan, dan user story

Admin DSDABM membutuhkan data identitas dan status petugas yang sesuai kondisi operasional. Sebagai admin, saya ingin menambah, melihat, memperbarui identitas, dan mengaktifkan atau menonaktifkan petugas agar penugasan hanya diberikan kepada petugas aktif tanpa menghilangkan riwayatnya.

Hasil yang diharapkan: akun petugas dapat dikelola secara khusus, akun nonaktif tidak mendapat akses atau tugas baru, dan seluruh referensi penugasan tetap utuh.

### 5.2 Ruang lingkup fitur

Aktor utama adalah admin DSDABM yang terautentikasi dan aktif. Warga, petugas, pimpinan, dan tamu tidak memiliki hak menjalankan fitur admin ini.

Termasuk daftar, pencarian, filter, tambah akun, detail beserta riwayat, edit identitas, aktivasi, nonaktivasi, pembatasan autentikasi, dan integrasi pemilihan petugas PBI 16.

Mengikuti deskripsi Jira, operasi data dibatasi pada Read/Create/Update. Tidak ada hapus permanen. Reset password, pengelolaan user dengan role lain, pengalihan tugas, serta penyelesaian tugas bukan bagian PBI ini. Kata CRUD pada diskusi awal tidak digunakan untuk menambahkan fitur hapus.

### 5.3 Alur pengguna

1. Admin membuka daftar petugas aktif/nonaktif, mencari nama/email, atau memfilter status.
2. Untuk menambah, admin mengisi nama, email, telepon, password, dan konfirmasinya. Server membuat akun dengan role petugas dan status aktif.
3. Admin dapat membuka detail/riwayat atau mengedit nama, email, dan telepon. Edit identitas tidak mengganti password, role, atau status.
4. Aktivasi/nonaktivasi menggunakan aksi terpisah dengan konfirmasi.
5. Sebelum nonaktivasi, server memeriksa tugas. Jika ada tugas selain selesai, proses ditolak dan informasi tugas penghambat ditampilkan.
6. Jika tidak ada tugas belum selesai, status menjadi nonaktif; login dan permintaan sesi lama berikutnya ditolak, serta penugasan baru diblokir.
7. Aktivasi kembali menggunakan ID akun yang sama dan mengembalikan kelayakan akses/penugasan.

### 5.4 Data dan aturan bisnis

| Data/aksi | Aturan kebutuhan |
| --- | --- |
| Nama | Wajib dan bukan hanya spasi |
| Email | Wajib, format valid, dan unik terhadap seluruh akun; saat edit, email sendiri tetap diperbolehkan |
| Telepon | Wajib berupa teks agar nol awal dan awalan `+` dipertahankan; format nomor harus valid |
| Password dan konfirmasi | Wajib saat pembuatan, sesuai kebijakan autentikasi, dan saling cocok; password disimpan sebagai hash |
| Role akun baru | Selalu petugas, ditetapkan server |
| Status akun baru | Selalu aktif, ditetapkan server |
| Edit identitas | Hanya nama, email, dan telepon |
| Aktivasi/nonaktivasi | Aksi terpisah dengan konfirmasi eksplisit |

Menggunakan identitas akun pada `users` beserta referensi penugasannya. Edit identitas tidak mengubah password, role, status aktif, atau riwayat. Target operasi harus merupakan petugas yang benar-benar ada.

Nonaktivasi diperbolehkan hanya jika tidak ada penugasan dengan status selain `selesai`. Jika terhalang tugas, tampilkan informasi tiket/judul tugas dan pertahankan data semula. Permintaan aktivasi/nonaktivasi berulang pada status yang sama tidak boleh merusak data.

Petugas nonaktif tidak boleh login atau menerima disposisi baru. Sesi yang sudah ada harus ditolak pada permintaan terlindungi berikutnya. Aktivasi kembali mempertahankan ID akun dan seluruh riwayat.

PBI ini menggunakan operasi Read/Create/Update sesuai deskripsi Jira. Tidak ada hapus permanen; kebutuhan CRUD yang pernah disebut sebelumnya dipertegas menjadi tambah, lihat, edit, dan perubahan status akun.

### 5.5 Kriteria penerimaan

| ID | Hasil yang harus dipenuhi |
| --- | --- |
| AC17-01 | Hanya admin dapat mengakses seluruh halaman dan permintaan pengelolaan petugas. |
| AC17-02 | Tamu wajib autentikasi. |
| AC17-03 | Daftar hanya memuat role petugas, aktif maupun nonaktif, dengan nama, email, telepon, dan status. |
| AC17-04 | Pencarian nama/email dan filter status dapat dipakai terpisah, bersamaan, dan direset. |
| AC17-05 | Daftar kosong menampilkan “Belum ada petugas lapangan”; hasil pencarian kosong “Petugas tidak ditemukan”. |
| AC17-06 | Akun baru selalu ber-role petugas, aktif, dan menyimpan password sebagai hash. |
| AC17-07 | Seluruh field pembuatan wajib diisi dengan nilai yang valid; nilai kosong/spasi ditolak. |
| AC17-08 | Email valid dan unik lintas role; edit boleh mempertahankan email akun sendiri. |
| AC17-09 | Telepon divalidasi sebagai teks dan mempertahankan nol awal atau awalan +. |
| AC17-10 | Password memenuhi kebijakan autentikasi dan cocok dengan konfirmasi. |
| AC17-11 | Edit hanya mengubah nama, email, telepon; password, role, status, serta relasi tugas tetap. |
| AC17-12 | ID tidak ditemukan atau target bukan role petugas ditolak. |
| AC17-13 | Petugas tanpa tugas belum selesai dapat dinonaktifkan setelah konfirmasi. |
| AC17-14 | Petugas dengan tugas belum selesai tidak dapat dinonaktifkan; tiket/judul penghambat ditampilkan tanpa mengubah data. |
| AC17-15 | Petugas nonaktif gagal login; sesi lama ditolak pada permintaan terlindungi berikutnya. |
| AC17-16 | Petugas nonaktif tidak tersedia untuk disposisi baru dan ditolak pada permintaan langsung. |
| AC17-17 | Aktivasi kembali mempertahankan ID dan memulihkan akses serta kelayakan penugasan. |
| AC17-18 | Identitas akun dan riwayat penugasan dipertahankan; tidak tersedia hapus permanen. |
| AC17-19 | Pembatalan tidak mengubah data; kegagalan simpan melakukan rollback dan menampilkan pesan. |
| AC17-20 | Perubahan berhasil tetap tersimpan setelah reload. |
| AC17-21 | Password maupun hash tidak ditampilkan pada daftar, detail, atau form edit. |
| AC17-22 | Persaingan nonaktivasi dan disposisi menjaga konsistensi: nonaktivasi lebih dulu membuat disposisi gagal; disposisi lebih dulu membuat nonaktivasi gagal. |

## 6. PBI 18 — Pencetakan Surat Tugas

### 6.1 Masalah, tujuan, dan user story

Admin DSDABM membutuhkan surat tugas dari disposisi tersimpan sebagai dokumen penugasan lapangan. Sebagai admin, saya ingin melihat pratinjau, mengunduh PDF, dan mencetak surat agar petugas membawa informasi identitas, lokasi, serta instruksi yang jelas.

Hasil yang diharapkan: surat memiliki nomor yang stabil, memakai data disposisi yang benar, dapat dibaca dalam format A4, serta tidak mengubah penugasan ketika ditampilkan atau diunduh.

### 6.2 Ruang lingkup fitur

Aktor utama adalah admin DSDABM yang terautentikasi dan aktif. Warga, petugas, pimpinan, dan tamu tidak memiliki hak menjalankan fitur admin ini.

Termasuk daftar disposisi untuk surat, pencarian, validasi kelengkapan, pratinjau HTML, unduh PDF, dialog cetak browser, penanganan kegagalan, dan surat historis untuk petugas nonaktif.

Di luar lingkup: membuat disposisi baru, mengubah status tugas, tanda tangan elektronik, penyimpanan arsip PDF permanen, pengiriman surat otomatis, pengaturan printer, serta pembuktian keberhasilan cetak fisik. Sumber utama adalah disposisi PBI 16 dan relasi laporan/petugas.

### 6.3 Alur pengguna

1. Admin membuka daftar surat dari disposisi yang sudah tersimpan dan mencari nomor tiket atau nama petugas.
2. Admin memilih pratinjau disposisi tertentu. Server mengambil relasi yang sesuai dan memeriksa kelengkapan.
3. Jika lengkap, tampilkan surat dengan nomor tetap dan tanggal disposisi; waktu pembuatan dokumen ditampilkan terpisah.
4. Admin memilih unduh untuk memperoleh PDF A4 atau Cetak untuk membuka dialog cetak browser.
5. Pembatalan cetak tidak mengubah data. Aplikasi tidak menyatakan printer telah berhasil mencetak.
6. Data tidak lengkap menampilkan komponen yang kurang. Kegagalan generator menampilkan pesan dan kesempatan mencoba kembali, tanpa mengirim PDF rusak sebagai sukses.

### 6.4 Data dan aturan bisnis

| Elemen surat | Sumber/aturan kebutuhan |
| --- | --- |
| Nomor surat | Unik per disposisi dan stabil pada pratinjau, unduh, dan cetak berulang |
| Tanggal penugasan | Bersumber dari tanggal disposisi |
| Waktu pembuatan dokumen | Dipisahkan dari tanggal penugasan |
| Identitas petugas | Petugas pada disposisi yang dipilih |
| Identitas laporan | Nomor tiket, judul, kategori, dan prioritas |
| Lokasi | Kelurahan, kecamatan, serta koordinat |
| Instruksi | Instruksi pada disposisi |

Sistem harus memeriksa keberadaan disposisi dan kelengkapan data sebelum membuat surat. Jika data wajib hilang, proses ditolak dengan keterangan komponen yang kurang. Data dari disposisi atau laporan lain tidak boleh tertukar.

Pratinjau dan PDF harus konsisten secara isi bisnis. PDF harus valid, bernama sesuai identitas surat, bertipe `application/pdf`, dan berukuran A4 portrait. Instruksi panjang harus berlanjut ke halaman berikutnya tanpa terpotong atau menimpa blok tanda tangan.

Cetak membuka dialog browser. Navigasi dan tombol aksi tidak ikut tercetak. Pembatalan tidak mengubah data; aplikasi tidak mengklaim printer telah berhasil mencetak secara fisik.

Pratinjau, unduh, dan cetak bersifat baca-saja: tidak membuat disposisi, mengubah status, atau memperbarui timestamps penugasan. Surat historis bagi petugas nonaktif tetap dapat diakses admin tanpa mengaktifkan akun.

### 6.5 Kriteria penerimaan

| ID | Hasil yang harus dipenuhi |
| --- | --- |
| AC18-01 | Hanya admin dapat mengakses daftar, pratinjau, cetak, dan PDF; role lain ditolak. |
| AC18-02 | Tamu wajib autentikasi. |
| AC18-03 | Daftar memuat tiket, petugas, tanggal, status, serta aksi pratinjau. |
| AC18-04 | Pencarian mendukung tiket atau nama petugas; reset mengembalikan daftar awal. |
| AC18-05 | Daftar kosong menampilkan “Belum ada disposisi yang dapat ditampilkan”; pencarian kosong “Disposisi tidak ditemukan”. |
| AC18-06 | Surat hanya dibuat jika seluruh data wajib tersedia. |
| AC18-07 | Isi memakai disposisi dan relasi yang dipilih, tidak tertukar dengan data lain. |
| AC18-08 | Surat memuat nomor, tanggal, petugas, tiket, judul, kategori, prioritas, wilayah, koordinat, dan instruksi. |
| AC18-09 | Nomor surat unik per disposisi dan tetap pada pengulangan pratinjau, unduh, atau cetak. |
| AC18-10 | Tanggal penugasan berasal dari tanggal_disposisi; waktu pembuatan dokumen dipisahkan. |
| AC18-11 | Unduhan menghasilkan PDF valid dengan Content-Type dan nama berkas yang sesuai. |
| AC18-12 | PDF memakai ukuran A4 portrait dengan tata letak yang terbaca. |
| AC18-13 | Instruksi panjang tidak terpotong atau bertumpuk; blok tanda tangan tetap terbaca. |
| AC18-14 | Pratinjau dan PDF menggunakan data bisnis serta template yang sama. |
| AC18-15 | Cetak membuka dialog browser dan menyembunyikan navigasi/aksi pada hasil cetak. |
| AC18-16 | Pembatalan cetak tidak menimbulkan perubahan data. |
| AC18-17 | Pratinjau, PDF, dan cetak tidak membuat disposisi atau mengubah status maupun updated_at. |
| AC18-18 | Disposisi hilang atau data wajib kosong ditolak dengan respons yang sesuai; kekurangan data dijelaskan. |
| AC18-19 | Kegagalan generator memberi pesan dan opsi mencoba kembali tanpa respons sukses PDF rusak. |
| AC18-20 | Password, hash, dan data yang tidak dibutuhkan tidak ditampilkan. |
| AC18-21 | Teks HTML/script dalam data ditampilkan secara aman melalui escaping. |
| AC18-22 | Surat historis petugas nonaktif tetap dapat dibuka tanpa mengaktifkan kembali akun. |
| AC18-23 | Aplikasi tidak mengklaim keberhasilan cetak fisik atau status printer. |

## 7. Aturan lintas PBI dan kebutuhan kualitas

### 7.1 Integritas data dan konkurensi

- Satu laporan hanya memiliki satu disposisi, termasuk saat dua admin mengirim permintaan bersamaan.
- Pemilihan petugas di form tidak menggantikan pemeriksaan status aktif di server saat menyimpan.
- Jika nonaktivasi berhasil lebih dahulu, penugasan baru kepada petugas tersebut harus ditolak.
- Jika penugasan baru berhasil lebih dahulu, nonaktivasi harus ditolak selama tugas belum selesai.
- Semua perubahan yang gagal harus dibatalkan secara utuh dan tidak menampilkan pesan berhasil.
- Identitas dan riwayat petugas harus tetap tersedia untuk penelusuran disposisi dan surat tugas.

### 7.2 Keamanan dan privasi

- Autentikasi dan otorisasi berlaku pada seluruh halaman serta permintaan langsung.
- Field yang ditetapkan server tidak dapat diganti melalui manipulasi input.
- Password dan hash tidak boleh muncul dalam daftar, detail, form edit, atau surat.
- Teks dari pengguna ditampilkan secara aman; HTML/script tidak boleh dieksekusi sebagai bagian surat.
- Pesan kesalahan harus membantu pengguna memahami kegagalan tanpa mengungkap informasi sensitif.

### 7.3 Kegunaan dan konsistensi

- Daftar menyediakan pencarian serta pesan yang membedakan data kosong dari hasil pencarian kosong.
- Validasi menjelaskan field atau kondisi yang harus diperbaiki.
- Aksi batal tidak menyimpan perubahan, dan aksi status petugas meminta konfirmasi.
- Data yang berhasil disimpan tetap tersedia setelah halaman dimuat ulang.
- Target numerik performa, SLA, dan beban pengguna tidak ditetapkan dalam PRD ini karena belum tersedia pada deskripsi sumber yang diketahui.

## 8. Rencana validasi dan penerimaan

Bagian ini merupakan rencana pemeriksaan kebutuhan, bukan laporan hasil pengujian.

| Area | Skenario utama | Hasil yang diharapkan |
| --- | --- | --- |
| Akses | Tamu dan setiap role mengakses halaman maupun request langsung | Hanya admin berwenang dapat menjalankan fitur |
| Disposisi normal | Pilih laporan layak, petugas aktif, dan instruksi valid | Tepat satu disposisi tersimpan dan laporan keluar dari daftar siap disposisi |
| Disposisi tidak valid | Instruksi kosong, petugas nonaktif, laporan tidak layak, atau form lama | Ditolak tanpa perubahan parsial |
| Duplikasi bersamaan | Dua proses mengirim disposisi untuk laporan sama | Maksimal satu disposisi tersimpan |
| Akun petugas | Tambah, lihat, edit, filter, aktifkan/nonaktifkan | Identitas valid, batas field dipatuhi, riwayat tetap |
| Tugas belum selesai | Nonaktifkan petugas dengan tugas belum selesai | Ditolak dengan informasi tugas penghambat |
| Sesi nonaktif | Login baru dan request sesi lama setelah nonaktivasi | Akses ditolak sesuai aturan |
| Konflik antarfitur | Penugasan dan nonaktivasi dijalankan bersamaan | Tidak ada hasil yang melanggar status aktif dan tugas berjalan |
| Surat normal | Pratinjau, unduh berulang, dan periksa PDF | Nomor stabil, relasi benar, data lengkap, PDF valid |
| Surat panjang | Instruksi melampaui satu halaman | Seluruh teks terbaca tanpa tumpang tindih |
| Surat tidak lengkap | Relasi/data wajib hilang atau generator gagal | Pesan kegagalan jelas, tidak ada PDF rusak yang dinyatakan sukses |
| Cetak | Buka lalu batalkan dialog cetak | Aksi/navigasi tidak ikut cetak dan data tidak berubah |
| Riwayat | Buka surat petugas yang sudah nonaktif | Surat dapat diakses admin tanpa aktivasi akun |
| Keamanan tampilan | Data berisi HTML/script atau pemeriksaan kredensial | Script tidak dieksekusi dan kredensial tidak tampil |

Pengujian konkurensi harus memakai permintaan yang benar-benar berjalan bersamaan pada database yang digunakan tim. Pengujian berurutan saja tidak membuktikan seluruh perilaku konkurensi. Alur dialog cetak perlu diuji di browser; keberhasilan membuka dialog tidak membuktikan hasil cetak fisik.

### 8.1 Definition of Done yang diusulkan

- Seluruh AC16-01–AC16-18, AC17-01–AC17-22, dan AC18-01–AC18-23 dipenuhi dan mempunyai bukti penerimaan.
- Alur berhasil, validasi, penolakan akses, pembatalan, serta kegagalan transaksi teruji.
- Konsistensi disposisi dan nonaktivasi teruji pada permintaan bersamaan.
- Pratinjau, PDF panjang, serta dialog cetak diverifikasi.
- Riwayat, kerahasiaan kredensial, dan sifat baca-saja surat terjaga.
- Hasil ditinjau penanggung jawab dan reviewer tim sebelum dinyatakan diterima.

## 9. Keputusan ruang lingkup dan sumber

PRD ini mengacu pada deskripsi Jira yang telah diketahui sebelumnya. Parameter teknis khusus kode, seperti jumlah item per halaman, batas panjang instruksi, pola nomor surat, nama class, dan rute implementasi, tidak dijadikan kebutuhan Jira tambahan dalam dokumen ini. Perubahan terhadap kebutuhan perlu disepakati dan dicatat kembali pada issue terkait.

| Sumber | Cakupan |
| --- | --- |
| [IFD-19](https://tubes-ppl.atlassian.net/browse/IFD-19) | PBI 16 — Buat Lembar Disposisi |
| [IFD-18](https://tubes-ppl.atlassian.net/browse/IFD-18) | PBI 17 — Kelola Petugas Lapangan |
| [IFD-17](https://tubes-ppl.atlassian.net/browse/IFD-17) | PBI 18 — Pencetakan Surat Tugas |

Dokumen gabungan ini menjadi versi PRD yang digunakan untuk permintaan terbaru; file PRD terpisah sebelumnya tetap tersimpan sebagai arsip.
