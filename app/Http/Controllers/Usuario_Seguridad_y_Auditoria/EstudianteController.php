<?php

namespace App\Http\Controllers\Usuario_Seguridad_y_Auditoria;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EstudianteController extends Controller
{
    /**
     * 1. Perfil del Estudiante
     */
    public function perfil()
    {
        $usuario = Auth::user();
        $persona = $usuario->persona;

        if (!$persona) {
            return redirect()->route('login')->withErrors(['correo' => 'La persona asociada al usuario no existe.']);
        }

        $postulante = DB::table('postulante')->where('Id_postulante', $persona->Id_persona)->first();
        $inscripcion = DB::table('inscripcion')->where('Id_postulante', $persona->Id_persona)->first();

        $carreraPostulada = null;
        if ($inscripcion) {
            $carreraPostulada = DB::table('inscripcion_carrera as ic')
                ->join('carrera as c', 'c.Id_carrera', '=', 'ic.Id_carrera')
                ->where('ic.Codigo_inscripcion', $inscripcion->Codigo_inscripcion)
                ->where('ic.prioridad', 1)
                ->value('c.nombre_carrera');
        }

        return view('Usuario_Seguridad_y_Auditoria.Estudiante.perfil', compact(
            'usuario',
            'persona',
            'postulante',
            'inscripcion',
            'carreraPostulada'
        ));
    }

    /**
     * 2. Estado de inscripción
     */
    public function estadoInscripcion()
    {
        $usuario = Auth::user();
        $persona = $usuario->persona;

        $inscripcion = DB::table('inscripcion')->where('Id_postulante', $persona->Id_persona)->first();

        // Obtener todos los documentos requeridos para postulantes
        $documentosRequeridos = DB::table('documento')
            ->whereRaw("LOWER(TRIM(destinado_a)) = 'postulantes'")
            ->get();

        // Obtener los documentos entregados por el postulante
        $documentosEntregados = DB::table('persona_documento')
            ->where('Id_persona', $persona->Id_persona)
            ->get()
            ->keyBy('Id_documento');

        // Mapear cada documento con su estado
        $documentosLista = $documentosRequeridos->map(function ($doc) use ($documentosEntregados) {
            $entrega = $documentosEntregados->get($doc->Id_documento);
            return [
                'nombre' => $doc->nombre,
                'tipo' => $doc->tipo_documento,
                'estado' => $entrega ? ucfirst(strtolower(trim($entrega->estado))) : 'Pendiente',
                'observacion' => ($entrega && $entrega->observacion) ? $entrega->observacion : 'Sin observaciones'
            ];
        });

        return view('Usuario_Seguridad_y_Auditoria.Estudiante.estado-inscripcion', compact(
            'inscripcion',
            'documentosLista'
        ));
    }

    /**
     * 3. Estado de admisión
     */
    public function estadoAdmision()
    {
        $usuario = Auth::user();
        $persona = $usuario->persona;

        $postulante = DB::table('postulante')->where('Id_postulante', $persona->Id_persona)->first();
        $inscripcion = DB::table('inscripcion')->where('Id_postulante', $persona->Id_persona)->first();

        $carreraPostulada = null;
        if ($inscripcion) {
            $carreraPostulada = DB::table('inscripcion_carrera as ic')
                ->join('carrera as c', 'c.Id_carrera', '=', 'ic.Id_carrera')
                ->where('ic.Codigo_inscripcion', $inscripcion->Codigo_inscripcion)
                ->where('ic.prioridad', 1)
                ->value('c.nombre_carrera');
        }

        // Buscar matrícula en pagos
        $pagoMatricula = DB::table('pago')
            ->whereRaw("LOWER(TRIM(concepto_pago)) LIKE '%matricula%'")
            ->first();

        $matriculaPagada = false;
        if ($inscripcion && $pagoMatricula) {
            $matriculaPagada = DB::table('pago_inscripcion')
                ->where('Codigo_inscripcion', $inscripcion->Codigo_inscripcion)
                ->where('Id_pago', $pagoMatricula->Id_pago)
                ->whereRaw("LOWER(TRIM(estado_pago_inscripcion)) = 'liquidado'")
                ->exists();
        }

        // Determinar estado de admisión
        $estado_admision = 'Pendiente';
        $mensaje_admision = 'Tu admisión aún se encuentra pendiente de revisión.';
        $carrera_aceptada = null;
        $grupo_asignado = null;
        $turno_asignado = null;
        $fecha_admision = null;

        if ($postulante && !empty($postulante->Id_asignacioncupo)) {
            $asignacion = DB::table('asignacioncupo as ac')
                ->join('carrera as c', 'c.Id_carrera', '=', 'ac.Id_carrera')
                ->where('ac.Id_asignacioncupo', $postulante->Id_asignacioncupo)
                ->first();

            if ($asignacion && strtolower(trim($asignacion->estado_asignacion)) === 'admitido') {
                $estado_admision = 'Aceptado';
                $mensaje_admision = 'Fuiste aceptado a la carrera.';
                $carrera_aceptada = $asignacion->nombre_carrera;
                $fecha_admision = $asignacion->fecha_asignacion;

                // Buscar grupo asignado
                $grupoPost = DB::table('grupo_postulante as gp')
                    ->join('grupo as g', 'g.Id_grupo', '=', 'gp.Id_grupo')
                    ->leftJoin('turno as t', 't.Id_turno', '=', 'g.Id_turno')
                    ->select('g.sigla_grupo', 't.nombre as turno')
                    ->where('gp.Id_postulante', $persona->Id_persona)
                    ->first();

                if ($grupoPost) {
                    $grupo_asignado = $grupoPost->sigla_grupo;
                    $turno_asignado = $grupoPost->turno;
                }
            }
        } else {
            // Verificar si fue reprobado en resultadoacademico
            $resultado = DB::table('resultadoacademico')
                ->where('Id_postulante', $persona->Id_persona)
                ->first();

            if ($resultado && strtolower(trim($resultado->estado_final)) === 'reprobado') {
                $estado_admision = 'Rechazado';
                $mensaje_admision = 'No fuiste admitido a la carrera postulada. Para más información, comunícate con admisiones.';
            } else {
                $estado_admision = 'En revisión';
                $mensaje_admision = 'Tu admisión aún se encuentra en revisión.';
            }
        }

        return view('Usuario_Seguridad_y_Auditoria.Estudiante.estado-admision', compact(
            'estado_admision',
            'mensaje_admision',
            'carreraPostulada',
            'carrera_aceptada',
            'grupo_asignado',
            'turno_asignado',
            'fecha_admision',
            'matriculaPagada',
            'inscripcion'
        ));
    }

    /**
     * 4. Notas (Lectura)
     */
    public function notas()
    {
        $usuario = Auth::user();
        $persona = $usuario->persona;

        $notas = DB::table('nota as n')
            ->join('evaluacion as e', 'e.Id_evaluacion', '=', 'n.Id_evaluacion')
            ->join('materia as m', 'm.Id_materia', '=', 'e.Id_materia')
            ->select(
                'n.nota',
                'n.estado_academico',
                'n.fecha',
                'e.numero_evaluacion',
                'm.nombre as nombre_materia'
            )
            ->where('n.Id_postulante', $persona->Id_persona)
            ->get();

        $notasMapeadas = $notas->map(function ($nota) {
            $evaluacionNombre = 'Evaluación ' . $nota->numero_evaluacion . ' - ' . $nota->nombre_materia;
            if ($nota->numero_evaluacion == 1) {
                $evaluacionNombre = 'Examen de admisión (' . $nota->nombre_materia . ')';
            } elseif ($nota->numero_evaluacion == 2) {
                $evaluacionNombre = 'Entrevista (' . $nota->nombre_materia . ')';
            }
            return [
                'evaluacion' => $evaluacionNombre,
                'nota' => $nota->nota,
                'estado' => $nota->estado_academico,
                'fecha' => $nota->fecha,
                'observacion' => strtolower($nota->estado_academico) === 'aprobado' ? 'Sin observaciones' : 'Pendiente o reprobado'
            ];
        });

        return view('Usuario_Seguridad_y_Auditoria.Estudiante.notas', compact('notasMapeadas'));
    }

    /**
     * 5. Pagar matrícula
     */
    public function pagarMatricula()
    {
        $usuario = Auth::user();
        $persona = $usuario->persona;

        $inscripcion = DB::table('inscripcion')->where('Id_postulante', $persona->Id_persona)->first();

        if (!$inscripcion) {
            return redirect()->route('estudiante.perfil')->withErrors(['error' => 'No tienes un registro de inscripción activo.']);
        }

        // Buscar concepto matrícula
        $pagoMatricula = DB::table('pago')
            ->whereRaw("LOWER(TRIM(concepto_pago)) LIKE '%matricula%'")
            ->first();

        if (!$pagoMatricula) {
            // Generar concepto de pago de matrícula de forma robusta por defecto si no existe
            $idPago = DB::table('pago')->insertGetId([
                'concepto_pago' => 'Matrícula',
                'monto' => 350.00,
                'estado_pago' => 'activo',
                'observaciones' => 'Pago de matrícula del CUP Preuniversitario'
            ], 'Id_pago');

            $pagoMatricula = DB::table('pago')->where('Id_pago', $idPago)->first();
        }

        // Asegurar que esté asignado al postulante
        $pagoInscripcion = DB::table('pago_inscripcion')
            ->where('Codigo_inscripcion', $inscripcion->Codigo_inscripcion)
            ->where('Id_pago', $pagoMatricula->Id_pago)
            ->first();

        if (!$pagoInscripcion) {
            DB::table('pago_inscripcion')->insert([
                'Id_pago' => $pagoMatricula->Id_pago,
                'Codigo_inscripcion' => $inscripcion->Codigo_inscripcion,
                'estado_pago_inscripcion' => 'Pendiente',
                'fecha_pago' => null,
                'Id_comprobante' => null
            ]);
            $pagoInscripcion = DB::table('pago_inscripcion')
                ->where('Codigo_inscripcion', $inscripcion->Codigo_inscripcion)
                ->where('Id_pago', $pagoMatricula->Id_pago)
                ->first();
        }

        return view('Usuario_Seguridad_y_Auditoria.Estudiante.pagar-matricula', compact(
            'pagoMatricula',
            'pagoInscripcion',
            'inscripcion'
        ));
    }

    /**
     * 6. Ver boleta de inscripción
     */
    public function boletaInscripcion()
    {
        $usuario = Auth::user();
        $persona = $usuario->persona;

        $postulante = DB::table('postulante')->where('Id_postulante', $persona->Id_persona)->first();
        $inscripcion = DB::table('inscripcion')->where('Id_postulante', $persona->Id_persona)->first();

        if (!$inscripcion) {
            return redirect()->route('estudiante.estado-admision')->withErrors(['error' => 'No se encontró tu inscripción.']);
        }

        // Verificar aceptación
        $admitido = false;
        $carreraAceptada = null;
        $carreraAceptadaId = null;
        $fechaAdmision = null;

        if ($postulante && !empty($postulante->Id_asignacioncupo)) {
            $asignacion = DB::table('asignacioncupo as ac')
                ->join('carrera as c', 'c.Id_carrera', '=', 'ac.Id_carrera')
                ->where('ac.Id_asignacioncupo', $postulante->Id_asignacioncupo)
                ->first();

            if ($asignacion && strtolower(trim($asignacion->estado_asignacion)) === 'admitido') {
                $admitido = true;
                $carreraAceptada = $asignacion->nombre_carrera;
                $carreraAceptadaId = $asignacion->Id_carrera;
                $fechaAdmision = $asignacion->fecha_asignacion;
            }
        }

        if (!$admitido) {
            return redirect()->route('estudiante.estado-admision')->withErrors([
                'error' => 'La boleta de inscripción estará disponible cuando seas aceptado a una carrera.'
            ]);
        }

        // Verificar pago matrícula
        $pagoMatricula = DB::table('pago')
            ->whereRaw("LOWER(TRIM(concepto_pago)) LIKE '%matricula%'")
            ->first();

        $pagoInscripcion = null;
        if ($pagoMatricula) {
            $pagoInscripcion = DB::table('pago_inscripcion')
                ->where('Codigo_inscripcion', $inscripcion->Codigo_inscripcion)
                ->where('Id_pago', $pagoMatricula->Id_pago)
                ->first();
        }

        $pagado = $pagoInscripcion && strtolower(trim($pagoInscripcion->estado_pago_inscripcion)) === 'liquidado';

        if (!$pagado) {
            return redirect()->route('estudiante.estado-admision')->withErrors([
                'error' => 'Para ver tu boleta de inscripción, primero debes pagar tu matrícula.'
            ]);
        }

        // Obtener grupo, turno y horarios
        $grupo_sigla = 'Sin grupo asignado';
        $turno_nombre = 'Sin turno asignado';
        $horarios = collect();

        $grupoPost = DB::table('grupo_postulante as gp')
            ->join('grupo as g', 'g.Id_grupo', '=', 'gp.Id_grupo')
            ->leftJoin('turno as t', 't.Id_turno', '=', 'g.Id_turno')
            ->select('g.Id_grupo', 'g.sigla_grupo', 't.nombre as turno')
            ->where('gp.Id_postulante', $persona->Id_persona)
            ->first();

        if ($grupoPost) {
            $grupo_sigla = $grupoPost->sigla_grupo;
            $turno_nombre = $grupoPost->turno;

            // Obtener horarios asociados al grupo
            $horarios = DB::table('grupo_horario as gh')
                ->join('horario as h', 'h.Id_horario', '=', 'gh.Id_horario')
                ->join('materia as m', 'm.Id_materia', '=', 'gh.Id_materia')
                ->select('h.dia', 'h.hora_inicio', 'h.hora_fin', 'm.nombre as materia')
                ->where('gh.Id_grupo', $grupoPost->Id_grupo)
                ->get();
        }

        // Obtener comprobante nro
        $nroComprobante = 'PayPal';
        if ($pagoInscripcion && $pagoInscripcion->Id_comprobante) {
            $nroComprobante = DB::table('comprobante')
                ->where('Id_comprobante', $pagoInscripcion->Id_comprobante)
                ->value('nro_comprobante') ?? 'PayPal';
        }

        return view('Usuario_Seguridad_y_Auditoria.Estudiante.boleta', compact(
            'persona',
            'inscripcion',
            'carreraAceptada',
            'fechaAdmision',
            'grupo_sigla',
            'turno_nombre',
            'horarios',
            'nroComprobante'
        ));
    }
}
