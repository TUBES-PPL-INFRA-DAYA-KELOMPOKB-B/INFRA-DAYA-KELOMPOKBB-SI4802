<?php

namespace App\Services;

use App\Models\DisposisiPenugasan;
use App\Models\HasilPerbaikan;
use App\Models\RiwayatStatus;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PetugasService
{
    // ==========================================
    // PBI-17 (IFD-18): Pengelolaan Petugas Lapangan (Admin DSDABM)
    // ==========================================

    /**
     * Mengambil daftar petugas lapangan dengan pencarian, filter status, dan paginasi (AC17-03, AC17-04).
     */
    public function getPaginatedPetugas(
        ?string $search = null,
        ?string $status = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        return User::query()
            ->where('role', 'petugas')
            ->withCount([
                'penugasanPetugas as total_tugas',
                'penugasanPetugas as tugas_aktif' => function ($query) {
                    $query->where('status_penugasan', '!=', 'selesai');
                },
            ])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($query, $status) {
                if ($status === 'aktif') {
                    $query->where('is_active', true);
                } elseif ($status === 'nonaktif') {
                    $query->where('is_active', false);
                }
            })
            ->orderBy('name', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Memeriksa apakah terdapat akun petugas sama sekali di database (AC17-05).
     */
    public function hasAnyPetugas(): bool
    {
        return User::where('role', 'petugas')->exists();
    }

    /**
     * Mengambil ringkasan metrik petugas lapangan untuk kartu ringkasan.
     */
    public function getMetrics(): array
    {
        $totalPetugas = User::where('role', 'petugas')->count();
        $totalAktif = User::where('role', 'petugas')->where('is_active', true)->count();
        $totalNonaktif = User::where('role', 'petugas')->where('is_active', false)->count();

        $totalSedangBertugas = User::where('role', 'petugas')
            ->whereHas('penugasanPetugas', function ($q) {
                $q->where('status_penugasan', '!=', 'selesai');
            })
            ->count();

        return [
            'totalPetugas' => $totalPetugas,
            'totalAktif' => $totalAktif,
            'totalNonaktif' => $totalNonaktif,
            'totalSedangBertugas' => $totalSedangBertugas,
        ];
    }

    /**
     * Mengambil data detail petugas beserta riwayat penugasan (AC17-12, AC17-18).
     */
    public function findPetugas(int $id): User
    {
        return User::where('role', 'petugas')
            ->with([
                'penugasanPetugas' => function ($query) {
                    $query->with([
                        'laporan.kelurahan.kecamatan',
                        'laporan.kategori',
                        'admin',
                    ])->orderBy('created_at', 'desc');
                },
            ])
            ->findOrFail($id);
    }

    /**
     * Menambahkan akun petugas lapangan baru (AC17-06, AC17-07, AC17-09).
     */
    public function createPetugas(array $data): User
    {
        return DB::transaction(function () use ($data) {
            return User::create([
                'name' => trim($data['name']),
                'email' => strtolower(trim($data['email'])),
                'phone' => (string) trim($data['phone']),
                'password' => Hash::make($data['password']),
                'role' => 'petugas',
                'is_active' => true,
            ]);
        });
    }

    /**
     * Memperbarui identitas petugas (hanya nama, email, dan telepon) (AC17-11).
     */
    public function updatePetugas(User $petugas, array $data): User
    {
        return DB::transaction(function () use ($petugas, $data) {
            $target = User::where('id', $petugas->id)
                ->where('role', 'petugas')
                ->lockForUpdate()
                ->firstOrFail();

            $target->name = trim($data['name']);
            $target->email = strtolower(trim($data['email']));
            $target->phone = (string) trim($data['phone']);

            // Password, role, dan is_active dilindungi dan tidak diubah
            $target->save();

            return $target;
        });
    }

    /**
     * Mengubah status aktif/nonaktif akun petugas dengan proteksi konkurensi (AC17-13, AC17-14, AC17-17, AC17-22).
     *
     * @return array{success: bool, message: string, status?: string, blocking_tasks?: Collection}
     */
    public function toggleStatus(int $id): array
    {
        return DB::transaction(function () use ($id) {
            $petugas = User::where('id', $id)
                ->where('role', 'petugas')
                ->lockForUpdate()
                ->firstOrFail();

            if ($petugas->is_active) {
                // Nonaktivasi: periksa apakah ada tugas berjalan (status != 'selesai')
                $blockingTasks = $petugas->penugasanPetugas()
                    ->with('laporan')
                    ->where('status_penugasan', '!=', 'selesai')
                    ->lockForUpdate()
                    ->get();

                if ($blockingTasks->isNotEmpty()) {
                    return [
                        'success' => false,
                        'message' => 'Petugas tidak dapat dinonaktifkan karena masih memiliki tugas penugasan yang belum selesai.',
                        'blocking_tasks' => $blockingTasks,
                    ];
                }

                $petugas->is_active = false;
                $petugas->save();

                return [
                    'success' => true,
                    'message' => "Petugas {$petugas->name} berhasil dinonaktifkan.",
                    'status' => 'nonaktif',
                ];
            } else {
                // Aktivasi kembali: pulihkan status aktif dengan ID akun yang sama
                $petugas->is_active = true;
                $petugas->save();

                return [
                    'success' => true,
                    'message' => "Petugas {$petugas->name} berhasil diaktifkan kembali.",
                    'status' => 'aktif',
                ];
            }
        });
    }

    // ==========================================
    // Fitur Petugas Lapangan (Tugas Lapangan)
    // ==========================================

    /**
     * Mengambil daftar tugas penugasan lapangan milik petugas tertentu secara terpaginasi.
     * Sesuai FR-19.1 - FR-19.4: filter status, pencarian, dan pengurutan default prioritas tertinggi lalu disposisi terlama.
     */
    public function getPaginatedTugas(
        int $petugasId,
        ?string $status = null,
        ?string $search = null,
        ?string $sort = null,
        int $perPage = 9
    ): LengthAwarePaginator {
        $query = DisposisiPenugasan::with([
            'laporan.kelurahan.kecamatan',
            'laporan.kategori',
            'laporan.warga',
            'admin',
        ])
            ->where('petugas_id', $petugasId);

        if ($status && in_array(strtolower($status), ['ditugaskan', 'diterima', 'sedang_dikerjakan', 'dalam_pengerjaan', 'selesai'], true)) {
            if (in_array(strtolower($status), ['sedang_dikerjakan', 'dalam_pengerjaan'], true)) {
                $query->whereIn('status_penugasan', ['sedang_dikerjakan', 'dalam_pengerjaan']);
            } else {
                $query->where('status_penugasan', strtolower($status));
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_surat_tugas', 'like', "%{$search}%")
                    ->orWhereHas('laporan', function ($lq) use ($search) {
                        $lq->where('nomor_tiket', 'like', "%{$search}%")
                            ->orWhere('judul', 'like', "%{$search}%")
                            ->orWhere('deskripsi_kondisi', 'like', "%{$search}%")
                            ->orWhereHas('kelurahan', function ($kq) use ($search) {
                                $kq->where('nama_kelurahan', 'like', "%{$search}%")
                                    ->orWhereHas('kecamatan', function ($kcq) use ($search) {
                                        $kcq->where('nama_kecamatan', 'like', "%{$search}%");
                                    });
                            });
                    });
            });
        }

        $query->join('laporan_kerusakan', 'disposisi_penugasan.laporan_id', '=', 'laporan_kerusakan.id')
            ->select('disposisi_penugasan.*');

        if ($sort === 'terbaru') {
            $query->orderBy('disposisi_penugasan.tanggal_disposisi', 'desc')
                  ->orderBy('disposisi_penugasan.id', 'desc');
        } elseif ($sort === 'terlama') {
            $query->orderBy('disposisi_penugasan.tanggal_disposisi', 'asc')
                  ->orderBy('disposisi_penugasan.id', 'asc');
        } elseif ($sort === 'tenggat') {
            $query->orderBy('disposisi_penugasan.tenggat_waktu', 'asc');
        } else {
            // Default FR-19.3: Prioritas tertinggi lalu tanggal disposisi terlama
            $query->orderByRaw("
                CASE laporan_kerusakan.prioritas
                    WHEN 'tinggi' THEN 1
                    WHEN 'sedang' THEN 2
                    WHEN 'rendah' THEN 3
                    ELSE 4
                END ASC
            ")
            ->orderBy('disposisi_penugasan.tanggal_disposisi', 'asc')
            ->orderBy('disposisi_penugasan.id', 'asc');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Mengambil detail tugas tertentu dan memverifikasi kepemilikan petugas (IDOR prevention).
     */
    public function getTugasDetail(int $disposisiId, int $petugasId): DisposisiPenugasan
    {
        $disposisi = DisposisiPenugasan::with([
            'laporan.kelurahan.kecamatan',
            'laporan.kategori',
            'laporan.warga',
            'laporan.riwayatStatus.user',
            'admin',
            'petugas',
            'hasilPerbaikan',
        ])->findOrFail($disposisiId);

        if ($disposisi->petugas_id !== $petugasId) {
            throw new HttpException(403, 'Anda tidak memiliki hak akses terhadap tugas penugasan ini.');
        }

        return $disposisi;
    }

    /**
     * Menerima tugas penugasan oleh petugas lapangan (FR-19.8 / PBI-19).
     * Mengubah status penugasan dari 'ditugaskan' menjadi 'diterima' dan mencatat riwayat log.
     */
    public function acceptTask(int $disposisiId, int $petugasId): DisposisiPenugasan
    {
        $disposisi = $this->getTugasDetail($disposisiId, $petugasId);

        if (! $disposisi->canBeAccepted()) {
            throw new HttpException(422, 'Tugas ini tidak dalam status siap diterima (status saat ini: ' . $disposisi->status_penugasan . ').');
        }

        return DB::transaction(function () use ($disposisi, $petugasId) {
            $disposisi->update([
                'status_penugasan' => 'diterima',
            ]);

            $petugas = User::find($petugasId);
            $namaPetugas = $petugas ? $petugas->name : 'Petugas Lapangan';

            RiwayatStatus::create([
                'laporan_id' => $disposisi->laporan_id,
                'user_id' => $petugasId,
                'status' => 'diproses',
                'keterangan' => "Tugas penanganan laporan diterima oleh petugas {$namaPetugas}.",
            ]);

            return $disposisi->fresh();
        });
    }

    /**
     * Memulai pelaksanaan pengerjaan lapangan oleh petugas.
     */
    public function mulaiPengerjaan(int $disposisiId, int $petugasId): DisposisiPenugasan
    {
        $disposisi = $this->getTugasDetail($disposisiId, $petugasId);

        if (! $disposisi->canBeStarted()) {
            throw new HttpException(422, 'Tugas ini tidak dalam status siap dikerjakan.');
        }

        return DB::transaction(function () use ($disposisi, $petugasId) {
            $disposisi->update([
                'status_penugasan' => 'sedang_dikerjakan',
            ]);

            $petugas = User::find($petugasId);
            $namaPetugas = $petugas ? $petugas->name : 'Petugas Lapangan';

            RiwayatStatus::create([
                'laporan_id' => $disposisi->laporan_id,
                'user_id' => $petugasId,
                'status' => 'diproses',
                'keterangan' => "Petugas {$namaPetugas} telah memulai penanganan teknis di lokasi.",
            ]);

            return $disposisi->fresh();
        });
    }

    /**
     * Memperbarui progres pengerjaan, catatan perbaikan, dan/atau foto bukti perbaikan (PBI-20).
     * Sesuai FR-20.1 - FR-20.9.
     */
    public function updateProgres(
        int $disposisiId,
        int $petugasId,
        ?string $catatanPerbaikan = null,
        ?UploadedFile $fotoBukti = null,
        ?string $statusPenugasan = null
    ): DisposisiPenugasan {
        $disposisi = $this->getTugasDetail($disposisiId, $petugasId);

        // FR-20.8: Petugas hanya dapat memperbarui progres dan mengunggah foto untuk tugas yang ditugaskan kepada dirinya dan belum ditutup/selesai.
        if ($disposisi->isSelesai()) {
            throw new HttpException(422, 'Tugas ini sudah selesai dan tidak dapat diperbarui lagi.');
        }

        // Tentukan status penugasan baru jika diberikan atau transisi otomatis (FR-20.1)
        $targetStatus = $statusPenugasan ?: $disposisi->status_penugasan;

        // Jika saat ini 'ditugaskan' atau 'diterima', dan petugas memperbarui progres, maka otomatis beralih ke 'sedang_dikerjakan'
        if (in_array($disposisi->status_penugasan, ['ditugaskan', 'diterima'], true) && ! $statusPenugasan) {
            $targetStatus = 'sedang_dikerjakan';
        }

        if ($targetStatus === 'dalam_pengerjaan') {
            $targetStatus = 'sedang_dikerjakan';
        }

        return DB::transaction(function () use ($disposisi, $petugasId, $catatanPerbaikan, $fotoBukti, $targetStatus) {
            $path = null;
            if ($fotoBukti) {
                // FR-20.3, FR-20.4, FR-20.5: Simpan foto dengan nama unik di local asset storage
                $path = FileUploadService::storePublicFile($fotoBukti, 'laporan/sesudah');
            }

            // FR-20.5 & Bagian 6 PRD: Simpan / perbarui pada tabel hasil_perbaikan
            $hasilData = [
                'petugas_id' => $petugasId,
            ];

            if ($catatanPerbaikan !== null) {
                $hasilData['catatan_perbaikan'] = $catatanPerbaikan;
            }

            if ($path !== null) {
                $hasilData['foto_bukti_sesudah'] = $path;
            }

            HasilPerbaikan::updateOrCreate(
                ['disposisi_id' => $disposisi->id],
                $hasilData
            );

            $oldStatus = $disposisi->status_penugasan;
            $statusChanged = $oldStatus !== $targetStatus;

            $disposisiUpdate = [
                'status_penugasan' => $targetStatus,
            ];
            if ($catatanPerbaikan !== null) {
                $disposisiUpdate['catatan_perbaikan'] = $catatanPerbaikan;
            }
            if ($path !== null) {
                $disposisiUpdate['foto_bukti_sesudah'] = $path;
            }
            $disposisi->update($disposisiUpdate);

            // FR-20.7: Status laporan kerusakan utama otomatis tersinkronisasi menjadi 'diproses' saat penugasan dalam pengerjaan
            $laporanUpdate = [];
            if ($targetStatus === 'sedang_dikerjakan') {
                $laporanUpdate['status'] = 'diproses';
            }
            if ($catatanPerbaikan !== null) {
                $laporanUpdate['catatan_perbaikan'] = $catatanPerbaikan;
            }
            if ($path !== null) {
                $laporanUpdate['foto_bukti_sesudah'] = $path;
            }
            if (! empty($laporanUpdate)) {
                $disposisi->laporan->update($laporanUpdate);
            }

            $petugas = User::find($petugasId);
            $namaPetugas = $petugas ? $petugas->name : 'Petugas Lapangan';

            // FR-20.6: Setiap perubahan status tercatat secara otomatis pada log riwayat status
            $keteranganLog = "Progres penanganan diperbarui oleh {$namaPetugas}.";
            if ($statusChanged) {
                $keteranganLog = "Status penugasan diubah menjadi [{$targetStatus}] oleh {$namaPetugas}.";
            }
            if ($catatanPerbaikan) {
                $keteranganLog .= " Catatan: {$catatanPerbaikan}";
            }
            if ($path) {
                $keteranganLog .= " (Foto bukti hasil perbaikan diunggah)";
            }

            RiwayatStatus::create([
                'laporan_id' => $disposisi->laporan_id,
                'user_id' => $petugasId,
                'status' => 'diproses',
                'keterangan' => $keteranganLog,
            ]);

            return $disposisi->fresh(['hasilPerbaikan', 'laporan.riwayatStatus.user']);
        });
    }

    /**
     * Menyelesaikan tugas pengerjaan dengan unggahan foto bukti sesudah dan catatan teknis (PBI-21 / FR-21.1 - FR-21.7).
     * Dapat dipanggil melalui aksi completeTask().
     */
    public function selesaikanTugas(
        int $disposisiId,
        int $petugasId,
        ?UploadedFile $fotoSesudah,
        string $catatanPerbaikan
    ): DisposisiPenugasan {
        $disposisi = $this->getTugasDetail($disposisiId, $petugasId);

        // FR-21.7: Tugas yang sudah selesai tidak dapat ditutup ulang (penutupan ganda ditolak)
        if (! $disposisi->canBeCompleted() || $disposisi->isSelesai()) {
            throw new HttpException(422, 'Tugas ini sudah selesai atau tidak dapat diselesaikan kembali.');
        }

        $path = FileUploadService::storePublicFile($fotoSesudah, 'laporan/sesudah');

        return DB::transaction(function () use ($disposisi, $petugasId, $path, $catatanPerbaikan) {
            $now = now();

            $disposisi->update([
                'status_penugasan' => 'selesai',
                'foto_bukti_sesudah' => $path,
                'catatan_perbaikan' => $catatanPerbaikan,
                'tanggal_selesai' => $now->toDateString(),
            ]);

            $disposisi->laporan->update([
                'status' => 'selesai',
                'foto_bukti_sesudah' => $path,
                'catatan_perbaikan' => $catatanPerbaikan,
                'selesai_at' => $now,
            ]);

            $petugas = User::find($petugasId);
            $namaPetugas = $petugas ? $petugas->name : 'Petugas Lapangan';

            RiwayatStatus::create([
                'laporan_id' => $disposisi->laporan_id,
                'user_id' => $petugasId,
                'status' => 'selesai',
                'keterangan' => "Perbaikan tuntas oleh {$namaPetugas}. Catatan: {$catatanPerbaikan}",
            ]);

            return $disposisi->fresh(['hasilPerbaikan']);
        });
    }

    /**
     * Alias aksi completeTask sesuai FR-21.1 pada PRD.
     */
    public function completeTask(
        int $disposisiId,
        int $petugasId,
        ?UploadedFile $fotoSesudah,
        string $catatanPerbaikan
    ): DisposisiPenugasan {
        return $this->selesaikanTugas($disposisiId, $petugasId, $fotoSesudah, $catatanPerbaikan);
    }

    /**
     * Mengambil statistik ringkasan tugas untuk kartu metrik petugas.
     */
    public function getStatistikTugas(int $petugasId): array
    {
        $baseQuery = DisposisiPenugasan::where('petugas_id', $petugasId);

        return [
            'total' => (clone $baseQuery)->count(),
            'ditugaskan' => (clone $baseQuery)->where('status_penugasan', 'ditugaskan')->count(),
            'diterima' => (clone $baseQuery)->where('status_penugasan', 'diterima')->count(),
            'sedang_dikerjakan' => (clone $baseQuery)->whereIn('status_penugasan', ['sedang_dikerjakan', 'dalam_pengerjaan'])->count(),
            'selesai' => (clone $baseQuery)->where('status_penugasan', 'selesai')->count(),
        ];
    }
}
