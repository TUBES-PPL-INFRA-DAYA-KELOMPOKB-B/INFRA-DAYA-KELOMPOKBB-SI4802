<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserService
{
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
        if ($avatarFile) {
            $data['avatar'] = $this->uploadAvatar($avatarFile);
        }

        return User::create($data);
    }

    public function updateUser(int $id, array $data, ?UploadedFile $avatarFile = null): User
    {
        $user = User::findOrFail($id);

        if ($avatarFile) {
            if ($user->avatar) {
                FileUploadService::deletePublicFile($user->avatar);
            }
            $data['avatar'] = FileUploadService::storePublicFile($avatarFile, 'avatars');
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        // Atur status aktif jika diberikan
        if (isset($data['is_active'])) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        $user->update($data);

        return $user;
    }

    public function toggleUserStatus(int $id): User
    {
        $user = User::findOrFail($id);
        $user->is_active = ! $user->is_active;
        $user->save();

        return $user;
    }

    public function deleteUser(int $id): bool
    {
        $user = User::findOrFail($id);

        if ($user->avatar) {
            FileUploadService::deletePublicFile($user->avatar);
        }

        return $user->delete();
    }

    protected function uploadAvatar(UploadedFile $file): string
    {
        return FileUploadService::storePublicFile($file, 'avatars');
    }
}
