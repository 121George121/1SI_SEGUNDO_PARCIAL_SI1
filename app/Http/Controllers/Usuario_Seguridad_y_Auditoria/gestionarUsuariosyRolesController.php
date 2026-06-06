<?php

namespace App\Http\Controllers\Usuario_Seguridad_y_Auditoria;

use App\Http\Controllers\Controller;
use App\Models\Usuario_Sefuridad_y_Auditoria\gestionarUsuariosyRoles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class gestionarUsuariosyRolesController extends Controller
{
    private const ROLES = [
        'tipo_Superadministrador' => 'Superadministrador',
        'tipo_Administrador' => 'Administrador',
        'tipo_Docente' => 'Docente',
        'tipo_Postulante' => 'Postulante',
    ];

    public function index(): View
    {
        $usuarios = gestionarUsuariosyRoles::with('persona')
            ->orderBy('Id_usuario')
            ->get();

        return view('Usuario_Seguridad_y_Auditoria.GestionarUsuariosyRoles', compact('usuarios'));
    }

    public function create(): View
    {
        return view('Usuario_Seguridad_y_Auditoria.FormularioUsuario', [
            'usuario' => null,
            'rolesDisponibles' => self::ROLES,
            'administrador' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->validarUsuario($request, true);

        DB::transaction(function () use ($request): void {
            $personaId = DB::table('persona')->insertGetId([
                'ci' => $request->ci,
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'sexo' => $request->sexo,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'telefono' => $request->telefono,
                'correo' => $request->correo,
                'direccion' => $request->direccion,
                'estado' => 'activo',
                ...$this->rolesDesdeRequest($request),
            ], 'Id_persona');

            if ($this->esAdministradorOSuperadministrador($request)) {
                DB::table('administrador')->updateOrInsert(
                    ['Id_administrador' => $personaId],
                    [
                        'cargo' => $request->cargo,
                        'area' => $request->area,
                        'estado' => $request->estado_administrador ?? 'activo',
                    ]
                );
            }

            gestionarUsuariosyRoles::create([
                'nombre_usuario' => $request->nombre_usuario,
                'correo' => $request->correo,
                'contrasena' => Hash::make($request->contrasena),
                'estado' => 'activo',
                'fecha_creacion' => now()->toDateString(),
                'Id_persona' => $personaId,
            ]);
        });

        $this->registrarBitacora('Usuario creado: '.$request->nombre_usuario);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario registrado correctamente.');
    }

    public function edit(int $id): View
    {
        $usuario = gestionarUsuariosyRoles::with('persona')->findOrFail($id);

        $administrador = null;

        if ($usuario->Id_persona) {
            $administrador = DB::table('administrador')
                ->where('Id_administrador', $usuario->Id_persona)
                ->first();
        }

        return view('Usuario_Seguridad_y_Auditoria.FormularioUsuario', [
            'usuario' => $usuario,
            'rolesDisponibles' => self::ROLES,
            'administrador' => $administrador,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $usuario = gestionarUsuariosyRoles::with('persona')->findOrFail($id);

        $this->validarUsuario($request, false, $id);

        DB::transaction(function () use ($request, $usuario): void {
            $usuario->persona->update([
                'ci' => $request->ci,
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'sexo' => $request->sexo,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'telefono' => $request->telefono,
                'correo' => $request->correo,
                'direccion' => $request->direccion,
                ...$this->rolesDesdeRequest($request),
            ]);

            if ($this->esAdministradorOSuperadministrador($request)) {
                DB::table('administrador')->updateOrInsert(
                    ['Id_administrador' => $usuario->Id_persona],
                    [
                        'cargo' => $request->cargo,
                        'area' => $request->area,
                        'estado' => $request->estado_administrador ?? 'activo',
                    ]
                );
            } else {
                DB::table('administrador')
                    ->where('Id_administrador', $usuario->Id_persona)
                    ->update([
                        'estado' => 'inactivo',
                    ]);
            }

            $datosUsuario = [
                'nombre_usuario' => $request->nombre_usuario,
                'correo' => $request->correo,
                'estado' => $request->estado ?? 'activo',
            ];

            if ($request->filled('contrasena')) {
                $datosUsuario['contrasena'] = Hash::make($request->contrasena);
            }

            $usuario->update($datosUsuario);
        });

        $this->registrarBitacora('Usuario actualizado: '.$usuario->nombre_usuario);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $usuario = gestionarUsuariosyRoles::with('persona')->findOrFail($id);

        if ((int) auth()->id() === (int) $usuario->Id_usuario) {
            return back()->withErrors(['general' => 'No puedes desactivar tu propio usuario.']);
        }

        $usuario->update(['estado' => 'inactivo']);
        $usuario->persona?->update(['estado' => 'inactivo']);

        if ($usuario->Id_persona) {
            DB::table('administrador')
                ->where('Id_administrador', $usuario->Id_persona)
                ->update([
                    'estado' => 'inactivo',
                ]);
        }

        $this->registrarBitacora('Usuario desactivado: '.$usuario->nombre_usuario);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario desactivado correctamente.');
    }

    public function mostrarAsignarRoles(int $id): View
    {
        $usuario = gestionarUsuariosyRoles::with('persona')->findOrFail($id);

        return view('Usuario_Seguridad_y_Auditoria.AsignarRoles', [
            'usuario' => $usuario,
            'rolesDisponibles' => self::ROLES,
        ]);
    }

    public function assignRoles(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['in:'.implode(',', array_keys(self::ROLES))],
        ], [
            'roles.required' => 'Debes seleccionar al menos un rol.',
        ]);

        $usuario = gestionarUsuariosyRoles::with('persona')->findOrFail($id);

        $usuario->persona->update($this->rolesDesdeRequest($request));

        if ($this->esAdministradorOSuperadministrador($request)) {
            DB::table('administrador')->updateOrInsert(
                ['Id_administrador' => $usuario->Id_persona],
                [
                    'estado' => 'activo',
                ]
            );
        } else {
            DB::table('administrador')
                ->where('Id_administrador', $usuario->Id_persona)
                ->update([
                    'estado' => 'inactivo',
                ]);
        }

        $this->registrarBitacora('Roles actualizados para: '.$usuario->nombre_usuario);

        return redirect()->route('usuarios.index')
            ->with('success', 'Roles asignados correctamente.');
    }

    private function validarUsuario(Request $request, bool $esNuevo, ?int $id = null): array
    {
        $reglas = [
            'nombre_usuario' => ['required', 'string', 'max:50'],
            'correo' => ['required', 'email', 'max:150'],
            'ci' => ['required', 'string', 'max:20'],
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'sexo' => ['nullable', 'in:M,F'],
            'fecha_nacimiento' => ['required', 'date'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string'],
            'estado' => ['nullable', 'in:activo,inactivo'],
            'cargo' => ['nullable', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:100'],
            'estado_administrador' => ['nullable', 'in:activo,inactivo'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['in:'.implode(',', array_keys(self::ROLES))],
        ];

        if ($this->esAdministradorOSuperadministrador($request)) {
            $reglas['cargo'] = ['required', 'string', 'max:100'];
            $reglas['area'] = ['required', 'string', 'max:100'];
        }

        if ($esNuevo) {
            $reglas['nombre_usuario'][] = 'unique:usuario,nombre_usuario';
            $reglas['correo'][] = 'unique:usuario,correo';
            $reglas['ci'][] = 'unique:persona,ci';

            $reglas['contrasena'] = [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ];
        } else {
            $usuario = gestionarUsuariosyRoles::findOrFail($id);

            $reglas['nombre_usuario'][] = 'unique:usuario,nombre_usuario,'.$id.',Id_usuario';
            $reglas['correo'][] = 'unique:usuario,correo,'.$id.',Id_usuario';
            $reglas['ci'][] = 'unique:persona,ci,'.$usuario->Id_persona.',Id_persona';

            $reglas['contrasena'] = [
                'nullable',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ];
        }

        return $request->validate($reglas, [
            'contrasena.regex' => 'La contraseña debe tener mayúsculas, minúsculas, números y caracteres especiales.',
            'roles.required' => 'Debes seleccionar al menos un rol.',
            'cargo.required' => 'El cargo es obligatorio para Administrador o Superadministrador.',
            'area.required' => 'El área es obligatoria para Administrador o Superadministrador.',
        ]);
    }

    private function rolesDesdeRequest(Request $request): array
    {
        $roles = $request->input('roles', []);

        return [
            'tipo_Superadministrador' => in_array('tipo_Superadministrador', $roles, true),
            'tipo_Administrador' => in_array('tipo_Administrador', $roles, true),
            'tipo_Docente' => in_array('tipo_Docente', $roles, true),
            'tipo_Postulante' => in_array('tipo_Postulante', $roles, true),
        ];
    }

    private function esAdministradorOSuperadministrador(Request $request): bool
    {
        $roles = $request->input('roles', []);

        return in_array('tipo_Administrador', $roles, true)
            || in_array('tipo_Superadministrador', $roles, true);
    }

    private function registrarBitacora(string $descripcion): void
    {
        if (!auth()->check()) {
            return;
        }

        DB::table('bitacora')->insert([
            'tipo' => 'Usuarios y Roles',
            'descripcion' => $descripcion,
            'fecha' => now()->toDateString(),
            'hora' => now()->format('H:i:s'),
            'estado' => 'activo',
            'Id_usuario' => auth()->id(),
        ]);
    }
}