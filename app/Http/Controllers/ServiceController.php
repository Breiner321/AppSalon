<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceRequest;
use App\Models\Service;
use App\Support\HandlesDatabaseErrors;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Servicios del salón: catálogo público y CRUD del panel de administración.
 */
class ServiceController extends Controller
{
    use HandlesDatabaseErrors;
    /**
     * Catálogo público de servicios (rutas / y /servicios).
     * Solo muestra los servicios activos, en orden alfabético.
     */
    public function catalog(): View
    {
        $servicios = Service::where('activo', true)->orderBy('nombre')->get();

        return view('services.index', compact('servicios'));
    }

    /**
     * Listado de servicios en el panel de administración.
     */
    public function index()
    {
        // TODO U5: listado de servicios del panel
        abort(501);
    }

    /**
     * Formulario para crear un servicio. Se usa la misma vista que para editar;
     * el servicio nuevo llega activo por defecto.
     */
    public function create(): View
    {
        $servicio = new Service(['activo' => true]);

        return view('admin.servicios.form', compact('servicio'));
    }

    /**
     * Guarda un servicio nuevo.
     */
    public function store(ServiceRequest $request): RedirectResponse
    {
        $resultado = $this->runSafely(
            fn () => Service::create($request->validated()),
            'No pudimos guardar el servicio. Intenta de nuevo.'
        );

        // Si falló, runSafely ya preparó la redirección con el mensaje de error.
        if ($resultado instanceof RedirectResponse) {
            return $resultado;
        }

        return redirect()
            ->route('admin.servicios.index')
            ->with('success', 'Servicio creado');
    }

    /**
     * Formulario para editar un servicio.
     */
    public function edit(string $id)
    {
        // TODO U5: formulario de edición
        abort(501);
    }

    /**
     * Actualiza un servicio existente.
     */
    public function update(string $id)
    {
        // TODO U6: validar y actualizar el servicio
        abort(501);
    }

    /**
     * Elimina un servicio.
     */
    public function destroy(string $id)
    {
        // TODO U6: eliminar el servicio
        abort(501);
    }
}
