# Checklist · Entrega Intermedia (Semana 11)

Verificación de los requisitos contra la rúbrica de 100 puntos. Cada casilla se comprobó en el código, con un test
o en la aplicación corriendo. Estado al cerrar el code freeze: **`php artisan test` → 72 tests en verde (502 aserciones)**
sobre MariaDB 10.4.32 (XAMPP).

> **Fuente de los requisitos.** El enunciado oficial no está en el repositorio. Las casillas salen de los
> requisitos y entregables que el equipo copió del enunciado en las tareas (prompts de Dev 1 y Dev 2) y en
> `docs/seguridad.md`. Antes de entregar, cotejar esta lista con el enunciado oficial (ver *Pendientes*).

Leyenda: `[x]` verificado · `[ ]` no se cumple o falta verificar (va a *Pendientes*).

## 1. Base de datos · 20 puntos

- [x] **Tabla `usuarios`** con nombre (60), apellido (60), email (30, único), password (60), telefono (10), admin, confirmado y token (15).
  Evidencia: `database/migrations/0001_01_01_000000_create_users_table.php`; consulta a `information_schema.COLUMNS`.
- [x] **Tabla `servicios`** con nombre (60), precio DECIMAL(5,2), descripcion, duracion y activo.
  Evidencia: `database/migrations/2026_10_23_000001_create_servicios_table.php`.
- [x] **Tabla `citas`** con fecha, hora, usuarioId, total DECIMAL(6,2) y estado (pendiente, confirmada, completada, cancelada).
  Evidencia: `database/migrations/2026_10_23_000002_create_citas_table.php`.
- [x] **Tabla pivote `citasServicios`** (relación muchos a muchos entre citas y servicios).
  Evidencia: `database/migrations/2026_10_23_000003_create_citas_servicios_table.php`; `Appointment::servicios()`, `Service::citas()`.
- [x] **Llaves foráneas con integridad referencial**: `citas.usuarioId → usuarios` (SET NULL), `citasServicios.citaId → citas` (CASCADE),
  `citasServicios.servicioId → servicios` (CASCADE). Motor InnoDB, `utf8mb4_unicode_ci`.
  Evidencia: consulta a `information_schema.REFERENTIAL_CONSTRAINTS`; `tests/Feature/IntegridadTest.php` (3 tests).
- [x] **Migraciones** que crean todo desde cero: `php artisan migrate:fresh` sin errores.
- [x] **Datos de prueba (seeders)**: 2 usuarios (admin y cliente), 10 servicios, 2 citas con 2 servicios cada una y total = suma de precios.
  Evidencia: `database/seeders/{User,Service,Appointment}Seeder.php`; `php artisan migrate:fresh --seed`.
- [x] **Script SQL completo** (estructura + datos) exportado con `mysqldump`.
  Evidencia: `database/sql/appsalon_mvc_php.sql` (T6c), importación probada en una base temporal.
- [x] **Modelos Eloquent con sus relaciones**: `User`, `Service`, `Appointment`, `AppointmentService` (pivote).
  Evidencia: `app/Models/*.php`.

## 2. Autenticación y seguridad · 25 puntos

- [x] **Registro de usuarios** con validación del lado del servidor (nombre, apellido, email único ≤ 30, teléfono de 10 dígitos, contraseña ≥ 6 confirmada).
  Evidencia: `RegisterRequest`; `RegistroTest`, `AutenticacionTest` (teléfono de 9 y 11 dígitos, email > 30, confirmación distinta).
- [x] **Inicio de sesión** con `Auth::attempt` y mensaje genérico "Credenciales incorrectas" (no revela si el email existe).
  Evidencia: `AuthController@login`; `LoginTest` (6 tests).
- [x] **Cierre de sesión** que invalida la sesión y regenera el token CSRF.
  Evidencia: `AuthController@logout`; `LoginTest::el_logout_cierra_la_sesion`, `AutenticacionTest::admin_redirige_al_login_despues_del_logout`.
- [x] **Contraseñas con hash** (bcrypt, cast `hashed`); nunca en texto plano; `password` y `token` ocultos al serializar.
  Evidencia: `app/Models/User.php`; `RegistroTest`, `SeguridadTest`.
- [x] **Protección CSRF** en todos los formularios (`@csrf`); un POST sin token responde 419.
  Evidencia: 5 formularios con `@csrf`; `AutenticacionTest::un_post_sin_token_csrf_devuelve_419`, `RevisionCrudTest`.
- [x] **Protección XSS**: todas las vistas usan `{{ }}`; ningún `{!! !!}`.
  Evidencia: búsqueda en `resources/views`; `ComponentesUiTest`, `RevisionCrudTest::el_html_del_nombre_y_la_descripcion_sale_escapado`.
- [x] **Sesiones seguras**: `session()->regenerate()` al entrar, cookie `http_only`, `same_site=lax`, 120 minutos.
  Evidencia: `config/session.php`, `.env.example`.
- [x] **Permisos de administrador**: middleware `admin` (403 para clientes) junto con `auth` en todas las rutas del panel; `admin` fuera de `$fillable`.
  Evidencia: `app/Http/Middleware/AdminMiddleware.php`, `routes/web.php`; `AdminAccesoTest`, `ServicioCrudTest::el_cliente_recibe_403_en_todo_el_crud`.
- [x] **Prepared statements vía Eloquent**: sin `DB::raw`, `whereRaw` ni SQL concatenado en `app/`.
  Evidencia: búsqueda en `app/`; `docs/revision-A10.md` punto 8.
- [x] **Documento de seguridad** que relaciona cada requisito con su mecanismo.
  Evidencia: `docs/seguridad.md`.

## 3. Servicios · 20 puntos

- [x] **Catálogo público** con los servicios activos (nombre, descripción, duración y precio), en orden alfabético.
  Evidencia: `ServiceController@catalog`, `resources/views/services/index.blade.php`; `CatalogoTest` (3 tests). Rutas `/` y `/servicios`.
- [x] **Listado en el panel** con estado activo/inactivo y paginación de 10.
  Evidencia: `ServiceController@index`; `ServicioCrudTest::la_paginacion_muestra_10_por_pagina`. Ruta `/admin/servicios`.
- [x] **Crear servicio** con validación (nombre ≤ 60, precio 0–999.99, duración 10–480 min).
  Evidencia: `ServiceRequest`, `ServiceController@store`; `ServicioCrudTest::se_rechazan_datos_invalidos` (con proveedor de datos).
- [x] **Editar servicio**. Evidencia: `ServiceController@edit/update`; `ServicioCrudTest::el_admin_crea_edita_y_elimina_un_servicio`.
- [x] **Eliminar servicio** con confirmación (modal), solo por DELETE y con CSRF.
  Evidencia: `admin/servicios/index.blade.php`; `RevisionCrudTest::un_servicio_solo_se_borra_por_delete`.
- [x] **Activar y desactivar** servicios sin borrarlos (el inactivo no sale en el catálogo).
  Evidencia: `ServicioCrudTest::el_switch_apagado_crea_el_servicio_inactivo`, `CatalogoTest`.
- [x] **Solo el administrador** gestiona servicios. Evidencia: ver bloque 2, permisos.

## 4. Arquitectura · 20 puntos

- [x] **Patrón MVC de Laravel 12**: rutas → controladores → modelos → vistas Blade.
  Evidencia: `routes/web.php`, `app/Http/Controllers`, `app/Models`, `resources/views`.
- [x] **Rutas con nombre** (contrato del equipo) usadas con `route('...')`.
  Evidencia: `routes/web.php`; `docs/revision-T6a.md` §1.
- [x] **Validación en Form Requests** (`RegisterRequest`, `LoginRequest`, `ServiceRequest`) y solo `validated()` al escribir.
- [x] **Middleware** para autenticación, invitados, administrador y límite de intentos.
  Evidencia: `bootstrap/app.php`, `routes/web.php`.
- [x] **Manejo de errores** centralizado: `runSafely` (transacción + log + mensaje) y vistas 403, 404, 419, 429, 500 (+ 4xx/5xx).
  Evidencia: `app/Support/HandlesDatabaseErrors.php`, `resources/views/errors`; `docs/convenciones.md`; `ErroresTest`.
- [x] **Código documentado**: PHPDoc y comentarios en español en controladores, modelos, requests, middleware, seeders y vistas.
- [x] **Archivo de configuración de ejemplo** sin contraseñas reales: `.env.example`.
- [x] **Tests automatizados**: 72 tests de Feature y Unit en verde.
- [x] **Control de versiones**: una rama por tarea, Conventional Commits con el ID de tarea y merge `--no-ff` a `develop`.
  Evidencia: `git log --oneline --graph`.
- [x] **README** con instalación, credenciales, funcionalidades y tecnologías. Evidencia: `README.md` (T7).

## 5. Interfaz · 15 puntos

- [x] **Layout común** con navegación por rol (invitado, cliente, admin) y enlace activo.
  Evidencia: `resources/views/layouts/app.blade.php`; `NavegacionTest` (5 tests).
- [x] **Bootstrap 5 y diseño propio** con la paleta del salón. Evidencia: `public/css/app.css`, `docs/guia-visual.md`.
- [x] **Responsive** en 360, 768 y 1280 px, botones táctiles de 44 px, listado en tarjetas en móvil.
  Evidencia: `docs/responsive.md` (36 combinaciones revisadas).
- [x] **Mensajes de éxito y error** (`<x-alert />`) y errores por campo (`<x-form-error />`).
  Evidencia: `ComponentesUiTest`, `InterfazTest`.
- [x] **Todo en español**, incluidos los mensajes de validación y las páginas de error.
  Evidencia: `lang/es`, `lang/es.json`; `IdiomaTest` (4 tests).
- [x] **Manual de usuario en PDF de 5 páginas como máximo**.
  Evidencia: `docs/manual/Manual-de-usuario-AppSalon.pdf` (5 páginas, comprobado con `php docs/manual/contar-paginas.php`).
- [x] **Capturas de pantalla** de las vistas principales. Evidencia: `docs/capturas/` (11 capturas).

## Funcionalidades adicionales

- [x] **Confirmación de cuenta por token** (A7): al registrarse se envía un correo con un enlace `/confirmar/{token}` de 15 caracteres;
  sin confirmar no se puede iniciar sesión. Evidencia: `app/Mail/ConfirmarCuenta.php`, `AuthController@confirmar`; `ConfirmacionTest` (5 tests).
- [x] **Límite de intentos (throttle)**: 5 por minuto en `POST /login` y `POST /registro`; el sexto responde 429.
  Evidencia: `routes/web.php`; `SeguridadTest`, `AutenticacionTest`.
- [x] **Páginas de error propias** en español: 403, 404, 419, 429, 500 y genéricas 4xx/5xx. Evidencia: `resources/views/errors`; `ErroresTest`, `IdiomaTest`.
- [x] **Panel de administración** con conteos (servicios activos, inactivos y usuarios) y accesos rápidos.
  Evidencia: `AdminController@index`; `NavegacionTest::el_panel_muestra_los_conteos_y_los_accesos`.
- [x] **Tests automatizados**: 72 tests (Feature y Unit) sobre una base de pruebas separada (`appsalon_mvc_php_test`).

## Pendientes

1. **Cotejar con el enunciado oficial.** Esta lista no se pudo comparar palabra por palabra con el enunciado porque no está en el repositorio.
   Si algún requisito del enunciado no aparece aquí, agregarlo y verificarlo.
2. **Hallazgos de `docs/revision-T6a.md` sin aplicar** (cambian lógica después del code freeze; esperan aprobación):
   - `LoginRequest`: `email` sin `max:30`.
   - `errors/500.blade.php`: usa `route()`; si el error viene del enrutador, la página de error también podría fallar.
   - `auth/registro.blade.php`: el campo "Confirmar contraseña" no se marca en rojo.
3. **Unidad del precio** pendiente de confirmar con el profesor (`ServiceSeeder`, `docs/revision-A10.md` §4).
4. **Citas**: la reserva de citas por el cliente no tiene interfaz. Las tablas y modelos existen, pero `AppointmentController` está vacío a propósito: es de la Entrega Final.
