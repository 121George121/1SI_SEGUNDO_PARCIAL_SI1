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
                $query = DB::table('docente as d')
                    ->join('persona as p', 'p.Id_persona', '=', 'd.Id_docente')
                    ->leftJoin('grupo_materia as gm', 'gm.Id_docente', '=', 'd.Id_docente')
                    ->leftJoin('grupo as g', 'g.Id_grupo', '=', 'gm.Id_grupo')
                    ->leftJoin('materia as mat', 'mat.Id_materia', '=', 'gm.Id_materia')
                    ->leftJoin('gestion as ge', 'ge.Id_gestion', '=', 'g.Id_gestion')
                    ->leftJoin('grupo_horario as gh', 'gh.Id_grupo', '=', 'g.Id_grupo')
                    ->leftJoin('horario as h', 'h.Id_horario', '=', 'gh.Id_horario')
                    ->leftJoin('grupo_postulante as gpos', 'gpos.Id_grupo', '=', 'g.Id_grupo')
                    ->leftJoin('inscripcion as ins', 'ins.Id_postulante', '=', 'gpos.Id_postulante')
                    ->leftJoin('inscripcion_carrera as ic', function($join) {
                        $join->on('ic.Codigo_inscripcion', '=', 'ins.Codigo_inscripcion')
                             ->where('ic.prioridad', '=', 1);
                    })
                    ->leftJoin('carrera as ca', 'ca.Id_carrera', '=', 'ic.Id_carrera')
                    ->select(
                        'd.Id_docente',
                        'p.ci',
                        DB::raw("CONCAT(p.nombre, ' ', p.apellido) as nombre_docente"),
                        'd.anio_servicio',
                        'g.sigla_grupo',
                        'mat.nombre as nombre_materia',
                        'ge.anio as anio_gestion',
                        'ge.periodo as periodo_gestion',
                        DB::raw("CONCAT(h.dia, ' ', to_char(h.hora_inicio, 'HH24:MI'), '-', to_char(h.hora_fin, 'HH24:MI')) as horario_clase"),
                        DB::raw("string_agg(DISTINCT ca.nombre_carrera, ', ') as nombre_carreras")
                    )
                    ->groupBy(
                        'd.Id_docente',
                        'p.ci',
                        'p.nombre',
                        'p.apellido',
                        'd.anio_servicio',
                        'g.sigla_grupo',
                        'mat.nombre',
                        'ge.anio',
                        'ge.periodo',
                        'h.dia',
                        'h.hora_inicio',
                        'h.hora_fin'
                    )
                    ->orderBy('nombre_docente', 'asc');

                if ($request->filled('Id_gestion')) {
                    $query->where('g.Id_gestion', $request->Id_gestion);
                }
                if ($request->filled('Id_grupo')) {
                    $query->where('g.Id_grupo', $request->Id_grupo);
                }
                if ($request->filled('Id_carrera')) {
                    $query->where('ic.Id_carrera', $request->Id_carrera);
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

    /**
     * Parsea un texto de voz a filtros de reporte, usando la API de Gemini si está disponible,
     * o un resolvedor por reglas locales de NLP como fallback.
     */
    public function parsearAudio(Request $request)
    {
        $request->validate([
            'texto' => 'required|string',
        ]);

        $texto = $request->texto;

        // Obtener datos de la base de datos para mapeo de IDs
        $gestiones = DB::table('gestion')
            ->select('Id_gestion', 'anio', 'periodo')
            ->get();

        $carreras = DB::table('carrera')
            ->select('Id_carrera', 'nombre_carrera')
            ->where('estado', 'activo')
            ->get();

        $grupos = DB::table('grupo')
            ->select('Id_grupo', 'sigla_grupo')
            ->where('estado', 'activo')
            ->get();

        $apiKey = env('GEMINI_API_KEY');

        if ($apiKey) {
            try {
                // Formatear opciones para enviar al modelo
                $gestionesStr = "";
                foreach ($gestiones as $g) {
                    $gestionesStr .= "- ID {$g->Id_gestion}: {$g->anio} - {$g->periodo}\n";
                }

                $carrerasStr = "";
                foreach ($carreras as $c) {
                    $carrerasStr .= "- ID {$c->Id_carrera}: {$c->nombre_carrera}\n";
                }

                $gruposStr = "";
                foreach ($grupos as $g) {
                    $gruposStr .= "- ID {$g->Id_grupo}: {$g->sigla_grupo}\n";
                }

                $prompt = "Tu tarea es analizar el siguiente comando de voz en español de un usuario que desea generar un reporte académico y mapearlo a los filtros correspondientes en formato JSON.

Opciones de tipo de reporte válidas (tipo_reporte):
- 'general': Lista General de Postulantes
- 'aprobados': Postulantes Aprobados
- 'reprobados': Postulantes Reprobados
- 'promedios': Promedios Generales por Carrera
- 'grupos': Cantidad de Grupos Habilitados
- 'materias': Estadísticas por Materia
- 'docentes': Docentes por Grupos
- 'ranking_grupos': Grupos con Mayor Cantidad de Aprobados

Gestiones Académicas disponibles:
{$gestionesStr}

Carreras disponibles:
{$carrerasStr}

Grupos disponibles:
{$gruposStr}

Criterio de ordenamiento (meritos):
- 0: Alfabético (Apellido, Nombre)
- 1: Mérito Académico (Mayor Promedio) (selecciona esto si mencionan 'méritos', 'promedios más altos', 'mejores promedios', 'mayor promedio', 'calificaciones', etc.)

El comando de voz del usuario es: '{$texto}'

Debes retornar ÚNICAMENTE un objeto JSON con la siguiente estructura (si no se encuentra o no aplica algún filtro opcional como carrera, grupo o gestión, asígnale null):
{
  \"tipo_reporte\": \"general\" | \"aprobados\" | \"reprobados\" | \"promedios\" | \"grupos\" | \"materias\" | \"docentes\" | \"ranking_grupos\",
  \"Id_gestion\": int | null,
  \"Id_carrera\": int | null,
  \"Id_grupo\": int | null,
  \"meritos\": int (0 o 1)
}

No agregues texto explicativo ni bloques de código markdown, solo el objeto JSON.";

                // Realizar petición HTTP a la API de Gemini
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json'
                    ]
                ]);

                if ($response->successful()) {
                    $jsonRes = $response->json();
                    $textResult = $jsonRes['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    $filters = json_decode(trim($textResult), true);

                    if ($filters && isset($filters['tipo_reporte'])) {
                        return response()->json([
                            'success' => true,
                            'metodo' => 'Gemini AI',
                            'filters' => $filters
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Fallback en caso de error
            }
        }

        // Fallback local basado en reglas de texto
        $filters = $this->parsearAudioLocal($texto, $gestiones, $carreras, $grupos);
        return response()->json([
            'success' => true,
            'metodo' => 'Local Fallback NLP',
            'filters' => $filters
        ]);
    }

    /**
     * Fallback por reglas de texto locales si la API key de Gemini no está o falla la petición.
     */
    private function parsearAudioLocal(string $texto, $gestiones, $carreras, $grupos)
    {
        $textoLower = mb_strtolower($texto);
        $textoSinAcentos = $this->quitarAcentos($textoLower);
        
        // 1. Determinar tipo de reporte
        $tipo_reporte = 'general';
        if (str_contains($textoSinAcentos, 'aprobado') || str_contains($textoSinAcentos, 'admitido') || str_contains($textoSinAcentos, 'pasaron')) {
            $tipo_reporte = 'aprobados';
        } elseif (str_contains($textoSinAcentos, 'reprobado') || str_contains($textoSinAcentos, 'aplazado') || str_contains($textoSinAcentos, 'no admitido')) {
            $tipo_reporte = 'reprobados';
        } elseif (str_contains($textoSinAcentos, 'promedio') || str_contains($textoSinAcentos, 'media')) {
            $tipo_reporte = 'promedios';
        } elseif (str_contains($textoSinAcentos, 'materia') || str_contains($textoSinAcentos, 'asignatura') || str_contains($textoSinAcentos, 'estadistica')) {
            $tipo_reporte = 'materias';
        } elseif (str_contains($textoSinAcentos, 'docente') || str_contains($textoSinAcentos, 'profesor') || str_contains($textoSinAcentos, 'maestro')) {
            $tipo_reporte = 'docentes';
        } elseif (str_contains($textoSinAcentos, 'ranking') || str_contains($textoSinAcentos, 'mejores grupos') || str_contains($textoSinAcentos, 'mas aprobado')) {
            $tipo_reporte = 'ranking_grupos';
        } elseif (str_contains($textoSinAcentos, 'grupo') || str_contains($textoSinAcentos, 'habilitado')) {
            $tipo_reporte = 'grupos';
        }

        // 2. Determinar gestión
        $Id_gestion = null;
        
        // Buscar un año de 4 dígitos en el texto
        $anioDetectado = null;
        if (preg_match('/\b(20[0-9]{2})\b/', $textoLower, $matches)) {
            $anioDetectado = intval($matches[1]);
        }
        
        // Determinar periodo mencionado
        $periodoDetectado = null;
        if (str_contains($textoSinAcentos, 'ii') || str_contains($textoSinAcentos, '2') || str_contains($textoSinAcentos, 'dos') || str_contains($textoSinAcentos, 'segundo')) {
            $periodoDetectado = 'ii';
        } elseif (str_contains($textoSinAcentos, 'i') || str_contains($textoSinAcentos, '1') || str_contains($textoSinAcentos, 'uno') || str_contains($textoSinAcentos, 'primer')) {
            $periodoDetectado = 'i';
        }
        
        // Buscar coincidencia en la base de datos
        foreach ($gestiones as $g) {
            $anio = intval($g->anio);
            $periodo = mb_strtolower($g->periodo); // e.g. "i" o "ii"
            
            if ($anioDetectado && $anio !== $anioDetectado) {
                continue;
            }
            
            // Si coincide el año y además el periodo coincide
            if ($anioDetectado && $periodoDetectado && $periodo === $periodoDetectado) {
                $Id_gestion = $g->Id_gestion;
                break;
            }
            
            // Si solo coincide el año (sin periodo especificado o primer match)
            if ($anioDetectado && !$Id_gestion) {
                $Id_gestion = $g->Id_gestion;
            }
        }

        // 3. Determinar carrera
        $Id_carrera = null;
        foreach ($carreras as $c) {
            $nombreCarrera = mb_strtolower($c->nombre_carrera);
            $nombreCarreraSinAcentos = $this->quitarAcentos($nombreCarrera);
            
            // Si el texto hablado contiene el nombre completo de la carrera
            if (str_contains($textoSinAcentos, $nombreCarreraSinAcentos)) {
                $Id_carrera = $c->Id_carrera;
                break;
            }
            
            // O si contiene palabras clave significativas (ej. "sistemas", "redes", "informatica")
            $palabrasClave = array_filter(explode(' ', $nombreCarreraSinAcentos), function($word) {
                return strlen($word) > 4 && !in_array($word, ['ingenieria', 'licenciatura', 'tecnologia']);
            });
            
            foreach ($palabrasClave as $palabra) {
                if (str_contains($textoSinAcentos, $palabra)) {
                    $Id_carrera = $c->Id_carrera;
                    break 2; // salir de ambos bucles
                }
            }
        }

        // 4. Determinar grupo
        $Id_grupo = null;
        $textoNormalizado = preg_replace('/[^a-z0-9]/', '', $textoSinAcentos);
        foreach ($grupos as $g) {
            $sigla = mb_strtolower($g->sigla_grupo);
            $siglaNormalizada = preg_replace('/[^a-z0-9]/', '', $sigla);
            // Buscar coincidencia exacta o contenida
            if ($siglaNormalizada !== '' && (
                str_contains($textoNormalizado, $siglaNormalizada) ||
                preg_match('/\b' . preg_quote($sigla, '/') . '\b/', $textoLower)
            )) {
                $Id_grupo = $g->Id_grupo;
                break;
            }
        }

        // 5. Criterio de ordenamiento
        $meritos = 0;
        if (str_contains($textoSinAcentos, 'merito') || str_contains($textoSinAcentos, 'promedio alto') || str_contains($textoSinAcentos, 'mejor promedio') || str_contains($textoSinAcentos, 'calificacion')) {
            $meritos = 1;
        }

        return [
            'tipo_reporte' => $tipo_reporte,
            'Id_gestion' => $Id_gestion,
            'Id_carrera' => $Id_carrera,
            'Id_grupo' => $Id_grupo,
            'meritos' => $meritos
        ];
    }

    /**
     * Remueve acentos y caracteres especiales para comparaciones de texto más flexibles.
     */
    private function quitarAcentos(string $cadena): string
    {
        return str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ'],
            ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N'],
            $cadena
        );
    }
}

