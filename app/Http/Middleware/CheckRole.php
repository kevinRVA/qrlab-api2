<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * El parámetro $role puede ser un único rol ("admin") o varios separados
     * por pipe ("admin|coordinador") para permitir acceso compartido.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $allowedRoles = explode('|', $role);

        if (Auth::check() && !in_array(Auth::user()->role, $allowedRoles)) {

            // Redirigir al panel correspondiente al rol real del usuario
            $userRole = Auth::user()->role;

            if ($userRole === 'admin' || $userRole === 'coordinador') {
                return redirect('/admin');
            } elseif ($userRole === 'teacher') {
                return redirect('/docente');
            } else {
                return redirect('/perfil');
            }
        }

        // Si el rol es correcto (o está en la lista permitida), lo dejamos pasar
        return $next($request);
    }
}