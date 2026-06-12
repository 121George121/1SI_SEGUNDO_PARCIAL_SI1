<?php

namespace App\Http\Controllers\Gestion_Academica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class gestionarEvaluacionesYNotasController extends Controller
{
    public function index(Request $request)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $evaluaciones = DB::table('evaluacion as e')
            ->join('grupo as g', DB::raw('"g"."Id_grupo"'), '=', DB::raw('"e"."Id_grupo"'))
            ->join('materia as m', DB::raw('"m"."Id_materia"'), '=', DB::raw('"e"."Id_materia"'))
            ->select(
                DB::raw('"e"."Id_evaluacion" as id_evaluacion'),
                'e.numero_evaluacion',
                'e.porcentaje',
                'e.fecha',
                'e.estado',
                DB::raw('"e"."Id_grupo" as id_grupo'),
                DB::raw('"e"."Id_materia" as id_materia'),
                'g.sigla_grupo',
                'm.nombre as nombre_materia'
            )
            ->orderBy(DB::raw('"e"."Id_evaluacion"'), 'desc')
            ->get();

        $gruposMaterias = DB::table('grupo_materia as gm')
            ->join('grupo as g', DB::raw('"g"."Id_grupo"'), '=', DB::raw('"gm"."Id_grupo"'))
            ->join('materia as m', DB::raw('"m"."Id_materia"'), '=', DB::raw('"gm"."Id_materia"'))
            ->join('docente as d', DB::raw('"d"."Id_docente"'), '=', DB::raw('"gm"."Id_docente"'))
            ->join('persona as p', DB::raw('"p"."Id_persona"'), '=', DB::raw('"d"."Id_docente"'))
            ->select(
                DB::raw('"gm"."Id_grupo" as id_grupo'),
                DB::raw('"gm"."Id_materia" as id_materia'),
                'g.sigla_grupo',
                'm.nombre as nombre_materia',
                'p.nombre as nombre_docente',
                'p.apellido as apellido_docente'
            )
            ->orderBy('g.sigla_grupo')
            ->orderBy('m.nombre')
            ->get();

        $postulantesGrupo = DB::table('grupo_postulante as gp')
            ->join('grupo as g', DB::raw('"g"."Id_grupo"'), '=', DB::raw('"gp"."Id_grupo"'))
            ->join('postulante as po', DB::raw('"po"."Id_postulante"'), '=', DB::raw('"gp"."Id_postulante"'))
            ->join('persona as p', DB::raw('"p"."Id_persona"'), '=', DB::raw('"po"."Id_postulante"'))
            ->select(
                DB::raw('"gp"."Id_grupo" as id_grupo'),
                DB::raw('"gp"."Id_postulante" as id_postulante'),
                'g.sigla_grupo',
                'p.ci',
                'p.nombre',
                'p.apellido'
            )
            ->orderBy('g.sigla_grupo')
            ->orderBy('p.nombre')
            ->get();

        $notas = DB::table('nota as n')
            ->join('evaluacion as e', DB::raw('"e"."Id_evaluacion"'), '=', DB::raw('"n"."Id_evaluacion"'))
            ->join('grupo as g', DB::raw('"g"."Id_grupo"'), '=', DB::raw('"n"."Id_grupo"'))
            ->join('materia as m', DB::raw('"m"."Id_materia"'), '=', DB::raw('"e"."Id_materia"'))
            ->join('postulante as po', DB::raw('"po"."Id_postulante"'), '=', DB::raw('"n"."Id_postulante"'))
            ->join('persona as p', DB::raw('"p"."Id_persona"'), '=', DB::raw('"po"."Id_postulante"'))
            ->select(
                DB::raw('"n"."Id_nota" as id_nota'),
                DB::raw('"n"."Id_evaluacion" as id_evaluacion'),
                DB::raw('"n"."Id_grupo" as id_grupo'),
                DB::raw('"n"."Id_postulante" as id_postulante'),
                'n.nota',
                'n.estado_academico',
                'n.fecha',
                'e.numero_evaluacion',
                'e.porcentaje',
                'g.sigla_grupo',
                'm.nombre as nombre_materia',
                'p.ci',
                'p.nombre',
                'p.apellido'
            )
            ->orderBy(DB::raw('"n"."Id_nota"'), 'desc')
            ->get();

        // Filtrar evaluaciones por el grupo y materia seleccionados
        $evaluacionesFiltradas = collect();
        if ($request->filled('id_grupo') && $request->filled('id_materia')) {
            $evaluacionesFiltradas = DB::table('evaluacion')
                ->where('Id_grupo', $request->id_grupo)
                ->where('Id_materia', $request->id_materia)
                ->orderBy('numero_evaluacion')
                ->get();
        }

        // Cargar planilla de estudiantes con notas correspondientes
        $estudiantesPlanilla = collect();
        if ($request->filled('id_grupo') && $request->filled('id_evaluacion')) {
            $estudiantesPlanilla = DB::table('grupo_postulante as gp')
                ->join('postulante as po', DB::raw('"po"."Id_postulante"'), '=', DB::raw('"gp"."Id_postulante"'))
                ->join('persona as p', DB::raw('"p"."Id_persona"'), '=', DB::raw('"po"."Id_postulante"'))
                ->leftJoin('nota as n', function($join) use ($request) {
                    $join->on(DB::raw('"n"."Id_postulante"'), '=', DB::raw('"gp"."Id_postulante"'))
                         ->where('n.Id_evaluacion', '=', $request->id_evaluacion);
                })
                ->select(
                    DB::raw('"gp"."Id_postulante" as id_postulante'),
                    'p.ci',
                    'p.nombre',
                    'p.apellido',
                    'n.nota',
                    'n.estado_academico',
                    DB::raw('"n"."Id_nota" as id_nota')
                )
                ->where('gp.Id_grupo', $request->id_grupo)
                ->orderBy('p.nombre')
                ->orderBy('p.apellido')
                ->get();
        }

        return view('Gestion_Academica.gestionarEvaluacionesYNotas', compact(
            'evaluaciones',
            'gruposMaterias',
            'postulantesGrupo',
            'notas',
            'evaluacionesFiltradas',
            'estudiantesPlanilla'
        ));
    }

    public function storeEvaluacion(Request $request)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $request->validate([
            'Id_grupo' => 'required|exists:grupo,Id_grupo',
            'Id_materia' => 'required|exists:materia,Id_materia',
            'numero_evaluacion' => 'required|integer|min:1',
            'porcentaje' => 'required|numeric|min:1|max:100',
            'fecha' => 'required|date',
            'estado' => 'required|in:activo,inactivo',
        ]);

        $existeGrupoMateria = DB::table('grupo_materia')
            ->where('Id_grupo', $request->Id_grupo)
            ->where('Id_materia', $request->Id_materia)
            ->exists();

        if (!$existeGrupoMateria) {
            return back()->withErrors([
                'error' => 'Ese grupo no tiene asignada esa materia.'
            ])->withInput();
        }

        $existeEvaluacion = DB::table('evaluacion')
            ->where('Id_grupo', $request->Id_grupo)
            ->where('Id_materia', $request->Id_materia)
            ->where('numero_evaluacion', $request->numero_evaluacion)
            ->exists();

        if ($existeEvaluacion) {
            return back()->withErrors([
                'error' => 'Ya existe esa evaluación para ese grupo y materia.'
            ])->withInput();
        }

        DB::table('evaluacion')->insert([
            'numero_evaluacion' => $request->numero_evaluacion,
            'porcentaje' => $request->porcentaje,
            'fecha' => $request->fecha,
            'estado' => $request->estado,
            'Id_grupo' => $request->Id_grupo,
            'Id_materia' => $request->Id_materia,
        ]);

        $this->registrarBitacora('Registró evaluación número '.$request->numero_evaluacion);

        return redirect()->route('evaluaciones-notas.index')
            ->with('success', 'Evaluación registrada correctamente.');
    }

    public function updateEvaluacion(Request $request, $id)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $request->validate([
            'Id_grupo' => 'required|exists:grupo,Id_grupo',
            'Id_materia' => 'required|exists:materia,Id_materia',
            'numero_evaluacion' => 'required|integer|min:1',
            'porcentaje' => 'required|numeric|min:1|max:100',
            'fecha' => 'required|date',
            'estado' => 'required|in:activo,inactivo',
        ]);

        $existeGrupoMateria = DB::table('grupo_materia')
            ->where('Id_grupo', $request->Id_grupo)
            ->where('Id_materia', $request->Id_materia)
            ->exists();

        if (!$existeGrupoMateria) {
            return back()->withErrors([
                'error' => 'Ese grupo no tiene asignada esa materia.'
            ])->withInput();
        }

        $existeEvaluacion = DB::table('evaluacion')
            ->where('Id_evaluacion', '!=', $id)
            ->where('Id_grupo', $request->Id_grupo)
            ->where('Id_materia', $request->Id_materia)
            ->where('numero_evaluacion', $request->numero_evaluacion)
            ->exists();

        if ($existeEvaluacion) {
            return back()->withErrors([
                'error' => 'Ya existe esa evaluación para ese grupo y materia.'
            ])->withInput();
        }

        DB::table('evaluacion')
            ->where('Id_evaluacion', $id)
            ->update([
                'numero_evaluacion' => $request->numero_evaluacion,
                'porcentaje' => $request->porcentaje,
                'fecha' => $request->fecha,
                'estado' => $request->estado,
                'Id_grupo' => $request->Id_grupo,
                'Id_materia' => $request->Id_materia,
            ]);

        $this->registrarBitacora('Actualizó evaluación ID '.$id);

        return redirect()->route('evaluaciones-notas.index')
            ->with('success', 'Evaluación actualizada correctamente.');
    }

    public function destroyEvaluacion($id)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        DB::table('evaluacion')
            ->where('Id_evaluacion', $id)
            ->delete();

        $this->registrarBitacora('Eliminó evaluación ID '.$id);

        return redirect()->route('evaluaciones-notas.index')
            ->with('success', 'Evaluación eliminada correctamente.');
    }

    public function storeNota(Request $request)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $request->validate([
            'Id_evaluacion' => 'required|exists:evaluacion,Id_evaluacion',
            'Id_postulante' => 'required|exists:postulante,Id_postulante',
            'nota' => 'required|numeric|min:0|max:100',
            'estado_academico' => 'required|string|max:20',
        ]);

        $evaluacion = DB::table('evaluacion')
            ->where('Id_evaluacion', $request->Id_evaluacion)
            ->first();

        if (!$evaluacion) {
            return back()->withErrors([
                'error' => 'La evaluación no existe.'
            ])->withInput();
        }

        $perteneceGrupo = DB::table('grupo_postulante')
            ->where('Id_grupo', $evaluacion->Id_grupo)
            ->where('Id_postulante', $request->Id_postulante)
            ->exists();

        if (!$perteneceGrupo) {
            return back()->withErrors([
                'error' => 'El postulante no pertenece al grupo de esta evaluación.'
            ])->withInput();
        }

        $existeNota = DB::table('nota')
            ->where('Id_evaluacion', $request->Id_evaluacion)
            ->where('Id_postulante', $request->Id_postulante)
            ->exists();

        if ($existeNota) {
            return back()->withErrors([
                'error' => 'Ese postulante ya tiene nota registrada para esta evaluación.'
            ])->withInput();
        }

        DB::table('nota')->insert([
            'nota' => $request->nota,
            'estado_academico' => $request->estado_academico,
            'fecha' => now()->toDateString(),
            'Id_evaluacion' => $request->Id_evaluacion,
            'Id_grupo' => $evaluacion->Id_grupo,
            'Id_postulante' => $request->Id_postulante,
        ]);

        $this->registrarBitacora('Registró nota de postulante.');

        return redirect()->route('evaluaciones-notas.index')
            ->with('success', 'Nota registrada correctamente.');
    }

    public function updateNota(Request $request, $id)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $request->validate([
            'Id_evaluacion' => 'required|exists:evaluacion,Id_evaluacion',
            'Id_postulante' => 'required|exists:postulante,Id_postulante',
            'nota' => 'required|numeric|min:0|max:100',
            'estado_academico' => 'required|string|max:20',
        ]);

        $evaluacion = DB::table('evaluacion')
            ->where('Id_evaluacion', $request->Id_evaluacion)
            ->first();

        if (!$evaluacion) {
            return back()->withErrors([
                'error' => 'La evaluación no existe.'
            ])->withInput();
        }

        $perteneceGrupo = DB::table('grupo_postulante')
            ->where('Id_grupo', $evaluacion->Id_grupo)
            ->where('Id_postulante', $request->Id_postulante)
            ->exists();

        if (!$perteneceGrupo) {
            return back()->withErrors([
                'error' => 'El postulante no pertenece al grupo de esta evaluación.'
            ])->withInput();
        }

        $existeNota = DB::table('nota')
            ->where('Id_nota', '!=', $id)
            ->where('Id_evaluacion', $request->Id_evaluacion)
            ->where('Id_postulante', $request->Id_postulante)
            ->exists();

        if ($existeNota) {
            return back()->withErrors([
                'error' => 'Ese postulante ya tiene nota registrada para esta evaluación.'
            ])->withInput();
        }

        DB::table('nota')
            ->where('Id_nota', $id)
            ->update([
                'nota' => $request->nota,
                'estado_academico' => $request->estado_academico,
                'Id_evaluacion' => $request->Id_evaluacion,
                'Id_grupo' => $evaluacion->Id_grupo,
                'Id_postulante' => $request->Id_postulante,
            ]);

        $this->registrarBitacora('Actualizó nota ID '.$id);

        return redirect()->route('evaluaciones-notas.index')
            ->with('success', 'Nota actualizada correctamente.');
    }

    public function destroyNota($id)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        DB::table('nota')
            ->where('Id_nota', $id)
            ->delete();

        $this->registrarBitacora('Eliminó nota ID '.$id);

        return redirect()->route('evaluaciones-notas.index')
            ->with('success', 'Nota eliminada correctamente.');
    }

    public function guardarNotasLote(Request $request)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $request->validate([
            'Id_evaluacion' => 'required|exists:evaluacion,Id_evaluacion',
            'Id_grupo' => 'required|exists:grupo,Id_grupo',
            'notas' => 'required|array',
            'estados' => 'required|array',
        ]);

        $idEvaluacion = $request->Id_evaluacion;
        $idGrupo = $request->Id_grupo;

        DB::transaction(function () use ($request, $idEvaluacion, $idGrupo) {
            foreach ($request->notas as $idPostulante => $valorNota) {
                if ($valorNota === null || $valorNota === '') {
                    // Si el input está vacío, eliminamos la nota si existiera
                    DB::table('nota')
                        ->where('Id_evaluacion', $idEvaluacion)
                        ->where('Id_postulante', $idPostulante)
                        ->delete();
                    continue;
                }

                $estadoAcademico = $request->estados[$idPostulante] ?? 'Observado';

                DB::table('nota')->updateOrInsert(
                    [
                        'Id_evaluacion' => $idEvaluacion,
                        'Id_postulante' => $idPostulante,
                    ],
                    [
                        'nota' => $valorNota,
                        'estado_academico' => $estadoAcademico,
                        'fecha' => now()->toDateString(),
                        'Id_grupo' => $idGrupo,
                    ]
                );
            }
        });

        $this->registrarBitacora('Registró/Actualizó notas en lote para evaluación ID '.$idEvaluacion);

        return redirect()->route('evaluaciones-notas.index', [
            'tab' => 'notas',
            'id_grupo' => $idGrupo,
            'id_materia' => $request->id_materia,
            'id_evaluacion' => $idEvaluacion
        ])->with('success', 'Planilla de notas actualizada correctamente.');
    }

    private function registrarBitacora(string $descripcion): void
    {
        if (!Auth::check()) {
            return;
        }

        DB::table('bitacora')->insert([
            'tipo' => 'Gestion Academica',
            'descripcion' => $descripcion,
            'fecha' => now()->toDateString(),
            'hora' => now()->format('H:i:s'),
            'estado' => 'activo',
            'Id_usuario' => Auth::id(),
        ]);
    }

    private function validarPrerrequisitos()
    {
        if (DB::table('grupo')->count() === 0) {
            return redirect()->route('menu')->withErrors([
                'error' => 'Debe registrar al menos un grupo antes de gestionar evaluaciones y notas.'
            ]);
        }
        return null;
    }
}