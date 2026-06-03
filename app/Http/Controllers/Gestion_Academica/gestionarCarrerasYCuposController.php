<?php

namespace App\Http\Controllers\Gestion_Academica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Gestion_Academica\gestionarCarrerasYCupos;

class gestionarCarrerasYCuposController extends Controller
{
    public function index()
    {
        $carreras = DB::table('carrera as c')
            ->leftJoin('cupocarrera as cc', DB::raw('"cc"."Id_carrera"'), '=', DB::raw('"c"."Id_carrera"'))
            ->leftJoin('gestion as g', DB::raw('"g"."Id_gestion"'), '=', DB::raw('"cc"."Id_gestion"'))
            ->select(
                'c.Id_carrera as id_carrera',
                'c.nombre_carrera',
                'c.descripcion',
                'c.estado',
                DB::raw('"cc"."Id_cupo" as id_cupo'),
                'cc.cantidad_cupos',
                DB::raw('"g"."Id_gestion" as id_gestion'),
                'g.anio',
                'g.periodo',
                'g.estado as estado_gestion'
            )
            ->orderBy('c.Id_carrera', 'desc')
            ->get();

        $gestiones = DB::table('gestion')
            ->select(
                DB::raw('"Id_gestion" as id_gestion'),
                'anio',
                'periodo',
                'estado'
            )
            ->where('estado', 'activo')
            ->orderBy('anio', 'desc')
            ->get();

        return view('Gestion_Academica.gestionarCarrerasYCupos', compact('carreras', 'gestiones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_carrera' => 'required|string|max:150|unique:carrera,nombre_carrera',
            'descripcion' => 'nullable|string',
            'estado' => 'required|string|max:20',
            'id_gestion' => 'required|exists:gestion,Id_gestion',
            'cantidad_cupos' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            $carrera = gestionarCarrerasYCupos::create([
                'nombre_carrera' => $request->nombre_carrera,
                'descripcion' => $request->descripcion,
                'estado' => $request->estado,
            ]);

            DB::table('cupocarrera')->insert([
                'cantidad_cupos' => $request->cantidad_cupos,
                'Id_gestion' => $request->id_gestion,
                'Id_carrera' => $carrera->Id_carrera,
            ]);

            $this->registrarBitacora(
                'Gestion Academica',
                'Registró la carrera ' . $carrera->nombre_carrera . ' con ' . $request->cantidad_cupos . ' cupos.'
            );

            DB::commit();

            return redirect()->route('carreras-cupos.index')
                ->with('success', 'Carrera registrada y cupos asignados correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al guardar: ' . $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre_carrera' => 'required|string|max:150|unique:carrera,nombre_carrera,' . $id . ',Id_carrera',
            'descripcion' => 'nullable|string',
            'estado' => 'required|string|max:20',
        ]);

        $carrera = gestionarCarrerasYCupos::findOrFail($id);

        $carrera->update([
            'nombre_carrera' => $request->nombre_carrera,
            'descripcion' => $request->descripcion,
            'estado' => $request->estado,
        ]);

        $this->registrarBitacora(
            'Gestion Academica',
            'Actualizó la carrera ' . $carrera->nombre_carrera . '.'
        );

        return redirect()->route('carreras-cupos.index')
            ->with('success', 'Carrera actualizada correctamente.');
    }

    public function actualizarCupos(Request $request, $id)
    {
        $request->validate([
            'id_gestion' => 'required|exists:gestion,Id_gestion',
            'cantidad_cupos' => 'required|integer|min:0',
        ]);

        $carrera = gestionarCarrerasYCupos::findOrFail($id);

        DB::table('cupocarrera')->updateOrInsert(
            [
                'Id_carrera' => $id,
                'Id_gestion' => $request->id_gestion,
            ],
            [
                'cantidad_cupos' => $request->cantidad_cupos,
            ]
        );

        $this->registrarBitacora(
            'Gestion Academica',
            'Actualizó los cupos de ' . $carrera->nombre_carrera . ' a ' . $request->cantidad_cupos . '.'
        );

        return redirect()->route('carreras-cupos.index')
            ->with('success', 'Cupos actualizados correctamente.');
    }

    public function deshabilitar($id)
    {
        $carrera = gestionarCarrerasYCupos::findOrFail($id);

        $carrera->update([
            'estado' => 'inactivo',
        ]);

        $this->registrarBitacora(
            'Gestion Academica',
            'Deshabilitó la carrera ' . $carrera->nombre_carrera . '.'
        );

        return redirect()->route('carreras-cupos.index')
            ->with('success', 'Carrera deshabilitada correctamente.');
    }

    public function habilitar($id)
    {
        $carrera = gestionarCarrerasYCupos::findOrFail($id);

        $carrera->update([
            'estado' => 'activo',
        ]);

        $this->registrarBitacora(
            'Gestion Academica',
            'Habilitó la carrera ' . $carrera->nombre_carrera . '.'
        );

        return redirect()->route('carreras-cupos.index')
            ->with('success', 'Carrera habilitada correctamente.');
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