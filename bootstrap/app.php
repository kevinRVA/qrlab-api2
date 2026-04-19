<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // ── Confiar en todos los proxies (Railway, Heroku, etc.) ──────────
        // Railway termina SSL en su proxy y nos envía el tráfico como HTTP,
        // pero incluye el header X-Forwarded-Proto: https.
        // Con esto Laravel lo detecta y genera URLs/formularios en HTTPS.
        $middleware->trustProxies(at: '*');

        // ── Alias de middleware personalizados ────────────────────────────
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        // Añadir a las rutas web para forzar sesión única
        $middleware->web(append: [
            \App\Http\Middleware\EnforceSingleSession::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();