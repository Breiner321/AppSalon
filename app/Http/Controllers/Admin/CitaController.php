<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\ReservaCitas;
use App\Support\HandlesDatabaseErrors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Citas en el panel: agenda del día o de la semana, listado con búsqueda y
 * filtros, y cambio de estado.
 */
class CitaController extends Controller
{
    use HandlesDatabaseErrors;

    /**
     * Estados a los que se puede pasar desde cada estado. Completada y
     * cancelada son finales.
     */
    public const TRANSICIONES = [
        Appointment::PENDIENTE => [Appointment::CONFIRMADA, Appointment::COMPLETADA, Appointment::CANCELADA],
        Appointment::CONFIRMADA => [Appointment::PENDIENTE, Appointment::COMPLETADA, Appointment::CANCELADA],
        Appointment::COMPLETADA => [],
        Appointment::CANCELADA => [],
    ];

    /**
     * Listado de citas con búsqueda por cliente (nombre, apellido o correo) y
     * filtros por estado y rango de fechas.
     */
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:60'],
            'estado' => ['nullable', Rule::in(Appointment::ESTADOS)],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
        ], [
            'hasta.after_or_equal' => 'La fecha "hasta" debe ser igual o posterior a la fecha "desde".',
        ]);

        $citas = Appointment::with(['usuario', 'servicios'])
            ->when($filtros['q'] ?? null, function ($q, string $texto) {
                $q->whereHas('usuario', function ($q) use ($texto) {
                    $q->where('nombre', 'like', '%'.$texto.'%')
                        ->orWhere('apellido', 'like', '%'.$texto.'%')
                        ->orWhere('email', 'like', '%'.$texto.'%')
                        ->orWhereRaw("CONCAT(nombre, ' ', apellido) LIKE ?", ['%'.$texto.'%']);
                });
            })
            ->when($filtros['estado'] ?? null, fn ($q, string $estado) => $q->where('estado', $estado))
            ->when($filtros['desde'] ?? null, fn ($q, string $desde) => $q->whereDate('fecha', '>=', $desde))
            ->when($filtros['hasta'] ?? null, fn ($q, string $hasta) => $q->whereDate('fecha', '<=', $hasta))
            ->orderByDesc('fecha')
            ->orderBy('hora')
            ->paginate(15)
            ->withQueryString();

        return view('admin.citas.index', compact('citas', 'filtros'));
    }

    /**
     * Agenda del día (vista=dia) o de la semana de lunes a domingo (vista=semana).
     */
    public function agenda(Request $request): View
    {
        $datos = $request->validate([
            'vista' => ['nullable', 'in:dia,semana'],
            'fecha' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $vista = $datos['vista'] ?? 'dia';
        $fecha = isset($datos['fecha']) ? Carbon::parse($datos['fecha']) : today();

        [$desde, $hasta] = $vista === 'semana'
            ? [$fecha->copy()->startOfWeek(Carbon::MONDAY), $fecha->copy()->endOfWeek(Carbon::SUNDAY)]
            : [$fecha->copy(), $fecha->copy()];

        $citas = Appointment::with(['usuario', 'servicios'])
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get();

        // Un grupo por día del rango, aunque el día no tenga citas.
        $dias = collect();
        for ($dia = $desde->copy(); $dia->lte($hasta); $dia->addDay()) {
            $dias->put($dia->toDateString(), $citas->filter(fn (Appointment $c) => $c->fecha->isSameDay($dia))->values());
        }

        $paso = $vista === 'semana' ? 7 : 1;

        return view('admin.citas.agenda', [
            'vista' => $vista,
            'fecha' => $fecha,
            'desde' => $desde,
            'hasta' => $hasta,
            'dias' => $dias,
            'anterior' => $fecha->copy()->subDays($paso)->toDateString(),
            'siguiente' => $fecha->copy()->addDays($paso)->toDateString(),
            'transiciones' => self::TRANSICIONES,
            'resumen' => [
                'total' => $citas->where('estado', '!=', Appointment::CANCELADA)->count(),
                'ingresos' => (float) $citas->where('estado', '!=', Appointment::CANCELADA)->sum('total'),
            ],
        ]);
    }

    /**
     * Cambia el estado de una cita y avisa al cliente por correo cuando se
     * confirma o se cancela.
     */
    public function cambiarEstado(Request $request, Appointment $cita, ReservaCitas $reservas): RedirectResponse
    {
        $permitidos = self::TRANSICIONES[$cita->estado] ?? [];

        $datos = $request->validate([
            'estado' => ['required', Rule::in($permitidos)],
        ], [
            'estado.in' => 'Una cita '.$cita->estado.' no puede pasar a ese estado.',
            'estado.required' => 'Elige el estado nuevo.',
        ]);

        $resultado = $this->runSafely(
            fn () => $reservas->cambiarEstado($cita, $datos['estado']),
            'No pudimos cambiar el estado de la cita. Intenta de nuevo.'
        );

        if ($resultado instanceof RedirectResponse) {
            return $resultado;
        }

        return back()->with('success', "Cita #{$cita->id} marcada como {$datos['estado']}");
    }
}
