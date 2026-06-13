<?php

namespace App\Http\Controllers\Gestion_Academica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class asignarDocentesAGruposYMateriasController extends Controller
{
    public function index()
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $asignaciones = DB::table('grupo_materia as gm')
            ->join('grupo as g', DB::raw('"g"."Id_grupo"'), '=', DB::raw('"gm"."Id_grupo"'))
            ->join('materia as m', DB::raw('"m"."Id_materia"'), '=', DB::raw('"gm"."Id_materia"'))
            ->join('docente as d', DB::raw('"d"."Id_docente"'), '=', DB::raw('"gm"."Id_docente"'))
            ->join('persona as p', DB::raw('"p"."Id_persona"'), '=', DB::raw('"d"."Id_docente"'))
            ->select(
                DB::raw('"gm"."Id_grupo" as id_grupo'),
                DB::raw('"gm"."Id_materia" as id_materia'),
                DB::raw('"gm"."Id_docente" as id_docente'),

                'g.sigla_grupo',
                'm.nombre as nombre_materia',

                'p.ci',
                'p.nombre',
                'p.apellido'
            )
            ->orderBy('g.sigla_grupo')
            ->orderBy('m.nombre')
            ->get();

        $docentes = DB::table('docente as d')
            ->join('persona as p', DB::raw('"p"."Id_persona"'), '=', DB::raw('"d"."Id_docente"'))
            ->select(
                DB::raw('"d"."Id_docente" as id_docente'),
                'p.ci',
                'p.nombre',
                'p.apellido'
            )
            ->whereRaw("LOWER(TRIM(d.estado)) = 'activo'")
            ->orderBy('p.nombre')
            ->get();

        $grupos = DB::table('grupo')
            ->select(
                DB::raw('"Id_grupo" as id_grupo'),
                'sigla_grupo',
                'estado'
            )
            ->whereRaw("LOWER(TRIM(estado)) = 'activo'")
            ->orderBy('sigla_grupo')
            ->get();

        $materias = DB::table('materia')
            ->select(
                DB::raw('"Id_materia" as id_materia'),
                'nombre',
                'estado'
            )
            ->whereRaw("LOWER(TRIM(estado)) = 'activo'")
            ->orderBy('nombre')
            ->get();

        $docenteMaterias = DB::table('docente_especialidad as de')
            ->join('especialidad as e', 'e.Id_especialidad', '=', 'de.Id_especialidad')
            ->select('de.Id_docente as id_docente', 'e.id_materia')
            ->get();

        $existingAssignments = DB::table('grupo_materia')
            ->select('Id_grupo as id_grupo', 'Id_materia as id_materia')
            ->get();

        return view('Gestion_Academica.asignarDocentesAGruposYMaterias', compact(
            'asignaciones',
            'docentes',
            'grupos',
            'materias',
            'docenteMaterias',
            'existingAssignments'
        ));
    }

    public function store(Request $request)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $request->validate([
            'Id_grupo' => 'required|exists:grupo,Id_grupo',
            'Id_materia' => 'required|exists:materia,Id_materia',
            'Id_docente' => 'required|exists:docente,Id_docente',
        ]);

        $existe = DB::table('grupo_materia')
            ->where('Id_grupo', $request->Id_grupo)
            ->where('Id_materia', $request->Id_materia)
            ->exists();

        if ($existe) {
            return back()->withErrors([
                'error' => 'Ese grupo ya tiene una asignación para esa materia.'
            ])->withInput();
        }

        DB::table('grupo_materia')->insert([
            'Id_grupo' => $request->Id_grupo,
            'Id_materia' => $request->Id_materia,
            'Id_docente' => $request->Id_docente,
        ]);

        $this->crearEvaluacionesAutomaticas($request->Id_grupo, $request->Id_materia);

        $this->registrarBitacora('Asignó docente a grupo y materia.');

        return redirect()->route('asignaciones-docentes.index')
            ->with('success', 'Asignación registrada correctamente.');
    }

    public function update(Request $request, $idGrupo, $idMateria)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $request->validate([
            'Id_grupo' => 'required|exists:grupo,Id_grupo',
            'Id_materia' => 'required|exists:materia,Id_materia',
            'Id_docente' => 'required|exists:docente,Id_docente',
        ]);

        $cambioClave = ((int)$request->Id_grupo !== (int)$idGrupo)
            || ((int)$request->Id_materia !== (int)$idMateria);

        if ($cambioClave) {
            $existe = DB::table('grupo_materia')
                ->where('Id_grupo', $request->Id_grupo)
                ->where('Id_materia', $request->Id_materia)
                ->exists();

            if ($existe) {
                return back()->withErrors([
                    'error' => 'Ese grupo ya tiene una asignación para esa materia.'
                ])->withInput();
            }
        }

        DB::table('grupo_materia')
            ->where('Id_grupo', $idGrupo)
            ->where('Id_materia', $idMateria)
            ->update([
                'Id_grupo' => $request->Id_grupo,
                'Id_materia' => $request->Id_materia,
                'Id_docente' => $request->Id_docente,
            ]);

        $this->crearEvaluacionesAutomaticas($request->Id_grupo, $request->Id_materia);

        $this->registrarBitacora('Actualizó asignación de docente a grupo y materia.');

        return redirect()->route('asignaciones-docentes.index')
            ->with('success', 'Asignación actualizada correctamente.');
    }

    public function destroy($idGrupo, $idMateria)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        DB::table('grupo_materia')
            ->where('Id_grupo', $idGrupo)
            ->where('Id_materia', $idMateria)
            ->delete();

        $this->registrarBitacora('Eliminó asignación de docente a grupo y materia.');

        return redirect()->route('asignaciones-docentes.index')
            ->with('success', 'Asignación eliminada correctamente.');
    }

    public function autogenerarView(Request $request)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $gestiones = DB::table('gestion')
            ->select(
                'Id_gestion as id_gestion',
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

        // Stats
        $totalGrupos = DB::table('grupo')
            ->where('Id_gestion', $idGestion)
            ->count();

        // Required assignments from grupo_horario
        $requiredAssignments = DB::table('grupo_horario as gh')
            ->join('grupo as g', 'g.Id_grupo', '=', 'gh.Id_grupo')
            ->where('g.Id_gestion', $idGestion)
            ->select('gh.Id_grupo', 'gh.Id_materia')
            ->groupBy('gh.Id_grupo', 'gh.Id_materia')
            ->get();

        $totalRequeridos = $requiredAssignments->count();

        $totalAsignados = DB::table('grupo_materia as gm')
            ->join('grupo as g', 'g.Id_grupo', '=', 'gm.Id_grupo')
            ->where('g.Id_gestion', $idGestion)
            ->count();

        $totalPendientes = max(0, $totalRequeridos - $totalAsignados);

        $totalDocentesActivos = DB::table('docente as d')
            ->whereRaw("LOWER(TRIM(d.estado)) = 'activo'")
            ->count();

        return view('Gestion_Academica.asignarDocentesAutomatico', compact(
            'gestiones',
            'idGestion',
            'totalGrupos',
            'totalRequeridos',
            'totalAsignados',
            'totalPendientes',
            'totalDocentesActivos'
        ));
    }

    public function autogenerarStore(Request $request)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $request->validate([
            'Id_gestion' => 'required|exists:gestion,Id_gestion',
        ]);

        $idGestion = $request->Id_gestion;

        // 1. Fetch all active teachers
        $docentesActivos = DB::table('docente as d')
            ->join('persona as p', 'p.Id_persona', '=', 'd.Id_docente')
            ->select('d.Id_docente', 'p.nombre', 'p.apellido', 'p.ci')
            ->whereRaw("LOWER(TRIM(d.estado)) = 'activo'")
            ->get();

        if ($docentesActivos->isEmpty()) {
            return back()->withErrors([
                'error' => 'No hay docentes activos registrados en el sistema para realizar asignaciones.'
            ])->withInput();
        }

        // 2. Fetch specialties of all teachers and map them to subject IDs.
        $docentesEspecialidades = DB::table('docente_especialidad as de')
            ->join('especialidad as e', 'e.Id_especialidad', '=', 'de.Id_especialidad')
            ->select('de.Id_docente', 'e.id_materia')
            ->get();

        $docenteMaterias = [];
        foreach ($docentesEspecialidades as $de) {
            if ($de->id_materia) {
                $docenteMaterias[$de->Id_docente][] = (int)$de->id_materia;
            }
        }

        // 3. Fetch all required assignments in the selected gestion
        $requiredAssignments = DB::table('grupo_horario as gh')
            ->join('grupo as g', 'g.Id_grupo', '=', 'gh.Id_grupo')
            ->join('materia as m', 'm.Id_materia', '=', 'gh.Id_materia')
            ->select('gh.Id_grupo', 'gh.Id_materia', 'g.sigla_grupo', 'm.nombre as nombre_materia')
            ->where('g.Id_gestion', $idGestion)
            ->groupBy('gh.Id_grupo', 'gh.Id_materia', 'g.sigla_grupo', 'm.nombre')
            ->get();

        if ($requiredAssignments->isEmpty()) {
            return back()->withErrors([
                'error' => 'No hay materias u horarios registrados en los grupos de la gestión seleccionada.'
            ])->withInput();
        }

        // 4. Fetch all schedule slots for groups in the selected gestion
        $schedules = DB::table('grupo_horario as gh')
            ->join('horario as h', 'h.Id_horario', '=', 'gh.Id_horario')
            ->select('gh.Id_grupo', 'gh.Id_materia', 'h.dia', 'h.hora_inicio', 'h.hora_fin')
            ->whereIn('gh.Id_grupo', function($query) use ($idGestion) {
                $query->select('Id_grupo')->from('grupo')->where('Id_gestion', $idGestion);
            })
            ->get();

        $taskSchedules = [];
        foreach ($schedules as $s) {
            $key = $s->Id_grupo . '_' . $s->Id_materia;
            $taskSchedules[$key][] = [
                'dia' => trim($s->dia),
                'start' => $this->timeToMinutes($s->hora_inicio),
                'end' => $this->timeToMinutes($s->hora_fin)
            ];
        }

        DB::beginTransaction();

        try {
            // Clear all existing assignments for this gestion in grupo_materia
            DB::table('grupo_materia')
                ->whereIn('Id_grupo', function($query) use ($idGestion) {
                    $query->select('Id_grupo')->from('grupo')->where('Id_gestion', $idGestion);
                })
                ->delete();

            // Track assignments and slots during execution
            $assignedGroups = [];
            $teacherSlots = [];
            $assignedDocente = [];

            foreach ($docentesActivos as $docente) {
                $assignedGroups[$docente->Id_docente] = [];
                $teacherSlots[$docente->Id_docente] = [];
            }

            // Define checking logic
            $canAssign = function($docenteId, $groupId, $materiaId) use (&$assignedGroups, &$teacherSlots, $taskSchedules, $docenteMaterias) {
                // Check specialty
                $allowed = $docenteMaterias[$docenteId] ?? [];
                if (!in_array((int)$materiaId, $allowed)) {
                    return false;
                }

                // Check group limit
                $currentGroups = $assignedGroups[$docenteId];
                if (count($currentGroups) >= 4 && !in_array((int)$groupId, $currentGroups)) {
                    return false;
                }

                $key = $groupId . '_' . $materiaId;
                $newSlots = $taskSchedules[$key] ?? [];
                if (empty($newSlots)) {
                    return true;
                }

                // Check conflicts and rest period constraint
                $tempSlots = $teacherSlots[$docenteId];
                foreach ($newSlots as $slot) {
                    $dia = $slot['dia'];
                    $tempSlots[$dia][] = $slot;
                }

                foreach ($tempSlots as $dia => $daySlots) {
                    if (count($daySlots) <= 1) {
                        continue;
                    }

                    // Sort slots by start time
                    usort($daySlots, function($a, $b) {
                        return $a['start'] <=> $b['start'];
                    });

                    // Check overlap
                    for ($i = 0; $i < count($daySlots) - 1; $i++) {
                        $curr = $daySlots[$i];
                        $next = $daySlots[$i + 1];
                        if ($curr['end'] > $next['start']) {
                            return false; // overlap conflict
                        }
                    }

                    // Check 3 consecutive slots on the same day (consecutive gap <= 15 min)
                    if (count($daySlots) >= 3) {
                        for ($i = 0; $i < count($daySlots) - 2; $i++) {
                            $a = $daySlots[$i];
                            $b = $daySlots[$i + 1];
                            $c = $daySlots[$i + 2];

                            $consecAB = ($b['start'] - $a['end']) <= 15;
                            $consecBC = ($c['start'] - $b['end']) <= 15;

                            if ($consecAB && $consecBC) {
                                return false; // violation of break period
                            }
                        }
                    }
                }

                return true;
            };

            $assignTeacher = function($docenteId, $groupId, $materiaId) use (&$assignedGroups, &$teacherSlots, $taskSchedules) {
                if (!in_array((int)$groupId, $assignedGroups[$docenteId])) {
                    $assignedGroups[$docenteId][] = (int)$groupId;
                }

                $key = $groupId . '_' . $materiaId;
                $newSlots = $taskSchedules[$key] ?? [];
                foreach ($newSlots as $slot) {
                    $dia = $slot['dia'];
                    $teacherSlots[$docenteId][$dia][] = $slot;
                }
            };

            // Order tasks by number of qualified teachers (MRV Heuristic)
            $tasks = [];
            foreach ($requiredAssignments as $item) {
                $groupId = (int)$item->Id_grupo;
                $materiaId = (int)$item->Id_materia;

                $qualifiedCount = 0;
                foreach ($docentesActivos as $docente) {
                    $allowed = $docenteMaterias[$docente->Id_docente] ?? [];
                    if (in_array($materiaId, $allowed)) {
                        $qualifiedCount++;
                    }
                }

                $tasks[] = [
                    'groupId' => $groupId,
                    'materiaId' => $materiaId,
                    'sigla_grupo' => $item->sigla_grupo,
                    'nombre_materia' => $item->nombre_materia,
                    'qualifiedCount' => $qualifiedCount
                ];
            }

            usort($tasks, function($a, $b) {
                return $a['qualifiedCount'] <=> $b['qualifiedCount'];
            });

            $successCount = 0;
            $failedTasks = [];

            foreach ($tasks as $task) {
                $groupId = $task['groupId'];
                $materiaId = $task['materiaId'];

                $candidates = [];
                foreach ($docentesActivos as $docente) {
                    if ($canAssign($docente->Id_docente, $groupId, $materiaId)) {
                        $groupCount = count($assignedGroups[$docente->Id_docente]);
                        $taskCount = 0;
                        foreach ($teacherSlots[$docente->Id_docente] as $dia => $slots) {
                            $taskCount += count($slots);
                        }
                        $candidates[] = [
                            'docente' => $docente,
                            'groupCount' => $groupCount,
                            'taskCount' => $taskCount
                        ];
                    }
                }

                if (!empty($candidates)) {
                    // Sort to load balance: choose the one with fewest groups first, then fewest tasks
                    usort($candidates, function($a, $b) {
                        if ($a['groupCount'] === $b['groupCount']) {
                            return $a['taskCount'] <=> $b['taskCount'];
                        }
                        return $a['groupCount'] <=> $b['groupCount'];
                    });

                    $chosen = $candidates[0]['docente'];
                    $assignTeacher($chosen->Id_docente, $groupId, $materiaId);

                    $assignedDocente[] = [
                        'Id_grupo' => $groupId,
                        'Id_materia' => $materiaId,
                        'Id_docente' => $chosen->Id_docente
                    ];
                    $successCount++;
                } else {
                    $failedTasks[] = $task['sigla_grupo'] . ' - ' . $task['nombre_materia'];
                }
            }

            if (!empty($assignedDocente)) {
                DB::table('grupo_materia')->insert($assignedDocente);
                foreach ($assignedDocente as $ad) {
                    $this->crearEvaluacionesAutomaticas($ad['Id_grupo'], $ad['Id_materia']);
                }
            }

            $msg = "Asignación automática completada con éxito. Se asignaron {$successCount} materias/grupos.";
            if (!empty($failedTasks)) {
                $msg .= " Quedaron sin asignar las siguientes materias por incompatibilidad horaria o falta de especialidad: " . implode(', ', $failedTasks);
            }

            $this->registrarBitacora("Realizó asignación automática de docentes para la gestión ID: {$idGestion}.");

            DB::commit();

            return redirect()->route('asignaciones-docentes.index')
                ->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al asignar docentes automáticamente: ' . $e->getMessage()
            ])->withInput();
        }
    }

    private function timeToMinutes($timeStr)
    {
        if (!$timeStr) {
            return 0;
        }
        $parts = explode(':', $timeStr);
        $hours = isset($parts[0]) ? intval($parts[0]) : 0;
        $minutes = isset($parts[1]) ? intval($parts[1]) : 0;
        return $hours * 60 + $minutes;
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
        if (DB::table('carrera')->count() === 0 || 
            DB::table('gestion')->count() === 0 || 
            DB::table('turno')->count() === 0 || 
            DB::table('materia')->count() === 0 || 
            DB::table('horario')->count() === 0) {
            return redirect()->route('menu')->withErrors([
                'error' => 'Debe registrar al menos: Carreras y Cupos, Gestiones, Turnos, Materias y Horarios antes de gestionar asignaciones de docentes.'
            ]);
        }
        return null;
    }

    private function crearEvaluacionesAutomaticas($idGrupo, $idMateria)
    {
        $evaluaciones = [
            ['numero_evaluacion' => 1, 'porcentaje' => 30.00, 'fecha' => now()->toDateString(), 'estado' => 'activo', 'Id_grupo' => $idGrupo, 'Id_materia' => $idMateria],
            ['numero_evaluacion' => 2, 'porcentaje' => 30.00, 'fecha' => now()->toDateString(), 'estado' => 'activo', 'Id_grupo' => $idGrupo, 'Id_materia' => $idMateria],
            ['numero_evaluacion' => 3, 'porcentaje' => 40.00, 'fecha' => now()->toDateString(), 'estado' => 'activo', 'Id_grupo' => $idGrupo, 'Id_materia' => $idMateria],
        ];

        foreach ($evaluaciones as $eval) {
            $existe = DB::table('evaluacion')
                ->where('Id_grupo', $eval['Id_grupo'])
                ->where('Id_materia', $eval['Id_materia'])
                ->where('numero_evaluacion', $eval['numero_evaluacion'])
                ->exists();

            if (!$existe) {
                DB::table('evaluacion')->insert($eval);
            }
        }
    }
}