# Convenciones del backend

## Escrituras en la base de datos: `runSafely`

Toda acción de un controlador que **crea, actualiza o borra** registros pasa por el trait
`App\Support\HandlesDatabaseErrors`. Su método `runSafely(callable $callback, string $errorMessage)`:

1. corre el callback dentro de `DB::transaction` (si falla a la mitad, no queda nada a medias);
2. si ocurre una `QueryException` o cualquier `Throwable`, escribe `Log::error` con contexto
   (SQL, código, ruta, usuario) en `storage/logs/laravel.log`;
3. devuelve `back()->withInput()->with('error', $errorMessage)`: el usuario vuelve al formulario con
   lo que escribió (sin las contraseñas) y ve el mensaje en `<x-alert />`.

Si todo sale bien, `runSafely` devuelve lo que devuelva el callback (por ejemplo, el modelo creado).

### Ejemplo desde un controlador

```php
use App\Http\Requests\ServiceRequest;
use App\Models\Service;
use App\Support\HandlesDatabaseErrors;
use Illuminate\Http\RedirectResponse;

class ServiceController extends Controller
{
    use HandlesDatabaseErrors;

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
}
```

### Reglas

- La validación va en un Form Request, **antes** de `runSafely`: los errores de validación no son
  errores de base de datos y Laravel ya los maneja.
- El mensaje es para el usuario: lenguaje simple, sin detalles técnicos. Los detalles van al log.
- Las lecturas simples (`Service::all()`, `findOrFail`) no necesitan `runSafely`; si fallan,
  se muestra la página `errors/500`.

## Páginas de error

Las vistas propias están en `resources/views/errors`: 403, 404, 419, 429 y 500. Todas tienen un botón
hacia `servicios.catalogo`. La 500 no extiende el layout para poder mostrarse aunque el layout falle.
