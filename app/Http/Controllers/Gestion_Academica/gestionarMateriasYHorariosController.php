<?php

namespace App\Http\Controllers\Gestion_Academica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class gestionarMateriasYHorariosController extends Controller
{
    public function indexMaterias()
    {
        $materias = DB::table('materia')
            ->select(
                DB::raw('"Id_materia" as id_materia'),
                'nombre',
                'descripcion',
                'estado'
            )
            ->orderBy(DB::raw('"Id_materia"'), 'desc')
            ->get();

        return view('Gestion_Academica.gestionarMaterias', compact('materias'));
    }

    public function storeMateria(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:activo,inactivo',
        ]);

        DB::table('materia')->insert([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'estado' => $request->estado,
        ]);

        $this->registrarBitacora('Registró materia: '.$request->nombre);

        return redirect()->route('materias.index')
            ->with('success', 'Materia registrada correctamente.');
    }

    public function updateMateria(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:activo,inactivo',
        ]);

        DB::table('materia')
            ->where('Id_materia', $id)
            ->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'estado' => $request->estado,
            ]);

        $this->registrarBitacora('Actualizó materia ID '.$id);

        return redirect()->route('materias.index')
            ->with('success', 'Materia actualizada correctamente.');
    }

    public function destroyMateria($id)
    {
        DB::table('materia')
            ->where('Id_materia', $id)
            ->delete();

        $this->registrarBitacora('Eliminó materia ID '.$id);

        return redirect()->route('materias.index')
            ->with('success', 'Materia eliminada correctamente.');
    }

    public function indexHorarios()
    {
        $horarios = DB::table('horario')
            ->select(
                DB::raw('"Id_horario" as id_horario'),
                'dia',
                'hora_inicio',
                'hora_fin',
                'estado'
            )
            ->orderBy(DB::raw('"Id_horario"'), 'desc')
            ->get();

        return view('Gestion_Academica.gestionarHorarios', compact('horarios'));
    }

    public function storeHorario(Request $request)
    {
        $request->validate([
            'dia' => 'required|string|max:20',
            'hora_inicio' => 'required',
            'hora_fin' => 'required|after:hora_inicio',
            'estado' => 'required|in:activo,inactivo',
        ]);

        DB::table('horario')->insert([
            'dia' => $request->dia,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'estado' => $request->estado,
        ]);

        $this->registrarBitacora('Registró horario: '.$request->dia);

        return redirect()->route('horarios.index')
            ->with('success', 'Horario registrado correctamente.');
    }

    public function updateHorario(Request $request, $id)
    {
        $request->validate([
            'dia' => 'required|string|max:20',
            'hora_inicio' => 'required',
            'hora_fin' => 'required|after:hora_inicio',
            'estado' => 'required|in:activo,inactivo',
        ]);

        DB::table('horario')
            ->where('Id_horario', $id)
            ->update([
                'dia' => $request->dia,
                'hora_inicio' => $request->hora_inicio,
                'hora_fin' => $request->hora_fin,
                'estado' => $request->estado,
            ]);

        $this->registrarBitacora('Actualizó horario ID '.$id);

        return redirect()->route('horarios.index')
            ->with('success', 'Horario actualizado correctamente.');
    }

    public function destroyHorario($id)
    {
        DB::table('horario')
            ->where('Id_horario', $id)
            ->delete();

        $this->registrarBitacora('Eliminó horario ID '.$id);

        return redirect()->route('horarios.index')
            ->with('success', 'Horario eliminado correctamente.');
    }

    private function registrarBitacora($descripcion)
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