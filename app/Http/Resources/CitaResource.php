<?php

namespace App\Http\Resources;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Cita en las respuestas JSON de la API.
 *
 * @mixin Appointment
 */
class CitaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fecha' => $this->fecha->toDateString(),
            'hora' => $this->horaCorta(),
            'horaFin' => $this->horaFin(),
            'duracion' => $this->duracion,
            'total' => (float) $this->total,
            'estado' => $this->estado,
            'modificable' => $this->puedeModificarse(),
            'usuarioId' => $this->usuarioId,
            'usuario' => new UsuarioResource($this->whenLoaded('usuario')),
            'servicios' => ServicioResource::collection($this->whenLoaded('servicios')),
            'creadaEl' => $this->created_at?->toIso8601String(),
        ];
    }
}
