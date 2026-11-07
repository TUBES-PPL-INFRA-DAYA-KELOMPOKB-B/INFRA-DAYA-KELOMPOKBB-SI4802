# PRD: INFRA-DAYA (Infrastruktur dan Daya Tanggap Kota Bandung)

| Atribut | Keterangan |
|---|---|
| Nama produk | INFRA-DAYA |
| Jenis produk | Aplikasi web pelaporan dan penanganan infrastruktur publik |
| Wilayah | Kota Bandung (30 Kecamatan, 151 Kelurahan) |
| Dinas terkait | Dinas Sumber Daya Air dan Bina Marga (DSDABM) Kota Bandung |
| Sumber | Proposal Pengembangan Produk PPL, Kelompok E, 5 Oktober 2026 |
| Versi dokumen | 0.1 (Draf) |
| Tim pengembang | Rakean Ahmad Zayyid Ardhi, Muhammad Daffa Fachrurozi, Maurellio Christopher Yonathan, I Nyoman Aditya Wahyu Nughraha, Wahyu Wijaya, Muhammad Daffa Reifansya, Baso Aditya Riski |
| Status | Draf, menunggu review |

---

## 1. Ringkasan

INFRA-DAYA adalah platform web terpusat tempat warga Kota Bandung melaporkan kerusakan infrastruktur publik (jalan, drainase, Penerangan Jalan Umum, jembatan/tanggul) tanpa harus berada di lokasi kejadian dan tanpa bergantung pada GPS real-time. Laporan diverifikasi dan didisposisikan secara digital oleh DSDABM, lalu ditindaklanjuti tim lapangan. Pimpinan memantau kinerja melalui dashboard SLA dan peta sebaran.

## 2. Latar Belakang dan Masalah

- **Pelaporan tidak terstruktur:** Aduan masuk lewat formulir fisik berjenjang, lisan, atau media sosial, dengan lokasi berupa deskripsi teks yang multitafsir.
- **Pelaporan berbasis GPS menyulitkan:** Aplikasi yang mewajibkan GPS real-time memaksa warga melapor di tempat kejadian, padahal warga biasanya baru sempat melapor saat sudah sampai rumah, kantor, atau kampus.
- **Verifikasi sulit:** Admin DSDABM kesulitan memastikan keabsahan laporan dan menentukan titik lokasi secara presisi.
- **Penanganan tanpa acuan waktu:** Tim lapangan bekerja tanpa panduan navigasi koordinat dan belum ada SLA yang terukur.
- **Tidak ada transparansi:** Warga tidak tahu apakah aduannya sedang diverifikasi, dijadwalkan, atau sudah selesai.
- **Konteks kebijakan:** Permasalahan ini berkaitan dengan SDGs 9, khususnya Target 9.1 tentang infrastruktur yang berkualitas, andal, dan tangguh.

## 3. Tujuan dan Non-Tujuan

**Tujuan:**
1. Warga dapat melaporkan kerusakan infrastruktur secara presisi dan fleksibel, tanpa bergantung pada posisi GPS real-time.
2. Tersedia geotagging dan peta interaktif berbasis section wilayah (kecamatan lalu kelurahan) untuk memudahkan pemetaan titik kerusakan.
3. Sistem mendukung kolaborasi empat peran: Warga, Admin DSDABM, Petugas Lapangan, dan Pimpinan/Wali Kota.
4. Warga dapat melacak riwayat status penanganan dan memberi rating serta ulasan.
5. Fitur utama lolos pengujian otomatis end-to-end menggunakan Laravel Dusk.

**Non-tujuan (versi ini):**
- Integrasi dengan sistem pemerintah lain di luar DSDABM, misalnya sistem anggaran atau kepegawaian.
- Aplikasi mobile native. Versi ini menggunakan web responsif.
- Cakupan di luar Kota Bandung atau kategori infrastruktur selain empat kategori yang disebutkan.
- Pembayaran atau pengadaan material perbaikan.

## 4. Ruang Lingkup Produk

**Kategori infrastruktur:** Jalan Raya/Trotoar, Saluran Drainase/Gorong-gorong, Penerangan Jalan Umum (PJU), Jembatan/Tanggul Air.

**Peran pengguna:**

| Peran | Deskripsi | Hak akses utama |
|---|---|---|
| Warga (Pelapor) | Masyarakat yang tinggal, bekerja, atau melintasi Kota Bandung | Registrasi dan login, membuat laporan, menggeser penanda di peta, memantau linimasa laporan sendiri, melihat peta publik, memberi rating dan ulasan |
| Admin DSDABM (Verifikator) | Staf operasional DSDABM | Kelola master data, verifikasi laporan (setujui/tolak), tetapkan prioritas, terbitkan disposisi, lihat dashboard, ekspor rekapitulasi |
| Petugas Lapangan (Eksekutor) | Tim teknis yang melakukan perbaikan | Lihat daftar tugas, buka rute dan koordinat, perbarui status pengerjaan, unggah foto hasil |
| Pimpinan / Wali Kota (Evaluator) | Pengambil kebijakan di Pemkot dan pimpinan dinas | Dashboard eksekutif SLA, analitik sebaran per kecamatan, rata-rata waktu tanggap, unduh ringkasan kinerja |

## 5. Alur Bisnis

**Alur yang diusulkan:**

1. **Pengajuan (Warga):** Warga memilih kecamatan dan kelurahan, menggeser penanda di peta ke titik kerusakan, mengisi judul dan deskripsi, mengunggah foto, lalu mengirim. Status awal: *Menunggu Verifikasi*.
2. **Verifikasi dan disposisi (Admin):** Admin memeriksa foto, kategori, dan koordinat. Laporan palsu atau tidak valid ditolak dengan alasan. Laporan valid disetujui, diberi prioritas, lalu didisposisikan ke Petugas Lapangan. Status: *Diproses*.
3. **Eksekusi (Petugas):** Petugas menerima tugas, membuka rute di peta, melakukan perbaikan, lalu mengunggah foto hasil. Status: *Selesai*.
4. **Konfirmasi (Warga):** Warga memantau linimasa, melihat dokumentasi hasil, dan memberi rating 1 sampai 5 beserta ulasan.
5. **Evaluasi (Pimpinan):** Pimpinan memantau dashboard SLA, tren, dan peta sebaran untuk pengambilan keputusan.

**Kondisi error utama:** Jika koordinat belum ditentukan atau foto melebihi 5 MB, sistem menampilkan notifikasi dan meminta pengguna melengkapi data sebelum laporan tersimpan.

## 6. User Story dan Kebutuhan Fungsional

Format: *Sebagai [peran], saya ingin [aksi], agar [manfaat].* Prioritas memakai MoSCoW.

| ID | Modul | User story | Prioritas |
|---|---|---|---|
| SKF-01 | Autentikasi dan akun | Sebagai warga, saya ingin mendaftar, masuk, dan keluar secara mandiri, agar saya bisa melapor dan memantau laporan saya. Akses dibatasi berdasarkan peran (RBAC). | Must |
| SKF-02 | Pelaporan berbasis section | Sebagai warga, saya ingin memilih kecamatan dan kelurahan secara berjenjang, lalu mengisi judul, deskripsi, kategori, dan foto, agar laporan tercatat lengkap. | Must |
| SKF-03 | Geotagging | Sebagai warga, saya ingin menggeser penanda di peta ke titik kerusakan, agar lokasi tercatat presisi tanpa harus berada di lokasi saat melapor. | Must |
| SKF-04 | Peta publik | Sebagai warga atau pimpinan, saya ingin melihat sebaran kerusakan di peta dan memfilternya berdasarkan kategori dan status, agar kondisi kota terlihat jelas. | Should |
| SKF-05 | Master data | Sebagai admin, saya ingin mengelola data 30 kecamatan, 151 kelurahan, kategori, status, dan bobot prioritas, agar data wilayah selalu akurat. | Must |
| SKF-06 | Verifikasi | Sebagai admin, saya ingin menyetujui atau menolak laporan dengan alasan, agar hanya laporan valid yang ditindaklanjuti. | Must |
| SKF-07 | Disposisi digital | Sebagai admin, saya ingin menetapkan prioritas dan menerbitkan surat tugas digital ke petugas, agar penugasan berjalan tanpa kertas. | Must |
| SKF-08 | Progres lapangan | Sebagai petugas, saya ingin menerima tugas, membuka rute koordinat, memperbarui status, dan mengunggah foto hasil, agar pekerjaan terdokumentasi. | Must |
| SKF-09 | Linimasa status | Sebagai warga, saya ingin melihat riwayat status laporan secara kronologis, agar saya tahu perkembangan aduan saya. | Must |
| SKF-10 | Rating dan ulasan | Sebagai warga, saya ingin memberi rating 1 sampai 5 dan ulasan setelah perbaikan selesai, agar kualitas layanan terukur. | Should |
| SKF-11 | Dashboard SLA | Sebagai pimpinan, saya ingin melihat capaian SLA, tren, dan ranking kecamatan, agar saya bisa mengevaluasi dinas. | Must |
| SKF-12 | Ekspor data | Sebagai admin atau pimpinan, saya ingin mengekspor rekapitulasi laporan (rentang tanggal, kategori, kecamatan) ke Excel dan PDF. | Should |

**Fitur tambahan dari rencana sprint:** notifikasi, kolom komentar, cari laporan dengan kode unik, upvote laporan, deteksi laporan duplikat otomatis, dan perhitungan skor prioritas. Fitur ini diberi status prioritas di bagian 12.

## 7. Kebutuhan Non-Fungsional

| ID | Kategori | Kebutuhan |
|---|---|---|
| NFR-01 | Kinerja | Halaman dimuat maksimal 3 detik pada koneksi minimal 10 Mbps. Penanda peta dimuat maksimal 2 detik. |
| NFR-02 | Keamanan | Kata sandi dienkripsi satu arah dengan Bcrypt. Formulir dilindungi token CSRF, sanitasi input, dan pencegahan SQL injection. Unggahan foto hanya format .jpg, .jpeg, .png dengan ukuran maksimal 5 MB. Koneksi memakai HTTPS. |
| NFR-03 | Kegunaan | Antarmuka responsif (mobile-friendly) untuk ponsel dan desktop. |
| NFR-04 | Keandalan dan pengujian | Alur utama (autentikasi, pelaporan, verifikasi, disposisi, penerbitan laporan) lolos pengujian E2E Laravel Dusk dengan tingkat kelulusan 100%. |
| NFR-05 | Ketersediaan | Layanan tersedia 24 jam sehari, 7 hari seminggu. |

## 8. Kebutuhan Teknis

| ID | Komponen | Spesifikasi |
|---|---|---|
| TEK-01 | Bahasa | PHP 8.2 atau lebih baru |
| TEK-02 | Framework | Laravel 10.x atau 11.x (MVC) |
| TEK-03 | Basis data | MySQL 8.0 |
| TEK-04 | Front-end | Blade, Tailwind CSS v3.x, JavaScript ES6 |
| TEK-05 | Peta | Leaflet.js dengan OpenStreetMap |
| TEK-06 | Pengujian | Laravel Dusk dengan ChromeDriver |
| TEK-07 | Ekspor | Maatwebsite/Laravel-Excel (.xlsx) dan DomPDF (PDF) |
| TEK-08 | Server | Nginx atau Apache di Ubuntu Server, versioning dengan Git |

## 9. Arsitektur dan Rancangan Data

**Arsitektur berlapis:**
- **Presentation:** Blade dan Tailwind untuk desktop, layout responsif dengan Leaflet untuk mobile.
- **Security & Network:** Nginx sebagai reverse proxy, HTTPS, Bcrypt, session manager, proteksi CSRF dan SQL injection.
- **Business Services:** Service Pelaporan Aduan, Service Verifikasi dan Disposisi, Service Master Data Wilayah, Engine Monitoring SLA dan Heatmap.
- **Base Services:** Auth dan RBAC, Audit Log dan Tracking Timeline, Media Upload Handler, Export Engine.
- **Data & Spatial:** Spatial Geo-Engine, Eloquent ORM, MySQL query builder dan cache.
- **Infrastructure & Testing:** Ubuntu Server, MySQL, penyimpanan lokal untuk foto, Laravel Dusk.

**Entitas data utama:**

| Tabel | Fungsi | Relasi utama |
|---|---|---|
| users | Akun seluruh peran (role: warga, admin, petugas, pimpinan) | - |
| kecamatan | 30 kecamatan | 1 ke banyak kelurahan |
| kelurahan | 151 kelurahan | Milik satu kecamatan |
| kategori_infrastruktur | Empat kategori fasilitas dan bobot prioritas | 1 ke banyak laporan |
| laporan_kerusakan | Aduan warga, koordinat, foto, status, prioritas | Milik satu warga dan satu kelurahan |
| disposisi_penugasan | Surat tugas dari admin ke petugas | 1 ke 1 dengan laporan |
| hasil_perbaikan | Bukti penyelesaian dan foto sesudah | 1 ke 1 dengan disposisi |
| umpan_balik | Rating dan ulasan warga | 1 ke 1 dengan laporan |

**Status laporan yang digunakan:** Menunggu Verifikasi, Diproses, Selesai, dan Ditolak.

## 10. Metrik Keberhasilan

| Metrik | Target | Cara pengukuran |
|---|---|---|
| Waktu muat halaman | Maksimal 3 detik | Uji performa pada koneksi 10 Mbps |
| Waktu muat penanda peta | Maksimal 2 detik | Uji performa |
| Lulus pengujian E2E | 100% skenario | Laravel Dusk |
| Waktu respons dinas (SLA) | Ditetapkan oleh DSDABM di modul SLA | Dashboard SLA |
| Tingkat kepuasan warga | Rata-rata rating tiap periode | Data umpan balik |
| Laporan valid vs ditolak | Dipantau per periode | Data verifikasi |

## 11. Asumsi, Dependensi, dan Risiko

**Asumsi:**
- DSDABM bersedia menggunakan sistem sebagai satu-satunya kanal resmi pelaporan kerusakan.
- Peta OpenStreetMap tersedia dan dapat diakses untuk seluruh pengguna.
- Data 30 kecamatan dan 151 kelurahan tersedia dalam bentuk resmi dari Pemkot Bandung.

**Dependensi:**
- Daftar nama dan akun petugas lapangan dari DSDABM.
- Format surat tugas dan lembar disposisi yang disetujui DSDABM.
- Layanan OpenStreetMap dan lingkungan server Ubuntu.

**Risiko:**

| Risiko | Dampak | Kemungkinan | Mitigasi |
|---|---|---|---|
| Laporan palsu atau spam membanjiri sistem | Tinggi | Sedang | Verifikasi admin, fitur tolak dengan alasan, deteksi laporan duplikat |
| Koordinat tidak akurat | Tinggi | Sedang | Penandaan manual, pemeriksaan admin sebelum disposisi |
| Foto berukuran besar memperlambat unggahan | Sedang | Tinggi | Batas 5 MB dan validasi format di sisi klien dan server |
| Adopsi warga rendah | Tinggi | Sedang | Sosialisasi melalui kelurahan dan kecamatan |
| Keterlambatan jadwal sprint | Sedang | Sedang | Prioritas MoSCoW, fitur Could dapat ditunda |

## 12. Prioritas Fitur dan Rencana Pengembangan

Pengembangan memakai Agile Scrum.

**Sprint 1: Fondasi, data master, dan pengajuan laporan**
- Master data wilayah: CRUD kecamatan dan kelurahan, dropdown hierarkis.
- Manajemen user dan role: CRUD user, pengaturan akses, log aktivitas.
- Pengajuan laporan: form, foto bukti, pinpoint peta, ubah atau batalkan laporan.
- Master kategori: CRUD kategori, tingkat keparahan default, filter.
- Verifikasi laporan: daftar aduan dengan filter, terima atau tolak, penetapan prioritas.
- Disposisi digital: lembar disposisi, kelola petugas, surat tugas, daftar tugas dengan navigasi peta, update progres dengan foto, tutup tugas.

**Sprint 2: Pemantauan, visualisasi, dan interaksi publik**
- Peta sebaran: marker, filter, ringkasan per kecamatan.
- Tracking dan riwayat: linimasa status, riwayat laporan, pencarian dengan kode unik.
- Notifikasi dan penilaian: notifikasi, kolom komentar, rating hasil.
- Dashboard SLA: grafik capaian, tabel tugas terlambat, pengaturan target waktu.
- Dashboard pimpinan: tren per periode, ranking kecamatan, indikator KPI.
- Laporan dan ekspor: rekap periodik, ekspor PDF dan Excel, rekap per kategori.
- Dukungan dan prioritas: upvote, deteksi duplikat, perhitungan skor prioritas.

**Catatan jadwal:** Proposal mencantumkan Sprint 1 pada minggu 1 sampai 5 dan Sprint 2 pada minggu 11 sampai 15, dengan rentang minggu dalam kurung yang belum jelas maknanya. Jadwal ini perlu dikonfirmasi ulang.

## 13. Pertanyaan Terbuka

| No | Pertanyaan | Catatan dari proposal | Pemutus |
|---|---|---|---|
| 1 | Apa arti rentang minggu dalam kurung di jadwal sprint? | Ada dua rentang minggu per baris | Tim PPL |
| 2 | Status laporan: apakah "Menunggu Verifikasi, Diproses, Selesai" atau "Pending" dan "Ditolak"? | Istilah berbeda di bagian 2.3, 4.2, dan 4.3 | Tim dan DSDABM |
| 3 | Apakah NIK dibutuhkan untuk akun warga? | Ada di class diagram, tidak ada di tabel users | Tim dan DSDABM |
| 4 | Apakah UC-04 sengaja dilewati atau hilang dari daftar use case? | Penomoran UC melompat dari UC-03 ke UC-05 | Tim PPL |
| 5 | Apakah notifikasi wajib di versi awal? | Ada di sprint 2 dan belum ada di kebutuhan fungsional | Product owner |
| 6 | Bagaimana batas SLA per kategori dan siapa yang menetapkan? | Modul SLA ada, nilai target belum disebutkan | DSDABM |
| 7 | Apakah ada akses publik untuk melihat laporan tanpa login? | Peta publik ada di SKF-04 tanpa keterangan login | Product owner |
| 8 | Bagaimana kebijakan retensi foto dan data laporan? | Belum ada | DSDABM dan tim |
| 9 | Mockup bagian 4.5 belum tersedia, perlu dilengkapi? | Hanya berupa judul | Tim PPL |

---

## Lampiran

- Diagram Use Case, Arsitektur, ERD, dan Class Diagram dari proposal (bagian 4).
- Mockup antarmuka: Dashboard, Lapor Kerusakan, Dashboard Operasional, Kelola Data Wilayah.
- Glosarium: SLA, DSDABM, RBAC, geotagging, disposisi, E2E testing.
