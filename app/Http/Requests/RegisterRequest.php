<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida el formulario de registro de clientes.
 *
 * Los límites coinciden con las columnas de la tabla usuarios
 * (nombre 60, apellido 60, email 30, telefono 10).
 */
class RegisterRequest extends FormRequest
{
    /**
     * Cualquier visitante puede registrarse; el middleware guest ya impide
     * que lo haga alguien con sesión iniciada.
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
            'nombre' => 'required|string|max:60',
            'apellido' => 'required|string|max:60',
            'email' => 'required|email|max:30|unique:usuarios,email',
            'password' => 'required|string|min:6|confirmed',
            'telefono' => 'nullable|digits:10',
        ];
    }

    /**
     * Mensajes específicos del registro. Los demás salen de lang/es/validation.php.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Escribe un correo electrónico válido.',
            'email.max' => 'El correo electrónico no debe tener más de 30 caracteres.',
            'email.unique' => 'Ya existe una cuenta con ese correo electrónico.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'telefono.digits' => 'El teléfono debe tener exactamente 10 dígitos.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nombre' => 'nombre',
            'apellido' => 'apellido',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'telefono' => 'teléfono',
        ];
    }
}
