# Revisión T6a: vistas de autenticación y de errores

Revisión del Dev 2 sobre el código del Dev 1. Se revisaron `resources/views/auth/{login,registro}.blade.php`
y `resources/views/errors/{403,404,419,429,500}.blade.php` contra `docs/guia-visual.md`.

## 1. Nombres de ruta

Se compararon todos los `route('...')` y `routeIs('...')` de `resources/views` con `php artisan route:list`.
**Todos existen**: `inicio`, `servicios.catalogo`, `login`, `logout`, `registro`, `admin.index` y
`admin.servicios.{index,create,store,edit,update,destroy}`.

## 2. Correcciones visuales aplicadas

| Archivo | Cambio |
|---|---|
| `resources/views/errors/404.blade.php:14` | Botón "Ver servicios" → "Volver a los servicios", igual que 403 y 500 |
| `resources/views/errors/419.blade.php:15` | Botón "Ir a los servicios" → "Volver a los servicios" |
| `resources/views/errors/429.blade.php:14` | Botón "Ir a los servicios" → "Volver a los servicios" |
| `resources/views/errors/500.blade.php:9,17,24` | Carga Poppins para el título (guía §2) y usa `text-body-secondary` como el resto de las vistas, en lugar de `text-secondary` |
| `resources/views/auth/login.blade.php:1` | Comentario desactualizado ("la lógica llega en A4") → indica que lo valida `LoginRequest` |

Lo que ya estaba bien: las dos vistas de auth usan `card-form`, `@csrf`, `old()` (sin rellenar contraseñas),
`is-invalid` + `<x-form-error>` en cada campo validado, `form-text` con `aria-describedby` en el teléfono,
`col-12 col-md-6` para apilar en móvil y un solo botón primario. Las páginas de error siguen todas el mismo patrón
(ícono `display-1 text-primary`, `h1`, texto suave, botón primario hacia `servicios.catalogo`).

## 3. Hallazgos de lógica (no se tocaron, son del Dev 1)

| # | Archivo y línea | Hallazgo | Sugerencia |
|---|---|---|---|
| 1 | `app/Http/Controllers/AuthController.php:69` | `Auth::attempt` no revisa `confirmado`. Hoy todos los registros quedan con `confirmado = false` (`RegistroTest:58`) y aun así pueden iniciar sesión. | Si la confirmación por correo no entra en el alcance, documentarlo y dejarlo así. Si entra: `Auth::attempt([...credenciales, 'confirmado' => true])` y un mensaje "Confirma tu cuenta" distinto de "Credenciales incorrectas". |
| 2 | `app/Http/Requests/LoginRequest.php:27` | `email` no tiene `max:30`, a diferencia de `RegisterRequest` y de la columna `usuarios.email`. No es un fallo (el correo largo no existirá en la base), pero es una consulta inútil. | Agregar `max:30` para que sea igual que el registro. |
| 3 | `resources/views/errors/500.blade.php:28` | La 500 es autónoma para mostrarse aunque falle el layout, pero usa `route('servicios.catalogo')`. Si el error viene del enrutador o del generador de URLs, la página de error podría fallar también. | Usar `href="{{ url('/servicios') }}"` o directamente `href="/servicios"`. Ojo: `ErroresTest` solo revisa la 404, así que no rompe pruebas. |
| 4 | `resources/views/errors/500.blade.php:12-18` | Los colores de la paleta están repetidos a mano en un `<style>`. Es intencional (no carga `app.css`), pero si cambia la paleta hay que cambiarlos en dos sitios. | Dejar un comentario en `app.css` junto a `:root` que recuerde actualizar también la 500. |
| 5 | `resources/views/auth/registro.blade.php:62-66` | "Confirmar contraseña" nunca se marca en rojo. El error `confirmed` cae en `password`, así que el mensaje sí se ve, pero solo debajo del primer campo. | Opcional: agregar `@error('password') is-invalid @enderror` también a `password_confirmation` (sin `<x-form-error>`, para no duplicar el mensaje). |
