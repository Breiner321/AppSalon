<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\HandlesDatabaseErrors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Gestión de usuarios del panel: búsqueda, filtros, activar o desactivar la
 * cuenta y asignar el rol (cliente, empleado o administrador).
 */
class UsuarioController extends Controller
{
    use HandlesDatabaseErrors;

    /**
     * Listado paginado con búsqueda por nombre, apellido, correo o teléfono y
     * filtros por rol y estado. Los filtros se conservan al paginar.
     */
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:60'],
            'rol' => ['nullable', Rule::in(array_keys(User::ROLES))],
            'estado' => ['nullable', 'in:activo,inactivo,sin_confirmar'],
        ]);

        $usuarios = User::query()
            ->withCount('citas')
            ->when($filtros['q'] ?? null, function ($q, string $texto) {
                $q->where(function ($q) use ($texto) {
                    foreach (['nombre', 'apellido', 'email', 'telefono'] as $columna) {
                        $q->orWhere($columna, 'like', '%'.$texto.'%');
                    }
                    $q->orWhereRaw("CONCAT(nombre, ' ', apellido) LIKE ?", ['%'.$texto.'%']);
                });
            })
            ->when(($filtros['rol'] ?? null) === 'admin', fn ($q) => $q->where('admin', true))
            ->when(($filtros['rol'] ?? null) === 'empleado', fn ($q) => $q->where('admin', false)->where('empleado', true))
            ->when(($filtros['rol'] ?? null) === 'cliente', fn ($q) => $q->clientes())
            ->when(($filtros['estado'] ?? null) === 'activo', fn ($q) => $q->where('activo', true))
            ->when(($filtros['estado'] ?? null) === 'inactivo', fn ($q) => $q->where('activo', false))
            ->when(($filtros['estado'] ?? null) === 'sin_confirmar', fn ($q) => $q->where('confirmado', false))
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->paginate(10)
            ->withQueryString();

        return view('admin.usuarios.index', [
            'usuarios' => $usuarios,
            'filtros' => $filtros,
            'roles' => User::ROLES,
        ]);
    }

    /**
     * Activa o desactiva una cuenta. Un usuario desactivado no puede iniciar
     * sesión y sus tokens de la API se borran. Nadie puede desactivarse a sí mismo.
     */
    public function cambiarActivo(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->is($request->user())) {
            return back()->with('warning', 'No puedes desactivar tu propia cuenta.');
        }

        $activo = ! $usuario->isActivo();

        $resultado = $this->runSafely(function () use ($usuario, $activo) {
            $usuario->forceFill(['activo' => $activo])->save();

            if (! $activo) {
                $usuario->tokens()->delete();
            }

            return true;
        }, 'No pudimos cambiar el estado del usuario. Intenta de nuevo.');

        if ($resultado instanceof RedirectResponse) {
            return $resultado;
        }

        return back()->with('success', ($activo ? 'Cuenta activada: ' : 'Cuenta desactivada: ').$usuario->nombreCompleto());
    }

    /**
     * Asigna uno de los roles: cliente, empleado o administrador. Nadie puede
     * cambiarse su propio rol (el salón podría quedarse sin acceso al panel).
     */
    public function cambiarRol(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->is($request->user())) {
            return back()->with('warning', 'No puedes cambiar tu propio rol.');
        }

        $rol = $request->validate([
            'rol' => ['required', Rule::in(array_keys(User::ROLES))],
        ])['rol'];

        $resultado = $this->runSafely(
            fn () => $usuario->asignarRol($rol)->save(),
            'No pudimos cambiar el rol del usuario. Intenta de nuevo.'
        );

        if ($resultado instanceof RedirectResponse) {
            return $resultado;
        }

        return back()->with('success', $usuario->nombreCompleto().' ahora tiene el rol '.mb_strtolower(User::ROLES[$rol]));
    }
}
