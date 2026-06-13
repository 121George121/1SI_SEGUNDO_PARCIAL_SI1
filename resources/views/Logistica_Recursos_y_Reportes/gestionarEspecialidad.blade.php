@extends('Logistica_Recursos_y_Reportes.Menu')

@section('content')

<h1 class="titulo">Gestionar Especialidades</h1>
<p class="subtitulo">Registrar especialidades para los docentes y administrar el catálogo general.</p>

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
        grid-template-columns: 1fr 1fr;
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
    .form-group select {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
        background-color: white;
    }

    .btn-primary,
    .btn-warning,
    .btn-danger {
        border: none;
        padding: 9px 12px;
        border-radius: 7px;
        color: white;
        cursor: pointer;
        font-weight: bold;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary { background: #0b2d6b; }
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
        min-width: 600px;
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
        vertical-align: middle;
    }

    .acciones {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .inline-form {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .inline-form input,
    .inline-form select {
        padding: 7px;
        border: 1px solid #ccc;
        border-radius: 6px;
        background-color: white;
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
    }
</style>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

<div class="card-box">
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Registrar Especialidad</h2>

    <form action="{{ route('especialidades.store') }}" method="POST" class="form-grid">
        @csrf

        <div class="form-group">
            <label>Nombre de Especialidad</label>
            <input type="text" name="nombre_especialidad" value="{{ old('nombre_especialidad') }}" placeholder="Ej: Redes y Telecomunicaciones" required>
        </div>

        <div class="form-group">
            <label>Materia Relacionada</label>
            <select name="Id_materia" required>
                <option value="">Seleccione Materia</option>
                @foreach($materias as $m)
                    <option value="{{ $m->id_materia }}" {{ old('Id_materia') == $m->id_materia ? 'selected' : '' }}>
                        {{ $m->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group" style="grid-column: 1 / -1; margin-top: 10px;">
            <button type="submit" class="btn-primary" style="align-self: flex-start;">Registrar Especialidad</button>
        </div>
    </form>
</div>

<div class="card-box">
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Especialidades Registradas</h2>

    <div style="margin-bottom:16px;">
        <label style="font-weight:bold; color:#0b2d6b; display:block; margin-bottom:6px;">Buscar Especialidad</label>
        <input type="text" id="buscarEspecialidad" placeholder="Buscar por nombre..." style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:14px;">
    </div>

    <div class="table-responsive">
        <table id="tablaEspecialidades">
            <thead>
                <tr>
                    <th style="width: 100px;">ID</th>
                    <th>Nombre de Especialidad</th>
                    <th>Materia Relacionada</th>
                    <th style="width: 250px;">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($especialidades as $item)
                    <tr>
                        <td>{{ $item->id_especialidad }}</td>
                        <td>{{ $item->nombre_especialidad }}</td>
                        <td>
                            <span style="background: #f1f5f9; padding: 4px 8px; border-radius: 6px; font-weight: bold; color: #0b2d6b; border: 1px solid #e2e8f0;">
                                {{ $item->nombre_materia ?? 'Sin Materia' }}
                            </span>
                        </td>
                        <td>
                            <div class="acciones">

                                <details style="display: inline-block;">
                                    <summary class="btn-warning" style="list-style: none; padding: 6px 12px; border-radius: 6px; color: white; margin-bottom: 0;">Editar</summary>

                                    <form action="{{ route('especialidades.update', $item->id_especialidad) }}" method="POST" class="inline-form" style="position: absolute; background: white; border: 1px solid #ccc; padding: 12px; border-radius: 8px; z-index: 10; width: 280px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); margin-top: 5px;">
                                        @csrf
                                        @method('PUT')

                                        <div style="margin-bottom: 8px;">
                                            <label style="font-weight: bold; font-size: 12px; color: #666; display: block; margin-bottom: 4px;">Nombre Especialidad</label>
                                            <input type="text" name="nombre_especialidad" value="{{ $item->nombre_especialidad }}" required style="width: 100%;">
                                        </div>

                                        <div style="margin-bottom: 8px;">
                                            <label style="font-weight: bold; font-size: 12px; color: #666; display: block; margin-bottom: 4px;">Materia</label>
                                            <select name="Id_materia" required style="width: 100%;">
                                                @foreach($materias as $m)
                                                    <option value="{{ $m->id_materia }}" {{ $item->id_materia == $m->id_materia ? 'selected' : '' }}>
                                                        {{ $m->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <button type="submit" class="btn-warning" style="width: 100%;">Actualizar</button>
                                    </form>
                                </details>

                                <form action="{{ route('especialidades.destroy', $item->id_especialidad) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta especialidad? También se desvinculará de los docentes.')" style="margin: 0; display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger" style="padding: 6px 12px; border-radius: 6px;">Eliminar</button>
                                </form>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align:center; padding:20px;">
                            No hay especialidades registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const buscador = document.getElementById('buscarEspecialidad');
    const tabla = document.getElementById('tablaEspecialidades');

    if (!buscador || !tabla) return;

    buscador.addEventListener('keyup', function () {
        const texto = buscador.value.toLowerCase();
        const filas = tabla.querySelectorAll('tbody tr');

        filas.forEach(function (fila) {
            const contenido = fila.textContent.toLowerCase();
            fila.style.display = contenido.includes(texto) ? '' : 'none';
        });
    });
});
</script>

@endsection
