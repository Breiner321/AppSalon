<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Ejecuta escrituras en la base de datos de forma segura desde un controlador.
 *
 * Uso (ver docs/convenciones.md):
 *
 *     $resultado = $this->runSafely(fn () => Service::create($datos), 'No se pudo guardar el servicio.');
 *     if ($resultado instanceof RedirectResponse) {
 *         return $resultado;
 *     }
 */
trait HandlesDatabaseErrors
{
    /**
     * Corre el callback dentro de una transacción. Si algo falla, deshace los
     * cambios, registra el error en el log y devuelve al formulario anterior con
     * los datos escritos y un mensaje flash 'error' para el usuario.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @param  string  $errorMessage  Mensaje en lenguaje simple que verá el usuario.
     * @return T|RedirectResponse El valor del callback, o la redirección si falló.
     */
    protected function runSafely(callable $callback, string $errorMessage): mixed
    {
        try {
            return DB::transaction($callback);
        } catch (QueryException $e) {
            Log::error($errorMessage, [
                'tipo' => 'consulta',
                'sql' => $e->getSql(),
                'codigo' => $e->getCode(),
                'error' => $e->getMessage(),
                'ruta' => request()->path(),
                'usuarioId' => request()->user()?->id,
            ]);
        } catch (Throwable $e) {
            Log::error($errorMessage, [
                'tipo' => $e::class,
                'error' => $e->getMessage(),
                'archivo' => $e->getFile().':'.$e->getLine(),
                'ruta' => request()->path(),
                'usuarioId' => request()->user()?->id,
            ]);
        }

        // No se reenvían las contraseñas al formulario.
        return back()
            ->withInput(request()->except('password', 'password_confirmation'))
            ->with('error', $errorMessage);
    }
}
