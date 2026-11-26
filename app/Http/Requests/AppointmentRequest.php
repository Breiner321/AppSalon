<?php

namespace App\Http\Requests;

use App\Models\Appointment;
use App\Models\Service;
use App\Services\Disponibilidad;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Valida la reserva y la modificación de una cita, en la web y en la API.
 *
 * Primero revisa el formato (fecha, hora y lista de servicios) y después, si
 * todo está bien, la disponibilidad: que el salón atienda ese día, que la cita
 * quede dentro del horario y que no se sobreponga con otra (Disponibilidad).
 *
 * Campos: fecha (AAAA-MM-DD), hora (HH:MM) y servicios (lista de ids).
 */
class AppointmentRequest extends FormRequest
{
    /**
     * Cualquier usuario con sesión puede reservar. Que la cita a modificar sea
     * suya lo revisa AppointmentPolicy en el controlador.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Acepta también la hora con segundos (10:00:00) y un solo id de servicio.
     */
    protected function prepareForValidation(): void
    {
        $hora = $this->input('hora');

        if (is_string($hora) && preg_match('/^\d{2}:\d{2}:00$/', $hora)) {
            $this->merge(['hora' => substr($hora, 0, 5)]);
        }

        if ($this->has('servicios') && ! is_array($this->input('servicios'))) {
            $this->merge(['servicios' => [$this->input('servicios')]]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $actuales = $this->citaActual()?->servicios()->pluck('servicios.id')->all() ?? [];

        return [
            'fecha' => [
                'bail',
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
                'before_or_equal:'.app(Disponibilidad::class)->ultimoDiaReservable()->toDateString(),
            ],
            'hora' => ['required', 'date_format:H:i'],
            'servicios' => ['required', 'array', 'min:1', 'max:10'],
            'servicios.*' => [
                'integer',
                'distinct',
                // Servicios activos; al modificar, también los que la cita ya tenía.
                Rule::exists('servicios', 'id')->where(
                    fn ($q) => $q->where('activo', true)->orWhereIn('id', $actuales)
                ),
            ],
        ];
    }

    /**
     * Revisión de disponibilidad, solo si el formato es correcto.
     *
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $motivo = app(Disponibilidad::class)->motivoNoDisponible(
                    Carbon::parse($this->input('fecha')),
                    $this->input('hora'),
                    $this->duracionTotal(),
                    $this->citaActual()?->id,
                );

                if ($motivo) {
                    $validator->errors()->add('hora', $motivo);
                }
            },
        ];
    }

    /**
     * Suma de la duración de los servicios elegidos, en minutos.
     */
    public function duracionTotal(): int
    {
        return (int) Service::whereIn('id', (array) $this->input('servicios', []))->sum('duracion');
    }

    /**
     * Datos listos para ReservaCitas.
     *
     * @return array{fecha: string, hora: string, servicios: list<int>}
     */
    public function datosCita(): array
    {
        $datos = $this->validated();

        return [
            'fecha' => $datos['fecha'],
            'hora' => $datos['hora'],
            'servicios' => array_map('intval', $datos['servicios']),
        ];
    }

    /**
     * Cita que se está modificando (rutas con {cita}); null al reservar.
     */
    private function citaActual(): ?Appointment
    {
        $cita = $this->route('cita');

        return $cita instanceof Appointment ? $cita : null;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'fecha.required' => 'Elige la fecha de la cita.',
            'fecha.date_format' => 'La fecha debe tener el formato AAAA-MM-DD.',
            'fecha.after_or_equal' => 'La fecha no puede ser anterior a hoy.',
            'fecha.before_or_equal' => 'Solo se puede reservar hasta con '.config('salon.dias_maximos').' días de anticipación.',
            'hora.required' => 'Elige la hora de la cita.',
            'hora.date_format' => 'La hora debe tener el formato HH:MM.',
            'servicios.required' => 'Elige al menos un servicio.',
            'servicios.min' => 'Elige al menos un servicio.',
            'servicios.max' => 'Puedes elegir como máximo 10 servicios por cita.',
            'servicios.*.integer' => 'Uno de los servicios elegidos no es válido.',
            'servicios.*.distinct' => 'Elegiste el mismo servicio dos veces.',
            'servicios.*.exists' => 'Uno de los servicios elegidos no existe o ya no está disponible.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'fecha' => 'fecha',
            'hora' => 'hora',
            'servicios' => 'servicios',
        ];
    }
}
