# ✂ AppSalon

## 1. Descripción

AppSalon es un sistema web para un salón de belleza. Los visitantes consultan el catálogo de servicios,
los clientes crean su cuenta, la confirman por correo e inician sesión, y los administradores gestionan
los servicios del salón desde un panel propio.

> **Estado:** Entrega Intermedia (semana 11). La reserva de citas, la API y los reportes son de la
> Entrega Final (semana 16). Ver [13. Pendientes para la Entrega Final](#13-pendientes-para-la-entrega-final).

![Catálogo de servicios](docs/capturas/01-catalogo.png)

## 2. Tecnologías

Versiones con las que se desarrolló y probó el proyecto:

| Capa | Tecnología | Versión |
|---|---|---|
| Lenguaje | PHP (`php -v`) | 8.4.27 (compatible desde 8.2, la versión de XAMPP) |
| Framework | Laravel (`php artisan --version`) | 12.70.2: Eloquent, Blade, Form Requests, middleware, Mail |
| Base de datos | MariaDB de XAMPP (`SELECT VERSION();`) | 10.4.32-MariaDB (compatible con MySQL 8) |
| Interfaz | Bootstrap y Bootstrap Icons por CDN, fuente Poppins | Bootstrap 5.3.3, Icons 1.11.3. No necesita Node ni Vite |
| Pruebas | PHPUnit | 11.5 (72 pruebas) |
| Herramientas | Composer, Git y GitHub, phpMyAdmin | Composer 2 |

## 3. Requisitos

- **XAMPP** con PHP 8.2 o superior y MySQL/MariaDB. Las extensiones `pdo_mysql`, `mbstring`, `openssl`
  y `fileinfo` ya vienen activas en XAMPP.
- **Composer 2** ([getcomposer.org](https://getcomposer.org/download/)). El instalador para Windows lo agrega al PATH.
- **Git**.

Antes de instalar, comprueba en una ventana nueva de PowerShell que los tres comandos responden:

```powershell
php -v
composer -V
git --version
```

Si alguno dice *"no se reconoce como nombre de un cmdlet"*, ese programa no está en el PATH. Reinstálalo con su
instalador oficial o agrega su carpeta a la variable de entorno `Path` (para PHP de XAMPP: `C:\xampp\php`).

## 4. Instalación (Windows con XAMPP)

Los comandos son para **PowerShell**. Si instalaste XAMPP en otra carpeta, cambia `C:\xampp` por la tuya.

1. **Clonar el repositorio** y entrar a la carpeta.
   ```powershell
   git clone https://github.com/Breiner321/AppSalon.git
   cd AppSalon
   ```
2. **Instalar las dependencias de PHP.**
   ```powershell
   composer install
   ```
3. **Crear el archivo de configuración** a partir del ejemplo y **generar la clave** de la aplicación.
   ```powershell
   Copy-Item .env.example .env
   php artisan key:generate
   ```
   Los valores del ejemplo ya sirven para XAMPP: base `appsalon_mvc_php`, usuario `root` sin contraseña,
   idioma `es` y correos escritos en el log (`MAIL_MAILER=log`).
4. **Encender MySQL** con el botón *Start* de la fila *MySQL* en el panel de control de XAMPP.
5. **Crear la base de datos** (también puedes hacerlo en phpMyAdmin con el cotejamiento `utf8mb4_unicode_ci`).
   ```powershell
   C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE appsalon_mvc_php CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
   ```
6. **Crear las tablas y cargar los datos de prueba.**
   ```powershell
   php artisan migrate --seed
   ```
7. **Levantar el servidor** y abrir **http://127.0.0.1:8000**.
   ```powershell
   php artisan serve
   ```

### Alternativa: importar el script SQL

En lugar de los pasos 5 y 6 puedes importar [`database/sql/appsalon_mvc_php.sql`](database/sql/appsalon_mvc_php.sql),
que crea la base `appsalon_mvc_php` con todas las tablas y los mismos datos de prueba:

- **phpMyAdmin:** pestaña *Importar* → elegir el archivo → *Continuar*.
- **Consola:** PowerShell no admite `<`, así que se usa `cmd`:
  ```powershell
  cmd /c "C:\xampp\mysql\bin\mysql.exe -u root < database\sql\appsalon_mvc_php.sql"
  ```

## 5. Credenciales de prueba

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | `admin@appsalon.com` | `Admin123` |
| Cliente | `cliente@appsalon.com` | `Cliente123` |

Las dos cuentas ya están confirmadas. Las crea `database/seeders/UserSeeder.php` y también vienen en el script SQL.
También puedes registrar clientes nuevos en `/registro` (ver [10. Confirmación de cuenta](#10-confirmación-de-cuenta)).

## 6. Funcionalidades por rol

### Visitante (sin sesión)
- **Catálogo** (`/` y `/servicios`): tarjetas con los servicios activos (nombre, descripción, duración y precio).
- **Registro** (`/registro`) validado en el servidor: nombre y apellido, correo válido y único de hasta 30 caracteres,
  contraseña de al menos 6 caracteres con confirmación y teléfono opcional de 10 dígitos.
- **Confirmación de cuenta** con el enlace que llega por correo (`/confirmar/{token}`).
- **Inicio de sesión** (`/login`). Si los datos son incorrectos, el mensaje no revela si el correo existe.
  Una cuenta sin confirmar no puede entrar.

### Cliente
- Todo lo del visitante, con su nombre en el menú. Después del login va al catálogo.
- **Cierre de sesión**, que invalida la sesión.
- Si intenta entrar al panel de administración, ve la página **403 Acceso denegado**.

### Administrador
- **Panel** (`/admin`) con el número de servicios activos, servicios inactivos y usuarios registrados. Después del login va aquí.
- **CRUD de servicios** (`/admin/servicios`): listado paginado de 10 en 10, crear, editar, activar o desactivar
  (el servicio inactivo no sale en el catálogo) y eliminar con una ventana de confirmación.

### Para todos
- **Diseño responsive** revisado a 360, 768 y 1280 px ([docs/responsive.md](docs/responsive.md)).
- **Todo en español**, incluidos los mensajes de validación y las páginas de error 403, 404, 419, 429 y 500.
- **Mensajes** de éxito, error, aviso e información que se cierran solos.

## 7. Estructura de carpetas

```
app/
├── Http/Controllers/   AuthController, ServiceController, AdminController, AppointmentController (Entrega Final)
├── Http/Middleware/    AdminMiddleware (alias 'admin')
├── Http/Requests/      RegisterRequest, LoginRequest, ServiceRequest
├── Mail/               ConfirmarCuenta (correo con el enlace de confirmación)
├── Models/             User, Service, Appointment, AppointmentService (pivote)
└── Support/            HandlesDatabaseErrors (escrituras seguras con runSafely)
bootstrap/app.php       middleware: alias 'admin' y redirecciones de invitados y usuarios
database/
├── factories/          UserFactory, ServiceFactory
├── migrations/         usuarios, servicios, citas, citasServicios y tablas de Laravel
├── seeders/            UserSeeder, ServiceSeeder, AppointmentSeeder
└── sql/                appsalon_mvc_php.sql (script completo: estructura y datos)
docs/                   documentación (manual PDF, seguridad, checklist, revisiones, capturas)
lang/es/                validación, autenticación, contraseñas y paginación en español
public/css/app.css      estilos propios sobre Bootstrap
resources/views/
├── layouts/            layout principal con la navegación por rol
├── components/         <x-alert />, <x-form-error />
├── auth/               registro y login
├── services/           catálogo público
├── admin/              panel y CRUD de servicios
├── emails/             correo de confirmación de cuenta
└── errors/             403, 404, 419, 429, 500, 4xx y 5xx
routes/web.php          rutas web (los nombres de ruta son el contrato del equipo)
tests/Feature/          pruebas de registro, login, confirmación, seguridad, servicios, navegación e interfaz
.env.example            archivo de configuración de ejemplo (sin contraseñas)
```

### Base de datos

| Tabla | Campos principales |
|---|---|
| `usuarios` | id, nombre, apellido, email (único), password, telefono, admin, confirmado, token |
| `servicios` | id, nombre, precio, descripcion, duracion, activo |
| `citas` | id, fecha, hora, usuarioId → usuarios, total, estado |
| `citasServicios` | id, citaId → citas, servicioId → servicios |

Si se borra un usuario, sus citas quedan con `usuarioId` en NULL. Si se borra una cita o un servicio, sus filas
de `citasServicios` se borran en cascada.

## 8. Seguridad

- Contraseñas con **bcrypt**. `password` y `token` nunca salen al serializar un usuario.
- **Validación en el servidor** con Form Requests. Solo se guardan los campos validados.
- **XSS:** todas las vistas imprimen con `{{ }}`.
- **CSRF:** todos los formularios llevan `@csrf`. Sin token, la respuesta es 419.
- **Sesiones:** se regeneran al entrar y se invalidan al salir. Cookie `http_only`, `same_site=lax`, 120 minutos.
- **Permisos:** el middleware `admin` protege `/admin` y todo el CRUD de servicios. Un cliente recibe 403.
- **Límite de intentos:** 5 por minuto en el login y en el registro. Al sexto se responde 429.
- **Consultas** con Eloquent (prepared statements), sin SQL concatenado.

El detalle de cada requisito, con su archivo y su prueba, está en **[docs/seguridad.md](docs/seguridad.md)**.
La revisión del CRUD está en [docs/revision-A10.md](docs/revision-A10.md).

## 9. Tests

Las pruebas usan una base aparte, `appsalon_mvc_php_test` (ver `phpunit.xml`), que se vacía en cada prueba.
Créala una vez y corre las pruebas con MySQL encendido:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE appsalon_mvc_php_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
php artisan test
```

Resultado esperado: **72 pruebas en verde**. Cubren registro, login y logout, confirmación por token, permisos,
CSRF, throttle, XSS, integridad referencial, CRUD de servicios, catálogo, navegación, interfaz e idioma.

## 10. Confirmación de cuenta

Al registrarse, la cuenta queda sin confirmar y se envía un correo con un enlace único:
`http://127.0.0.1:8000/confirmar/{token}`. El token tiene 15 caracteres. Hasta abrir ese enlace, el cliente no puede iniciar sesión.

En el entorno local no se envían correos de verdad (`MAIL_MAILER=log`): el correo completo queda escrito al final
de `storage/logs/laravel.log`. Para obtener el enlace del último registro:

```powershell
Select-String -Path storage\logs\laravel.log -Pattern 'http\S*/confirmar/\w+' | Select-Object -Last 1 | ForEach-Object { $_.Matches.Value }
```

Copia el enlace en el navegador. Verás "Cuenta confirmada" y ya podrás iniciar sesión.
Cada enlace sirve una sola vez. Si el token no existe, se muestra "Token no válido".

## 11. Equipo y reparto de tareas

El proyecto lo desarrollaron 2 personas. Cada tarea tiene su rama `feature/<ID>-<tema>`, su commit con el ID y su merge `--no-ff` a `develop`.

| Integrante | Rol | Tareas |
|---|---|---|
| Dev 1 | Backend, seguridad y entrega | T1 proyecto base, T2 migraciones, T3 modelos, A1 rutas, A2 Form Requests, A3 registro, T4 seeders, T5 manejo de errores, A4 login y sesiones, A5 middleware admin, A6 auditoría de seguridad, A7 confirmación por token, A8 tests de autenticación, A10 revisión del CRUD, T6b checklist, T6c script SQL, T7 README |
| Dev 2 | Interfaz, servicios y documentación | U1 guía visual, U2 layout, U3 alertas y errores de formulario, U4 catálogo, U5–U6 CRUD de servicios, U7 navegación y panel, U8 responsive, U9 pulido, U10 capturas, A9 idioma, T6a revisión cruzada, T8 manual, T9 primera versión del README y del SQL |

## 12. Manual de usuario

**[Manual de usuario (PDF, 5 páginas)](docs/manual/Manual-de-usuario-AppSalon.pdf)**: guía para clientes y administradores.

Otra documentación:

| Documento | Contenido |
|---|---|
| [docs/checklist-entrega-intermedia.md](docs/checklist-entrega-intermedia.md) | Requisitos de la rúbrica con su evidencia |
| [docs/seguridad.md](docs/seguridad.md) | Cada requisito de seguridad, cómo se cumple y qué prueba lo verifica |
| [docs/convenciones.md](docs/convenciones.md) | Convenciones del backend (`runSafely`, páginas de error) |
| [docs/guia-visual.md](docs/guia-visual.md) | Paleta, tipografía, componentes y bocetos |
| [docs/responsive.md](docs/responsive.md) | Revisión a 360, 768 y 1280 px |
| [docs/revision-T6a.md](docs/revision-T6a.md) y [docs/revision-A10.md](docs/revision-A10.md) | Revisiones cruzadas de código |

### Regenerar el script SQL

Después de cambiar las migraciones o los seeders:

```powershell
php artisan migrate:fresh --seed
C:\xampp\mysql\bin\mysqldump.exe -u root --routines --databases appsalon_mvc_php --result-file=database\sql\appsalon_mvc_php.sql
```

No uses `>` en PowerShell, porque guarda el archivo en UTF-16. En Windows, MariaDB escribe `citasservicios` en minúsculas.
Cámbialo a `citasServicios` en el script para que también funcione en servidores Linux, y vuelve a poner la cabecera
con las instrucciones y las credenciales.

## 13. Pendientes para la Entrega Final

- Reserva de citas por el cliente: calendario, varios servicios por cita, total automático y validación de disponibilidad
  (las tablas `citas` y `citasServicios` ya existen).
- Agenda de citas para el administrador y cambio de estado (pendiente, confirmada, completada, cancelada).
- Recuperación de contraseña por correo y reenvío del correo de confirmación.
- Panel avanzado: estadísticas, gestión de usuarios y reportes con gráficas y exportación.
- API REST con autenticación por token.
- Observaciones abiertas de [docs/revision-A10.md](docs/revision-A10.md) y de la sección *Pendientes* del
  [checklist](docs/checklist-entrega-intermedia.md), como confirmar la unidad del precio con el profesor.
