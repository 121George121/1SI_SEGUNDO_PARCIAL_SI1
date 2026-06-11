@extends('Gestion_Academica.Menu')

@section('content')

<h1 class="titulo">Gestionar Gestiones</h1>
<p class="subtitulo">Registrar gestiones académicas, definir periodos, fechas de inicio y fin, y administrar su estado.</p>

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

    .full {
        grid-column: 1 / 3;
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
        display: grid;
        grid-template-columns: repeat(2, minmax(120px, 1fr));
        gap: 6px;
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
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Registrar Gestión</h2>

    <form action="{{ route('gestiones.store') }}" method="POST" class="form-grid">
        @csrf

        <div class="form-group">
            <label>Año</label>
            <input type="number" name="anio" min="2000" max="2100" value="{{ date('Y') }}" required>
        </div>

        <div class="form-group">
            <label>Periodo</label>
            <input type="text" name="periodo" placeholder="Ej: I/2026, II/2026, Semestre 1" required>
        </div>

        <div class="form-group">
            <label>Fecha de Inicio</label>
            <input type="date" name="fecha_inicio">
        </div>

        <div class="form-group">
            <label>Fecha de Fin</label>
            <input type="date" name="fecha_fin">
        </div>

        <div class="form-group">
            <label>Estado</label>
            <select name="estado" required>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>

        <div class="form-group full" style="margin-top: 10px;">
            <button type="submit" class="btn-primary">
                Registrar Gestión
            </button>
        </div>
    </form>
</div>

<div class="card-box">
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Gestiones Registradas</h2>

    <div style="margin-bottom:16px;">
        <label style="font-weight:bold;color:#0b2d6b;">Buscar Gestión</label>
        <input type="text" id="buscarGestion" placeholder="Buscar por año, periodo o estado..."
               style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;margin-top:6px;">
    </div>

    <div class="table-responsive">
        <table id="tablaGestiones">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Año</th>
                    <th>Periodo</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($gestiones as $item)
                    <tr>
                        <td>{{ $item->Id_gestion }}</td>
                        <td>{{ $item->anio }}</td>
                        <td>{{ $item->periodo }}</td>
                        <td>{{ $item->fecha_inicio ?? '-' }}</td>
                        <td>{{ $item->fecha_fin ?? '-' }}</td>
                        <td>
                            <span class="estado {{ strtolower(trim($item->estado)) === 'activo' ? 'estado-activo' : 'estado-inactivo' }}">
                                {{ ucfirst($item->estado) }}
                            </span>
                        </td>
                        <td>
                            <div class="acciones">
                                <details>
                                    <summary>Editar Gestión</summary>

                                    <form action="{{ route('gestiones.update', $item->Id_gestion) }}" method="POST" class="inline-form">
                                        @csrf
                                        @method('PUT')

                                        <div style="margin-bottom: 6px;">
                                            <label style="font-size: 11px; font-weight: bold; color: #0b2d6b;">Año</label>
                                            <input type="number" name="anio" value="{{ $item->anio }}" required style="width: 100%;">
                                        </div>

                                        <div style="margin-bottom: 6px;">
                                            <label style="font-size: 11px; font-weight: bold; color: #0b2d6b;">Periodo</label>
                                            <input type="text" name="periodo" value="{{ $item->periodo }}" required style="width: 100%;">
                                        </div>

                                        <div style="margin-bottom: 6px;">
                                            <label style="font-size: 11px; font-weight: bold; color: #0b2d6b;">F. Inicio</label>
                                            <input type="date" name="fecha_inicio" value="{{ $item->fecha_inicio }}" style="width: 100%;">
                                        </div>

                                        <div style="margin-bottom: 6px;">
                                            <label style="font-size: 11px; font-weight: bold; color: #0b2d6b;">F. Fin</label>
                                            <input type="date" name="fecha_fin" value="{{ $item->fecha_fin }}" style="width: 100%;">
                                        </div>

                                        <div style="margin-bottom: 6px; grid-column: 1 / 3;">
                                            <label style="font-size: 11px; font-weight: bold; color: #0b2d6b;">Estado</label>
                                            <select name="estado" required style="width: 100%;">
                                                <option value="activo" {{ strtolower(trim($item->estado)) === 'activo' ? 'selected' : '' }}>Activo</option>
                                                <option value="inactivo" {{ strtolower(trim($item->estado)) === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                            </select>
                                        </div>

                                        <button type="submit" class="btn-warning" style="grid-column: 1 / 3; width: 100%; margin-top: 6px;">
                                            Actualizar
                                        </button>
                                    </form>
                                </details>

                                <form action="{{ route('gestiones.destroy', $item->Id_gestion) }}"
                                      method="POST"
                                      onsubmit="return confirm('¿Seguro que deseas eliminar esta gestión académica?');">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn-danger" style="width: 100%;">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;">No hay gestiones registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const buscador = document.getElementById('buscarGestion');
    const tabla = document.getElementById('tablaGestiones');

    if (buscador && tabla) {
        buscador.addEventListener('keyup', function () {
            const texto = buscador.value.toLowerCase();
            const filas = tabla.querySelectorAll('tbody tr');

            filas.forEach(function (fila) {
                const contenido = fila.textContent.toLowerCase();
                fila.style.display = contenido.includes(texto) ? '' : 'none';
            });
        });
    }
});
</script>

@endsection
