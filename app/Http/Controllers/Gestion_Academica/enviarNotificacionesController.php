<?php

namespace App\Http\Controllers\Gestion_Academica;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\NotificacionSistemaMail;

class enviarNotificacionesController extends Controller
{
    /**
     * Método principal para registrar e intentar enviar una notificación.
     *
     * @param string $correo
     * @param string $titulo
     * @param string $mensaje
     * @param string $tipo_notificacion
     * @param string $destinatario
     * @return bool Retorna true si se envió correctamente, false si falló.
     */
    public function enviarNotificacion($correo, $titulo, $mensaje, $tipo_notificacion, $destinatario)
    {
        $fecha = now()->toDateString();
        $hora = now()->format('H:i:s');
        
        // 1. Guardar la notificación con estado 'pendiente' en la base de datos
        $idNotificacion = DB::table('notificacion')->insertGetId([
            'tipo_notificacion' => $tipo_notificacion,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'destinatario' => $destinatario,
            'correo_destinatario' => $correo,
            'fecha_envio' => $fecha,
            'hora_envio' => $hora,
            'estado_envio' => 'pendiente',
        ], 'Id_notificacion');

        $estado_envio = 'fallido';

        // Validar formato del correo antes de enviar
        if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            try {
                // 2. Intentar enviar el correo electrónico
                Mail::to($correo)->send(new NotificacionSistemaMail($titulo, $mensaje));
                $estado_envio = 'enviado';
            } catch (\Throwable $e) {
                // Registrar error en los logs de Laravel para auditoría
                Log::error("Fallo al enviar correo a {$correo}: " . $e->getMessage());
                $estado_envio = 'fallido';
            }
        } else {
            Log::warning("Correo inválido provisto para la notificación: '{$correo}'");
            $estado_envio = 'fallido';
        }

        // 3. Actualizar estado_envio como 'enviado' o 'fallido'
        DB::table('notificacion')
            ->where('Id_notificacion', $idNotificacion)
            ->update([
                'estado_envio' => $estado_envio
            ]);

        return $estado_envio === 'enviado';
    }

    /**
     * Notificar el registro de un pago realizado.
     */
    public function notificarPagoRealizado($correo, $nombre, $datosPago)
    {
        $concepto = $datosPago['concepto'] ?? 'Pago Preuniversitario';
        $monto = $datosPago['monto'] ?? '0.00';
        $comprobante = $datosPago['nro_comprobante'] ?? 'N/A';

        $titulo = 'Confirmación de Pago Realizado - CUP FICCT';
        $mensaje = "Hola, {$nombre}.\n\nSe ha registrado de manera exitosa tu pago en nuestro sistema.\n\nDetalles del Pago:\n- Concepto: {$concepto}\n- Monto: Bs. {$monto}\n- Comprobante: {$comprobante}\n\nGracias por realizar tu pago a tiempo.";

        return $this->enviarNotificacion($correo, $titulo, $mensaje, 'pago realizado', $nombre);
    }

    /**
     * Notificar la asignación a un grupo académico.
     */
    public function notificarAsignacionGrupo($correo, $nombre, $grupo)
    {
        $sigla = $grupo['sigla'] ?? 'N/A';
        $materia = $grupo['materia'] ?? 'N/A';
        $turno = $grupo['turno'] ?? 'N/A';
        $aula = $grupo['aula'] ?? 'N/A';

        $titulo = 'Asignación de Grupo Académico - CUP FICCT';
        $mensaje = "Hola, {$nombre}.\n\nSe te ha asignado a un nuevo grupo académico para el periodo correspondiente.\n\nDetalles de la Asignación:\n- Grupo: {$sigla}\n- Materia: {$materia}\n- Turno: {$turno}\n- Aula/Ubicación: {$aula}\n\nPor favor, asiste puntualmente a tus clases.";

        return $this->enviarNotificacion($correo, $titulo, $mensaje, 'asignación a grupo', $nombre);
    }

    /**
     * Notificar cuando se registra una nota académica.
     */
    public function notificarNotaRegistrada($correo, $nombre, $nota)
    {
        $materia = $nota['materia'] ?? 'N/A';
        $evaluacion = $nota['evaluacion'] ?? 'N/A';
        $valor = $nota['valor'] ?? '0.00';

        $titulo = 'Nueva Calificación Registrada - CUP FICCT';
        $mensaje = "Hola, {$nombre}.\n\nSe ha publicado una nueva nota en el sistema académico.\n\nDetalles de la Calificación:\n- Materia: {$materia}\n- Evaluación: {$evaluacion}\n- Nota obtenida: {$valor}\n\nPuedes consultar tu historial completo en el portal de estudiante.";

        return $this->enviarNotificacion($correo, $titulo, $mensaje, 'nota registrada', $nombre);
    }

    /**
     * Notificar un cambio de horario.
     */
    public function notificarCambioHorario($correo, $nombre, $horario)
    {
        $materia = $horario['materia'] ?? 'N/A';
        $grupo = $horario['grupo'] ?? 'N/A';
        $dia = $horario['dia'] ?? 'N/A';
        $inicio = $horario['hora_inicio'] ?? 'N/A';
        $fin = $horario['hora_fin'] ?? 'N/A';

        $titulo = 'Cambio de Horario de Clase - CUP FICCT';
        $mensaje = "Hola, {$nombre}.\n\nTe informamos que se ha modificado el horario para una de tus materias.\n\nDetalles del Nuevo Horario:\n- Materia: {$materia}\n- Grupo: {$grupo}\n- Día: {$dia}\n- Horario: {$inicio} a {$fin}\n\nLamentamos los inconvenientes. Por favor, toma tus previsiones.";

        return $this->enviarNotificacion($correo, $titulo, $mensaje, 'cambio de horario', $nombre);
    }

    /**
     * Notificar la revisión de documentos.
     */
    public function notificarRevisionDocumentos($correo, $nombre, $detallesDocumentos)
    {
        $titulo = 'Revisión de Documentación - CUP FICCT';
        
        $mensaje = "Hola, {$nombre}.\n\nSe ha revisado la documentación presentada para tu inscripción.\n\nDetalles de la revisión:\n";
        foreach ($detallesDocumentos as $doc) {
            $mensaje .= "- {$doc['nombre']}: {$doc['estado']}";
            if (!empty($doc['observacion'])) {
                $mensaje .= " (Observación: {$doc['observacion']})";
            }
            $mensaje .= "\n";
        }
        
        $mensaje .= "\nPor favor, ingresa al portal si necesitas corregir algún documento observado.";

        return $this->enviarNotificacion($correo, $titulo, $mensaje, 'revision documentos', $nombre);
    }

    /**
     * Enviar una notificación general.
     */
    public function enviarNotificacionGeneral($correo, $titulo, $mensaje, $tipo)
    {
        $destinatario = explode('@', $correo)[0] ?? 'Usuario';
        return $this->enviarNotificacion($correo, $titulo, $mensaje, $tipo, $destinatario);
    }
}
