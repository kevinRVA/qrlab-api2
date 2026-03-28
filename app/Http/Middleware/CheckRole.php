<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. Verificamos si el usuario no tiene el rol exigido para esa ruta
        if (Auth::check() && Auth::user()->role !== $role) {
            
            // 2. Si es un intruso, lo mandamos a su panel correspondiente
            $userRole = Auth::user()->role;
            
            if ($userRole === 'admin') {
                return redirect('/admin');
            } elseif ($userRole === 'teacher') {
                return redirect('/docente');
            } else {
                return redirect('/perfil');
            }
        }

        // Si el rol es correcto, lo dejamos pasar
        return $next($request);
    }
}