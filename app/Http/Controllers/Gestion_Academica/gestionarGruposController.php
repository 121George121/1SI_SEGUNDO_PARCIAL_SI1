<?php

namespace App\Http\Controllers\Gestion_Academica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Gestion_Academica\gestionarGrupos;

class gestionarGruposController extends Controller
{
    public function index()
    {
        $grupos = DB::table('grupo as gr')
            ->leftJoin('aula as a', DB::raw('"a"."Id_aula"'), '=', DB::raw('"gr"."Id_aula"'))
            ->leftJoin('modalidad as m', DB::raw('"m"."Id_modalidad"'), '=', DB::raw('"gr"."Id_modalidad"'))
            ->leftJoin('turno as t', DB::raw('"t"."Id_turno"'), '=', DB::raw('"gr"."Id_turno"'))
            ->leftJoin('docente as d', DB::raw('"d"."Id_docente"'), '=', DB::raw('"gr"."Id_docente"'))
            ->leftJoin('persona as p', DB::raw('"p"."Id_persona"'), '=', DB::raw('"d"."Id_docente"'))
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

                DB::raw('"gr"."Id_docente" as id_docente'),
                'p.nombre as nombre_docente',
                'p.apellido as apellido_docente',

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
            ->where('estado', 'activo')
            ->orderBy('nro_aula')
            ->get();

        $modalidades = DB::table('modalidad')
            ->select(
                DB::raw('"Id_modalidad" as id_modalidad'),
                'nombre_modalidad',
                'estado'
            )
            ->where('estado', 'activo')
            ->orderBy('nombre_modalidad')
            ->get();

        $turnos = DB::table('turno')
            ->select(
                DB::raw('"Id_turno" as id_turno'),
                'nombre',
                'estado'
            )
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        $docentes = DB::table('docente as d')
            ->join('persona as p', DB::raw('"p"."Id_persona"'), '=', DB::raw('"d"."Id_docente"'))
            ->select(
                DB::raw('"d"."Id_docente" as id_docente'),
                'p.nombre',
                'p.apellido',
                'd.estado'
            )
            ->where('d.estado', 'activo')
            ->orderBy('p.nombre')
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

        return view('Gestion_Academica.gestionarGrupos', compact(
            'grupos',
            'aulas',
            'modalidades',
            'turnos',
            'docentes',
            'gestiones'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sigla_grupo' => 'required|string|max:50',
            'capacidad_max' => 'required|integer|min:1',
            'cant_estudiantes' => 'required|integer|min:0',
            'estado' => 'required|string|max:20',
            'Id_aula' => 'required|exists:aula,Id_aula',
            'Id_modalidad' => 'required|exists:modalidad,Id_modalidad',
            'Id_turno' => 'required|exists:turno,Id_turno',
            'Id_docente' => 'required|exists:docente,Id_docente',
            'Id_gestion' => 'required|exists:gestion,Id_gestion',
        ]);

        $aula = DB::table('aula')
            ->select('capacidad', 'nro_aula')
            ->where('Id_aula', $request->Id_aula)
            ->first();

        if (!$aula) {
            return back()->withErrors(['error' => 'El aula seleccionada no existe.']);
        }

        if ($request->capacidad_max > $aula->capacidad) {
            return back()->withErrors([
                'error' => 'La capacidad máxima del grupo no puede superar la capacidad del aula seleccionada.'
            ]);
        }

        if ($request->cant_estudiantes > $request->capacidad_max) {
            return back()->withErrors([
                'error' => 'La cantidad de estudiantes no puede superar la capacidad máxima del grupo.'
            ]);
        }

        DB::beginTransaction();

        try {
            gestionarGrupos::create([
                'sigla_grupo' => $request->sigla_grupo,
                'capacidad_max' => $request->capacidad_max,
                'estado' => $request->estado,
                'cant_estudiantes' => $request->cant_estudiantes,
                'Id_aula' => $request->Id_aula,
                'Id_modalidad' => $request->Id_modalidad,
                'Id_turno' => $request->Id_turno,
                'Id_docente' => $request->Id_docente,
                'Id_gestion' => $request->Id_gestion,
            ]);

            $this->registrarBitacora(
                'Gestion Academica',
                'Registró el grupo ' . $request->sigla_grupo . ' con aula ' . $aula->nro_aula . '.'
            );

            DB::commit();

            return redirect()->route('grupos.index')
                ->with('success', 'Grupo registrado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al registrar grupo: ' . $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'sigla_grupo' => 'required|string|max:50',
            'capacidad_max' => 'required|integer|min:1',
            'cant_estudiantes' => 'required|integer|min:0',
            'estado' => 'required|string|max:20',
            'Id_aula' => 'required|exists:aula,Id_aula',
            'Id_modalidad' => 'required|exists:modalidad,Id_modalidad',
            'Id_turno' => 'required|exists:turno,Id_turno',
            'Id_docente' => 'required|exists:docente,Id_docente',
            'Id_gestion' => 'required|exists:gestion,Id_gestion',
        ]);

        $aula = DB::table('aula')
            ->select('capacidad', 'nro_aula')
            ->where('Id_aula', $request->Id_aula)
            ->first();

        if ($request->capacidad_max > $aula->capacidad) {
            return back()->withErrors([
                'error' => 'La capacidad máxima del grupo no puede superar la capacidad del aula.'
            ]);
        }

        if ($request->cant_estudiantes > $request->capacidad_max) {
            return back()->withErrors([
                'error' => 'La cantidad de estudiantes no puede superar la capacidad máxima.'
            ]);
        }

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
                'Id_docente' => $request->Id_docente,
                'Id_gestion' => $request->Id_gestion,
            ]);

        $this->registrarBitacora(
            'Gestion Academica',
            'Actualizó el grupo ' . $request->sigla_grupo . '.'
        );

        return redirect()->route('grupos.index')
            ->with('success', 'Grupo actualizado correctamente.');
    }

    public function deshabilitar($id)
    {
        $grupo = DB::table('grupo')
            ->select('sigla_grupo')
            ->where('Id_grupo', $id)
            ->first();

        DB::table('grupo')
            ->where('Id_grupo', $id)
            ->update([
                'estado' => 'inactivo',
            ]);

        $this->registrarBitacora(
            'Gestion Academica',
            'Deshabilitó el grupo ' . ($grupo->sigla_grupo ?? $id) . '.'
        );

        return redirect()->route('grupos.index')
            ->with('success', 'Grupo deshabilitado correctamente.');
    }

    public function habilitar($id)
    {
        $grupo = DB::table('grupo')
            ->select('sigla_grupo')
            ->where('Id_grupo', $id)
            ->first();

        DB::table('grupo')
            ->where('Id_grupo', $id)
            ->update([
                'estado' => 'activo',
            ]);

        $this->registrarBitacora(
            'Gestion Academica',
            'Habilitó el grupo ' . ($grupo->sigla_grupo ?? $id) . '.'
        );

        return redirect()->route('grupos.index')
            ->with('success', 'Grupo habilitado correctamente.');
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