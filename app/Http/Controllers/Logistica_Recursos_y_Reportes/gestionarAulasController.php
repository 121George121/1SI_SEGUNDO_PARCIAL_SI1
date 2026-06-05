<?php

namespace App\Http\Controllers\Logistica_Recursos_y_Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Logistica_Recursos_y_Reportes\gestionarAulas;

class gestionarAulasController extends Controller
{
    public function index()
    {
        $aulas = DB::table('aula')
            ->select(
                DB::raw('"Id_aula" as id_aula'),
                'nro_aula',
                'capacidad',
                'ubicacion',
                'estado'
            )
            ->orderBy(DB::raw('"Id_aula"'), 'desc')
            ->get();

        return view('Logistica_Recursos_y_Reportes.gestionarAulas', compact('aulas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nro_aula' => 'required|string|max:50|unique:aula,nro_aula',
            'capacidad' => 'required|integer|min:1',
            'ubicacion' => 'nullable|string|max:100',
            'estado' => 'required|string|max:20',
        ]);

        DB::beginTransaction();

        try {
            gestionarAulas::create([
                'nro_aula' => $request->nro_aula,
                'capacidad' => $request->capacidad,
                'ubicacion' => $request->ubicacion,
                'estado' => $request->estado,
            ]);

            $this->registrarBitacora(
                'Logistica y Recursos',
                'Registró el aula ' . $request->nro_aula . ' con capacidad de ' . $request->capacidad . ' estudiantes.'
            );

            DB::commit();

            return redirect()->route('aulas.index')
                ->with('success', 'Aula registrada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al registrar aula: ' . $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nro_aula' => [
                'required',
                'string',
                'max:50',
                Rule::unique('aula', 'nro_aula')->ignore($id, 'Id_aula'),
            ],
            'capacidad' => 'required|integer|min:1',
            'ubicacion' => 'nullable|string|max:100',
            'estado' => 'required|string|max:20',
        ]);

        DB::table('aula')
            ->where('Id_aula', $id)
            ->update([
                'nro_aula' => $request->nro_aula,
                'capacidad' => $request->capacidad,
                'ubicacion' => $request->ubicacion,
                'estado' => $request->estado,
            ]);

        $this->registrarBitacora(
            'Logistica y Recursos',
            'Actualizó el aula ' . $request->nro_aula . '.'
        );

        return redirect()->route('aulas.index')
            ->with('success', 'Aula actualizada correctamente.');
    }

    public function actualizarCapacidad(Request $request, $id)
    {
        $request->validate([
            'capacidad' => 'required|integer|min:1',
        ]);

        $aula = DB::table('aula')
            ->select('nro_aula')
            ->where('Id_aula', $id)
            ->first();

        DB::table('aula')
            ->where('Id_aula', $id)
            ->update([
                'capacidad' => $request->capacidad,
            ]);

        $this->registrarBitacora(
            'Logistica y Recursos',
            'Asignó/actualizó capacidad del aula ' . ($aula->nro_aula ?? $id) . ' a ' . $request->capacidad . '.'
        );

        return redirect()->route('aulas.index')
            ->with('success', 'Capacidad actualizada correctamente.');
    }

    public function deshabilitar($id)
    {
        $aula = DB::table('aula')
            ->select('nro_aula')
            ->where('Id_aula', $id)
            ->first();

        DB::table('aula')
            ->where('Id_aula', $id)
            ->update([
                'estado' => 'inactivo',
            ]);

        $this->registrarBitacora(
            'Logistica y Recursos',
            'Deshabilitó el aula ' . ($aula->nro_aula ?? $id) . '.'
        );

        return redirect()->route('aulas.index')
            ->with('success', 'Aula deshabilitada correctamente.');
    }

    public function habilitar($id)
    {
        $aula = DB::table('aula')
            ->select('nro_aula')
            ->where('Id_aula', $id)
            ->first();

        DB::table('aula')
            ->where('Id_aula', $id)
            ->update([
                'estado' => 'activo',
            ]);

        $this->registrarBitacora(
            'Logistica y Recursos',
            'Habilitó el aula ' . ($aula->nro_aula ?? $id) . '.'
        );

        return redirect()->route('aulas.index')
            ->with('success', 'Aula habilitada correctamente.');
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