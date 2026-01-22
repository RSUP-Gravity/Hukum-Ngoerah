<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
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
        // Configure Rate Limiting
        $this->configureRateLimiting();

        // Use Bootstrap pagination
        Paginator::useBootstrap();
        
        // Share global variables with views
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $view->with('unreadNotificationsCount', auth()->user()->unreadNotifications()->count());
            }
        });

        // Custom Blade directives
        Blade::directive('permission', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->hasPermission({$expression})): ?>";
        });

        Blade::directive('endpermission', function () {
            return "<?php endif; ?>";
        });

        Blade::directive('formatDate', function ($expression) {
            return "<?php echo ($expression) ? ($expression)->locale('id')->isoFormat('D MMMM Y') : '-'; ?>";
        });

        Blade::directive('formatDateTime', function ($expression) {
            return "<?php echo ($expression) ? ($expression)->locale('id')->isoFormat('D MMM Y HH:mm') : '-'; ?>";
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // General API rate limit (60 requests per minute)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Login rate limiting (5 attempts per minute)
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->input('username') . '|' . $request->ip())
                ->response(function (Request $request, array $headers) {
                    return back()
                        ->withErrors(['username' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . ceil($headers['Retry-After'] / 60) . ' menit.'])
                        ->withInput($request->only('username'));
                });
        });

        // Password reset rate limiting (3 attempts per hour)
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perHour(3)
                ->by($request->ip())
                ->response(function () {
                    return back()
                        ->withErrors(['error' => 'Terlalu banyak permintaan reset password. Silakan coba lagi nanti.']);
                });
        });

        // Document download rate limiting (100 downloads per hour)
        RateLimiter::for('downloads', function (Request $request) {
            return Limit::perHour(100)->by($request->user()?->id ?: $request->ip());
        });

        // Export rate limiting (20 exports per hour)
        RateLimiter::for('exports', function (Request $request) {
            return Limit::perHour(20)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return back()
                        ->with('error', 'Anda telah mencapai batas maksimum ekspor per jam. Silakan coba lagi nanti.');
                });
        });

        // Search rate limiting (30 searches per minute)
        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        // Admin actions rate limiting (30 actions per minute)
        RateLimiter::for('admin', function (Request $request) {
            return Limit::perMinute(30)
                ->by($request->user()?->id ?: $request->ip());
        });

        // Heavy operations rate limiting (10 per minute)
        RateLimiter::for('heavy', function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->user()?->id ?: $request->ip());
        });
    }
}
