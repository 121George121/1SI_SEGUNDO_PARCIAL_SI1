@extends('Inscripcion_y_Documentacion.Menu')

@section('content')

<h1 class="titulo">CU03 - Gestionar Inscripción</h1>
<p class="subtitulo">Registro, modificación y eliminación de inscripciones de postulantes.</p>

<style>
    .card-box {
        background: #fff;
        padding: 22px;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.08);
        margin-bottom: 24px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: bold;
        color: #0b2d6b;
        margin-bottom: 6px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
        outline: none;
    }

    .full {
        grid-column: 1 / 3;
    }

    .btn-primary,
    .btn-success,
    .btn-warning,
    .btn-danger {
        border: none;
        padding: 9px 12px;
        border-radius: 7px;
        color: white;
        cursor: pointer;
        font-weight: bold;
    }

    .btn-primary { background: #0b2d6b; }
    .btn-success { background: #16a34a; }
    .btn-warning { background: #f59e0b; }
    .btn-danger { background: #dc2626; }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 16px;
    }

    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 16px;
    }

    .info-ci {
        display: none;
        padding: 10px;
        border-radius: 8px;
        margin-top: 8px;
        font-weight: bold;
    }

    /* ---- Searchable dropdown ---- */
    .search-select {
        position: relative;
    }

    .search-input {
        width: 100%;
    }

    .search-options {
        display: none;
        position: absolute;
        top: 100%;
        margin-top: 6px;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ccc;
        border-radius: 8px;
        max-height: 180px;
        overflow-y: auto;
        z-index: 999;
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }

    .search-options.activo {
        display: block;
    }

    .search-option {
        padding: 10px;
        cursor: pointer;
        color: #333;
    }

    .search-option:hover {
        background: #e5e7eb;
    }

    /* ---- Table ---- */
    .table-responsive {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1100px;
    }

    th {
        background: #0b2d6b;
        color: white;
        padding: 12px;
        text-align: left;
    }

    td {
        padding: 12px;
        border-bottom: 1px solid #ddd;
        vertical-align: top;
    }

    .acciones {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    details summary {
        cursor: pointer;
        font-weight: bold;
        color: #0b2d6b;
        margin-bottom: 8px;
    }

    .inline-form {
        display: grid;
        grid-template-columns: repeat(2, minmax(120px, 1fr));
        gap: 6px;
    }

    .inline-form input,
    .inline-form select {
        padding: 7px;
        border: 1px solid #ccc;
        border-radius: 6px;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .full {
            grid-column: 1;
        }
    }

    /* ---- Modal ---- */
    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to   { opacity: 1; transform: scale(1); }
    }
</style>

{{-- ── Mensajes de sesión ── --}}
@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

@if(session('import_errors'))
    <div class="alert-error" style="background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; padding: 16px; border-radius: 8px; margin-bottom: 24px; font-size: 14px; font-weight: 600; text-align: left;">
        <p style="margin: 0 0 8px; font-weight: 700;">Errores durante la importación:</p>
        <ul style="margin: 0; padding-left: 20px; list-style-type: disc;">
            @foreach(session('import_errors') as $error)
                <li style="margin-bottom: 4px;">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

{{-- ══════════════════════════════════════════════════
     CARD 1 – REGISTRAR INSCRIPCIÓN  +  BOTÓN EXCEL
     ══════════════════════════════════════════════════ --}}
<div class="card-box">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
        <h2 style="color:#0b2d6b; margin: 0;">Registrar Inscripción</h2>

        {{-- Botón que abre el modal de importación Excel --}}
        <button type="button"
                onclick="abrirModalImportar()"
                style="background: #10b981; display: inline-flex; align-items: center; gap: 8px;
                       padding: 10px 18px; font-size: 14px; border: none; border-radius: 7px;
                       color: white; cursor: pointer; font-weight: bold; transition: background 0.2s;"
                onmouseover="this.style.background='#059669'"
                onmouseout="this.style.background='#10b981'">
            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Importar postulantes por Excel
        </button>
    </div>

    {{-- Formulario manual de inscripción --}}
    <form action="{{ route('inscripcion.store') }}" method="POST" class="form-grid" id="formRegistrar">
        @csrf

        <div class="form-group">
            <label>CI del Postulante</label>
            <input type="text" name="ci" id="ci_postulante" value="{{ old('ci') }}" required>
            <div id="mensaje_ci" class="info-ci"></div>
        </div>

        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="nombre" id="nombre_postulante" value="{{ old('nombre') }}" required>
        </div>

        <div class="form-group">
            <label>Apellido</label>
            <input type="text" name="apellido" id="apellido_postulante" value="{{ old('apellido') }}" required>
        </div>

        <div class="form-group">
            <label>Sexo</label>
            <select name="sexo" id="sexo_postulante">
                <option value="">Seleccione</option>
                <option value="M">Masculino</option>
                <option value="F">Femenino</option>
            </select>
        </div>

        <div class="form-group">
            <label>Fecha de Nacimiento</label>
            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento_postulante" required>
        </div>

        <div class="form-group">
            <label>Teléfono</label>
            <input type="text" name="telefono" id="telefono_postulante">
        </div>

        <div class="form-group">
            <label>Correo</label>
            <input type="email" name="correo" id="correo_postulante" required>
        </div>

        <div class="form-group full">
            <label>Dirección</label>
            <textarea name="direccion" id="direccion_postulante"></textarea>
        </div>

        {{-- Carrera Principal (búsqueda en vivo) --}}
        <div class="form-group search-select" data-search-select>
            <label>Carrera Principal</label>
            <input type="text" class="search-input" placeholder="Buscar carrera principal..." autocomplete="off" required>
            <input type="hidden" name="Id_carrera_principal" required>
            <div class="search-options">
                @foreach($carreras as $carrera)
                    <div class="search-option" data-value="{{ $carrera->id_carrera }}">{{ $carrera->nombre_carrera }}</div>
                @endforeach
            </div>
        </div>

        {{-- Carrera Secundaria (búsqueda en vivo) --}}
        <div class="form-group search-select" data-search-select>
            <label>Carrera Secundaria</label>
            <input type="text" class="search-input" placeholder="Buscar carrera secundaria..." autocomplete="off">
            <input type="hidden" name="Id_carrera_secundaria">
            <div class="search-options">
                @foreach($carreras as $carrera)
                    <div class="search-option" data-value="{{ $carrera->id_carrera }}">{{ $carrera->nombre_carrera }}</div>
                @endforeach
            </div>
        </div>

        {{-- Gestión (búsqueda en vivo) --}}
        <div class="form-group search-select" data-search-select>
            <label>Gestión</label>
            <input type="text" class="search-input" placeholder="Buscar gestión..." autocomplete="off" required>
            <input type="hidden" name="Id_gestion">
            <div class="search-options">
                @foreach($gestiones as $gestion)
                    <div class="search-option" data-value="{{ $gestion->id_gestion }}">{{ $gestion->anio }} - {{ $gestion->periodo }}</div>
                @endforeach
            </div>
        </div>

        <div class="form-group">
            <label>Modalidad de Preferencia</label>
            <select name="Id_modalidad_preferencia" id="id_modalidad_preferencia" required>
                <option value="">Seleccione Modalidad</option>
                @foreach($modalidades as $mod)
                    <option value="{{ $mod->id_modalidad }}">{{ $mod->nombre_modalidad }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Turno de Preferencia</label>
            <select name="Id_turno_preferencia" id="id_turno_preferencia" required>
                <option value="">Seleccione Turno</option>
                @foreach($turnos as $tur)
                    <option value="{{ $tur->id_turno }}">{{ $tur->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group full">
            <button type="submit" class="btn-primary">Registrar Inscripción</button>
        </div>
    </form>
</div>

{{-- ══════════════════════════════════════════════════
     CARD 2 – LISTADO DE INSCRIPCIONES
     ══════════════════════════════════════════════════ --}}
<div class="card-box">
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Inscripciones Registradas</h2>

    <div style="margin-bottom:16px;">
        <label style="font-weight:bold; color:#0b2d6b; display:block; margin-bottom:6px;">
            Buscar Inscripción
        </label>
        <input
            type="text"
            id="buscarInscripcion"
            placeholder="Buscar por CI, nombre, carrera, gestión, código o estado..."
            style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:14px;">
    </div>

    <div class="table-responsive">
        <table id="tablaInscripciones">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>CI</th>
                    <th>Postulante</th>
                    <th>Carrera Principal</th>
                    <th>Carrera Secundaria</th>
                    <th>Gestión</th>
                    <th>Preferencia</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($inscripciones as $item)
                    <tr>
                        <td>{{ $item->codigo_inscripcion }}</td>
                        <td>{{ $item->ci }}</td>
                        <td>{{ $item->nombre }} {{ $item->apellido }}</td>
                        <td>{{ $item->carrera_principal ?? 'Sin carrera principal' }}</td>
                        <td>{{ $item->carrera_secundaria ?? 'Sin carrera secundaria' }}</td>
                        <td>
                            @if($item->anio)
                                {{ $item->anio }} - {{ $item->periodo }}
                            @else
                                Sin gestión
                            @endif
                        </td>
                        <td>
                            @if($item->modalidad_preferencia)
                                {{ $item->modalidad_preferencia }} ({{ $item->turno_preferencia }})
                            @else
                                <span style="color: #999;">Sin preferencia</span>
                            @endif
                        </td>
                        <td>{{ $item->fecha_inscripcion }}</td>
                        <td>{{ ucfirst($item->estado_inscripcion) }}</td>
                        <td>
                            <div class="acciones">
                                <a href="{{ route('inscripcion.documentos.form', $item->codigo_inscripcion) }}" class="btn-success">
                                    Validar Documentos
                                </a>

                                @php
                                    $docsValidados = ($item->documentos_aprobados == $totalDocumentos && $totalDocumentos > 0);
                                @endphp

                                @if($docsValidados)
                                    <a href="{{ route('pagos.index', ['ci' => $item->ci]) }}" class="btn-primary" style="text-align: center; text-decoration: none;">
                                        Liquidar Pago
                                    </a>
                                @else
                                    <button class="btn-primary" style="background: #cbd5e1; color: #94a3b8; cursor: not-allowed; text-align: center; border: none; padding: 9px 12px; border-radius: 7px; font-weight: bold;" disabled title="Requiere validar previamente todos los documentos.">
                                        Liquidar Pago
                                    </button>
                                @endif

                                {{-- Editar inscripción inline --}}
                                <details>
                                    <summary>Editar Inscripción</summary>

                                    <form action="{{ route('inscripcion.update', $item->id_inscripcion) }}" method="POST" class="inline-form">
                                        @csrf
                                        @method('PUT')

                                        <input type="text"  name="ci"       value="{{ $item->ci }}"       required>
                                        <input type="text"  name="nombre"   value="{{ $item->nombre }}"   required>
                                        <input type="text"  name="apellido" value="{{ $item->apellido }}" required>

                                        <select name="sexo">
                                            <option value="">Sexo</option>
                                            <option value="M" {{ $item->sexo == 'M' ? 'selected' : '' }}>M</option>
                                            <option value="F" {{ $item->sexo == 'F' ? 'selected' : '' }}>F</option>
                                        </select>

                                        <input type="date"  name="fecha_nacimiento" value="{{ substr($item->fecha_nacimiento, 0, 10) }}" required>
                                        <input type="text"  name="telefono"         value="{{ $item->telefono }}">
                                        <input type="email" name="correo"           value="{{ $item->correo }}" required>
                                        <input type="text"  name="direccion"        value="{{ $item->direccion }}">

                                        <select name="estado" required>
                                            <option value="En_Revision" {{ $item->estado_inscripcion == 'En_Revision' ? 'selected' : '' }}>En_Revision</option>
                                        </select>

                                        <select name="Id_carrera_principal" required>
                                            @foreach($carreras as $carrera)
                                                <option value="{{ $carrera->id_carrera }}"
                                                    {{ $item->id_carrera_principal == $carrera->id_carrera ? 'selected' : '' }}>
                                                    {{ $carrera->nombre_carrera }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <select name="Id_carrera_secundaria">
                                            <option value="">Sin carrera secundaria</option>
                                            @foreach($carreras as $carrera)
                                                <option value="{{ $carrera->id_carrera }}"
                                                    {{ $item->id_carrera_secundaria == $carrera->id_carrera ? 'selected' : '' }}>
                                                    {{ $carrera->nombre_carrera }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <select name="Id_gestion" required>
                                            @foreach($gestiones as $gestion)
                                                <option value="{{ $gestion->id_gestion }}"
                                                    {{ $item->id_gestion == $gestion->id_gestion ? 'selected' : '' }}>
                                                    {{ $gestion->anio }} - {{ $gestion->periodo }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <select name="Id_modalidad_preferencia" required>
                                            <option value="">Modalidad Pref.</option>
                                            @foreach($modalidades as $mod)
                                                <option value="{{ $mod->id_modalidad }}"
                                                    {{ $item->id_modalidad_preferencia == $mod->id_modalidad ? 'selected' : '' }}>
                                                    {{ $mod->nombre_modalidad }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <select name="Id_turno_preferencia" required>
                                            <option value="">Turno Pref.</option>
                                            @foreach($turnos as $tur)
                                                <option value="{{ $tur->id_turno }}"
                                                    {{ $item->id_turno_preferencia == $tur->id_turno ? 'selected' : '' }}>
                                                    {{ $tur->nombre }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <button type="submit" class="btn-warning">Actualizar</button>
                                    </form>
                                </details>

                                <form
                                    action="{{ route('inscripcion.destroy', $item->id_inscripcion) }}"
                                    method="POST"
                                    onsubmit="return confirm('¿Seguro que deseas eliminar esta inscripción?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align:center; padding:20px;">
                            No hay inscripciones registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ══════════════════════════════════════════════════
     MODAL – IMPORTAR POSTULANTES POR EXCEL
     ══════════════════════════════════════════════════ --}}
<div id="modalImportar"
     style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6);
            backdrop-filter:blur(4px); z-index:9999; align-items:center;
            justify-content:center; padding:16px;">

    <div style="background:#ffffff; border-radius:16px; width:100%; max-width:580px;
                box-shadow:0 20px 25px -5px rgba(0,0,0,0.15); overflow:hidden;
                border:1px solid #e2e8f0; animation:modalFadeIn 0.25s ease-out;">

        {{-- Cabecera del modal --}}
        <div style="background:#0b2d6b; color:white; padding:20px 24px; font-weight:700;
                    font-size:17px; display:flex; justify-content:space-between; align-items:center;">
            <span style="display:flex; align-items:center; gap:8px;">
                <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Importar Postulantes por Excel
            </span>
            <button onclick="cerrarModalImportar()"
                    style="background:none; border:none; color:white; cursor:pointer; font-size:26px; line-height:1; outline:none;">
                &times;
            </button>
        </div>

        {{-- Cuerpo del modal --}}
        <form action="{{ route('inscripcion.importarPostulantes') }}"
              method="POST"
              enctype="multipart/form-data"
              style="padding:24px; display:flex; flex-direction:column; gap:20px; margin:0;">
            @csrf

            {{-- Zona de arrastre / selección --}}
            <div id="dropZona"
                 style="border:2px dashed #cbd5e1; border-radius:12px; padding:32px 16px;
                        text-align:center; cursor:pointer; background:#f8fafc; transition:all 0.2s;"
                 ondragover="event.preventDefault(); this.style.borderColor='#0b2d6b'; this.style.background='#eff6ff';"
                 ondragleave="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';"
                 ondrop="manejarSoltarArchivo(event);">

                <svg style="width:48px;height:48px;color:#94a3b8;margin:0 auto 12px;display:block;"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <p style="font-weight:700; color:#334155; margin:0 0 4px; font-size:14.5px;">
                    Arrastra tu archivo aquí o haz clic en "Examinar"
                </p>
                <p style="font-size:12px; color:#64748b; margin:0 0 16px;">
                    Formatos aceptados: .xlsx, .xls
                </p>
                <input type="file" name="excel_file" id="archivoExcel"
                       accept=".xlsx,.xls" required
                       style="display:none;" onchange="mostrarNombreArchivo(this)">
                <button type="button"
                        onclick="document.getElementById('archivoExcel').click()"
                        style="background:#0b2d6b; color:white; font-weight:600; padding:10px 20px;
                               border:none; border-radius:7px; cursor:pointer;">
                    Examinar archivo
                </button>
                <p id="nombreArchivo"
                   style="margin-top:12px; font-weight:700; color:#10b981; font-size:13px; display:none;"></p>
            </div>

            {{-- Columnas requeridas (información) --}}
            <div style="background:#f1f5f9; border-radius:8px; padding:14px; border-left:4px solid #0b2d6b;">
                <h4 style="margin:0 0 10px; font-size:13px; font-weight:700; color:#1e293b;">
                    Columnas requeridas en el Excel:
                </h4>
                <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:5px; font-size:12px; color:#475569; font-weight:500;">
                    <div>• ci</div>
                    <div>• nombre</div>
                    <div>• apellido</div>
                    <div>• sexo (M/F)</div>
                    <div>• fecha_nacimiento</div>
                    <div>• telefono <em>(opcional)</em></div>
                    <div>• correo_electronico</div>
                    <div>• direccion <em>(opcional)</em></div>
                    <div>• carrera_principal</div>
                    <div>• carrera_secundaria <em>(opcional)</em></div>
                    <div>• gestion_anio</div>
                    <div>• gestion_periodo</div>
                    <div>• modalidad</div>
                    <div>• turno</div>
                </div>
                <p style="margin:10px 0 0; font-size:11.5px; color:#64748b;">
                    💡 El usuario se crea automáticamente con el correo como nombre de usuario y el CI como contraseña inicial.
                </p>
            </div>

            {{-- Botones del modal --}}
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                <a href="{{ route('inscripcion.descargarPlantilla') }}"
                   style="background:#3b82f6; color:white; font-weight:600; display:inline-flex; align-items:center;
                          gap:6px; text-decoration:none; padding:10px 16px; border-radius:7px; font-size:14px;">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Descargar Plantilla
                </a>

                <div style="display:flex; gap:12px;">
                    <button type="button" onclick="cerrarModalImportar()"
                            style="background:#e2e8f0; color:#475569; font-weight:600; padding:10px 20px;
                                   border:none; border-radius:7px; cursor:pointer;">
                        Cancelar
                    </button>
                    <button type="submit"
                            style="background:#10b981; color:white; font-weight:700; padding:10px 20px;
                                   border:none; border-radius:7px; cursor:pointer;">
                        Importar Postulantes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════
     SCRIPTS  (un solo bloque, sin duplicados)
     ══════════════════════════════════════════════════ --}}
<script>
/* ─── MODAL ─────────────────────────────────────────── */
function abrirModalImportar() {
    const modal = document.getElementById('modalImportar');
    modal.style.display = 'flex';
}

function cerrarModalImportar() {
    const modal = document.getElementById('modalImportar');
    modal.style.display = 'none';
    // Limpiar selección de archivo
    document.getElementById('archivoExcel').value = '';
    document.getElementById('nombreArchivo').style.display = 'none';
    document.getElementById('nombreArchivo').textContent = '';
    document.getElementById('dropZona').style.borderColor = '#cbd5e1';
    document.getElementById('dropZona').style.background  = '#f8fafc';
}

// Cerrar al hacer clic fuera del cuadro blanco
document.getElementById('modalImportar').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalImportar();
});

function mostrarNombreArchivo(input) {
    const etiqueta = document.getElementById('nombreArchivo');
    if (input.files && input.files[0]) {
        etiqueta.textContent = '✓ Seleccionado: ' + input.files[0].name;
        etiqueta.style.display = 'block';
    } else {
        etiqueta.style.display = 'none';
    }
}

function manejarSoltarArchivo(e) {
    e.preventDefault();
    const zona  = document.getElementById('dropZona');
    const input = document.getElementById('archivoExcel');
    zona.style.borderColor = '#cbd5e1';
    zona.style.background  = '#f8fafc';
    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
        // Asignar archivos al input
        const dt = new DataTransfer();
        dt.items.add(e.dataTransfer.files[0]);
        input.files = dt.files;
        mostrarNombreArchivo(input);
    }
}

/* ─── BÚSQUEDA EN VIVO (DROPDOWN) ───────────────────── */
document.addEventListener('DOMContentLoaded', function () {

    // Inicializar todos los contenedores search-select del formulario principal
    document.querySelectorAll('#formRegistrar [data-search-select]').forEach(function(contenedor) {
        const inputTexto = contenedor.querySelector('.search-input');
        const inputHidden = contenedor.querySelector('input[type="hidden"]');
        const panelOpciones = contenedor.querySelector('.search-options');
        const listaOpciones = contenedor.querySelectorAll('.search-option');

        // Mostrar panel al enfocar
        inputTexto.addEventListener('focus', function () {
            panelOpciones.classList.add('activo');
        });

        // Filtrar opciones mientras escribe
        inputTexto.addEventListener('input', function () {
            const busqueda = inputTexto.value.toLowerCase();
            inputHidden.value = '';
            listaOpciones.forEach(function(op) {
                op.style.display = op.textContent.toLowerCase().includes(busqueda) ? 'block' : 'none';
            });
            panelOpciones.classList.add('activo');
        });

        // Seleccionar opción
        listaOpciones.forEach(function(op) {
            op.addEventListener('click', function() {
                inputTexto.value  = op.textContent.trim();
                inputHidden.value = op.dataset.value;
                panelOpciones.classList.remove('activo');
            });
        });

        // Cerrar panel al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!contenedor.contains(e.target)) {
                panelOpciones.classList.remove('activo');
            }
        });

        // Validar al enviar formulario (solo carrera principal y gestión son requeridas)
        const esRequerido = inputHidden.hasAttribute('required');
        if (esRequerido) {
            document.getElementById('formRegistrar').addEventListener('submit', function(e) {
                if (!inputHidden.value) {
                    e.preventDefault();
                    alert('Debe seleccionar una opción de la lista: ' + (inputTexto.placeholder || ''));
                    inputTexto.focus();
                }
            }, { once: false });
        }
    });

    /* ─── BÚSQUEDA POR CI ──────────────────────────── */
    const inputCi  = document.getElementById('ci_postulante');
    const msgCi    = document.getElementById('mensaje_ci');

    if (inputCi) {
        inputCi.addEventListener('blur', function () {
            const ci = inputCi.value.trim();
            if (!ci) return;

            fetch("{{ url('/inscripcion/buscar-ci') }}/" + encodeURIComponent(ci))
                .then(r => r.json())
                .then(data => {
                    if (!data.existe) {
                        msgCi.style.display     = 'block';
                        msgCi.style.background  = '#fef3c7';
                        msgCi.style.color       = '#92400e';
                        msgCi.innerText = 'CI no encontrado. Se registrará como nuevo postulante.';
                        return;
                    }
                    const p = data.persona;
                    document.getElementById('nombre_postulante').value           = p.nombre     ?? '';
                    document.getElementById('apellido_postulante').value          = p.apellido   ?? '';
                    document.getElementById('sexo_postulante').value              = p.sexo       ?? '';
                    document.getElementById('fecha_nacimiento_postulante').value  = p.fecha_nacimiento ? p.fecha_nacimiento.substring(0, 10) : '';
                    document.getElementById('telefono_postulante').value          = p.telefono   ?? '';
                    document.getElementById('correo_postulante').value            = p.correo     ?? '';
                    document.getElementById('direccion_postulante').value         = p.direccion  ?? '';

                    msgCi.style.display    = 'block';
                    msgCi.style.background = '#d1fae5';
                    msgCi.style.color      = '#065f46';
                    msgCi.innerText = data.inscripcion
                        ? 'Persona encontrada. Ya tiene inscripción. Al guardar se actualizará.'
                        : 'Persona encontrada. Al guardar se registrará como postulante e inscripción.';
                })
                .catch(() => {
                    msgCi.style.display    = 'block';
                    msgCi.style.background = '#fee2e2';
                    msgCi.style.color      = '#991b1b';
                    msgCi.innerText = 'Error al buscar CI.';
                });
        });
    }

    /* ─── BUSCADOR DE TABLA ───────────────────────── */
    const buscadorTabla = document.getElementById('buscarInscripcion');
    const tablaInsc     = document.getElementById('tablaInscripciones');

    if (buscadorTabla && tablaInsc) {
        buscadorTabla.addEventListener('keyup', function () {
            const texto = buscadorTabla.value.toLowerCase();
            tablaInsc.querySelectorAll('tbody tr').forEach(function(fila) {
                fila.style.display = fila.textContent.toLowerCase().includes(texto) ? '' : 'none';
            });
        });
    }
});
</script>

@endsection