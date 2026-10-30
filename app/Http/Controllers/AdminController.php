<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\View\View;

/**
 * Panel de administración del salón.
 */
class AdminController extends Controller
{
    /**
     * Página principal del panel de administración. El acceso solo para admin
     * ya lo controla el middleware 'admin' (A5) en routes/web.php.
     */
    public function index(): View
    {
        return view('admin.index', [
            'totalServicios' => Service::count(),
            'serviciosActivos' => Service::where('activo', true)->count(),
        ]);
    }
}
