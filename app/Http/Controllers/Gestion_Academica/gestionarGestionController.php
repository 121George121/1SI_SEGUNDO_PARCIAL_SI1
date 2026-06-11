<?php

namespace App\Http\Controllers\Gestion_Academica;

use App\Http\Controllers\Controller;
use App\Models\Gestion_Academica\gestionarGestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class gestionarGestionController extends Controller
{
    public function index()
    {
        $gestiones = gestionarGestion::orderBy('anio', 'desc')
            ->orderBy('periodo', 'asc')
            ->get();

        return view('Gestion_Academica.gestionarGestion', compact('gestiones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'anio' => 'required|integer|between:2000,2100',
            'periodo' => 'required|string|max:50',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'estado' => 'required|in:activo,inactivo',
        ]);

        DB::beginTransaction();

        try {
            gestionarGestion::create([
                'anio' => $request->anio,
                'periodo' => $request->periodo,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'estado' => $request->estado,
            ]);

            $this->registrarBitacora('Gestion Academica', 'Registró la gestión: ' . $request->anio . ' - ' . $request->periodo);

            DB::commit();

            return redirect()->route('gestiones.index')
                ->with('success', 'Gestión registrada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al registrar gestión: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'anio' => 'required|integer|between:2000,2100',
            'periodo' => 'required|string|max:50',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'estado' => 'required|in:activo,inactivo',
        ]);

        DB::beginTransaction();

        try {
            $gestion = gestionarGestion::findOrFail($id);
            $gestion->update([
                'anio' => $request->anio,
                'periodo' => $request->periodo,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'estado' => $request->estado,
            ]);

            $this->registrarBitacora('Gestion Academica', 'Actualizó la gestión ID ' . $id . ' a: ' . $request->anio . ' - ' . $request->periodo);

            DB::commit();

            return redirect()->route('gestiones.index')
                ->with('success', 'Gestión actualizada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al actualizar gestión: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $gestion = gestionarGestion::findOrFail($id);
            $gestion->delete();

            $this->registrarBitacora('Gestion Academica', 'Eliminó la gestión ID ' . $id);

            DB::commit();

            return redirect()->route('gestiones.index')
                ->with('success', 'Gestión eliminada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'No se puede eliminar esta gestión porque está siendo utilizada por otros registros (ej: grupos o asignación de cupos).'
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
