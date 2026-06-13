<?php

namespace App\Http\Controllers\Gestion_Academica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class gestionarGruposController extends Controller
{
    public function index()
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $grupos = DB::table('grupo as gr')
            ->leftJoin('aula as a', DB::raw('"a"."Id_aula"'), '=', DB::raw('"gr"."Id_aula"'))
            ->leftJoin('modalidad as m', DB::raw('"m"."Id_modalidad"'), '=', DB::raw('"gr"."Id_modalidad"'))
            ->leftJoin('turno as t', DB::raw('"t"."Id_turno"'), '=', DB::raw('"gr"."Id_turno"'))
            ->leftJoin('gestion as g', DB::raw('"g"."Id_gestion"'), '=', DB::raw('"gr"."Id_gestion"'))
            ->select(
                DB::raw('"gr"."Id_grupo" as id_grupo'),
                'gr.sigla_grupo',
                'gr.capacidad_max',
                'gr.estado',
                'gr.cant_estudiantes',

                DB::raw('"gr"."Id_aula" as id_aula'),
                'a.nro_aula',
                'a.capacidad as capacidad_aula',
                'a.ubicacion',

                DB::raw('"gr"."Id_modalidad" as id_modalidad'),
                'm.nombre_modalidad',

                DB::raw('"gr"."Id_turno" as id_turno'),
                't.nombre as nombre_turno',

                DB::raw('"gr"."Id_gestion" as id_gestion'),
                'g.anio',
                'g.periodo'
            )
            ->orderBy(DB::raw('"gr"."Id_grupo"'), 'desc')
            ->get();

        $aulas = DB::table('aula')
            ->select(
                DB::raw('"Id_aula" as id_aula'),
                'nro_aula',
                'capacidad',
                'ubicacion',
                'estado'
            )
            ->whereRaw("LOWER(TRIM(estado)) = 'activo'")
            ->orderBy('nro_aula')
            ->get();

        $modalidades = DB::table('modalidad')
            ->select(
                DB::raw('"Id_modalidad" as id_modalidad'),
                'nombre_modalidad',
                'estado'
            )
            ->whereRaw("LOWER(TRIM(estado)) = 'activo'")
            ->orderBy('nombre_modalidad')
            ->get();

        $turnos = DB::table('turno')
            ->select(
                DB::raw('"Id_turno" as id_turno'),
                'nombre',
                'estado'
            )
            ->whereRaw("LOWER(TRIM(estado)) = 'activo'")
            ->orderBy('nombre')
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
            ->orderBy('periodo')
            ->get();

        return view('Gestion_Academica.gestionarGrupos', compact(
            'grupos',
            'aulas',
            'modalidades',
            'turnos',
            'gestiones'
        ));
    }

    public function store(Request $request)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $request->validate([
            'sigla_grupo' => 'required|string|max:50',
            'capacidad_max' => 'required|integer|min:1',
            'cant_estudiantes' => 'required|integer|min:0',
            'estado' => 'required|in:activo,inactivo',
            'Id_aula' => 'required|exists:aula,Id_aula',
            'Id_modalidad' => 'required|exists:modalidad,Id_modalidad',
            'Id_turno' => 'required|exists:turno,Id_turno',
            'Id_gestion' => 'required|exists:gestion,Id_gestion',
        ]);

        $aula = DB::table('aula')
            ->select('capacidad', 'nro_aula')
            ->where('Id_aula', $request->Id_aula)
            ->first();

        if ($aula && $aula->capacidad !== null && $request->capacidad_max > $aula->capacidad) {
            return back()->withErrors([
                'error' => 'La capacidad máxima del grupo no puede superar la capacidad del aula.'
            ])->withInput();
        }

        if ($request->cant_estudiantes > $request->capacidad_max) {
            return back()->withErrors([
                'error' => 'La cantidad de estudiantes no puede superar la capacidad máxima del grupo.'
            ])->withInput();
        }

        DB::beginTransaction();

        try {
            DB::table('grupo')->insert([
                'sigla_grupo' => $request->sigla_grupo,
                'capacidad_max' => $request->capacidad_max,
                'estado' => $request->estado,
                'cant_estudiantes' => $request->cant_estudiantes,
                'Id_aula' => $request->Id_aula,
                'Id_modalidad' => $request->Id_modalidad,
                'Id_turno' => $request->Id_turno,
                'Id_gestion' => $request->Id_gestion,
            ]);

            $this->registrarBitacora(
                'Gestion Academica',
                'Registró el grupo ' . $request->sigla_grupo . '.'
            );

            DB::commit();

            return redirect()->route('grupos.index')
                ->with('success', 'Grupo registrado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al registrar grupo: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $request->validate([
            'sigla_grupo' => 'required|string|max:50',
            'capacidad_max' => 'required|integer|min:1',
            'cant_estudiantes' => 'required|integer|min:0',
            'estado' => 'required|in:activo,inactivo',
            'Id_aula' => 'required|exists:aula,Id_aula',
            'Id_modalidad' => 'required|exists:modalidad,Id_modalidad',
            'Id_turno' => 'required|exists:turno,Id_turno',
            'Id_gestion' => 'required|exists:gestion,Id_gestion',
        ]);

        $aula = DB::table('aula')
            ->select('capacidad', 'nro_aula')
            ->where('Id_aula', $request->Id_aula)
            ->first();

        if ($aula && $aula->capacidad !== null && $request->capacidad_max > $aula->capacidad) {
            return back()->withErrors([
                'error' => 'La capacidad máxima del grupo no puede superar la capacidad del aula.'
            ])->withInput();
        }

        if ($request->cant_estudiantes > $request->capacidad_max) {
            return back()->withErrors([
                'error' => 'La cantidad de estudiantes no puede superar la capacidad máxima del grupo.'
            ])->withInput();
        }

        DB::beginTransaction();

        try {
            DB::table('grupo')
                ->where('Id_grupo', $id)
                ->update([
                    'sigla_grupo' => $request->sigla_grupo,
                    'capacidad_max' => $request->capacidad_max,
                    'estado' => $request->estado,
                    'cant_estudiantes' => $request->cant_estudiantes,
                    'Id_aula' => $request->Id_aula,
                    'Id_modalidad' => $request->Id_modalidad,
                    'Id_turno' => $request->Id_turno,
                    'Id_gestion' => $request->Id_gestion,
                ]);

            $this->registrarBitacora(
                'Gestion Academica',
                'Actualizó el grupo ' . $request->sigla_grupo . '.'
            );

            DB::commit();

            return redirect()->route('grupos.index')
                ->with('success', 'Grupo actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al actualizar grupo: ' . $e->getMessage()
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
            DB::table('grupo')
                ->where('Id_grupo', $id)
                ->delete();

            $this->registrarBitacora(
                'Gestion Academica',
                'Eliminó el grupo ID ' . $id . '.'
            );

            DB::commit();

            return redirect()->route('grupos.index')
                ->with('success', 'Grupo eliminado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al eliminar grupo: ' . $e->getMessage()
            ]);
        }
    }

    public function autogenerarView(Request $request)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $gestiones = DB::table('gestion')
            ->select(
                DB::raw('"Id_gestion" as id_gestion'),
                'anio',
                'periodo',
                'estado'
            )
            ->whereRaw("LOWER(TRIM(estado)) = 'activo'")
            ->orderBy('anio', 'desc')
            ->orderBy('periodo')
            ->get();

        $idGestion = $request->input('Id_gestion');
        if (!$idGestion) {
            $activeGestion = $gestiones->first();
            $idGestion = $activeGestion ? $activeGestion->id_gestion : null;
        }

        $this->actualizarEstadosPostulantes($idGestion);

        // 1. Total validated students for the selected gestion
        $totalValidados = DB::table('inscripcion as i')
            ->join('postulante as po', 'po.Id_postulante', '=', 'i.Id_postulante')
            ->where('i.estado', '=', 'Inscrito')
            ->where('po.estado_inscripcion', '=', 'Inscrito')
            ->where('i.Id_gestion', '=', $idGestion)
            ->count();

        // 2. Distributions
        $distribucion = DB::table('inscripcion as i')
            ->join('postulante as po', 'po.Id_postulante', '=', 'i.Id_postulante')
            ->join('preferencia_inscripcion as pi', 'pi.Codigo_inscripcion', '=', 'i.Codigo_inscripcion')
            ->join('modalidad as m', 'm.Id_modalidad', '=', 'pi.Id_modalidad')
            ->join('turno as t', 't.Id_turno', '=', 'pi.Id_turno')
            ->select(
                'm.nombre_modalidad',
                't.nombre as nombre_turno',
                DB::raw('count("i"."Codigo_inscripcion") as total')
            )
            ->where('i.estado', '=', 'Inscrito')
            ->where('po.estado_inscripcion', '=', 'Inscrito')
            ->where('i.Id_gestion', '=', $idGestion)
            ->groupBy('m.nombre_modalidad', 't.nombre')
            ->get();

        $distribucionSinGrupo = DB::table('inscripcion as i')
            ->join('postulante as po', 'po.Id_postulante', '=', 'i.Id_postulante')
            ->join('preferencia_inscripcion as pi', 'pi.Codigo_inscripcion', '=', 'i.Codigo_inscripcion')
            ->join('modalidad as m', 'm.Id_modalidad', '=', 'pi.Id_modalidad')
            ->join('turno as t', 't.Id_turno', '=', 'pi.Id_turno')
            ->select(
                'm.nombre_modalidad',
                't.nombre as nombre_turno',
                DB::raw('count("i"."Codigo_inscripcion") as total')
            )
            ->where('i.estado', '=', 'Inscrito')
            ->where('po.estado_inscripcion', '=', 'Inscrito')
            ->where('i.Id_gestion', '=', $idGestion)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('grupo_postulante as gp')
                    ->whereRaw('gp."Id_postulante" = po."Id_postulante"');
            })
            ->groupBy('m.nombre_modalidad', 't.nombre')
            ->get();

        $modalidadesValidas = DB::table('modalidad')->whereRaw("LOWER(TRIM(estado)) = 'activo'")->get();
        $turnosValidos = DB::table('turno')->whereRaw("LOWER(TRIM(estado)) = 'activo'")->get();

        $stats = [];
        foreach ($modalidadesValidas as $m) {
            foreach ($turnosValidos as $t) {
                $stats[$m->nombre_modalidad][$t->nombre] = [
                    'total' => 0,
                    'sin_grupo' => 0
                ];
            }
        }

        foreach ($distribucion as $row) {
            if (isset($stats[$row->nombre_modalidad][$row->nombre_turno])) {
                $stats[$row->nombre_modalidad][$row->nombre_turno]['total'] = $row->total;
            }
        }

        foreach ($distribucionSinGrupo as $row) {
            if (isset($stats[$row->nombre_modalidad][$row->nombre_turno])) {
                $stats[$row->nombre_modalidad][$row->nombre_turno]['sin_grupo'] = $row->total;
            }
        }

        return view('Gestion_Academica.autogenerarGrupos', compact(
            'gestiones',
            'idGestion',
            'totalValidados',
            'stats',
            'modalidadesValidas',
            'turnosValidos'
        ));
    }

    public function autogenerarStore(Request $request)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $request->validate([
            'Id_gestion' => 'required|exists:gestion,Id_gestion',
            'estudiantes_por_aula' => 'required|integer|min:1',
        ]);

        $idGestion = $request->Id_gestion;
        $C = $request->estudiantes_por_aula;

        $this->actualizarEstadosPostulantes($idGestion);

        // Fetch all validated students without group in this gestion
        $postulantes = DB::table('inscripcion as i')
            ->join('postulante as po', 'po.Id_postulante', '=', 'i.Id_postulante')
            ->join('persona as p', 'p.Id_persona', '=', 'po.Id_postulante')
            ->join('preferencia_inscripcion as pi', 'pi.Codigo_inscripcion', '=', 'i.Codigo_inscripcion')
            ->select(
                'po.Id_postulante',
                'pi.Id_modalidad',
                'pi.Id_turno'
            )
            ->where('i.estado', '=', 'Inscrito')
            ->where('po.estado_inscripcion', '=', 'Inscrito')
            ->where('i.Id_gestion', '=', $idGestion)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('grupo_postulante as gp')
                    ->whereRaw('gp."Id_postulante" = po."Id_postulante"');
            })
            ->get();

        if ($postulantes->isEmpty()) {
            return back()->withErrors([
                'error' => 'No hay postulantes validados pendientes de asignación en la gestión seleccionada.'
            ])->withInput();
        }

        // Fetch active classrooms
        $aulas = DB::table('aula')
            ->whereRaw("LOWER(TRIM(estado)) = 'activo'")
            ->orderBy('Id_aula')
            ->get();

        if ($aulas->isEmpty()) {
            return back()->withErrors([
                'error' => 'No hay aulas activas registradas en el sistema. Debe activar o registrar al menos una antes de continuar.'
            ])->withInput();
        }

        // Fetch turnos and map shift names to single-letter codes
        $turnos = DB::table('turno')->get();
        $turnoCodes = [];
        foreach ($turnos as $t) {
            $name = mb_strtolower(trim($t->nombre));
            if (strpos($name, 'mañana') !== false || strpos($name, 'manana') !== false) {
                $turnoCodes[$t->Id_turno] = 'M';
            } elseif (strpos($name, 'tarde') !== false) {
                $turnoCodes[$t->Id_turno] = 'T';
            } elseif (strpos($name, 'noche') !== false) {
                $turnoCodes[$t->Id_turno] = 'N';
            } else {
                $turnoCodes[$t->Id_turno] = strtoupper(substr(trim($t->nombre), 0, 1));
            }
        }

        DB::beginTransaction();

        try {
            // Group students by modality first
            $groupedByModalidad = $postulantes->groupBy('Id_modalidad');

            $aulaIndex = 0;
            $totalGruposCreados = 0;
            $totalAsignados = 0;

            foreach ($groupedByModalidad as $idModalidad => $studentsInModalidad) {
                $N_m = $studentsInModalidad->count();
                $G_m = (int) ceil($N_m / $C);

                // Determine the shift with most preferences for this modality
                $turnoCounts = $studentsInModalidad->groupBy('Id_turno')
                    ->map(function ($group) {
                        return $group->count();
                    })
                    ->toArray();

                // Sort descending to find the shift with maximum count
                arsort($turnoCounts);
                $idTurnoMasPreferido = key($turnoCounts);

                // If not found, fallback to first active shift
                if (!$idTurnoMasPreferido) {
                    $idTurnoMasPreferido = DB::table('turno')->whereRaw("LOWER(TRIM(estado)) = 'activo'")->value('Id_turno');
                }

                $turnoCode = $turnoCodes[$idTurnoMasPreferido] ?? 'G';
                $remainingStudents = $studentsInModalidad->pluck('Id_postulante')->toArray();

                for ($i = 0; $i < $G_m; $i++) {
                    if (empty($remainingStudents)) {
                        break;
                    }

                    // Extract up to C students for this group
                    $chunk = array_splice($remainingStudents, 0, $C);

                    // Assign classroom sequentially
                    $aula = $aulas->get($aulaIndex % $aulas->count());
                    $aulaIndex++;

                    // Generate unique sigla in format: ShiftPrefix + sequential number (e.g. T1, T2)
                    $sec = 1;
                    do {
                        $sigla = $turnoCode . $sec;
                        $existe = DB::table('grupo')
                            ->where('sigla_grupo', $sigla)
                            ->where('Id_gestion', $idGestion)
                            ->exists();
                        if ($existe) {
                            $sec++;
                        }
                    } while ($existe);

                    // Insert group with maximum capacity from the interface ($C)
                    $idGrupo = DB::table('grupo')->insertGetId([
                        'sigla_grupo' => $sigla,
                        'capacidad_max' => $C,
                        'estado' => 'activo',
                        'cant_estudiantes' => count($chunk),
                        'Id_aula' => $aula->Id_aula,
                        'Id_modalidad' => $idModalidad,
                        'Id_turno' => $idTurnoMasPreferido,
                        'Id_gestion' => $idGestion,
                    ], 'Id_grupo');

                    $totalGruposCreados++;

                    // Assign students to group
                    foreach ($chunk as $idPostulante) {
                        DB::table('grupo_postulante')->insert([
                            'Id_grupo' => $idGrupo,
                            'Id_postulante' => $idPostulante,
                            'estado' => 'activo',
                            'fecha_asignacion' => now()->toDateString(),
                        ]);
                        $totalAsignados++;
                    }
                }
            }

            $this->registrarBitacora(
                'Gestion Academica',
                "Autogeneró {$totalGruposCreados} grupos y asignó a {$totalAsignados} postulantes validados."
            );

            DB::commit();

            return redirect()->route('grupos.index')
                ->with('success', "Proceso completado. Se han autogenerado exitosamente {$totalGruposCreados} grupos y se asignaron {$totalAsignados} estudiantes.");

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al autogenerar grupos: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function horarioView($id)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $grupo = DB::table('grupo as g')
            ->join('aula as a', 'a.Id_aula', '=', 'g.Id_aula')
            ->join('modalidad as m', 'm.Id_modalidad', '=', 'g.Id_modalidad')
            ->join('turno as t', 't.Id_turno', '=', 'g.Id_turno')
            ->join('gestion as ge', 'ge.Id_gestion', '=', 'g.Id_gestion')
            ->select(
                'g.Id_grupo as id_grupo',
                'g.sigla_grupo',
                'g.Id_turno as id_turno',
                't.nombre as nombre_turno',
                'a.nro_aula',
                'a.ubicacion',
                'm.nombre_modalidad',
                'ge.anio',
                'ge.periodo'
            )
            ->where('g.Id_grupo', $id)
            ->first();

        if (!$grupo) {
            return redirect()->route('grupos.index')->withErrors(['error' => 'Grupo no encontrado.']);
        }

        // Obtener las materias activas
        $materias = DB::table('materia')
            ->select('Id_materia as id_materia', 'nombre')
            ->whereRaw("LOWER(TRIM(estado)) = 'activo'")
            ->orderBy('nombre')
            ->get();

        // Obtener horarios asociados a este turno y activos
        $horarios = DB::table('horario')
            ->select(
                'Id_horario as id_horario',
                'dia',
                'hora_inicio',
                'hora_fin',
                'estado',
                'Id_turno'
            )
            ->where('Id_turno', $grupo->id_turno)
            ->whereRaw("LOWER(TRIM(estado)) = 'activo'")
            ->orderBy('hora_inicio')
            ->orderBy('dia')
            ->get();

        // Obtener asignaciones actuales
        $asignaciones = DB::table('grupo_horario')
            ->where('Id_grupo', $id)
            ->get()
            ->mapWithKeys(function ($item) {
                $key = $item->Id_horario ?? $item->id_horario ?? null;
                return [$key => $item];
            });

        // Formatear los bloques de hora únicos
        $bloques = [];
        foreach ($horarios as $h) {
            $key = substr($h->hora_inicio, 0, 5) . ' - ' . substr($h->hora_fin, 0, 5);
            if (!isset($bloques[$key])) {
                $bloques[$key] = [
                    'hora_inicio' => $h->hora_inicio,
                    'hora_fin' => $h->hora_fin,
                    'dias' => []
                ];
            }
            // Guardamos el ID del horario asociado a este día y bloque
            $bloques[$key]['dias'][$h->dia] = $h->id_horario;
        }

        return view('Gestion_Academica.asignarHorariosGrupo', compact('grupo', 'materias', 'bloques', 'asignaciones'));
    }

    public function horarioStore(Request $request, $id)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $grupo = DB::table('grupo')->where('Id_grupo', $id)->first();
        if (!$grupo) {
            return back()->withErrors(['error' => 'Grupo no encontrado.']);
        }

        DB::beginTransaction();
        try {
            // Eliminar asignaciones anteriores
            DB::table('grupo_horario')->where('Id_grupo', $id)->delete();

            // Guardar las nuevas asignaciones
            if ($request->has('horario_materia')) {
                foreach ($request->horario_materia as $horarioId => $materiaId) {
                    if (!empty($materiaId)) {
                        DB::table('grupo_horario')->insert([
                            'Id_grupo' => $id,
                            'Id_horario' => $horarioId,
                            'Id_materia' => $materiaId
                        ]);
                    }
                }
            }

            $this->registrarBitacora(
                'Gestion Academica',
                'Configuró el horario para el grupo ' . $grupo->sigla_grupo . '.'
            );

            DB::commit();
            return redirect()->route('grupos.horario', $id)
                ->with('success', 'Horario del grupo guardado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al guardar el horario: ' . $e->getMessage()]);
        }
    }

    public function horarioImprimir($id)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $grupo = DB::table('grupo as g')
            ->join('aula as a', 'a.Id_aula', '=', 'g.Id_aula')
            ->join('modalidad as m', 'm.Id_modalidad', '=', 'g.Id_modalidad')
            ->join('turno as t', 't.Id_turno', '=', 'g.Id_turno')
            ->join('gestion as ge', 'ge.Id_gestion', '=', 'g.Id_gestion')
            ->select(
                'g.Id_grupo as id_grupo',
                'g.sigla_grupo',
                'g.Id_turno as id_turno',
                't.nombre as nombre_turno',
                'a.nro_aula',
                'a.capacidad',
                'a.ubicacion',
                'm.nombre_modalidad',
                'ge.anio',
                'ge.periodo'
            )
            ->where('g.Id_grupo', $id)
            ->first();

        if (!$grupo) {
            return redirect()->route('grupos.index')->withErrors(['error' => 'Grupo no encontrado.']);
        }

        // Obtener asignaciones de materias y horarios para este grupo
        $horarios = DB::table('grupo_horario as gh')
            ->join('horario as h', 'h.Id_horario', '=', 'gh.Id_horario')
            ->join('materia as m', 'm.Id_materia', '=', 'gh.Id_materia')
            ->select(
                'h.dia',
                'h.hora_inicio',
                'h.hora_fin',
                'm.nombre as nombre_materia',
                'm.descripcion as desc_materia'
            )
            ->where('gh.Id_grupo', $id)
            ->whereRaw("LOWER(TRIM(h.estado)) = 'activo'")
            ->orderBy('h.hora_inicio')
            ->get();

        // Agrupar por día para que el ticket sea idéntico al del usuario
        $diasList = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $scheduleByDay = [];
        foreach ($diasList as $dia) {
            $scheduleByDay[$dia] = [];
        }

        foreach ($horarios as $h) {
            $diaNorm = trim($h->dia);
            if (isset($scheduleByDay[$diaNorm])) {
                $scheduleByDay[$diaNorm][] = [
                    'materia' => $h->nombre_materia,
                    'rango' => substr($h->hora_inicio, 0, 5) . '-' . substr($h->hora_fin, 0, 5),
                    'aula' => $grupo->nro_aula
                ];
            }
        }

        return view('Gestion_Academica.imprimirHorarioGrupo', compact('grupo', 'scheduleByDay'));
    }

    private function registrarBitacora(string $tipo, string $descripcion): void
    {
        if (!Auth::check()) {
            return;
        }

        DB::table('bitacora')->insert([
            'tipo' => $tipo,
            'descripcion' => $descripcion,
            'fecha' => now()->toDateString(),
            'hora' => now()->format('H:i:s'),
            'estado' => 'activo',
            'Id_usuario' => Auth::id(),
        ]);
    }

    private function validarPrerrequisitos()
    {
        if (DB::table('carrera')->count() === 0 || 
            DB::table('gestion')->count() === 0 || 
            DB::table('turno')->count() === 0 || 
            DB::table('materia')->count() === 0 || 
            DB::table('horario')->count() === 0) {
            return redirect()->route('menu')->withErrors([
                'error' => 'Debe registrar al menos: Carreras y Cupos, Gestiones, Turnos, Materias y Horarios antes de acceder a la gestión de grupos.'
            ]);
        }
        return null;
    }

    private function actualizarEstadosPostulantes($idGestion)
    {
        if (!$idGestion) {
            return;
        }

        // Obtener inscripciones de la gestión
        $inscripciones = DB::table('inscripcion')
            ->where('Id_gestion', $idGestion)
            ->get();

        // Contar documentos destinados a Postulantes
        $totalDocumentos = DB::table('documento')
            ->whereRaw("LOWER(TRIM(destinado_a)) = 'postulantes'")
            ->count();

        // Contar conceptos de pago activos
        $totalPagosActivos = DB::table('pago')
            ->whereRaw("LOWER(TRIM(estado_pago)) = 'activo'")
            ->count();

        foreach ($inscripciones as $ins) {
            $idPostulante = $ins->Id_postulante;
            $codigoInscripcion = $ins->Codigo_inscripcion;

            // Contar documentos aprobados del postulante
            $documentosAprobados = DB::table('persona_documento as pd')
                ->join('documento as doc', DB::raw('"doc"."Id_documento"'), '=', DB::raw('"pd"."Id_documento"'))
                ->where('pd.Id_persona', $idPostulante)
                ->whereRaw("LOWER(TRIM(doc.destinado_a)) = 'postulantes'")
                ->where('pd.estado', 'Aprobado')
                ->count();

            // Contar pagos liquidados de esa inscripción
            $pagosLiquidados = DB::table('pago_inscripcion as pi')
                ->join('pago as pg', DB::raw('"pg"."Id_pago"'), '=', DB::raw('"pi"."Id_pago"'))
                ->where('pi.Codigo_inscripcion', $codigoInscripcion)
                ->whereRaw("LOWER(TRIM(pg.estado_pago)) = 'activo'")
                ->where('pi.estado_pago_inscripcion', 'Liquidado')
                ->count();

            $esValido = (
                $totalDocumentos > 0 &&
                $documentosAprobados == $totalDocumentos &&
                $totalPagosActivos > 0 &&
                $pagosLiquidados == $totalPagosActivos
            );

            if ($esValido) {
                DB::table('inscripcion')
                    ->where('Codigo_inscripcion', $codigoInscripcion)
                    ->update(['estado' => 'Inscrito']);

                DB::table('postulante')
                    ->where('Id_postulante', $idPostulante)
                    ->update(['estado_inscripcion' => 'Inscrito']);
            } else {
                if ($ins->estado === 'Inscrito') {
                    DB::table('inscripcion')
                        ->where('Codigo_inscripcion', $codigoInscripcion)
                        ->update(['estado' => 'En_Revision']);

                    DB::table('postulante')
                        ->where('Id_postulante', $idPostulante)
                        ->update(['estado_inscripcion' => 'En_Revision']);
                }
            }
        }
    }
}