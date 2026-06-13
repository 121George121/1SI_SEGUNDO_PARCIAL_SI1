@extends('Logistica_Recursos_y_Reportes.Menu')

@section('content')

<h1 class="titulo">CU09 - Gestionar Docentes</h1>
<p class="subtitulo">Registrar docentes, validar documentos, asignar especialidad y actualizar información.</p>

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

    .table-responsive {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1200px;
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

    .estado {
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: bold;
        display: inline-block;
    }

    .estado-activo {
        background: #d1fae5;
        color: #065f46;
    }

    .estado-inactivo {
        background: #fee2e2;
        color: #991b1b;
    }

    .acciones {
        display: flex;
        flex-direction: column;
        gap: 8px;
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

    details summary {
        cursor: pointer;
        font-weight: bold;
        color: #0b2d6b;
        margin-bottom: 8px;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .full {
            grid-column: 1;
        }
    }
</style>

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

<div class="card-box">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
        <h2 style="color:#0b2d6b; margin: 0;">Registrar Docente</h2>
        <button type="button" class="btn-success" style="background: #10b981; display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; font-size: 14px; border: none; border-radius: 7px; color: white; cursor: pointer; font-weight: bold;" onclick="openImportModal()">
            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Agregar docentes por Excel
        </button>
    </div>

    <form action="{{ route('docentes.store') }}" method="POST" class="form-grid">
        @csrf

        <div class="form-group">
            <label>CI</label>
            <input type="text" name="ci" required>
        </div>

        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="nombre" required>
        </div>

        <div class="form-group">
            <label>Apellido</label>
            <input type="text" name="apellido" required>
        </div>

        <div class="form-group">
            <label>Sexo</label>
            <select name="sexo">
                <option value="">Seleccione</option>
                <option value="M">Masculino</option>
                <option value="F">Femenino</option>
            </select>
        </div>

        <div class="form-group">
            <label>Fecha de Nacimiento</label>
            <input type="date" name="fecha_nacimiento" required>
        </div>

        <div class="form-group">
            <label>Teléfono</label>
            <input type="text" name="telefono">
        </div>

        <div class="form-group">
            <label>Correo</label>
            <input type="email" name="correo">
        </div>

        <div class="form-group">
            <label>Años de Servicio</label>
            <input type="number" name="anio_servicio" min="0" required>
        </div>

        <div class="form-group">
            <label>Estado</label>
            <select name="estado" required>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>

        <div class="form-group">
            <label>Especialidades</label>
            <select name="especialidades[]" multiple>
                @foreach($especialidades as $especialidad)
                    <option value="{{ $especialidad->id_especialidad }}">
                        {{ $especialidad->nombre_especialidad }}
                    </option>
                @endforeach
            </select>
            <small>Mantén presionado CTRL para seleccionar varias especialidades.</small>
        </div>

        <div class="form-group full">
            <label>Dirección</label>
            <textarea name="direccion"></textarea>
        </div>

        <div class="form-group full">
            <button type="submit" class="btn-primary">Registrar Docente</button>
        </div>
    </form>
</div>

<div class="card-box">
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Docentes Registrados</h2>
    <div style="margin-bottom:16px;">
    <label style="font-weight:bold; color:#0b2d6b; display:block; margin-bottom:6px;">
        Buscar Docente Registrado
    </label>

    <input 
        type="text" 
        id="buscarDocente" 
        placeholder="Buscar por CI, nombre, apellido, correo, teléfono, especialidad o estado..."
        style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:14px;"
    >
</div>

    <div class="table-responsive">
        <table id="tablaDocentes">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>CI</th>
                    <th>Docente</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Años Servicio</th>
                    <th>Especialidades</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($docentes as $docente)
                    <tr>
                        <td>{{ $docente->id_docente }}</td>
                        <td>{{ $docente->ci }}</td>
                        <td>{{ $docente->nombre }} {{ $docente->apellido }}</td>
                        <td>{{ $docente->correo ?? 'Sin correo' }}</td>
                        <td>{{ $docente->telefono ?? 'Sin teléfono' }}</td>
                        <td>{{ $docente->anio_servicio }}</td>
                        <td>{{ $docente->especialidades }}</td>
                        <td>
                            <span class="estado estado-{{ $docente->estado }}">
                                {{ ucfirst($docente->estado) }}
                            </span>
                        </td>

                        <td>
                            <div class="acciones">
                                <a href="{{ route('docentes.documentos.form', $docente->id_docente) }}" class="btn-success">
                                    Validar Documentos
                                </a>
                                <details>
                                    <summary>Actualizar Docente</summary>

                                    <form action="{{ route('docentes.update', $docente->id_docente) }}" method="POST" class="inline-form">
                                        @csrf
                                        @method('PUT')

                                        <input type="text" name="ci" value="{{ $docente->ci }}" required>
                                        <input type="text" name="nombre" value="{{ $docente->nombre }}" required>
                                        <input type="text" name="apellido" value="{{ $docente->apellido }}" required>

                                        <select name="sexo">
                                            <option value="">Sexo</option>
                                            <option value="M" {{ $docente->sexo == 'M' ? 'selected' : '' }}>M</option>
                                            <option value="F" {{ $docente->sexo == 'F' ? 'selected' : '' }}>F</option>
                                        </select>

                                        <input type="date" name="fecha_nacimiento" value="{{ $docente->fecha_nacimiento }}" required>
                                        <input type="text" name="telefono" value="{{ $docente->telefono }}">
                                        <input type="email" name="correo" value="{{ $docente->correo }}">
                                        <input type="number" name="anio_servicio" min="0" value="{{ $docente->anio_servicio }}" required>

                                        <input type="hidden" name="estado" value="En_Revision">

                                        <input type="text" name="direccion" value="{{ $docente->direccion }}">

                                        <select name="especialidades[]" multiple>
                                            @foreach($especialidades as $especialidad)
                                                <option value="{{ $especialidad->id_especialidad }}">
                                                    {{ $especialidad->nombre_especialidad }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <button type="submit" class="btn-warning">Actualizar</button>
                                    </form>
                                </details>

                                @if($docente->estado === 'activo')
                                    <form action="{{ route('docentes.deshabilitar', $docente->id_docente) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn-danger">Deshabilitar</button>
                                    </form>
                                @else
                                    <form action="{{ route('docentes.habilitar', $docente->id_docente) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn-success">Habilitar</button>
                                    </form>
                                @endif

                                <form action="{{ route('docentes.destroy', $docente->id_docente) }}" 
                                    method="POST"
                                    onsubmit="return confirm('¿Seguro que deseas eliminar este docente? También se eliminará su usuario y sus especialidades asignadas.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center; padding:20px;">
                            No hay docentes registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const buscador = document.getElementById('buscarDocente');
    const tabla = document.getElementById('tablaDocentes');

    if (!buscador || !tabla) return;

    buscador.addEventListener('keyup', function () {
        const texto = buscador.value.toLowerCase();
        const filas = tabla.querySelectorAll('tbody tr');

        filas.forEach(function (fila) {
            const contenidoFila = fila.textContent.toLowerCase();

            if (contenidoFila.includes(texto)) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    });
});

function openImportModal() {
    document.getElementById('importModal').style.display = 'flex';
}

function closeImportModal() {
    document.getElementById('importModal').style.display = 'none';
    document.getElementById('excel_file').value = '';
    document.getElementById('fileNameDisplay').style.display = 'none';
}

function updateFileName(input) {
    const display = document.getElementById('fileNameDisplay');
    if (input.files && input.files[0]) {
        display.textContent = '✓ Seleccionado: ' + input.files[0].name;
        display.style.display = 'block';
    } else {
        display.style.display = 'none';
    }
}

function handleDrop(e) {
    e.preventDefault();
    const input = document.getElementById('excel_file');
    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
        input.files = e.dataTransfer.files;
        updateFileName(input);
    }
}
</script>

<!-- Modal de Importación -->
<div id="importModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 16px; width: 100%; max-width: 550px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; border: 1px solid #e2e8f0; animation: modalFadeIn 0.3s ease-out; text-align: left;">
        <div style="background: #0b2d6b; color: white; padding: 20px; font-weight: 700; font-size: 18px; display: flex; justify-content: space-between; align-items: center;">
            <span style="display: flex; align-items: center; gap: 8px;">
                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Importar Docentes por Excel
            </span>
            <button onclick="closeImportModal()" style="background: none; border: none; color: white; cursor: pointer; font-size: 24px; line-height: 1; outline: none;">&times;</button>
        </div>
        <form action="{{ route('docentes.importarDocentes') }}" method="POST" enctype="multipart/form-data" style="padding: 24px; display: flex; flex-direction: column; gap: 20px; margin: 0;">
            @csrf
            
            <div style="border: 2px dashed #cbd5e1; border-radius: 12px; padding: 32px 16px; text-align: center; cursor: pointer; background: #f8fafc; transition: all 0.2s;" 
                 ondragover="event.preventDefault(); this.style.borderColor='#0b2d6b'; this.style.background='#f0f4f8';" 
                 ondragleave="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';" 
                 ondrop="handleDrop(event); this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';">
                <svg style="width: 48px; height: 48px; color: #94a3b8; margin: 0 auto 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <p style="font-weight: 700; color: #334155; margin: 0 0 4px; font-size: 14.5px;">Selecciona o arrastra tu archivo Excel</p>
                <p style="font-size: 12px; color: #64748b; margin: 0 0 16px;">Formatos aceptados: .xlsx, .xls</p>
                <input type="file" name="excel_file" id="excel_file" accept=".xlsx, .xls" required style="display: none;" onchange="updateFileName(this)">
                <button type="button" class="btn-success" style="background: #0b2d6b; color: white; font-weight: 600; padding: 10px 16px; border: none; border-radius: 7px; cursor: pointer;" onclick="document.getElementById('excel_file').click()">Examinar archivo</button>
                <p id="fileNameDisplay" style="margin-top: 12px; font-weight: 750; color: #10b981; font-size: 13.5px; display: none;"></p>
            </div>

            <div style="background: #f1f5f9; border-radius: 8px; padding: 14px; border-left: 4px solid #0b2d6b;">
                <h4 style="margin: 0 0 8px; font-size: 13px; font-weight: 750; color: #1e293b;">Columnas requeridas en el archivo:</h4>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 6px; font-size: 12px; color: #475569; font-weight: 550;">
                    <div>• ci</div>
                    <div>• nombre</div>
                    <div>• apellido</div>
                    <div>• sexo (M/F)</div>
                    <div>• fecha_nacimiento</div>
                    <div>• telefono (opcional)</div>
                    <div>• correo_electronico</div>
                    <div>• direccion (opcional)</div>
                    <div>• anio_servicio</div>
                    <div>• especialidades (opcional, separadas por coma)</div>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px; flex-wrap: wrap; gap: 12px;">
                <a href="{{ route('docentes.descargarPlantilla') }}" class="btn-success" style="background: #3b82f6; color: white; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; padding: 10px 16px; border-radius: 7px;">
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Descargar Plantilla
                </a>
                <div style="display: flex; gap: 12px;">
                    <button type="button" class="btn-danger" onclick="closeImportModal()" style="font-weight: 600; padding: 10px 16px; border: none; border-radius: 7px; cursor: pointer;">Cancelar</button>
                    <button type="submit" class="btn-success" style="background: #10b981; color: white; font-weight: 700; padding: 10px 16px; border: none; border-radius: 7px; cursor: pointer;">Importar Docentes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
</style>
@endsection