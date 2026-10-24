<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida el formulario de inicio de sesión. La comprobación de las
 * credenciales contra la base de datos se hace en AuthController (A4).
 */
class LoginRequest extends FormRequest
{
    /**
     * Cualquier visitante puede intentar iniciar sesión.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Escribe un correo electrónico válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email' => 'correo electrónico',
            'password' => 'contraseña',
        ];
    }
}
