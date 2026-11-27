<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Cuentas para probar la aplicación: un administrador, una empleada, la cliente de prueba
 * y ocho clientes más con datos realistas (uno sin confirmar y uno desactivado),
 * para que la agenda, la búsqueda y los filtros tengan datos.
 */
class UserSeeder extends Seeder
{
    /**
     * Crea o actualiza las cuentas por su email. Todas usan la contraseña
     * Cliente123 salvo el administrador (Admin123) y la empleada (Empleado123).
     *
     * admin, empleado y confirmado están fuera de $fillable (para que un formulario no
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

        $clientes = [
            ['Camila', 'Rodríguez', 'camila.rodriguez@correo.com', '3104567890'],
            ['Andrés', 'Martínez', 'andres.martinez@correo.com', '3125551234'],
            ['Valentina', 'López', 'valentina.lopez@correo.com', '3156789012'],
            ['Santiago', 'Hernández', 'santi.hernandez@correo.com', '3001112233'],
            ['Daniela', 'Torres', 'daniela.torres@correo.com', '3017894561'],
            ['Mariana', 'Ramírez', 'mariana.ramirez@correo.com', '3204445566'],
            ['Julián', 'Castro', 'julian.castro@correo.com', '3189990011', 'confirmado' => false],
            ['Paula', 'Moreno', 'paula.moreno@correo.com', '3162223344', 'activo' => false],
        ];

        foreach ($clientes as $c) {
            $usuarios[] = [
                'nombre' => $c[0],
                'apellido' => $c[1],
                'email' => $c[2],
                'password' => 'Cliente123',
                'telefono' => $c[3],
                'admin' => false,
                'confirmado' => $c['confirmado'] ?? true,
                'activo' => $c['activo'] ?? true,
            ];
        }

        // La empleada va al final para no cambiar los ids de las cuentas anteriores.
        $usuarios[] = [
            'nombre' => 'Sofía',
            'apellido' => 'Vargas',
            'email' => 'empleado@appsalon.com',
            'password' => 'Empleado123',
            'telefono' => '3009876543',
            'admin' => false,
            'empleado' => true,
        ];

        User::unguarded(function () use ($usuarios) {
            foreach ($usuarios as $datos) {
                User::updateOrCreate(
                    ['email' => $datos['email']],
                    $datos + ['empleado' => false, 'confirmado' => true, 'activo' => true],
                );
            }
        });
    }
}
