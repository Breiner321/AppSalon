<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Genera servicios de prueba que respetan los límites de la tabla servicios
 * (nombre de 60 caracteres, precio DECIMAL(5,2) menor a 1000).
 *
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Estado por defecto: servicio activo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => ucfirst(fake()->unique()->words(3, true)),
            'precio' => fake()->randomFloat(2, 10, 300),
            'descripcion' => fake()->sentence(),
            'duracion' => fake()->randomElement([20, 30, 45, 60, 90, 120]),
            'activo' => true,
        ];
    }

    /**
     * Servicio desactivado (no aparece en el catálogo público).
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }
}
