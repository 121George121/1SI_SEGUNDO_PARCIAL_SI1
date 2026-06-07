<?php

namespace App\Http\Controllers\Gestion_Academica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class gestionarGruposController extends Controller
{
    public function index()
    {
        $grupos = DB::table('grupo as gr')
            ->leftJoin('aula as a', DB::raw('"a"."Id_aula"'), '=', DB::raw('"gr"."Id_aula"'))
            ->leftJoin('modalidad as m', DB::raw('"m"."Id_modalidad"'), '=', DB::raw('"gr"."Id_modalidad"'))
            ->leftJoin('turno as t', DB::raw('"t"."Id_turno"'), '=', DB::raw('"gr"."Id_turno"'))
            ->leftJoin('gestion as g', DB::raw('"g"."Id_gestion"'), '=', DB::raw('"gr"."Id_gestion"'))
            ->select(
                DB::raw('"gr"."Id_grupo" as id_grupo'),
                'gr.sigla_grupo',
                'gr.capacidad_max',
                'gr.estado',
                'gr.cant_estudiantes',

                DB::raw('"gr"."Id_aula" as id_aula'),
                'a.nro_aula',
                'a.capacidad as capacidad_aula',
                'a.ubicacion',

                DB::raw('"gr"."Id_modalidad" as id_modalidad'),
                'm.nombre_modalidad',

                DB::raw('"gr"."Id_turno" as id_turno'),
                't.nombre as nombre_turno',

                DB::raw('"gr"."Id_gestion" as id_gestion'),
                'g.anio',
                'g.periodo'
            )
            ->orderBy(DB::raw('"gr"."Id_grupo"'), 'desc')
            ->get();

        $aulas = DB::table('aula')
            ->select(
                DB::raw('"Id_aula" as id_aula'),
                'nro_aula',
                'capacidad',
                'ubicacion',
                'estado'
            )
            ->whereRaw("LOWER(TRIM(estado)) = 'activo'")
            ->orderBy('nro_aula')
            ->get();

        $modalidades = DB::table('modalidad')
            ->select(
                DB::raw('"Id_modalidad" as id_modalidad'),
                'nombre_modalidad',
                'estado'
            )
            ->whereRaw("LOWER(TRIM(estado)) = 'activo'")
            ->orderBy('nombre_modalidad')
            ->get();

        $turnos = DB::table('turno')
            ->select(
                DB::raw('"Id_turno" as id_turno'),
                'nombre',
                'estado'
            )
            ->whereRaw("LOWER(TRIM(estado)) = 'activo'")
            ->orderBy('nombre')
            ->get();

        $gestiones = DB::table('gestion')
            ->select(
                DB::raw('"Id_gestion" as id_gestion'),
                'anio',
                'periodo',
                'estado'
            )
            ->whereRaw("LOWER(TRIM(estado)) = 'activo'")
            ->orderBy('anio', 'desc')
            ->orderBy('periodo')
            ->get();

        return view('Gestion_Academica.gestionarGrupos', compact(
            'grupos',
            'aulas',
            'modalidades',
            'turnos',
            'gestiones'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sigla_grupo' => 'required|string|max:50',
            'capacidad_max' => 'required|integer|min:1',
            'cant_estudiantes' => 'required|integer|min:0',
            'estado' => 'required|in:activo,inactivo',
            'Id_aula' => 'required|exists:aula,Id_aula',
            'Id_modalidad' => 'required|exists:modalidad,Id_modalidad',
            'Id_turno' => 'required|exists:turno,Id_turno',
            'Id_gestion' => 'required|exists:gestion,Id_gestion',
        ]);

        $aula = DB::table('aula')
            ->select('capacidad', 'nro_aula')
            ->where('Id_aula', $request->Id_aula)
            ->first();

        if ($aula && $aula->capacidad !== null && $request->capacidad_max > $aula->capacidad) {
            return back()->withErrors([
                'error' => 'La capacidad máxima del grupo no puede superar la capacidad del aula.'
            ])->withInput();
        }

        if ($request->cant_estudiantes > $request->capacidad_max) {
            return back()->withErrors([
                'error' => 'La cantidad de estudiantes no puede superar la capacidad máxima del grupo.'
            ])->withInput();
        }

        DB::beginTransaction();

        try {
            DB::table('grupo')->insert([
                'sigla_grupo' => $request->sigla_grupo,
                'capacidad_max' => $request->capacidad_max,
                'estado' => $request->estado,
                'cant_estudiantes' => $request->cant_estudiantes,
                'Id_aula' => $request->Id_aula,
                'Id_modalidad' => $request->Id_modalidad,
                'Id_turno' => $request->Id_turno,
                'Id_gestion' => $request->Id_gestion,
            ]);

            $this->registrarBitacora(
                'Gestion Academica',
                'Registró el grupo ' . $request->sigla_grupo . '.'
            );

            DB::commit();

            return redirect()->route('grupos.index')
                ->with('success', 'Grupo registrado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al registrar grupo: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'sigla_grupo' => 'required|string|max:50',
            'capacidad_max' => 'required|integer|min:1',
            'cant_estudiantes' => 'required|integer|min:0',
            'estado' => 'required|in:activo,inactivo',
            'Id_aula' => 'required|exists:aula,Id_aula',
            'Id_modalidad' => 'required|exists:modalidad,Id_modalidad',
            'Id_turno' => 'required|exists:turno,Id_turno',
            'Id_gestion' => 'required|exists:gestion,Id_gestion',
        ]);

        $aula = DB::table('aula')
            ->select('capacidad', 'nro_aula')
            ->where('Id_aula', $request->Id_aula)
            ->first();

        if ($aula && $aula->capacidad !== null && $request->capacidad_max > $aula->capacidad) {
            return back()->withErrors([
                'error' => 'La capacidad máxima del grupo no puede superar la capacidad del aula.'
            ])->withInput();
        }

        if ($request->cant_estudiantes > $request->capacidad_max) {
            return back()->withErrors([
                'error' => 'La cantidad de estudiantes no puede superar la capacidad máxima del grupo.'
            ])->withInput();
        }

        DB::beginTransaction();

        try {
            DB::table('grupo')
                ->where('Id_grupo', $id)
                ->update([
                    'sigla_grupo' => $request->sigla_grupo,
                    'capacidad_max' => $request->capacidad_max,
                    'estado' => $request->estado,
                    'cant_estudiantes' => $request->cant_estudiantes,
                    'Id_aula' => $request->Id_aula,
                    'Id_modalidad' => $request->Id_modalidad,
                    'Id_turno' => $request->Id_turno,
                    'Id_gestion' => $request->Id_gestion,
                ]);

            $this->registrarBitacora(
                'Gestion Academica',
                'Actualizó el grupo ' . $request->sigla_grupo . '.'
            );

            DB::commit();

            return redirect()->route('grupos.index')
                ->with('success', 'Grupo actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al actualizar grupo: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            DB::table('grupo')
                ->where('Id_grupo', $id)
                ->delete();

            $this->registrarBitacora(
                'Gestion Academica',
                'Eliminó el grupo ID ' . $id . '.'
            );

            DB::commit();

            return redirect()->route('grupos.index')
                ->with('success', 'Grupo eliminado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al eliminar grupo: ' . $e->getMessage()
            ]);
        }
    }

    private function registrarBitacora(string $tipo, string $descripcion): void
    {
        if (!Auth::check()) {
            return;
        }

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