<?php

namespace App\Policies;

use App\Models\LaporanKerusakan;
use App\Models\User;

class LaporanPolicy
{
    /**
     * Warga hanya bisa melihat laporan miliknya sendiri,
     * Petugas, Admin, dan Pimpinan dapat melihat laporan sesuai wewenang.
     */
    public function view(User $user, LaporanKerusakan $laporan): bool
    {
        if ($user->isAdmin() || $user->isPetugas() || $user->isPimpinan()) {
            return true;
        }

        return (int) $laporan->warga_id === (int) $user->id;
    }

    /**
     * Warga hanya bisa mengubah laporannya sendiri dan HANYA saat status masih 'menunggu_verifikasi'.
     */
    public function update(User $user, LaporanKerusakan $laporan): bool
    {
        return (int) $laporan->warga_id === (int) $user->id && $laporan->canBeModifiedByWarga();
    }

    /**
     * Warga hanya bisa membatalkan/menghapus laporannya sendiri saat status masih 'menunggu_verifikasi'.
     */
    public function delete(User $user, LaporanKerusakan $laporan): bool
    {
        return (int) $laporan->warga_id === (int) $user->id && $laporan->canBeCancelledByWarga();
    }

    /**
     * Alias kebijakan untuk aksi pembatalan tiket laporan.
     */
    public function cancel(User $user, LaporanKerusakan $laporan): bool
    {
        return (int) $laporan->warga_id === (int) $user->id && $laporan->canBeCancelledByWarga();
    }
}
