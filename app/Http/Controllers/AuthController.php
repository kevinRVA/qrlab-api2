<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validamos que nos envíen correo y contraseña
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 2. Buscamos al usuario en la base de datos
        $user = User::where('email', $request->email)->first();

        // 3. Verificamos si existe y si la contraseña coincide
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas. Verifica tu correo y contraseña.'
            ], 401);
        }

        // 4. Creamos un Token de seguridad para la app móvil
        $token = $user->createToken('qrlab_mobile_token')->plainTextToken;

        // 5. Devolvemos el éxito, los datos del usuario y su token
        return response()->json([
            'message' => 'Login exitoso',
            'user' => $user,
            'token' => $token
        ], 200);
    }
}