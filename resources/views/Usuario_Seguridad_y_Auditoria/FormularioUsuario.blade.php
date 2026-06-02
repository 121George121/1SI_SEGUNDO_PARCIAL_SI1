@extends('Usuario_Seguridad_y_Auditoria.Menu')

@section('title', $usuario ? 'Editar usuario' : 'Registrar usuario')

@section('content')
@php
    $persona = $usuario?->persona;
    $rolesActuales = [];
    if ($persona) {
        foreach ($rolesDisponibles as $campo => $nombre) {
            if ($persona->{$campo}) {
                $rolesActuales[] = $campo;
            }
        }
    }
@endphp

<h2 style="font-size:24px;font-weight:bold;margin-bottom:12px;color:#0b2d6b;">
    {{ $usuario ? 'Editar usuario' : 'Registrar usuario' }}
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

<form method="POST" action="{{ $usuario ? route('usuarios.update', $usuario->Id_usuario) : route('usuarios.store') }}" style="background:#fff;padding:20px;border-radius:8px;max-width:720px;">
    @csrf
    @if($usuario)
        @method('PUT')
    @endif

    <h3 style="margin-bottom:10px;color:#082f6f;">Datos de acceso</h3>
    <p style="margin-bottom:8px;"><label>Nombre de usuario</label><br>
        <input type="text" name="nombre_usuario" value="{{ old('nombre_usuario', $usuario->nombre_usuario ?? '') }}" required style="width:100%;padding:8px;margin-top:4px;"></p>
    <p style="margin-bottom:8px;"><label>Correo</label><br>
        <input type="email" name="correo" value="{{ old('correo', $usuario->correo ?? '') }}" required style="width:100%;padding:8px;margin-top:4px;"></p>
    <p style="margin-bottom:8px;"><label>Contrasena {{ $usuario ? '(dejar vacio para no cambiar)' : '' }}</label><br>
        <input type="password" name="contrasena" {{ $usuario ? '' : 'required' }} style="width:100%;padding:8px;margin-top:4px;"></p>
    <p style="margin-bottom:16px;"><label>Confirmar contrasena</label><br>
        <input type="password" name="contrasena_confirmation" style="width:100%;padding:8px;margin-top:4px;"></p>

    @if($usuario)
        <p style="margin-bottom:16px;"><label>Estado</label><br>
            <select name="estado" style="width:100%;padding:8px;margin-top:4px;">
                <option value="activo" @selected(old('estado', $usuario->estado) === 'activo')>activo</option>
                <option value="inactivo" @selected(old('estado', $usuario->estado) === 'inactivo')>inactivo</option>
            </select>
        </p>
    @endif

    <h3 style="margin-bottom:10px;color:#082f6f;">Datos de persona</h3>
    <p style="margin-bottom:8px;"><label>CI</label><br>
        <input type="text" name="ci" value="{{ old('ci', $persona->ci ?? '') }}" required style="width:100%;padding:8px;margin-top:4px;"></p>
    <p style="margin-bottom:8px;"><label>Nombre</label><br>
        <input type="text" name="nombre" value="{{ old('nombre', $persona->nombre ?? '') }}" required style="width:100%;padding:8px;margin-top:4px;"></p>
    <p style="margin-bottom:8px;"><label>Apellido</label><br>
        <input type="text" name="apellido" value="{{ old('apellido', $persona->apellido ?? '') }}" required style="width:100%;padding:8px;margin-top:4px;"></p>
    <p style="margin-bottom:8px;"><label>Sexo</label><br>
        <select name="sexo" style="width:100%;padding:8px;margin-top:4px;">
            <option value="">-</option>
            <option value="M" @selected(old('sexo', $persona->sexo ?? '') === 'M')>M</option>
            <option value="F" @selected(old('sexo', $persona->sexo ?? '') === 'F')>F</option>
        </select>
    </p>
    <p style="margin-bottom:8px;"><label>Fecha de nacimiento</label><br>
        <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $persona->fecha_nacimiento ?? '') }}" required style="width:100%;padding:8px;margin-top:4px;"></p>
    <p style="margin-bottom:8px;"><label>Telefono</label><br>
        <input type="text" name="telefono" value="{{ old('telefono', $persona->telefono ?? '') }}" style="width:100%;padding:8px;margin-top:4px;"></p>
    <p style="margin-bottom:16px;"><label>Direccion</label><br>
        <input type="text" name="direccion" value="{{ old('direccion', $persona->direccion ?? '') }}" style="width:100%;padding:8px;margin-top:4px;"></p>

    <h3 style="margin-bottom:10px;color:#082f6f;">Roles</h3>
  <div style="margin-bottom:16px;">
        @foreach($rolesDisponibles as $campo => $nombre)
            <label style="display:block;margin-bottom:6px;">
                <input type="checkbox" name="roles[]" value="{{ $campo }}"
                    @checked(in_array($campo, old('roles', $rolesActuales), true))>
                {{ $nombre }}
            </label>
        @endforeach
    </div>

    <button type="submit" class="btn btn-primary">{{ $usuario ? 'Actualizar' : 'Guardar' }}</button>
    <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
</form>
@endsection
