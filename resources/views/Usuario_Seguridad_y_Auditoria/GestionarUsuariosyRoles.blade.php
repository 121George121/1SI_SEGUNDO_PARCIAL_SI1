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

@if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
@endif

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom:16px; flex-wrap: wrap; gap: 12px;">
    <a href="{{ route('usuarios.create') }}" class="btn btn-primary">
        Agregar Usuario
    </a>

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

@endsection