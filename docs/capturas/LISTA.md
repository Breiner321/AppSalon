# Capturas del manual de usuario (U10)

Las usa `docs/manual/manual-usuario.html`. Todas se generaron con Chrome headless y la base recién sembrada.

| Archivo | URL | Rol | Ventana | Qué debe verse |
|---|---|---|---|---|
| `01-catalogo.png` | `/` | invitado | 1280 × 800 | Título "Nuestros servicios", invitación a registrarse y tarjetas en 3 columnas con duración y precio |
| `02-registro.png` | `/registro` | invitado | 1280 × 800 | Formulario "Crear cuenta" vacío, con asteriscos en los obligatorios |
| `03-registro-errores.png` | `/registro` (enviado con datos inválidos) | invitado | 1280 × 800 | Campos en rojo con su mensaje: apellido vacío, correo inválido, teléfono de 5 dígitos, contraseña corta. "Ana" se conserva |
| `04-login.png` | `/login` | invitado | 1280 × 800 | Formulario "Iniciar sesión" con correo y contraseña |
| `05-panel-admin.png` | `/admin` | admin | 1280 × 800 | Dashboard de la Entrega Final: citas de hoy, ingresos, clientes, aviso de pendientes y citas del día |
| `06-listado-servicios.png` | `/admin/servicios` | admin | 1280 × 800 | Tabla con nombre, precio, duración, estado y botones editar/eliminar |
| `07-crear-servicio.png` | `/admin/servicios/create` | admin | 1280 × 800 | Formulario "Nuevo servicio" con el switch Activo encendido |
| `08-editar-servicio.png` | `/admin/servicios/{id}/edit` (primer servicio) | admin | 1280 × 800 | Formulario "Editar servicio" con los datos de "Corte de cabello hombre" |
| `09-confirmar-eliminar.png` | `/admin/servicios` + clic en 🗑 | admin | 1280 × 800 | Modal "Eliminar servicio" con el nombre del servicio y los botones Cancelar/Eliminar |
| `10-acceso-denegado.png` | `/admin` | cliente | 1280 × 800 | Página 403 "No tienes permiso para ver esta página"; la navbar del cliente no tiene Panel |
| `11-movil-catalogo.png` | `/` | invitado | 360 × 780 | Catálogo en una columna con el menú colapsado (☰) |
| `12-reservar-cita.png` | `/citas/nueva` (2 servicios, un día y una hora elegidos) | cliente | 1280 × página completa | Pasos 1–3 de la reserva: servicios marcados, calendario con el día elegido, horas libres y resumen con el total |
| `13-mis-citas.png` | `/citas` | cliente | 1280 × 800 | Tarjetas de las próximas citas con estado, servicios, total y botones Modificar/Cancelar |
| `14-agenda-semana.png` | `/admin/agenda?vista=semana` | admin | 1280 × 800 | Agenda de lunes a domingo con las citas de cada día y su estado |
| `15-citas-filtros.png` | `/admin/citas?estado=pendiente` | admin | 1280 × 800 | Filtros de cliente, estado y fechas, y tabla de citas pendientes con el selector de estado |
| `16-usuarios.png` | `/admin/usuarios?q=appsalon.com` | admin | 1280 × 800 | Las tres cuentas de prueba (administrador, cliente y empleado), filtros de rol y estado, botones Desactivar y selector de rol con Asignar |
| `17-horarios.png` | `/admin/horarios` | admin | 1280 × 800 | Horario semanal editable y fechas bloqueadas |
| `18-reportes.png` | `/admin/reportes` | admin | 1280 × 800 | Filtros del período, rangos rápidos, tarjetas de resumen y gráficas de citas e ingresos por día |
| `19-movil-calendario.png` | `/citas/nueva?servicio=1` | cliente | 360 × 780 | Calendario y horas libres en el celular |
| `20-olvide-contrasena.png` | `/olvide-contrasena` | invitado | 1280 × 800 | Formulario para pedir el enlace de recuperación de contraseña |
| `21-swagger.png` | `/api/documentacion` | invitado | 1280 × 800 | Swagger UI con las instrucciones de uso y los endpoints agrupados en Autenticación, Servicios y Citas |
| `22-swagger-citas.png` | `/api/documentacion` + desplegar `POST /citas` | invitado | 1280 × 800 | Operación "Reservar una cita" abierta, con el cuerpo de ejemplo y el botón Execute |
| `23-panel-empleado.png` | `/admin` | empleado | 1280 × 800 | "Panel del salón" con el menú reducido (Panel, Agenda, Citas) y el botón Ver citas |
| `24-reportes-servicio.png` | `/admin/reportes?servicio=1` | admin | 1280 × 800 | Reportes filtrados por "Corte de cabello mujer": filtro de servicio, resumen y gráficas |
| `25-citas-servicio.png` | `/admin/citas?servicio=1` | admin | 1280 × 800 | Búsqueda de citas con el filtro de servicio aplicado |

Las capturas 12 a 20 (Entrega Final) se tomaron con Chromium (Playwright) sobre la base recién sembrada el 21/11/2026,
por eso las fechas y los números cambian si se vuelven a generar otro día.

Las capturas 16 y 21 a 25 (rúbrica de la Entrega Final: roles, empleado, filtro por servicio y Swagger) las genera
`docs/capturas/herramienta/capturas-rubrica.mjs` y las usa el instructivo de evaluación
(`docs/Instructivo-Evaluacion-Entrega-Final-AppSalon.docx`).

## Generarlas otra vez

```bash
php artisan migrate:fresh --seed
php artisan serve
node docs/capturas/herramienta/capturas.mjs
node docs/capturas/herramienta/capturas-rubrica.mjs
```

El script (`docs/capturas/herramienta/capturas.mjs`) inicia sesión con las cuentas de prueba de
`database/seeders/UserSeeder.php` y no crea ni borra datos. Si el login responde "Demasiados intentos",
espera un minuto (límite de 5 intentos por minuto) y vuelve a correrlo.

## Tomarlas a mano

1. `php artisan migrate:fresh --seed` para que los datos sean los de prueba.
2. Abre Chrome, F12 → ícono de dispositivo (Ctrl+Shift+M) → **Responsive**, y escribe el tamaño de la tabla
   (1280 × 800; para la 11, 360 × 780).
3. Entra con la cuenta del rol que indica la tabla (las credenciales están en el README y en `UserSeeder`).
   Para cambiar de rol usa el menú con tu nombre → **Cerrar sesión**.
4. Abre la URL, deja la página como describe la columna "Qué debe verse" y en DevTools pulsa
   Ctrl+Shift+P → **Capture screenshot** (no "full size").
5. Guarda el archivo en `docs/capturas/` con el nombre exacto de la tabla.
