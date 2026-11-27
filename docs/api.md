# API REST · AppSalon

API para consultar servicios y gestionar citas desde otra aplicación (por ejemplo, una app móvil o
Postman). Usa las mismas reglas que la web: horario del salón, fechas bloqueadas y no sobreposición.

- **Documentación Swagger (OpenAPI 3):** `http://127.0.0.1:8000/api/documentacion`. Muestra todos los endpoints con
  sus parámetros, esquemas y códigos de respuesta, y permite probarlos: haz el login, pulsa **Authorize** y pega el
  token. La especificación está en [`public/api-docs/openapi.yaml`](../public/api-docs/openapi.yaml) y la prueba
  `tests/Feature/DocumentacionApiTest.php` verifica que incluya todas las rutas de `routes/api.php`.
- **URL base:** `http://127.0.0.1:8000/api`
- **Formato:** JSON en UTF-8. Envía `Content-Type: application/json` en los POST y PUT, y
  `Accept: application/json` en todas las peticiones (las rutas `/api/*` responden JSON aunque falte).
- **Autenticación:** token de **Laravel Sanctum**. Se obtiene con `POST /api/login` y se envía en la
  cabecera `Authorization: Bearer <token>`. Dura 24 horas (`SANCTUM_EXPIRATION` en minutos).
- **Límites:** `POST /api/login` admite 5 intentos por minuto; las rutas con token, 60 peticiones por minuto.
- **Código:** rutas en `routes/api.php`, controladores en `app/Http/Controllers/Api`, forma del JSON en
  `app/Http/Resources`, pruebas en `tests/Feature/ApiTest.php`.

## Resumen de endpoints

| Método | Ruta | Token | Descripción |
|---|---|---|---|
| POST | `/api/login` | No | Inicia sesión y devuelve un token |
| GET | `/api/servicios` | No | Servicios activos |
| GET | `/api/servicios/{id}` | No | Un servicio activo |
| GET | `/api/usuario` | Sí | Datos del dueño del token |
| POST | `/api/logout` | Sí | Revoca el token |
| GET | `/api/disponibilidad?fecha=&servicios[]=` | Sí | Horas libres de un día |
| GET | `/api/citas/usuario/{id}` | Sí | Citas de un usuario (las propias; un admin, las de cualquiera) |
| GET | `/api/citas/{id}` | Sí | Detalle de una cita |
| POST | `/api/citas` | Sí | Reserva una cita |
| PUT | `/api/citas/{id}` | Sí | Modifica fecha, hora y servicios |
| PATCH | `/api/citas/{id}/cancelar` | Sí | Cancela una cita |

## Formato de las respuestas

Las respuestas correctas traen los datos en `data` y, cuando aplica, un `message` y un `meta`:

```json
{ "message": "Cita reservada", "data": { ... } }
```

Los errores siempre traen `message`; los de validación (422) además traen `errors` por campo:

```json
{
  "message": "Ese horario ya está ocupado. Elige otra hora.",
  "errors": { "hora": ["Ese horario ya está ocupado. Elige otra hora."] }
}
```

| Código | Cuándo |
|---|---|
| 200 | Correcto |
| 201 | Cita creada |
| 401 | Falta el token, no es válido o expiró; o credenciales incorrectas en el login |
| 403 | La cita o el usuario no son tuyos; cuenta sin confirmar o desactivada |
| 404 | La cita, el usuario o el servicio no existen (`{"message": "Recurso no encontrado."}`) |
| 405 | Método HTTP no permitido en esa ruta |
| 409 | La cita ya no se puede modificar o cancelar (ya pasó, está cancelada o completada) |
| 422 | Datos inválidos u horario no disponible |
| 429 | Demasiadas peticiones |

### Objeto servicio

| Campo | Tipo | Ejemplo |
|---|---|---|
| `id` | entero | `1` |
| `nombre` | texto | `"Corte de cabello mujer"` |
| `descripcion` | texto o null | `"Corte a la medida con lavado y secado."` |
| `precio` | número | `35` |
| `duracion` | entero (minutos) | `45` |
| `activo` | booleano | `true` |

### Objeto cita

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | entero | |
| `fecha` | texto `AAAA-MM-DD` | |
| `hora` | texto `HH:MM` | Hora de inicio |
| `horaFin` | texto `HH:MM` | Hora de inicio + duración |
| `duracion` | entero | Minutos (suma de los servicios) |
| `total` | número | Suma de los precios de los servicios |
| `estado` | texto | `pendiente`, `confirmada`, `completada` o `cancelada` |
| `modificable` | booleano | Si todavía se puede modificar o cancelar |
| `usuarioId` | entero o null | Dueño de la cita |
| `usuario` | objeto | Solo en `GET /api/citas/{id}` |
| `servicios` | lista de servicios | |
| `creadaEl` | texto ISO 8601 | Fecha en que se reservó |

---

## POST /api/login

Cuerpo:

```json
{ "email": "cliente@appsalon.com", "password": "Cliente123", "dispositivo": "postman" }
```

`dispositivo` es opcional: es el nombre con que se guarda el token.

Respuesta **200**:

```json
{
  "message": "Bienvenido, Laura",
  "data": {
    "token": "2|f912VukdzzO1xDIGwGxdxf90aRV33G1N7ht9dp5X5232def9",
    "tipo": "Bearer",
    "expiraEl": "2026-11-22T01:27:37+00:00",
    "usuario": {
      "id": 2,
      "nombre": "Laura",
      "apellido": "Gomez",
      "email": "cliente@appsalon.com",
      "telefono": "3007654321",
      "admin": false,
      "rol": "cliente"
    }
  }
}
```

Errores: **401** `{"message": "Credenciales incorrectas"}` (no revela si el correo existe), **403** si la
cuenta no está confirmada o está desactivada, **422** si falta el correo o la contraseña.

## POST /api/logout

Revoca el token usado en la petición. Respuesta **200**:

```json
{ "message": "Sesión cerrada. El token ya no es válido." }
```

## GET /api/usuario

Respuesta **200**: `{ "data": { "id": 2, "nombre": "Laura", ... } }` (mismo objeto usuario del login).

## GET /api/servicios

Público. Servicios activos en orden alfabético.

```json
{
  "data": [
    {
      "id": 2,
      "nombre": "Corte de cabello hombre",
      "descripcion": "Corte clásico o moderno con acabado a máquina y tijera.",
      "precio": 25,
      "duracion": 30,
      "activo": true
    },
    { "id": 1, "nombre": "Corte de cabello mujer", "...": "..." }
  ],
  "meta": { "total": 10 }
}
```

`GET /api/servicios/{id}` devuelve `{ "data": { ...servicio } }` o **404** si no existe o está inactivo.

## GET /api/disponibilidad

Parámetros: `fecha` (`AAAA-MM-DD`) y `servicios[]` (uno o más ids). Devuelve las horas en que puede
empezar una cita con esos servicios.

```
GET /api/disponibilidad?fecha=2026-12-04&servicios[]=1
```

```json
{
  "data": {
    "fecha": "2026-12-04",
    "abierto": true,
    "motivo": null,
    "apertura": "09:00",
    "cierre": "19:00",
    "duracion": 45,
    "turnos": ["09:00", "16:00", "16:30"]
  }
}
```

Si el salón no atiende ese día: `"abierto": false`, `"turnos": []` y, si es una fecha bloqueada, su `motivo`.

## GET /api/citas/usuario/{id}

Citas del usuario, de la más reciente a la más antigua. Un cliente solo puede pedir las suyas
(**403** `{"message": "Solo puedes consultar tus propias citas."}`); un administrador puede pedir las de
cualquiera. Filtro opcional: `?estado=pendiente|confirmada|completada|cancelada`.

```
GET /api/citas/usuario/2?estado=confirmada
Authorization: Bearer 2|f912Vu...
```

```json
{
  "data": [
    {
      "id": 148,
      "fecha": "2026-12-12",
      "hora": "11:00",
      "horaFin": "14:20",
      "duracion": 200,
      "total": 218,
      "estado": "confirmada",
      "modificable": true,
      "usuarioId": 2,
      "servicios": [
        { "id": 3, "nombre": "Corte infantil", "descripcion": "Corte para niños y niñas de hasta 12 años.", "precio": 20, "duracion": 30, "activo": true },
        { "id": 5, "nombre": "Mechas o balayage", "descripcion": "Iluminación del cabello con técnica de mechas o balayage.", "precio": 180, "duracion": 150, "activo": true },
        { "id": 10, "nombre": "Diseño de cejas", "descripcion": "Depilación y perfilado de cejas con cera o pinza.", "precio": 18, "duracion": 20, "activo": true }
      ],
      "creadaEl": "2026-12-07T12:00:00+00:00"
    }
  ],
  "meta": { "usuarioId": 2, "total": 1 }
}
```

## GET /api/citas/{id}

Detalle de una cita propia (o de cualquiera, para un admin), con `usuario` incluido. **403** si es ajena.

## POST /api/citas

Reserva una cita para el dueño del token. La cita queda **pendiente** y el cliente recibe un correo.

| Campo | Regla |
|---|---|
| `fecha` | Obligatorio, `AAAA-MM-DD`, desde hoy y hasta 60 días adelante |
| `hora` | Obligatorio, `HH:MM` (también se acepta `HH:MM:00`) |
| `servicios` | Obligatorio, lista de 1 a 10 ids de servicios activos, sin repetir |

Además, el salón debe atender ese día, la cita debe quedar dentro del horario, con al menos 60 minutos
de anticipación, y no puede sobreponerse con otra.

```json
{ "fecha": "2026-12-04", "hora": "10:00", "servicios": [1, 7] }
```

Respuesta **201**:

```json
{
  "message": "Cita reservada",
  "data": {
    "id": 149,
    "fecha": "2026-12-04",
    "hora": "10:00",
    "horaFin": "11:25",
    "duracion": 85,
    "total": 57,
    "estado": "pendiente",
    "modificable": true,
    "usuarioId": 2,
    "servicios": [
      { "id": 1, "nombre": "Corte de cabello mujer", "precio": 35, "duracion": 45, "...": "..." },
      { "id": 7, "nombre": "Manicure tradicional", "precio": 22, "duracion": 40, "...": "..." }
    ],
    "creadaEl": "2026-11-21T01:30:00+00:00"
  }
}
```

Errores **422** frecuentes:

```json
{ "message": "Ese horario ya está ocupado. Elige otra hora.", "errors": { "hora": ["Ese horario ya está ocupado. Elige otra hora."] } }
```

```json
{ "message": "El salón no atiende los domingos.", "errors": { "hora": ["El salón no atiende los domingos."] } }
```

```json
{
  "message": "La fecha debe tener el formato AAAA-MM-DD. (y 2 errores más)",
  "errors": {
    "fecha": ["La fecha debe tener el formato AAAA-MM-DD."],
    "hora": ["La hora debe tener el formato HH:MM."],
    "servicios": ["Elige al menos un servicio."]
  }
}
```

## PUT /api/citas/{id}

Cambia fecha, hora y servicios (mismos campos y reglas que `POST /api/citas`). El horario actual de la
cita cuenta como libre. La cita vuelve a **pendiente** y el cliente recibe un correo.

```json
{ "fecha": "2026-12-04", "hora": "16:00", "servicios": [1] }
```

Respuesta **200** `{ "message": "Cita actualizada", "data": { ...cita } }`.
**403** si la cita es ajena, **404** si no existe, **409** si ya no se puede modificar, **422** si el
horario nuevo no está disponible.

## PATCH /api/citas/{id}/cancelar

Cancela la cita y libera su horario. Respuesta **200**
`{ "message": "Cita cancelada", "data": { ..., "estado": "cancelada", "modificable": false } }`.
**409** si ya pasó o ya estaba cancelada.

---

## Probar con curl

En PowerShell usa `curl.exe` (no el alias `curl`). Primero el token:

```bash
curl -X POST http://127.0.0.1:8000/api/login -H "Content-Type: application/json" -d "{\"email\":\"cliente@appsalon.com\",\"password\":\"Cliente123\"}"
```

Después, con el token copiado:

```bash
curl http://127.0.0.1:8000/api/citas/usuario/2 -H "Authorization: Bearer <token>"
```

```bash
curl -X POST http://127.0.0.1:8000/api/citas -H "Authorization: Bearer <token>" -H "Content-Type: application/json" -d "{\"fecha\":\"2026-12-04\",\"hora\":\"10:00\",\"servicios\":[1,7]}"
```

En **Postman**: haz el login, copia `data.token` y ponlo en la pestaña *Authorization* → *Bearer Token*.
