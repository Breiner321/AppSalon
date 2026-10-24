<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * Genera usuarios de prueba que respetan los límites de la tabla usuarios.
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Hash de la contraseña 'password', calculado una sola vez.
     */
    protected static ?string $password;

    /**
     * Estado por defecto: cliente confirmado, no administrador.
     * El email tiene como máximo 30 caracteres (p. ej. usuario1234@example.com).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName(),
            'apellido' => fake()->lastName(),
            'email' => fake()->unique()->numerify('usuario####').'@example.com',
            'password' => static::$password ??= Hash::make('password'),
            'telefono' => fake()->numerify('##########'),
            'admin' => false,
            'confirmado' => true,
            'token' => null,
        ];
    }

    /**
     * Usuario con permisos de administrador.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'admin' => true,
        ]);
    }

    /**
     * Usuario que aún no confirma su cuenta.
     */
    public function unconfirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'confirmado' => false,
        ]);
    }
}
