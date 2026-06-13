<?php

namespace App\Http\Controllers\Usuario_Seguridad_y_Auditoria;

use App\Http\Controllers\Controller;
use App\Models\Usuario_Sefuridad_y_Auditoria\gestionarUsuariosyRoles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class gestionarUsuariosyRolesController extends Controller
{
    private const ROLES = [
        'tipo_Superadministrador' => 'Superadministrador',
        'tipo_Administrador' => 'Administrador',
        'tipo_Docente' => 'Docente',
        'tipo_Postulante' => 'Postulante',
    ];

    public function index(): View
    {
        $usuarios = gestionarUsuariosyRoles::with('persona')
            ->orderBy('Id_usuario')
            ->get();

        return view('Usuario_Seguridad_y_Auditoria.GestionarUsuariosyRoles', compact('usuarios'));
    }

    public function create(): View
    {
        return view('Usuario_Seguridad_y_Auditoria.FormularioUsuario', [
            'usuario' => null,
            'rolesDisponibles' => self::ROLES,
            'superadministrador' => null,
            'administrador' => null,
            'docente' => null,
            'postulante' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->validarUsuario($request, true);

        DB::transaction(function () use ($request): void {
            $personaId = DB::table('persona')->insertGetId([
                'ci' => $request->ci,
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'sexo' => $request->sexo,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'telefono' => $request->telefono,
                'correo' => $request->correo,
                'direccion' => $request->direccion,
                'estado' => 'activo',
                ...$this->rolesDesdeRequest($request),
            ], 'Id_persona');

            $rol = $request->input('rol');

            // 1. Superadministrador
            if ($rol === 'tipo_Superadministrador') {
                DB::table('superadministrador')->updateOrInsert(
                    ['Id_superadministrador' => $personaId],
                    [
                        'cargo' => $request->cargo_superadmin,
                        'estado' => $request->estado_superadmin ?? 'activo',
                    ]
                );
            }

            // 2. Administrador
            if ($rol === 'tipo_Administrador') {
                DB::table('administrador')->updateOrInsert(
                    ['Id_administrador' => $personaId],
                    [
                        'cargo' => $request->cargo,
                        'area' => $request->area,
                        'estado' => $request->estado_administrador ?? 'activo',
                    ]
                );
            }

            // 3. Docente
            if ($rol === 'tipo_Docente') {
                DB::table('docente')->updateOrInsert(
                    ['Id_docente' => $personaId],
                    [
                        'anio_servicio' => $request->anio_servicio,
                        'estado' => $request->estado_docente ?? 'activo',
                    ]
                );
            }

            // 4. Postulante
            if ($rol === 'tipo_Postulante') {
                DB::table('postulante')->updateOrInsert(
                    ['Id_postulante' => $personaId],
                    [
                        'estado_inscripcion' => $request->estado_inscripcion ?? 'En_Revision',
                        'fecha_registro' => $request->fecha_registro ?? now()->toDateString(),
                    ]
                );
            }

            gestionarUsuariosyRoles::create([
                'nombre_usuario' => $request->nombre_usuario,
                'correo' => $request->correo,
                'contrasena' => Hash::make($request->contrasena),
                'estado' => 'activo',
                'fecha_creacion' => now()->toDateString(),
                'Id_persona' => $personaId,
            ]);
        });

        $this->registrarBitacora('Usuario creado: '.$request->nombre_usuario);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario registrado correctamente.');
    }

    public function edit(int $id): View
    {
        $usuario = gestionarUsuariosyRoles::with('persona')->findOrFail($id);

        $superadministrador = null;
        $administrador = null;
        $docente = null;
        $postulante = null;

        if ($usuario->Id_persona) {
            $superadministrador = DB::table('superadministrador')
                ->where('Id_superadministrador', $usuario->Id_persona)
                ->first();

            $administrador = DB::table('administrador')
                ->where('Id_administrador', $usuario->Id_persona)
                ->first();

            $docente = DB::table('docente')
                ->where('Id_docente', $usuario->Id_persona)
                ->first();

            $postulante = DB::table('postulante')
                ->where('Id_postulante', $usuario->Id_persona)
                ->first();
        }

        return view('Usuario_Seguridad_y_Auditoria.FormularioUsuario', [
            'usuario' => $usuario,
            'rolesDisponibles' => self::ROLES,
            'superadministrador' => $superadministrador,
            'administrador' => $administrador,
            'docente' => $docente,
            'postulante' => $postulante,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $usuario = gestionarUsuariosyRoles::with('persona')->findOrFail($id);

        $this->validarUsuario($request, false, $id);

        DB::transaction(function () use ($request, $usuario): void {
            $usuario->persona->update([
                'ci' => $request->ci,
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'sexo' => $request->sexo,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'telefono' => $request->telefono,
                'correo' => $request->correo,
                'direccion' => $request->direccion,
                ...$this->rolesDesdeRequest($request),
            ]);

            $rol = $request->input('rol');

            // 1. Superadministrador
            if ($rol === 'tipo_Superadministrador') {
                DB::table('superadministrador')->updateOrInsert(
                    ['Id_superadministrador' => $usuario->Id_persona],
                    [
                        'cargo' => $request->cargo_superadmin,
                        'estado' => $request->estado_superadmin ?? 'activo',
                    ]
                );
            } else {
                DB::table('superadministrador')
                    ->where('Id_superadministrador', $usuario->Id_persona)
                    ->update(['estado' => 'inactivo']);
            }

            // 2. Administrador
            if ($rol === 'tipo_Administrador') {
                DB::table('administrador')->updateOrInsert(
                    ['Id_administrador' => $usuario->Id_persona],
                    [
                        'cargo' => $request->cargo,
                        'area' => $request->area,
                        'estado' => $request->estado_administrador ?? 'activo',
                    ]
                );
            } else {
                DB::table('administrador')
                    ->where('Id_administrador', $usuario->Id_persona)
                    ->update(['estado' => 'inactivo']);
            }

            // 3. Docente
            if ($rol === 'tipo_Docente') {
                DB::table('docente')->updateOrInsert(
                    ['Id_docente' => $usuario->Id_persona],
                    [
                        'anio_servicio' => $request->anio_servicio,
                        'estado' => $request->estado_docente ?? 'activo',
                    ]
                );
            } else {
                DB::table('docente')
                    ->where('Id_docente', $usuario->Id_persona)
                    ->update(['estado' => 'inactivo']);
            }

            // 4. Postulante
            if ($rol === 'tipo_Postulante') {
                DB::table('postulante')->updateOrInsert(
                    ['Id_postulante' => $usuario->Id_persona],
                    [
                        'estado_inscripcion' => $request->estado_inscripcion ?? 'En_Revision',
                        'fecha_registro' => $request->fecha_registro ?? now()->toDateString(),
                    ]
                );
            } else {
                DB::table('postulante')
                    ->where('Id_postulante', $usuario->Id_persona)
                    ->update(['estado_inscripcion' => 'inactivo']);
            }

            $datosUsuario = [
                'nombre_usuario' => $request->nombre_usuario,
                'correo' => $request->correo,
                'estado' => $request->estado ?? 'activo',
            ];

            if ($request->filled('contrasena')) {
                $datosUsuario['contrasena'] = Hash::make($request->contrasena);
            }

            $usuario->update($datosUsuario);
        });

        $this->registrarBitacora('Usuario actualizado: '.$usuario->nombre_usuario);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $usuario = gestionarUsuariosyRoles::with('persona')->findOrFail($id);

        if ((int) auth()->id() === (int) $usuario->Id_usuario) {
            return back()->withErrors(['general' => 'No puedes eliminar tu propio usuario.']);
        }

        DB::beginTransaction();
        try {
            $nombreUsuario = $usuario->nombre_usuario;
            $personaId = $usuario->Id_persona;

            // Delete user record first
            $usuario->delete();

            // Delete associated persona (cascades to superadmin, admin, docente, postulante, etc.)
            if ($personaId) {
                DB::table('persona')->where('Id_persona', $personaId)->delete();
            }

            $this->registrarBitacora('Usuario y persona eliminados: ' . $nombreUsuario);

            DB::commit();

            return redirect()->route('usuarios.index')
                ->with('success', 'Usuario y sus roles asociados eliminados correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('usuarios.index')
                ->withErrors(['general' => 'No se pudo eliminar el usuario: ' . $e->getMessage()]);
        }
    }

    public function mostrarAsignarRoles(int $id): View
    {
        $usuario = gestionarUsuariosyRoles::with('persona')->findOrFail($id);

        return view('Usuario_Seguridad_y_Auditoria.AsignarRoles', [
            'usuario' => $usuario,
            'rolesDisponibles' => self::ROLES,
        ]);
    }

    public function assignRoles(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'rol' => ['required', 'string', 'in:'.implode(',', array_keys(self::ROLES))],
        ], [
            'rol.required' => 'Debes seleccionar un rol.',
        ]);

        $usuario = gestionarUsuariosyRoles::with('persona')->findOrFail($id);

        DB::transaction(function () use ($request, $usuario): void {
            $usuario->persona->update($this->rolesDesdeRequest($request));

            $rolSeleccionado = $request->input('rol');

            // 1. Superadministrador
            if ($rolSeleccionado === 'tipo_Superadministrador') {
                DB::table('superadministrador')->updateOrInsert(
                    ['Id_superadministrador' => $usuario->Id_persona],
                    ['estado' => 'activo']
                );
            } else {
                DB::table('superadministrador')
                    ->where('Id_superadministrador', $usuario->Id_persona)
                    ->update(['estado' => 'inactivo']);
            }

            // 2. Administrador
            if ($rolSeleccionado === 'tipo_Administrador') {
                DB::table('administrador')->updateOrInsert(
                    ['Id_administrador' => $usuario->Id_persona],
                    ['estado' => 'activo']
                );
            } else {
                DB::table('administrador')
                    ->where('Id_administrador', $usuario->Id_persona)
                    ->update(['estado' => 'inactivo']);
            }

            // 3. Docente
            if ($rolSeleccionado === 'tipo_Docente') {
                DB::table('docente')->updateOrInsert(
                    ['Id_docente' => $usuario->Id_persona],
                    ['estado' => 'activo']
                );
            } else {
                DB::table('docente')
                    ->where('Id_docente', $usuario->Id_persona)
                    ->update(['estado' => 'inactivo']);
            }

            // 4. Postulante
            if ($rolSeleccionado === 'tipo_Postulante') {
                DB::table('postulante')->updateOrInsert(
                    ['Id_postulante' => $usuario->Id_persona],
                    ['estado_inscripcion' => 'En_Revision']
                );
            } else {
                DB::table('postulante')
                    ->where('Id_postulante', $usuario->Id_persona)
                    ->update(['estado_inscripcion' => 'inactivo']);
            }
        });

        $this->registrarBitacora('Roles actualizados para: '.$usuario->nombre_usuario);

        return redirect()->route('usuarios.index')
            ->with('success', 'Roles asignados correctamente.');
    }

    private function validarUsuario(Request $request, bool $esNuevo, ?int $id = null): array
    {
        $reglas = [
            'nombre_usuario' => ['required', 'string', 'max:50'],
            'correo' => ['required', 'email', 'max:150'],
            'ci' => ['required', 'string', 'max:20'],
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'sexo' => ['nullable', 'in:M,F'],
            'fecha_nacimiento' => ['required', 'date'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string'],
            'estado' => ['nullable', 'in:activo,inactivo'],
            'rol' => ['required', 'string', 'in:'.implode(',', array_keys(self::ROLES))],
        ];

        $rol = $request->input('rol');

        if ($rol === 'tipo_Superadministrador') {
            $reglas['cargo_superadmin'] = ['required', 'string', 'max:100'];
        }
        if ($rol === 'tipo_Administrador') {
            $reglas['cargo'] = ['required', 'string', 'max:100'];
            $reglas['area'] = ['required', 'string', 'max:100'];
        }
        if ($rol === 'tipo_Docente') {
            $reglas['anio_servicio'] = ['required', 'integer', 'min:0'];
        }
        if ($rol === 'tipo_Postulante') {
            $reglas['estado_inscripcion'] = ['required', 'string', 'max:20'];
            $reglas['fecha_registro'] = ['required', 'date'];
        }

        if ($esNuevo) {
            $reglas['nombre_usuario'][] = 'unique:usuario,nombre_usuario';
            $reglas['correo'][] = 'unique:usuario,correo';
            $reglas['ci'][] = 'unique:persona,ci';

            $reglas['contrasena'] = [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ];
        } else {
            $usuario = gestionarUsuariosyRoles::findOrFail($id);

            $reglas['nombre_usuario'][] = 'unique:usuario,nombre_usuario,'.$id.',Id_usuario';
            $reglas['correo'][] = 'unique:usuario,correo,'.$id.',Id_usuario';
            $reglas['ci'][] = 'unique:persona,ci,'.$usuario->Id_persona.',Id_persona';

            $reglas['contrasena'] = [
                'nullable',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ];
        }

        return $request->validate($reglas, [
            'contrasena.regex' => 'La contraseña debe tener mayúsculas, minúsculas, números y caracteres especiales.',
            'rol.required' => 'Debes seleccionar un rol.',
            'cargo_superadmin.required' => 'El cargo es obligatorio para Superadministrador.',
            'cargo.required' => 'El cargo es obligatorio para Administrador.',
            'area.required' => 'El área es obligatoria para Administrador.',
            'anio_servicio.required' => 'Los años de servicio son obligatorios para Docente.',
            'anio_servicio.integer' => 'Los años de servicio deben ser un número entero.',
            'estado_inscripcion.required' => 'El estado de inscripción es obligatorio para Postulante.',
            'fecha_registro.required' => 'La fecha de registro es obligatoria para Postulante.',
        ]);
    }

    private function rolesDesdeRequest(Request $request): array
    {
        $rolSeleccionado = $request->input('rol');

        return [
            'tipo_Superadministrador' => $rolSeleccionado === 'tipo_Superadministrador',
            'tipo_Administrador' => $rolSeleccionado === 'tipo_Administrador',
            'tipo_Docente' => $rolSeleccionado === 'tipo_Docente',
            'tipo_Postulante' => $rolSeleccionado === 'tipo_Postulante',
        ];
    }

    private function esAdministradorOSuperadministrador(Request $request): bool
    {
        $rolSeleccionado = $request->input('rol');

        return $rolSeleccionado === 'tipo_Administrador'
            || $rolSeleccionado === 'tipo_Superadministrador';
    }

    private function registrarBitacora(string $descripcion): void
    {
        if (!auth()->check()) {
            return;
        }

        DB::table('bitacora')->insert([
            'tipo' => 'Usuarios y Roles',
            'descripcion' => $descripcion,
            'fecha' => now()->toDateString(),
            'hora' => now()->format('H:i:s'),
            'estado' => 'activo',
            'Id_usuario' => auth()->id(),
        ]);
    }

    /**
     * Import multiple postulantes from Excel (.xlsx, .xls)
     */
    public function importarPostulantes(Request $request): RedirectResponse
    {
        // 1. Validate that the Excel file is provided and correct format
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

        // Get header row and map column names (case-insensitive) to their indexes
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
            'correo_electronico' => array_search('correo_electronico', $headers, true),
            'direccion' => array_search('direccion', $headers, true),
            'nombre_usuario' => array_search('nombre_usuario', $headers, true),
            'contrasena' => array_search('contrasena', $headers, true),
        ];

        // Also check "contraseña" with ñ in case they wrote it that way
        if ($colMapping['contrasena'] === false) {
            $colMapping['contrasena'] = array_search('contraseña', $headers, true);
        }

        // Validate that crucial columns exist
        $missingColumns = [];
        foreach ($colMapping as $key => $index) {
            if ($index === false && !in_array($key, ['telefono', 'direccion'], true)) {
                $missingColumns[] = $key;
            }
        }

        if (!empty($missingColumns)) {
            return back()->withErrors(['excel_file' => 'Faltan columnas requeridas en el Excel: ' . implode(', ', $missingColumns)]);
        }

        $importErrors = [];
        $validatedData = [];

        // Track duplicates in the same file
        $seenCi = [];
        $seenCorreo = [];
        $seenUsername = [];

        // Skip headers, process row-by-row
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            // Check if row is completely empty
            $isEmptyRow = true;
            foreach ($row as $cell) {
                if ($cell !== null && trim($cell) !== '') {
                    $isEmptyRow = false;
                    break;
                }
            }
            if ($isEmptyRow) {
                continue; // Skip empty rows silently
            }

            $numFila = $i + 1; // 1-indexed Excel row number

            // Helper to get value or null
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
            $correo = $getVal('correo_electronico');
            $direccion = $getVal('direccion');
            $nombre_usuario = $getVal('nombre_usuario');
            $contrasena = $getVal('contrasena');

            $rowErrors = [];

            // 1. Required field validations
            if (empty($ci)) $rowErrors[] = 'El campo "ci" es obligatorio.';
            if (empty($nombre)) $rowErrors[] = 'El campo "nombre" es obligatorio.';
            if (empty($apellido)) $rowErrors[] = 'El campo "apellido" es obligatorio.';
            if (empty($sexo)) $rowErrors[] = 'El campo "sexo" es obligatorio.';
            if (empty($fecha_nacimiento)) $rowErrors[] = 'El campo "fecha_nacimiento" es obligatorio.';
            if (empty($correo)) $rowErrors[] = 'El campo "correo_electronico" es obligatorio.';
            if (empty($nombre_usuario)) $rowErrors[] = 'El campo "nombre_usuario" es obligatorio.';
            if (empty($contrasena)) $rowErrors[] = 'El campo "contraseña" es obligatorio.';

            // 2. Format validations
            if (!empty($sexo) && !in_array(strtoupper($sexo), ['M', 'F'], true)) {
                $rowErrors[] = 'El sexo debe ser "M" o "F".';
            }
            if (!empty($correo) && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = 'El correo electrónico no tiene un formato válido.';
            }
            // Parse Excel date formats or standard date string
            if (!empty($fecha_nacimiento)) {
                // If it is numeric (Excel serial date)
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
                        $rowErrors[] = 'El formato de fecha_nacimiento es inválido (debe ser YYYY-MM-DD o formato Excel).';
                    } else {
                        $fecha_nacimiento = date('Y-m-d', $timestamp);
                    }
                }
            }

            // 3. Batch duplicates check
            if (!empty($ci)) {
                if (isset($seenCi[$ci])) {
                    $rowErrors[] = "El CI '{$ci}' está duplicado dentro del archivo (ya visto en fila " . $seenCi[$ci] . ").";
                } else {
                    $seenCi[$ci] = $numFila;
                }
            }

            if (!empty($correo)) {
                $correoLower = strtolower($correo);
                if (isset($seenCorreo[$correoLower])) {
                    $rowErrors[] = "El correo '{$correo}' está duplicado dentro del archivo (ya visto en fila " . $seenCorreo[$correoLower] . ").";
                } else {
                    $seenCorreo[$correoLower] = $numFila;
                }
            }

            if (!empty($nombre_usuario)) {
                $userLower = strtolower($nombre_usuario);
                if (isset($seenUsername[$userLower])) {
                    $rowErrors[] = "El nombre de usuario '{$nombre_usuario}' está duplicado dentro del archivo (ya visto en fila " . $seenUsername[$userLower] . ").";
                } else {
                    $seenUsername[$userLower] = $numFila;
                }
            }

            // 4. Database uniqueness validations
            if (empty($rowErrors)) {
                $existeCi = DB::table('persona')->where('ci', $ci)->exists();
                if ($existeCi) {
                    $rowErrors[] = "El CI '{$ci}' ya está registrado en el sistema.";
                }

                $existeCorreo = DB::table('persona')->where('correo', $correo)->exists()
                    || DB::table('usuario')->where('correo', $correo)->exists();
                if ($existeCorreo) {
                    $rowErrors[] = "El correo '{$correo}' ya está registrado en el sistema.";
                }

                $existeUsuario = DB::table('usuario')->where('nombre_usuario', $nombre_usuario)->exists();
                if ($existeUsuario) {
                    $rowErrors[] = "El nombre de usuario '{$nombre_usuario}' ya existe.";
                }
            }

            if (!empty($rowErrors)) {
                $importErrors[] = "Fila {$numFila}: " . implode(' ', $rowErrors);
            } else {
                $validatedData[] = [
                    'ci' => $ci,
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'sexo' => strtoupper($sexo),
                    'fecha_nacimiento' => $fecha_nacimiento,
                    'telefono' => $telefono,
                    'correo' => $correo,
                    'direccion' => $direccion,
                    'nombre_usuario' => $nombre_usuario,
                    'contrasena' => $contrasena,
                ];
            }
        }

        // If there were any errors, abort and return listing them
        if (!empty($importErrors)) {
            return back()->with('import_errors', $importErrors);
        }

        // Save records under a secure transaction
        DB::beginTransaction();
        try {
            foreach ($validatedData as $data) {
                // Insert Persona setting only tipo_Postulante to true, others to false
                $personaId = DB::table('persona')->insertGetId([
                    'ci' => $data['ci'],
                    'nombre' => $data['nombre'],
                    'apellido' => $data['apellido'],
                    'sexo' => $data['sexo'],
                    'fecha_nacimiento' => $data['fecha_nacimiento'],
                    'telefono' => $data['telefono'],
                    'correo' => $data['correo'],
                    'direccion' => $data['direccion'],
                    'estado' => 'activo',
                    'tipo_Superadministrador' => false,
                    'tipo_Administrador' => false,
                    'tipo_Docente' => false,
                    'tipo_Postulante' => true,
                ], 'Id_persona');

                // Insert Postulante details
                DB::table('postulante')->insert([
                    'Id_postulante' => $personaId,
                    'estado_inscripcion' => 'En_Revision',
                    'fecha_registro' => now()->toDateString(), // automatically assigned using current system date
                ]);

                // Create associated Usuario
                gestionarUsuariosyRoles::create([
                    'nombre_usuario' => $data['nombre_usuario'],
                    'correo' => $data['correo'],
                    'contrasena' => Hash::make($data['contrasena']),
                    'estado' => 'activo',
                    'fecha_creacion' => now()->toDateString(),
                    'Id_persona' => $personaId,
                ]);

                $this->registrarBitacora('Postulante importado por Excel: ' . $data['nombre_usuario']);
            }

            DB::commit();

            return redirect()->route('usuarios.index')
                ->with('success', 'Se han registrado correctamente ' . count($validatedData) . ' postulantes desde el Excel.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['excel_file' => 'Error al guardar los postulantes en la base de datos: ' . $e->getMessage()]);
        }
    }

    /**
     * Download Excel template file for importing applicants
     */
    public function descargarPlantilla()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Plantilla Postulantes');

        $headers = [
            'ci', 'nombre', 'apellido', 'sexo', 'fecha_nacimiento', 
            'telefono', 'correo_electronico', 'direccion', 
            'nombre_usuario', 'contraseña'
        ];

        // Fill headers
        foreach ($headers as $colIdx => $header) {
            $sheet->setCellValue([$colIdx + 1, 1], $header);
            // Auto size columns
            $sheet->getColumnDimensionByColumn($colIdx + 1)->setAutoSize(true);
        }

        // Fill single sample row
        $sampleData = [
            '12345678', 'Juan', 'Perez', 'M', '2000-01-30', '70011223', 'juan.perez@example.com', 'Av. Bush S/N', 'juanperez', 'Password123!'
        ];
        foreach ($sampleData as $colIdx => $val) {
            $sheet->setCellValue([$colIdx + 1, 2], $val);
        }

        $fileName = 'plantilla_postulantes.xlsx';

        // Prepare file download response directly
        return response()->streamDownload(function() use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}