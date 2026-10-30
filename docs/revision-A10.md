# Revisión A10 · Seguridad del CRUD de servicios

Revisión del trabajo del Dev 2 (U4–U9) hecha por el Dev 1 antes del code freeze de la Entrega Intermedia.

**Alcance:** `app/Http/Controllers/ServiceController.php`, `app/Http/Requests/ServiceRequest.php`,
`app/Http/Controllers/AdminController.php`, `resources/views/admin/**`, `resources/views/services/index.blade.php`,
`resources/views/layouts/app.blade.php` y las rutas `admin.index` y `admin.servicios.*` de `routes/web.php`.

**Resultado:** no se encontraron fallas de seguridad, así que **no se cambió código del Dev 2**.
Se agregó `tests/Feature/RevisionCrudTest.php` para dejar cada punto comprobado con un test.

## Puntos revisados

| # | Punto | Resultado | Evidencia |
|---|---|---|---|
| 1 | Solo se usa `$request->validated()` | ✅ Cumple. `store` y `update` usan `$request->validated()` de `ServiceRequest`; ningún método usa `$request->all()` ni `input()` para escribir. | `ServiceController.php:55` y `:83`; test `los_campos_no_validados_se_ignoran` |
| 2 | Todas las acciones tienen `['auth', 'admin']` | ✅ Cumple. Las 7 rutas (`admin.index` y las 6 de `admin.servicios.*`) muestran `auth` y `admin` en `php artisan route:list --path=admin -v`. | `routes/web.php`; tests `AdminAccesoTest`, `ServicioCrudTest::el_cliente_recibe_403_en_todo_el_crud`, `RevisionCrudTest::un_invitado_no_puede_crear_ni_borrar` |
| 3 | `destroy` solo por DELETE y con `@csrf` | ✅ Cumple. La ruta resource solo registra DELETE para `destroy` (GET y POST responden 405). El formulario del modal lleva `@csrf` y `@method('DELETE')`. | `admin/servicios/index.blade.php:123-125`; tests `un_servicio_solo_se_borra_por_delete`, `un_delete_sin_token_csrf_devuelve_419_y_no_borra` |
| 4 | No hay `{!! !!}` | ✅ Cumple. Ninguna vista de `resources/views` usa `{!! !!}` ni `<?=`. El script del modal copia el nombre con `textContent`, que no interpreta HTML. | Búsqueda en `resources/views`; test `el_html_del_nombre_y_la_descripcion_sale_escapado` |
| 5 | Todas las escrituras usan `runSafely` | ✅ Cumple. `store`, `update` y `destroy` pasan por `runSafely`, con mensajes en lenguaje simple. | `ServiceController.php:54`, `:82`, `:102` |
| 6 | Sin consultas N+1 | ✅ Cumple. Las vistas no recorren relaciones. El listado hace 1 consulta paginada más el conteo; el panel hace 3 `count()` fijos. El número de consultas no cambia al pasar de 2 a 10 servicios. | test `el_listado_y_el_catalogo_no_tienen_consultas_n_mas_1` |
| 7 | Asignación masiva | ✅ Cumple. `Service` tiene `$fillable` explícito, sin `$guarded = []`. | `app/Models/Service.php` |
| 8 | Consultas con parámetros | ✅ Cumple. No hay `DB::raw`, `whereRaw`, `DB::select` ni `DB::statement` en `app/`. Todo pasa por Eloquent y route model binding. | Búsqueda en `app/` |

## Observaciones que no son fallas (para la Entrega Final)

No se corrigieron porque no son fallas de seguridad y el código entra en congelamiento.

1. **`ServiceRequest::authorize()` devuelve `true`.** Hoy es correcto, porque la ruta ya exige `auth` y `admin`.
   Como defensa en profundidad, podría devolver `$this->user()?->isAdmin() ?? false`, así el request sigue protegido
   aunque alguien lo use en otra ruta.
2. **El nombre del servicio no es único.** Se pueden crear dos servicios con el mismo nombre. Si importa,
   se puede agregar `Rule::unique('servicios', 'nombre')->ignore($this->route('servicio'))` (no requiere cambiar el esquema).
3. **Borrar un servicio lo quita de las citas existentes** (cascada en `citasServicios`) y el `total` de esas citas
   no se recalcula. El modal ya avisa al administrador. Para la Entrega Final conviene preferir desactivar a borrar
   cuando el servicio tenga citas, o recalcular el total.
4. **La unidad del precio** se muestra con `$`, pero sigue pendiente de confirmar con el profesor (ver `ServiceSeeder`).
