<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServicioResource;
use App\Models\Service;
use Illuminate\Http\JsonResponse;

/**
 * Catálogo de servicios en la API. Es público, como el catálogo web.
 */
class ServicioController extends Controller
{
    /**
     * GET /api/servicios: servicios activos en orden alfabético.
     */
    public function index(): JsonResponse
    {
        $servicios = Service::where('activo', true)->orderBy('nombre')->get();

        return response()->json([
            'data' => ServicioResource::collection($servicios),
            'meta' => ['total' => $servicios->count()],
        ]);
    }

    /**
     * GET /api/servicios/{id}: un servicio activo.
     */
    public function show(int $id): JsonResponse
    {
        $servicio = Service::where('activo', true)->findOrFail($id);

        return response()->json(['data' => new ServicioResource($servicio)]);
    }
}
