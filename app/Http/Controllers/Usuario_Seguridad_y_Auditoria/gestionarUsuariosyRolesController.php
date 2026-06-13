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
            'superadministrador' => null,
            'administrador' => null,
            'docente' => null,
            'postulante' => null,
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

            $rol = $request->input('rol');

            // 1. Superadministrador
            if ($rol === 'tipo_Superadministrador') {
                DB::table('superadministrador')->updateOrInsert(
                    ['Id_superadministrador' => $personaId],
                    [
                        'cargo' => $request->cargo_superadmin,
                        'estado' => $request->estado_superadmin ?? 'activo',
                    ]
                );
            }

            // 2. Administrador
            if ($rol === 'tipo_Administrador') {
                DB::table('administrador')->updateOrInsert(
                    ['Id_administrador' => $personaId],
                    [
                        'cargo' => $request->cargo,
                        'area' => $request->area,
                        'estado' => $request->estado_administrador ?? 'activo',
                    ]
                );
            }

            // 3. Docente
            if ($rol === 'tipo_Docente') {
                DB::table('docente')->updateOrInsert(
                    ['Id_docente' => $personaId],
                    [
                        'anio_servicio' => $request->anio_servicio,
                        'estado' => $request->estado_docente ?? 'activo',
                    ]
                );
            }

            // 4. Postulante
            if ($rol === 'tipo_Postulante') {
                DB::table('postulante')->updateOrInsert(
                    ['Id_postulante' => $personaId],
                    [
                        'estado_inscripcion' => $request->estado_inscripcion ?? 'En_Revision',
                        'fecha_registro' => $request->fecha_registro ?? now()->toDateString(),
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

        $superadministrador = null;
        $administrador = null;
        $docente = null;
        $postulante = null;

        if ($usuario->Id_persona) {
            $superadministrador = DB::table('superadministrador')
                ->where('Id_superadministrador', $usuario->Id_persona)
                ->first();

            $administrador = DB::table('administrador')
                ->where('Id_administrador', $usuario->Id_persona)
                ->first();

            $docente = DB::table('docente')
                ->where('Id_docente', $usuario->Id_persona)
                ->first();

            $postulante = DB::table('postulante')
                ->where('Id_postulante', $usuario->Id_persona)
                ->first();
        }

        return view('Usuario_Seguridad_y_Auditoria.FormularioUsuario', [
            'usuario' => $usuario,
            'rolesDisponibles' => self::ROLES,
            'superadministrador' => $superadministrador,
            'administrador' => $administrador,
            'docente' => $docente,
            'postulante' => $postulante,
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

            $rol = $request->input('rol');

            // 1. Superadministrador
            if ($rol === 'tipo_Superadministrador') {
                DB::table('superadministrador')->updateOrInsert(
                    ['Id_superadministrador' => $usuario->Id_persona],
                    [
                        'cargo' => $request->cargo_superadmin,
                        'estado' => $request->estado_superadmin ?? 'activo',
                    ]
                );
            } else {
                DB::table('superadministrador')
                    ->where('Id_superadministrador', $usuario->Id_persona)
                    ->update(['estado' => 'inactivo']);
            }

            // 2. Administrador
            if ($rol === 'tipo_Administrador') {
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
                    ->update(['estado' => 'inactivo']);
            }

            // 3. Docente
            if ($rol === 'tipo_Docente') {
                DB::table('docente')->updateOrInsert(
                    ['Id_docente' => $usuario->Id_persona],
                    [
                        'anio_servicio' => $request->anio_servicio,
                        'estado' => $request->estado_docente ?? 'activo',
                    ]
                );
            } else {
                DB::table('docente')
                    ->where('Id_docente', $usuario->Id_persona)
                    ->update(['estado' => 'inactivo']);
            }

            // 4. Postulante
            if ($rol === 'tipo_Postulante') {
                DB::table('postulante')->updateOrInsert(
                    ['Id_postulante' => $usuario->Id_persona],
                    [
                        'estado_inscripcion' => $request->estado_inscripcion ?? 'En_Revision',
                        'fecha_registro' => $request->fecha_registro ?? now()->toDateString(),
                    ]
                );
            } else {
                DB::table('postulante')
                    ->where('Id_postulante', $usuario->Id_persona)
                    ->update(['estado_inscripcion' => 'inactivo']);
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
            return back()->withErrors(['general' => 'No puedes eliminar tu propio usuario.']);
        }

        DB::beginTransaction();
        try {
            $nombreUsuario = $usuario->nombre_usuario;
            $personaId = $usuario->Id_persona;

            // Delete user record first
            $usuario->delete();

            // Delete associated persona (cascades to superadmin, admin, docente, postulante, etc.)
            if ($personaId) {
                DB::table('persona')->where('Id_persona', $personaId)->delete();
            }

            $this->registrarBitacora('Usuario y persona eliminados: ' . $nombreUsuario);

            DB::commit();

            return redirect()->route('usuarios.index')
                ->with('success', 'Usuario y sus roles asociados eliminados correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('usuarios.index')
                ->withErrors(['general' => 'No se pudo eliminar el usuario: ' . $e->getMessage()]);
        }
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
            'rol' => ['required', 'string', 'in:'.implode(',', array_keys(self::ROLES))],
        ], [
            'rol.required' => 'Debes seleccionar un rol.',
        ]);

        $usuario = gestionarUsuariosyRoles::with('persona')->findOrFail($id);

        DB::transaction(function () use ($request, $usuario): void {
            $usuario->persona->update($this->rolesDesdeRequest($request));

            $rolSeleccionado = $request->input('rol');

            // 1. Superadministrador
            if ($rolSeleccionado === 'tipo_Superadministrador') {
                DB::table('superadministrador')->updateOrInsert(
                    ['Id_superadministrador' => $usuario->Id_persona],
                    ['estado' => 'activo']
                );
            } else {
                DB::table('superadministrador')
                    ->where('Id_superadministrador', $usuario->Id_persona)
                    ->update(['estado' => 'inactivo']);
            }

            // 2. Administrador
            if ($rolSeleccionado === 'tipo_Administrador') {
                DB::table('administrador')->updateOrInsert(
                    ['Id_administrador' => $usuario->Id_persona],
                    ['estado' => 'activo']
                );
            } else {
                DB::table('administrador')
                    ->where('Id_administrador', $usuario->Id_persona)
                    ->update(['estado' => 'inactivo']);
            }

            // 3. Docente
            if ($rolSeleccionado === 'tipo_Docente') {
                DB::table('docente')->updateOrInsert(
                    ['Id_docente' => $usuario->Id_persona],
                    ['estado' => 'activo']
                );
            } else {
                DB::table('docente')
                    ->where('Id_docente', $usuario->Id_persona)
                    ->update(['estado' => 'inactivo']);
            }

            // 4. Postulante
            if ($rolSeleccionado === 'tipo_Postulante') {
                DB::table('postulante')->updateOrInsert(
                    ['Id_postulante' => $usuario->Id_persona],
                    ['estado_inscripcion' => 'En_Revision']
                );
            } else {
                DB::table('postulante')
                    ->where('Id_postulante', $usuario->Id_persona)
                    ->update(['estado_inscripcion' => 'inactivo']);
            }
        });

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
            'rol' => ['required', 'string', 'in:'.implode(',', array_keys(self::ROLES))],
        ];

        $rol = $request->input('rol');

        if ($rol === 'tipo_Superadministrador') {
            $reglas['cargo_superadmin'] = ['required', 'string', 'max:100'];
        }
        if ($rol === 'tipo_Administrador') {
            $reglas['cargo'] = ['required', 'string', 'max:100'];
            $reglas['area'] = ['required', 'string', 'max:100'];
        }
        if ($rol === 'tipo_Docente') {
            $reglas['anio_servicio'] = ['required', 'integer', 'min:0'];
        }
        if ($rol === 'tipo_Postulante') {
            $reglas['estado_inscripcion'] = ['required', 'string', 'max:20'];
            $reglas['fecha_registro'] = ['required', 'date'];
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
            'rol.required' => 'Debes seleccionar un rol.',
            'cargo_superadmin.required' => 'El cargo es obligatorio para Superadministrador.',
            'cargo.required' => 'El cargo es obligatorio para Administrador.',
            'area.required' => 'El área es obligatoria para Administrador.',
            'anio_servicio.required' => 'Los años de servicio son obligatorios para Docente.',
            'anio_servicio.integer' => 'Los años de servicio deben ser un número entero.',
            'estado_inscripcion.required' => 'El estado de inscripción es obligatorio para Postulante.',
            'fecha_registro.required' => 'La fecha de registro es obligatoria para Postulante.',
        ]);
    }

    private function rolesDesdeRequest(Request $request): array
    {
        $rolSeleccionado = $request->input('rol');

        return [
            'tipo_Superadministrador' => $rolSeleccionado === 'tipo_Superadministrador',
            'tipo_Administrador' => $rolSeleccionado === 'tipo_Administrador',
            'tipo_Docente' => $rolSeleccionado === 'tipo_Docente',
            'tipo_Postulante' => $rolSeleccionado === 'tipo_Postulante',
        ];
    }

    private function esAdministradorOSuperadministrador(Request $request): bool
    {
        $rolSeleccionado = $request->input('rol');

        return $rolSeleccionado === 'tipo_Administrador'
            || $rolSeleccionado === 'tipo_Superadministrador';
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