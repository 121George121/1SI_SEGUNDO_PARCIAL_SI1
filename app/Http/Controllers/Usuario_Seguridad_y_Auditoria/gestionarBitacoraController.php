<?php

namespace App\Http\Controllers\Usuario_Seguridad_y_Auditoria;

use App\Http\Controllers\Controller;
use App\Models\Usuario_Sefuridad_y_Auditoria\gestionarBitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class gestionarBitacoraController extends Controller
{
    public function index(Request $request): View
    {
        $query = gestionarBitacora::with(['usuario', 'detalle']);

        // Filtro por búsqueda de texto general
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('descripcion', 'like', "%{$search}%")
                  ->orWhere('tipo', 'like', "%{$search}%")
                  ->orWhereHas('detalle', function ($dq) use ($search) {
                      $dq->where('accion', 'like', "%{$search}%")
                         ->orWhere('direccion_ip', 'like', "%{$search}%");
                  })
                  ->orWhereHas('usuario', function ($uq) use ($search) {
                      $uq->where('nombre_usuario', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro por tipo/módulo
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        // Filtro por usuario
        if ($request->filled('usuario_id')) {
            $query->where('Id_usuario', $request->input('usuario_id'));
        }

        // Filtro por rango de fechas
        if ($request->filled('fecha_inicio')) {
            $query->where('fecha', '>=', $request->input('fecha_inicio'));
        }
        if ($request->filled('fecha_fin')) {
            $query->where('fecha', '<=', $request->input('fecha_fin'));
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        $logs = $query->orderBy('Id_bitacora', 'desc')->get();

        // Obtener datos auxiliares para los filtros
        $usuarios = DB::table('usuario')
            ->select('Id_usuario', 'nombre_usuario')
            ->orderBy('nombre_usuario')
            ->get();

        $tipos = DB::table('bitacora')
            ->distinct()
            ->orderBy('tipo')
            ->pluck('tipo');

        return view('Usuario_Seguridad_y_Auditoria.gestionarBitacora', compact('logs', 'usuarios', 'tipos'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $bitacora = gestionarBitacora::findOrFail($id);
        
        // Cambiar el estado a inactivo
        $bitacora->update([
            'estado' => 'inactivo'
        ]);

        // Registrar acción en la bitácora
        if (auth()->check()) {
            DB::table('bitacora')->insert([
                'tipo' => 'Usuarios y Roles',
                'descripcion' => 'Desactivó entrada de bitácora ID ' . $id,
                'fecha' => now()->toDateString(),
                'hora' => now()->format('H:i:s'),
                'estado' => 'activo',
                'Id_usuario' => auth()->id(),
            ]);
        }

        return redirect()->route('bitacora.index')
            ->with('success', 'Entrada de bitácora desactivada correctamente.');
    }
}
