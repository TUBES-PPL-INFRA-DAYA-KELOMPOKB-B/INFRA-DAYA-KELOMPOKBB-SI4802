<?php

namespace App\Services;

use App\Models\UserLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserLogService
{
    /**
     * AC 6.2, 6.3, 6.6: Mencatat log audit aktivitas akun pengguna.
     * Menjamin keamanan data sensitif (password/hash tidak pernah dicatat).
     */
    public function log(
        string $action,
        ?int $targetUserId = null,
        ?array $oldValue = null,
        ?array $newValue = null,
        ?string $reason = null,
        ?int $actorId = null
    ): UserLog {
        $actorId = $actorId ?? auth()->id();

        // AC 6.6: Sanitasi data sensitif
        if ($oldValue) {
            unset($oldValue['password'], $oldValue['remember_token']);
        }
        if ($newValue) {
            unset($newValue['password'], $newValue['password_confirmation'], $newValue['remember_token']);
        }

        return UserLog::create([
            'actor_id' => $actorId,
            'target_user_id' => $targetUserId,
            'action' => $action,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'reason' => $reason,
            'ip_address' => request() ? request()->ip() : null,
            'created_at' => now(),
        ]);
    }

    /**
     * AC 6.4, 6.6: Mendapatkan daftar log berpaginasi dengan filter urutan terbaru, aksi, user, pencarian nama/email, dan rentang tanggal.
     */
    public function getPaginatedLogs(
        ?string $action = null,
        ?int $userId = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $search = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        return UserLog::with(['actor', 'targetUser'])
            ->when($action, function ($query, $action) {
                $query->where('action', $action);
            })
            ->when($userId, function ($query, $userId) {
                $query->where(function ($q) use ($userId) {
                    $q->where('target_user_id', $userId)
                        ->orWhere('actor_id', $userId);
                });
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('targetUser', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })->orWhereHas('actor', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })->orWhere('old_value->name', 'like', "%{$search}%")
                      ->orWhere('old_value->email', 'like', "%{$search}%")
                      ->orWhere('new_value->name', 'like', "%{$search}%")
                      ->orWhere('new_value->email', 'like', "%{$search}%");
                });
            })
            ->when($startDate, function ($query, $startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query, $endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            })
            ->latest('created_at')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }
}

