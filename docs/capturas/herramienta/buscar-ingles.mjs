// Busca texto en inglés visible (A9): recorre las pantallas y sus estados de error y
// revisa el texto visible, el <title>, los placeholders y los aria-label.
// Uso (con php artisan serve corriendo y la base sembrada):
//   node docs/capturas/herramienta/buscar-ingles.mjs
import { abrirChrome } from './chrome.mjs';

const ADMIN = ['admin@appsalon.com', 'Admin123'];
const CLIENTE = ['cliente@appsalon.com', 'Cliente123'];

// Palabras que delatan un texto de Laravel o Bootstrap sin traducir.
const INGLES = /\b(the|field|must|required|invalid|showing|results|previous|next|login|logout|register|password|email address|not found|forbidden|server error|page expired|too many|method not allowed|unauthorized|toggle navigation|close|whoops|these credentials|dashboard|submit|cancel|delete|edit|save)\b/i;

// Rellena un formulario con valores y lo envía. null deja el campo vacío.
const enviar = (valores) => async (p) => {
    await p.evaluar(`(() => {
        const v = ${JSON.stringify(valores)};
        for (const [id, valor] of Object.entries(v)) document.getElementById(id).value = valor ?? '';
    })()`);
    await p.enviarFormulario('form[novalidate] button[type=submit]');
};

const PANTALLAS = [
    { nombre: 'catálogo', ruta: '/' },
    { nombre: 'registro vacío', ruta: '/registro', antes: enviar({}) },
    { nombre: 'registro con datos inválidos', ruta: '/registro', antes: enviar({
        nombre: 'x'.repeat(61), apellido: 'López', email: 'no-es-correo', telefono: '12ab',
        password: '123', password_confirmation: '999' }) },
    { nombre: 'registro con correo repetido', ruta: '/registro', antes: enviar({
        nombre: 'Ana', apellido: 'López', email: 'cliente@appsalon.com', telefono: '',
        password: 'secreto123', password_confirmation: 'secreto123' }) },
    { nombre: 'login vacío', ruta: '/login', antes: enviar({}) },
    { nombre: 'login con correo inválido', ruta: '/login', antes: enviar({ email: 'abc', password: 'x' }) },
    { nombre: 'login con credenciales malas', ruta: '/login', antes: enviar({ email: 'nadie@appsalon.com', password: 'malamala' }) },
    { nombre: 'error 404', ruta: '/no-existe' },
    { nombre: 'error 405 (GET /logout)', ruta: '/logout' },
    { nombre: 'catálogo como cliente', ruta: '/servicios', rol: CLIENTE },
    { nombre: 'error 403', ruta: '/admin', rol: CLIENTE },
    { nombre: 'panel', ruta: '/admin', rol: ADMIN },
    { nombre: 'listado', ruta: '/admin/servicios', rol: ADMIN },
    { nombre: 'servicio vacío', ruta: '/admin/servicios/create', rol: ADMIN, antes: enviar({ nombre: '', precio: '', duracion: '' }) },
    { nombre: 'servicio con texto en números', ruta: '/admin/servicios/create', rol: ADMIN, antes: enviar({
        nombre: 'Prueba', precio: 'abc', duracion: '2.5', descripcion: 'x'.repeat(501) }) },
    { nombre: 'servicio fuera de rango', ruta: '/admin/servicios/create', rol: ADMIN, antes: enviar({
        nombre: 'Prueba', precio: '-5', duracion: '500' }) },
    { nombre: 'servicio inexistente', ruta: '/admin/servicios/9999/edit', rol: ADMIN },
];

const TEXTO = `(() => {
    const partes = [document.title, document.body.innerText];
    document.querySelectorAll('[placeholder], [aria-label], [title]').forEach((el) => {
        partes.push(el.getAttribute('placeholder') || '', el.getAttribute('aria-label') || '', el.getAttribute('title') || '');
    });
    return partes.join('\\n');
})()`;

const { pagina, cerrar } = await abrirChrome();
await pagina.tamano(1280);
let hallazgos = 0;
let sesion = null;

try {
    for (const p of PANTALLAS) {
        const rol = p.rol ?? null;
        if (rol !== sesion) {
            await pagina.cerrarSesion();
            if (rol) await pagina.iniciarSesion(...rol);
            sesion = rol;
        }
        await pagina.ir(p.ruta);
        if (p.antes) await p.antes(pagina);
        const lineas = (await pagina.evaluar(TEXTO)).split('\n').map((l) => l.trim()).filter(Boolean);
        const sospechosas = [...new Set(lineas.filter((l) => INGLES.test(l)))];
        hallazgos += sospechosas.length;
        console.log(`${sospechosas.length ? '✗' : '✓'} ${p.nombre}`);
        sospechosas.forEach((l) => console.log('    - ' + l.slice(0, 120)));
        if (/invalid|datos inválidos|fuera de rango|texto en números|vacío|repetido|credenciales/.test(p.nombre)) {
            const errores = await pagina.evaluar(`[...document.querySelectorAll('.invalid-feedback')].map(e => e.innerText.trim())`);
            errores.forEach((e) => console.log('    · ' + e));
        }
    }
} finally {
    await cerrar();
}

console.log(hallazgos ? `\n${hallazgos} textos sospechosos.` : '\nSin textos en inglés.');
process.exitCode = hallazgos ? 1 : 0;
