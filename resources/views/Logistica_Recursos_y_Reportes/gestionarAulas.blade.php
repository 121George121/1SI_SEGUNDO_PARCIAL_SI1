@extends('Logistica_Recursos_y_Reportes.Menu')

@section('content')

<h1 class="titulo">CU08 - Gestionar Aulas</h1>
<p class="subtitulo">Registrar aulas, editar datos, asignar capacidad y consultar disponibilidad.</p>

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
    .form-group select {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
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
        min-width: 900px;
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
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
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
    }
</style>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

<div class="card-box">
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Registrar Aula</h2>

    <form action="{{ route('aulas.store') }}" method="POST" class="form-grid">
        @csrf

        <div class="form-group">
            <label>Número de Aula</label>
            <input type="text" name="nro_aula" value="{{ old('nro_aula') }}" placeholder="Ej: Aula 101" required>
        </div>

        <div class="form-group">
            <label>Capacidad</label>
            <input type="number" name="capacidad" min="1" value="{{ old('capacidad') }}" placeholder="Ej: 40" required>
        </div>

        <div class="form-group">
            <label>Ubicación</label>
            <input type="text" name="ubicacion" value="{{ old('ubicacion') }}" placeholder="Ej: Módulo 2 - Planta baja">
        </div>

        <div class="form-group">
            <label>Estado</label>
            <select name="estado" required>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>

        <div class="form-group">
            <button type="submit" class="btn-primary">Registrar Aula</button>
        </div>
    </form>
</div>

<div class="card-box">
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Aulas Registradas</h2>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nro Aula</th>
                    <th>Capacidad</th>
                    <th>Ubicación</th>
                    <th>Disponibilidad</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($aulas as $aula)
                    <tr>
                        <td>{{ $aula->id_aula }}</td>
                        <td>{{ $aula->nro_aula }}</td>
                        <td>{{ $aula->capacidad }}</td>
                        <td>{{ $aula->ubicacion ?? 'Sin ubicación' }}</td>
                        <td>
                            <span class="estado estado-{{ $aula->estado }}">
                                {{ ucfirst($aula->estado) }}
                            </span>
                        </td>
                        <td>
                            <div class="acciones">

                                <details>
                                    <summary>Editar Aula</summary>

                                    <form action="{{ route('aulas.update', $aula->id_aula) }}" method="POST" class="inline-form">
                                        @csrf
                                        @method('PUT')

                                        <input type="text" name="nro_aula" value="{{ $aula->nro_aula }}" required>
                                        <input type="number" name="capacidad" min="1" value="{{ $aula->capacidad }}" required>
                                        <input type="text" name="ubicacion" value="{{ $aula->ubicacion }}">

                                        <select name="estado" required>
                                            <option value="activo" {{ $aula->estado == 'activo' ? 'selected' : '' }}>Activo</option>
                                            <option value="inactivo" {{ $aula->estado == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                        </select>

                                        <button type="submit" class="btn-warning">Actualizar</button>
                                    </form>
                                </details>

                                <form action="{{ route('aulas.capacidad', $aula->id_aula) }}" method="POST" class="inline-form">
                                    @csrf
                                    @method('PUT')

                                    <input type="number" name="capacidad" min="1" value="{{ $aula->capacidad }}" required>

                                    <button type="submit" class="btn-success">Asignar Capacidad</button>
                                </form>

                                @if($aula->estado === 'activo')
                                    <form action="{{ route('aulas.deshabilitar', $aula->id_aula) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn-danger">Deshabilitar</button>
                                    </form>
                                @else
                                    <form action="{{ route('aulas.habilitar', $aula->id_aula) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn-success">Habilitar</button>
                                    </form>
                                @endif

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding:20px;">
                            No hay aulas registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection