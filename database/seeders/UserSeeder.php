<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Cuentas fijas para probar la aplicación: un administrador y un cliente.
 */
class UserSeeder extends Seeder
{
    /**
     * Crea o actualiza las dos cuentas por su email, ya confirmadas.
     *
     * admin y confirmado están fuera de $fillable (para que un formulario no
     * pueda cambiarlos), por eso la asignación se hace dentro de unguarded().
     * El cast 'hashed' del modelo aplica bcrypt a la contraseña.
     */
    public function run(): void
    {
        $usuarios = [
            [
                'nombre' => 'Admin',
                'apellido' => 'Salon',
                'email' => 'admin@appsalon.com',
                'password' => 'Admin123',
                'telefono' => '3001234567',
                'admin' => true,
            ],
            [
                'nombre' => 'Laura',
                'apellido' => 'Gomez',
                'email' => 'cliente@appsalon.com',
                'password' => 'Cliente123',
                'telefono' => '3007654321',
                'admin' => false,
            ],
        ];

        User::unguarded(function () use ($usuarios) {
            foreach ($usuarios as $datos) {
                User::updateOrCreate(
                    ['email' => $datos['email']],
                    $datos + ['confirmado' => true],
                );
            }
        });
    }
}
