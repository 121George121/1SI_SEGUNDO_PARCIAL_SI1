<?php

namespace App\Http\Controllers\Gestion_Academica;

use App\Http\Controllers\Controller;
use App\Models\Gestion_Academica\gestionarModalidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class gestionarModalidadController extends Controller
{
    public function index()
    {
        $modalidades = gestionarModalidad::orderBy('nombre_modalidad', 'asc')->get();

        return view('Gestion_Academica.gestionarModalidad', compact('modalidades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_modalidad' => 'required|string|max:100',
            'estado' => 'required|in:activo,inactivo',
        ]);

        DB::beginTransaction();

        try {
            gestionarModalidad::create([
                'nombre_modalidad' => $request->nombre_modalidad,
                'estado' => $request->estado,
            ]);

            $this->registrarBitacora('Gestion Academica', 'Registró la modalidad: ' . $request->nombre_modalidad);

            DB::commit();

            return redirect()->route('modalidades.index')
                ->with('success', 'Modalidad registrada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al registrar modalidad: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre_modalidad' => 'required|string|max:100',
            'estado' => 'required|in:activo,inactivo',
        ]);

        DB::beginTransaction();

        try {
            $modalidad = gestionarModalidad::findOrFail($id);
            $modalidad->update([
                'nombre_modalidad' => $request->nombre_modalidad,
                'estado' => $request->estado,
            ]);

            $this->registrarBitacora('Gestion Academica', 'Actualizó la modalidad ID ' . $id . ' a: ' . $request->nombre_modalidad);

            DB::commit();

            return redirect()->route('modalidades.index')
                ->with('success', 'Modalidad actualizada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al actualizar modalidad: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $modalidad = gestionarModalidad::findOrFail($id);
            $modalidad->delete();

            $this->registrarBitacora('Gestion Academica', 'Eliminó la modalidad ID ' . $id);

            DB::commit();

            return redirect()->route('modalidades.index')
                ->with('success', 'Modalidad eliminada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'No se puede eliminar esta modalidad porque está siendo utilizada por otros registros (ej: grupos).'
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
