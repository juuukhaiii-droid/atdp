<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\RedirectIfAuthenticated;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function ($middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            // Overrides the framework default, which falls back to
            // redirecting authenticated users hitting a guest route (e.g.
            // /login) to "/" - and "/" unconditionally redirects to /login,
            // causing an infinite redirect loop for anyone already logged
            // in. This sends them to their role's dashboard instead.
            'guest' => RedirectIfAuthenticated::class,
        ]);

        // Production sits behind Hostinger's edge/CDN (hcdn). Without this,
        // $request->ip() returns the edge server's IP instead of the real
        // visitor's, which silently breaks the office-WiFi check that
        // gates attendance check-in/out - it would reject everyone
        // regardless of their actual network. Hostinger doesn't publish a
        // fixed edge IP range, so trust the whole proxy chain and read the
        // standard forwarded headers.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
