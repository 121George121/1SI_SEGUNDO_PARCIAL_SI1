@extends('Gestion_Academica.Menu')

@section('content')

<h1 class="titulo">CU06 - Gestionar Carreras y Cupos</h1>
<p class="subtitulo">Registrar carreras, asignar cupos, consultar cupos y deshabilitar carreras.</p>

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
        min-width: 1000px;
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

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

<div class="card-box">
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Registrar Carrera y Asignar Cupo</h2>

    @if($gestiones->count() == 0)
        <div class="alert-error">
            No existe una gestión activa. Primero registra una gestión en la tabla gestion.
        </div>
    @endif

    <form action="{{ route('carreras-cupos.store') }}" method="POST" class="form-grid">
        @csrf

        <div class="form-group">
            <label>Nombre de la Carrera</label>
            <input type="text" name="nombre_carrera" value="{{ old('nombre_carrera') }}" required>
        </div>

        <div class="form-group">
            <label>Estado</label>
            <select name="estado" required>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>

        <div class="form-group">
            <label>Gestión</label>
            <select name="id_gestion" required>
                <option value="">Seleccione una gestión</option>
                @foreach($gestiones as $gestion)
                    <option value="{{ $gestion->id_gestion }}">
                        {{ $gestion->anio }} - {{ $gestion->periodo }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Cantidad de Cupos</label>
            <input type="number" name="cantidad_cupos" min="0" value="{{ old('cantidad_cupos') }}" required>
        </div>

        <div class="form-group full">
            <label>Descripción</label>
            <textarea name="descripcion">{{ old('descripcion') }}</textarea>
        </div>

        <div class="form-group full">
            <button type="submit" class="btn-primary">Registrar Carrera y Cupos</button>
        </div>
    </form>
</div>

<div class="card-box">
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Carreras y Cupos Registrados</h2>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Carrera</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Gestión</th>
                    <th>Cupos</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($carreras as $item)
                    <tr>
                        <td>{{ $item->id_carrera }}</td>
                        <td>{{ $item->nombre_carrera }}</td>
                        <td>{{ $item->descripcion ?? 'Sin descripción' }}</td>
                        <td>
                            <span class="estado estado-{{ $item->estado }}">
                                {{ ucfirst($item->estado) }}
                            </span>
                        </td>
                        <td>
                            @if($item->id_gestion)
                                {{ $item->anio }} - {{ $item->periodo }}
                            @else
                                Sin gestión
                            @endif
                        </td>
                        <td>{{ $item->cantidad_cupos ?? 0 }}</td>

                        <td>
                            <div class="acciones">

                                <details>
                                    <summary>Editar carrera</summary>

                                    <form action="{{ route('carreras-cupos.update', $item->id_carrera) }}" method="POST" class="inline-form">
                                        @csrf
                                        @method('PUT')

                                        <input type="text" name="nombre_carrera" value="{{ $item->nombre_carrera }}" required>

                                        <select name="estado" required>
                                            <option value="activo" {{ $item->estado == 'activo' ? 'selected' : '' }}>Activo</option>
                                            <option value="inactivo" {{ $item->estado == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                        </select>

                                        <input type="text" name="descripcion" value="{{ $item->descripcion }}">

                                        <button type="submit" class="btn-warning">Actualizar</button>
                                    </form>
                                </details>

                                <form action="{{ route('carreras-cupos.cupos', $item->id_carrera) }}" method="POST" class="inline-form">
                                    @csrf
                                    @method('PUT')

                                    <select name="id_gestion" required>
                                        <option value="">Gestión</option>
                                        @foreach($gestiones as $gestion)
                                            <option value="{{ $gestion->id_gestion }}" {{ $item->id_gestion == $gestion->id_gestion ? 'selected' : '' }}>
                                                {{ $gestion->anio }} - {{ $gestion->periodo }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <input type="number" name="cantidad_cupos" min="0" value="{{ $item->cantidad_cupos ?? 0 }}" required>

                                    <button type="submit" class="btn-success">Actualizar Cupos</button>
                                </form>

                                @if($item->estado === 'activo')
                                    <form action="{{ route('carreras-cupos.deshabilitar', $item->id_carrera) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn-danger">Deshabilitar</button>
                                    </form>
                                @else
                                    <form action="{{ route('carreras-cupos.habilitar', $item->id_carrera) }}" method="POST">
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
                        <td colspan="7" style="text-align:center; padding:20px;">
                            No hay carreras registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection