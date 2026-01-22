<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordChange
{
    /**
     * Routes that are excluded from password change check
     */
    protected array $excludedRoutes = [
        'password.change',
        'password.update',
        'logout',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $routeName = $request->route()?->getName();

        // Skip check for excluded routes
        if (in_array($routeName, $this->excludedRoutes)) {
            return $next($request);
        }

        // Check if password change is required
        if ($user->needsPasswordChange()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Anda harus mengganti password terlebih dahulu.',
                    'require_password_change' => true,
                ], 403);
            }

            return redirect()->route('password.change')
                ->with('warning', 'Anda harus mengganti password Anda sebelum melanjutkan.');
        }

        return $next($request);
    }
}
