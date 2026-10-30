// Genera las capturas del manual (U10) en docs/capturas/*.png.
// Uso, con la base recién sembrada para que los datos sean los de prueba:
//   php artisan migrate:fresh --seed
//   php artisan serve            (en otra terminal)
//   node docs/capturas/herramienta/capturas.mjs
// Escritorio a 1280 x 800 y móvil a 360 x 780. No crea ni borra datos.
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';
import { abrirChrome } from './chrome.mjs';

const CARPETA = join(dirname(fileURLToPath(import.meta.url)), '..');
const ADMIN = ['admin@appsalon.com', 'Admin123'];
const CLIENTE = ['cliente@appsalon.com', 'Cliente123'];

const rellenar = (valores) => `(() => {
    const v = ${JSON.stringify(valores)};
    for (const [id, valor] of Object.entries(v)) document.getElementById(id).value = valor;
})()`;

const CAPTURAS = [
    { archivo: '01-catalogo', ruta: '/' },
    { archivo: '02-registro', ruta: '/registro' },
    {
        archivo: '03-registro-errores', ruta: '/registro',
        antes: async (p) => {
            await p.evaluar(rellenar({ nombre: 'Ana', apellido: '', email: 'ana.correo.com', telefono: '31245', password: '123', password_confirmation: '123' }));
            await p.enviarFormulario('form[novalidate] button[type=submit]');
        },
    },
    { archivo: '04-login', ruta: '/login' },
    { archivo: '05-panel-admin', ruta: '/admin', rol: ADMIN },
    { archivo: '06-listado-servicios', ruta: '/admin/servicios', rol: ADMIN },
    { archivo: '07-crear-servicio', ruta: '/admin/servicios/create', rol: ADMIN },
    {
        // Abre el primer servicio del listado (el id cambia según la base).
        archivo: '08-editar-servicio', ruta: '/admin/servicios', rol: ADMIN,
        antes: async (p) => {
            await p.ir(await p.evaluar(`document.querySelector('.d-md-block a[aria-label^="Editar"]').href`));
        },
    },
    {
        archivo: '09-confirmar-eliminar', ruta: '/admin/servicios', rol: ADMIN,
        antes: async (p) => {
            await p.evaluar(`document.querySelector('.d-md-block [data-bs-target="#modalEliminar"]').click()`);
            await p.esperar(600);
        },
    },
    { archivo: '10-acceso-denegado', ruta: '/admin', rol: CLIENTE },
    { archivo: '11-movil-catalogo', ruta: '/', ancho: 360, alto: 780 },
];

const { pagina, cerrar } = await abrirChrome();
let sesion = null;

try {
    for (const c of CAPTURAS) {
        const rol = c.rol ?? null;
        if (rol !== sesion) {
            await pagina.cerrarSesion();
            if (rol) await pagina.iniciarSesion(...rol);
            sesion = rol;
        }
        await pagina.tamano(c.ancho ?? 1280, c.alto ?? 800);
        await pagina.ir(c.ruta);
        if (c.antes) await c.antes(pagina);
        const archivo = join(CARPETA, `${c.archivo}.png`);
        await pagina.captura(archivo);
        console.log(`✓ ${c.archivo}.png  (${c.ancho ?? 1280} px, ${rol ? (rol === ADMIN ? 'admin' : 'cliente') : 'invitado'})`);
    }
} finally {
    await cerrar();
}
