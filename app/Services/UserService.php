<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function __construct(protected ?UserLogService $logService = null)
    {
        $this->logService = $logService ?? app(UserLogService::class);
    }

    /**
     * Mendapatkan daftar pengguna dengan paginasi, filter role, dan pencarian.
     */
    public function getPaginatedUsers(?string $search = null, ?string $role = null, int $perPage = 10): LengthAwarePaginator
    {
        return User::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($role, function ($query, $role) {
                $query->where('role', $role);
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findUser(int $id): User
    {
        return User::findOrFail($id);
    }

    public function createUser(array $data, ?UploadedFile $avatarFile = null): User
    {
        return DB::transaction(function () use ($data, $avatarFile) {
            if ($avatarFile) {
                $data['avatar'] = $this->uploadAvatar($avatarFile);
            }

            $user = User::create($data);

            // AC 6.3: Catat log pembuatan user baru
            $this->logService->log(
                'user_created',
                $user->id,
                null,
                $user->only(['name', 'email', 'phone', 'role', 'is_active'])
            );

            return $user;
        });
    }

    public function updateUser(int $id, array $data, ?UploadedFile $avatarFile = null): User
    {
        return DB::transaction(function () use ($id, $data, $avatarFile) {
            $user = User::findOrFail($id);

            $oldData = $user->only(['name', 'email', 'phone', 'role', 'is_active']);

            // AC 5.6: Admin tidak dapat mengubah role akunnya sendiri
            if (isset($data['role']) && $data['role'] !== $user->role) {
                if (auth()->id() === $user->id) {
                    throw new \DomainException('Anda tidak dapat mengubah role akun Anda sendiri.');
                }

                // AC 5.5: Perlindungan admin aktif terakhir
                if ($user->isLastActiveAdmin() && $data['role'] !== 'admin') {
                    throw new \DomainException('Tidak dapat menurunkan role Admin aktif terakhir. Sistem harus memiliki setidaknya satu Admin yang aktif.');
                }
            }

            // Perlindungan penonaktifan admin sendiri & admin terakhir
            if (isset($data['is_active']) && ! (bool) $data['is_active']) {
                if (auth()->id() === $user->id) {
                    throw new \DomainException('Anda tidak dapat menonaktifkan akun Anda sendiri.');
                }

                if ($user->isLastActiveAdmin()) {
                    throw new \DomainException('Tidak dapat menonaktifkan Admin aktif terakhir. Sistem harus memiliki setidaknya satu Admin yang aktif.');
                }
            }

            if ($avatarFile) {
                if ($user->avatar) {
                    FileUploadService::deletePublicFile($user->avatar);
                }
                $data['avatar'] = FileUploadService::storePublicFile($avatarFile, 'avatars');
            }

            $passwordChanged = ! empty($data['password']);
            if (! $passwordChanged) {
                unset($data['password']);
            }

            // Atur status aktif jika diberikan
            if (isset($data['is_active'])) {
                $data['is_active'] = (bool) $data['is_active'];
            }

            $user->update($data);

            $newData = $user->only(['name', 'email', 'phone', 'role', 'is_active']);

            // AC 6.3: Catat log perubahan data
            $this->logService->log('user_updated', $user->id, $oldData, $newData);

            if ($passwordChanged) {
                $this->logService->log(
                    'password_changed',
                    $user->id,
                    null,
                    ['status' => 'password_updated']
                );
            }

            if (isset($oldData['role']) && isset($newData['role']) && $oldData['role'] !== $newData['role']) {
                $this->logService->log(
                    'role_changed',
                    $user->id,
                    ['role' => $oldData['role']],
                    ['role' => $newData['role']]
                );
            }

            return $user;
        });
    }

    /**
     * AC 5.2: Aksi khusus untuk mengubah peran (role) pengguna secara langsung.
     */
    public function updateUserRole(int $id, string $newRole): User
    {
        return DB::transaction(function () use ($id, $newRole) {
            $user = User::findOrFail($id);

            if (auth()->id() === $user->id && $newRole !== $user->role) {
                throw new \DomainException('Anda tidak dapat mengubah role akun Anda sendiri.');
            }

            if ($user->isLastActiveAdmin() && $newRole !== 'admin') {
                throw new \DomainException('Tidak dapat menurunkan role Admin aktif terakhir. Sistem harus memiliki setidaknya satu Admin yang aktif.');
            }

            $oldRole = $user->role;
            $user->update(['role' => $newRole]);

            // AC 6.3: Catat perubahan peran
            $this->logService->log(
                'role_changed',
                $user->id,
                ['role' => $oldRole],
                ['role' => $newRole]
            );

            return $user;
        });
    }

    public function toggleUserStatus(int $id, ?string $reason = null): User
    {
        return DB::transaction(function () use ($id, $reason) {
            $user = User::findOrFail($id);

            if (auth()->id() === $user->id) {
                throw new \DomainException('Anda tidak dapat menonaktifkan akun Anda sendiri saat sedang masuk.');
            }

            if ($user->is_active && $user->isLastActiveAdmin()) {
                throw new \DomainException('Tidak dapat menonaktifkan Admin aktif terakhir. Sistem harus memiliki setidaknya satu Admin yang aktif.');
            }

            $oldStatus = $user->is_active;
            $user->is_active = ! $oldStatus;
            $user->deactivated_at = $user->is_active ? null : now();
            $user->save();

            // AC 6.2: Pencatatan aktivasi / nonaktivasi
            $action = $user->is_active ? 'account_activated' : 'account_deactivated';
            $this->logService->log(
                $action,
                $user->id,
                ['is_active' => $oldStatus],
                ['is_active' => $user->is_active],
                $reason
            );

            return $user;
        });
    }

    public function deleteUser(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $user = User::findOrFail($id);

            if ($user->hasRelatedData()) {
                throw new \DomainException('Pengguna tidak dapat dihapus karena memiliki riwayat laporan atau penugasan terkait.');
            }

            $userData = $user->only(['name', 'email', 'role']);
            $userData['id'] = $user->id;

            if ($user->avatar) {
                FileUploadService::deletePublicFile($user->avatar);
            }

            // AC 6.3: Catat log penghapusan sebelum record user dihapus (FK ON DELETE SET NULL)
            $this->logService->log('user_deleted', $user->id, $userData, null);

            return (bool) $user->delete();
        });
    }

    protected function uploadAvatar(UploadedFile $file): string
    {
        return FileUploadService::storePublicFile($file, 'avatars');
    }
}
