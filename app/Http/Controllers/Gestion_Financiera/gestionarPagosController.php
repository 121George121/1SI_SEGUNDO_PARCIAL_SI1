<?php

namespace App\Http\Controllers\Gestion_Financiera;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Gestion_Financiera\gestionarPagos;

class gestionarPagosController extends Controller
{
    public function index()
    {
        $conceptos = DB::table('pago')
            ->select(
                DB::raw('"Id_pago" as id_pago'),
                'concepto_pago',
                'monto',
                'estado_pago',
                'observaciones'
            )
            ->orderBy(DB::raw('"Id_pago"'), 'desc')
            ->get();

        $pagosInscripcion = DB::table('inscripcion as i')
            ->join('postulante as po', DB::raw('"po"."Id_postulante"'), '=', DB::raw('"i"."Id_postulante"'))
            ->join('persona as p', DB::raw('"p"."Id_persona"'), '=', DB::raw('"po"."Id_postulante"'))

            ->leftJoin('inscripcion_carrera as ic1', function ($join) {
                $join->on(DB::raw('"ic1"."Codigo_inscripcion"'), '=', DB::raw('"i"."Codigo_inscripcion"'))
                    ->where('ic1.prioridad', '=', 1);
            })
            ->leftJoin('carrera as c1', DB::raw('"c1"."Id_carrera"'), '=', DB::raw('"ic1"."Id_carrera"'))

            ->crossJoin('pago as pg')

            ->leftJoin('pago_inscripcion as pi', function ($join) {
                $join->on(DB::raw('"pi"."Codigo_inscripcion"'), '=', DB::raw('"i"."Codigo_inscripcion"'))
                    ->on(DB::raw('"pi"."Id_pago"'), '=', DB::raw('"pg"."Id_pago"'));
            })

            ->leftJoin('comprobante as co', DB::raw('"co"."Id_comprobante"'), '=', DB::raw('"pi"."Id_comprobante"'))

            ->select(
                DB::raw('"i"."Codigo_inscripcion" as codigo_inscripcion'),
                DB::raw('"i"."estado" as estado_inscripcion'),
                'i.fecha_inscripcion',

                DB::raw('"p"."Id_persona" as id_persona'),
                'p.ci',
                'p.nombre',
                'p.apellido',
                'p.correo',

                DB::raw('"c1"."nombre_carrera" as carrera_principal'),

                DB::raw('"pg"."Id_pago" as id_pago'),
                'pg.concepto_pago',
                'pg.monto',
                'pg.estado_pago as estado_concepto_pago',
                'pg.observaciones',

                DB::raw('COALESCE(pi.estado_pago_inscripcion, \'Pendiente\') as estado_pago_inscripcion'),
                'pi.fecha_pago',

                DB::raw('"co"."Id_comprobante" as id_comprobante'),
                'co.nro_comprobante',
                'co.fecha_emision'
            )
            ->whereRaw("LOWER(TRIM(pg.estado_pago)) = 'activo'")
            ->orderBy(DB::raw('"i"."Codigo_inscripcion"'), 'desc')
            ->orderBy(DB::raw('"pg"."Id_pago"'), 'asc')
            ->get();

        return view('Gestion_Financiera.gestionarPagos', compact(
            'conceptos',
            'pagosInscripcion'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'concepto_pago' => 'required|string|max:50',
            'monto' => 'required|numeric|min:0',
            'estado_pago' => 'required|in:activo,inactivo',
            'observaciones' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            gestionarPagos::create([
                'concepto_pago' => $request->concepto_pago,
                'monto' => $request->monto,
                'estado_pago' => $request->estado_pago,
                'observaciones' => $request->observaciones,
            ]);

            $this->registrarBitacora(
                'Gestion Financiera',
                'Registró el concepto de pago: ' . $request->concepto_pago . '.'
            );

            DB::commit();

            return redirect()->route('pagos.index')
                ->with('success', 'Concepto de pago registrado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al registrar concepto de pago: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'concepto_pago' => 'required|string|max:50',
            'monto' => 'required|numeric|min:0',
            'estado_pago' => 'required|in:activo,inactivo',
            'observaciones' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            DB::table('pago')
                ->where('Id_pago', $id)
                ->update([
                    'concepto_pago' => $request->concepto_pago,
                    'monto' => $request->monto,
                    'estado_pago' => $request->estado_pago,
                    'observaciones' => $request->observaciones,
                ]);

            $this->registrarBitacora(
                'Gestion Financiera',
                'Actualizó el concepto de pago ID ' . $id . '.'
            );

            DB::commit();

            return redirect()->route('pagos.index')
                ->with('success', 'Concepto de pago actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al actualizar concepto de pago: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $concepto = DB::table('pago')
                ->where('Id_pago', $id)
                ->first();

            if (!$concepto) {
                return redirect()->route('pagos.index')
                    ->withErrors(['error' => 'El concepto de pago no existe.']);
            }

            DB::table('pago')
                ->where('Id_pago', $id)
                ->delete();

            $this->registrarBitacora(
                'Gestion Financiera',
                'Eliminó el concepto de pago: ' . $concepto->concepto_pago . '.'
            );

            DB::commit();

            return redirect()->route('pagos.index')
                ->with('success', 'Concepto de pago eliminado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al eliminar concepto de pago: ' . $e->getMessage()
            ]);
        }
    }

    public function guardarPagoInscripcion(Request $request)
    {
        $request->validate([
            'Id_pago' => 'required|exists:pago,Id_pago',
            'Codigo_inscripcion' => 'required|exists:inscripcion,Codigo_inscripcion',
            'estado_pago_inscripcion' => 'required|in:Pendiente,Liquidado,Rechazado',
            'nro_comprobante' => 'nullable|string|max:50',
            'fecha_emision' => 'nullable|date',
        ]);

        DB::beginTransaction();

        try {
            $idComprobante = null;

            if ($request->estado_pago_inscripcion === 'Liquidado') {
                if (!$request->filled('nro_comprobante')) {
                    DB::rollBack();

                    return back()->withErrors([
                        'error' => 'Para liquidar el pago debe ingresar un número de comprobante.'
                    ])->withInput();
                }

                $idComprobante = DB::table('comprobante')->insertGetId([
                    'nro_comprobante' => $request->nro_comprobante,
                    'fecha_emision' => $request->fecha_emision ?? now()->toDateString(),
                ], 'Id_comprobante');
            }

            $existe = DB::table('pago_inscripcion')
                ->where('Id_pago', $request->Id_pago)
                ->where('Codigo_inscripcion', $request->Codigo_inscripcion)
                ->exists();

            if ($existe) {
                $datosUpdate = [
                    'estado_pago_inscripcion' => $request->estado_pago_inscripcion,
                    'fecha_pago' => $request->estado_pago_inscripcion === 'Liquidado'
                        ? now()->toDateString()
                        : null,
                ];

                if ($idComprobante) {
                    $datosUpdate['Id_comprobante'] = $idComprobante;
                }

                if ($request->estado_pago_inscripcion !== 'Liquidado') {
                    $datosUpdate['Id_comprobante'] = null;
                }

                DB::table('pago_inscripcion')
                    ->where('Id_pago', $request->Id_pago)
                    ->where('Codigo_inscripcion', $request->Codigo_inscripcion)
                    ->update($datosUpdate);
            } else {
                DB::table('pago_inscripcion')->insert([
                    'Id_pago' => $request->Id_pago,
                    'Codigo_inscripcion' => $request->Codigo_inscripcion,
                    'estado_pago_inscripcion' => $request->estado_pago_inscripcion,
                    'fecha_pago' => $request->estado_pago_inscripcion === 'Liquidado'
                        ? now()->toDateString()
                        : null,
                    'Id_comprobante' => $idComprobante,
                ]);
            }

            $this->actualizarEstadoInscripcionPorDocumentosYPago($request->Codigo_inscripcion);

            $this->registrarBitacora(
                'Gestion Financiera',
                'Procesó el pago ID ' . $request->Id_pago . ' de la inscripción ' . $request->Codigo_inscripcion . '.'
            );

            DB::commit();

            return redirect()->route('pagos.index')
                ->with('success', 'Pago de inscripción actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al procesar pago: ' . $e->getMessage()
            ])->withInput();
        }
    }

    private function actualizarEstadoInscripcionPorDocumentosYPago($codigoInscripcion)
    {
        $inscripcion = DB::table('inscripcion')
            ->where('Codigo_inscripcion', $codigoInscripcion)
            ->first();

        if (!$inscripcion) {
            return;
        }

        $idPostulante = $inscripcion->Id_postulante;

        $totalDocumentos = DB::table('documento')
            ->whereRaw("LOWER(TRIM(destinado_a)) = 'postulantes'")
            ->count();

        $documentosAprobados = DB::table('persona_documento as pd')
            ->join('documento as doc', DB::raw('"doc"."Id_documento"'), '=', DB::raw('"pd"."Id_documento"'))
            ->where('pd.Id_persona', $idPostulante)
            ->whereRaw("LOWER(TRIM(doc.destinado_a)) = 'postulantes'")
            ->where('pd.estado', 'Aprobado')
            ->count();

        $totalPagosActivos = DB::table('pago')
            ->whereRaw("LOWER(TRIM(estado_pago)) = 'activo'")
            ->count();

        $pagosLiquidados = DB::table('pago_inscripcion as pi')
            ->join('pago as pg', DB::raw('"pg"."Id_pago"'), '=', DB::raw('"pi"."Id_pago"'))
            ->where('pi.Codigo_inscripcion', $codigoInscripcion)
            ->whereRaw("LOWER(TRIM(pg.estado_pago)) = 'activo'")
            ->where('pi.estado_pago_inscripcion', 'Liquidado')
            ->count();

        if (
            $totalDocumentos > 0 &&
            $documentosAprobados == $totalDocumentos &&
            $totalPagosActivos > 0 &&
            $pagosLiquidados == $totalPagosActivos
        ) {
            DB::table('inscripcion')
                ->where('Codigo_inscripcion', $codigoInscripcion)
                ->update([
                    'estado' => 'Inscrito',
                ]);

            DB::table('postulante')
                ->where('Id_postulante', $idPostulante)
                ->update([
                    'estado_inscripcion' => 'Inscrito',
                ]);
        } else {
            DB::table('inscripcion')
                ->where('Codigo_inscripcion', $codigoInscripcion)
                ->update([
                    'estado' => 'En_Revision',
                ]);

            DB::table('postulante')
                ->where('Id_postulante', $idPostulante)
                ->update([
                    'estado_inscripcion' => 'En_Revision',
                ]);
        }
    }

    private function registrarBitacora($tipo, $descripcion)
    {
        if (Auth::check()) {
            DB::table('bitacora')->insert([
                'tipo' => $tipo,
                'descripcion' => $descripcion,
                'fecha' => now()->toDateString(),
                'hora' => now()->format('H:i:s'),
                'estado' => 'activo',
                'Id_usuario' => Auth::id(),
            ]);
        }
    }
}