<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UserLogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserLogController extends Controller
{
    public function __construct(
        protected UserLogService $logService
    ) {}

    /**
     * AC 6.4, 6.5, 6.6: Menampilkan riwayat log aktivitas pengguna.
     * Log bersifat append-only, hanya-baca, tidak ada aksi edit atau delete.
     */
    public function index(Request $request): View
    {
        $action = $request->query('action');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $search = $request->query('search');

        $logs = $this->logService->getPaginatedLogs(
            action: $action,
            startDate: $startDate,
            endDate: $endDate,
            search: $search,
            perPage: 15
        );

        $actions = [
            'user_created' => 'Pembuatan Akun',
            'user_updated' => 'Pembaruan Data Akun',
            'role_changed' => 'Perubahan Role',
            'password_changed' => 'Perubahan Password',
            'account_activated' => 'Aktivasi Akun',
            'account_deactivated' => 'Nonaktivasi Akun',
            'user_deleted' => 'Penghapusan Akun',
            'login_success' => 'Login Berhasil',
            'login_failed' => 'Login Gagal',
        ];

        return view('admin.user-logs.index', compact('logs', 'actions', 'action', 'startDate', 'endDate', 'search'));
    }
}

