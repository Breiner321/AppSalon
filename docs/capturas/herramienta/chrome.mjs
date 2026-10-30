// Control mínimo de Chrome headless por el protocolo DevTools (CDP), sin dependencias.
// Lo usan auditar-responsive.mjs (U8) y capturas.mjs (U10). Requiere Node 22+ (WebSocket global).
import { spawn } from 'node:child_process';
import { mkdtempSync, writeFileSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

export const BASE = process.env.APP_URL ?? 'http://127.0.0.1:8000';

const RUTAS_CHROME = [
    'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
];

const esperar = (ms) => new Promise((r) => setTimeout(r, ms));

export async function abrirChrome() {
    const perfil = mkdtempSync(join(tmpdir(), 'appsalon-chrome-'));
    const puerto = 9300 + Math.floor(Math.random() * 500);
    const ejecutable = process.env.CHROME ?? RUTAS_CHROME[0];
    const proceso = spawn(ejecutable, [
        '--headless=new', `--remote-debugging-port=${puerto}`, `--user-data-dir=${perfil}`,
        '--no-first-run', '--no-default-browser-check', '--hide-scrollbars', '--lang=es-ES', 'about:blank',
    ], { stdio: 'ignore' });

    let destino;
    for (let i = 0; i < 50 && !destino; i++) {
        await esperar(200);
        try {
            const lista = await (await fetch(`http://127.0.0.1:${puerto}/json/list`)).json();
            destino = lista.find((t) => t.type === 'page');
        } catch { /* Chrome aún no responde */ }
    }
    if (!destino) throw new Error('No se pudo conectar con Chrome');

    const ws = new WebSocket(destino.webSocketDebuggerUrl);
    await new Promise((ok, mal) => { ws.onopen = ok; ws.onerror = mal; });

    let id = 0;
    const pendientes = new Map();
    const oyentes = new Map();
    ws.onmessage = ({ data }) => {
        const msg = JSON.parse(data);
        if (msg.id && pendientes.has(msg.id)) {
            const { ok, mal } = pendientes.get(msg.id);
            pendientes.delete(msg.id);
            msg.error ? mal(new Error(msg.error.message)) : ok(msg.result);
        } else if (msg.method && oyentes.has(msg.method)) {
            oyentes.get(msg.method).splice(0).forEach((f) => f(msg.params));
        }
    };

    const enviar = (method, params = {}) => new Promise((ok, mal) => {
        pendientes.set(++id, { ok, mal });
        ws.send(JSON.stringify({ id, method, params }));
    });
    const una = (evento) => new Promise((ok) => {
        if (!oyentes.has(evento)) oyentes.set(evento, []);
        oyentes.get(evento).push(ok);
    });

    await enviar('Page.enable');
    await enviar('Runtime.enable');

    const pagina = {
        async tamano(ancho, alto = 900) {
            await enviar('Emulation.setDeviceMetricsOverride', {
                width: ancho, height: alto, deviceScaleFactor: 1, mobile: ancho < 768,
            });
            await enviar('Emulation.setTouchEmulationEnabled', { enabled: ancho < 768 });
        },
        async ir(ruta) {
            const carga = una('Page.loadEventFired');
            await enviar('Page.navigate', { url: ruta.startsWith('http') ? ruta : BASE + ruta });
            await carga;
            await esperar(400); // fuentes e íconos del CDN
        },
        async evaluar(expresion) {
            const r = await enviar('Runtime.evaluate', { expression: expresion, awaitPromise: true, returnByValue: true });
            if (r.exceptionDetails) throw new Error(r.exceptionDetails.exception?.description ?? 'Error en la página');
            return r.result.value;
        },
        // Envía un formulario con un clic real y espera la página siguiente.
        async enviarFormulario(selectorBoton) {
            const carga = una('Page.loadEventFired');
            await this.evaluar(`document.querySelector(${JSON.stringify(selectorBoton)}).click()`);
            await carga;
            await esperar(400);
        },
        async captura(archivo, { completa = false } = {}) {
            const opciones = { format: 'png', captureBeyondViewport: completa };
            if (completa) {
                const { ancho, alto } = await this.evaluar(
                    '({ ancho: document.documentElement.clientWidth, alto: document.documentElement.scrollHeight })');
                opciones.clip = { x: 0, y: 0, width: ancho, height: alto, scale: 1 };
            }
            const { data } = await enviar('Page.captureScreenshot', opciones);
            writeFileSync(archivo, Buffer.from(data, 'base64'));
        },
        async iniciarSesion(email, password) {
            await this.ir('/login');
            await this.evaluar(`(() => {
                document.getElementById('email').value = ${JSON.stringify(email)};
                document.getElementById('password').value = ${JSON.stringify(password)};
            })()`);
            await this.enviarFormulario('form button[type=submit]');
        },
        async cerrarSesion() {
            await enviar('Network.enable');
            await enviar('Network.clearBrowserCookies');
        },
        esperar,
    };

    const cerrar = async () => {
        ws.close();
        proceso.kill();
        await esperar(500);
        try { rmSync(perfil, { recursive: true, force: true }); } catch { /* Chrome aún libera el perfil */ }
    };

    return { pagina, cerrar };
}
