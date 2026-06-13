<?php

namespace App\Http\Controllers\Logistica_Recursos_y_Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Logistica_Recursos_y_Reportes\gestionarDocentes;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class gestionarDocentesController extends Controller
{
    public function index()
    {
        $docentes = DB::table('docente as d')
            ->join('persona as p', DB::raw('"p"."Id_persona"'), '=', DB::raw('"d"."Id_docente"'))
            ->leftJoin('docente_especialidad as de', DB::raw('"de"."Id_docente"'), '=', DB::raw('"d"."Id_docente"'))
            ->leftJoin('especialidad as e', DB::raw('"e"."Id_especialidad"'), '=', DB::raw('"de"."Id_especialidad"'))
            ->leftJoin('materia as m', DB::raw('"m"."Id_materia"'), '=', DB::raw('"e"."id_materia"'))
            ->select(
                DB::raw('"d"."Id_docente" as id_docente'),
                DB::raw('"p"."Id_persona" as id_persona'),
                'p.ci',
                'p.nombre',
                'p.apellido',
                'p.sexo',
                'p.fecha_nacimiento',
                'p.telefono',
                'p.correo',
                'p.direccion',
                'd.anio_servicio',
                'd.estado',
                DB::raw('COALESCE(string_agg("e"."nombre_especialidad" || COALESCE(\' (\' || "m"."nombre" || \')\', \'\'), \', \'), \'Sin especialidad\') as especialidades')
            )
            ->groupBy(
                DB::raw('"d"."Id_docente"'),
                DB::raw('"p"."Id_persona"'),
                'p.ci',
                'p.nombre',
                'p.apellido',
                'p.sexo',
                'p.fecha_nacimiento',
                'p.telefono',
                'p.correo',
                'p.direccion',
                'd.anio_servicio',
                'd.estado'
            )
            ->orderBy(DB::raw('"d"."Id_docente"'), 'desc')
            ->get();

        $especialidades = DB::table('especialidad')
            ->select(
                DB::raw('"Id_especialidad" as id_especialidad'),
                'nombre_especialidad'
            )
            ->orderBy('nombre_especialidad')
            ->get();

        return view('Logistica_Recursos_y_Reportes.gestionarDocentes', compact('docentes', 'especialidades'));
    }


    public function store(Request $request)
{
    $request->validate([
        'ci' => 'required|string|max:20',
        'nombre' => 'required|string|max:100',
        'apellido' => 'required|string|max:100',
        'sexo' => 'nullable|string|max:1',
        'fecha_nacimiento' => 'required|date',
        'telefono' => 'nullable|string|max:20',
        'correo' => 'required|email|max:150',
        'direccion' => 'nullable|string',
        'anio_servicio' => 'required|integer|min:0',
        'estado' => 'required|string|max:20',
        'especialidades' => 'nullable|array',
        'especialidades.*' => 'exists:especialidad,Id_especialidad',
    ]);

    DB::beginTransaction();

    try {
        // 1. Buscar si la persona ya existe por CI
        $persona = DB::table('persona')
            ->where('ci', $request->ci)
            ->first();

        if ($persona) {
            $idPersona = $persona->Id_persona;

            // Actualizar datos de la persona existente
            DB::table('persona')
                ->where('Id_persona', $idPersona)
                ->update([
                    'nombre' => $request->nombre,
                    'apellido' => $request->apellido,
                    'sexo' => $request->sexo,
                    'fecha_nacimiento' => $request->fecha_nacimiento,
                    'telefono' => $request->telefono,
                    'correo' => $request->correo,
                    'direccion' => $request->direccion,
                    'estado' => 'activo',
                    'tipo_Docente' => true,
                ]);
        } else {
            // Crear persona si no existe
            $idPersona = DB::table('persona')->insertGetId([
                'ci' => $request->ci,
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'sexo' => $request->sexo,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'telefono' => $request->telefono,
                'correo' => $request->correo,
                'direccion' => $request->direccion,
                'estado' => 'activo',
                'tipo_Docente' => true,
            ], 'Id_persona');
        }

        // 2. Verificar si ya existe como docente
        $docenteExiste = DB::table('docente')
            ->where('Id_docente', $idPersona)
            ->exists();

        if ($docenteExiste) {
            DB::table('docente')
                ->where('Id_docente', $idPersona)
                ->update([
                    'anio_servicio' => $request->anio_servicio,
                    'estado' => $request->estado,
                ]);
        } else {
            DB::table('docente')->insert([
                'Id_docente' => $idPersona,
                'anio_servicio' => $request->anio_servicio,
                'estado' => 'En_Revision',
            ]);
        }

        // 3. Verificar que el correo no esté usado por otro usuario
        $usuarioCorreoOtro = DB::table('usuario')
            ->where('correo', $request->correo)
            ->where('Id_persona', '!=', $idPersona)
            ->exists();

        if ($usuarioCorreoOtro) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'El correo ya está usado por otro usuario.'
            ])->withInput();
        }

        // 4. Crear o actualizar usuario del docente
        $usuarioExiste = DB::table('usuario')
            ->where('Id_persona', $idPersona)
            ->exists();

        if ($usuarioExiste) {
            DB::table('usuario')
                ->where('Id_persona', $idPersona)
                ->update([
                    'nombre_usuario' => $request->correo,
                    'correo' => $request->correo,
                    'estado' => 'activo',
                ]);
        } else {
            DB::table('usuario')->insert([
                'nombre_usuario' => $request->correo,
                'correo' => $request->correo,
                'contrasena' => Hash::make($request->ci),
                'estado' => 'activo',
                'fecha_creacion' => now()->toDateString(),
                'Id_persona' => $idPersona,
            ]);
        }

        // 5. Actualizar especialidades
        DB::table('docente_especialidad')
            ->where('Id_docente', $idPersona)
            ->delete();

        if ($request->filled('especialidades')) {
            foreach ($request->especialidades as $idEspecialidad) {
                DB::table('docente_especialidad')->insert([
                    'Id_docente' => $idPersona,
                    'Id_especialidad' => $idEspecialidad,
                ]);
            }
        }

        $this->registrarBitacora(
            'Logistica y Recursos',
            'Registró o actualizó al docente ' . $request->nombre . ' ' . $request->apellido . '.'
        );

        DB::commit();

        return redirect()->route('docentes.index')
            ->with('success', 'Docente registrado o actualizado correctamente. Usuario creado con correo y CI como contraseña inicial.');

    } catch (\Exception $e) {
        DB::rollBack();

        return back()->withErrors([
            'error' => 'Error al registrar docente: ' . $e->getMessage()
        ])->withInput();
    }
}

    public function update(Request $request, $id)
    {
        $request->validate([
            'ci' => [
                'required',
                'string',
                'max:20',
                Rule::unique('persona', 'ci')->ignore($id, 'Id_persona'),
            ],
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'sexo' => 'nullable|string|max:1',
            'fecha_nacimiento' => 'required|date',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:150',
            'direccion' => 'nullable|string',
            'anio_servicio' => 'required|integer|min:0',
            'estado' => 'required|string|max:20',
            'especialidades' => 'nullable|array',
            'especialidades.*' => 'exists:especialidad,Id_especialidad',
        ]);

        DB::beginTransaction();

        try {
            DB::table('persona')
                ->where('Id_persona', $id)
                ->update([
                    'ci' => $request->ci,
                    'nombre' => $request->nombre,
                    'apellido' => $request->apellido,
                    'sexo' => $request->sexo,
                    'fecha_nacimiento' => $request->fecha_nacimiento,
                    'telefono' => $request->telefono,
                    'correo' => $request->correo,
                    'direccion' => $request->direccion,
                    'tipo_Docente' => true,
                ]);

            DB::table('docente')
                ->where('Id_docente', $id)
                ->update([
                    'anio_servicio' => $request->anio_servicio,
                    'estado' => $request->estado,
                ]);

            DB::table('docente_especialidad')
                ->where('Id_docente', $id)
                ->delete();

            if ($request->filled('especialidades')) {
                foreach ($request->especialidades as $idEspecialidad) {
                    DB::table('docente_especialidad')->insert([
                        'Id_docente' => $id,
                        'Id_especialidad' => $idEspecialidad,
                    ]);
                }
            }

            $this->registrarBitacora(
                'Logistica y Recursos',
                'Actualizó al docente ' . $request->nombre . ' ' . $request->apellido . '.'
            );

            DB::commit();

            return redirect()->route('docentes.index')
                ->with('success', 'Docente actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al actualizar docente: ' . $e->getMessage()
            ]);
        }
    }

    public function validarDocumentos($id)
    {
        DB::table('persona_documento')
            ->where('Id_persona', $id)
            ->update([
                'estado' => 'validado',
                'fecha_revision' => now()->toDateString(),
            ]);

        $this->registrarBitacora(
            'Logistica y Recursos',
            'Validó documentos del docente ID ' . $id . '.'
        );

        return redirect()->route('docentes.index')
            ->with('success', 'Documentos del docente validados correctamente.');
    }

    public function deshabilitar($id)
    {
        DB::table('docente')
            ->where('Id_docente', $id)
            ->update(['estado' => 'inactivo']);

        DB::table('persona')
            ->where('Id_persona', $id)
            ->update(['estado' => 'inactivo']);

        $this->registrarBitacora(
            'Logistica y Recursos',
            'Deshabilitó al docente ID ' . $id . '.'
        );

        return redirect()->route('docentes.index')
            ->with('success', 'Docente deshabilitado correctamente.');
    }

    public function habilitar($id)
    {
        DB::table('docente')
            ->where('Id_docente', $id)
            ->update(['estado' => 'activo']);

        DB::table('persona')
            ->where('Id_persona', $id)
            ->update(['estado' => 'activo']);

        $this->registrarBitacora(
            'Logistica y Recursos',
            'Habilitó al docente ID ' . $id . '.'
        );

        return redirect()->route('docentes.index')
            ->with('success', 'Docente habilitado correctamente.');
    }
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $docente = DB::table('docente as d')
                ->join('persona as p', DB::raw('"p"."Id_persona"'), '=', DB::raw('"d"."Id_docente"'))
                ->select(
                    DB::raw('"d"."Id_docente" as id_docente'),
                    'p.nombre',
                    'p.apellido'
                )
                ->where('d.Id_docente', $id)
                ->first();

            if (!$docente) {
                return redirect()->route('docentes.index')
                    ->withErrors(['error' => 'El docente no existe.']);
            }

            // Verificar si el docente está asignado a algún grupo
            $tieneGrupos = DB::table('grupo')
                ->where('Id_docente', $id)
                ->exists();

            if ($tieneGrupos) {
                DB::rollBack();

                return redirect()->route('docentes.index')
                    ->withErrors([
                        'error' => 'No se puede eliminar este docente porque está asignado a uno o más grupos. Primero reasigna o elimina esos grupos.'
                    ]);
            }

            // Eliminar especialidades asignadas
            DB::table('docente_especialidad')
                ->where('Id_docente', $id)
                ->delete();

            // Eliminar usuario creado para el docente
            DB::table('usuario')
                ->where('Id_persona', $id)
                ->delete();

            // Eliminar registro de docente
            DB::table('docente')
                ->where('Id_docente', $id)
                ->delete();

            // Mantener la persona, pero ya no como docente
            DB::table('persona')
                ->where('Id_persona', $id)
                ->update([
                    'tipo_Docente' => false,
                    'estado' => 'inactivo',
                ]);

            $this->registrarBitacora(
                'Logistica y Recursos',
                'Eliminó al docente ' . $docente->nombre . ' ' . $docente->apellido . '.'
            );

            DB::commit();

            return redirect()->route('docentes.index')
                ->with('success', 'Docente eliminado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('docentes.index')
                ->withErrors([
                    'error' => 'No se pudo eliminar el docente: ' . $e->getMessage()
                ]);
        }
    }

    public function documentos($id)
    {
        $docente = DB::table('docente as d')
            ->join('persona as p', DB::raw('"p"."Id_persona"'), '=', DB::raw('"d"."Id_docente"'))
            ->select(
                DB::raw('"d"."Id_docente" as id_docente'),
                'p.ci',
                'p.nombre',
                'p.apellido',
                'd.estado'
            )
            ->where('d.Id_docente', $id)
            ->first();

        if (!$docente) {
            return redirect()->route('docentes.index')
                ->withErrors(['error' => 'El docente no existe.']);
        }

        $documentos = DB::table('documento as doc')
            ->leftJoin('persona_documento as pd', function ($join) use ($id) {
                $join->on(DB::raw('"pd"."Id_documento"'), '=', DB::raw('"doc"."Id_documento"'))
                    ->where(DB::raw('"pd"."Id_persona"'), '=', $id);
            })
            ->select(
                DB::raw('"doc"."Id_documento" as id_documento'),
                'doc.nombre',
                'doc.tipo_documento',
                'doc.destinado_a',
                'doc.descripcion',
                DB::raw('COALESCE(pd.estado, \'No presentado\') as estado_documento'),
                'pd.observacion',
                'pd.fecha_revision'
            )
            ->whereRaw("LOWER(TRIM(doc.destinado_a)) = 'docentes'")
            ->orderBy('doc.nombre')
            ->get();

        return view('Logistica_Recursos_y_Reportes.documentosDocente', compact('docente', 'documentos'));
    }

    public function guardarDocumentos(Request $request, $id)
    {
        $request->validate([
            'estado_documento' => 'required|array',
            'estado_documento.*' => 'required|in:Aprobado,Presentado,Rechazado,No presentado',
            'observacion' => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            $docente = DB::table('docente')
                ->where('Id_docente', $id)
                ->first();

            if (!$docente) {
                DB::rollBack();

                return redirect()->route('docentes.index')
                    ->withErrors(['error' => 'El docente no existe.']);
            }

            $idAdministrador = $this->obtenerIdAdministradorActual();

            if (!$idAdministrador) {
                DB::rollBack();

                return back()->withErrors([
                    'error' => 'El usuario actual no está registrado en la tabla administrador. Debe existir como administrador para validar documentos.'
                ]);
            }

            foreach ($request->estado_documento as $idDocumento => $estado) {
                $observacion = $request->observacion[$idDocumento] ?? null;

                $existe = DB::table('persona_documento')
                    ->where('Id_persona', $id)
                    ->where('Id_documento', $idDocumento)
                    ->exists();

                if ($existe) {
                    DB::table('persona_documento')
                        ->where('Id_persona', $id)
                        ->where('Id_documento', $idDocumento)
                        ->update([
                            'estado' => $estado,
                            'observacion' => $observacion,
                            'fecha_revision' => now()->toDateString(),
                            'Id_administrador' => $idAdministrador,
                        ]);
                } else {
                    DB::table('persona_documento')->insert([
                        'Id_persona' => $id,
                        'Id_documento' => $idDocumento,
                        'estado' => $estado,
                        'observacion' => $observacion,
                        'fecha_revision' => now()->toDateString(),
                        'Id_administrador' => $idAdministrador,
                    ]);
                }
            }

            $this->actualizarEstadoDocentePorDocumentos($id);

            $this->registrarBitacora(
                'Logistica y Recursos',
                'Actualizó documentos del docente ID ' . $id . '.'
            );

            DB::commit();

            return redirect()->route('docentes.documentos.form', $id)
                ->with('success', 'Documentos actualizados correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Error al guardar documentos: ' . $e->getMessage()
            ]);
        }
    }
    
    
    private function actualizarEstadoDocentePorDocumentos($idDocente)
    {
        $totalDocumentos = DB::table('documento')
            ->whereRaw("LOWER(TRIM(destinado_a)) = 'docentes'")
            ->count();

        if ($totalDocumentos == 0) {
            return;
        }

        $documentosAprobados = DB::table('persona_documento as pd')
            ->join('documento as doc', DB::raw('"doc"."Id_documento"'), '=', DB::raw('"pd"."Id_documento"'))
            ->where('pd.Id_persona', $idDocente)
            ->whereRaw("LOWER(TRIM(doc.destinado_a)) = 'docentes'")
            ->where('pd.estado', 'Aprobado')
            ->count();

        if ($documentosAprobados == $totalDocumentos) {
            DB::table('docente')
                ->where('Id_docente', $idDocente)
                ->update([
                    'estado' => 'activo',
                ]);

            DB::table('persona')
                ->where('Id_persona', $idDocente)
                ->update([
                    'estado' => 'activo',
                    'tipo_Docente' => true,
                ]);
        } else {
            DB::table('docente')
                ->where('Id_docente', $idDocente)
                ->update([
                    'estado' => 'En_Revision',
                ]);
        }
    }

    private function obtenerIdAdministradorActual()
    {
        if (!Auth::check()) {
            return null;
        }

        $usuario = DB::table('usuario')
            ->where('Id_usuario', Auth::id())
            ->first();

        if (!$usuario) {
            return null;
        }

        $idPersona = $usuario->Id_persona ?? null;

        if (!$idPersona) {
            return null;
        }

        $existeAdministrador = DB::table('administrador')
            ->where('Id_administrador', $idPersona)
            ->exists();

        if (!$existeAdministrador) {
            return null;
        }

        return $idPersona;
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

    public function descargarPlantilla()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Plantilla Docentes');

        $headers = [
            'ci', 'nombre', 'apellido', 'sexo', 'fecha_nacimiento', 
            'telefono', 'correo_electronico', 'direccion', 
            'anio_servicio', 'especialidades'
        ];

        // Fill headers
        foreach ($headers as $colIdx => $header) {
            $sheet->setCellValue([$colIdx + 1, 1], $header);
            $sheet->getColumnDimensionByColumn($colIdx + 1)->setAutoSize(true);
        }

        // Fill single sample row
        $sampleData = [
            '87654321', 'Ana', 'Rios', 'F', '1985-04-12', '76543210', 'ana.rios@example.com', 'Av. Melchor Pinto #12', '10', 'Licenciatura en Ciencias de la computacion'
        ];
        foreach ($sampleData as $colIdx => $val) {
            $sheet->setCellValue([$colIdx + 1, 2], $val);
        }

        $fileName = 'plantilla_registro_docentes.xlsx';

        return response()->streamDownload(function() use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function importarDocentes(Request $request)
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls'],
        ], [
            'excel_file.required' => 'Debe seleccionar un archivo Excel.',
            'excel_file.mimes' => 'El archivo debe ser de formato .xlsx o .xls.',
        ]);

        $file = $request->file('excel_file');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
        } catch (\Exception $e) {
            return back()->withErrors(['excel_file' => 'Error al cargar el archivo Excel: ' . $e->getMessage()]);
        }

        if (count($rows) <= 1) {
            return back()->withErrors(['excel_file' => 'El archivo Excel está vacío o no contiene datos.']);
        }

        $headers = array_map(function($val) {
            return trim(strtolower($val));
        }, $rows[0]);

        $colMapping = [
            'ci' => array_search('ci', $headers, true),
            'nombre' => array_search('nombre', $headers, true),
            'apellido' => array_search('apellido', $headers, true),
            'sexo' => array_search('sexo', $headers, true),
            'fecha_nacimiento' => array_search('fecha_nacimiento', $headers, true),
            'telefono' => array_search('telefono', $headers, true),
            'correo' => array_search('correo_electronico', $headers, true),
            'direccion' => array_search('direccion', $headers, true),
            'anio_servicio' => array_search('anio_servicio', $headers, true),
            'especialidades' => array_search('especialidades', $headers, true),
        ];

        // Validate headers
        $missingColumns = [];
        foreach ($colMapping as $key => $index) {
            if ($index === false && !in_array($key, ['telefono', 'direccion', 'especialidades'], true)) {
                $missingColumns[] = $key;
            }
        }

        if (!empty($missingColumns)) {
            return back()->withErrors(['excel_file' => 'Faltan columnas requeridas en el Excel: ' . implode(', ', $missingColumns)]);
        }

        $importErrors = [];
        $validatedData = [];

        // Check for duplicates in file
        $seenCi = [];
        $seenCorreo = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            // Check if row is empty
            $isEmptyRow = true;
            foreach ($row as $cell) {
                if ($cell !== null && trim($cell) !== '') {
                    $isEmptyRow = false;
                    break;
                }
            }
            if ($isEmptyRow) {
                continue;
            }

            $numFila = $i + 1;

            $getVal = function($key) use ($row, $colMapping) {
                $idx = $colMapping[$key];
                return ($idx !== false && isset($row[$idx])) ? trim($row[$idx]) : null;
            };

            $ci = $getVal('ci');
            $nombre = $getVal('nombre');
            $apellido = $getVal('apellido');
            $sexo = $getVal('sexo');
            $fecha_nacimiento = $getVal('fecha_nacimiento');
            $telefono = $getVal('telefono');
            $correo = $getVal('correo');
            $direccion = $getVal('direccion');
            $anio_servicio = $getVal('anio_servicio');
            $especialidadesStr = $getVal('especialidades');

            $rowErrors = [];

            // 1. Required field validations
            if (empty($ci)) $rowErrors[] = 'El campo "ci" es obligatorio.';
            if (empty($nombre)) $rowErrors[] = 'El campo "nombre" es obligatorio.';
            if (empty($apellido)) $rowErrors[] = 'El campo "apellido" es obligatorio.';
            if (empty($fecha_nacimiento)) $rowErrors[] = 'El campo "fecha_nacimiento" es obligatorio.';
            if (empty($correo)) $rowErrors[] = 'El campo "correo_electronico" es obligatorio.';
            if (empty($anio_servicio) && $anio_servicio !== '0') $rowErrors[] = 'El campo "anio_servicio" es obligatorio.';

            // 2. Format validations
            if (!empty($sexo) && !in_array(strtoupper($sexo), ['M', 'F'], true)) {
                $rowErrors[] = 'El sexo debe ser "M" o "F".';
            }
            if (!empty($correo) && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = 'El correo electrónico no tiene un formato válido.';
            }
            if (!empty($anio_servicio) && !is_numeric($anio_servicio)) {
                $rowErrors[] = 'Los años de servicio deben ser un número.';
            }

            // Parse Date
            if (!empty($fecha_nacimiento)) {
                if (is_numeric($fecha_nacimiento)) {
                    try {
                        $dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($fecha_nacimiento);
                        $fecha_nacimiento = $dateObj->format('Y-m-d');
                    } catch (\Exception $e) {
                        $rowErrors[] = 'El formato de fecha_nacimiento es inválido.';
                    }
                } else {
                    $timestamp = strtotime($fecha_nacimiento);
                    if ($timestamp === false) {
                        $rowErrors[] = 'El formato de fecha_nacimiento es inválido (debe ser YYYY-MM-DD).';
                    } else {
                        $fecha_nacimiento = date('Y-m-d', $timestamp);
                    }
                }
            }

            // Parse Especialidades
            $especialidadesIds = [];
            if (!empty($especialidadesStr)) {
                $names = explode(',', $especialidadesStr);
                foreach ($names as $name) {
                    $nameTrimmed = trim($name);
                    if (empty($nameTrimmed)) continue;
                    $esp = DB::table('especialidad')
                        ->whereRaw("LOWER(TRIM(nombre_especialidad)) = ?", [strtolower($nameTrimmed)])
                        ->first();
                    if ($esp) {
                        $especialidadesIds[] = $esp->Id_especialidad;
                    } else {
                        $rowErrors[] = "La especialidad '{$nameTrimmed}' no existe.";
                    }
                }
            }

            // 3. Duplicates check
            if (!empty($ci)) {
                if (isset($seenCi[$ci])) {
                    $rowErrors[] = "El CI '{$ci}' está duplicado en el archivo (visto en fila " . $seenCi[$ci] . ").";
                } else {
                    $seenCi[$ci] = $numFila;
                }
            }
            if (!empty($correo)) {
                $correoLower = strtolower($correo);
                if (isset($seenCorreo[$correoLower])) {
                    $rowErrors[] = "El correo '{$correo}' está duplicado en el archivo (visto en fila " . $seenCorreo[$correoLower] . ").";
                } else {
                    $seenCorreo[$correoLower] = $numFila;
                }
            }

            // DB unique check for new entries
            if (empty($rowErrors)) {
                $existPersona = DB::table('persona')->where('ci', $ci)->first();
                $idPersonaExistente = $existPersona ? $existPersona->Id_persona : null;

                $correoUsado = DB::table('persona')
                    ->where('correo', $correo)
                    ->when($idPersonaExistente, function($q) use ($idPersonaExistente) {
                        $q->where('Id_persona', '!=', $idPersonaExistente);
                    })
                    ->exists() || DB::table('usuario')
                    ->where('correo', $correo)
                    ->when($idPersonaExistente, function($q) use ($idPersonaExistente) {
                        $q->where('Id_persona', '!=', $idPersonaExistente);
                    })
                    ->exists();

                if ($correoUsado) {
                    $rowErrors[] = "El correo '{$correo}' ya está registrado en el sistema.";
                }
            }

            if (!empty($rowErrors)) {
                $importErrors[] = "Fila {$numFila}: " . implode(' ', $rowErrors);
            } else {
                $validatedData[] = [
                    'ci' => $ci,
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'sexo' => $sexo ? strtoupper($sexo) : null,
                    'fecha_nacimiento' => $fecha_nacimiento,
                    'telefono' => $telefono,
                    'correo' => $correo,
                    'direccion' => $direccion,
                    'anio_servicio' => intval($anio_servicio),
                    'especialidades' => $especialidadesIds,
                ];
            }
        }

        if (!empty($importErrors)) {
            return back()->with('import_errors', $importErrors);
        }

        DB::beginTransaction();
        try {
            foreach ($validatedData as $data) {
                // 1. Crear o actualizar persona
                $persona = DB::table('persona')->where('ci', $data['ci'])->first();

                if ($persona) {
                    $idPersona = $persona->Id_persona;
                    DB::table('persona')->where('Id_persona', $idPersona)->update([
                        'nombre' => $data['nombre'],
                        'apellido' => $data['apellido'],
                        'sexo' => $data['sexo'],
                        'fecha_nacimiento' => $data['fecha_nacimiento'],
                        'telefono' => $data['telefono'],
                        'correo' => $data['correo'],
                        'direccion' => $data['direccion'],
                        'estado' => 'activo',
                        'tipo_Docente' => true,
                    ]);
                } else {
                    $idPersona = DB::table('persona')->insertGetId([
                        'ci' => $data['ci'],
                        'nombre' => $data['nombre'],
                        'apellido' => $data['apellido'],
                        'sexo' => $data['sexo'],
                        'fecha_nacimiento' => $data['fecha_nacimiento'],
                        'telefono' => $data['telefono'],
                        'correo' => $data['correo'],
                        'direccion' => $data['direccion'],
                        'estado' => 'activo',
                        'tipo_Docente' => true,
                    ], 'Id_persona');
                }

                // 2. Crear o actualizar docente
                DB::table('docente')->updateOrInsert(
                    ['Id_docente' => $idPersona],
                    [
                        'anio_servicio' => $data['anio_servicio'],
                        'estado' => 'activo',
                    ]
                );

                // 3. Crear o actualizar usuario
                $usuario = DB::table('usuario')->where('Id_persona', $idPersona)->first();
                if ($usuario) {
                    DB::table('usuario')->where('Id_persona', $idPersona)->update([
                        'nombre_usuario' => $data['correo'],
                        'correo' => $data['correo'],
                        'estado' => 'activo',
                    ]);
                } else {
                    DB::table('usuario')->insert([
                        'nombre_usuario' => $data['correo'],
                        'correo' => $data['correo'],
                        'contrasena' => Hash::make($data['ci']),
                        'estado' => 'activo',
                        'fecha_creacion' => now()->toDateString(),
                        'Id_persona' => $idPersona,
                    ]);
                }

                // 4. Especialidades
                DB::table('docente_especialidad')->where('Id_docente', $idPersona)->delete();
                foreach ($data['especialidades'] as $espId) {
                    DB::table('docente_especialidad')->insert([
                        'Id_docente' => $idPersona,
                        'Id_especialidad' => $espId,
                    ]);
                }

                $this->registrarBitacora(
                    'Logistica y Recursos',
                    'Docente CI ' . $data['ci'] . ' importado por Excel.'
                );
            }

            DB::commit();

            return redirect()->route('docentes.index')
                ->with('success', 'Se han registrado correctamente ' . count($validatedData) . ' docentes desde el Excel.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['excel_file' => 'Error al registrar los docentes en la base de datos: ' . $e->getMessage()]);
        }
    }
}