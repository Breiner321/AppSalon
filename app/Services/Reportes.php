<?php

namespace App\Services;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Consultas de los reportes del panel (requerimiento 9).
 *
 * Todas reciben un período [desde, hasta] con las dos fechas incluidas.
 * Ingresos = suma del total de las citas COMPLETADAS (lo que el salón cobró).
 * El ingreso por servicio usa el precio guardado en citasServicios al reservar;
 * si una fila antigua no lo tiene, toma el precio actual del servicio.
 *
 * Las usan Admin\ReporteController (pantalla, PDF y CSV) y sus pruebas.
 */
class Reportes
{
    /**
     * Citas del período con su cliente y servicios, en orden cronológico.
     * Si se indica un estado, solo las de ese estado.
     *
     * @return Collection<int, Appointment>
     */
    public function citas(Carbon $desde, Carbon $hasta, ?string $estado = null): Collection
    {
        return Appointment::with(['usuario', 'servicios'])
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get();
    }

    /**
     * Totales del período.
     *
     * @return array{total: int, pendiente: int, confirmada: int, completada: int, cancelada: int, ingresos: float, ticketPromedio: float, clientes: int, tasaCancelacion: float}
     */
    public function resumen(Carbon $desde, Carbon $hasta): array
    {
        $porEstado = Appointment::query()
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->selectRaw('estado, COUNT(*) AS cantidad, COALESCE(SUM(total), 0) AS suma')
            ->groupBy('estado')
            ->get()
            ->keyBy('estado');

        $cantidad = fn (string $estado) => (int) ($porEstado[$estado]->cantidad ?? 0);
        $total = (int) $porEstado->sum('cantidad');
        $completadas = $cantidad(Appointment::COMPLETADA);
        $ingresos = (float) ($porEstado[Appointment::COMPLETADA]->suma ?? 0);

        return [
            'total' => $total,
            'pendiente' => $cantidad(Appointment::PENDIENTE),
            'confirmada' => $cantidad(Appointment::CONFIRMADA),
            'completada' => $completadas,
            'cancelada' => $cantidad(Appointment::CANCELADA),
            'ingresos' => round($ingresos, 2),
            'ticketPromedio' => $completadas > 0 ? round($ingresos / $completadas, 2) : 0.0,
            'clientes' => (int) Appointment::query()
                ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
                ->where('estado', Appointment::COMPLETADA)
                ->distinct()
                ->count('usuarioId'),
            'tasaCancelacion' => $total > 0 ? round($cantidad(Appointment::CANCELADA) * 100 / $total, 1) : 0.0,
        ];
    }

    /**
     * Ingresos por servicio en las citas completadas del período, de mayor a menor.
     *
     * @return list<array{servicio: string, veces: int, ingresos: float, porcentaje: float}>
     */
    public function ingresosPorServicio(Carbon $desde, Carbon $hasta): array
    {
        $filas = DB::table('citasServicios')
            ->join('citas', 'citas.id', '=', 'citasServicios.citaId')
            ->join('servicios', 'servicios.id', '=', 'citasServicios.servicioId')
            ->whereBetween('citas.fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->where('citas.estado', Appointment::COMPLETADA)
            ->groupBy('servicios.id', 'servicios.nombre')
            ->selectRaw('servicios.nombre AS servicio, COUNT(*) AS veces, SUM(COALESCE(citasServicios.precio, servicios.precio)) AS ingresos')
            ->orderByDesc('ingresos')
            ->orderBy('servicios.nombre')
            ->get();

        $totalIngresos = (float) $filas->sum('ingresos');

        return $filas->map(fn ($f) => [
            'servicio' => $f->servicio,
            'veces' => (int) $f->veces,
            'ingresos' => round((float) $f->ingresos, 2),
            'porcentaje' => $totalIngresos > 0 ? round((float) $f->ingresos * 100 / $totalIngresos, 1) : 0.0,
        ])->values()->all();
    }

    /**
     * Una fila por día del período (también los días sin citas) con las citas
     * de cada estado y los ingresos. Alimenta las gráficas.
     *
     * @return list<array{fecha: string, pendiente: int, confirmada: int, completada: int, cancelada: int, ingresos: float}>
     */
    public function porDia(Carbon $desde, Carbon $hasta): array
    {
        $datos = Appointment::query()
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->selectRaw('fecha, estado, COUNT(*) AS cantidad, COALESCE(SUM(total), 0) AS suma')
            ->groupBy('fecha', 'estado')
            ->get()
            ->groupBy(fn (Appointment $fila) => $fila->fecha->toDateString());

        $dias = [];
        for ($dia = $desde->copy(); $dia->lte($hasta); $dia->addDay()) {
            $filas = $datos->get($dia->toDateString(), collect())->keyBy('estado');
            $fila = ['fecha' => $dia->toDateString()];

            foreach (Appointment::ESTADOS as $estado) {
                $fila[$estado] = (int) ($filas[$estado]->cantidad ?? 0);
            }
            $fila['ingresos'] = round((float) ($filas[Appointment::COMPLETADA]->suma ?? 0), 2);

            $dias[] = $fila;
        }

        return $dias;
    }
}
