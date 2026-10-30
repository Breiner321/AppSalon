# Revisión responsive (U8)

Anchos revisados: **360 px** (móvil), **768 px** (tableta) y **1280 px** (escritorio).

## Revisión automática

Con `php artisan serve` corriendo y la base sembrada (`php artisan migrate:fresh --seed`):

```bash
node docs/capturas/herramienta/auditar-responsive.mjs
```

El script abre Chrome headless, inicia sesión con las cuentas de `UserSeeder` cuando la vista lo necesita
y revisa 12 vistas en los 3 anchos (36 combinaciones). En cada una mide:

- scroll horizontal (`scrollWidth > clientWidth`);
- botones y enlaces táctiles de menos de 44 px de alto (`.btn`, `button`, `.nav-link`, `.page-link`, `.dropdown-item`, `.navbar-toggler`);
- texto cortado o que se sale de la pantalla.

| Vista | Rol | 360 | 768 | 1280 |
|---|---|---|---|---|
| Catálogo | invitado y cliente | ✓ | ✓ | ✓ |
| Registro (vacío y con errores) | invitado | ✓ | ✓ | ✓ |
| Login | invitado | ✓ | ✓ | ✓ |
| Error 404 | invitado | ✓ | ✓ | ✓ |
| Error 403 | cliente | ✓ | ✓ | ✓ |
| Panel | admin | ✓ | ✓ | ✓ |
| Listado de servicios | admin | ✓ | ✓ | ✓ |
| Formulario nuevo (vacío y con errores) y editar | admin | ✓ | ✓ | ✓ |

Resultado al cerrar U8: **0 problemas**. Antes de U8 había 166, todos de botones de 31 a 40 px.

## Cambios hechos

- `public/css/app.css`: alto mínimo de 44 px (`2.75rem`) para `.btn`, `.nav-link` del navbar, `.dropdown-item`,
  `.page-link`, `.navbar-toggler` y el botón Cerrar de los modales.
- `admin/servicios/index`: por debajo de 768 px la tabla se reemplaza por tarjetas (`d-md-none`) con los botones
  Editar y Eliminar a todo el ancho; desde 768 px se ve la tabla (`d-none d-md-block`) dentro de `table-responsive`.

## Checklist manual (DevTools del navegador)

En Chrome: F12 → ícono de dispositivo (Ctrl+Shift+M) → **Responsive** → escribir el ancho (360, 768 y 1280).

- [ ] **Catálogo** `/`: 1 columna a 360, 2 a 768, 3 a 1280. La invitación a registrarse solo se ve sin sesión.
- [ ] **Navbar**: a 360 y 768 el menú colapsa en ☰; al abrirlo, los enlaces se tocan con comodidad.
      Cliente: Servicios y menú con su nombre. Admin: además Panel y Gestionar servicios.
- [ ] **Registro** `/registro`: Nombre/Apellido y Contraseña/Confirmar se apilan a 360. Enviar vacío marca los campos en rojo.
- [ ] **Login** `/login`: la tarjeta queda centrada y no toca los bordes.
- [ ] **Panel** `/admin` (admin): las 3 tarjetas de conteo se apilan a 360; los botones ocupan todo el ancho.
- [ ] **Listado** `/admin/servicios` (admin): tarjetas a 360, tabla a 768 y 1280. La paginación no se desborda.
- [ ] **Eliminar**: el modal se ve completo a 360 y los botones Cancelar/Eliminar no se cortan.
- [ ] **Formulario** `/admin/servicios/create` y `/admin/servicios/{id}/edit`: Precio y Duración se apilan a 360.
- [ ] **Errores** `/admin` como cliente (403) y `/no-existe` (404): ícono, texto y botón centrados.
- [ ] En ninguna vista aparece barra de scroll horizontal (arrastrar la página a los lados no debe moverla).
