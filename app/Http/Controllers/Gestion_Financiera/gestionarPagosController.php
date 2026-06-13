<?php

namespace App\Http\Controllers\Gestion_Financiera;

use App\Http\Controllers\Controller;
use App\Mail\Gestion_Financiera\ComprobantePagoMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Srmklive\PayPal\Services\PayPal;

class gestionarPagosController extends Controller
{
    public function index()
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        // Sincronizar de forma automática conceptos de pago activos con todas las inscripciones
        $pagosActivos = DB::table('pago')
            ->whereRaw("LOWER(TRIM(estado_pago)) = 'activo'")
            ->get();

        $inscripciones = DB::table('inscripcion')->select('Codigo_inscripcion')->get();

        foreach ($pagosActivos as $p) {
            foreach ($inscripciones as $ins) {
                $existe = DB::table('pago_inscripcion')
                    ->where('Codigo_inscripcion', $ins->Codigo_inscripcion)
                    ->where('Id_pago', $p->Id_pago)
                    ->exists();

                if (!$existe) {
                    DB::table('pago_inscripcion')->insert([
                        'Id_pago' => $p->Id_pago,
                        'Codigo_inscripcion' => $ins->Codigo_inscripcion,
                        'estado_pago_inscripcion' => 'Pendiente',
                        'fecha_pago' => null,
                        'Id_comprobante' => null,
                    ]);
                }
            }
        }

        // Obtener conceptos globales de pago (guardados en la tabla 'pago')
        $pagos = DB::table('pago')
            ->select(
                'Id_pago as id_pago',
                'concepto_pago',
                'monto',
                'estado_pago',
                'observaciones'
            )
            ->orderBy('Id_pago', 'desc')
            ->get();

        // Obtener inscripciones de postulantes activas
        $inscripciones = DB::table('inscripcion as i')
            ->join('postulante as po', 'po.Id_postulante', '=', 'i.Id_postulante')
            ->join('persona as p', 'p.Id_persona', '=', 'po.Id_postulante')
            ->select(
                'i.Codigo_inscripcion as codigo_inscripcion',
                'p.ci',
                'p.nombre',
                'p.apellido',
                'p.correo',
                'p.telefono',
                'p.direccion'
            )
            ->orderBy('i.Codigo_inscripcion', 'desc')
            ->get();

        // Obtener la asignación de pagos por inscripción (de la tabla intermedia 'pago_inscripcion')
        $pagosInscripcion = DB::table('pago_inscripcion as pi')
            ->join('pago as pa', 'pa.Id_pago', '=', 'pi.Id_pago')
            ->join('inscripcion as i', 'i.Codigo_inscripcion', '=', 'pi.Codigo_inscripcion')
            ->join('postulante as po', 'po.Id_postulante', '=', 'i.Id_postulante')
            ->join('persona as p', 'p.Id_persona', '=', 'po.Id_postulante')
            ->leftJoin('comprobante as c', 'c.Id_comprobante', '=', 'pi.Id_comprobante')
            ->leftJoin('inscripcion_carrera as ic', function ($join) {
                $join->on('ic.Codigo_inscripcion', '=', 'i.Codigo_inscripcion')
                     ->where('ic.prioridad', '=', 1);
            })
            ->leftJoin('carrera as ca', 'ca.Id_carrera', '=', 'ic.Id_carrera')
            ->select(
                'pi.Id_pago as id_pago',
                'pi.Codigo_inscripcion as codigo_inscripcion',
                'pi.estado_pago_inscripcion', 
                'pi.fecha_pago',
                'pi.Id_comprobante as id_comprobante',
                'pa.concepto_pago',
                'pa.monto',
                'pa.observaciones',
                'p.ci',
                'p.nombre',
                'p.apellido',
                'p.correo',
                'p.telefono',
                'p.direccion',
                'i.estado as estado_inscripcion',
                'ca.nombre_carrera as carrera_principal',
                'c.nro_comprobante',
                'c.fecha_emision',
                DB::raw('NULL as archivo')
            )
            ->orderBy('pi.Codigo_inscripcion', 'desc')
            ->get();

        $conceptos = $pagos;

        return view('Gestion_Financiera.gestionarPagos', compact(
            'conceptos',
            'pagos',
            'inscripciones',
            'pagosInscripcion'
        ));
    }

    public function generarPago(Request $request)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $request->validate([
            'concepto_pago' => 'required|string|max:100',
            'monto' => 'required|numeric|min:0.01',
            'estado_pago' => 'required|in:activo,inactivo',
            'observaciones' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $idPago = DB::table('pago')->insertGetId([
                'concepto_pago' => $request->concepto_pago,
                'monto' => $request->monto,
                'estado_pago' => $request->estado_pago,
                'observaciones' => $request->observaciones,
            ]);

            // Asignar automáticamente a todos los postulantes (inscripciones)
            $inscripciones = DB::table('inscripcion')->select('Codigo_inscripcion')->get();
            foreach ($inscripciones as $ins) {
                DB::table('pago_inscripcion')->insert([
                    'Id_pago' => $idPago,
                    'Codigo_inscripcion' => $ins->Codigo_inscripcion,
                    'estado_pago_inscripcion' => 'Pendiente',
                    'fecha_pago' => null,
                    'Id_comprobante' => null,
                ]);
            }

            $this->registrarBitacora('Generó concepto de pago: ' . $request->concepto_pago . ' y lo asignó automáticamente a todos los postulantes.');

            DB::commit();

            return redirect()->route('pagos.index')
                ->with('success', 'Concepto de pago generado y asignado automáticamente a todos los postulantes.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al generar y asignar el concepto de pago: ' . $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $request->validate([
            'concepto_pago' => 'required|string|max:100',
            'monto' => 'required|numeric|min:0.01',
            'estado_pago' => 'required|in:activo,inactivo',
            'observaciones' => 'nullable|string',
        ]);

        DB::table('pago')
            ->where('Id_pago', $id)
            ->update([
                'concepto_pago' => $request->concepto_pago,
                'monto' => $request->monto,
                'estado_pago' => $request->estado_pago,
                'observaciones' => $request->observaciones,
            ]);

        $this->registrarBitacora('Actualizó concepto de pago ID ' . $id);

        return redirect()->route('pagos.index')
            ->with('success', 'Concepto de pago actualizado correctamente.');
    }

    public function destroy($id)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        DB::beginTransaction();

        try {
            DB::table('pago')
                ->where('Id_pago', $id)
                ->delete();

            $this->registrarBitacora('Eliminó concepto de pago ID ' . $id);

            DB::commit();

            return redirect()->route('pagos.index')
                ->with('success', 'Concepto de pago eliminado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al eliminar el concepto de pago: ' . $e->getMessage()
            ]);
        }
    }



    public function guardarPagoInscripcion(Request $request)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $request->validate([
            'Id_pago' => 'required|integer', 
            'Codigo_inscripcion' => 'required|integer',
            'estado_pago_inscripcion' => 'required|in:Pendiente,Liquidado,Rechazado',
            'nro_comprobante' => 'nullable|string|max:50',
            'fecha_emision' => 'nullable|date',
        ]);

        DB::beginTransaction();

        try {
            $idPago = $request->Id_pago;
            $codigoInscripcion = $request->Codigo_inscripcion;
            $estado = $request->estado_pago_inscripcion;

            $pago = $this->buscarPagoInscripcion($idPago, $codigoInscripcion);

            if (!$pago) {
                return back()->withErrors(['error' => 'No se encontró el pago asignado.']);
            }

            $idComprobante = $pago->id_comprobante;

            if ($estado === 'Liquidado') {
                if (!$idComprobante) {
                    $nro = $request->nro_comprobante ?: 'COMP-' . now()->format('YmdHis') . '-' . $idPago;
                    $fecha = $request->fecha_emision ?: now()->toDateString();
                    $archivo = $nro . '.pdf';

                    $idComprobante = DB::table('comprobante')->insertGetId([
                        'nro_comprobante' => $nro,
                        'fecha_emision' => $fecha,
                        'archivo' => $archivo,
                    ], 'Id_comprobante');

                    Storage::disk('local')->makeDirectory('comprobantes');

                    DB::table('pago_inscripcion')
                        ->where('Id_pago', $idPago)
                        ->where('Codigo_inscripcion', $codigoInscripcion)
                        ->update(['Id_comprobante' => $idComprobante]);

                    $pagoActualizado = $this->buscarPagoInscripcion($idPago, $codigoInscripcion);

                    $pdf = Pdf::loadView('Gestion_Financiera.comprobantePdf', ['pago' => $pagoActualizado]);
                    Storage::disk('local')->put('comprobantes/' . $archivo, $pdf->output());
                    $pdfPath = storage_path('app/comprobantes/' . $archivo);

                    if (!empty($pagoActualizado->correo)) {
                        try {
                            Mail::to($pagoActualizado->correo)->send(new ComprobantePagoMail($pagoActualizado, $pdfPath));
                        } catch (\Throwable $e) {}

                        try {
                            $notificador = new \App\Http\Controllers\Gestion_Academica\enviarNotificacionesController();
                            $notificador->notificarPagoRealizado(
                                $pagoActualizado->correo,
                                $pagoActualizado->nombre . ' ' . $pagoActualizado->apellido,
                                [
                                    'concepto' => $pagoActualizado->concepto_pago,
                                    'monto' => $pagoActualizado->monto,
                                    'nro_comprobante' => $pagoActualizado->nro_comprobante ?? $nro
                                ]
                            );
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::error("Fallo al enviar notificación: " . $e->getMessage());
                        }
                    }
                }
            }

            DB::table('pago_inscripcion')
                ->where('Id_pago', $idPago)
                ->where('Codigo_inscripcion', $codigoInscripcion)
                ->update([
                    'estado_pago_inscripcion' => $estado, 
                    'fecha_pago' => $estado === 'Liquidado' ? ($request->fecha_emision ?: now()->toDateString()) : null,
                    'Id_comprobante' => $estado === 'Liquidado' ? $idComprobante : null,
                ]);

            try {
                $inscripcionController = new \App\Http\Controllers\Inscripcion_y_Documentacion\gestionarInscripcionController();
                $inscripcionController->actualizarEstadoInscripcionPorDocumentosYPago($codigoInscripcion);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Fallo actualización inscripción: " . $e->getMessage());
            }

            $this->registrarBitacora('Actualizó el pago ID ' . $idPago . ' para inscripción ' . $codigoInscripcion . ' a estado ' . $estado);

            DB::commit();

            return redirect()->route('pagos.index')->with('success', 'Pago actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al guardar el pago: ' . $e->getMessage()]);
        }
    }

    public function pagarConPaypal(int $idPago, int $codigoInscripcion)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $pago = $this->buscarPagoInscripcion($idPago, $codigoInscripcion);

        if (!$pago) return redirect()->route('pagos.index')->withErrors(['error' => 'Pago no encontrado.']);
        if (strtolower(trim($pago->estado_pago_inscripcion)) === 'liquidado') return redirect()->route('pagos.index')->withErrors(['error' => 'Pago ya liquidado.']);

        try {
            $provider = new PayPal;
            $provider->setApiCredentials(config('paypal'));
            $token = $provider->getAccessToken();
            $provider->setAccessToken($token);

            $montoUSD = number_format($pago->monto / 7, 2, '.', '');

            $response = $provider->createOrder([
                "intent" => "CAPTURE",
                "application_context" => [
                    "return_url" => route('paypal.success', [$idPago, $codigoInscripcion]),
                    "cancel_url" => route('paypal.cancel', [$idPago, $codigoInscripcion]),
                ],
                "purchase_units" => [
                    [
                        "amount" => [
                            "currency_code" => "USD",
                            "value" => $montoUSD
                        ]
                    ]
                ]
            ]);

            if (isset($response['links'])) {
                foreach ($response['links'] as $link) {
                    if ($link['rel'] === 'approve') return redirect()->away($link['href']);
                }
            }

            return back()->withErrors(['error' => 'No se pudo iniciar PayPal.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error PayPal: ' . $e->getMessage()]);
        }
    }

    public function paypalSuccess(int $idPago, int $codigoInscripcion)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        DB::beginTransaction();

        try {
            $pago = $this->buscarPagoInscripcion($idPago, $codigoInscripcion);
            if (!$pago) return redirect()->route('pagos.index')->withErrors(['error' => 'Pago no encontrado.']);
            if (strtolower(trim($pago->estado_pago_inscripcion)) === 'liquidado') return redirect()->route('pagos.index')->with('success', 'Pago ya liquidado.');

            $nroComprobante = 'COMP-' . now()->format('YmdHis') . '-' . $idPago;
            $archivo = $nroComprobante . '.pdf';

            $idComprobante = DB::table('comprobante')->insertGetId([
                'nro_comprobante' => $nroComprobante,
                'fecha_emision' => now()->toDateString(),
                'archivo' => $archivo,
            ], 'Id_comprobante');

            DB::table('pago_inscripcion')
                ->where('Id_pago', $idPago)
                ->where('Codigo_inscripcion', $codigoInscripcion)
                ->update([
                    'estado_pago_inscripcion' => 'Liquidado',
                    'fecha_pago' => now()->toDateString(),
                    'Id_comprobante' => $idComprobante,
                ]);

            $pagoActualizado = $this->buscarPagoInscripcion($idPago, $codigoInscripcion);
            Storage::disk('local')->makeDirectory('comprobantes');
            $pdf = Pdf::loadView('Gestion_Financiera.comprobantePdf', ['pago' => $pagoActualizado]);
            Storage::disk('local')->put('comprobantes/' . $archivo, $pdf->output());
            $pdfPath = storage_path('app/comprobantes/' . $archivo);

            if (!empty($pagoActualizado->correo)) {
                try {
                    Mail::to($pagoActualizado->correo)->send(new ComprobantePagoMail($pagoActualizado, $pdfPath));
                } catch (\Throwable $e) {}

                try {
                    $notificador = new \App\Http\Controllers\Gestion_Academica\enviarNotificacionesController();
                    $notificador->notificarPagoRealizado($pagoActualizado->correo, $pagoActualizado->nombre . ' ' . $pagoActualizado->apellido, ['concepto' => $pagoActualizado->concepto_pago, 'monto' => $pagoActualizado->monto, 'nro_comprobante' => $nroComprobante]);
                } catch (\Throwable $e) {}
            }

            $this->registrarBitacora('Liquidó pago ID ' . $idPago . ' para inscripción ' . $codigoInscripcion . ' con PayPal');

            try {
                $inscripcionController = new \App\Http\Controllers\Inscripcion_y_Documentacion\gestionarInscripcionController();
                $inscripcionController->actualizarEstadoInscripcionPorDocumentosYPago($codigoInscripcion);
            } catch (\Throwable $e) {}

            DB::commit();
            return redirect()->route('pagos.index')->with('success', 'Pago con PayPal completado.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('pagos.index')->withErrors(['error' => 'Error éxito PayPal: ' . $e->getMessage()]);
        }
    }

    public function paypalCancel(int $idPago, int $codigoInscripcion)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $this->registrarBitacora('Canceló pago ID ' . $idPago . ' en PayPal');
        return redirect()->route('pagos.index')->withErrors(['error' => 'El pago con PayPal fue cancelado.']);
    }

    public function emitirComprobante(int $idComprobante)
    {
        if ($redirect = $this->validarPrerrequisitos()) {
            return $redirect;
        }

        $comprobante = DB::table('comprobante')->where('Id_comprobante', $idComprobante)->first();
        if (!$comprobante || empty($comprobante->archivo)) return back()->withErrors(['error' => 'Comprobante no encontrado.']);
        $path = storage_path('app/comprobantes/' . $comprobante->archivo);
        if (!file_exists($path)) return back()->withErrors(['error' => 'Archivo no existe.']);
        return response()->file($path);
    }

    private function buscarPagoInscripcion(int $idPago, int $codigoInscripcion)
    {
        return DB::table('pago_inscripcion as pi')
            ->join('pago as pa', 'pa.Id_pago', '=', 'pi.Id_pago')
            ->join('inscripcion as i', 'i.Codigo_inscripcion', '=', 'pi.Codigo_inscripcion')
            ->join('postulante as po', 'po.Id_postulante', '=', 'i.Id_postulante')
            ->join('persona as p', 'p.Id_persona', '=', 'po.Id_postulante')
            ->leftJoin('comprobante as c', 'c.Id_comprobante', '=', 'pi.Id_comprobante')
            ->leftJoin('inscripcion_carrera as ic', function ($join) {
                $join->on('ic.Codigo_inscripcion', '=', 'i.Codigo_inscripcion')
                     ->where('ic.prioridad', '=', 1);
            })
            ->leftJoin('carrera as ca', 'ca.Id_carrera', '=', 'ic.Id_carrera')
            ->select(
                'pi.Id_pago as id_pago',
                'pi.Codigo_inscripcion as codigo_inscripcion',
                'pi.estado_pago_inscripcion',
                'pi.fecha_pago',
                'pi.Id_comprobante as id_comprobante',
                'pa.concepto_pago',
                'pa.monto',
                'pa.observaciones',
                'p.ci',
                'p.nombre',
                'p.apellido',
                'p.correo',
                'p.telefono',
                'p.direccion',
                'i.estado as estado_inscripcion',
                'ca.nombre_carrera as carrera_principal',
                'c.nro_comprobante',
                'c.fecha_emision',
                DB::raw('NULL as archivo')
            )
            ->where('pi.Id_pago', $idPago)
            ->where('pi.Codigo_inscripcion', $codigoInscripcion)
            ->first();
    }

    private function registrarBitacora(string $descripcion): void
    {
        if (!Auth::check()) return;
        DB::table('bitacora')->insert([
            'tipo' => 'Gestion Financiera',
            'descripcion' => $descripcion,
            'fecha' => now()->toDateString(),
            'hora' => now()->format('H:i:s'),
            'estado' => 'activo',
            'Id_usuario' => Auth::id(),
        ]);
    }

    private function validarPrerrequisitos()
    {
        if (DB::table('inscripcion')->count() === 0) {
            return redirect()->route('menu')->withErrors([
                'error' => 'Debe registrar al menos una inscripción antes de gestionar pagos.'
            ]);
        }
        return null;
    }
}