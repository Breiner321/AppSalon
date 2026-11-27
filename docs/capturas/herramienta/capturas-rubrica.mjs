// Capturas de lo agregado para la rúbrica de la Entrega Final: roles, empleado,
// filtro por servicio y documentación Swagger. Las usa el instructivo de evaluación.
// Uso, con el servidor corriendo y los datos de prueba sembrados:
//   php artisan serve            (en otra terminal)
//   node docs/capturas/herramienta/capturas-rubrica.mjs
// Agrupa las capturas por cuenta: solo inicia sesión dos veces (el login admite 5 intentos por minuto).
// No crea ni borra datos.
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';
import { abrirChrome } from './chrome.mjs';

const CARPETA = join(dirname(fileURLToPath(import.meta.url)), '..');
const ADMIN = ['admin@appsalon.com', 'Admin123'];
const EMPLEADO = ['empleado@appsalon.com', 'Empleado123'];

// Despliega en Swagger UI la operación cuyo resumen contiene el texto dado.
const abrirOperacion = (texto) => `(() => {
    const op = [...document.querySelectorAll('.opblock-summary')].find((e) => e.textContent.includes(${JSON.stringify(texto)}));
    op?.querySelector('button.opblock-summary-control')?.click();
    op?.scrollIntoView({ block: 'start' });
    window.scrollBy(0, -10);
})()`;

const CAPTURAS = [
    // Filtra por el dominio de las cuentas de prueba: se ven los tres roles y ninguna cuenta real.
    { archivo: '16-usuarios', ruta: '/admin/usuarios?q=appsalon.com', rol: ADMIN },
    { archivo: '24-reportes-servicio', ruta: '/admin/reportes?servicio=1', rol: ADMIN },
    { archivo: '25-citas-servicio', ruta: '/admin/citas?servicio=1', rol: ADMIN },
    { archivo: '23-panel-empleado', ruta: '/admin', rol: EMPLEADO },
    { archivo: '21-swagger', ruta: '/api/documentacion', espera: 3000 },
    {
        archivo: '22-swagger-citas', ruta: '/api/documentacion', espera: 3000,
        antes: async (p) => { await p.evaluar(abrirOperacion('Reservar una cita')); await p.esperar(800); },
    },
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
        await pagina.tamano(1280, 800);
        await pagina.ir(c.ruta);
        if (c.espera) await pagina.esperar(c.espera);
        if (c.antes) await c.antes(pagina);
        await pagina.captura(join(CARPETA, `${c.archivo}.png`));
        console.log(`✓ ${c.archivo}.png`);
    }
} finally {
    await cerrar();
}
