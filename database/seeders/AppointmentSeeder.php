<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use App\Services\Disponibilidad;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Citas de prueba realistas: las últimas seis semanas y las próximas tres.
 *
 * - Dos citas fijas para la cuenta cliente@appsalon.com (una pendiente y una
 *   confirmada), para probar modificar y cancelar.
 * - Entre 2 y 5 citas por día de atención, de los clientes activos, con 1 a 3
 *   servicios. Las pasadas quedan completadas (algunas canceladas) y las
 *   futuras pendientes o confirmadas.
 *
 * Las horas se toman de Disponibilidad::turnosLibres, así que respetan el
 * horario del salón y nunca se sobreponen. mt_srand fija la semilla: cada vez
 * que se corre el seeder salen las mismas citas (relativas a la fecha de hoy).
 *
 * Depende de UserSeeder, ServiceSeeder y HorarioSeeder.
 */
class AppointmentSeeder extends Seeder
{
    public function run(Disponibilidad $disponibilidad): void
    {
        mt_srand(2026);

        $servicios = Service::where('activo', true)->get();
        $laura = User::where('email', 'cliente@appsalon.com')->firstOrFail();

        // Citas fijas de la cliente de prueba.
        $fijas = [
            [3, '10:00', 'pendiente', ['Corte de cabello mujer', 'Manicure tradicional']],
            [7, '15:30', 'confirmada', ['Tinte completo']],
        ];

        foreach ($fijas as [$dias, $hora, $estado, $nombres]) {
            $fecha = $this->siguienteDiaHabil(today()->addDays($dias), $disponibilidad);
            $this->crearCita($fecha, $hora, $laura, $servicios->whereIn('nombre', $nombres), $estado);
        }

        // Citas generadas.
        $clientes = User::clientes()->where('confirmado', true)->get();

        for ($fecha = today()->subWeeks(6); $fecha->lte(today()->addWeeks(3)); $fecha = $fecha->copy()->addDay()) {
            if (! $disponibilidad->horarioDe($fecha)) {
                continue;
            }

            $cantidad = mt_rand(2, 5);

            for ($i = 0; $i < $cantidad; $i++) {
                $elegidos = $servicios->random(mt_rand(1, 3));
                $turnos = $disponibilidad->turnosLibres($fecha, (int) $elegidos->sum('duracion'), null, false);

                if (empty($turnos)) {
                    continue;
                }

                $hora = $turnos[mt_rand(0, count($turnos) - 1)];
                $cliente = $clientes[mt_rand(0, $clientes->count() - 1)];

                // Un cliente desactivado solo tiene citas pasadas.
                if (! $cliente->isActivo() && $fecha->isFuture()) {
                    $cliente = $clientes->firstWhere('activo', true);
                }

                $this->crearCita($fecha, $hora, $cliente, $elegidos, $this->estadoPara($fecha, $hora));
            }
        }
    }

    /**
     * Estado según si la cita ya pasó: completada (o cancelada) en el pasado,
     * pendiente o confirmada (o cancelada) en el futuro.
     */
    private function estadoPara(Carbon $fecha, string $hora): string
    {
        $azar = mt_rand(1, 100);

        if ($fecha->copy()->setTimeFromTimeString($hora)->isPast()) {
            return $azar <= 88 ? Appointment::COMPLETADA : Appointment::CANCELADA;
        }

        return match (true) {
            $azar <= 45 => Appointment::CONFIRMADA,
            $azar <= 92 => Appointment::PENDIENTE,
            default => Appointment::CANCELADA,
        };
    }

    /**
     * La misma fecha o la siguiente en que el salón atiende.
     */
    private function siguienteDiaHabil(Carbon $fecha, Disponibilidad $disponibilidad): Carbon
    {
        while (! $disponibilidad->horarioDe($fecha) || $fecha->isSaturday()) {
            $fecha->addDay();
        }

        return $fecha;
    }

    /**
     * Guarda la cita y sus servicios. El total y la duración son la suma de los servicios.
     *
     * @param  Collection<int, Service>  $servicios
     */
    private function crearCita(Carbon $fecha, string $hora, User $cliente, $servicios, string $estado): void
    {
        $cita = Appointment::create([
            'fecha' => $fecha->toDateString(),
            'hora' => $hora,
            'duracion' => $servicios->sum('duracion'),
            'usuarioId' => $cliente->id,
            'total' => $servicios->sum('precio'),
            'estado' => $estado,
        ]);

        // La reserva se hizo unos días antes de la cita.
        $cita->forceFill(['created_at' => $fecha->copy()->subDays(mt_rand(1, 10))->setTime(mt_rand(8, 20), mt_rand(0, 59))])->save();

        $cita->asignarServicios($servicios);
    }
}
