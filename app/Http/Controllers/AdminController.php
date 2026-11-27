<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\View\View;

/**
 * Panel de administración del salón.
 */
class AdminController extends Controller
{
    /**
     * Dashboard: citas del día, ingresos, clientes y servicios. El acceso solo
     * para admin lo controla el middleware 'admin' en routes/web.php.
     *
     * Ingresos: suma del total de las citas completadas. Los "ingresos
     * esperados de hoy" suman además las pendientes y confirmadas del día.
     */
    public function index(): View
    {
        $hoy = today()->toDateString();

        $citasHoy = Appointment::with(['usuario', 'servicios'])
            ->whereDate('fecha', $hoy)
            ->orderBy('hora')
            ->get();

        return view('admin.index', [
            'serviciosActivos' => Service::where('activo', true)->count(),
            'serviciosInactivos' => Service::where('activo', false)->count(),
            'usuariosRegistrados' => User::count(),
            'clientes' => User::clientes()->count(),
            'clientesNuevosMes' => User::clientes()->where('created_at', '>=', today()->startOfMonth())->count(),
            'citasHoy' => $citasHoy,
            'citasHoyActivas' => $citasHoy->where('estado', '!=', Appointment::CANCELADA)->count(),
            'ingresosHoy' => (float) $citasHoy->where('estado', Appointment::COMPLETADA)->sum('total'),
            'ingresosEsperadosHoy' => (float) $citasHoy->where('estado', '!=', Appointment::CANCELADA)->sum('total'),
            'ingresosMes' => (float) Appointment::where('estado', Appointment::COMPLETADA)
                ->whereBetween('fecha', [today()->startOfMonth()->toDateString(), today()->endOfMonth()->toDateString()])
                ->sum('total'),
            'pendientes' => Appointment::where('estado', Appointment::PENDIENTE)->whereDate('fecha', '>=', $hoy)->count(),
            'proximasSemana' => Appointment::ocupan()
                ->whereBetween('fecha', [today()->addDay()->toDateString(), today()->addDays(7)->toDateString()])
                ->count(),
        ]);
    }
}
