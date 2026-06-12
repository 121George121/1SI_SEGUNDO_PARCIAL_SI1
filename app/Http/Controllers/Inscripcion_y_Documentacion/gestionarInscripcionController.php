<?php

namespace App\Http\Controllers\Inscripcion_y_Documentacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class gestionarInscripcionController extends Controller
{
    public function index()
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $inscripciones = DB::table('inscripcion as i')
            ->join('postulante as po', DB::raw('"po"."Id_postulante"'), '=', DB::raw('"i"."Id_postulante"'))
            ->join('persona as p', DB::raw('"p"."Id_persona"'), '=', DB::raw('"po"."Id_postulante"'))
            ->leftJoin('gestion as g', DB::raw('"g"."Id_gestion"'), '=', DB::raw('"i"."Id_gestion"'))

            ->leftJoin('inscripcion_carrera as ic1', function ($join) {
                $join->on(DB::raw('"ic1"."Codigo_inscripcion"'), '=', DB::raw('"i"."Codigo_inscripcion"'))
                    ->where('ic1.prioridad', '=', 1);
            })
            ->leftJoin('carrera as c1', DB::raw('"c1"."Id_carrera"'), '=', DB::raw('"ic1"."Id_carrera"'))

            ->leftJoin('inscripcion_carrera as ic2', function ($join) {
                $join->on(DB::raw('"ic2"."Codigo_inscripcion"'), '=', DB::raw('"i"."Codigo_inscripcion"'))
                    ->where('ic2.prioridad', '=', 2);
            })
            ->leftJoin('carrera as c2', DB::raw('"c2"."Id_carrera"'), '=', DB::raw('"ic2"."Id_carrera"'))

            ->leftJoin('preferencia_inscripcion as pi', DB::raw('"pi"."Codigo_inscripcion"'), '=', DB::raw('"i"."Codigo_inscripcion"'))
            ->leftJoin('modalidad as m', DB::raw('"m"."Id_modalidad"'), '=', DB::raw('"pi"."Id_modalidad"'))
            ->leftJoin('turno as t', DB::raw('"t"."Id_turno"'), '=', DB::raw('"pi"."Id_turno"'))

            ->select(
                DB::raw('"i"."Codigo_inscripcion" as id_inscripcion'),
                DB::raw('"i"."Codigo_inscripcion" as codigo_inscripcion'),
                'i.fecha_inscripcion',
                DB::raw('"i"."estado" as estado_inscripcion'),

                DB::raw('"i"."Id_postulante" as id_postulante'),

                DB::raw('"p"."Id_persona" as id_persona'),
                'p.ci',
                'p.nombre',
                'p.apellido',
                'p.sexo',
                'p.fecha_nacimiento',
                'p.telefono',
                'p.correo',
                'p.direccion',

                DB::raw('"ic1"."Id_carrera" as id_carrera'),
                DB::raw('"ic1"."Id_carrera" as id_carrera_principal'),
                DB::raw('"c1"."nombre_carrera" as nombre_carrera'),
                DB::raw('"c1"."nombre_carrera" as carrera_principal'),

                DB::raw('"ic2"."Id_carrera" as id_carrera_secundaria'),
                DB::raw('"c2"."nombre_carrera" as carrera_secundaria'),

                DB::raw('"i"."Id_gestion" as id_gestion'),
                'g.anio',
                'g.periodo',
                DB::raw('"pi"."Id_preferencia" as id_preferencia'),
                DB::raw('"pi"."Id_modalidad" as id_modalidad_preferencia'),
                DB::raw('"pi"."Id_turno" as id_turno_preferencia'),
                'm.nombre_modalidad as modalidad_preferencia',
                't.nombre as turno_preferencia'
            )
            ->orderBy(DB::raw('"i"."Codigo_inscripcion"'), 'desc')
            ->get();

        $carreras = DB::table('carrera')
            ->select(
                DB::raw('"Id_carrera" as id_carrera'),
                'nombre_carrera',
                'estado'
            )
            ->whereRaw("LOWER(TRIM(estado)) = 'activo'")
            ->orderBy('nombre_carrera')
            ->get();

        $gestiones = DB::table('gestion')
            ->select(
                DB::raw('"Id_gestion" as id_gestion'),
                'anio',
                'periodo',
                'estado'
            )
            ->whereRaw("LOWER(TRIM(estado)) = 'activo'")
            ->orderBy('anio', 'desc')
            ->get();

        $modalidades = DB::table('modalidad')
            ->select(DB::raw('"Id_modalidad" as id_modalidad'), 'nombre_modalidad')
            ->whereRaw("LOWER(TRIM(estado)) = 'activo'")
            ->orderBy('nombre_modalidad')
            ->get();

        $turnos = DB::table('turno')
            ->select(DB::raw('"Id_turno" as id_turno'), 'nombre')
            ->whereRaw("LOWER(TRIM(estado)) = 'activo'")
            ->orderBy('nombre')
            ->get();

        return view('Inscripcion_y_Documentacion.gestionarInscripcion', compact(
            'inscripciones',
            'carreras',
            'gestiones',
            'modalidades',
            'turnos'
        ));
    }

    public function buscarPorCi($ci)
    {
        $persona = DB::table('persona')
            ->where('ci', $ci)
            ->first();

        if (!$persona) {
            return response()->json([
                'existe' => false,
                'mensaje' => 'No existe una persona con ese CI.'
            ]);
        }

        $idPersona = $persona->Id_persona;

        $postulante = DB::table('postulante')
            ->where('Id_postulante', $idPersona)
            ->first();

        $inscripcion = DB::table('inscripcion as i')
            ->leftJoin('inscripcion_carrera as ic1', function ($join) {
                $join->on(DB::raw('"ic1"."Codigo_inscripcion"'), '=', DB::raw('"i"."Codigo_inscripcion"'))
                    ->where('ic1.prioridad', '=', 1);
            })
            ->leftJoin('inscripcion_carrera as ic2', function ($join) {
                $join->on(DB::raw('"ic2"."Codigo_inscripcion"'), '=', DB::raw('"i"."Codigo_inscripcion"'))
                    ->where('ic2.prioridad', '=', 2);
            })
            ->leftJoin('preferencia_inscripcion as pi', DB::raw('"pi"."Codigo_inscripcion"'), '=', DB::raw('"i"."Codigo_inscripcion"'))
            ->select(
                DB::raw('"i"."Codigo_inscripcion" as Codigo_inscripcion'),
                'i.estado',
                'i.fecha_inscripcion',
                DB::raw('"i"."Id_postulante" as Id_postulante'),
                DB::raw('"ic1"."Id_carrera" as Id_carrera_principal'),
                DB::raw('"ic2"."Id_carrera" as Id_carrera_secundaria'),
                DB::raw('"i"."Id_gestion" as Id_gestion'),
                DB::raw('"pi"."Id_modalidad" as Id_modalidad_preferencia'),
                DB::raw('"pi"."Id_turno" as Id_turno_preferencia')
            )
            ->where('i.Id_postulante', $idPersona)
            ->first();

        return response()->json([
            'existe' => true,
            'persona' => [
                'id_persona' => $idPersona,
                'ci' => $persona->ci,
                'nombre' => $persona->nombre,
                'apellido' => $persona->apellido,
                'sexo' => $persona->sexo,
                'fecha_nacimiento' => $persona->fecha_nacimiento,
                'telefono' => $persona->telefono,
                'correo' => $persona->correo,
                'direccion' => $persona->direccion,
                'estado' => $persona->estado,
            ],
            'postulante' => $postulante,
            'inscripcion' => $inscripcion,
        ]);
    }

    public function store(Request $request)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $request->merge([
            'Id_carrera_principal' => $request->input('Id_carrera_principal') ?: $request->input('Id_carrera'),
        ]);

        $request->validate([
            'ci' => 'required|string|max:20',
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'sexo' => 'nullable|string|max:1',
            'fecha_nacimiento' => 'required|date',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'required|email|max:150',
            'direccion' => 'nullable|string',
            'Id_carrera_principal' => 'required|exists:carrera,Id_carrera',
            'Id_carrera_secundaria' => 'nullable|exists:carrera,Id_carrera|different:Id_carrera_principal',
            'Id_gestion' => 'required|exists:gestion,Id_gestion',
            'Id_modalidad_preferencia' => 'required|exists:modalidad,Id_modalidad',
            'Id_turno_preferencia' => 'required|exists:turno,Id_turno',
        ]);

        DB::beginTransaction();

        try {
            // 1. Buscar persona por CI
            $persona = DB::table('persona')
                ->where('ci', $request->ci)
                ->first();

            if ($persona) {
                $idPersona = $persona->Id_persona;

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
                        'tipo_Postulante' => true,
                    ]);
            } else {
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
                    'tipo_Postulante' => true,
                ], 'Id_persona');
            }

            // 2. Verificar que el correo no esté usado por otro usuario
            $correoUsadoPorOtroUsuario = DB::table('usuario')
                ->where('correo', $request->correo)
                ->where('Id_persona', '!=', $idPersona)
                ->exists();

            if ($correoUsadoPorOtroUsuario) {
                DB::rollBack();

                return back()->withErrors([
                    'error' => 'El correo ya está registrado en otro usuario.'
                ])->withInput();
            }

            // 3. Crear o actualizar postulante
            $existePostulante = DB::table('postulante')
                ->where('Id_postulante', $idPersona)
                ->exists();

            if (!$existePostulante) {
                DB::table('postulante')->insert([
                    'Id_postulante' => $idPersona,
                    'estado_inscripcion' => 'activo',
                ]);
            } else {
                DB::table('postulante')
                    ->where('Id_postulante', $idPersona)
                    ->update([
                        'estado_inscripcion' => 'activo',
                    ]);
            }

            // 4. Crear o actualizar usuario del postulante
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

            // 5. Crear o actualizar inscripción
            $inscripcionExistente = DB::table('inscripcion')
                ->where('Id_postulante', $idPersona)
                ->first();

            if ($inscripcionExistente) {
                $codigoInscripcion = $inscripcionExistente->Codigo_inscripcion;

                DB::table('inscripcion')
                    ->where('Codigo_inscripcion', $codigoInscripcion)
                    ->update([
                        'estado' => 'En_Revision',
                        'Id_gestion' => $request->input('Id_gestion'),
                    ]);

                DB::table('inscripcion_carrera')
                    ->where('Codigo_inscripcion', $codigoInscripcion)
                    ->delete();
            } else {
                $codigoInscripcion = DB::table('inscripcion')->insertGetId([
                    'estado' => 'En_Revision',
                    'fecha_inscripcion' => now()->toDateString(),
                    'Id_postulante' => $idPersona,
                    'Id_gestion' => $request->input('Id_gestion'),
                ], 'Codigo_inscripcion');
            }

            // 6. Registrar carrera principal
            DB::table('inscripcion_carrera')->insert([
                'Codigo_inscripcion' => $codigoInscripcion,
                'Id_carrera' => $request->input('Id_carrera_principal'),
                'prioridad' => 1,
                'estado' => 'activo',
            ]);

            // 7. Registrar carrera secundaria si existe
            if ($request->filled('Id_carrera_secundaria')) {
                DB::table('inscripcion_carrera')->insert([
                    'Codigo_inscripcion' => $codigoInscripcion,
                    'Id_carrera' => $request->input('Id_carrera_secundaria'),
                    'prioridad' => 2,
                    'estado' => 'activo',
                ]);
            }
            
            $this->actualizarEstadoInscripcionPorDocumentosYPago($codigoInscripcion);

            DB::table('preferencia_inscripcion')->updateOrInsert(
                ['Codigo_inscripcion' => $codigoInscripcion],
                [
                    'Id_modalidad' => $request->input('Id_modalidad_preferencia'),
                    'Id_turno' => $request->input('Id_turno_preferencia'),
                    'estado' => 'activo'
                ]
            );

            $this->registrarBitacora(
                'Inscripcion',
                'Registró o actualizó inscripción del postulante ID ' . $idPersona . '. Usuario creado/actualizado con correo.'
            );

            DB::commit();

            return redirect()->route('inscripcion.index')
                ->with('success', 'Inscripción guardada correctamente. El usuario del postulante es su correo y su contraseña inicial es su CI.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al guardar inscripción: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $request->merge([
            'Id_carrera_principal' => $request->input('Id_carrera_principal') ?: $request->input('Id_carrera'),
        ]);

        $request->validate([
            'ci' => 'required|string|max:20',
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'sexo' => 'nullable|string|max:1',
            'fecha_nacimiento' => 'required|date',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'required|email|max:150',
            'direccion' => 'nullable|string',
            'Id_carrera_principal' => 'required|exists:carrera,Id_carrera',
            'Id_carrera_secundaria' => 'nullable|exists:carrera,Id_carrera|different:Id_carrera_principal',
            'estado' => 'required|string|max:30',
            'Id_gestion' => 'required|exists:gestion,Id_gestion',
            'Id_modalidad_preferencia' => 'required|exists:modalidad,Id_modalidad',
            'Id_turno_preferencia' => 'required|exists:turno,Id_turno',
        ]);

        DB::beginTransaction();

        try {
            $inscripcion = DB::table('inscripcion')
                ->where('Codigo_inscripcion', $id)
                ->first();

            if (!$inscripcion) {
                return redirect()->route('inscripcion.index')
                    ->withErrors(['error' => 'La inscripción no existe.']);
            }

            $idPersona = $inscripcion->Id_postulante;

            DB::table('persona')
                ->where('Id_persona', $idPersona)
                ->update([
                    'ci' => $request->ci,
                    'nombre' => $request->nombre,
                    'apellido' => $request->apellido,
                    'sexo' => $request->sexo,
                    'fecha_nacimiento' => $request->fecha_nacimiento,
                    'telefono' => $request->telefono,
                    'correo' => $request->correo,
                    'direccion' => $request->direccion,
                    'tipo_Postulante' => true,
                ]);

            DB::table('inscripcion')
                ->where('Codigo_inscripcion', $id)
                ->update([
                    'estado' => $request->estado,
                    'Id_gestion' => $request->input('Id_gestion'),
                ]);

            DB::table('inscripcion_carrera')
                ->where('Codigo_inscripcion', $id)
                ->delete();

            DB::table('inscripcion_carrera')->insert([
                'Codigo_inscripcion' => $id,
                'Id_carrera' => $request->input('Id_carrera_principal'),
                'prioridad' => 1,
                'estado' => 'activo',
            ]);

            if ($request->filled('Id_carrera_secundaria')) {
                DB::table('inscripcion_carrera')->insert([
                    'Codigo_inscripcion' => $id,
                    'Id_carrera' => $request->input('Id_carrera_secundaria'),
                    'prioridad' => 2,
                    'estado' => 'activo',
                ]);
            }

            DB::table('preferencia_inscripcion')->updateOrInsert(
                ['Codigo_inscripcion' => $id],
                [
                    'Id_modalidad' => $request->input('Id_modalidad_preferencia'),
                    'Id_turno' => $request->input('Id_turno_preferencia'),
                    'estado' => 'activo'
                ]
            );

            $this->registrarBitacora(
                'Inscripcion',
                'Modificó inscripción código ' . $id . '.'
            );

            DB::commit();

            return redirect()->route('inscripcion.index')
                ->with('success', 'Inscripción actualizada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al actualizar inscripción: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function destroy($id)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        DB::beginTransaction();

        try {
            $inscripcion = DB::table('inscripcion')
                ->where('Codigo_inscripcion', $id)
                ->first();

            if (!$inscripcion) {
                return redirect()->route('inscripcion.index')
                    ->withErrors(['error' => 'La inscripción no existe.']);
            }

            $idPostulante = $inscripcion->Id_postulante;

            // 1. Eliminar asignación de carrera
            DB::table('inscripcion_carrera')
                ->where('Codigo_inscripcion', $id)
                ->delete();

            // 2. Eliminar inscripción
            DB::table('inscripcion')
                ->where('Codigo_inscripcion', $id)
                ->delete();

            // 3. Eliminar asignación de grupo
            DB::table('grupo_postulante')
                ->where('Id_postulante', $idPostulante)
                ->delete();

            // 4. Eliminar notas asociadas
            DB::table('nota')
                ->where('Id_postulante', $idPostulante)
                ->delete();

            // 5. Eliminar asistencia
            DB::table('asistencia')
                ->where('Id_postulante', $idPostulante)
                ->delete();

            // 6. Eliminar resultado académico
            DB::table('resultadoacademico')
                ->where('Id_postulante', $idPostulante)
                ->delete();

            // 7. Eliminar relación persona_documento
            DB::table('persona_documento')
                ->where('Id_persona', $idPostulante)
                ->delete();

            // 8. Eliminar pagos asociados de la inscripción
            DB::table('pago_inscripcion')
                ->where('Codigo_inscripcion', $id)
                ->delete();

            // 9. Eliminar registro de postulante
            DB::table('postulante')
                ->where('Id_postulante', $idPostulante)
                ->delete();

            // 10. Eliminar usuario asociado
            DB::table('usuario')
                ->where('Id_persona', $idPostulante)
                ->delete();

            // 11. Verificar si la persona tiene otros roles (administrador, docente, etc.)
            $persona = DB::table('persona')
                ->where('Id_persona', $idPostulante)
                ->first();

            if ($persona) {
                $tieneOtrosRoles = $persona->tipo_Superadministrador || 
                                   $persona->tipo_Administrador || 
                                   $persona->tipo_Docente;

                if (!$tieneOtrosRoles) {
                    // Si no tiene otros roles, eliminar el registro de persona para liberar CI y correo
                    DB::table('persona')
                        ->where('Id_persona', $idPostulante)
                        ->delete();
                } else {
                    // Si tiene otros roles, solo desactivar el rol de postulante
                    DB::table('persona')
                        ->where('Id_persona', $idPostulante)
                        ->update(['tipo_Postulante' => false]);
                }
            }

            $this->registrarBitacora(
                'Inscripcion',
                'Eliminó la inscripción código ' . $id . ' y liberó los datos de persona y usuario.'
            );

            DB::commit();

            return redirect()->route('inscripcion.index')
                ->with('success', 'Inscripción y usuario asociados eliminados correctamente. El correo y CI han sido liberados.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al eliminar la inscripción: ' . $e->getMessage()
            ]);
        }
    }


    public function documentos($codigo)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $inscripcion = DB::table('inscripcion as i')
            ->join('postulante as po', DB::raw('"po"."Id_postulante"'), '=', DB::raw('"i"."Id_postulante"'))
            ->join('persona as p', DB::raw('"p"."Id_persona"'), '=', DB::raw('"po"."Id_postulante"'))
            ->select(
                DB::raw('"i"."Codigo_inscripcion" as codigo_inscripcion'),
                'i.estado as estado_inscripcion',
                'i.fecha_inscripcion',
                DB::raw('"i"."Id_postulante" as id_postulante'),
                DB::raw('"p"."Id_persona" as id_persona'),
                'p.ci',
                'p.nombre',
                'p.apellido',
                'p.correo'
            )
            ->where('i.Codigo_inscripcion', $codigo)
            ->first();

        if (!$inscripcion) {
            return redirect()->route('inscripcion.index')
                ->withErrors(['error' => 'La inscripción no existe.']);
        }

        $pago = DB::table('pago as pg')
        ->leftJoin('pago_inscripcion as pi', function ($join) use ($codigo) {
            $join->on(DB::raw('"pi"."Id_pago"'), '=', DB::raw('"pg"."Id_pago"'))
                ->where(DB::raw('"pi"."Codigo_inscripcion"'), '=', $codigo);
        })
        ->leftJoin('comprobante as co', DB::raw('"co"."Id_comprobante"'), '=', DB::raw('"pi"."Id_comprobante"'))
        ->select(
            DB::raw('"pg"."Id_pago" as id_pago'),
            'pg.concepto_pago',
            'pg.monto',
            DB::raw('COALESCE(pi.estado_pago_inscripcion, \'Pendiente\') as estado_pago'),
            'pi.fecha_pago',
            'co.nro_comprobante',
            'co.fecha_emision'
        )
        ->whereRaw("LOWER(TRIM(pg.estado_pago)) = 'activo'")
        ->orderBy(DB::raw('"pg"."Id_pago"'), 'asc')
        ->first();

        $documentos = DB::table('documento as doc')
            ->leftJoin('persona_documento as pd', function ($join) use ($inscripcion) {
                $join->on(DB::raw('"pd"."Id_documento"'), '=', DB::raw('"doc"."Id_documento"'))
                    ->where(DB::raw('"pd"."Id_persona"'), '=', $inscripcion->id_postulante);
            })
            ->select(
                DB::raw('"doc"."Id_documento" as id_documento'),
                'doc.nombre',
                'doc.tipo_documento',
                'doc.destinado_a',
                'doc.descripcion',
                DB::raw('COALESCE(pd.estado, \'No presentado\') as estado_documento'),
                'pd.observacion',
                'pd.fecha_revision'
            )
            ->whereRaw("LOWER(TRIM(doc.destinado_a)) = 'postulantes'")
            ->orderBy('doc.nombre')
            ->get();

        return view('Inscripcion_y_Documentacion.documentosPostulante', compact(
            'inscripcion',
            'documentos',
            'pago'
        ));
    }

    public function guardarDocumentos(Request $request, $codigo)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $request->validate([
            'estado_documento' => 'required|array',
            'estado_documento.*' => 'required|in:Aprobado,Presentado,Rechazado,No presentado',
            'observacion' => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            $inscripcion = DB::table('inscripcion')
                ->where('Codigo_inscripcion', $codigo)
                ->first();

            if (!$inscripcion) {
                DB::rollBack();

                return redirect()->route('inscripcion.index')
                    ->withErrors(['error' => 'La inscripción no existe.']);
            }

            $idPostulante = $inscripcion->Id_postulante;

            $idAdministrador = $this->obtenerIdAdministradorActual();

            if (!$idAdministrador) {
                DB::rollBack();

                return back()->withErrors([
                    'error' => 'El usuario actual no está registrado en la tabla administrador. Debe existir como administrador para validar documentos.'
                ]);
            }

            $detallesDocumentos = [];

            foreach ($request->estado_documento as $idDocumento => $estado) {
                $observacion = $request->observacion[$idDocumento] ?? null;

                $nombreDoc = DB::table('documento')
                    ->where('Id_documento', $idDocumento)
                    ->value('nombre') ?? 'Documento';

                $detallesDocumentos[] = [
                    'nombre' => $nombreDoc,
                    'estado' => $estado,
                    'observacion' => $observacion,
                ];

                $existe = DB::table('persona_documento')
                    ->where('Id_persona', $idPostulante)
                    ->where('Id_documento', $idDocumento)
                    ->exists();

                if ($existe) {
                    DB::table('persona_documento')
                        ->where('Id_persona', $idPostulante)
                        ->where('Id_documento', $idDocumento)
                        ->update([
                            'estado' => $estado,
                            'observacion' => $observacion,
                            'fecha_revision' => now()->toDateString(),
                            'Id_administrador' => $idAdministrador,
                        ]);
                } else {
                    DB::table('persona_documento')->insert([
                        'Id_persona' => $idPostulante,
                        'Id_documento' => $idDocumento,
                        'estado' => $estado,
                        'observacion' => $observacion,
                        'fecha_revision' => now()->toDateString(),
                        'Id_administrador' => $idAdministrador,
                    ]);
                }
            }

            $this->actualizarEstadoInscripcionPorDocumentosYPago($codigo);

            // Enviar correo de notificación sobre revisión de documentos al postulante
            $postulanteInfo = DB::table('persona')
                ->where('Id_persona', $idPostulante)
                ->first();

            if ($postulanteInfo && !empty($postulanteInfo->correo)) {
                try {
                    $notificador = new \App\Http\Controllers\Gestion_Academica\enviarNotificacionesController();
                    $notificador->notificarRevisionDocumentos(
                        $postulanteInfo->correo,
                        $postulanteInfo->nombre . ' ' . $postulanteInfo->apellido,
                        $detallesDocumentos
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error("Fallo al enviar notificación de revisión de documentos: " . $e->getMessage());
                }
            }

            $this->registrarBitacora(
                'Inscripcion',
                'Actualizó documentos de la inscripción código ' . $codigo . '.'
            );

            DB::commit();

            return redirect()->route('inscripcion.documentos.form', $codigo)
                ->with('success', 'Documentos actualizados correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al guardar documentos: ' . $e->getMessage()
            ]);
        }
    }

    public function actualizarEstadoInscripcionPorDocumentosYPago($codigoInscripcion)
    {
        $inscripcion = DB::table('inscripcion')
            ->where('Codigo_inscripcion', $codigoInscripcion)
            ->first();

        if (!$inscripcion) {
            return;
        }

        $idPostulante = $inscripcion->Id_postulante;
        $estadoAnterior = $inscripcion->estado;

        // 1. Contar documentos destinados a Postulantes
        $totalDocumentos = DB::table('documento')
            ->whereRaw("LOWER(TRIM(destinado_a)) = 'postulantes'")
            ->count();

        // 2. Contar documentos aprobados del postulante
        $documentosAprobados = DB::table('persona_documento as pd')
            ->join('documento as doc', DB::raw('"doc"."Id_documento"'), '=', DB::raw('"pd"."Id_documento"'))
            ->where('pd.Id_persona', $idPostulante)
            ->whereRaw("LOWER(TRIM(doc.destinado_a)) = 'postulantes'")
            ->where('pd.estado', 'Aprobado')
            ->count();

        // 3. Contar conceptos de pago activos
        $totalPagosActivos = DB::table('pago')
            ->whereRaw("LOWER(TRIM(estado_pago)) = 'activo'")
            ->count();

        // 4. Contar pagos liquidados de esa inscripción
        $pagosLiquidados = DB::table('pago_inscripcion as pi')
            ->join('pago as pg', DB::raw('"pg"."Id_pago"'), '=', DB::raw('"pi"."Id_pago"'))
            ->where('pi.Codigo_inscripcion', $codigoInscripcion)
            ->whereRaw("LOWER(TRIM(pg.estado_pago)) = 'activo'")
            ->where('pi.estado_pago_inscripcion', 'Liquidado')
            ->count();

        // 5. Si todo está aprobado y liquidado, queda Inscrito
        if (
            $totalDocumentos > 0 &&
            $documentosAprobados == $totalDocumentos &&
            $totalPagosActivos > 0 &&
            $pagosLiquidados == $totalPagosActivos
        ) {
            DB::table('inscripcion')
                ->where('Codigo_inscripcion', $codigoInscripcion)
                ->update([
                    'estado' => 'Inscrito',
                ]);

            DB::table('postulante')
                ->where('Id_postulante', $idPostulante)
                ->update([
                    'estado_inscripcion' => 'Inscrito',
                ]);

            // Enviar notificación si pasa a estado Inscrito
            if ($estadoAnterior !== 'Inscrito') {
                $this->enviarNotificacionExitoInscripcion($codigoInscripcion);
            }
        } else {
            DB::table('inscripcion')
                ->where('Codigo_inscripcion', $codigoInscripcion)
                ->update([
                    'estado' => 'En_Revision',
                ]);

            DB::table('postulante')
                ->where('Id_postulante', $idPostulante)
                ->update([
                    'estado_inscripcion' => 'En_Revision',
                ]);
        }
    }

    private function obtenerIdAdministradorActual()
    {
        if (!Auth::check()) {
            return null;
        }

        $usuario = DB::table('usuario')
            ->where('Id_usuario', Auth::id())
            ->first();

        if (!$usuario) {
            return null;
        }

        $idPersona = $usuario->Id_persona ?? null;

        if (!$idPersona) {
            return null;
        }

        $existeAdministrador = DB::table('administrador')
            ->where('Id_administrador', $idPersona)
            ->exists();

        if (!$existeAdministrador) {
            return null;
        }

        return $idPersona;
    }

    private function enviarNotificacionExitoInscripcion($codigoInscripcion)
    {
        try {
            $inscripcion = DB::table('inscripcion as i')
                ->join('persona as p', 'p.Id_persona', '=', 'i.Id_postulante')
                ->select('p.correo', 'p.nombre', 'p.apellido')
                ->where('i.Codigo_inscripcion', $codigoInscripcion)
                ->first();

            if ($inscripcion && !empty($inscripcion->correo)) {
                $correo = $inscripcion->correo;
                $nombreCompleto = $inscripcion->nombre . ' ' . $inscripcion->apellido;

                $titulo = 'Inscripción Confirmada - CUP FICCT';
                $mensaje = "Hola, {$nombreCompleto}.\n\nTe informamos que tu inscripción ha sido procesada de manera exitosa.\n\nDetalles:\n- Tus documentos han sido validados y son válidos.\n- Tu pago se ha efectuado correctamente.\n\n¡Bienvenido al sistema académico!";

                $notificador = new \App\Http\Controllers\Gestion_Academica\enviarNotificacionesController();
                $notificador->enviarNotificacion(
                    $correo,
                    $titulo,
                    $mensaje,
                    'inscripción confirmada',
                    $nombreCompleto,
                    false
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Fallo al enviar notificación de éxito de inscripción: " . $e->getMessage());
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

    private function validarPrerrequisitos()
    {
        if (DB::table('gestion')->count() === 0 || DB::table('carrera')->count() === 0 || DB::table('modalidad')->count() === 0 || DB::table('turno')->count() === 0) {
            return redirect()->route('menu')->withErrors([
                'error' => 'Debe registrar al menos una gestión, carrera, modalidad y turno antes de gestionar inscripciones.'
            ]);
        }
        return null;
    }
}