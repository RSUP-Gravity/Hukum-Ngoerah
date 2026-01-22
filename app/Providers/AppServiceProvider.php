<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
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
}
