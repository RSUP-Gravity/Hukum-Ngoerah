<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        // Check document expiry daily at 07:00 WIB (UTC+8)
        $schedule->command('documents:check-expiry')
            ->dailyAt('07:00')
            ->timezone('Asia/Makassar')
            ->withoutOverlapping()
            ->runInBackground();

        // Clean old read notifications weekly on Sunday at 02:00
        $schedule->command('notifications:clean --days=30')
            ->weeklyOn(0, '02:00')
            ->timezone('Asia/Makassar')
            ->withoutOverlapping();

        // Clean temp watermarked files daily at 03:00
        $schedule->command('temp:cleanup')
            ->dailyAt('03:00')
            ->timezone('Asia/Makassar')
            ->withoutOverlapping();
    })
    ->withMiddleware(function (Middleware $middleware) {
        // Global middleware (runs on every request)
        $middleware->prepend(\App\Http\Middleware\ForceHttps::class);
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Web middleware group additions
        $middleware->web(append: [
            \App\Http\Middleware\SanitizeInput::class,
            \App\Http\Middleware\CheckActiveUser::class,
            \App\Http\Middleware\SessionTimeout::class,
            \App\Http\Middleware\RequirePasswordChange::class,
        ]);

        // Configure rate limiting
        $middleware->throttleWithRedis();

        // Register middleware aliases
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'active' => \App\Http\Middleware\CheckActiveUser::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
