<?php

namespace App\Http\Controllers\Logistica_Recursos_y_Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Logistica_Recursos_y_Reportes\gestionarDocentes;
use Illuminate\Support\Facades\Hash;

class gestionarDocentesController extends Controller
{
    public function index()
    {
        $docentes = DB::table('docente as d')
            ->join('persona as p', DB::raw('"p"."Id_persona"'), '=', DB::raw('"d"."Id_docente"'))
            ->leftJoin('docente_especialidad as de', DB::raw('"de"."Id_docente"'), '=', DB::raw('"d"."Id_docente"'))
            ->leftJoin('especialidad as e', DB::raw('"e"."Id_especialidad"'), '=', DB::raw('"de"."Id_especialidad"'))
            ->select(
                DB::raw('"d"."Id_docente" as id_docente'),
                DB::raw('"p"."Id_persona" as id_persona'),
                'p.ci',
                'p.nombre',
                'p.apellido',
                'p.sexo',
                'p.fecha_nacimiento',
                'p.telefono',
                'p.correo',
                'p.direccion',
                'd.anio_servicio',
                'd.estado',
                DB::raw("COALESCE(string_agg(e.nombre_especialidad, ', '), 'Sin especialidad') as especialidades")
            )
            ->groupBy(
                DB::raw('"d"."Id_docente"'),
                DB::raw('"p"."Id_persona"'),
                'p.ci',
                'p.nombre',
                'p.apellido',
                'p.sexo',
                'p.fecha_nacimiento',
                'p.telefono',
                'p.correo',
                'p.direccion',
                'd.anio_servicio',
                'd.estado'
            )
            ->orderBy(DB::raw('"d"."Id_docente"'), 'desc')
            ->get();

        $especialidades = DB::table('especialidad')
            ->select(
                DB::raw('"Id_especialidad" as id_especialidad'),
                'nombre_especialidad'
            )
            ->orderBy('nombre_especialidad')
            ->get();

        return view('Logistica_Recursos_y_Reportes.gestionarDocentes', compact('docentes', 'especialidades'));
    }


    public function store(Request $request)
{
    $request->validate([
        'ci' => 'required|string|max:20',
        'nombre' => 'required|string|max:100',
        'apellido' => 'required|string|max:100',
        'sexo' => 'nullable|string|max:1',
        'fecha_nacimiento' => 'required|date',
        'telefono' => 'nullable|string|max:20',
        'correo' => 'required|email|max:150',
        'direccion' => 'nullable|string',
        'anio_servicio' => 'required|integer|min:0',
        'estado' => 'required|string|max:20',
        'especialidades' => 'nullable|array',
        'especialidades.*' => 'exists:especialidad,Id_especialidad',
    ]);

    DB::beginTransaction();

    try {
        // 1. Buscar si la persona ya existe por CI
        $persona = DB::table('persona')
            ->where('ci', $request->ci)
            ->first();

        if ($persona) {
            $idPersona = $persona->Id_persona;

            // Actualizar datos de la persona existente
            DB::table('persona')
                ->where('Id_persona', $idPersona)
                ->update([
                    'nombre' => $request->nombre,
                    'apellido' => $request->apellido,
                    'sexo' => $request->sexo,
                    'fecha_nacimiento' => $request->fecha_nacimiento,
                    'telefono' => $request->telefono,
                    'correo' => $request->correo,
                    'direccion' => $request->direccion,
                    'estado' => 'activo',
                    'tipo_Docente' => true,
                ]);
        } else {
            // Crear persona si no existe
            $idPersona = DB::table('persona')->insertGetId([
                'ci' => $request->ci,
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'sexo' => $request->sexo,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'telefono' => $request->telefono,
                'correo' => $request->correo,
                'direccion' => $request->direccion,
                'estado' => 'activo',
                'tipo_Docente' => true,
            ], 'Id_persona');
        }

        // 2. Verificar si ya existe como docente
        $docenteExiste = DB::table('docente')
            ->where('Id_docente', $idPersona)
            ->exists();

        if ($docenteExiste) {
            DB::table('docente')
                ->where('Id_docente', $idPersona)
                ->update([
                    'anio_servicio' => $request->anio_servicio,
                    'estado' => $request->estado,
                ]);
        } else {
            DB::table('docente')->insert([
                'Id_docente' => $idPersona,
                'anio_servicio' => $request->anio_servicio,
                'estado' => $request->estado,
            ]);
        }

        // 3. Verificar que el correo no esté usado por otro usuario
        $usuarioCorreoOtro = DB::table('usuario')
            ->where('correo', $request->correo)
            ->where('Id_persona', '!=', $idPersona)
            ->exists();

        if ($usuarioCorreoOtro) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'El correo ya está usado por otro usuario.'
            ])->withInput();
        }

        // 4. Crear o actualizar usuario del docente
        $usuarioExiste = DB::table('usuario')
            ->where('Id_persona', $idPersona)
            ->exists();

        if ($usuarioExiste) {
            DB::table('usuario')
                ->where('Id_persona', $idPersona)
                ->update([
                    'nombre_usuario' => $request->correo,
                    'correo' => $request->correo,
                    'estado' => 'activo',
                ]);
        } else {
            DB::table('usuario')->insert([
                'nombre_usuario' => $request->correo,
                'correo' => $request->correo,
                'contrasena' => Hash::make($request->ci),
                'estado' => 'activo',
                'fecha_creacion' => now()->toDateString(),
                'Id_persona' => $idPersona,
            ]);
        }

        // 5. Actualizar especialidades
        DB::table('docente_especialidad')
            ->where('Id_docente', $idPersona)
            ->delete();

        if ($request->filled('especialidades')) {
            foreach ($request->especialidades as $idEspecialidad) {
                DB::table('docente_especialidad')->insert([
                    'Id_docente' => $idPersona,
                    'Id_especialidad' => $idEspecialidad,
                ]);
            }
        }

        $this->registrarBitacora(
            'Logistica y Recursos',
            'Registró o actualizó al docente ' . $request->nombre . ' ' . $request->apellido . '.'
        );

        DB::commit();

        return redirect()->route('docentes.index')
            ->with('success', 'Docente registrado o actualizado correctamente. Usuario creado con correo y CI como contraseña inicial.');

    } catch (\Exception $e) {
        DB::rollBack();

        return back()->withErrors([
            'error' => 'Error al registrar docente: ' . $e->getMessage()
        ])->withInput();
    }
}

    public function update(Request $request, $id)
    {
        $request->validate([
            'ci' => [
                'required',
                'string',
                'max:20',
                Rule::unique('persona', 'ci')->ignore($id, 'Id_persona'),
            ],
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'sexo' => 'nullable|string|max:1',
            'fecha_nacimiento' => 'required|date',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:150',
            'direccion' => 'nullable|string',
            'anio_servicio' => 'required|integer|min:0',
            'estado' => 'required|string|max:20',
            'especialidades' => 'nullable|array',
            'especialidades.*' => 'exists:especialidad,Id_especialidad',
        ]);

        DB::beginTransaction();

        try {
            DB::table('persona')
                ->where('Id_persona', $id)
                ->update([
                    'ci' => $request->ci,
                    'nombre' => $request->nombre,
                    'apellido' => $request->apellido,
                    'sexo' => $request->sexo,
                    'fecha_nacimiento' => $request->fecha_nacimiento,
                    'telefono' => $request->telefono,
                    'correo' => $request->correo,
                    'direccion' => $request->direccion,
                    'tipo_Docente' => true,
                ]);

            DB::table('docente')
                ->where('Id_docente', $id)
                ->update([
                    'anio_servicio' => $request->anio_servicio,
                    'estado' => $request->estado,
                ]);

            DB::table('docente_especialidad')
                ->where('Id_docente', $id)
                ->delete();

            if ($request->filled('especialidades')) {
                foreach ($request->especialidades as $idEspecialidad) {
                    DB::table('docente_especialidad')->insert([
                        'Id_docente' => $id,
                        'Id_especialidad' => $idEspecialidad,
                    ]);
                }
            }

            $this->registrarBitacora(
                'Logistica y Recursos',
                'Actualizó al docente ' . $request->nombre . ' ' . $request->apellido . '.'
            );

            DB::commit();

            return redirect()->route('docentes.index')
                ->with('success', 'Docente actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al actualizar docente: ' . $e->getMessage()
            ]);
        }
    }

    public function validarDocumentos($id)
    {
        DB::table('persona_documento')
            ->where('Id_persona', $id)
            ->update([
                'estado' => 'validado',
                'fecha_revision' => now()->toDateString(),
            ]);

        $this->registrarBitacora(
            'Logistica y Recursos',
            'Validó documentos del docente ID ' . $id . '.'
        );

        return redirect()->route('docentes.index')
            ->with('success', 'Documentos del docente validados correctamente.');
    }

    public function deshabilitar($id)
    {
        DB::table('docente')
            ->where('Id_docente', $id)
            ->update(['estado' => 'inactivo']);

        DB::table('persona')
            ->where('Id_persona', $id)
            ->update(['estado' => 'inactivo']);

        $this->registrarBitacora(
            'Logistica y Recursos',
            'Deshabilitó al docente ID ' . $id . '.'
        );

        return redirect()->route('docentes.index')
            ->with('success', 'Docente deshabilitado correctamente.');
    }

    public function habilitar($id)
    {
        DB::table('docente')
            ->where('Id_docente', $id)
            ->update(['estado' => 'activo']);

        DB::table('persona')
            ->where('Id_persona', $id)
            ->update(['estado' => 'activo']);

        $this->registrarBitacora(
            'Logistica y Recursos',
            'Habilitó al docente ID ' . $id . '.'
        );

        return redirect()->route('docentes.index')
            ->with('success', 'Docente habilitado correctamente.');
    }
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $docente = DB::table('docente as d')
                ->join('persona as p', DB::raw('"p"."Id_persona"'), '=', DB::raw('"d"."Id_docente"'))
                ->select(
                    DB::raw('"d"."Id_docente" as id_docente'),
                    'p.nombre',
                    'p.apellido'
                )
                ->where('d.Id_docente', $id)
                ->first();

            if (!$docente) {
                return redirect()->route('docentes.index')
                    ->withErrors(['error' => 'El docente no existe.']);
            }

            // Verificar si el docente está asignado a algún grupo
            $tieneGrupos = DB::table('grupo')
                ->where('Id_docente', $id)
                ->exists();

            if ($tieneGrupos) {
                DB::rollBack();

                return redirect()->route('docentes.index')
                    ->withErrors([
                        'error' => 'No se puede eliminar este docente porque está asignado a uno o más grupos. Primero reasigna o elimina esos grupos.'
                    ]);
            }

            // Eliminar especialidades asignadas
            DB::table('docente_especialidad')
                ->where('Id_docente', $id)
                ->delete();

            // Eliminar usuario creado para el docente
            DB::table('usuario')
                ->where('Id_persona', $id)
                ->delete();

            // Eliminar registro de docente
            DB::table('docente')
                ->where('Id_docente', $id)
                ->delete();

            // Mantener la persona, pero ya no como docente
            DB::table('persona')
                ->where('Id_persona', $id)
                ->update([
                    'tipo_Docente' => false,
                    'estado' => 'inactivo',
                ]);

            $this->registrarBitacora(
                'Logistica y Recursos',
                'Eliminó al docente ' . $docente->nombre . ' ' . $docente->apellido . '.'
            );

            DB::commit();

            return redirect()->route('docentes.index')
                ->with('success', 'Docente eliminado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('docentes.index')
                ->withErrors([
                    'error' => 'No se pudo eliminar el docente: ' . $e->getMessage()
                ]);
        }
    }
    private function registrarBitacora($tipo, $descripcion)
    {
        if (Auth::check()) {
            DB::table('bitacora')->insert([
                'tipo' => $tipo,
                'descripcion' => $descripcion,
                'fecha' => now()->toDateString(),
                'hora' => now()->format('H:i:s'),
                'estado' => 'activo',
                'Id_usuario' => Auth::id(),
            ]);
        }
    }
}