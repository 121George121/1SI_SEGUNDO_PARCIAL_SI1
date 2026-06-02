@extends('Usuario_Seguridad_y_Auditoria.Menu')

@section('title', 'Asignar roles')

@section('content')
@php
    $persona = $usuario->persona;
    $rolesActuales = [];
    if ($persona) {
        foreach ($rolesDisponibles as $campo => $nombre) {
            if ($persona->{$campo}) {
                $rolesActuales[] = $campo;
            }
        }
    }
@endphp

<h2 style="font-size:24px;font-weight:bold;margin-bottom:12px;color:#0b2d6b;">Asignar roles</h2>
<p style="margin-bottom:16px;">Usuario: <strong>{{ $usuario->nombre_usuario }}</strong> ({{ $usuario->correo }})</p>

@if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('usuarios.roles.update', $usuario->Id_usuario) }}" style="background:#fff;padding:20px;border-radius:8px;max-width:480px;">
    @csrf

    @foreach($rolesDisponibles as $campo => $nombre)
        <label style="display:block;margin-bottom:10px;">
            <input type="checkbox" name="roles[]" value="{{ $campo }}"
                @checked(in_array($campo, old('roles', $rolesActuales), true))>
            {{ $nombre }}
        </label>
    @endforeach

    <button type="submit" class="btn btn-primary" style="margin-top:12px;">Guardar roles</button>
    <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Volver</a>
</form>
@endsection
