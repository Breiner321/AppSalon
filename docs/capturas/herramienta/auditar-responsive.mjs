// Auditoría responsive (U8): recorre las vistas a 360, 768 y 1280 px y reporta
// scroll horizontal, botones de menos de 44 px de alto y texto cortado.
// Uso (con php artisan serve corriendo y la base sembrada):
//   node docs/capturas/herramienta/auditar-responsive.mjs
import { abrirChrome } from './chrome.mjs';

const ANCHOS = [360, 768, 1280];

// Cuentas de prueba de database/seeders/UserSeeder.php.
const ADMIN = ['admin@appsalon.com', 'Admin123'];
const CLIENTE = ['cliente@appsalon.com', 'Cliente123'];

const VISTAS = [
    { nombre: 'catálogo (invitado)', ruta: '/' },
    { nombre: 'registro', ruta: '/registro' },
    { nombre: 'registro con errores', ruta: '/registro', antes: (p) => p.enviarFormulario('form[novalidate] button[type=submit]') },
    { nombre: 'login', ruta: '/login' },
    { nombre: 'error 404', ruta: '/pagina-que-no-existe' },
    { nombre: 'catálogo (cliente)', ruta: '/servicios', rol: CLIENTE },
    { nombre: 'error 403 (cliente)', ruta: '/admin', rol: CLIENTE },
    { nombre: 'panel', ruta: '/admin', rol: ADMIN },
    { nombre: 'listado de servicios', ruta: '/admin/servicios', rol: ADMIN },
    { nombre: 'formulario nuevo', ruta: '/admin/servicios/create', rol: ADMIN },
    { nombre: 'formulario con errores', ruta: '/admin/servicios/create', rol: ADMIN, antes: (p) => p.enviarFormulario('form[novalidate] button[type=submit]') },
    { nombre: 'formulario editar', ruta: '/admin/servicios/1/edit', rol: ADMIN },
];

// Se ejecuta dentro de la página. Revisa solo elementos visibles.
const REVISION = `(() => {
    const visible = (el) => {
        const r = el.getBoundingClientRect();
        const cs = getComputedStyle(el);
        return r.width > 0 && r.height > 0 && cs.visibility !== 'hidden' && cs.display !== 'none';
    };
    const describir = (el) => {
        const texto = (el.innerText || el.value || el.getAttribute('aria-label') || '').trim().replace(/\\s+/g, ' ').slice(0, 30);
        return el.tagName.toLowerCase() + (el.className && typeof el.className === 'string' ? '.' + el.className.trim().split(/\\s+/).slice(0, 2).join('.') : '') + (texto ? ' «' + texto + '»' : '');
    };
    const problemas = [];
    const doc = document.documentElement;
    if (doc.scrollWidth > doc.clientWidth + 1) problemas.push('scroll horizontal: ' + doc.scrollWidth + ' > ' + doc.clientWidth);

    // Botones y controles que se tocan con el dedo.
    const tocables = document.querySelectorAll('.btn, button, .navbar-toggler, .page-link, .dropdown-item, .nav-link, input[type=submit]');
    for (const el of tocables) {
        if (!visible(el)) continue;
        const alto = Math.round(el.getBoundingClientRect().height);
        if (alto < 44) problemas.push('botón de ' + alto + ' px: ' + describir(el));
    }

    // Texto cortado: contenido más ancho que su caja sin poder desplazarse, o que se sale del viewport.
    for (const el of document.querySelectorAll('h1, h2, h3, h4, h5, p, label, .btn, .badge, td, th, .card-title, .navbar-brand, .breadcrumb-item')) {
        if (!visible(el) || el.closest('.table-responsive, .modal:not(.show)')) continue;
        const cs = getComputedStyle(el);
        if (el.scrollWidth > el.clientWidth + 1 && cs.overflowX !== 'visible' && cs.overflowX !== 'auto') problemas.push('texto cortado: ' + describir(el));
        const r = el.getBoundingClientRect();
        if (r.right > innerWidth + 1 || r.left < -1) problemas.push('se sale de la pantalla: ' + describir(el));
    }
    return [...new Set(problemas)];
})()`;

const { pagina, cerrar } = await abrirChrome();
let total = 0;
let sesion = null;

try {
    for (const vista of VISTAS) {
        const rol = vista.rol ?? null;
        if (rol !== sesion) {
            await pagina.cerrarSesion();
            if (rol) await pagina.iniciarSesion(...rol);
            sesion = rol;
        }
        for (const ancho of ANCHOS) {
            await pagina.tamano(ancho);
            await pagina.ir(vista.ruta);
            if (vista.antes) await vista.antes(pagina);
            const problemas = await pagina.evaluar(REVISION);
            total += problemas.length;
            console.log(`${problemas.length ? '✗' : '✓'} ${vista.nombre} @ ${ancho}px`);
            problemas.forEach((p) => console.log('    - ' + p));
        }
    }
} finally {
    await cerrar();
}

console.log(total ? `\n${total} problemas encontrados.` : '\nSin problemas: 0 scroll horizontal, 0 botones < 44 px, 0 textos cortados.');
process.exitCode = total ? 1 : 0;
