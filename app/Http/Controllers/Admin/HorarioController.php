<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Bloqueo;
use App\Models\Horario;
use App\Support\HandlesDatabaseErrors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;
use Illuminate\View\View;

/**
 * Disponibilidad horaria del salón: horario de cada día de la semana y fechas
 * bloqueadas. El calendario de reservas y la validación de citas leen estos datos.
 *
 * Cambiar el horario no borra ni mueve las citas que ya existen.
 */
class HorarioController extends Controller
{
    use HandlesDatabaseErrors;

    /**
     * Formulario del horario semanal y listado de fechas bloqueadas futuras.
     */
    public function index(): View
    {
        $guardados = Horario::orderBy('diaSemana')->get()->keyBy('diaSemana');

        // Lunes primero; si falta un día, se muestra cerrado.
        $horarios = collect([1, 2, 3, 4, 5, 6, 0])->map(fn (int $dia) => $guardados->get($dia)
            ?? new Horario(['diaSemana' => $dia, 'apertura' => '09:00:00', 'cierre' => '18:00:00', 'abierto' => false]));

        $bloqueos = Bloqueo::whereDate('fecha', '>=', today())->orderBy('fecha')->get();

        return view('admin.horarios.index', compact('horarios', 'bloqueos'));
    }

    /**
     * Guarda el horario de los siete días. Si un día está abierto, el cierre
     * debe ser posterior a la apertura.
     */
    public function update(Request $request): RedirectResponse
    {
        $validador = validator($request->all(), [
            'horarios' => ['required', 'array', 'size:7'],
            'horarios.*.abierto' => ['boolean'],
            'horarios.*.apertura' => ['required', 'date_format:H:i'],
            'horarios.*.cierre' => ['required', 'date_format:H:i'],
        ], [
            'horarios.*.apertura.required' => 'Escribe la hora de apertura.',
            'horarios.*.cierre.required' => 'Escribe la hora de cierre.',
            'horarios.*.apertura.date_format' => 'La hora debe tener el formato HH:MM.',
            'horarios.*.cierre.date_format' => 'La hora debe tener el formato HH:MM.',
        ]);

        $validador->after(function (Validator $v) use ($request) {
            foreach ((array) $request->input('horarios', []) as $dia => $h) {
                if (! empty($h['abierto']) && isset($h['apertura'], $h['cierre']) && $h['cierre'] <= $h['apertura']) {
                    $v->errors()->add("horarios.$dia.cierre", 'El cierre debe ser después de la apertura.');
                }
            }
        });

        $datos = $validador->validate();

        foreach (array_keys($datos['horarios']) as $dia) {
            if (! is_int($dia) || $dia < 0 || $dia > 6) {
                return back()->withInput()->with('error', 'Los días de la semana no son válidos.');
            }
        }

        $resultado = $this->runSafely(function () use ($datos) {
            foreach ($datos['horarios'] as $dia => $h) {
                Horario::updateOrCreate(['diaSemana' => $dia], [
                    'apertura' => $h['apertura'].':00',
                    'cierre' => $h['cierre'].':00',
                    'abierto' => (bool) ($h['abierto'] ?? false),
                ]);
            }

            return true;
        }, 'No pudimos guardar el horario. Intenta de nuevo.');

        if ($resultado instanceof RedirectResponse) {
            return $resultado;
        }

        return redirect()->route('admin.horarios.index')->with('success', 'Horario actualizado');
    }

    /**
     * Bloquea una fecha (el salón no atiende ese día).
     */
    public function storeBloqueo(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'fecha' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'unique:bloqueos,fecha'],
            'motivo' => ['nullable', 'string', 'max:100'],
        ], [
            'fecha.required' => 'Elige la fecha que quieres bloquear.',
            'fecha.after_or_equal' => 'Solo se pueden bloquear fechas de hoy en adelante.',
            'fecha.unique' => 'Esa fecha ya está bloqueada.',
            'motivo.max' => 'El motivo no debe tener más de 100 caracteres.',
        ]);

        $resultado = $this->runSafely(
            fn () => Bloqueo::create($datos),
            'No pudimos bloquear la fecha. Intenta de nuevo.'
        );

        if ($resultado instanceof RedirectResponse) {
            return $resultado;
        }

        $mensaje = 'Fecha bloqueada: '.$resultado->fecha->translatedFormat('l j \d\e F');
        $citas = Appointment::ocupan()->whereDate('fecha', $datos['fecha'])->count();

        return redirect()->route('admin.horarios.index')
            ->with('success', $mensaje)
            ->with($citas ? 'warning' : 'info', $citas
                ? "Ese día ya hay {$citas} cita(s) reservada(s). Revísalas en la agenda: el bloqueo no las cancela."
                : 'Los clientes ya no podrán reservar ese día.');
    }

    /**
     * Quita el bloqueo de una fecha.
     */
    public function destroyBloqueo(Bloqueo $bloqueo): RedirectResponse
    {
        $resultado = $this->runSafely(fn () => $bloqueo->delete(), 'No pudimos quitar el bloqueo. Intenta de nuevo.');

        if ($resultado instanceof RedirectResponse) {
            return $resultado;
        }

        return redirect()->route('admin.horarios.index')->with('success', 'Bloqueo eliminado');
    }
}
