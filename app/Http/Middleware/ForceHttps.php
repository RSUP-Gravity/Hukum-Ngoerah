<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to redirect all HTTP requests to HTTPS.
 * 
 * Only active in production environment.
 */
class ForceHttps
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only force HTTPS in production
        if (config('app.env') === 'production') {
            // Check if request is not secure
            if (!$request->secure() && !$this->isLocalhost($request)) {
                // Redirect to HTTPS
                return redirect()->secure($request->getRequestUri(), 301);
            }
        }

        return $next($request);
    }

    /**
     * Check if the request is coming from localhost.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    private function isLocalhost(Request $request): bool
    {
        $localIps = ['127.0.0.1', '::1', 'localhost'];
        
        return in_array($request->ip(), $localIps);
    }
}
