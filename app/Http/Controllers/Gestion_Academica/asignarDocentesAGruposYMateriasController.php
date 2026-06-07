<?php

namespace App\Http\Controllers\Gestion_Academica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class asignarDocentesAGruposYMateriasController extends Controller
{
    public function index()
    {
        $asignaciones = DB::table('grupo_materia as gm')
            ->join('grupo as g', DB::raw('"g"."Id_grupo"'), '=', DB::raw('"gm"."Id_grupo"'))
            ->join('materia as m', DB::raw('"m"."Id_materia"'), '=', DB::raw('"gm"."Id_materia"'))
            ->join('docente as d', DB::raw('"d"."Id_docente"'), '=', DB::raw('"gm"."Id_docente"'))
            ->join('persona as p', DB::raw('"p"."Id_persona"'), '=', DB::raw('"d"."Id_docente"'))
            ->select(
                DB::raw('"gm"."Id_grupo" as id_grupo'),
                DB::raw('"gm"."Id_materia" as id_materia'),
                DB::raw('"gm"."Id_docente" as id_docente'),

                'g.sigla_grupo',
                'm.nombre as nombre_materia',

                'p.ci',
                'p.nombre',
                'p.apellido'
            )
            ->orderBy('g.sigla_grupo')
            ->orderBy('m.nombre')
            ->get();

        $docentes = DB::table('docente as d')
            ->join('persona as p', DB::raw('"p"."Id_persona"'), '=', DB::raw('"d"."Id_docente"'))
            ->select(
                DB::raw('"d"."Id_docente" as id_docente'),
                'p.ci',
                'p.nombre',
                'p.apellido'
            )
            ->whereRaw("LOWER(TRIM(d.estado)) = 'activo'")
            ->orderBy('p.nombre')
            ->get();

        $grupos = DB::table('grupo')
            ->select(
                DB::raw('"Id_grupo" as id_grupo'),
                'sigla_grupo',
                'estado'
            )
            ->whereRaw("LOWER(TRIM(estado)) = 'activo'")
            ->orderBy('sigla_grupo')
            ->get();

        $materias = DB::table('materia')
            ->select(
                DB::raw('"Id_materia" as id_materia'),
                'nombre',
                'estado'
            )
            ->whereRaw("LOWER(TRIM(estado)) = 'activo'")
            ->orderBy('nombre')
            ->get();

        return view('Gestion_Academica.asignarDocentesAGruposYMaterias', compact(
            'asignaciones',
            'docentes',
            'grupos',
            'materias'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Id_grupo' => 'required|exists:grupo,Id_grupo',
            'Id_materia' => 'required|exists:materia,Id_materia',
            'Id_docente' => 'required|exists:docente,Id_docente',
        ]);

        $existe = DB::table('grupo_materia')
            ->where('Id_grupo', $request->Id_grupo)
            ->where('Id_materia', $request->Id_materia)
            ->exists();

        if ($existe) {
            return back()->withErrors([
                'error' => 'Ese grupo ya tiene una asignación para esa materia.'
            ])->withInput();
        }

        DB::table('grupo_materia')->insert([
            'Id_grupo' => $request->Id_grupo,
            'Id_materia' => $request->Id_materia,
            'Id_docente' => $request->Id_docente,
        ]);

        $this->registrarBitacora('Asignó docente a grupo y materia.');

        return redirect()->route('asignaciones-docentes.index')
            ->with('success', 'Asignación registrada correctamente.');
    }

    public function update(Request $request, $idGrupo, $idMateria)
    {
        $request->validate([
            'Id_grupo' => 'required|exists:grupo,Id_grupo',
            'Id_materia' => 'required|exists:materia,Id_materia',
            'Id_docente' => 'required|exists:docente,Id_docente',
        ]);

        $cambioClave = ((int)$request->Id_grupo !== (int)$idGrupo)
            || ((int)$request->Id_materia !== (int)$idMateria);

        if ($cambioClave) {
            $existe = DB::table('grupo_materia')
                ->where('Id_grupo', $request->Id_grupo)
                ->where('Id_materia', $request->Id_materia)
                ->exists();

            if ($existe) {
                return back()->withErrors([
                    'error' => 'Ese grupo ya tiene una asignación para esa materia.'
                ])->withInput();
            }
        }

        DB::table('grupo_materia')
            ->where('Id_grupo', $idGrupo)
            ->where('Id_materia', $idMateria)
            ->update([
                'Id_grupo' => $request->Id_grupo,
                'Id_materia' => $request->Id_materia,
                'Id_docente' => $request->Id_docente,
            ]);

        $this->registrarBitacora('Actualizó asignación de docente a grupo y materia.');

        return redirect()->route('asignaciones-docentes.index')
            ->with('success', 'Asignación actualizada correctamente.');
    }

    public function destroy($idGrupo, $idMateria)
    {
        DB::table('grupo_materia')
            ->where('Id_grupo', $idGrupo)
            ->where('Id_materia', $idMateria)
            ->delete();

        $this->registrarBitacora('Eliminó asignación de docente a grupo y materia.');

        return redirect()->route('asignaciones-docentes.index')
            ->with('success', 'Asignación eliminada correctamente.');
    }

    private function registrarBitacora(string $descripcion): void
    {
        if (!Auth::check()) {
            return;
        }

        DB::table('bitacora')->insert([
            'tipo' => 'Gestion Academica',
            'descripcion' => $descripcion,
            'fecha' => now()->toDateString(),
            'hora' => now()->format('H:i:s'),
            'estado' => 'activo',
            'Id_usuario' => Auth::id(),
        ]);
    }
}