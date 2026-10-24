# Guía visual de AppSalon

Referencia para que las tres personas del equipo construyan pantallas con el mismo aspecto.
Todo se hace con **Bootstrap 5.3** y **Bootstrap Icons** por CDN, sin Node ni Vite.
Los estilos propios van en `public/css/app.css`. Si un estilo se repite en dos vistas, va a ese archivo y no en un `style=""`.

## 1. Paleta de colores

| Rol | Variable CSS | Hex | Uso |
|---|---|---|---|
| Primario | `--salon-primary` | `#7B2D5B` | Navbar, botones principales, enlaces |
| Primario oscuro | `--salon-primary-dark` | `#5E2245` | Hover y foco de botones primarios |
| Acento | `--salon-accent` | `#E8A0BF` | Precios, insignias, detalles decorativos |
| Fondo | `--salon-bg` | `#FAF6F8` | Fondo general de la página |
| Superficie | `--salon-surface` | `#FFFFFF` | Tarjetas y formularios |
| Texto | `--salon-text` | `#2B2B2B` | Texto principal |
| Texto suave | `--salon-muted` | `#6C6C6C` | Ayudas, descripciones y pie de página |

Para los estados se usan los colores de Bootstrap sin modificarlos: `success`, `danger`, `warning` e `info`.
Así las alertas y los errores de validación se ven igual en todo el sitio.

El color primario se aplica a Bootstrap redefiniendo `--bs-primary` y `--bs-primary-rgb` en `app.css`.
Con eso, `btn-primary`, `text-primary` y `bg-primary` ya usan el color del salón.

## 2. Tipografía

- **Títulos (`h1`–`h3`, marca del navbar):** *Poppins* 600, cargada desde Google Fonts.
- **Texto:** la pila de fuentes del sistema que trae Bootstrap (`system-ui, -apple-system, "Segoe UI", Roboto…`).
- **Tamaños:** los de Bootstrap. Usa `h1` solo para el título de cada página y `h2`/`h3` para las secciones.
- **Precios:** `fw-semibold`, en el color de acento oscuro, con el formato `$90.00`.

## 3. Componentes de Bootstrap que usamos

| Necesidad | Componente | Notas |
|---|---|---|
| Navegación | `navbar navbar-expand-lg navbar-dark` | Colapsa en pantallas pequeñas con `navbar-toggler` |
| Mensajes flash | `alert alert-* alert-dismissible fade show` | Siempre a través de `<x-alert />` |
| Formularios | `form-control`, `form-label`, `invalid-feedback`, `form-text` | Ver la sección 5 |
| Botones | `btn btn-primary`, `btn btn-outline-primary`, `btn btn-outline-danger` | Un solo botón primario por formulario |
| Catálogo | `card` dentro de `row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4` | Una tarjeta por servicio |
| CRUD | `table table-hover align-middle` dentro de `table-responsive` | Acciones con iconos `bi-pencil` y `bi-trash` |
| Estados | `badge text-bg-success` / `text-bg-secondary` | Activo / inactivo |
| Confirmar borrado | `modal` o `onsubmit="return confirm(...)"` | Se decide en U6 |

Iconos frecuentes: `bi-scissors` (marca), `bi-person-plus` (registro), `bi-box-arrow-in-right` (login),
`bi-box-arrow-right` (logout), `bi-speedometer2` (admin), `bi-clock` (duración).

## 4. Bocetos de pantallas

### Login (`/login`)

```
┌──────────────────────────────────────────────┐
│ ✂ AppSalon        Servicios  Iniciar sesión  │
│                              Registrarse     │
├──────────────────────────────────────────────┤
│        ┌──────────────────────────┐          │
│        │     Iniciar sesión       │          │
│        │ Correo electrónico       │          │
│        │ [______________________] │          │
│        │ Contraseña               │          │
│        │ [______________________] │          │
│        │ [   Iniciar sesión    ]  │          │
│        │ ¿No tienes cuenta?       │          │
│        │ Regístrate               │          │
│        └──────────────────────────┘          │
├──────────────────────────────────────────────┤
│ © 2026 AppSalon                              │
└──────────────────────────────────────────────┘
```

### Registro (`/registro`)

```
        ┌──────────────────────────────────┐
        │          Crear cuenta            │
        │ Nombre            Apellido       │
        │ [____________]    [____________] │
        │ Correo electrónico               │
        │ [______________________________] │
        │ Teléfono (opcional, 10 dígitos)  │
        │ [______________________________] │
        │ Contraseña        Confirmar      │
        │ [____________]    [____________] │
        │ [         Crear cuenta         ] │
        │ ¿Ya tienes cuenta? Inicia sesión │
        └──────────────────────────────────┘
```

En móvil, los pares Nombre/Apellido y Contraseña/Confirmar se apilan (`col-12 col-md-6`).

### Catálogo (`/` y `/servicios`)

```
  Nuestros servicios
  Elige lo que necesitas y agenda tu cita.

  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐
  │ Corte       │ │ Tinte       │ │ Manicure    │
  │ Descripción │ │ Descripción │ │ Descripción │
  │ ⏱ 60 min    │ │ ⏱ 90 min    │ │ ⏱ 45 min    │
  │ $90.00      │ │ $300.00     │ │ $120.00     │
  └─────────────┘ └─────────────┘ └─────────────┘
```

Si no hay servicios activos se muestra un `alert alert-info` con el texto "Aún no hay servicios disponibles".

### CRUD de servicios (`/admin/servicios`)

```
  Servicios                              [+ Nuevo servicio]
  ┌────────────┬─────────┬──────────┬─────────┬──────────┐
  │ Nombre     │ Precio  │ Duración │ Estado  │ Acciones │
  ├────────────┼─────────┼──────────┼─────────┼──────────┤
  │ Corte      │ $90.00  │ 60 min   │ Activo  │ ✎  🗑    │
  │ Tinte      │ $300.00 │ 90 min   │ Inactivo│ ✎  🗑    │
  └────────────┴─────────┴──────────┴─────────┴──────────┘

  Formulario (crear / editar)
  Nombre [__________]  Precio [____]  Duración (min) [__]
  Descripción [_____________________________________]
  [x] Activo
  [ Guardar ]  Cancelar
```

## 5. Convención para formularios

1. **Siempre `@csrf`** en todo formulario POST. Para PUT, PATCH o DELETE se agrega `@method('PUT')`, `@method('PATCH')` o `@method('DELETE')`.
2. **Escapar siempre con `{{ }}`.** No se usa `{!! !!}` en ninguna vista.
3. **Recuperar lo escrito con `old()`**: `value="{{ old('email') }}"`. En edición, `old('nombre', $servicio->nombre)`. Las contraseñas nunca se rellenan.
4. **Marcar el campo inválido con `is-invalid`**:
   ```blade
   <input type="email" name="email" id="email"
          class="form-control @error('email') is-invalid @enderror"
          value="{{ old('email') }}" required maxlength="30">
   <x-form-error field="email" />
   ```
   `<x-form-error field="..." />` muestra el primer error del campo dentro de un `div.invalid-feedback`. Bootstrap lo hace visible porque va justo después de un `.is-invalid`.
5. **La validación vive en un Form Request** (`app/Http/Requests`). Los mensajes en español salen de `messages()` o de `lang/es/validation.php`. Los atributos `required` y `maxlength` del HTML ayudan, pero no reemplazan la validación del servidor.
6. **Patrón Post/Redirect/Get con mensaje flash.** Después de guardar, el controlador redirige y deja un mensaje en la sesión:
   ```php
   return redirect()->route('login')->with('success', 'Cuenta creada, ya puedes iniciar sesión');
   ```
   Las claves permitidas son `success`, `error`, `warning` e `info`. `<x-alert />`, incluido en el layout, las muestra automáticamente, así que las vistas no tienen que hacer nada.
7. **Si falla una escritura**, el controlador la envuelve en `try/catch`, registra el detalle con `Log::error()` y redirige con `back()->withInput()->with('error', '…')`. Al usuario nunca se le muestra el mensaje técnico.

## 6. Componentes Blade del proyecto

| Componente | Archivo | Uso |
|---|---|---|
| `<x-alert />` | `resources/views/components/alert.blade.php` | Ya está en el layout. Muestra los flash `success`, `error`, `warning` e `info` como `alert-dismissible` con un icono |
| `<x-form-error field="campo" />` | `resources/views/components/form-error.blade.php` | Muestra `@error('campo')` dentro de `div.invalid-feedback`. Se coloca justo después del input |

Ejemplo de un campo completo, con label, valor anterior, marca de error, ayuda y mensaje:

```blade
<div class="mb-3">
    <label for="telefono" class="form-label">Teléfono <span class="text-body-secondary">(opcional)</span></label>
    <input type="tel" name="telefono" id="telefono" inputmode="numeric" maxlength="10"
           class="form-control @error('telefono') is-invalid @enderror"
           value="{{ old('telefono') }}" aria-describedby="telefono-ayuda">
    <div id="telefono-ayuda" class="form-text">10 dígitos, sin espacios.</div>
    <x-form-error field="telefono" />
</div>
```

`invalid-feedback` solo se ve si es hermano de un `.is-invalid`. Si va después de un `form-text`, también se muestra, porque Bootstrap usa el selector `~` (hermano posterior).
