<?php

namespace App\Http\Controllers\Gestion_Academica;

use App\Http\Controllers\Controller;
use App\Models\Gestion_Academica\gestionarTurno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class gestionarTurnoContoller extends Controller
{
    public function index()
    {
        $turnos = gestionarTurno::orderBy('nombre', 'asc')->get();

        return view('Gestion_Academica.gestionarTurno', compact('turnos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'estado' => 'required|in:activo,inactivo',
        ]);

        DB::beginTransaction();

        try {
            gestionarTurno::create([
                'nombre' => $request->nombre,
                'estado' => $request->estado,
            ]);

            $this->registrarBitacora('Gestion Academica', 'Registró el turno: ' . $request->nombre);

            DB::commit();

            return redirect()->route('turnos.index')
                ->with('success', 'Turno registrado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al registrar turno: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'estado' => 'required|in:activo,inactivo',
        ]);

        DB::beginTransaction();

        try {
            $turno = gestionarTurno::findOrFail($id);
            $turno->update([
                'nombre' => $request->nombre,
                'estado' => $request->estado,
            ]);

            $this->registrarBitacora('Gestion Academica', 'Actualizó el turno ID ' . $id . ' a: ' . $request->nombre);

            DB::commit();

            return redirect()->route('turnos.index')
                ->with('success', 'Turno actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al actualizar turno: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $turno = gestionarTurno::findOrFail($id);
            $turno->delete();

            $this->registrarBitacora('Gestion Academica', 'Eliminó el turno ID ' . $id);

            DB::commit();

            return redirect()->route('turnos.index')
                ->with('success', 'Turno eliminado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'No se puede eliminar este turno porque está siendo utilizado por otros registros (ej: grupos o preferencias).'
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
