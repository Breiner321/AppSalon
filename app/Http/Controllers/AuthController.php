<?php

namespace App\Http\Controllers;

/**
 * Registro, inicio y cierre de sesión de los clientes del salón.
 */
class AuthController extends Controller
{
    /**
     * Muestra el formulario de registro.
     */
    public function showRegister()
    {
        // TODO A3: devolver la vista auth.registro
        abort(501);
    }

    /**
     * Crea la cuenta de un cliente nuevo.
     */
    public function register()
    {
        // TODO A3: validar con RegisterRequest y crear el usuario
        abort(501);
    }

    /**
     * Muestra el formulario de inicio de sesión.
     */
    public function showLogin()
    {
        // TODO A3: devolver la vista auth.login
        abort(501);
    }

    /**
     * Autentica al usuario con email y contraseña.
     */
    public function login()
    {
        // TODO A4: validar con LoginRequest, Auth::attempt y regenerar la sesión
        abort(501);
    }

    /**
     * Cierra la sesión del usuario actual.
     */
    public function logout()
    {
        // TODO A4: Auth::logout, invalidar la sesión y regenerar el token CSRF
        abort(501);
    }
}
