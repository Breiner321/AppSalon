<?php

/**
 * Reglas de negocio del salón para la reserva de citas.
 *
 * El horario semanal y los días bloqueados se guardan en la base de datos
 * (tablas horarios y bloqueos) y el administrador los cambia desde /admin/horarios.
 */
return [

    // Cada cuántos minutos empieza un turno en el calendario (09:00, 09:30, 10:00…).
    'intervalo_minutos' => (int) env('SALON_INTERVALO_MINUTOS', 30),

    // Con cuánta anticipación mínima (en minutos) se puede reservar o modificar una cita.
    'anticipacion_minutos' => (int) env('SALON_ANTICIPACION_MINUTOS', 60),

    // Hasta cuántos días hacia adelante se puede reservar.
    'dias_maximos' => (int) env('SALON_DIAS_MAXIMOS', 60),

    // Estados de cita que ocupan la agenda. Una cita cancelada libera su horario.
    'estados_que_ocupan' => ['pendiente', 'confirmada', 'completada'],

];
