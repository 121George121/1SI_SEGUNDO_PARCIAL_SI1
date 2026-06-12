<?php

namespace App\Http\Controllers\Logistica_Recursos_y_Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class reporteController extends Controller
{
    /**
     * Muestra la vista principal de reportes con los filtros disponibles.
     */
    public function index()
    {
        $gestiones = DB::table('gestion')
            ->select('Id_gestion', 'anio', 'periodo', 'estado')
            ->orderBy('anio', 'desc')
            ->orderBy('periodo', 'desc')
            ->get();

        $carreras = DB::table('carrera')
            ->select('Id_carrera', 'nombre_carrera', 'estado')
            ->where('estado', 'activo')
            ->orderBy('nombre_carrera', 'asc')
            ->get();

        $grupos = DB::table('grupo')
            ->select('Id_grupo', 'sigla_grupo', 'estado')
            ->where('estado', 'activo')
            ->orderBy('sigla_grupo', 'asc')
            ->get();

        return view('Logistica_Recursos_y_Reportes.reporte', compact('gestiones', 'carreras', 'grupos'));
    }

    /**
     * Genera los resultados del reporte según los filtros y tipo seleccionados.
     */
    public function generar(Request $request)
    {
        $request->validate([
            'tipo_reporte' => 'required|string|in:general,aprobados,reprobados,promedios,grupos,materias,docentes,ranking_grupos',
            'Id_gestion' => 'nullable|integer',
            'Id_carrera' => 'nullable|integer',
            'Id_grupo' => 'nullable|integer',
            'meritos' => 'nullable|boolean',
        ]);

        $tipo_reporte = $request->tipo_reporte;
        $resultados = $this->obtenerDatosReporte($request);

        if ($resultados->isEmpty()) {
            return back()->withErrors(['error' => 'No se encontraron registros con los filtros seleccionados.'])->withInput();
        }

        // Para volver a poblar los selects en la vista
        $gestiones = DB::table('gestion')->orderBy('anio', 'desc')->get();
        $carreras = DB::table('carrera')->where('estado', 'activo')->orderBy('nombre_carrera', 'asc')->get();
        $grupos = DB::table('grupo')->where('estado', 'activo')->orderBy('sigla_grupo', 'asc')->get();

        return view('Logistica_Recursos_y_Reportes.reporte', compact('gestiones', 'carreras', 'grupos', 'resultados', 'tipo_reporte'))
            ->withInput($request->all());
    }

    /**
     * Exporta el reporte a formato PDF o Excel.
     */
    public function exportar(Request $request)
    {
        $request->validate([
            'tipo_reporte' => 'required|string|in:general,aprobados,reprobados,promedios,grupos,materias,docentes,ranking_grupos',
            'format' => 'required|string|in:pdf,excel',
        ]);

        $tipo_reporte = $request->tipo_reporte;
        $format = $request->format;
        $resultados = $this->obtenerDatosReporte($request);

        // Registrar acción en la bitácora
        if (auth()->check()) {
            DB::table('bitacora')->insert([
                'tipo' => 'Logistica y Reportes',
                'descripcion' => 'Exportó reporte "' . $this->obtenerNombreReporte($tipo_reporte) . '" en formato ' . strtoupper($format),
                'fecha' => now()->toDateString(),
                'hora' => now()->format('H:i:s'),
                'estado' => 'activo',
                'Id_usuario' => auth()->id(),
            ]);
        }

        $nombreReporte = $this->obtenerNombreReporte($tipo_reporte);

        if ($format === 'excel') {
            return response()->streamDownload(function () use ($resultados, $tipo_reporte, $nombreReporte) {
                echo view('Logistica_Recursos_y_Reportes.reporteExcel', compact('resultados', 'tipo_reporte', 'nombreReporte'))->render();
            }, 'reporte_' . $tipo_reporte . '_' . now()->format('YmdHis') . '.xls', [
                'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
        } else {
            $pdf = Pdf::loadView('Logistica_Recursos_y_Reportes.reportePdf', compact('resultados', 'tipo_reporte', 'nombreReporte'));
            return $pdf->download('reporte_' . $tipo_reporte . '_' . now()->format('YmdHis') . '.pdf');
        }
    }

    /**
     * Devuelve el nombre descriptivo de un reporte.
     */
    private function obtenerNombreReporte(string $tipo): string
    {
        $nombres = [
            'general' => 'Lista General de Postulantes',
            'aprobados' => 'Postulantes Aprobados',
            'reprobados' => 'Postulantes Reprobados',
            'promedios' => 'Promedios Generales por Carrera',
            'grupos' => 'Cantidad de Grupos Habilitados',
            'materias' => 'Estadísticas por Materia',
            'docentes' => 'Docentes por Grupos',
            'ranking_grupos' => 'Grupos con Mayor Cantidad de Aprobados'
        ];

        return $nombres[$tipo] ?? 'Reporte Académico';
    }

    /**
     * Construye y ejecuta las consultas según el reporte seleccionado.
     */
    private function obtenerDatosReporte(Request $request)
    {
        $tipo = $request->tipo_reporte;

        switch ($tipo) {
            case 'general':
            case 'aprobados':
            case 'reprobados':
                $query = DB::table('postulante as po')
                    ->join('persona as p', 'p.Id_persona', '=', 'po.Id_postulante')
                    ->leftJoin('resultadoacademico as r', 'r.Id_postulante', '=', 'po.Id_postulante')
                    ->leftJoin('grupo_postulante as gp', 'gp.Id_postulante', '=', 'po.Id_postulante')
                    ->leftJoin('grupo as g', 'g.Id_grupo', '=', 'gp.Id_grupo')
                    ->leftJoin('inscripcion as i', 'i.Id_postulante', '=', 'po.Id_postulante')
                    ->leftJoin('inscripcion_carrera as ic', function ($join) {
                        $join->on('ic.Codigo_inscripcion', '=', 'i.Codigo_inscripcion')
                             ->where('ic.prioridad', '=', 1);
                    })
                    ->leftJoin('carrera as ca', 'ca.Id_carrera', '=', 'ic.Id_carrera')
                    ->select(
                        'po.Id_postulante',
                        'p.ci',
                        'p.nombre',
                        'p.apellido',
                        'p.correo',
                        'p.telefono',
                        'ca.nombre_carrera',
                        'g.sigla_grupo',
                        'r.promedio_final',
                        'r.estado_final',
                        'i.estado as estado_inscripcion'
                    );

                if ($request->filled('Id_gestion')) {
                    $query->where(function ($q) use ($request) {
                        $q->where('i.Id_gestion', $request->Id_gestion)
                          ->orWhere('g.Id_gestion', $request->Id_gestion);
                    });
                }
                if ($request->filled('Id_carrera')) {
                    $query->where('ic.Id_carrera', $request->Id_carrera);
                }
                if ($request->filled('Id_grupo')) {
                    $query->where('gp.Id_grupo', $request->Id_grupo);
                }

                if ($tipo === 'aprobados') {
                    $query->where(DB::raw('LOWER(r.estado_final)'), '=', 'aprobado');
                } elseif ($tipo === 'reprobados') {
                    $query->where(DB::raw('LOWER(r.estado_final)'), '=', 'reprobado');
                }

                if ($request->filled('meritos') && $request->meritos == '1') {
                    $query->orderBy('r.promedio_final', 'desc');
                } else {
                    $query->orderBy('p.apellido', 'asc')->orderBy('p.nombre', 'asc');
                }
                break;

            case 'promedios':
                $query = DB::table('inscripcion_carrera as ic')
                    ->join('carrera as ca', 'ca.Id_carrera', '=', 'ic.Id_carrera')
                    ->join('inscripcion as i', 'i.Codigo_inscripcion', '=', 'ic.Codigo_inscripcion')
                    ->join('postulante as po', 'po.Id_postulante', '=', 'i.Id_postulante')
                    ->leftJoin('resultadoacademico as r', 'r.Id_postulante', '=', 'po.Id_postulante')
                    ->select(
                        'ca.nombre_carrera',
                        DB::raw('COUNT(po.Id_postulante) as total_postulantes'),
                        DB::raw('ROUND(AVG(r.promedio_final), 2) as promedio_general')
                    )
                    ->groupBy('ca.Id_carrera', 'ca.nombre_carrera')
                    ->orderBy('promedio_general', 'desc');

                if ($request->filled('Id_gestion')) {
                    $query->where('i.Id_gestion', $request->Id_gestion);
                }
                if ($request->filled('Id_carrera')) {
                    $query->where('ca.Id_carrera', $request->Id_carrera);
                }
                break;

            case 'grupos':
                $query = DB::table('grupo as g')
                    ->leftJoin('aula as au', 'au.Id_aula', '=', 'g.Id_aula')
                    ->leftJoin('modalidad as m', 'm.Id_modalidad', '=', 'g.Id_modalidad')
                    ->leftJoin('turno as t', 't.Id_turno', '=', 'g.Id_turno')
                    ->select(
                        'g.Id_grupo',
                        'g.sigla_grupo',
                        'au.nro_aula',
                        'm.nombre_modalidad',
                        't.nombre as nombre_turno',
                        'g.capacidad_max',
                        'g.cant_estudiantes',
                        'g.estado'
                    )
                    ->orderBy('g.sigla_grupo', 'asc');

                if ($request->filled('Id_gestion')) {
                    $query->where('g.Id_gestion', $request->Id_gestion);
                }
                if ($request->filled('Id_grupo')) {
                    $query->where('g.Id_grupo', $request->Id_grupo);
                }
                break;

            case 'materias':
                $query = DB::table('grupo_materia as gm')
                    ->join('materia as mat', 'mat.Id_materia', '=', 'gm.Id_materia')
                    ->join('grupo as g', 'g.Id_grupo', '=', 'gm.Id_grupo')
                    ->leftJoin('docente as doc', 'doc.Id_docente', '=', 'gm.Id_docente')
                    ->leftJoin('persona as p', 'p.Id_persona', '=', 'doc.Id_docente')
                    ->leftJoin('nota as n', 'n.Id_grupo', '=', 'g.Id_grupo')
                    ->select(
                        'mat.nombre as nombre_materia',
                        'g.sigla_grupo',
                        DB::raw("CONCAT(p.nombre, ' ', p.apellido) as nombre_docente"),
                        DB::raw('COUNT(DISTINCT n.Id_postulante) as total_estudiantes'),
                        DB::raw('ROUND(AVG(n.nota), 2) as promedio_nota')
                    )
                    ->groupBy('mat.Id_materia', 'mat.nombre', 'g.Id_grupo', 'g.sigla_grupo', 'p.nombre', 'p.apellido')
                    ->orderBy('mat.nombre', 'asc')
                    ->orderBy('g.sigla_grupo', 'asc');

                if ($request->filled('Id_gestion')) {
                    $query->where('g.Id_gestion', $request->Id_gestion);
                }
                if ($request->filled('Id_grupo')) {
                    $query->where('g.Id_grupo', $request->Id_grupo);
                }
                break;

            case 'docentes':
                $query = DB::table('grupo_materia as gm')
                    ->join('grupo as g', 'g.Id_grupo', '=', 'gm.Id_grupo')
                    ->join('materia as m', 'm.Id_materia', '=', 'gm.Id_materia')
                    ->join('docente as d', 'd.Id_docente', '=', 'gm.Id_docente')
                    ->join('persona as p', 'p.Id_persona', '=', 'd.Id_docente')
                    ->select(
                        'g.sigla_grupo',
                        'm.nombre as nombre_materia',
                        'p.ci',
                        DB::raw("CONCAT(p.nombre, ' ', p.apellido) as nombre_docente"),
                        'd.anio_servicio'
                    )
                    ->orderBy('nombre_docente', 'asc')
                    ->orderBy('g.sigla_grupo', 'asc');

                if ($request->filled('Id_gestion')) {
                    $query->where('g.Id_gestion', $request->Id_gestion);
                }
                if ($request->filled('Id_grupo')) {
                    $query->where('g.Id_grupo', $request->Id_grupo);
                }
                break;

            case 'ranking_grupos':
                $query = DB::table('grupo_postulante as gp')
                    ->join('grupo as g', 'g.Id_grupo', '=', 'gp.Id_grupo')
                    ->join('postulante as po', 'po.Id_postulante', '=', 'gp.Id_postulante')
                    ->join('resultadoacademico as r', 'r.Id_postulante', '=', 'po.Id_postulante')
                    ->select(
                        'g.sigla_grupo',
                        DB::raw('COUNT(po.Id_postulante) as total_postulantes'),
                        DB::raw("SUM(CASE WHEN LOWER(r.estado_final) = 'aprobado' THEN 1 ELSE 0 END) as aprobados"),
                        DB::raw("ROUND(100.0 * SUM(CASE WHEN LOWER(r.estado_final) = 'aprobado' THEN 1 ELSE 0 END) / COUNT(po.Id_postulante), 2) as porcentaje_aprobados")
                    )
                    ->groupBy('g.Id_grupo', 'g.sigla_grupo')
                    ->orderBy('aprobados', 'desc');

                if ($request->filled('Id_gestion')) {
                    $query->where('g.Id_gestion', $request->Id_gestion);
                }
                if ($request->filled('Id_grupo')) {
                    $query->where('g.Id_grupo', $request->Id_grupo);
                }
                break;

            default:
                $query = DB::table('postulante')->select('*');
                break;
        }

        return $query->get();
    }
}
