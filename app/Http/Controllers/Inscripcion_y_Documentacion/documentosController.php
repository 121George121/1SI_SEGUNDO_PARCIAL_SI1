<?php

namespace App\Http\Controllers\Inscripcion_y_Documentacion;

use App\Http\Controllers\Controller;
use App\Models\Inscripcion_y_Documentacion\documentos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class documentosController extends Controller
{
    public function index(): View
    {
        $requisitos = documentos::orderByDesc('Id_documento')->get();

        $entregas = DB::table('persona_documento as pd')
            ->join('persona as p', 'p.Id_persona', '=', 'pd.Id_persona')
            ->join('documento as d', 'd.Id_documento', '=', 'pd.Id_documento')
            ->select(
                'p.Id_persona as id_persona',
                'p.ci',
                'p.nombre as nombre_persona',
                'p.apellido',
                'd.Id_documento as id_documento',
                'd.nombre as nombre_documento',
                'd.tipo_documento',
                'd.destinado_a',
                'pd.estado',
                'pd.observacion',
                'pd.fecha_revision'
            )
            ->orderByDesc('pd.fecha_revision')
            ->get();

        return view('Inscripcion_y_Documentacion.documentos', compact('requisitos', 'entregas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'tipo_documento' => 'required|string|max:50',
            'nombre' => 'required|string|max:100',
            'destinado_a' => 'required|in:Postulantes,Docentes',
            'descripcion' => 'nullable|string',
        ]);

        documentos::create([
            'tipo_documento' => $request->tipo_documento,
            'nombre' => $request->nombre,
            'fecha_registro' => now()->toDateString(),
            'destinado_a' => $request->destinado_a,
            'descripcion' => $request->descripcion,
        ]);

        $this->registrarBitacora('Registro de requisito documental: '.$request->nombre.' ('.$request->destinado_a.')');

        return redirect()->route('documentos.index')
            ->with('success', 'Requisito documental registrado correctamente.');
    }


    public function update(Request $request, int $id): RedirectResponse
    {
        $requisito = documentos::findOrFail($id);

        $request->validate([
            'tipo_documento' => 'required|string|max:50',
            'nombre' => 'required|string|max:100',
            'destinado_a' => 'required|in:Postulantes,Docentes',
            'descripcion' => 'nullable|string',
        ]);

        $requisito->update([
            'tipo_documento' => $request->tipo_documento,
            'nombre' => $request->nombre,
            'destinado_a' => $request->destinado_a,
            'descripcion' => $request->descripcion,
        ]);

        $this->registrarBitacora('Actualizacion de requisito documental: '.$requisito->nombre);

        return redirect()->route('documentos.index')
            ->with('success', 'Requisito documental actualizado correctamente.');
    }

    public function destroyRequisito(int $id): RedirectResponse
    {
        DB::beginTransaction();

        try {
            DB::table('persona_documento')->where('Id_documento', $id)->delete();
            documentos::where('Id_documento', $id)->delete();

            $this->registrarBitacora('Eliminacion del requisito documental ID '.$id);

            DB::commit();

            return redirect()->route('documentos.index')
                ->with('success', 'Requisito documental eliminado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al eliminar requisito: '.$e->getMessage(),
            ]);
        }
    }

    public function validarDocumento(int $idPersona, int $idDocumento): RedirectResponse
    {
        DB::table('persona_documento')
            ->where('Id_persona', $idPersona)
            ->where('Id_documento', $idDocumento)
            ->update([
                'estado' => 'validado',
                'observacion' => null,
                'fecha_revision' => now()->toDateString(),
                'Id_administrador' => $this->idAdministradorActual(),
            ]);

        $this->registrarBitacora('Validacion de documento presentado ID '.$idDocumento);

        return redirect()->route('documentos.index')
            ->with('success', 'Documento validado correctamente.');
    }

    public function observarDocumento(Request $request, int $idPersona, int $idDocumento): RedirectResponse
    {
        $request->validate([
            'observacion' => 'required|string|max:255',
        ]);

        DB::table('persona_documento')
            ->where('Id_persona', $idPersona)
            ->where('Id_documento', $idDocumento)
            ->update([
                'estado' => 'observado',
                'observacion' => $request->observacion,
                'fecha_revision' => now()->toDateString(),
                'Id_administrador' => $this->idAdministradorActual(),
            ]);

        $this->registrarBitacora('Observacion de documento presentado ID '.$idDocumento);

        return redirect()->route('documentos.index')
            ->with('success', 'Documento observado correctamente.');
    }

        private function idAdministradorActual(): ?int
    {
        $idPersona = Auth::user()->Id_persona ?? null;

        if (!$idPersona) {
            return null;
        }

        return DB::table('administrador')
            ->where('Id_administrador', $idPersona)
            ->value('Id_administrador');
    }

    private function registrarBitacora(string $descripcion): void
    {
        if (!Auth::check()) {
            return;
        }

        DB::table('bitacora')->insert([
            'tipo' => 'Documentos',
            'descripcion' => $descripcion,
            'fecha' => now()->toDateString(),
            'hora' => now()->format('H:i:s'),
            'estado' => 'activo',
            'Id_usuario' => Auth::id(),
        ]);
    }
}
