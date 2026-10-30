<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Dos citas de ejemplo para la cuenta cliente, cada una con dos servicios.
 *
 * Depende de UserSeeder y ServiceSeeder, que deben correr antes.
 */
class AppointmentSeeder extends Seeder
{
    /**
     * Crea las citas y llena la tabla citasServicios. El total de cada cita es
     * la suma de los precios de sus servicios.
     */
    public function run(): void
    {
        $cliente = User::where('email', 'cliente@appsalon.com')->firstOrFail();

        $citas = [
            [
                'fecha' => now()->addDays(3)->toDateString(),
                'hora' => '10:00:00',
                'estado' => 'pendiente',
                'servicios' => ['Corte de cabello mujer', 'Manicure tradicional'],
            ],
            [
                'fecha' => now()->addDays(7)->toDateString(),
                'hora' => '15:30:00',
                'estado' => 'confirmada',
                'servicios' => ['Tinte completo', 'Pedicure spa'],
            ],
        ];

        foreach ($citas as $datos) {
            $servicios = Service::whereIn('nombre', $datos['servicios'])->get();

            $cita = Appointment::create([
                'fecha' => $datos['fecha'],
                'hora' => $datos['hora'],
                'usuarioId' => $cliente->id,
                'total' => $servicios->sum('precio'),
                'estado' => $datos['estado'],
            ]);

            $cita->servicios()->attach($servicios->pluck('id'));
        }
    }
}
