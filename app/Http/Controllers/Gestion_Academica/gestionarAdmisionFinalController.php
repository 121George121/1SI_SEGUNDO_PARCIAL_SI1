<?php

namespace App\Http\Controllers\Gestion_Academica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Gestion_Academica\enviarNotificacionesController;

class gestionarAdmisionFinalController extends Controller
{
    /**
     * Muestra la interfaz principal de Gestión de Admisión Final.
     */
    public function index(Request $request)
    {
        $gestiones = DB::table('gestion')
            ->select('Id_gestion', 'anio', 'periodo', 'estado')
            ->orderBy('anio', 'desc')
            ->orderBy('periodo', 'desc')
            ->get();

        $idGestion = $request->input('Id_gestion');

        $carrerasCupos = [];
        $admitidos = [];
        $rechazados = [];
        $totalPostulantes = 0;
        $totalPostulantesConNotas = 0;

        if ($idGestion) {
            // 1. Obtener cupos y ocupación por carrera
            $carrerasCupos = DB::table('cupocarrera as cc')
                ->join('carrera as c', 'c.Id_carrera', '=', 'cc.Id_carrera')
                ->leftJoin('asignacioncupo as ac', function($join) use ($idGestion) {
                    $join->on('ac.Id_carrera', '=', 'cc.Id_carrera')
                         ->where('ac.Id_gestion', '=', $idGestion);
                })
                ->select(
                    'c.Id_carrera',
                    'c.nombre_carrera',
                    'cc.cantidad_cupos',
                    DB::raw('COUNT(ac."Id_asignacioncupo") as cupos_ocupados')
                )
                ->where('cc.Id_gestion', $idGestion)
                ->groupBy('c.Id_carrera', 'c.nombre_carrera', 'cc.cantidad_cupos')
                ->get();

            // 2. Total postulantes de la gestión
            $totalPostulantes = DB::table('inscripcion')
                ->where('Id_gestion', $idGestion)
                ->count();

            // 3. Postulantes con notas finales calculadas en resultadoacademico
            $totalPostulantesConNotas = DB::table('inscripcion as i')
                ->join('resultadoacademico as r', 'r.Id_postulante', '=', 'i.Id_postulante')
                ->where('i.Id_gestion', $idGestion)
                ->count();

            // 4. Obtener admitidos
            $admitidos = DB::table('postulante as po')
                ->join('inscripcion as i', 'i.Id_postulante', '=', 'po.Id_postulante')
                ->join('persona as p', 'p.Id_persona', '=', 'po.Id_postulante')
                ->join('asignacioncupo as ac', 'ac.Id_asignacioncupo', '=', 'po.Id_asignacioncupo')
                ->join('carrera as ca', 'ca.Id_carrera', '=', 'ac.Id_carrera')
                ->select(
                    'p.ci',
                    DB::raw("CONCAT(p.apellido, ', ', p.nombre) as nombre_completo"),
                    'ca.nombre_carrera',
                    'ac.promedio_final',
                    'ac.puesto_merito'
                )
                ->where('i.Id_gestion', $idGestion)
                ->orderBy('ac.puesto_merito', 'asc')
                ->get();

            // 5. Obtener rechazados (con notas pero sin asignación de cupo)
            $rechazados = DB::table('postulante as po')
                ->join('inscripcion as i', 'i.Id_postulante', '=', 'po.Id_postulante')
                ->join('resultadoacademico as r', 'r.Id_postulante', '=', 'po.Id_postulante')
                ->join('persona as p', 'p.Id_persona', '=', 'po.Id_postulante')
                ->select(
                    'p.ci',
                    DB::raw("CONCAT(p.apellido, ', ', p.nombre) as nombre_completo"),
                    'r.promedio_final'
                )
                ->where('i.Id_gestion', $idGestion)
                ->whereNull('po.Id_asignacioncupo')
                ->orderBy('r.promedio_final', 'desc')
                ->get();
        }

        return view('Gestion_Academica.gestionarAdmisionFinal', compact(
            'gestiones',
            'idGestion',
            'carrerasCupos',
            'admitidos',
            'rechazados',
            'totalPostulantes',
            'totalPostulantesConNotas'
        ));
    }

    /**
     * Consolida los resultados y realiza la asignación por mérito y prioridades.
     */
    public function consolidar(Request $request)
    {
        $request->validate([
            'Id_gestion' => 'required|integer',
        ]);

        $idGestion = $request->Id_gestion;

        // 1. Obtener cupos definidos por carrera en esta gestión
        $cuposCarreras = DB::table('cupocarrera')
            ->where('Id_gestion', $idGestion)
            ->get();

        if ($cuposCarreras->isEmpty()) {
            return back()->withErrors(['error' => 'No hay cupos definidos por carrera para la gestión seleccionada.'])->withInput();
        }

        // Estructurar cupos disponibles
        $cuposDisponibles = [];
        foreach ($cuposCarreras as $cc) {
            $cuposDisponibles[$cc->Id_carrera] = $cc->cantidad_cupos;
        }

        // 2. Obtener postulantes de esta gestión con promedio final calculado
        $postulantes = DB::table('postulante as po')
            ->join('inscripcion as i', 'i.Id_postulante', '=', 'po.Id_postulante')
            ->join('resultadoacademico as r', 'r.Id_postulante', '=', 'po.Id_postulante')
            ->join('persona as p', 'p.Id_persona', '=', 'po.Id_postulante')
            ->select(
                'po.Id_postulante',
                'p.correo',
                'p.nombre',
                'p.apellido',
                'r.promedio_final',
                'i.Codigo_inscripcion'
            )
            ->where('i.Id_gestion', $idGestion)
            ->orderBy('r.promedio_final', 'desc')
            ->get();

        if ($postulantes->isEmpty()) {
            return back()->withErrors(['error' => 'No se encontraron postulantes con notas finales calculadas en esta gestión.'])->withInput();
        }

        $idPostulantesGestion = $postulantes->pluck('Id_postulante')->toArray();

        // 3. Ejecutar algoritmo en transacción
        DB::transaction(function () use ($idGestion, $idPostulantesGestion, $postulantes, $cuposDisponibles) {
            // Limpiar admisiones previas de esta gestión
            DB::table('asignacioncupo')
                ->where('Id_gestion', $idGestion)
                ->delete();

            DB::table('postulante')
                ->whereIn('Id_postulante', $idPostulantesGestion)
                ->update(['Id_asignacioncupo' => null]);

            DB::table('resultadoacademico')
                ->whereIn('Id_postulante', $idPostulantesGestion)
                ->update(['estado_final' => 'Reprobado']);

            // 4. Asignación por mérito y prioridades
            $cuposAsignadosCount = [];
            foreach (array_keys($cuposDisponibles) as $carreraId) {
                $cuposAsignadosCount[$carreraId] = 0;
            }

            $puestoMerito = 1;

            foreach ($postulantes as $postulante) {
                $opcionesCarrera = DB::table('inscripcion_carrera')
                    ->where('Codigo_inscripcion', $postulante->Codigo_inscripcion)
                    ->orderBy('prioridad', 'asc')
                    ->get();

                $carreraAsignadaId = null;

                foreach ($opcionesCarrera as $opcion) {
                    $carreraId = $opcion->Id_carrera;
                    $maxCupos = $cuposDisponibles[$carreraId] ?? 0;
                    $actuales = $cuposAsignadosCount[$carreraId] ?? 0;

                    if ($actuales < $maxCupos) {
                        $carreraAsignadaId = $carreraId;
                        $cuposAsignadosCount[$carreraId]++;
                        break;
                    }
                }

                if ($carreraAsignadaId) {
                    // Crear asignación
                    $idAsignacion = DB::table('asignacioncupo')->insertGetId([
                        'fecha_asignacion' => now()->toDateString(),
                        'promedio_final' => $postulante->promedio_final,
                        'puesto_merito' => $puestoMerito,
                        'estado_asignacion' => 'admitido',
                        'Id_carrera' => $carreraAsignadaId,
                        'Id_gestion' => $idGestion,
                    ], 'Id_asignacioncupo');

                    // Guardar asignación en postulante
                    DB::table('postulante')
                        ->where('Id_postulante', $postulante->Id_postulante)
                        ->update(['Id_asignacioncupo' => $idAsignacion]);

                    // Marcar como Aprobado
                    DB::table('resultadoacademico')
                        ->where('Id_postulante', $postulante->Id_postulante)
                        ->update(['estado_final' => 'Aprobado']);
                }

                $puestoMerito++;
            }
        });

        // Registrar acción en la bitácora
        if (auth()->check()) {
            DB::table('bitacora')->insert([
                'tipo' => 'Gestion Academica',
                'descripcion' => 'Consolidó y ejecutó admisión final para la gestión ID: ' . $idGestion,
                'fecha' => now()->toDateString(),
                'hora' => now()->format('H:i:s'),
                'estado' => 'activo',
                'Id_usuario' => auth()->id(),
            ]);
        }

        return redirect()->route('admision.index', ['Id_gestion' => $idGestion])->with('success', 'Consolidación de admisión final ejecutada con éxito.');
    }

    /**
     * Envía notificaciones de resultados finales a todos los postulantes.
     */
    public function notificar(Request $request)
    {
        $request->validate([
            'Id_gestion' => 'required|integer',
        ]);

        $idGestion = $request->Id_gestion;

        $postulantes = DB::table('postulante as po')
            ->join('inscripcion as i', 'i.Id_postulante', '=', 'po.Id_postulante')
            ->join('persona as p', 'p.Id_persona', '=', 'po.Id_postulante')
            ->leftJoin('asignacioncupo as ac', 'ac.Id_asignacioncupo', '=', 'po.Id_asignacioncupo')
            ->leftJoin('carrera as ca', 'ca.Id_carrera', '=', 'ac.Id_carrera')
            ->select(
                'p.nombre',
                'p.apellido',
                'p.correo',
                'ac.estado_asignacion',
                'ac.puesto_merito',
                'ac.promedio_final',
                'ca.nombre_carrera'
            )
            ->where('i.Id_gestion', $idGestion)
            ->get();

        if ($postulantes->isEmpty()) {
            return back()->withErrors(['error' => 'No hay postulantes registrados en esta gestión para notificar.'])->withInput();
        }

        $notifier = new enviarNotificacionesController();
        $enviados = 0;

        foreach ($postulantes as $p) {
            if (empty($p->correo) || !filter_var($p->correo, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $nombreCompleto = $p->nombre . ' ' . $p->apellido;
            $titulo = 'Resultados de Admisión Final - CUP FICCT';
            
            if ($p->estado_asignacion === 'admitido') {
                $mensaje = "Hola, {$nombreCompleto}.\n\n" .
                    "¡Felicidades! Nos complace informarte que has aprobado satisfactoriamente el CUP (Curso Preuniversitario).\n\n" .
                    "Te damos la más cordial bienvenida a la Universidad Autónoma Gabriel René Moreno y a la Facultad de Ingeniería en Ciencias de la Computación.\n\n" .
                    "La Universidad Autónoma Gabriel René Moreno (UAGRM) es la institución de educación superior pública más importante del departamento de Santa Cruz, dedicada a la formación de profesionales de excelencia, con un fuerte compromiso social y ético.\n\n" .
                    "Por su parte, la Facultad de Ingeniería en Ciencias de la Computación (FICCT) se destaca por formar líderes tecnológicos en el ámbito de la informática, sistemas, y redes, preparándolos para afrontar los retos globales de la era digital y contribuir al desarrollo tecnológico del país.\n\n" .
                    "Detalles de tu admisión:\n" .
                    "- Carrera Asignada: {$p->nombre_carrera}\n" .
                    "- Promedio Final: " . number_format($p->promedio_final, 2) . "\n" .
                    "- Puesto de Mérito: #{$p->puesto_merito}\n\n" .
                    "Felicidades por este gran logro en tu carrera académica. Te deseamos el mayor de los éxitos en esta nueva etapa universitaria.";
            } else {
                $mensaje = "Hola, {$nombreCompleto}.\n\n" .
                    "Te informamos que ha finalizado el proceso de asignación de cupos del CUP Preuniversitario.\n\n" .
                    "Lamentablemente, debido al límite de cupos disponibles por carrera, no has alcanzado una vacante en esta oportunidad.\n\n" .
                    "Te animamos a seguir preparándote para futuras convocatorias.";
            }

            $success = $notifier->enviarNotificacionGeneral($p->correo, $titulo, $mensaje, 'admision final');
            if ($success) {
                $enviados++;
            }
        }

        // Registrar acción en la bitácora
        if (auth()->check()) {
            DB::table('bitacora')->insert([
                'tipo' => 'Gestion Academica',
                'descripcion' => 'Notificó resultados de admisión final a los postulantes de la gestión ID: ' . $idGestion,
                'fecha' => now()->toDateString(),
                'hora' => now()->format('H:i:s'),
                'estado' => 'activo',
                'Id_usuario' => auth()->id(),
            ]);
        }

        return redirect()->route('admision.index', ['Id_gestion' => $idGestion])->with('success', "Notificaciones enviadas con éxito a {$enviados} postulantes.");
    }
}
