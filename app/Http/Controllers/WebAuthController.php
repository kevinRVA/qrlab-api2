<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebAuthController extends Controller
{
    // Muestra la vista del Login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Procesa las credenciales
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            // --- Control de Sesión Única ---
            $token = \Illuminate\Support\Str::random(60);
            $user->session_token = $token;
            $user->save();
            $request->session()->put('qrlab_session_token', $token);
            // -------------------------------

            // Redirección según el Rol
            if ($user->role === 'admin' || $user->role === 'coordinador') {
                return redirect()->intended('/admin');
            } 
            
            if ($user->role === 'teacher') {
                return redirect()->intended('/docente');
            }

            // Si es estudiante, intended() lo mandará automáticamente al QR que escaneó.
            // Si entró directo a la web sin escanear nada, lo mandamos a un perfil básico.
            return redirect()->intended('/perfil'); 
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    // Cierra la sesión
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}