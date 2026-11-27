<?php

namespace App\Services;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Consultas de los reportes del panel (requerimiento 9).
 *
 * Todas reciben un período [desde, hasta] con las dos fechas incluidas y, de
 * forma opcional, el id de un servicio. Con servicio, solo cuentan las citas
 * que lo incluyen y los ingresos son solo los de ese servicio.
 *
 * Ingresos = lo cobrado en las citas COMPLETADAS. Sin filtro de servicio es la
 * suma del total de esas citas; por servicio se usa el precio guardado en
 * citasServicios al reservar (si una fila antigua no lo tiene, el precio actual).
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
    public function citas(Carbon $desde, Carbon $hasta, ?string $estado = null, ?int $servicio = null): Collection
    {
        return $this->citasDelPeriodo($desde, $hasta, $servicio)
            ->with(['usuario', 'servicios'])
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
    public function resumen(Carbon $desde, Carbon $hasta, ?int $servicio = null): array
    {
        $porEstado = $this->citasDelPeriodo($desde, $hasta, $servicio)
            ->selectRaw('estado, COUNT(*) AS cantidad, COALESCE(SUM(total), 0) AS suma')
            ->groupBy('estado')
            ->get()
            ->keyBy('estado');

        $cantidad = fn (string $estado) => (int) ($porEstado[$estado]->cantidad ?? 0);
        $total = (int) $porEstado->sum('cantidad');
        $completadas = $cantidad(Appointment::COMPLETADA);
        $ingresos = $servicio
            ? (float) $this->lineasCompletadas($desde, $hasta, $servicio)->sum(DB::raw('COALESCE(citasServicios.precio, servicios.precio)'))
            : (float) ($porEstado[Appointment::COMPLETADA]->suma ?? 0);

        return [
            'total' => $total,
            'pendiente' => $cantidad(Appointment::PENDIENTE),
            'confirmada' => $cantidad(Appointment::CONFIRMADA),
            'completada' => $completadas,
            'cancelada' => $cantidad(Appointment::CANCELADA),
            'ingresos' => round($ingresos, 2),
            'ticketPromedio' => $completadas > 0 ? round($ingresos / $completadas, 2) : 0.0,
            'clientes' => (int) $this->citasDelPeriodo($desde, $hasta, $servicio)
                ->where('estado', Appointment::COMPLETADA)
                ->distinct()
                ->count('usuarioId'),
            'tasaCancelacion' => $total > 0 ? round($cantidad(Appointment::CANCELADA) * 100 / $total, 1) : 0.0,
        ];
    }

    /**
     * Ingresos por servicio en las citas completadas del período, de mayor a menor.
     * Con servicio, devuelve solo la fila de ese servicio.
     *
     * @return list<array{servicio: string, veces: int, ingresos: float, porcentaje: float}>
     */
    public function ingresosPorServicio(Carbon $desde, Carbon $hasta, ?int $servicio = null): array
    {
        $filas = $this->lineasCompletadas($desde, $hasta, $servicio)
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
    public function porDia(Carbon $desde, Carbon $hasta, ?int $servicio = null): array
    {
        $datos = $this->citasDelPeriodo($desde, $hasta, $servicio)
            ->selectRaw('fecha, estado, COUNT(*) AS cantidad, COALESCE(SUM(total), 0) AS suma')
            ->groupBy('fecha', 'estado')
            ->get()
            ->groupBy(fn (Appointment $fila) => $fila->fecha->toDateString());

        // Con servicio, el ingreso del día es solo el de ese servicio.
        $ingresosServicio = $servicio
            ? $this->lineasCompletadas($desde, $hasta, $servicio)
                ->groupBy('citas.fecha')
                ->selectRaw('citas.fecha AS fecha, SUM(COALESCE(citasServicios.precio, servicios.precio)) AS ingresos')
                ->pluck('ingresos', 'fecha')
                ->mapWithKeys(fn ($valor, $fecha) => [Carbon::parse($fecha)->toDateString() => (float) $valor])
            : null;

        $dias = [];
        for ($dia = $desde->copy(); $dia->lte($hasta); $dia->addDay()) {
            $filas = $datos->get($dia->toDateString(), collect())->keyBy('estado');
            $fila = ['fecha' => $dia->toDateString()];

            foreach (Appointment::ESTADOS as $estado) {
                $fila[$estado] = (int) ($filas[$estado]->cantidad ?? 0);
            }
            $fila['ingresos'] = round($ingresosServicio
                ? (float) $ingresosServicio->get($dia->toDateString(), 0)
                : (float) ($filas[Appointment::COMPLETADA]->suma ?? 0), 2);

            $dias[] = $fila;
        }

        return $dias;
    }

    /**
     * Citas del período; con servicio, solo las que lo incluyen.
     *
     * @return Builder<Appointment>
     */
    private function citasDelPeriodo(Carbon $desde, Carbon $hasta, ?int $servicio): Builder
    {
        return Appointment::query()
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->when($servicio, fn ($q) => $q->whereHas('servicios', fn ($s) => $s->whereKey($servicio)));
    }

    /**
     * Una fila por servicio realizado en las citas completadas del período
     * (citasServicios unida a citas y servicios); con servicio, solo ese.
     */
    private function lineasCompletadas(Carbon $desde, Carbon $hasta, ?int $servicio): QueryBuilder
    {
        return DB::table('citasServicios')
            ->join('citas', 'citas.id', '=', 'citasServicios.citaId')
            ->join('servicios', 'servicios.id', '=', 'citasServicios.servicioId')
            ->whereBetween('citas.fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->where('citas.estado', Appointment::COMPLETADA)
            ->when($servicio, fn ($q) => $q->where('servicios.id', $servicio));
    }
}
