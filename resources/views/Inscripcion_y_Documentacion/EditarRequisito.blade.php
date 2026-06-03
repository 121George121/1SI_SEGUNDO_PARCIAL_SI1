@extends('Inscripcion_y_Documentacion.Menu')

@section('title', 'Editar requisito documental')

@section('content')
<style>
    .page-title { font-size: 26px; color: #0b2d6b; font-weight: 800; margin-bottom: 16px; }
    .doc-container { background: #fff; padding: 22px; border-radius: 12px; box-shadow: 0 4px 14px rgba(0,0,0,0.08); max-width: 720px; }
    .doc-form { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
    .form-group { display: flex; flex-direction: column; }
    .form-group label { font-weight: bold; color: #0b2d6b; margin-bottom: 6px; }
    .form-group input, .form-group select, .form-group textarea { padding: 10px; border: 1px solid #ccc; border-radius: 8px; }
    .form-group textarea { resize: vertical; min-height: 70px; }
    .full { grid-column: 1 / 3; }
    .btn-primary { background: #0b2d6b; color: white; border: none; padding: 11px 16px; border-radius: 8px; cursor: pointer; font-weight: bold; }
    .btn-secondary { background: #e5e7eb; color: #111; padding: 11px 16px; border-radius: 8px; text-decoration: none; display: inline-block; margin-left: 8px; }
    .alert-error { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
</style>

<h1 class="page-title">Editar requisito documental</h1>

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

<div class="doc-container">
    <form action="{{ route('documentos.update', $requisito->Id_documento) }}" method="POST" class="doc-form">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Tipo de documento</label>
            <input type="text" name="tipo_documento" value="{{ old('tipo_documento', $requisito->tipo_documento) }}" required>
        </div>

        <div class="form-group">
            <label>Nombre del documento</label>
            <input type="text" name="nombre" value="{{ old('nombre', $requisito->nombre) }}" required>
        </div>

        <div class="form-group">
            <label>Destinado a</label>
            <select name="destinado_a" required>
                <option value="Postulantes" @selected(old('destinado_a', $requisito->destinado_a) === 'Postulantes')>Postulantes</option>
                <option value="Docentes" @selected(old('destinado_a', $requisito->destinado_a) === 'Docentes')>Docentes</option>
            </select>
        </div>

        <div class="form-group full">
            <label>Descripcion</label>
            <textarea name="descripcion">{{ old('descripcion', $requisito->descripcion) }}</textarea>
        </div>

        <div class="form-group full">
            <button type="submit" class="btn-primary">Guardar cambios</button>
            <a href="{{ route('documentos.index') }}" class="btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
