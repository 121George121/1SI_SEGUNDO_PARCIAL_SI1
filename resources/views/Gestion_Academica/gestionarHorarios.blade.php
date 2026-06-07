@extends('Gestion_Academica.Menu')

@section('title', 'CU14 - Gestionar Horarios')

@section('content')

<h1 class="titulo">CU14 - Gestionar Horarios</h1>
<p class="subtitulo">Registrar, editar y eliminar horarios.</p>

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
        color: #0b2d6b;
        font-weight: bold;
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
        min-width: 850px;
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

    .estado-ok {
        background: #d1fae5;
        color: #065f46;
    }

    .estado-error {
        background: #fee2e2;
        color: #991b1b;
    }

    .acciones {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    details summary {
        cursor: pointer;
        color: #0b2d6b;
        font-weight: bold;
        margin-bottom: 8px;
    }

    .inline-form {
        display: grid;
        grid-template-columns: repeat(2, minmax(120px, 1fr));
        gap: 8px;
    }

    .inline-form input,
    .inline-form select {
        padding: 7px;
        border: 1px solid #ccc;
        border-radius: 6px;
    }
</style>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

<div class="card-box">
    <h2 style="color:#0b2d6b;margin-bottom:16px;">Registrar Horario</h2>

    <form action="{{ route('horarios.store') }}" method="POST" class="form-grid">
        @csrf

        <div class="form-group">
            <label>Día</label>
            <select name="dia" required>
                <option value="">Seleccione</option>
                <option value="Lunes">Lunes</option>
                <option value="Martes">Martes</option>
                <option value="Miércoles">Miércoles</option>
                <option value="Jueves">Jueves</option>
                <option value="Viernes">Viernes</option>
                <option value="Sábado">Sábado</option>
            </select>
        </div>

        <div class="form-group">
            <label>Estado</label>
            <select name="estado" required>
                <option value="activo">activo</option>
                <option value="inactivo">inactivo</option>
            </select>
        </div>

        <div class="form-group">
            <label>Hora Inicio</label>
            <input type="time" name="hora_inicio" required>
        </div>

        <div class="form-group">
            <label>Hora Fin</label>
            <input type="time" name="hora_fin" required>
        </div>

        <div class="form-group full">
            <button type="submit" class="btn-primary">
                Registrar Horario
            </button>
        </div>
    </form>
</div>

<div class="card-box">
    <h2 style="color:#0b2d6b;margin-bottom:16px;">Horarios Registrados</h2>

    <div style="margin-bottom:16px;">
        <label style="font-weight:bold;color:#0b2d6b;">Buscar Horario</label>
        <input type="text" id="buscarHorario" placeholder="Buscar por día, hora o estado..."
               style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;margin-top:6px;">
    </div>

    <div class="table-responsive">
        <table id="tablaHorarios">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Día</th>
                    <th>Hora Inicio</th>
                    <th>Hora Fin</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($horarios as $horario)
                    <tr>
                        <td>{{ $horario->id_horario }}</td>
                        <td>{{ $horario->dia }}</td>
                        <td>{{ substr($horario->hora_inicio, 0, 5) }}</td>
                        <td>{{ substr($horario->hora_fin, 0, 5) }}</td>
                        <td>
                            <span class="estado {{ strtolower(trim($horario->estado)) === 'activo' ? 'estado-ok' : 'estado-error' }}">
                                {{ $horario->estado }}
                            </span>
                        </td>
                        <td>
                            <div class="acciones">
                                <details>
                                    <summary>Editar Horario</summary>

                                    <form action="{{ route('horarios.update', $horario->id_horario) }}" method="POST" class="inline-form">
                                        @csrf
                                        @method('PUT')

                                        <select name="dia" required>
                                            @foreach(['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'] as $dia)
                                                <option value="{{ $dia }}" {{ $horario->dia == $dia ? 'selected' : '' }}>
                                                    {{ $dia }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <select name="estado" required>
                                            <option value="activo" {{ strtolower(trim($horario->estado)) === 'activo' ? 'selected' : '' }}>activo</option>
                                            <option value="inactivo" {{ strtolower(trim($horario->estado)) === 'inactivo' ? 'selected' : '' }}>inactivo</option>
                                        </select>

                                        <input type="time" name="hora_inicio" value="{{ substr($horario->hora_inicio, 0, 5) }}" required>
                                        <input type="time" name="hora_fin" value="{{ substr($horario->hora_fin, 0, 5) }}" required>

                                        <button type="submit" class="btn-warning">
                                            Actualizar
                                        </button>
                                    </form>
                                </details>

                                <form action="{{ route('horarios.destroy', $horario->id_horario) }}"
                                      method="POST"
                                      onsubmit="return confirm('¿Seguro que deseas eliminar este horario?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn-danger">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;">No hay horarios registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const buscador = document.getElementById('buscarHorario');
    const tabla = document.getElementById('tablaHorarios');

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