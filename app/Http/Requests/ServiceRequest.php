<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida el formulario de servicios del panel (crear y editar).
 *
 * Los límites coinciden con la tabla servicios: nombre de 60 caracteres y
 * precio DECIMAL(5,2), es decir, como máximo 999.99.
 */
class ServiceRequest extends FormRequest
{
    /**
     * Solo llegan administradores: las rutas admin.servicios.* ya tienen
     * los middleware 'auth' y 'admin'.
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
            'precio' => 'required|numeric|min:0|max:999.99',
            'descripcion' => 'nullable|string|max:500',
            'duracion' => 'required|integer|min:10|max:480',
            'activo' => 'boolean',
        ];
    }

    /**
     * Mensajes específicos del formulario de servicios. Los demás salen de lang/es/validation.php.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del servicio es obligatorio.',
            'nombre.max' => 'El nombre no debe tener más de 60 caracteres.',
            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric' => 'El precio debe ser un número.',
            'precio.min' => 'El precio no puede ser negativo.',
            'precio.max' => 'El precio no puede ser mayor a $999.99.',
            'descripcion.max' => 'La descripción no debe tener más de 500 caracteres.',
            'duracion.required' => 'La duración es obligatoria.',
            'duracion.integer' => 'La duración debe ser un número entero de minutos.',
            'duracion.min' => 'La duración debe ser de al menos 10 minutos.',
            'duracion.max' => 'La duración no puede ser mayor a 480 minutos (8 horas).',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nombre' => 'nombre',
            'precio' => 'precio',
            'descripcion' => 'descripción',
            'duracion' => 'duración',
            'activo' => 'activo',
        ];
    }
}
