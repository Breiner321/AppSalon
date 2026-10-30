<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Fila de la tabla pivote citasServicios: un servicio dentro de una cita.
 */
class AppointmentService extends Pivot
{
    protected $table = 'citasServicios';

    /**
     * La tabla tiene id autoincremental propio.
     */
    public $incrementing = true;

    public $timestamps = false;
}
