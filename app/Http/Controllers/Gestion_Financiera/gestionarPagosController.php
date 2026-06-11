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
        $pagos = DB::table('pago')
            ->select(
                DB::raw('"Id_pago" as id_pago'),
                'concepto_pago',
                'monto',
                'estado_pago',
                'observaciones'
            )
            ->orderBy(DB::raw('"Id_pago"'), 'desc')
            ->get();

        $inscripciones = DB::table('inscripcion as i')
            ->join('postulante as po', DB::raw('"po"."Id_postulante"'), '=', DB::raw('"i"."Id_postulante"'))
            ->join('persona as p', DB::raw('"p"."Id_persona"'), '=', DB::raw('"po"."Id_postulante"'))
            ->select(
                DB::raw('"i"."Codigo_inscripcion" as codigo_inscripcion'),
                'p.ci',
                'p.nombre',
                'p.apellido',
                'p.correo',
                'p.telefono',
                'p.direccion'
            )
            ->orderBy(DB::raw('"i"."Codigo_inscripcion"'), 'desc')
            ->get();

        $pagosInscripcion = DB::table('pago_inscripcion as pi')
            ->join('pago as pa', DB::raw('"pa"."Id_pago"'), '=', DB::raw('"pi"."Id_pago"'))
            ->join('inscripcion as i', DB::raw('"i"."Codigo_inscripcion"'), '=', DB::raw('"pi"."Codigo_inscripcion"'))
            ->join('postulante as po', DB::raw('"po"."Id_postulante"'), '=', DB::raw('"i"."Id_postulante"'))
            ->join('persona as p', DB::raw('"p"."Id_persona"'), '=', DB::raw('"po"."Id_postulante"'))
            ->leftJoin('comprobante as c', DB::raw('"c"."Id_comprobante"'), '=', DB::raw('"pi"."Id_comprobante"'))
            ->leftJoin('inscripcion_carrera as ic', function ($join) {
                $join->on(DB::raw('"ic"."Codigo_inscripcion"'), '=', DB::raw('"i"."Codigo_inscripcion"'))
                     ->where('ic.prioridad', '=', 1);
            })
            ->leftJoin('carrera as ca', DB::raw('"ca"."Id_carrera"'), '=', DB::raw('"ic"."Id_carrera"'))
            ->select(
                DB::raw('"pi"."Id_pago" as id_pago'),
                DB::raw('"pi"."Codigo_inscripcion" as codigo_inscripcion'),
                'pi.estado_pago_inscripcion',
                'pi.fecha_pago',
                'pi.metodo_pago',
                'pi.referencia_pago',
                DB::raw('"pi"."Id_comprobante" as id_comprobante'),

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
                'c.archivo'
            )
            ->orderBy(DB::raw('"pi"."Codigo_inscripcion"'), 'desc')
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
        $request->validate([
            'concepto_pago' => 'required|string|max:50',
            'monto' => 'required|numeric|min:0.01',
            'estado_pago' => 'required|in:activo,inactivo',
            'observaciones' => 'nullable|string',
        ]);

        DB::table('pago')->insert([
            'concepto_pago' => $request->concepto_pago,
            'monto' => $request->monto,
            'estado_pago' => $request->estado_pago,
            'observaciones' => $request->observaciones,
        ]);

        $this->registrarBitacora('Generó concepto de pago: ' . $request->concepto_pago);

        return redirect()->route('pagos.index')
            ->with('success', 'Concepto de pago generado correctamente.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'concepto_pago' => 'required|string|max:50',
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

    public function asignarPago(Request $request)
    {
        $request->validate([
            'Id_pago' => 'required|exists:pago,Id_pago',
            'Codigo_inscripcion' => 'nullable|exists:inscripcion,Codigo_inscripcion',
        ]);

        $idPago = $request->Id_pago;

        if ($request->has('asignar_todos') && $request->asignar_todos == '1') {
            $inscripciones = DB::table('inscripcion')->select('Codigo_inscripcion')->get();
            $creados = 0;

            foreach ($inscripciones as $ins) {
                $existe = DB::table('pago_inscripcion')
                    ->where('Id_pago', $idPago)
                    ->where('Codigo_inscripcion', $ins->Codigo_inscripcion)
                    ->exists();

                if (!$existe) {
                    DB::table('pago_inscripcion')->insert([
                        'Id_pago' => $idPago,
                        'Codigo_inscripcion' => $ins->Codigo_inscripcion,
                        'estado_pago_inscripcion' => 'Pendiente',
                        'fecha_pago' => null,
                        'Id_comprobante' => null,
                        'metodo_pago' => null,
                        'referencia_pago' => null,
                    ]);
                    $creados++;
                }
            }

            $this->registrarBitacora('Asignó pago ID ' . $idPago . ' a todos los postulantes (' . $creados . ' asignados).');

            return redirect()->route('pagos.index')
                ->with('success', 'Concepto de pago asignado correctamente a ' . $creados . ' postulantes.');
        } else {
            if (!$request->filled('Codigo_inscripcion')) {
                return back()->withErrors(['error' => 'Debe seleccionar un postulante para la asignación individual.'])->withInput();
            }

            $codigoInscripcion = $request->Codigo_inscripcion;

            $existe = DB::table('pago_inscripcion')
                ->where('Id_pago', $idPago)
                ->where('Codigo_inscripcion', $codigoInscripcion)
                ->exists();

            if ($existe) {
                return back()->withErrors([
                    'error' => 'Ese pago ya fue asignado a esa inscripción.'
                ])->withInput();
            }

            DB::table('pago_inscripcion')->insert([
                'Id_pago' => $idPago,
                'Codigo_inscripcion' => $codigoInscripcion,
                'estado_pago_inscripcion' => 'Pendiente',
                'fecha_pago' => null,
                'Id_comprobante' => null,
                'metodo_pago' => null,
                'referencia_pago' => null,
            ]);

            $this->registrarBitacora('Asignó pago a inscripción ' . $codigoInscripcion);

            return redirect()->route('pagos.index')
                ->with('success', 'Pago asignado correctamente.');
        }
    }

    public function guardarPagoInscripcion(Request $request)
    {
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
                    $nro = $request->nro_comprobante ?: 'COMP-' . now()->format('YmdHis') . '-' . $idPago . '-' . $codigoInscripcion;
                    $fecha = $request->fecha_emision ?: now()->toDateString();
                    $archivo = $nro . '.pdf';

                    $idComprobante = DB::table('comprobante')->insertGetId([
                        'nro_comprobante' => $nro,
                        'fecha_emision' => $fecha,
                        'archivo' => $archivo,
                    ], 'Id_comprobante');

                    // Generar PDF y guardar
                    Storage::disk('local')->makeDirectory('comprobantes');

                    DB::table('pago_inscripcion')
                        ->where('Id_pago', $idPago)
                        ->where('Codigo_inscripcion', $codigoInscripcion)
                        ->update([
                            'Id_comprobante' => $idComprobante,
                        ]);

                    $pagoActualizado = $this->buscarPagoInscripcion($idPago, $codigoInscripcion);

                    $pdf = Pdf::loadView('Gestion_Financiera.comprobantePdf', [
                        'pago' => $pagoActualizado,
                    ]);

                    Storage::disk('local')->put('comprobantes/' . $archivo, $pdf->output());

                    $pdfPath = storage_path('app/comprobantes/' . $archivo);

                    if (!empty($pagoActualizado->correo)) {
                        try {
                            Mail::to($pagoActualizado->correo)
                                ->send(new ComprobantePagoMail($pagoActualizado, $pdfPath));
                        } catch (\Throwable $e) {
                            // Ignorar fallas de correo
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
                    'metodo_pago' => $estado === 'Liquidado' ? 'manual' : null,
                    'referencia_pago' => $estado === 'Liquidado' ? ($request->nro_comprobante ?: 'MANUAL-' . now()->format('YmdHis')) : null,
                ]);

            $this->registrarBitacora('Actualizó el pago de la inscripción ' . $codigoInscripcion . ' a estado ' . $estado);

            DB::commit();

            return redirect()->route('pagos.index')
                ->with('success', 'Pago actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al guardar el pago: ' . $e->getMessage()
            ]);
        }
    }

    public function pagarConPaypal(int $idPago, int $codigoInscripcion)
    {
        $pago = $this->buscarPagoInscripcion($idPago, $codigoInscripcion);

        if (!$pago) {
            return redirect()->route('pagos.index')
                ->withErrors(['error' => 'No se encontró el pago asignado.']);
        }

        if (strtolower(trim($pago->estado_pago_inscripcion)) === 'liquidado') {
            return redirect()->route('pagos.index')
                ->withErrors(['error' => 'Este pago ya se encuentra liquidado.']);
        }

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
                    if ($link['rel'] === 'approve') {
                        return redirect()->away($link['href']);
                    }
                }
            }

            return back()->withErrors(['error' => 'No se pudo iniciar la pasarela de PayPal.']);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error con PayPal: ' . $e->getMessage()]);
        }
    }

    public function paypalSuccess(int $idPago, int $codigoInscripcion)
    {
        DB::beginTransaction();

        try {
            $pago = $this->buscarPagoInscripcion($idPago, $codigoInscripcion);

            if (!$pago) {
                return redirect()->route('pagos.index')
                    ->withErrors(['error' => 'No se encontró el pago asignado.']);
            }

            if (strtolower(trim($pago->estado_pago_inscripcion)) === 'liquidado') {
                return redirect()->route('pagos.index')
                    ->with('success', 'Este pago ya fue liquidado anteriormente.');
            }

            $nroComprobante = 'COMP-' . now()->format('YmdHis') . '-' . $idPago . '-' . $codigoInscripcion;
            $archivo = $nroComprobante . '.pdf';

            $idComprobante = DB::table('comprobante')->insertGetId([
                'nro_comprobante' => $nroComprobante,
                'fecha_emision' => now()->toDateString(),
                'archivo' => $archivo,
            ], 'Id_comprobante');

            $referencia = 'PAYPAL-' . now()->format('YmdHis') . '-' . rand(1000, 9999);

            DB::table('pago_inscripcion')
                ->where('Id_pago', $idPago)
                ->where('Codigo_inscripcion', $codigoInscripcion)
                ->update([
                    'estado_pago_inscripcion' => 'Liquidado',
                    'fecha_pago' => now()->toDateString(),
                    'Id_comprobante' => $idComprobante,
                    'metodo_pago' => 'paypal',
                    'referencia_pago' => $referencia,
                ]);

            $pagoActualizado = $this->buscarPagoInscripcion($idPago, $codigoInscripcion);

            Storage::disk('local')->makeDirectory('comprobantes');

            $pdf = Pdf::loadView('Gestion_Financiera.comprobantePdf', [
                'pago' => $pagoActualizado,
            ]);

            Storage::disk('local')->put('comprobantes/' . $archivo, $pdf->output());

            $pdfPath = storage_path('app/comprobantes/' . $archivo);

            if (!empty($pagoActualizado->correo)) {
                try {
                    Mail::to($pagoActualizado->correo)
                        ->send(new ComprobantePagoMail($pagoActualizado, $pdfPath));
                } catch (\Throwable $e) {
                    // Ignorar
                }
            }

            $this->registrarBitacora('Liquidó pago de inscripción ' . $codigoInscripcion . ' mediante pasarela PayPal');

            DB::commit();

            return redirect()->route('pagos.index')
                ->with('success', 'Pago con PayPal completado correctamente. Se ha enviado el comprobante a su correo.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('pagos.index')
                ->withErrors(['error' => 'Error al registrar el éxito de PayPal: ' . $e->getMessage()]);
        }
    }

    public function paypalCancel(int $idPago, int $codigoInscripcion)
    {
        $this->registrarBitacora('Canceló pago de inscripción ' . $codigoInscripcion . ' en pasarela PayPal');

        return redirect()->route('pagos.index')
            ->withErrors(['error' => 'El pago con PayPal fue cancelado por el usuario.']);
    }

    public function emitirComprobante(int $idComprobante)
    {
        $comprobante = DB::table('comprobante')
            ->where('Id_comprobante', $idComprobante)
            ->first();

        if (!$comprobante || empty($comprobante->archivo)) {
            return back()->withErrors(['error' => 'Comprobante no encontrado.']);
        }

        $path = storage_path('app/comprobantes/' . $comprobante->archivo);

        if (!file_exists($path)) {
            return back()->withErrors(['error' => 'El archivo del comprobante no existe.']);
        }

        return response()->file($path);
    }

    private function buscarPagoInscripcion(int $idPago, int $codigoInscripcion)
    {
        return DB::table('pago_inscripcion as pi')
            ->join('pago as pa', DB::raw('"pa"."Id_pago"'), '=', DB::raw('"pi"."Id_pago"'))
            ->join('inscripcion as i', DB::raw('"i"."Codigo_inscripcion"'), '=', DB::raw('"pi"."Codigo_inscripcion"'))
            ->join('postulante as po', DB::raw('"po"."Id_postulante"'), '=', DB::raw('"i"."Id_postulante"'))
            ->join('persona as p', DB::raw('"p"."Id_persona"'), '=', DB::raw('"po"."Id_postulante"'))
            ->leftJoin('comprobante as c', DB::raw('"c"."Id_comprobante"'), '=', DB::raw('"pi"."Id_comprobante"'))
            ->leftJoin('inscripcion_carrera as ic', function ($join) {
                $join->on(DB::raw('"ic"."Codigo_inscripcion"'), '=', DB::raw('"i"."Codigo_inscripcion"'))
                     ->where('ic.prioridad', '=', 1);
            })
            ->leftJoin('carrera as ca', DB::raw('"ca"."Id_carrera"'), '=', DB::raw('"ic"."Id_carrera"'))
            ->select(
                DB::raw('"pi"."Id_pago" as id_pago'),
                DB::raw('"pi"."Codigo_inscripcion" as codigo_inscripcion'),
                'pi.estado_pago_inscripcion',
                'pi.fecha_pago',
                'pi.metodo_pago',
                'pi.referencia_pago',
                DB::raw('"pi"."Id_comprobante" as id_comprobante'),

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
                'c.archivo'
            )
            ->where('pi.Id_pago', $idPago)
            ->where('pi.Codigo_inscripcion', $codigoInscripcion)
            ->first();
    }

    private function registrarBitacora(string $descripcion): void
    {
        if (!Auth::check()) {
            return;
        }

        DB::table('bitacora')->insert([
            'tipo' => 'Gestion Financiera',
            'descripcion' => $descripcion,
            'fecha' => now()->toDateString(),
            'hora' => now()->format('H:i:s'),
            'estado' => 'activo',
            'Id_usuario' => Auth::id(),
        ]);
    }
}