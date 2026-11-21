{{-- Reserva y modificación de citas con calendario interactivo.
     Recibe desde AppointmentController@create y @edit:
       $cita          cita nueva o la que se modifica
       $servicios     servicios que se pueden elegir
       $calendario    días abiertos, fechas bloqueadas y rango permitido (Disponibilidad::datosCalendario)
       $seleccionados ids de los servicios que ya tenía la cita
     El script pide las horas libres a citas.disponibilidad cada vez que cambian el día o los servicios.
     El servidor vuelve a validar todo al guardar (AppointmentRequest). --}}
@extends('layouts.app')

@php
    $editando = $cita->exists;
    $preseleccion = request()->integer('servicio') ? [request()->integer('servicio')] : [];
    $elegidos = array_map('intval', (array) old('servicios', $editando ? $seleccionados : $preseleccion));
    $fechaInicial = old('fecha', $editando ? $cita->fecha->toDateString() : '');
    $horaInicial = old('hora', $editando ? $cita->horaCorta() : '');
@endphp

@section('title', $editando ? 'Modificar cita' : 'Reservar cita')

@section('content')
    <nav aria-label="Ruta de navegación" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('citas.index') }}">Mis citas</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $editando ? 'Modificar' : 'Reservar' }}</li>
        </ol>
    </nav>

    <h1 class="mb-1">{{ $editando ? 'Modificar cita' : 'Reservar cita' }}</h1>
    <p class="text-body-secondary mb-4">
        @if ($editando)
            Al guardar, la cita vuelve a quedar pendiente hasta que el salón la confirme.
        @else
            Elige tus servicios, el día y la hora. Te enviaremos un correo con el detalle.
        @endif
    </p>

    <form method="POST" novalidate id="formCita"
          action="{{ $editando ? route('citas.update', $cita) : route('citas.store') }}">
        @csrf
        @if ($editando)
            @method('PUT')
        @endif

        <input type="hidden" name="fecha" id="fecha" value="{{ $fechaInicial }}">
        <input type="hidden" name="hora" id="hora" value="{{ $horaInicial }}">

        <div class="row g-4">
            {{-- Paso 1: servicios --}}
            <div class="col-12 col-lg-5">
                <section class="card card-form-plano h-100" aria-labelledby="paso1">
                    <div class="card-body">
                        <h2 class="h5" id="paso1"><span class="paso">1</span> Servicios</h2>
                        <p class="small text-body-secondary">Puedes elegir varios.</p>

                        @error('servicios')
                            <div class="alert alert-danger py-2" role="alert">{{ $message }}</div>
                        @enderror
                        @error('servicios.*')
                            <div class="alert alert-danger py-2" role="alert">{{ $message }}</div>
                        @enderror

                        <div class="lista-servicios" role="group" aria-label="Servicios">
                            @foreach ($servicios as $servicio)
                                <label class="opcion-servicio d-flex align-items-center gap-3 p-2 mb-2" for="servicio-{{ $servicio->id }}">
                                    <input type="checkbox" class="form-check-input mt-0 js-servicio" name="servicios[]"
                                           id="servicio-{{ $servicio->id }}" value="{{ $servicio->id }}"
                                           data-precio="{{ $servicio->precio }}" data-duracion="{{ $servicio->duracion }}"
                                           data-nombre="{{ $servicio->nombre }}"
                                           @checked(in_array($servicio->id, $elegidos, true))>
                                    <span class="flex-grow-1">
                                        <span class="d-block fw-semibold">{{ $servicio->nombre }}</span>
                                        <span class="small text-body-secondary"><i class="bi bi-clock" aria-hidden="true"></i> {{ $servicio->duracion }} min</span>
                                    </span>
                                    <span class="precio text-nowrap">${{ number_format((float) $servicio->precio, 2) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </section>
            </div>

            {{-- Paso 2 y 3: día y hora --}}
            <div class="col-12 col-lg-7">
                <section class="card card-form-plano mb-4" aria-labelledby="paso2">
                    <div class="card-body">
                        <h2 class="h5" id="paso2"><span class="paso">2</span> Día</h2>
                        @error('fecha')
                            <div class="alert alert-danger py-2" role="alert">{{ $message }}</div>
                        @enderror

                        <div id="calendario" class="calendario" data-calendario='@json($calendario)'>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="mesAnterior" aria-label="Mes anterior">
                                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                                </button>
                                <h3 class="h6 mb-0" id="tituloMes" aria-live="polite"></h3>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="mesSiguiente" aria-label="Mes siguiente">
                                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div class="calendario-semana" aria-hidden="true">
                                <span>Lu</span><span>Ma</span><span>Mi</span><span>Ju</span><span>Vi</span><span>Sá</span><span>Do</span>
                            </div>
                            <div class="calendario-dias" id="diasMes" role="grid" aria-labelledby="tituloMes"></div>
                            <p class="small text-body-secondary mt-2 mb-0">
                                <span class="leyenda leyenda-libre"></span> Disponible
                                <span class="leyenda leyenda-cerrado ms-3"></span> Cerrado o no disponible
                            </p>
                        </div>
                        <noscript>
                            <div class="alert alert-warning mt-3 mb-0">Activa JavaScript en tu navegador para usar el calendario.</div>
                        </noscript>
                    </div>
                </section>

                <section class="card card-form-plano mb-4" aria-labelledby="paso3">
                    <div class="card-body">
                        <h2 class="h5" id="paso3"><span class="paso">3</span> Hora</h2>
                        @error('hora')
                            <div class="alert alert-danger py-2" role="alert" data-test="error-hora">{{ $message }}</div>
                        @enderror
                        <div id="turnos" aria-live="polite">
                            <p class="text-body-secondary mb-0">Elige tus servicios y un día para ver las horas libres.</p>
                        </div>
                    </div>
                </section>

                <section class="card resumen-cita" aria-labelledby="resumenTitulo">
                    <div class="card-body">
                        <h2 class="h5" id="resumenTitulo">Resumen</h2>
                        <dl class="row mb-3">
                            <dt class="col-5 fw-normal text-body-secondary">Servicios</dt>
                            <dd class="col-7 text-end" id="resumenServicios">Ninguno</dd>
                            <dt class="col-5 fw-normal text-body-secondary">Duración</dt>
                            <dd class="col-7 text-end" id="resumenDuracion">0 min</dd>
                            <dt class="col-5 fw-normal text-body-secondary">Fecha y hora</dt>
                            <dd class="col-7 text-end" id="resumenFecha">Sin elegir</dd>
                            <dt class="col-5">Total</dt>
                            <dd class="col-7 text-end precio fs-5 mb-0" id="resumenTotal">$0.00</dd>
                        </dl>
                        <div class="d-flex flex-column flex-sm-row gap-2">
                            <button type="submit" class="btn btn-primary flex-fill" id="botonGuardar">
                                <i class="bi bi-check-lg" aria-hidden="true"></i> {{ $editando ? 'Guardar cambios' : 'Reservar cita' }}
                            </button>
                            <a href="{{ route('citas.index') }}" class="btn btn-outline-primary">Volver</a>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            const cal = JSON.parse(document.getElementById('calendario').dataset.calendario);
            const urlDisponibilidad = @json(route('citas.disponibilidad'));
            const citaId = @json($editando ? $cita->id : null);

            const inputFecha = document.getElementById('fecha');
            const inputHora = document.getElementById('hora');
            const casillas = Array.from(document.querySelectorAll('.js-servicio'));
            const contDias = document.getElementById('diasMes');
            const contTurnos = document.getElementById('turnos');
            const titulo = document.getElementById('tituloMes');
            const formatoMes = new Intl.DateTimeFormat('es', { month: 'long', year: 'numeric' });
            const formatoDia = new Intl.DateTimeFormat('es', { weekday: 'long', day: 'numeric', month: 'long' });
            const mayuscula = (t) => t.charAt(0).toUpperCase() + t.slice(1);
            const dinero = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // Las fechas se manejan como texto AAAA-MM-DD para no depender de la zona horaria del navegador.
            const aTexto = (d) => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
            const aFecha = (t) => { const [a, m, d] = t.split('-').map(Number); return new Date(a, m - 1, d); };

            const desde = aFecha(cal.desde);
            const hasta = aFecha(cal.hasta);
            let mes = inputFecha.value ? aFecha(inputFecha.value) : new Date(desde);
            mes = new Date(mes.getFullYear(), mes.getMonth(), 1);
            let peticion = 0;

            function seleccionados() {
                return casillas.filter((c) => c.checked);
            }

            function diaDisponible(d) {
                const texto = aTexto(d);
                return d >= desde && d <= hasta && cal.diasAbiertos.includes(d.getDay()) && !cal.bloqueos.includes(texto);
            }

            function pintarCalendario() {
                titulo.textContent = mayuscula(formatoMes.format(mes));
                contDias.replaceChildren();

                // Lunes = primera columna.
                const vacios = (mes.getDay() + 6) % 7;
                for (let i = 0; i < vacios; i++) {
                    contDias.appendChild(document.createElement('span'));
                }

                const ultimo = new Date(mes.getFullYear(), mes.getMonth() + 1, 0).getDate();
                for (let n = 1; n <= ultimo; n++) {
                    const d = new Date(mes.getFullYear(), mes.getMonth(), n);
                    const texto = aTexto(d);
                    const boton = document.createElement('button');
                    boton.type = 'button';
                    boton.className = 'dia';
                    boton.textContent = n;
                    boton.dataset.fecha = texto;
                    boton.setAttribute('aria-label', formatoDia.format(d));
                    boton.disabled = !diaDisponible(d);
                    boton.setAttribute('aria-pressed', texto === inputFecha.value ? 'true' : 'false');
                    if (texto === aTexto(new Date())) boton.classList.add('hoy');
                    boton.addEventListener('click', () => elegirDia(texto));
                    contDias.appendChild(boton);
                }

                document.getElementById('mesAnterior').disabled = mes <= new Date(desde.getFullYear(), desde.getMonth(), 1);
                document.getElementById('mesSiguiente').disabled = mes >= new Date(hasta.getFullYear(), hasta.getMonth(), 1);
            }

            function elegirDia(texto) {
                if (inputFecha.value !== texto) inputHora.value = '';
                inputFecha.value = texto;
                pintarCalendario();
                cargarTurnos();
            }

            function mensajeTurnos(texto, clase = 'text-body-secondary') {
                const p = document.createElement('p');
                p.className = 'mb-0 ' + clase;
                p.textContent = texto;
                contTurnos.replaceChildren(p);
            }

            async function cargarTurnos() {
                const elegidos = seleccionados();
                actualizarResumen();

                if (elegidos.length === 0) return mensajeTurnos('Elige al menos un servicio para ver las horas libres.');
                if (!inputFecha.value) return mensajeTurnos('Elige un día en el calendario.');

                const params = new URLSearchParams({ fecha: inputFecha.value });
                elegidos.forEach((c) => params.append('servicios[]', c.value));
                if (citaId) params.append('cita', citaId);

                const id = ++peticion;
                mensajeTurnos('Buscando horas libres…');

                try {
                    const respuesta = await fetch(urlDisponibilidad + '?' + params, { headers: { Accept: 'application/json' } });
                    if (!respuesta.ok) throw new Error(respuesta.status);
                    const datos = await respuesta.json();
                    if (id !== peticion) return; // llegó una respuesta más nueva

                    if (!datos.abierto) {
                        inputHora.value = '';
                        actualizarResumen();
                        return mensajeTurnos('El salón no atiende ese día' + (datos.motivo ? ' (' + datos.motivo + ')' : '') + '.', 'text-danger');
                    }

                    if (datos.turnos.length === 0) {
                        inputHora.value = '';
                        actualizarResumen();
                        return mensajeTurnos('No quedan horas libres ese día para ' + datos.duracion + ' minutos de servicio. Prueba otro día.', 'text-danger');
                    }

                    if (!datos.turnos.includes(inputHora.value)) inputHora.value = '';

                    const info = document.createElement('p');
                    info.className = 'small text-body-secondary';
                    info.textContent = 'Horario del día: ' + datos.apertura + ' a ' + datos.cierre + '. Tu cita dura ' + datos.duracion + ' minutos.';

                    const grupo = document.createElement('div');
                    grupo.className = 'turnos';
                    grupo.setAttribute('role', 'group');
                    grupo.setAttribute('aria-label', 'Horas libres');
                    datos.turnos.forEach((hora) => {
                        const b = document.createElement('button');
                        b.type = 'button';
                        b.className = 'btn btn-outline-primary turno';
                        b.textContent = hora;
                        b.dataset.hora = hora;
                        b.setAttribute('aria-pressed', hora === inputHora.value ? 'true' : 'false');
                        b.addEventListener('click', () => {
                            inputHora.value = hora;
                            grupo.querySelectorAll('.turno').forEach((x) => x.setAttribute('aria-pressed', x === b ? 'true' : 'false'));
                            actualizarResumen();
                        });
                        grupo.appendChild(b);
                    });

                    contTurnos.replaceChildren(info, grupo);
                    actualizarResumen();
                } catch (e) {
                    if (id === peticion) mensajeTurnos('No pudimos consultar las horas libres. Revisa tu conexión e intenta de nuevo.', 'text-danger');
                }
            }

            function actualizarResumen() {
                const elegidos = seleccionados();
                const total = elegidos.reduce((s, c) => s + parseFloat(c.dataset.precio), 0);
                const duracion = elegidos.reduce((s, c) => s + parseInt(c.dataset.duracion, 10), 0);

                document.getElementById('resumenServicios').textContent = elegidos.length ? elegidos.map((c) => c.dataset.nombre).join(', ') : 'Ninguno';
                document.getElementById('resumenDuracion').textContent = duracion + ' min';
                document.getElementById('resumenTotal').textContent = '$' + dinero.format(total);
                document.getElementById('resumenFecha').textContent = inputFecha.value
                    ? mayuscula(formatoDia.format(aFecha(inputFecha.value))) + (inputHora.value ? ', ' + inputHora.value : ' (falta la hora)')
                    : 'Sin elegir';
                document.getElementById('botonGuardar').disabled = !(elegidos.length && inputFecha.value && inputHora.value);
            }

            document.getElementById('mesAnterior').addEventListener('click', () => { mes = new Date(mes.getFullYear(), mes.getMonth() - 1, 1); pintarCalendario(); });
            document.getElementById('mesSiguiente').addEventListener('click', () => { mes = new Date(mes.getFullYear(), mes.getMonth() + 1, 1); pintarCalendario(); });
            casillas.forEach((c) => c.addEventListener('change', cargarTurnos));

            pintarCalendario();
            cargarTurnos();
        })();
    </script>
@endpush
