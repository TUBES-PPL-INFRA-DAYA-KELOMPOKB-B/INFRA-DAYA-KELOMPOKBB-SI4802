<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserActive
{
    /**
     * Handle an incoming request.
     * Memastikan pengguna yang sedang masuk berstatus aktif.
     * Jika akun dinonaktifkan oleh administrator, segera batalkan sesi dan arahkan ke login.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            // Periksa status keaktifan terkini akun
            if (! $request->user()->fresh()?->is_active) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Akun Anda sedang dinonaktifkan oleh administrator.',
                    ], 403);
                }

                return redirect()->route('login')->withErrors([
                    'email' => 'Akun Anda sedang dinonaktifkan oleh administrator.',
                ]);
            }
        }

        return $next($request);
    }
}
