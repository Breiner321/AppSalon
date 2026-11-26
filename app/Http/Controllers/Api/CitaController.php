<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AppointmentRequest;
use App\Http\Resources\CitaResource;
use App\Models\Appointment;
use App\Models\User;
use App\Services\Disponibilidad;
use App\Services\ReservaCitas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

/**
 * Citas en la API. Todas las rutas piden un token de Sanctum.
 *
 * Usa las mismas reglas que la web: AppointmentRequest valida y
 * ReservaCitas guarda (con la revisión de sobreposición dentro de la transacción).
 */
class CitaController extends Controller
{
    public function __construct(private ReservaCitas $reservas)
    {
        //
    }

    /**
     * GET /api/citas/usuario/{id}: citas de un usuario. Un cliente solo puede
     * pedir las suyas; un administrador, las de cualquiera.
     * Filtro opcional ?estado=pendiente|confirmada|completada|cancelada.
     */
    public function porUsuario(Request $request, User $usuario): JsonResponse
    {
        if (! $request->user()->isAdmin() && ! $request->user()->is($usuario)) {
            return response()->json(['message' => 'Solo puedes consultar tus propias citas.'], 403);
        }

        $request->validate(['estado' => ['nullable', 'in:'.implode(',', Appointment::ESTADOS)]]);

        $citas = $usuario->citas()
            ->with('servicios')
            ->when($request->query('estado'), fn ($q, $estado) => $q->where('estado', $estado))
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->get();

        return response()->json([
            'data' => CitaResource::collection($citas),
            'meta' => ['usuarioId' => $usuario->id, 'total' => $citas->count()],
        ]);
    }

    /**
     * GET /api/citas/{id}: detalle de una cita.
     */
    public function show(Appointment $cita): JsonResponse
    {
        Gate::authorize('view', $cita);

        return response()->json(['data' => new CitaResource($cita->load(['servicios', 'usuario']))]);
    }

    /**
     * POST /api/citas: reserva una cita para el dueño del token.
     */
    public function store(AppointmentRequest $request): JsonResponse
    {
        $cita = $this->reservas->crear($request->user(), $request->datosCita());

        return response()->json([
            'message' => 'Cita reservada',
            'data' => new CitaResource($cita->load('servicios')),
        ], 201);
    }

    /**
     * PUT /api/citas/{id}: cambia fecha, hora y servicios. La cita vuelve a
     * quedar pendiente de confirmación.
     */
    public function update(AppointmentRequest $request, Appointment $cita): JsonResponse
    {
        Gate::authorize('update', $cita);

        if (! $cita->puedeModificarse()) {
            return response()->json(['message' => 'Esta cita ya no se puede modificar.'], 409);
        }

        $cita = $this->reservas->actualizar($cita, $request->datosCita());

        return response()->json([
            'message' => 'Cita actualizada',
            'data' => new CitaResource($cita->load('servicios')),
        ]);
    }

    /**
     * PATCH /api/citas/{id}/cancelar: cancela la cita y libera su horario.
     */
    public function cancelar(Appointment $cita): JsonResponse
    {
        Gate::authorize('cancel', $cita);

        if (! $cita->puedeModificarse()) {
            return response()->json(['message' => 'Esta cita ya no se puede cancelar.'], 409);
        }

        $cita = $this->reservas->cancelar($cita);

        return response()->json([
            'message' => 'Cita cancelada',
            'data' => new CitaResource($cita->load('servicios')),
        ]);
    }

    /**
     * GET /api/disponibilidad?fecha=AAAA-MM-DD&servicios[]=1: horas libres de un día.
     */
    public function disponibilidad(Request $request, Disponibilidad $disponibilidad): JsonResponse
    {
        $datos = $request->validate([
            'fecha' => ['required', 'date_format:Y-m-d'],
            'servicios' => ['required', 'array', 'min:1'],
            'servicios.*' => ['integer'],
        ], [
            'fecha.required' => 'Indica la fecha (AAAA-MM-DD).',
            'servicios.required' => 'Indica al menos un servicio.',
        ]);

        return response()->json([
            'data' => $disponibilidad->resumenDia(Carbon::parse($datos['fecha']), $datos['servicios']),
        ]);
    }
}
