<?php

namespace App\Providers;

use App\Models\LaporanKerusakan;
use App\Models\User;
use App\Policies\LaporanPolicy;
use App\Services\UserLogService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(
            LaporanKerusakan::class,
            LaporanPolicy::class
        );

        // AC 6.3: Catat event login berhasil
        Event::listen(function (Login $event) {
            /** @var User $user */
            $user = $event->user;
            if ($user instanceof User) {
                app(UserLogService::class)->log(
                    action: 'login_success',
                    targetUserId: $user->id,
                    newValue: ['email' => $user->email, 'role' => $user->role],
                    actorId: $user->id
                );
            }
        });

        // AC 6.3 & 6.6: Catat event login gagal (tanpa menyimpan password/hash)
        Event::listen(function (Failed $event) {
            $user = $event->user;
            $email = $event->credentials['email'] ?? null;
            app(UserLogService::class)->log(
                action: 'login_failed',
                targetUserId: $user instanceof User ? $user->id : null,
                newValue: ['email' => $email],
                actorId: $user instanceof User ? $user->id : null
            );
        });
    }
}
