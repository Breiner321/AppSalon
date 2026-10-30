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
     * Listado de servicios en el panel de administración, 10 por página.
     */
    public function index(): View
    {
        $servicios = Service::orderBy('nombre')->paginate(10);

        return view('admin.servicios.index', compact('servicios'));
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
     * Formulario para editar un servicio (route model binding: {servicio}).
     */
    public function edit(Service $servicio): View
    {
        return view('admin.servicios.form', compact('servicio'));
    }

    /**
     * Actualiza un servicio existente.
     */
    public function update(ServiceRequest $request, Service $servicio): RedirectResponse
    {
        $resultado = $this->runSafely(
            fn () => $servicio->update($request->validated()),
            'No pudimos actualizar el servicio. Intenta de nuevo.'
        );

        if ($resultado instanceof RedirectResponse) {
            return $resultado;
        }

        return redirect()
            ->route('admin.servicios.index')
            ->with('success', 'Servicio actualizado');
    }

    /**
     * Elimina un servicio. Las filas de citasServicios que lo usan se borran
     * en cascada (ver la migración de citasServicios).
     */
    public function destroy(Service $servicio): RedirectResponse
    {
        $resultado = $this->runSafely(
            fn () => $servicio->delete(),
            'No pudimos eliminar el servicio. Intenta de nuevo.'
        );

        if ($resultado instanceof RedirectResponse) {
            return $resultado;
        }

        return redirect()
            ->route('admin.servicios.index')
            ->with('success', 'Servicio eliminado');
    }
}
