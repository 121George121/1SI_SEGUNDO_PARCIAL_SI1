<?php

namespace App\Http\Controllers\Usuario_Seguridad_y_Auditoria;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * DocenteController
 *
 * Maneja el portal privado del Docente.
 * Todas las consultas filtran siempre por el docente autenticado para
 * garantizar que no pueda ver datos de otros docentes.
 * Las vistas son SOLO LECTURA: sin registro, edición ni eliminación.
 */
class DocenteController extends Controller
{
    // ─────────────────────────────────────────────────
    // Helper: obtener Id_persona del usuario autenticado
    // y verificar que sea docente
    // ─────────────────────────────────────────────────
    private function obtenerIdDocente()
    {
        $usuario  = Auth::user();
        $persona  = $usuario?->persona;

        if (!$persona || !$persona->tipo_Docente) {
            abort(403, 'Acceso denegado. Solo los docentes pueden ver esta sección.');
        }

        // Verificar que existe en tabla docente
        $docente = DB::table('docente')->where('Id_docente', $persona->Id_persona)->first();
        if (!$docente) {
            abort(403, 'No existe un registro de docente para este usuario.');
        }

        return $persona->Id_persona;
    }

    // ─────────────────────────────────────────────────
    // 1. PERFIL DEL DOCENTE
    // ─────────────────────────────────────────────────
    public function perfil()
    {
        $idDocente = $this->obtenerIdDocente();
        $usuario   = Auth::user();
        $persona   = $usuario->persona;

        // Datos del registro docente (anio_servicio, estado)
        $docente = DB::table('docente')->where('Id_docente', $idDocente)->first();

        // Especialidades del docente
        $especialidades = DB::table('docente_especialidad as de')
            ->join('especialidad as e', 'e.Id_especialidad', '=', 'de.Id_especialidad')
            ->join('materia as m', 'm.Id_materia', '=', 'e.id_materia')
            ->select('e.nombre_especialidad', 'm.nombre as nombre_materia')
            ->where('de.Id_docente', $idDocente)
            ->get();

        return view('Usuario_Seguridad_y_Auditoria.Vista_Docente.perfil', compact(
            'usuario',
            'persona',
            'docente',
            'especialidades'
        ));
    }

    // ─────────────────────────────────────────────────
    // 2. MIS MATERIAS
    // Solo las materias asignadas al docente autenticado
    // a través de grupo_materia
    // ─────────────────────────────────────────────────
    public function materias()
    {
        $idDocente = $this->obtenerIdDocente();

        // grupo_materia relaciona docente ↔ materia ↔ grupo
        $materias = DB::table('grupo_materia as gm')
            ->join('materia as m', 'm.Id_materia', '=', 'gm.Id_materia')
            ->join('grupo as g',   'g.Id_grupo',   '=', 'gm.Id_grupo')
            ->join('gestion as ge','ge.Id_gestion', '=', 'g.Id_gestion')
            ->leftJoin('grupo_horario as gh', function ($join) {
                $join->on('gh.Id_grupo',   '=', 'gm.Id_grupo')
                     ->on('gh.Id_materia', '=', 'gm.Id_materia');
            })
            ->leftJoin('horario as h', 'h.Id_horario', '=', 'gh.Id_horario')
            ->select(
                'm.Id_materia',
                'm.nombre as nombre_materia',
                'm.descripcion as descripcion_materia',
                'm.estado as estado_materia',
                'g.Id_grupo',
                'g.sigla_grupo',
                'ge.anio',
                'ge.periodo',
                DB::raw("STRING_AGG(DISTINCT (h.dia || ' ' || TO_CHAR(h.hora_inicio,'HH24:MI') || '-' || TO_CHAR(h.hora_fin,'HH24:MI')), ', ') as horarios")
            )
            ->where('gm.Id_docente', $idDocente)
            ->groupBy(
                'm.Id_materia', 'm.nombre', 'm.descripcion', 'm.estado',
                'g.Id_grupo', 'g.sigla_grupo', 'ge.anio', 'ge.periodo'
            )
            ->orderBy('m.nombre')
            ->get();

        return view('Usuario_Seguridad_y_Auditoria.Vista_Docente.materias', compact('materias'));
    }

    // ─────────────────────────────────────────────────
    // 3. MIS GRUPOS
    // Solo los grupos donde el docente está asignado
    // ─────────────────────────────────────────────────
    public function grupos()
    {
        $idDocente = $this->obtenerIdDocente();

        $grupos = DB::table('grupo_materia as gm')
            ->join('grupo as g',    'g.Id_grupo',    '=', 'gm.Id_grupo')
            ->join('materia as m',  'm.Id_materia',  '=', 'gm.Id_materia')
            ->join('turno as t',    't.Id_turno',    '=', 'g.Id_turno')
            ->join('modalidad as mo','mo.Id_modalidad','=','g.Id_modalidad')
            ->join('gestion as ge', 'ge.Id_gestion', '=', 'g.Id_gestion')
            ->leftJoin('grupo_horario as gh', function ($join) {
                $join->on('gh.Id_grupo',   '=', 'gm.Id_grupo')
                     ->on('gh.Id_materia', '=', 'gm.Id_materia');
            })
            ->leftJoin('horario as h', 'h.Id_horario', '=', 'gh.Id_horario')
            ->select(
                'g.Id_grupo',
                'g.sigla_grupo',
                'g.estado as estado_grupo',
                'g.cant_estudiantes',
                'g.capacidad_max',
                'm.nombre as nombre_materia',
                't.nombre as turno',
                'mo.nombre_modalidad as modalidad',
                'ge.anio',
                'ge.periodo',
                DB::raw("STRING_AGG(DISTINCT (h.dia || ' ' || TO_CHAR(h.hora_inicio,'HH24:MI') || '-' || TO_CHAR(h.hora_fin,'HH24:MI')), ', ') as horarios")
            )
            ->where('gm.Id_docente', $idDocente)
            ->groupBy(
                'g.Id_grupo', 'g.sigla_grupo', 'g.estado', 'g.cant_estudiantes', 'g.capacidad_max',
                'm.nombre', 't.nombre', 'mo.nombre_modalidad', 'ge.anio', 'ge.periodo'
            )
            ->orderBy('g.sigla_grupo')
            ->get();

        return view('Usuario_Seguridad_y_Auditoria.Vista_Docente.grupos', compact('grupos'));
    }

    // ─────────────────────────────────────────────────
    // 4. ESTUDIANTES DE UN GRUPO
    // Solo si ese grupo pertenece al docente autenticado
    // ─────────────────────────────────────────────────
    public function estudiantesGrupo($idGrupo)
    {
        $idDocente = $this->obtenerIdDocente();

        // Verificar que el grupo está asignado a este docente
        $perteneceAlDocente = DB::table('grupo_materia')
            ->where('Id_grupo',   $idGrupo)
            ->where('Id_docente', $idDocente)
            ->exists();

        if (!$perteneceAlDocente) {
            abort(403, 'No tienes acceso a los estudiantes de este grupo.');
        }

        // Datos del grupo
        $grupo = DB::table('grupo as g')
            ->join('turno as t',      't.Id_turno',      '=', 'g.Id_turno')
            ->join('modalidad as mo', 'mo.Id_modalidad', '=', 'g.Id_modalidad')
            ->join('gestion as ge',   'ge.Id_gestion',   '=', 'g.Id_gestion')
            ->select(
                'g.Id_grupo',
                'g.sigla_grupo',
                'g.estado as estado_grupo',
                'g.cant_estudiantes',
                't.nombre as turno',
                'mo.nombre_modalidad as modalidad',
                'ge.anio',
                'ge.periodo'
            )
            ->where('g.Id_grupo', $idGrupo)
            ->first();

        if (!$grupo) {
            return redirect()->route('docente.grupos')->withErrors(['error' => 'El grupo no existe.']);
        }

        // Materias del grupo asignadas a este docente
        $materias = DB::table('grupo_materia as gm')
            ->join('materia as m', 'm.Id_materia', '=', 'gm.Id_materia')
            ->where('gm.Id_grupo',   $idGrupo)
            ->where('gm.Id_docente', $idDocente)
            ->pluck('m.nombre')
            ->implode(', ');

        // Horarios del grupo (para las materias de este docente)
        $horarios = DB::table('grupo_horario as gh')
            ->join('horario as h',   'h.Id_horario',  '=', 'gh.Id_horario')
            ->join('materia as m',   'm.Id_materia',  '=', 'gh.Id_materia')
            ->join('grupo_materia as gm', function($join) use ($idGrupo, $idDocente) {
                $join->on('gm.Id_grupo',   '=', 'gh.Id_grupo')
                     ->on('gm.Id_materia', '=', 'gh.Id_materia')
                     ->where('gm.Id_docente', $idDocente);
            })
            ->select('h.dia', 'h.hora_inicio', 'h.hora_fin', 'm.nombre as materia')
            ->where('gh.Id_grupo', $idGrupo)
            ->get();

        // Lista de estudiantes del grupo
        $estudiantes = DB::table('grupo_postulante as gp')
            ->join('persona as p',      'p.Id_persona',     '=', 'gp.Id_postulante')
            ->join('postulante as po',  'po.Id_postulante', '=', 'gp.Id_postulante')
            ->join('inscripcion as i',  'i.Id_postulante',  '=', 'gp.Id_postulante')
            ->leftJoin('asignacioncupo as ac', 'ac.Id_asignacioncupo', '=', 'po.Id_asignacioncupo')
            ->leftJoin('resultadoacademico as ra', 'ra.Id_postulante', '=', 'po.Id_postulante')
            ->select(
                'p.ci',
                'p.nombre',
                'p.apellido',
                'p.correo',
                'po.estado_inscripcion',
                'i.estado as estado_inscripcion_detalle',
                'gp.estado as estado_grupo',
                'gp.fecha_asignacion',
                'ac.estado_asignacion',
                'ra.estado_final'
            )
            ->where('gp.Id_grupo', $idGrupo)
            ->orderBy('p.apellido')
            ->get();

        return view('Usuario_Seguridad_y_Auditoria.Vista_Docente.estudiantes-grupo', compact(
            'grupo',
            'materias',
            'horarios',
            'estudiantes'
        ));
    }

    // ─────────────────────────────────────────────────
    // 5. NOTAS (solo lectura)
    // Muestra las notas de los grupos asignados al docente
    // ─────────────────────────────────────────────────
    public function notas(Request $request)
    {
        $idDocente = $this->obtenerIdDocente();

        // IDs de grupos del docente
        $idsGrupos = DB::table('grupo_materia')
            ->where('Id_docente', $idDocente)
            ->pluck('Id_grupo')
            ->unique()
            ->values();

        // Filtro de grupo opcional
        $grupoFiltro = $request->input('grupo_id');

        $notas = DB::table('nota as n')
            ->join('evaluacion as ev',   'ev.Id_evaluacion', '=', 'n.Id_evaluacion')
            ->join('materia as m',        'm.Id_materia',     '=', 'ev.Id_materia')
            ->join('grupo as g',          'g.Id_grupo',       '=', 'n.Id_grupo')
            ->join('persona as p',        'p.Id_persona',     '=', 'n.Id_postulante')
            ->select(
                'n.Id_nota',
                'n.nota',
                'n.estado_academico',
                'n.fecha',
                'ev.numero_evaluacion',
                'm.nombre as nombre_materia',
                'g.Id_grupo',
                'g.sigla_grupo',
                'p.ci',
                'p.nombre as nombre_estudiante',
                'p.apellido as apellido_estudiante'
            )
            ->whereIn('n.Id_grupo', $idsGrupos)
            ->when($grupoFiltro, fn($q) => $q->where('n.Id_grupo', $grupoFiltro))
            ->orderBy('g.sigla_grupo')
            ->orderBy('p.apellido')
            ->orderBy('ev.numero_evaluacion')
            ->get();

        // Lista de grupos para el filtro
        $gruposDisponibles = DB::table('grupo_materia as gm')
            ->join('grupo as g', 'g.Id_grupo', '=', 'gm.Id_grupo')
            ->select('g.Id_grupo', 'g.sigla_grupo')
            ->where('gm.Id_docente', $idDocente)
            ->distinct()
            ->get();

        return view('Usuario_Seguridad_y_Auditoria.Vista_Docente.notas', compact(
            'notas',
            'gruposDisponibles',
            'grupoFiltro'
        ));
    }
}
