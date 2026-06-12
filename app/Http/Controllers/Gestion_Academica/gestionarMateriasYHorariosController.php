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
        $horarios = DB::table('horario as h')
            ->leftJoin('turno as t', 't.Id_turno', '=', 'h.Id_turno')
            ->select(
                'h.Id_horario as id_horario',
                'h.dia',
                'h.hora_inicio',
                'h.hora_fin',
                'h.estado',
                'h.Id_turno as id_turno',
                't.nombre as nombre_turno'
            )
            ->orderBy('h.Id_horario', 'desc')
            ->get();

        $turnos = DB::table('turno')
            ->select('Id_turno as id_turno', 'nombre')
            ->whereRaw("LOWER(TRIM(estado)) = 'activo'")
            ->orderBy('nombre')
            ->get();

        return view('Gestion_Academica.gestionarHorarios', compact('horarios', 'turnos'));
    }

    public function storeHorario(Request $request)
    {
        $request->validate([
            'dias' => 'required|array',
            'dias.*' => 'required|string|max:20',
            'hora_inicio' => 'required',
            'hora_fin' => 'required|after:hora_inicio',
            'estado' => 'required|in:activo,inactivo',
            'Id_turno' => 'required|exists:turno,Id_turno',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->dias as $dia) {
                // Check duplicate
                $exists = DB::table('horario')
                    ->where('dia', $dia)
                    ->where('hora_inicio', $request->hora_inicio)
                    ->where('hora_fin', $request->hora_fin)
                    ->where('Id_turno', $request->Id_turno)
                    ->exists();

                if (!$exists) {
                    DB::table('horario')->insert([
                        'dia' => $dia,
                        'hora_inicio' => $request->hora_inicio,
                        'hora_fin' => $request->hora_fin,
                        'estado' => $request->estado,
                        'Id_turno' => $request->Id_turno,
                    ]);
                }
            }

            $turno = DB::table('turno')->where('Id_turno', $request->Id_turno)->value('nombre');
            $this->registrarBitacora('Registró horarios para turno: ' . $turno . ' (' . implode(', ', $request->dias) . ')');

            DB::commit();
            return redirect()->route('horarios.index')
                ->with('success', 'Horario(s) registrado(s) correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar horarios: ' . $e->getMessage()])->withInput();
        }
    }

    public function updateHorario(Request $request, $id)
    {
        $request->validate([
            'dia' => 'required|string|max:20',
            'hora_inicio' => 'required',
            'hora_fin' => 'required|after:hora_inicio',
            'estado' => 'required|in:activo,inactivo',
            'Id_turno' => 'required|exists:turno,Id_turno',
        ]);

        DB::table('horario')
            ->where('Id_horario', $id)
            ->update([
                'dia' => $request->dia,
                'hora_inicio' => $request->hora_inicio,
                'hora_fin' => $request->hora_fin,
                'estado' => $request->estado,
                'Id_turno' => $request->Id_turno,
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