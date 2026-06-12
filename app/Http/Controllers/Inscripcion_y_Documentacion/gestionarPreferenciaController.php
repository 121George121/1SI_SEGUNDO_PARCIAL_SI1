<?php

namespace App\Http\Controllers\Inscripcion_y_Documentacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Inscripcion_y_Documentacion\gestionarPreferencia;

class gestionarPreferenciaController extends Controller
{
    /**
     * Guarda o actualiza las preferencias de modalidad y turno para una inscripción.
     */
    public function store(Request $request)
    {
        $request->validate([
            'Codigo_inscripcion' => 'required|integer|exists:inscripcion,Codigo_inscripcion',
            'Id_modalidad' => 'required|integer|exists:modalidad,Id_modalidad',
            'Id_turno' => 'required|integer|exists:turno,Id_turno',
        ]);

        DB::beginTransaction();
        try {
            DB::table('preferencia_inscripcion')->updateOrInsert(
                ['Codigo_inscripcion' => $request->Codigo_inscripcion],
                [
                    'Id_modalidad' => $request->Id_modalidad,
                    'Id_turno' => $request->Id_turno,
                    'estado' => 'activo'
                ]
            );

            DB::commit();
            return back()->with('success', 'Preferencia de inscripción (CU07) guardada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al guardar la preferencia: ' . $e->getMessage()]);
        }
    }

    /**
     * Elimina una preferencia por su ID.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            DB::table('preferencia_inscripcion')
                ->where('Id_preferencia', $id)
                ->delete();

            DB::commit();
            return back()->with('success', 'Preferencia de inscripción eliminada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al eliminar la preferencia: ' . $e->getMessage()]);
        }
    }
}
