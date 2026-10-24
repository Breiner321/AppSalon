<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Servicios del salón: catálogo público y CRUD del panel de administración.
 */
class ServiceController extends Controller
{
    /**
     * Catálogo público de servicios (rutas / y /servicios).
     */
    public function catalog(): View
    {
        // TODO U4: cargar los servicios activos y mostrarlos en tarjetas
        return view('services.index');
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
     * Formulario para crear un servicio.
     */
    public function create()
    {
        // TODO U5: formulario de alta
        abort(501);
    }

    /**
     * Guarda un servicio nuevo.
     */
    public function store()
    {
        // TODO U6: validar y crear el servicio
        abort(501);
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
