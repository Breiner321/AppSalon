<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\User;
use Illuminate\View\View;

/**
 * Panel de administración del salón.
 */
class AdminController extends Controller
{
    /**
     * Página principal del panel: conteos simples y accesos rápidos. El acceso
     * solo para admin ya lo controla el middleware 'admin' (A5) en routes/web.php.
     * Las gráficas y los reportes quedan para la Entrega Final.
     */
    public function index(): View
    {
        return view('admin.index', [
            'serviciosActivos' => Service::where('activo', true)->count(),
            'serviciosInactivos' => Service::where('activo', false)->count(),
            'usuariosRegistrados' => User::count(),
        ]);
    }
}
