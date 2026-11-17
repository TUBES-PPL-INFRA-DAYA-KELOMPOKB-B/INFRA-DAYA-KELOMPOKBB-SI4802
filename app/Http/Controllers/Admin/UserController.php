<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserCreateRequest;
use App\Http\Requests\Admin\UserUpdateRequest;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function index(Request $request): View
    {
        $search = $request->query('search');
        $role = $request->query('role');

        $users = $this->userService->getPaginatedUsers($search, $role);

        return view('admin.users.index', compact('users', 'search', 'role'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(UserCreateRequest $request): RedirectResponse
    {
        $this->userService->createUser(
            $request->validated(),
            $request->file('avatar')
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    public function show(int $id): View
    {
        $user = $this->userService->findUser($id);

        return view('admin.users.show', compact('user'));
    }

    public function edit(int $id): View
    {
        $user = $this->userService->findUser($id);

        return view('admin.users.edit', compact('user'));
    }

    public function update(UserUpdateRequest $request, int $id): RedirectResponse
    {
        try {
            $this->userService->updateUser(
                $id,
                $request->validated(),
                $request->file('avatar')
            );
        } catch (\DomainException $e) {
            return redirect()
                ->route('admin.users.edit', $id)
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * AC 5.2: Aksi khusus ubah role pengguna.
     */
    public function updateRole(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:admin,petugas,pimpinan,warga'],
        ], [
            'role.required' => 'Peran pengguna wajib dipilih.',
            'role.in' => 'Peran yang dipilih tidak valid.',
        ]);

        try {
            $user = $this->userService->updateUserRole($id, $validated['role']);
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', "Peran pengguna {$user->name} berhasil diubah menjadi {$user->role}.");
    }

    public function toggleActive(Request $request, int $id): RedirectResponse
    {
        if (auth()->id() === $id) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri saat sedang masuk.');
        }

        try {
            $user = $this->userService->toggleUserStatus($id, $request->input('reason'));
        } catch (\DomainException $e) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', $e->getMessage());
        }

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        Log::info("Status aktivasi akun user ID {$user->id} ({$user->name}) berhasil {$statusText} oleh Admin ID ".auth()->id());

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Status akun pengguna {$user->name} berhasil {$statusText}.");
    }

    public function destroy(int $id): RedirectResponse
    {
        if (auth()->id() === $id) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri saat sedang masuk.');
        }

        try {
            $this->userService->deleteUser($id);
        } catch (\DomainException $e) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
