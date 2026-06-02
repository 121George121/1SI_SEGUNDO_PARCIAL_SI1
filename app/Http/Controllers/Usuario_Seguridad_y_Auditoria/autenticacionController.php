<?php

namespace App\Http\Controllers\Usuario_Seguridad_y_Auditoria;

use App\Http\Controllers\Controller;
use App\Mail\Usuario_Seguridad_y_Auditoria\RecuperacionContrasenaMail;
use App\Models\Usuario_Sefuridad_y_Auditoria\autenticacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class autenticacionController extends Controller
{
    private const MAX_INTENTOS = 5;

    private const SEGUNDOS_BLOQUEO = 300;

    public function mostrarLogin(Request $request): View
    {
        return view('Usuario_Seguridad_y_Auditoria.Login', $this->estadoIntentosLogin(
            old('correo', $request->query('correo'))
        ));
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'correo' => ['required', 'email'],
            'contrasena' => ['required', 'string'],
        ]);

        $key = $this->throttleKey($request);
        $estadoIntentos = $this->estadoIntentosLogin($request->correo, $request->ip());

        if ($estadoIntentos['bloqueado']) {
            return back()
                ->withErrors([
                    'correo' => 'Cuenta bloqueada temporalmente por intentos fallidos.',
                ])
                ->with($estadoIntentos)
                ->onlyInput('correo');
        }

        $usuario = autenticacion::with('persona')->where('correo', $request->correo)->first();

        if (!$usuario || !password_verify($request->contrasena, $usuario->contrasena)) {
            $this->incrementarIntentos($key);
            $this->registrarBitacora($usuario?->Id_usuario, 'Intento fallido de login');

            $estadoIntentos = $this->estadoIntentosLogin($request->correo, $request->ip());

            return back()
                ->withErrors([
                    'correo' => 'Credenciales incorrectas.',
                ])
                ->with($estadoIntentos)
                ->onlyInput('correo');
        }

        if ($usuario->estado !== 'activo') {
            $this->registrarBitacora($usuario->Id_usuario, 'Usuario inactivo intento login');

            return back()->withErrors([
                'correo' => 'Usuario inactivo o bloqueado.',
            ]);
        }

        Auth::login($usuario);
        $request->session()->regenerate();

        cache()->forget($key);
        cache()->forget($key.'_timer');

        $this->registrarBitacora($usuario->Id_usuario, 'Inicio de sesion correcto');

        return $this->redirigirSegunRol($usuario);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Sesion cerrada correctamente.');
    }

    public function mostrarOlvidoContrasena(): View
    {
        return view('Usuario_Seguridad_y_Auditoria.OlvideContrasena');
    }

    public function enviarCodigoRecuperacion(Request $request): RedirectResponse
    {
        $request->validate([
            'correo' => ['required', 'email'],
        ]);

        $usuario = autenticacion::where('correo', $request->correo)->first();
        if (!$usuario) {
            return back()->withErrors([
                'correo' => 'No existe un usuario con ese correo.',
            ])->onlyInput('correo');
        }

        $resultado = $this->enviarCodigoPorCorreo($request->correo, $usuario);
        if ($resultado instanceof RedirectResponse) {
            return $resultado;
        }

        return redirect()->route('password.reset.form')->with([
            'correo_recuperacion' => $request->correo,
            'success' => 'Se envio un codigo de recuperacion a tu correo.',
        ]);
    }

    public function reenviarCodigoRecuperacion(Request $request): RedirectResponse
    {
        $correo = session('correo_recuperacion');

        if (!$correo) {
            return redirect()->route('password.forgot')->withErrors([
                'correo' => 'Primero debes solicitar un codigo de recuperacion.',
            ]);
        }

        $usuario = autenticacion::where('correo', $correo)->first();
        if (!$usuario) {
            return redirect()->route('password.forgot')->withErrors([
                'correo' => 'No existe un usuario con ese correo.',
            ]);
        }

        $resultado = $this->enviarCodigoPorCorreo($correo, $usuario);
        if ($resultado instanceof RedirectResponse) {
            return $resultado;
        }

        return redirect()->route('password.reset.form')->with([
            'correo_recuperacion' => $correo,
            'success' => 'Se reenvio un nuevo codigo a tu correo.',
        ]);
    }

    public function mostrarFormularioCambioContrasena(Request $request): View|RedirectResponse
    {
        if (!session('correo_recuperacion')) {
            return redirect()->route('password.forgot')->withErrors([
                'correo' => 'Primero solicita un codigo de recuperacion.',
            ]);
        }

        return view('Usuario_Seguridad_y_Auditoria.CambiarContrasena');
    }

    public function cambiarContrasena(Request $request): RedirectResponse
    {
        $correo = session('correo_recuperacion');

        if (!$correo) {
            return redirect()->route('password.forgot')->withErrors([
                'correo' => 'Primero debes solicitar un codigo de recuperacion.',
            ]);
        }

        $request->validate([
            'codigo' => ['required', 'digits:6'],
            'contrasena' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
                'confirmed',
            ],
        ], [
            'contrasena.regex' => 'La contrasena debe tener mayusculas, minusculas, numeros y caracteres especiales.',
        ]);

        $token = DB::table('password_reset_tokens')->where('email', $correo)->first();
        if (!$token) {
            return back()->withErrors([
                'codigo' => 'No hay solicitud de recuperacion activa. Usa Volver a enviar.',
            ])->withInput($request->only('codigo'));
        }

        if (now()->diffInMinutes(\Illuminate\Support\Carbon::parse($token->created_at)) > 10) {
            DB::table('password_reset_tokens')->where('email', $correo)->delete();

            return back()->withErrors([
                'codigo' => 'El codigo expiro. Usa Volver a enviar.',
            ])->withInput($request->only('codigo'));
        }

        if (!Hash::check($request->codigo, $token->token)) {
            return back()->withErrors([
                'codigo' => 'El codigo es incorrecto.',
            ])->withInput($request->only('codigo'));
        }

        autenticacion::where('correo', $correo)->update([
            'contrasena' => Hash::make($request->contrasena),
        ]);

        DB::table('password_reset_tokens')->where('email', $correo)->delete();
        session()->forget('correo_recuperacion');

        return redirect()->route('login')->with('success', 'Contrasena actualizada correctamente.');
    }

    private function enviarCodigoPorCorreo(string $correo, autenticacion $usuario): ?RedirectResponse
    {
        $codigo = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $correo],
            [
                'token' => Hash::make($codigo),
                'created_at' => now(),
            ]
        );

        try {
            Mail::to($correo)->send(
                new RecuperacionContrasenaMail(
                    codigo: $codigo,
                    nombreUsuario: $usuario->nombre_usuario,
                )
            );
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors([
                'correo' => 'No se pudo enviar el correo. Verifica la configuracion SMTP en .env.',
            ])->onlyInput('correo');
        }

        return null;
    }

    private function throttleKey(Request $request): string
    {
        return Str::lower($request->correo).'|'.$request->ip();
    }

    private function incrementarIntentos(string $key): void
    {
        $intentos = (int) cache()->get($key, 0);
        cache()->put($key, $intentos + 1, self::SEGUNDOS_BLOQUEO);

        if ($intentos + 1 >= self::MAX_INTENTOS) {
            cache()->put($key.'_timer', time() + self::SEGUNDOS_BLOQUEO, self::SEGUNDOS_BLOQUEO);
        }
    }

    private function estadoIntentosLogin(?string $correo, ?string $ip = null): array
    {
        $intentosFallidos = 0;
        $segundosRestantes = 0;
        $bloqueado = false;

        if ($correo) {
            $key = Str::lower($correo).'|'.($ip ?? request()->ip());
            $intentosFallidos = (int) cache()->get($key, 0);

            if ($intentosFallidos >= self::MAX_INTENTOS && cache()->has($key.'_timer')) {
                $segundosRestantes = max(0, (int) cache()->get($key.'_timer') - time());
                $bloqueado = $segundosRestantes > 0;

                if (!$bloqueado) {
                    cache()->forget($key);
                    cache()->forget($key.'_timer');
                    $intentosFallidos = 0;
                }
            }
        }

        return [
            'intentosFallidos' => $intentosFallidos,
            'intentosMaximos' => self::MAX_INTENTOS,
            'segundosRestantes' => $segundosRestantes,
            'bloqueado' => $bloqueado,
        ];
    }

    private function registrarBitacora(?int $usuarioId, string $accion): void
    {
        if (!$usuarioId) {
            return;
        }

        DB::table('bitacora')->insert([
            'tipo' => 'Autenticacion',
            'descripcion' => $accion,
            'fecha' => now()->toDateString(),
            'hora' => now()->format('H:i:s'),
            'estado' => 'activo',
            'Id_usuario' => $usuarioId,
        ]);
    }

    private function redirigirSegunRol(autenticacion $usuario): RedirectResponse
    {
        $persona = $usuario->persona;

        if (!$persona) {
            Auth::logout();

            return redirect()->route('login')->withErrors([
                'correo' => 'La persona asociada no existe.',
            ]);
        }

        return redirect()->route('menu')->with('success', 'Bienvenido, '.$persona->nombre.' '.$persona->apellido.'.');
    }
}