<?php

namespace App\Http\Controllers\Logistica_Recursos_y_Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Logistica_Recursos_y_Reportes\gestionarEspecialidad;

class gestionarEspecialidadController extends Controller
{
    public function index()
    {
        $especialidades = DB::table('especialidad as e')
            ->leftJoin('materia as m', 'm.Id_materia', '=', 'e.id_materia')
            ->select(
                'e.Id_especialidad as id_especialidad',
                'e.nombre_especialidad',
                'e.id_materia as id_materia',
                'm.nombre as nombre_materia'
            )
            ->orderBy('e.Id_especialidad', 'desc')
            ->get();

        $materias = DB::table('materia')
            ->select('Id_materia as id_materia', 'nombre')
            ->whereRaw("LOWER(TRIM(estado)) = 'activo'")
            ->orderBy('nombre')
            ->get();

        return view('Logistica_Recursos_y_Reportes.gestionarEspecialidad', compact('especialidades', 'materias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_especialidad' => 'required|string|max:150|unique:especialidad,nombre_especialidad',
            'Id_materia' => 'required|exists:materia,Id_materia',
        ]);

        DB::beginTransaction();

        try {
            gestionarEspecialidad::create([
                'nombre_especialidad' => $request->nombre_especialidad,
                'id_materia' => $request->Id_materia,
            ]);

            $this->registrarBitacora(
                'Logistica y Recursos',
                'Registró la especialidad: ' . $request->nombre_especialidad
            );

            DB::commit();

            return redirect()->route('especialidades.index')
                ->with('success', 'Especialidad registrada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al registrar especialidad: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre_especialidad' => [
                'required',
                'string',
                'max:150',
                Rule::unique('especialidad', 'nombre_especialidad')->ignore($id, 'Id_especialidad'),
            ],
            'Id_materia' => 'required|exists:materia,Id_materia',
        ]);

        DB::table('especialidad')
            ->where('Id_especialidad', $id)
            ->update([
                'nombre_especialidad' => $request->nombre_especialidad,
                'id_materia' => $request->Id_materia,
            ]);

        $this->registrarBitacora(
            'Logistica y Recursos',
            'Actualizó la especialidad ID ' . $id . ' a: ' . $request->nombre_especialidad
        );

        return redirect()->route('especialidades.index')
            ->with('success', 'Especialidad actualizada correctamente.');
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            // Eliminar asociaciones de docentes con esta especialidad antes de borrarla
            DB::table('docente_especialidad')
                ->where('Id_especialidad', $id)
                ->delete();

            // Obtener el nombre para registrar en bitácora
            $especialidad = DB::table('especialidad')
                ->where('Id_especialidad', $id)
                ->first();

            $nombre = $especialidad ? $especialidad->nombre_especialidad : $id;

            DB::table('especialidad')
                ->where('Id_especialidad', $id)
                ->delete();

            $this->registrarBitacora(
                'Logistica y Recursos',
                'Eliminó la especialidad: ' . $nombre
            );

            DB::commit();

            return redirect()->route('especialidades.index')
                ->with('success', 'Especialidad eliminada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al eliminar especialidad: ' . $e->getMessage()
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
