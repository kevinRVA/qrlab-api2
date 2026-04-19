<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garantiza sesión única por usuario.
 *
 * Al iniciar sesión se guarda un token único en la BD (users.session_token)
 * y también en la sesión activa del navegador (qrlab_session_token).
 *
 * En cada request autenticado se compara ambos tokens.
 * Si no coinciden significa que el usuario abrió sesión en otro dispositivo
 * y se cierra la sesión actual forzosamente.
 */
class EnforceSingleSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user           = Auth::user();
            $sessionToken   = $request->session()->get('qrlab_session_token');

            // Si el token de sesión no coincide con el de la BD → sesión inválida
            if ($user->session_token && $sessionToken !== $user->session_token) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/login')->withErrors([
                    'email' => 'Tu sesión fue cerrada porque iniciaste sesión en otro dispositivo.',
                ]);
            }
        }

        return $next($request);
    }
}
