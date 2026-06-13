<?php

namespace App\Http\Controllers\Gestion_Academica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class gestionarPostulantesAGruposController extends Controller
{
    public function index()
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        // 1. Obtener la lista de postulantes inscritos y validados (estado = 'Inscrito') que no estén en ningún grupo
        $postulantesSinGrupo = DB::table('postulante as po')
            ->join('persona as p', DB::raw('"p"."Id_persona"'), '=', DB::raw('"po"."Id_postulante"'))
            ->join('inscripcion as i', DB::raw('"i"."Id_postulante"'), '=', DB::raw('"po"."Id_postulante"'))
            ->select(
                DB::raw('"po"."Id_postulante" as id_postulante'),
                'p.ci',
                'p.nombre',
                'p.apellido'
            )
            ->where('i.estado', '=', 'Inscrito')
            ->where('po.estado_inscripcion', '=', 'Inscrito')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('grupo_postulante as gp')
                    ->whereRaw('gp."Id_postulante" = po."Id_postulante"');
            })
            ->orderBy('p.nombre')
            ->orderBy('p.apellido')
            ->get();

        // 2. Obtener los grupos activos disponibles con sus campos de modalidad y turno descritos
        $grupos = DB::table('grupo as g')
            ->join('modalidad as m', DB::raw('"m"."Id_modalidad"'), '=', DB::raw('"g"."Id_modalidad"'))
            ->join('turno as t', DB::raw('"t"."Id_turno"'), '=', DB::raw('"g"."Id_turno"'))
            ->select(
                DB::raw('"g"."Id_grupo" as id_grupo'),
                'g.sigla_grupo',
                'g.capacidad_max',
                'g.cant_estudiantes',
                'g.estado',
                DB::raw('"g"."Id_modalidad" as id_modalidad'),
                DB::raw('"g"."Id_turno" as id_turno'),
                'm.nombre_modalidad as modalidad',
                't.nombre as turno'
            )
            ->whereRaw("LOWER(TRIM(g.estado)) = 'activo'")
            ->orderBy('g.sigla_grupo')
            ->get();

        // 3. Obtener postulantes ya asignados
        $asignaciones = DB::table('grupo_postulante as gp')
            ->join('grupo as g', DB::raw('"g"."Id_grupo"'), '=', DB::raw('"gp"."Id_grupo"'))
            ->join('postulante as po', DB::raw('"po"."Id_postulante"'), '=', DB::raw('"gp"."Id_postulante"'))
            ->join('persona as p', DB::raw('"p"."Id_persona"'), '=', DB::raw('"po"."Id_postulante"'))
            ->select(
                DB::raw('"gp"."Id_grupo" as id_grupo'),
                DB::raw('"gp"."Id_postulante" as id_postulante'),
                'g.sigla_grupo',
                'p.ci',
                'p.nombre',
                'p.apellido',
                'gp.fecha_asignacion',
                'gp.estado'
            )
            ->orderBy('g.sigla_grupo')
            ->orderBy('p.nombre')
            ->get();

        // 4. Obtener las modalidades activas para los selectores de filtro
        $modalidades = DB::table('modalidad')
            ->select(DB::raw('"Id_modalidad" as id_modalidad'), 'nombre_modalidad')
            ->whereRaw("LOWER(TRIM(estado)) = 'activo'")
            ->orderBy('nombre_modalidad')
            ->get();

        // 5. Obtener los turnos activos para los selectores de filtro
        $turnos = DB::table('turno')
            ->select(DB::raw('"Id_turno" as id_turno'), 'nombre')
            ->whereRaw("LOWER(TRIM(estado)) = 'activo'")
            ->orderBy('nombre')
            ->get();

        return view('Gestion_Academica.gestionarPostulantesAGrupos', compact(
            'postulantesSinGrupo',
            'grupos',
            'asignaciones',
            'modalidades',
            'turnos'
        ));
    }

    public function store(Request $request)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $request->validate([
            'Id_grupo' => 'required|integer|exists:grupo,Id_grupo',
            'Id_postulante' => 'required|integer|exists:postulante,Id_postulante',
            'Id_modalidad' => 'required|integer',
            'Id_turno' => 'required|integer',
        ]);

        $idGrupo = $request->Id_grupo;
        $idPostulante = $request->Id_postulante;

        // 1. Verificar que el grupo esté activo y tenga capacidad disponible
        $grupo = DB::table('grupo')
            ->where('Id_grupo', $idGrupo)
            ->first();

        if (!$grupo) {
            return back()->withErrors(['error' => 'El grupo seleccionado no existe.']);
        }

        if (strtolower(trim($grupo->estado)) !== 'activo') {
            return back()->withErrors(['error' => 'El grupo seleccionado no está activo.']);
        }

        if ($grupo->cant_estudiantes >= $grupo->capacidad_max) {
            return back()->withErrors(['error' => 'El grupo seleccionado ya alcanzó su capacidad máxima.']);
        }

        // 2. Verificar que el postulante no esté asignado a ningún grupo
        $yaAsignado = DB::table('grupo_postulante')
            ->where('Id_postulante', $idPostulante)
            ->exists();

        if ($yaAsignado) {
            return back()->withErrors(['error' => 'El postulante ya está asignado a otro grupo.']);
        }

        // 3. Verificar que el grupo coincida con la modalidad y turno seleccionados (preferencia del filtro)
        if ($grupo->Id_modalidad != $request->Id_modalidad || $grupo->Id_turno != $request->Id_turno) {
            return back()->withErrors(['error' => 'El grupo de destino no coincide con la modalidad y turno seleccionados.']);
        }

        DB::beginTransaction();

        try {
            // 4. Guardar asignación en la tabla grupo_postulante
            DB::table('grupo_postulante')->insert([
                'Id_grupo' => $idGrupo,
                'Id_postulante' => $idPostulante,
                'fecha_asignacion' => now()->toDateString(),
                'estado' => 'activo',
            ]);

            // 5. Actualizar la cantidad de estudiantes en el grupo
            DB::table('grupo')
                ->where('Id_grupo', $idGrupo)
                ->increment('cant_estudiantes');

            // Registrar acción en bitácora
            $postulanteInfo = DB::table('persona')->where('Id_persona', $idPostulante)->first();
            $nombreCompleto = $postulanteInfo ? $postulanteInfo->nombre . ' ' . $postulanteInfo->apellido : 'ID ' . $idPostulante;
            $this->registrarBitacora("Asignó al postulante {$nombreCompleto} al grupo {$grupo->sigla_grupo}.");

            DB::commit();

            return redirect()->route('postulantes-grupos.index')
                ->with('success', 'Postulante asignado al grupo correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al asignar postulante al grupo: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy($idGrupo, $idPostulante)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $asignacion = DB::table('grupo_postulante')
            ->where('Id_grupo', $idGrupo)
            ->where('Id_postulante', $idPostulante)
            ->first();

        if (!$asignacion) {
            return redirect()->route('postulantes-grupos.index')
                ->withErrors(['error' => 'No se encontró la asignación especificada.']);
        }

        DB::beginTransaction();

        try {
            // 1. Eliminar asignación de la tabla grupo_postulante
            DB::table('grupo_postulante')
                ->where('Id_grupo', $idGrupo)
                ->where('Id_postulante', $idPostulante)
                ->delete();

            // 2. Decrementar la cantidad de estudiantes en el grupo
            DB::table('grupo')
                ->where('Id_grupo', $idGrupo)
                ->decrement('cant_estudiantes');

            // Registrar acción en bitácora
            $grupoInfo = DB::table('grupo')->where('Id_grupo', $idGrupo)->first();
            $postulanteInfo = DB::table('persona')->where('Id_persona', $idPostulante)->first();
            
            $siglaGrupo = $grupoInfo ? $grupoInfo->sigla_grupo : 'ID ' . $idGrupo;
            $nombreCompleto = $postulanteInfo ? $postulanteInfo->nombre . ' ' . $postulanteInfo->apellido : 'ID ' . $idPostulante;
            
            $this->registrarBitacora("Eliminó la asignación del postulante {$nombreCompleto} del grupo {$siglaGrupo}.");

            DB::commit();

            return redirect()->route('postulantes-grupos.index')
                ->with('success', 'Asignación eliminada correctamente y cupo del grupo liberado.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al eliminar la asignación: ' . $e->getMessage()
            ]);
        }
    }

    public function asignacionGeneral()
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        // 1. Obtener todos los postulantes inscritos/validados que no tengan grupo
        $postulantes = DB::table('postulante as po')
            ->join('inscripcion as i', DB::raw('"i"."Id_postulante"'), '=', DB::raw('"po"."Id_postulante"'))
            ->select(DB::raw('"po"."Id_postulante" as id_postulante'))
            ->where('i.estado', '=', 'Inscrito')
            ->where('po.estado_inscripcion', '=', 'Inscrito')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('grupo_postulante as gp')
                    ->whereRaw('gp."Id_postulante" = po."Id_postulante"');
            })
            ->get();

        if ($postulantes->isEmpty()) {
            return back()->with('success', 'No hay postulantes validados pendientes de asignar.');
        }

        // 2. Obtener todos los grupos activos con capacidad disponible
        $grupos = DB::table('grupo')
            ->select(
                DB::raw('"Id_grupo" as id_grupo'),
                'sigla_grupo',
                'capacidad_max',
                'cant_estudiantes'
            )
            ->whereRaw("LOWER(TRIM(estado)) = 'activo'")
            ->whereRaw("cant_estudiantes < capacidad_max")
            ->orderBy('sigla_grupo')
            ->get()
            ->toArray();

        if (empty($grupos)) {
            return back()->withErrors(['error' => 'No hay grupos activos con cupo disponible para realizar la asignación general.']);
        }

        DB::beginTransaction();
        try {
            $asignadosCount = 0;
            $grupoIndex = 0;
            $totalGrupos = count($grupos);

            foreach ($postulantes as $po) {
                // Encontrar un grupo con capacidad
                while ($grupoIndex < $totalGrupos && $grupos[$grupoIndex]->cant_estudiantes >= $grupos[$grupoIndex]->capacidad_max) {
                    $grupoIndex++;
                }

                // Si no quedan grupos con espacio, paramos
                if ($grupoIndex >= $totalGrupos) {
                    break;
                }

                $grupo = $grupos[$grupoIndex];

                // Realizar la asignación
                DB::table('grupo_postulante')->insert([
                    'Id_grupo' => $grupo->id_grupo,
                    'Id_postulante' => $po->id_postulante,
                    'fecha_asignacion' => now()->toDateString(),
                    'estado' => 'activo',
                ]);

                // Incrementar estudiantes en el grupo
                DB::table('grupo')
                    ->where('Id_grupo', $grupo->id_grupo)
                    ->increment('cant_estudiantes');

                // Actualizar nuestra variable local para el bucle
                $grupo->cant_estudiantes++;
                $asignadosCount++;
            }

            if ($asignadosCount > 0) {
                $this->registrarBitacora("Realizó asignación general automática de {$asignadosCount} postulantes a grupos.");
                DB::commit();
                return redirect()->route('postulantes-grupos.index')
                    ->with('success', "Asignación general completada con éxito. Se asignaron {$asignadosCount} postulantes a los grupos.");
            } else {
                DB::rollBack();
                return back()->withErrors(['error' => 'No se pudo realizar la asignación general (sin cupo disponible).']);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al realizar la asignación general: ' . $e->getMessage()]);
        }
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
                'error' => 'Debe registrar al menos: Carreras y Cupos, Gestiones, Turnos, Materias y Horarios antes de realizar asignaciones.'
            ]);
        }
        return null;
    }
}
