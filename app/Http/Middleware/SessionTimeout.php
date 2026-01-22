<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    /**
     * Maximum session lifetime in minutes (8 hours)
     */
    protected int $maxLifetime = 480;

    /**
     * Idle timeout in minutes (2 hours)
     */
    protected int $idleTimeout = 120;

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

        $session = $request->session();
        $now = time();

        // Check if session has started time
        if (!$session->has('session_started_at')) {
            $session->put('session_started_at', $now);
        }

        // Check maximum session lifetime
        $sessionStartedAt = $session->get('session_started_at');
        if (($now - $sessionStartedAt) > ($this->maxLifetime * 60)) {
            return $this->terminateSession($request, 'Sesi Anda telah berakhir karena melebihi batas waktu maksimal. Silakan login kembali.');
        }

        // Check idle timeout
        $lastActivity = $session->get('last_activity', $now);
        if (($now - $lastActivity) > ($this->idleTimeout * 60)) {
            return $this->terminateSession($request, 'Sesi Anda telah berakhir karena tidak aktif. Silakan login kembali.');
        }

        // Update last activity
        $session->put('last_activity', $now);

        // Add session info to response headers for frontend
        $response = $next($request);
        
        $remainingTime = min(
            ($this->maxLifetime * 60) - ($now - $sessionStartedAt),
            ($this->idleTimeout * 60)
        );

        if ($response instanceof Response && method_exists($response, 'header')) {
            $response->headers->set('X-Session-Remaining', max(0, $remainingTime));
        }

        return $response;
    }

    /**
     * Terminate the session and redirect to login
     */
    protected function terminateSession(Request $request, string $message): Response
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'session_expired' => true,
            ], 401);
        }

        return redirect()->route('login')->with('warning', $message);
    }
}
