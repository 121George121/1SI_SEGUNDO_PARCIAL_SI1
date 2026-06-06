@extends('Usuario_Seguridad_y_Auditoria.Menu')

@section('title', 'Usuarios y Roles')

@section('content')

<h2 style="font-size:26px;font-weight:bold;margin-bottom:12px;color:#0b2d6b;">
    CU2 - Usuarios y Roles
</h2>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
@endif

<a href="{{ route('usuarios.create') }}" class="btn btn-primary" style="margin-bottom:16px;">
    Agregar Usuario
</a>

<table>
    <thead>
        <tr>
            <th>Usuario</th>
            <th>Correo</th>
            <th>Persona</th>
            <th>Roles</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>

    <tbody>
        @forelse($usuarios as $usuario)
            <tr>
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
                              onsubmit="return confirm('¿Desactivar este usuario?');">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn btn-danger">
                                Desactivar
                            </button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align:center;color:#666;">
                    No hay usuarios registrados.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

@endsection