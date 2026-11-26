<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppointmentRequest;
use App\Models\Appointment;
use App\Models\Service;
use App\Services\Disponibilidad;
use App\Services\ReservaCitas;
use App\Support\HandlesDatabaseErrors;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Citas del cliente: reservar con el calendario, ver, modificar y cancelar.
 *
 * Las reglas de disponibilidad están en App\Services\Disponibilidad y las
 * escrituras en App\Services\ReservaCitas (las mismas que usa la API).
 */
class AppointmentController extends Controller
{
    use HandlesDatabaseErrors;

    public function __construct(
        private Disponibilidad $disponibilidad,
        private ReservaCitas $reservas,
    ) {
        //
    }

    /**
     * Mis citas: las próximas primero y luego el historial.
     */
    public function index(Request $request): View
    {
        $citas = $request->user()->citas()->with('servicios')->orderByDesc('fecha')->orderByDesc('hora')->get();

        [$proximas, $historial] = $citas->partition(
            fn (Appointment $cita) => $cita->estado !== Appointment::CANCELADA && $cita->fin()->isFuture()
        );

        return view('appointments.index', [
            'proximas' => $proximas->sortBy(fn (Appointment $c) => $c->inicio())->values(),
            'historial' => $historial->values(),
        ]);
    }

    /**
     * Formulario de reserva con el calendario interactivo.
     */
    public function create(): View
    {
        return view('appointments.form', [
            'cita' => new Appointment,
            'servicios' => Service::where('activo', true)->orderBy('nombre')->get(),
            'calendario' => $this->disponibilidad->datosCalendario(),
            'seleccionados' => [],
        ]);
    }

    /**
     * Guarda la cita nueva y envía el correo de confirmación.
     */
    public function store(AppointmentRequest $request): RedirectResponse
    {
        $resultado = $this->runSafely(
            fn () => $this->reservas->crear($request->user(), $request->datosCita()),
            'No pudimos reservar tu cita. Intenta de nuevo.'
        );

        if ($resultado instanceof RedirectResponse) {
            return $resultado;
        }

        return redirect()
            ->route('citas.index')
            ->with('success', 'Cita reservada para el '.$resultado->fecha->translatedFormat('j \d\e F').' a las '.$resultado->horaCorta().'. Te enviamos un correo con el detalle.');
    }

    /**
     * Formulario para cambiar fecha, hora o servicios de una cita propia.
     */
    public function edit(Appointment $cita): View|RedirectResponse
    {
        Gate::authorize('update', $cita);

        if (! $cita->puedeModificarse()) {
            return redirect()->route('citas.index')->with('warning', 'Esta cita ya no se puede modificar.');
        }

        $seleccionados = $cita->servicios()->pluck('servicios.id')->all();

        return view('appointments.form', [
            'cita' => $cita,
            // Servicios activos y los que la cita ya tenía aunque se hayan desactivado.
            'servicios' => Service::where('activo', true)->orWhereIn('id', $seleccionados)->orderBy('nombre')->get(),
            'calendario' => $this->disponibilidad->datosCalendario(),
            'seleccionados' => $seleccionados,
        ]);
    }

    /**
     * Guarda los cambios. La cita vuelve a quedar pendiente de confirmación.
     */
    public function update(AppointmentRequest $request, Appointment $cita): RedirectResponse
    {
        Gate::authorize('update', $cita);

        if (! $cita->puedeModificarse()) {
            return redirect()->route('citas.index')->with('warning', 'Esta cita ya no se puede modificar.');
        }

        $resultado = $this->runSafely(
            fn () => $this->reservas->actualizar($cita, $request->datosCita()),
            'No pudimos guardar los cambios de tu cita. Intenta de nuevo.'
        );

        if ($resultado instanceof RedirectResponse) {
            return $resultado;
        }

        return redirect()->route('citas.index')->with('success', 'Cita actualizada. El salón la confirmará de nuevo.');
    }

    /**
     * Cancela una cita propia que todavía no empieza.
     */
    public function cancelar(Appointment $cita): RedirectResponse
    {
        Gate::authorize('cancel', $cita);

        if (! $cita->puedeModificarse()) {
            return redirect()->route('citas.index')->with('warning', 'Esta cita ya no se puede cancelar.');
        }

        $resultado = $this->runSafely(
            fn () => $this->reservas->cancelar($cita),
            'No pudimos cancelar tu cita. Intenta de nuevo.'
        );

        if ($resultado instanceof RedirectResponse) {
            return $resultado;
        }

        return redirect()->route('citas.index')->with('success', 'Cita cancelada');
    }

    /**
     * Horas libres de un día (JSON) para el calendario.
     *
     * GET /citas/disponibilidad?fecha=2026-12-01&servicios[]=1&servicios[]=4&cita=7
     * El parámetro cita, opcional, es la cita que se está modificando: su
     * propio horario cuenta como libre.
     */
    public function disponibilidad(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'fecha' => ['required', 'date_format:Y-m-d'],
            'servicios' => ['nullable', 'array'],
            'servicios.*' => ['integer'],
            'cita' => ['nullable', 'integer'],
        ]);

        $ignorar = null;
        if (! empty($datos['cita'])) {
            $cita = Appointment::find($datos['cita']);
            $ignorar = $cita && Gate::allows('update', $cita) ? $cita->id : null;
        }

        return response()->json(
            $this->disponibilidad->resumenDia(Carbon::parse($datos['fecha']), $datos['servicios'] ?? [], $ignorar)
        );
    }
}
