# Capturas del manual de usuario (U10)

Las usa `docs/manual/manual-usuario.html`. Todas se generaron con Chrome headless y la base recién sembrada.

| Archivo | URL | Rol | Ventana | Qué debe verse |
|---|---|---|---|---|
| `01-catalogo.png` | `/` | invitado | 1280 × 800 | Título "Nuestros servicios", invitación a registrarse y tarjetas en 3 columnas con duración y precio |
| `02-registro.png` | `/registro` | invitado | 1280 × 800 | Formulario "Crear cuenta" vacío, con asteriscos en los obligatorios |
| `03-registro-errores.png` | `/registro` (enviado con datos inválidos) | invitado | 1280 × 800 | Campos en rojo con su mensaje: apellido vacío, correo inválido, teléfono de 5 dígitos, contraseña corta. "Ana" se conserva |
| `04-login.png` | `/login` | invitado | 1280 × 800 | Formulario "Iniciar sesión" con correo y contraseña |
| `05-panel-admin.png` | `/admin` | admin | 1280 × 800 | Navbar con Panel activo, tarjetas de servicios activos, inactivos y usuarios, botones "Nuevo servicio" y "Ver servicios" |
| `06-listado-servicios.png` | `/admin/servicios` | admin | 1280 × 800 | Tabla con nombre, precio, duración, estado y botones editar/eliminar |
| `07-crear-servicio.png` | `/admin/servicios/create` | admin | 1280 × 800 | Formulario "Nuevo servicio" con el switch Activo encendido |
| `08-editar-servicio.png` | `/admin/servicios/{id}/edit` (primer servicio) | admin | 1280 × 800 | Formulario "Editar servicio" con los datos de "Corte de cabello hombre" |
| `09-confirmar-eliminar.png` | `/admin/servicios` + clic en 🗑 | admin | 1280 × 800 | Modal "Eliminar servicio" con el nombre del servicio y los botones Cancelar/Eliminar |
| `10-acceso-denegado.png` | `/admin` | cliente | 1280 × 800 | Página 403 "No tienes permiso para ver esta página"; la navbar del cliente no tiene Panel |
| `11-movil-catalogo.png` | `/` | invitado | 360 × 780 | Catálogo en una columna con el menú colapsado (☰) |

## Generarlas otra vez

```bash
php artisan migrate:fresh --seed
php artisan serve
node docs/capturas/herramienta/capturas.mjs
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
