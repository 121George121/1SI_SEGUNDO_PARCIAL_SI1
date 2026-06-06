@extends('Usuario_Seguridad_y_Auditoria.Menu')

@section('title', $usuario ? 'Editar usuario' : 'Agregar usuario')

@section('content')

@php
    $persona = $usuario?->persona;
    $administrador = $administrador ?? null;

    $rolesActuales = [];

    if ($persona) {
        foreach ($rolesDisponibles as $campo => $nombre) {
            if ($persona->{$campo}) {
                $rolesActuales[] = $campo;
            }
        }
    }
@endphp

<h2 style="font-size:26px;font-weight:bold;margin-bottom:12px;color:#0b2d6b;">
    {{ $usuario ? 'Editar Usuario' : 'Agregar Usuario' }}
</h2>

@if($errors->any())
    <div class="alert alert-error">
        <ul style="margin-left:18px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST"
      action="{{ $usuario ? route('usuarios.update', $usuario->Id_usuario) : route('usuarios.store') }}"
      style="background:#fff;padding:20px;border-radius:8px;max-width:760px;">

    @csrf

    @if($usuario)
        @method('PUT')
    @endif

    <h3 style="margin-bottom:10px;color:#082f6f;">Datos de Persona</h3>

    <p style="margin-bottom:8px;">
        <label>CI</label><br>
        <input type="text" name="ci" value="{{ old('ci', $persona->ci ?? '') }}" required style="width:100%;padding:8px;margin-top:4px;">
    </p>

    <p style="margin-bottom:8px;">
        <label>Nombre</label><br>
        <input type="text" name="nombre" value="{{ old('nombre', $persona->nombre ?? '') }}" required style="width:100%;padding:8px;margin-top:4px;">
    </p>

    <p style="margin-bottom:8px;">
        <label>Apellido</label><br>
        <input type="text" name="apellido" value="{{ old('apellido', $persona->apellido ?? '') }}" required style="width:100%;padding:8px;margin-top:4px;">
    </p>

    <p style="margin-bottom:8px;">
        <label>Sexo</label><br>
        <select name="sexo" style="width:100%;padding:8px;margin-top:4px;">
            <option value="">Seleccione</option>
            <option value="M" @selected(old('sexo', $persona->sexo ?? '') === 'M')>Masculino</option>
            <option value="F" @selected(old('sexo', $persona->sexo ?? '') === 'F')>Femenino</option>
        </select>
    </p>

    <p style="margin-bottom:8px;">
        <label>Fecha de nacimiento</label><br>
        <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $persona->fecha_nacimiento ?? '') }}" required style="width:100%;padding:8px;margin-top:4px;">
    </p>

    <p style="margin-bottom:8px;">
        <label>Teléfono</label><br>
        <input type="text" name="telefono" value="{{ old('telefono', $persona->telefono ?? '') }}" style="width:100%;padding:8px;margin-top:4px;">
    </p>

    <p style="margin-bottom:8px;">
        <label>Correo</label><br>
        <input type="email" name="correo" value="{{ old('correo', $usuario->correo ?? $persona->correo ?? '') }}" required style="width:100%;padding:8px;margin-top:4px;">
    </p>

    <p style="margin-bottom:16px;">
        <label>Dirección</label><br>
        <input type="text" name="direccion" value="{{ old('direccion', $persona->direccion ?? '') }}" style="width:100%;padding:8px;margin-top:4px;">
    </p>

    <h3 style="margin-bottom:10px;color:#082f6f;">Datos de Usuario</h3>

    <p style="margin-bottom:8px;">
        <label>Nombre de usuario</label><br>
        <input type="text" name="nombre_usuario" value="{{ old('nombre_usuario', $usuario->nombre_usuario ?? '') }}" required style="width:100%;padding:8px;margin-top:4px;">
    </p>

    <p style="margin-bottom:8px;">
        <label>Contraseña {{ $usuario ? '(dejar vacío para no cambiar)' : '' }}</label><br>
        <input type="password" name="contrasena" {{ $usuario ? '' : 'required' }} style="width:100%;padding:8px;margin-top:4px;">
    </p>

    <p style="margin-bottom:16px;">
        <label>Confirmar contraseña</label><br>
        <input type="password" name="contrasena_confirmation" style="width:100%;padding:8px;margin-top:4px;">
    </p>

    @if($usuario)
        <p style="margin-bottom:16px;">
            <label>Estado Usuario</label><br>
            <select name="estado" style="width:100%;padding:8px;margin-top:4px;">
                <option value="activo" @selected(old('estado', $usuario->estado) === 'activo')>activo</option>
                <option value="inactivo" @selected(old('estado', $usuario->estado) === 'inactivo')>inactivo</option>
            </select>
        </p>
    @endif

    <h3 style="margin-bottom:10px;color:#082f6f;">Rol</h3>

    <div style="margin-bottom:16px;">
        @foreach($rolesDisponibles as $campo => $nombre)
            <label style="display:block;margin-bottom:6px;">
                <input type="checkbox" name="roles[]" value="{{ $campo }}"
                    @checked(in_array($campo, old('roles', $rolesActuales), true))>
                {{ $nombre }}
            </label>
        @endforeach
    </div>

    <div id="camposAdministrador" style="display:none;margin-top:20px;margin-bottom:20px;background:#f8fafc;padding:18px;border-radius:10px;border:1px solid #dbeafe;">
        <h3 style="color:#0b2d6b;margin-bottom:12px;">
            Datos de Administrador / Superadministrador
        </h3>

        <p style="margin-bottom:8px;">
            <label>Cargo</label><br>
            <input type="text"
                   name="cargo"
                   value="{{ old('cargo', $administrador->cargo ?? '') }}"
                   placeholder="Ej: Administrador Académico"
                   style="width:100%;padding:8px;margin-top:4px;">
        </p>

        <p style="margin-bottom:8px;">
            <label>Área</label><br>
            <input type="text"
                   name="area"
                   value="{{ old('area', $administrador->area ?? '') }}"
                   placeholder="Ej: Usuario, Seguridad y Auditoría"
                   style="width:100%;padding:8px;margin-top:4px;">
        </p>

        <p style="margin-bottom:8px;">
            <label>Estado Administrador</label><br>
            <select name="estado_administrador" style="width:100%;padding:8px;margin-top:4px;">
                <option value="activo" @selected(old('estado_administrador', $administrador->estado ?? 'activo') === 'activo')>
                    activo
                </option>
                <option value="inactivo" @selected(old('estado_administrador', $administrador->estado ?? '') === 'inactivo')>
                    inactivo
                </option>
            </select>
        </p>
    </div>

    <button type="submit" class="btn btn-primary">
        {{ $usuario ? 'Actualizar' : 'Guardar' }}
    </button>

    <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
        Cancelar
    </a>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const camposAdministrador = document.getElementById('camposAdministrador');

    function verificarAdministrador() {
        const admin = document.querySelector('input[name="roles[]"][value="tipo_Administrador"]');
        const superadmin = document.querySelector('input[name="roles[]"][value="tipo_Superadministrador"]');

        const esAdmin = admin && admin.checked;
        const esSuperadmin = superadmin && superadmin.checked;

        camposAdministrador.style.display = (esAdmin || esSuperadmin) ? 'block' : 'none';
    }

    document.querySelectorAll('input[name="roles[]"]').forEach(function (checkbox) {
        checkbox.addEventListener('change', verificarAdministrador);
    });

    verificarAdministrador();
});
</script>

@endsection