<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Mail\ConfirmarCuenta;
use App\Models\User;
use App\Support\HandlesDatabaseErrors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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
     * Crea la cuenta de un cliente nuevo, le envía el correo de confirmación
     * y lo lleva al login.
     *
     * Solo se guardan los campos validados; admin queda en false. token y
     * confirmado están fuera de $fillable, por eso se asignan con forceFill.
     * El cast 'hashed' del modelo aplica bcrypt. El correo se envía dentro de
     * la transacción: si falla, la cuenta no queda creada a medias.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $resultado = $this->runSafely(function () use ($request) {
            $usuario = new User($request->safe()->only(['nombre', 'apellido', 'email', 'password', 'telefono']));
            $usuario->forceFill([
                'token' => Str::random(15),
                'confirmado' => false,
            ])->save();

            Mail::to($usuario)->send(new ConfirmarCuenta($usuario));

            return $usuario;
        }, 'No pudimos crear tu cuenta. Intenta de nuevo en unos minutos.');

        if ($resultado instanceof RedirectResponse) {
            return $resultado;
        }

        return redirect()
            ->route('login')
            ->with('success', 'Revisa tu correo para confirmar la cuenta');
    }

    /**
     * Confirma la cuenta con el token del enlace enviado por correo.
     *
     * El token se borra al usarlo, así que cada enlace sirve una sola vez.
     */
    public function confirmar(string $token): RedirectResponse
    {
        $usuario = User::where('token', $token)->first();

        if (! $usuario) {
            return redirect()
                ->route('login')
                ->with('error', 'Token no válido');
        }

        $resultado = $this->runSafely(
            fn () => $usuario->forceFill(['confirmado' => true, 'token' => null])->save(),
            'No pudimos confirmar tu cuenta. Intenta de nuevo en unos minutos.'
        );

        if ($resultado instanceof RedirectResponse) {
            return $resultado;
        }

        return redirect()
            ->route('login')
            ->with('success', 'Cuenta confirmada');
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
     *
     * Una cuenta sin confirmar (A7) no puede entrar aunque la contraseña sea
     * correcta: se cierra la sesión recién abierta y se le pide confirmar.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Credenciales incorrectas']);
        }

        /** @var User $usuario */
        $usuario = Auth::user();

        if (! $usuario->confirmado) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withInput($request->only('email'))
                ->with('warning', 'Confirma tu cuenta desde el enlace enviado a tu correo');
        }

        $request->session()->regenerate();

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
