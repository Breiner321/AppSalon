# Checklist · Entrega Final (Semana 16)

Requisitos adicionales de la Entrega Final contra el enunciado (`Proyecto_Final.docx`). Cada casilla se comprobó en el
código, con una prueba automática o en la aplicación corriendo. Estado: **`php artisan test` → 116 pruebas en verde**
sobre MariaDB.

Leyenda: `[x]` verificado · `[ ]` pendiente.

> **Alcance.** Esta etapa cubre los requerimientos 6, 7 y 8 y la documentación final. El requerimiento
> **9 (Reportes y exportación)** queda fuera de este trabajo: ver *Pendientes*.

## 6. Sistema de citas completo · 40 puntos

- [x] **Reserva con calendario interactivo.** Calendario mensual que deshabilita días pasados, cerrados, bloqueados o fuera
  del rango; al elegir un día consulta las horas libres sin recargar la página.
  Evidencia: `resources/views/appointments/form.blade.php`, `GET /citas/disponibilidad`; `CitaReservaTest::test_el_formulario_muestra_el_calendario…`,
  `test_la_disponibilidad_devuelve_solo_las_horas_libres`; captura `docs/capturas/12-reservar-cita.png`.
- [x] **Selección múltiple de servicios por cita**, con total y duración calculados en el servidor.
  Evidencia: `AppointmentRequest` (`servicios[]`), `ReservaCitas::crear`; `test_reserva_una_cita_con_varios_servicios_y_envia_el_correo`.
- [x] **Validación de disponibilidad (no sobreposición).** Intervalos `[hora, hora + duración)`; revisión en el Form Request y otra
  vez dentro de la transacción con `SELECT … FOR UPDATE`.
  Evidencia: `Disponibilidad::motivoNoDisponible`, `ReservaCitas::asegurarDisponible`; `test_no_permite_citas_que_se_sobreponen`,
  `test_una_cita_cancelada_libera_su_horario`, `ApiTest::test_post_citas_reserva_y_valida`. Datos de prueba: 0 sobreposiciones (consulta SQL).
- [x] **Gestión de horarios del salón**: horario por día de la semana, fechas bloqueadas, anticipación mínima y límite de días.
  Evidencia: tablas `horarios` y `bloqueos`, `config/salon.php`, `/admin/horarios`; `HorariosTest`, `test_respeta_el_horario_del_salon`,
  `test_no_permite_reservar_en_una_fecha_bloqueada`.
- [x] **Cancelación y modificación de citas** por el cliente (web y API), solo de citas propias, pendientes o confirmadas y futuras.
  Evidencia: `AppointmentController@edit/update/cancelar`, `AppointmentPolicy`; `test_modifica_su_cita…`, `test_cancela_su_cita…`,
  `test_no_puede_ver_ni_cambiar_citas_de_otro_cliente`, `test_no_puede_modificar_ni_cancelar_una_cita_pasada_o_cancelada`.
- [x] **Notificaciones de confirmación por email** al reservar, modificar, confirmar y cancelar.
  Evidencia: `App\Mail\CitaNotificacion`, `resources/views/emails/cita.blade.php`; `Mail::assertSent` en `CitaReservaTest`
  y `AdminCitasTest`; `test_el_correo_de_la_cita_tiene_el_detalle`.

## 7. Panel de administración avanzado · 20 puntos

- [x] **Dashboard con estadísticas**: citas del día, ingresos de hoy (y esperados), ingresos del mes, clientes y nuevos del mes,
  pendientes de confirmar y lista de citas de hoy.
  Evidencia: `AdminController@index`, `resources/views/admin/index.blade.php`; `AdminCitasTest::test_el_dashboard_muestra…`; captura `05-panel-admin.png`.
- [x] **Gestión completa de usuarios**: activar/desactivar (bloquea login web y API, borra tokens, cierra la sesión abierta) y cambiar el rol.
  Evidencia: `Admin\UsuarioController`, `EnsureUserIsActive`; `AdminUsuariosTest`; captura `16-usuarios.png`.
- [x] **Vista de agenda del día y de la semana**, con navegación y cambio de estado en línea.
  Evidencia: `Admin\CitaController@agenda`, `resources/views/admin/citas/agenda.blade.php`; `test_la_agenda_del_dia_y_de_la_semana`; captura `14-agenda-semana.png`.
- [x] **Búsqueda y filtros en citas** (cliente, estado, rango de fechas) **y usuarios** (texto, rol, estado).
  Evidencia: `/admin/citas`, `/admin/usuarios`; `test_busca_y_filtra_citas`, `test_busca_y_filtra_usuarios`; captura `15-citas-filtros.png`.
- [x] **Gestión de disponibilidad horaria** desde el panel: horario semanal y fechas bloqueadas.
  Evidencia: `Admin\HorarioController`; `HorariosTest`; captura `17-horarios.png`.

## 8. API y funcionalidades modernas · 20 puntos

- [x] **API REST básica** para consultar servicios y citas. Evidencia: `routes/api.php`, `app/Http/Controllers/Api`.
- [x] **Respuestas JSON estructuradas**: `data`, `message`, `meta`; errores con `message` y `errors`; acentos sin escapar.
  Evidencia: `app/Http/Resources`, `bootstrap/app.php`; `ApiTest` (`assertJsonStructure`, `assertJsonPath`).
- [x] **Autenticación API con Laravel Sanctum** (token Bearer, expira en 24 h, logout revoca).
  Evidencia: `Api\AuthController`, `config/sanctum.php`; `test_login_devuelve_un_token…`, `test_logout_revoca_el_token`, `test_sin_token_responde_401_en_json`.
- [x] **Endpoints documentados**: `GET /api/servicios`, `GET /api/citas/usuario/{id}`, `POST /api/citas`, `PUT /api/citas/{id}`
  (más login, logout, usuario, disponibilidad, detalle y cancelar). Evidencia: [docs/api.md](api.md); una prueba por endpoint en `ApiTest`.

## Entregables finales

- [x] **Manual técnico** con arquitectura, diagrama de base de datos y documentación de la API:
  [docs/manual-tecnico.md](manual-tecnico.md) y [docs/api.md](api.md).
- [x] **Código fuente completo y documentado** (docblocks en español en controladores, servicios, modelos y pruebas).
- [x] **Base de datos con datos de prueba realistas**: 10 usuarios, 10 servicios, horario semanal, 1 fecha bloqueada y ~150 citas
  de las últimas 6 semanas y las próximas 3, sin sobreposiciones. Evidencia: `database/seeders`, `database/sql/appsalon_mvc_php.sql` (importación probada).
- [x] **Sistema funcionando en servidor local** (`php artisan serve`, ver README). Revisado en Chromium a 1280 px y 360–390 px sin desplazamiento horizontal.
- [x] **Manual de usuario (PDF, 5 páginas)** actualizado: reserva y gestión de citas (pág. 3), panel de administración (pág. 4) y mensajes nuevos.
  Evidencia: `docs/manual/Manual-de-usuario-AppSalon.pdf`; `php docs/manual/contar-paginas.php` → 5 páginas.

## Pendientes

- **Requerimiento 9 · Reportes y exportación** (reporte de citas por período, ingresos por servicio/período, PDF, Excel/CSV y gráficas):
  fuera del alcance de esta etapa.
- **Recuperación de contraseña por correo** (aparece en la descripción general del módulo de autenticación, no en los requerimientos 6–8).
