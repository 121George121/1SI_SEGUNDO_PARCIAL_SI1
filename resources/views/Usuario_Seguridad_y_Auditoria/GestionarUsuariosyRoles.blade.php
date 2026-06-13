@extends('Usuario_Seguridad_y_Auditoria.Menu')

@section('title', 'Usuarios y Roles')

@section('content')

<style>
    .tab-btn {
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.25s ease;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .tab-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.12);
    }
    .tab-btn:active {
        transform: translateY(0);
    }
</style>

<h2 style="font-size:26px;font-weight:bold;margin-bottom:12px;color:#0b2d6b;">
    CU2 - Usuarios y Roles
</h2>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('import_errors'))
    <div class="alert alert-error" style="background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; padding: 16px; border-radius: 8px; margin-bottom: 24px; font-size: 14px; font-weight: 600; text-align: left;">
        <p style="margin: 0 0 8px; font-weight: 700;">Errores durante la importación:</p>
        <ul style="margin: 0; padding-left: 20px; list-style-type: disc;">
            @foreach(session('import_errors') as $error)
                <li style="margin-bottom: 4px;">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
@endif

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom:16px; flex-wrap: wrap; gap: 12px;">
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="{{ route('usuarios.create') }}" class="btn btn-primary">
            Agregar Usuario
        </a>
        <button type="button" class="btn" style="background: #10b981; color: white; display: inline-flex; align-items: center; gap: 8px; font-weight: 600;" onclick="openImportModal()">
            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Agregar postulantes por Excel
        </button>
    </div>

    <div style="flex: 1; max-width: 350px;">
        <input 
            type="text" 
            id="buscarUsuario" 
            placeholder="Buscar por usuario, correo, nombre..."
            style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:14px; outline: none;"
        >
    </div>
</div>

<!-- Separador por roles en botones/pestañas -->
<div class="tab-navigation" style="display: flex; gap: 10px; margin-bottom: 18px; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; flex-wrap: wrap;">
    <button type="button" class="tab-btn" id="btn-user-todos" onclick="showUsers('Todos')" style="background: #0b2d6b; color: white;">
        TODOS
    </button>
    <button type="button" class="tab-btn" id="btn-user-super" onclick="showUsers('Superadministrador')" style="background: #e5e7eb; color: #333;">
        SUPERADMINISTRADOR
    </button>
    <button type="button" class="tab-btn" id="btn-user-admin" onclick="showUsers('Administrador')" style="background: #e5e7eb; color: #333;">
        ADMINISTRADOR
    </button>
    <button type="button" class="tab-btn" id="btn-user-docentes" onclick="showUsers('Docente')" style="background: #e5e7eb; color: #333;">
        DOCENTES
    </button>
    <button type="button" class="tab-btn" id="btn-user-postulantes" onclick="showUsers('Postulante')" style="background: #e5e7eb; color: #333;">
        POSTULANTES
    </button>
</div>

<table>
    <thead>
        <tr>
            <th>Usuario</th>
            <th>Correo</th>
            <th>Persona</th>
            <th>Roles</th>
            <th>F. Registro</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>

    <tbody>
        @forelse($usuarios as $usuario)
            <tr class="fila-usuario" data-roles="{{ json_encode($usuario->rolesLista()) }}">
                <td>{{ $usuario->nombre_usuario }}</td>
                <td>{{ $usuario->correo }}</td>

                <td>
                    @if($usuario->persona)
                        {{ $usuario->persona->nombre }} {{ $usuario->persona->apellido }}
                    @else
                        -
                    @endif
                </td>

                <td>
                    @forelse($usuario->rolesLista() as $rol)
                        <span style="display:inline-block;background:#dbeafe;color:#1e3a8a;padding:2px 8px;border-radius:4px;margin:2px;font-size:12px;">
                            {{ $rol }}
                        </span>
                    @empty
                        <span style="color:#888;">Sin roles</span>
                    @endforelse
                </td>

                <td>{{ $usuario->fecha_creacion }}</td>
                <td>{{ $usuario->estado }}</td>

                <td>
                    <a href="{{ route('usuarios.edit', $usuario->Id_usuario) }}" class="btn btn-secondary">
                        Editar
                    </a>

                    <a href="{{ route('usuarios.roles', $usuario->Id_usuario) }}" class="btn btn-primary">
                        Asignar roles
                    </a>

                    @if((int) auth()->id() !== (int) $usuario->Id_usuario)
                        <form action="{{ route('usuarios.destroy', $usuario->Id_usuario) }}"
                              method="POST"
                              style="display:inline;"
                              onsubmit="return confirm('¿Seguro que deseas eliminar este usuario y todos sus roles asociados?');">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn btn-danger">
                                Eliminar
                            </button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align:center;color:#666;">
                    No hay usuarios registrados.
                </td>
            </tr>
        @endforelse
        <tr id="no-users-row" style="display: none;">
            <td colspan="7" style="text-align:center; padding: 20px; color: #777;">
                No hay usuarios con este rol.
            </td>
        </tr>
    </tbody>
</table>

<script>
    function showUsers(role) {
        const filas = document.querySelectorAll('.fila-usuario');
        let count = 0;
        
        // Obtener texto de búsqueda si existe
        const buscador = document.getElementById('buscarUsuario');
        const filtroTexto = buscador ? buscador.value.toLowerCase() : '';

        filas.forEach(fila => {
            const roles = JSON.parse(fila.dataset.roles || '[]');
            const coincideRol = (role === 'Todos' || roles.includes(role));
            
            const contenidoFila = fila.textContent.toLowerCase();
            const coincideTexto = contenidoFila.includes(filtroTexto);

            if (coincideRol && coincideTexto) {
                fila.style.display = '';
                count++;
            } else {
                fila.style.display = 'none';
            }
        });

        const noRow = document.getElementById('no-users-row');
        if (noRow) {
            noRow.style.display = (count === 0) ? '' : 'none';
        }

        // Conmutar clases activas de los botones
        const btnIds = {
            'Todos': 'btn-user-todos',
            'Superadministrador': 'btn-user-super',
            'Administrador': 'btn-user-admin',
            'Docente': 'btn-user-docentes',
            'Postulante': 'btn-user-postulantes'
        };

        Object.keys(btnIds).forEach(key => {
            const btn = document.getElementById(btnIds[key]);
            if (btn) {
                if (key === role) {
                    btn.style.background = '#0b2d6b';
                    btn.style.color = 'white';
                } else {
                    btn.style.background = '#e5e7eb';
                    btn.style.color = '#333';
                }
            }
        });
        
        // Guardar el rol activo en un atributo temporal del buscador para combinar filtros
        if (buscador) {
            buscador.dataset.rolActivo = role;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const buscador = document.getElementById('buscarUsuario');
        if (buscador) {
            buscador.dataset.rolActivo = 'Todos';
            buscador.addEventListener('keyup', function () {
                const rolActivo = buscador.dataset.rolActivo || 'Todos';
                showUsers(rolActivo);
            });
        }
    });
</script>

<!-- Modal de Importación -->
<div id="importModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 16px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; border: 1px solid #e2e8f0; animation: modalFadeIn 0.3s ease-out; text-align: left;">
        <div style="background: #0b2d6b; color: white; padding: 20px; font-weight: 700; font-size: 18px; display: flex; justify-content: space-between; align-items: center;">
            <span style="display: flex; align-items: center; gap: 8px;">
                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Importar Postulantes por Excel
            </span>
            <button onclick="closeImportModal()" style="background: none; border: none; color: white; cursor: pointer; font-size: 24px; line-height: 1; outline: none;">&times;</button>
        </div>
        <form action="{{ route('usuarios.importarPostulantes') }}" method="POST" enctype="multipart/form-data" style="padding: 24px; display: flex; flex-direction: column; gap: 20px; margin: 0;">
            @csrf
            
            <div style="border: 2px dashed #cbd5e1; border-radius: 12px; padding: 32px 16px; text-align: center; cursor: pointer; background: #f8fafc; transition: all 0.2s;" 
                 ondragover="event.preventDefault(); this.style.borderColor='#0b2d6b'; this.style.background='#f0f4f8';" 
                 ondragleave="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';" 
                 ondrop="handleDrop(event); this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';">
                <svg style="width: 48px; height: 48px; color: #94a3b8; margin: 0 auto 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <p style="font-weight: 700; color: #334155; margin: 0 0 4px; font-size: 14.5px;">Selecciona o arrastra tu archivo Excel</p>
                <p style="font-size: 12px; color: #64748b; margin: 0 0 16px;">Formatos aceptados: .xlsx, .xls</p>
                <input type="file" name="excel_file" id="excel_file" accept=".xlsx, .xls" required style="display: none;" onchange="updateFileName(this)">
                <button type="button" class="btn" style="background: #0b2d6b; color: white; font-weight: 600;" onclick="document.getElementById('excel_file').click()">Examinar archivo</button>
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
                    <div>• nombre_usuario</div>
                    <div>• contraseña</div>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px; flex-wrap: wrap; gap: 12px;">
                <a href="{{ route('usuarios.descargarPlantilla') }}" class="btn" style="background: #3b82f6; color: white; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Descargar Plantilla
                </a>
                <div style="display: flex; gap: 12px;">
                    <button type="button" class="btn btn-secondary" onclick="closeImportModal()" style="font-weight: 600;">Cancelar</button>
                    <button type="submit" class="btn" style="background: #10b981; color: white; font-weight: 700;">Importar Postulantes</button>
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

<script>
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

@endsection