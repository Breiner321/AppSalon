<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Support\HandlesDatabaseErrors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Registro, inicio y cierre de sesión de los clientes del salón.
 */
class AuthController extends Controller
{
    use HandlesDatabaseErrors;

    /**
     * Muestra el formulario de registro.
     */
    public function showRegister(): View
    {
        return view('auth.registro');
    }

    /**
     * Crea la cuenta de un cliente nuevo y lo envía al login.
     *
     * Solo se guardan los campos validados; admin y confirmado quedan con su
     * valor por defecto (false). El cast 'hashed' del modelo aplica bcrypt.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $resultado = $this->runSafely(
            fn () => User::create($request->safe()->only(['nombre', 'apellido', 'email', 'password', 'telefono'])),
            'No pudimos crear tu cuenta. Intenta de nuevo en unos minutos.'
        );

        if ($resultado instanceof RedirectResponse) {
            return $resultado;
        }

        return redirect()
            ->route('login')
            ->with('success', 'Cuenta creada, ya puedes iniciar sesión');
    }

    /**
     * Muestra el formulario de inicio de sesión.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Autentica al usuario con email y contraseña.
     *
     * Si falla, el mensaje es el mismo exista o no el email, para no revelar
     * qué cuentas están registradas. Si funciona, se regenera el id de sesión
     * (evita la fijación de sesión) y se envía al usuario a la página que
     * intentaba ver o, si no había ninguna, a la que corresponde a su rol.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Credenciales incorrectas']);
        }

        $request->session()->regenerate();

        /** @var User $usuario */
        $usuario = Auth::user();

        return redirect()
            ->intended(self::inicioSegunRol($usuario))
            ->with('success', "Bienvenido, {$usuario->nombre}");
    }

    /**
     * Cierra la sesión del usuario actual, invalida la sesión y genera un
     * token CSRF nuevo.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('info', 'Sesión cerrada');
    }

    /**
     * Página de inicio de cada rol: el admin va al panel y el cliente al catálogo.
     * También la usa redirectUsersTo en bootstrap/app.php.
     */
    public static function inicioSegunRol(?User $usuario): string
    {
        return $usuario?->isAdmin()
            ? route('admin.index')
            : route('servicios.catalogo');
    }
}
