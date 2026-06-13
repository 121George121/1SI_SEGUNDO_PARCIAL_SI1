<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Postulantes Registrados
        $postulantesTotal = DB::table('postulante')->count();
        $postulantesEsteMes = DB::table('postulante')
            ->whereYear('fecha_registro', now()->year)
            ->whereMonth('fecha_registro', now()->month)
            ->count();
        $postulantesMesAnterior = DB::table('postulante')
            ->whereYear('fecha_registro', now()->subMonth()->year)
            ->whereMonth('fecha_registro', now()->subMonth()->month)
            ->count();

        if ($postulantesMesAnterior > 0) {
            $postulantesCambio = round((($postulantesEsteMes - $postulantesMesAnterior) / $postulantesMesAnterior) * 100, 1);
            $postulantesCambioTexto = ($postulantesCambio >= 0 ? '+' : '') . $postulantesCambio . '% respecto al mes anterior';
        } else {
            $postulantesCambioTexto = '+' . $postulantesEsteMes . ' nuevos este mes';
        }

        // 2. Inscripciones Activas
        $inscripcionesTotal = DB::table('inscripcion')->count();
        $inscripcionesEsteMes = DB::table('inscripcion')
            ->whereYear('fecha_inscripcion', now()->year)
            ->whereMonth('fecha_inscripcion', now()->month)
            ->count();
        $inscripcionesMesAnterior = DB::table('inscripcion')
            ->whereYear('fecha_inscripcion', now()->subMonth()->year)
            ->whereMonth('fecha_inscripcion', now()->subMonth()->month)
            ->count();

        if ($inscripcionesMesAnterior > 0) {
            $inscripcionesCambio = round((($inscripcionesEsteMes - $inscripcionesMesAnterior) / $inscripcionesMesAnterior) * 100, 1);
            $inscripcionesCambioTexto = ($inscripcionesCambio >= 0 ? '+' : '') . $inscripcionesCambio . '% respecto al mes anterior';
        } else {
            $inscripcionesCambioTexto = '+' . $inscripcionesEsteMes . ' nuevas este mes';
        }

        // 3. Pagos Completados
        $pagosTotal = DB::table('pago_inscripcion')->where('estado_pago_inscripcion', 'Liquidado')->count();
        $pagosEsteMes = DB::table('pago_inscripcion')
            ->where('estado_pago_inscripcion', 'Liquidado')
            ->whereYear('fecha_pago', now()->year)
            ->whereMonth('fecha_pago', now()->month)
            ->count();
        $pagosMesAnterior = DB::table('pago_inscripcion')
            ->where('estado_pago_inscripcion', 'Liquidado')
            ->whereYear('fecha_pago', now()->subMonth()->year)
            ->whereMonth('fecha_pago', now()->subMonth()->month)
            ->count();

        if ($pagosMesAnterior > 0) {
            $pagosCambio = round((($pagosEsteMes - $pagosMesAnterior) / $pagosMesAnterior) * 100, 1);
            $pagosCambioTexto = ($pagosCambio >= 0 ? '+' : '') . $pagosCambio . '% respecto al mes anterior';
        } else {
            $pagosCambioTexto = '+' . $pagosEsteMes . ' completados este mes';
        }

        // 4. Grupos Asignados
        $gruposAsignadosTotal = DB::table('grupo_postulante')->distinct('Id_grupo')->count('Id_grupo');
        $grupoAsignacionesEsteMes = DB::table('grupo_postulante')
            ->whereYear('fecha_asignacion', now()->year)
            ->whereMonth('fecha_asignacion', now()->month)
            ->count();
        $grupoAsignacionesMesAnterior = DB::table('grupo_postulante')
            ->whereYear('fecha_asignacion', now()->subMonth()->year)
            ->whereMonth('fecha_asignacion', now()->subMonth()->month)
            ->count();

        if ($grupoAsignacionesMesAnterior > 0) {
            $grupoCambio = round((($grupoAsignacionesEsteMes - $grupoAsignacionesMesAnterior) / $grupoAsignacionesMesAnterior) * 100, 1);
            $grupoCambioTexto = ($grupoCambio >= 0 ? '+' : '') . $grupoCambio . '% asignados este mes';
        } else {
            $grupoCambioTexto = '+' . $grupoAsignacionesEsteMes . ' asignaciones este mes';
        }

        // --- Gráfico 1: Inscripciones por Estado ---
        // 1. Inscritos
        $cantInscritos = DB::table('inscripcion')->where('estado', 'Inscrito')->count();

        // 2. Observados (tienen algún documento rechazado)
        $cantObservados = DB::table('inscripcion as i')
            ->join('persona_documento as pd', DB::raw('"pd"."Id_persona"'), '=', DB::raw('"i"."Id_postulante"'))
            ->where('i.estado', 'En_Revision')
            ->where('pd.estado', 'Rechazado')
            ->distinct('i.Codigo_inscripcion')
            ->count('i.Codigo_inscripcion');

        // 3. Documentos Pendientes & En Proceso (En_Revision)
        $totalRequiredDocs = DB::table('documento')
            ->whereRaw("LOWER(TRIM(destinado_a)) = 'postulantes'")
            ->count();

        $enRevisionInscripciones = DB::table('inscripcion')
            ->where('estado', 'En_Revision')
            ->get();

        $cantPendientes = 0;
        $cantEnProceso = 0;

        foreach ($enRevisionInscripciones as $ins) {
            $idPostulante = $ins->Id_postulante;

            // Si tiene algún documento rechazado, ya se contó como observado
            $hasRechazado = DB::table('persona_documento')
                ->where('Id_persona', $idPostulante)
                ->where('estado', 'Rechazado')
                ->exists();

            if ($hasRechazado) {
                continue;
            }

            // Contar documentos aprobados
            $approvedDocs = DB::table('persona_documento as pd')
                ->join('documento as doc', DB::raw('"doc"."Id_documento"'), '=', DB::raw('"pd"."Id_documento"'))
                ->where('pd.Id_persona', $idPostulante)
                ->whereRaw("LOWER(TRIM(doc.destinado_a)) = 'postulantes'")
                ->where('pd.estado', 'Aprobado')
                ->count();

            if ($approvedDocs < $totalRequiredDocs) {
                $cantPendientes++;
            } else {
                // Todos los documentos aprobados pero inscripción sigue en revisión (ej. falta pago)
                $cantEnProceso++;
            }
        }

        // --- Gráfico 2: Inscripciones por Mes ---
        $meses = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
            7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
        ];

        $chartMesLabels = [];
        $chartMesData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $chartMesLabels[] = $meses[$date->month];
            
            $chartMesData[] = DB::table('inscripcion')
                ->whereYear('fecha_inscripcion', $date->year)
                ->whereMonth('fecha_inscripcion', $date->month)
                ->count();
        }

        return view('Menu', compact(
            'postulantesTotal',
            'postulantesCambioTexto',
            'inscripcionesTotal',
            'inscripcionesCambioTexto',
            'pagosTotal',
            'pagosCambioTexto',
            'gruposAsignadosTotal',
            'grupoCambioTexto',
            'cantInscritos',
            'cantEnProceso',
            'cantPendientes',
            'cantObservados',
            'chartMesLabels',
            'chartMesData'
        ));
    }
}
