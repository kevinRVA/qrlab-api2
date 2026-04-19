<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Evita que el navegador almacene en caché las páginas autenticadas.
 * Así, al presionar "Atrás" después de cerrar sesión, el navegador
 * solicita la página de nuevo al servidor en vez de mostrar la copia cacheada.
 *
 * Nota: StreamedResponse (response()->stream()) no tiene withHeaders(), por lo
 * que los headers se aplican directamente sobre el objeto Symfony en ese caso.
 */
class NoCacheHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // StreamedResponse no tiene el helper withHeaders() de Laravel;
        // usamos la bolsa de headers de Symfony directamente.
        if ($response instanceof StreamedResponse) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma',        'no-cache');
            $response->headers->set('Expires',       'Sat, 01 Jan 2000 00:00:00 GMT');
            return $response;
        }

        return $response->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
            'Expires'       => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ]);
    }
}
