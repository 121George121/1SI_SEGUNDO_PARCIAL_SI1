@extends('Gestion_Academica.Menu')

@section('content')

<h1 class="titulo">CU11 - Gestionar Grupos</h1>
<p class="subtitulo">Registrar grupos, editar datos, asignar aula, modalidad, turno y validar capacidad.</p>

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
        min-width: 1200px;
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
    }
    .search-select {
    position: relative;
}

.search-input {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
    width: 100%;
}

.search-options {
    display: none;
    position: absolute;
    top: 72px;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ccc;
    border-radius: 8px;
    max-height: 180px;
    overflow-y: auto;
    z-index: 999;
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.search-options.activo {
    display: block;
}

.search-option {
    padding: 10px;
    cursor: pointer;
    color: #333;
}

.search-option:hover {
    background: #e5e7eb;
}
</style>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

<div class="card-box">
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Registrar Grupo</h2>

    <form action="{{ route('grupos.store') }}" method="POST" class="form-grid">
        @csrf

        <div class="form-group">
            <label>Sigla del Grupo</label>
            <input type="text" name="sigla_grupo" placeholder="Ej: G-01" required>
        </div>

        <div class="form-group">
            <label>Capacidad Máxima</label>
            <input type="number" name="capacidad_max" min="1" placeholder="Ej: 40" required>
        </div>

        <div class="form-group">
            <label>Cantidad de Estudiantes</label>
            <input type="number" name="cant_estudiantes" min="0" value="0" required>
        </div>

        <div class="form-group">
            <label>Estado</label>
            <select name="estado" required>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>

        <div class="form-group search-select" data-search-select>
            <label>Aula</label>

            <input type="text" class="search-input" placeholder="Buscar aula..." autocomplete="off" required>
            <input type="hidden" name="Id_aula">

            <div class="search-options">
                @foreach($aulas as $aula)
                    <div class="search-option" data-value="{{ $aula->id_aula }}">
                        {{ $aula->nro_aula }} - Capacidad: {{ $aula->capacidad }}
                    </div>
                @endforeach
            </div>
        </div>

        <div class="form-group search-select" data-search-select>
            <label>Modalidad</label>

            <input type="text" class="search-input" placeholder="Buscar modalidad..." autocomplete="off" required>
            <input type="hidden" name="Id_modalidad">

            <div class="search-options">
                @foreach($modalidades as $modalidad)
                    <div class="search-option" data-value="{{ $modalidad->id_modalidad }}">
                        {{ $modalidad->nombre_modalidad }}
                    </div>
                @endforeach
            </div>
        </div>

        <div class="form-group search-select" data-search-select>
            <label>Turno</label>

            <input type="text" class="search-input" placeholder="Buscar turno..." autocomplete="off" required>
            <input type="hidden" name="Id_turno">

            <div class="search-options">
                @foreach($turnos as $turno)
                    <div class="search-option" data-value="{{ $turno->id_turno }}">
                        {{ $turno->nombre }}
                    </div>
                @endforeach
            </div>
        </div>

        <div class="form-group search-select" data-search-select>
            <label>Docente</label>

            <input type="text" class="search-input" placeholder="Buscar docente..." autocomplete="off" required>
            <input type="hidden" name="Id_docente">

            <div class="search-options">
                @foreach($docentes as $docente)
                    <div class="search-option" data-value="{{ $docente->id_docente }}">
                        {{ $docente->nombre }} {{ $docente->apellido }}
                    </div>
                @endforeach
            </div>
        </div>

       <div class="form-group search-select" data-search-select>
            <label>Gestión</label>

            <input type="text" class="search-input" placeholder="Buscar gestión..." autocomplete="off" required>
            <input type="hidden" name="Id_gestion">

            <div class="search-options">
                @foreach($gestiones as $gestion)
                    <div class="search-option" data-value="{{ $gestion->id_gestion }}">
                        {{ $gestion->anio }} - {{ $gestion->periodo }}
                    </div>
                @endforeach
            </div>
        </div>

        <div class="form-group">
            <label>&nbsp;</label>
            <button type="submit" class="btn-primary">Registrar Grupo</button>
        </div>
    </form>
</div>

<div class="card-box">
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Grupos Registrados</h2>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Grupo</th>
                    <th>Capacidad</th>
                    <th>Estudiantes</th>
                    <th>Aula</th>
                    <th>Modalidad</th>
                    <th>Turno</th>
                    <th>Docente</th>
                    <th>Gestión</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($grupos as $grupo)
                    <tr>
                        <td>{{ $grupo->id_grupo }}</td>
                        <td>{{ $grupo->sigla_grupo }}</td>
                        <td>{{ $grupo->capacidad_max }}</td>
                        <td>{{ $grupo->cant_estudiantes }}</td>
                        <td>{{ $grupo->nro_aula ?? 'Sin aula' }}</td>
                        <td>{{ $grupo->nombre_modalidad ?? 'Sin modalidad' }}</td>
                        <td>{{ $grupo->nombre_turno ?? 'Sin turno' }}</td>
                        <td>
                            @if($grupo->nombre_docente)
                                {{ $grupo->nombre_docente }} {{ $grupo->apellido_docente }}
                            @else
                                Sin docente
                            @endif
                        </td>
                        <td>
                            @if($grupo->anio)
                                {{ $grupo->anio }} - {{ $grupo->periodo }}
                            @else
                                Sin gestión
                            @endif
                        </td>
                        <td>
                            <span class="estado estado-{{ $grupo->estado }}">
                                {{ ucfirst($grupo->estado) }}
                            </span>
                        </td>

                        <td>
                            <div class="acciones">
                                <details>
                                    <summary>Editar Grupo</summary>

                                    <form action="{{ route('grupos.update', $grupo->id_grupo) }}" method="POST" class="inline-form">
                                        @csrf
                                        @method('PUT')

                                        <input type="text" name="sigla_grupo" value="{{ $grupo->sigla_grupo }}" required>
                                        <input type="number" name="capacidad_max" min="1" value="{{ $grupo->capacidad_max }}" required>
                                        <input type="number" name="cant_estudiantes" min="0" value="{{ $grupo->cant_estudiantes }}" required>

                                        <select name="estado" required>
                                            <option value="activo" {{ $grupo->estado == 'activo' ? 'selected' : '' }}>Activo</option>
                                            <option value="inactivo" {{ $grupo->estado == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                        </select>

                                        <select name="Id_aula" required>
                                            @foreach($aulas as $aula)
                                                <option value="{{ $aula->id_aula }}" {{ $grupo->id_aula == $aula->id_aula ? 'selected' : '' }}>
                                                    {{ $aula->nro_aula }} - Cap: {{ $aula->capacidad }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <select name="Id_modalidad" required>
                                            @foreach($modalidades as $modalidad)
                                                <option value="{{ $modalidad->id_modalidad }}" {{ $grupo->id_modalidad == $modalidad->id_modalidad ? 'selected' : '' }}>
                                                    {{ $modalidad->nombre_modalidad }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <select name="Id_turno" required>
                                            @foreach($turnos as $turno)
                                                <option value="{{ $turno->id_turno }}" {{ $grupo->id_turno == $turno->id_turno ? 'selected' : '' }}>
                                                    {{ $turno->nombre }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <select name="Id_docente" required>
                                            @foreach($docentes as $docente)
                                                <option value="{{ $docente->id_docente }}" {{ $grupo->id_docente == $docente->id_docente ? 'selected' : '' }}>
                                                    {{ $docente->nombre }} {{ $docente->apellido }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <select name="Id_gestion" required>
                                            @foreach($gestiones as $gestion)
                                                <option value="{{ $gestion->id_gestion }}" {{ $grupo->id_gestion == $gestion->id_gestion ? 'selected' : '' }}>
                                                    {{ $gestion->anio }} - {{ $gestion->periodo }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <button type="submit" class="btn-warning">Actualizar</button>
                                    </form>
                                </details>

                                @if($grupo->estado === 'activo')
                                    <form action="{{ route('grupos.deshabilitar', $grupo->id_grupo) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn-danger">Deshabilitar</button>
                                    </form>
                                @else
                                    <form action="{{ route('grupos.habilitar', $grupo->id_grupo) }}" method="POST">
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
                        <td colspan="11" style="text-align:center; padding:20px;">
                            No hay grupos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


<script>
document.querySelectorAll('[data-search-select]').forEach(function (contenedor) {
    const input = contenedor.querySelector('.search-input');
    const hidden = contenedor.querySelector('input[type="hidden"]');
    const opciones = contenedor.querySelector('.search-options');
    const items = contenedor.querySelectorAll('.search-option');

    input.addEventListener('focus', function () {
        opciones.classList.add('activo');
    });

    input.addEventListener('input', function () {
        const texto = input.value.toLowerCase();

        hidden.value = '';

        items.forEach(function (item) {
            const coincide = item.textContent.toLowerCase().includes(texto);
            item.style.display = coincide ? 'block' : 'none';
        });

        opciones.classList.add('activo');
    });

    items.forEach(function (item) {
        item.addEventListener('click', function () {
            input.value = item.textContent.trim();
            hidden.value = item.dataset.value;
            opciones.classList.remove('activo');
        });
    });

    document.addEventListener('click', function (e) {
        if (!contenedor.contains(e.target)) {
            opciones.classList.remove('activo');
        }
    });
});
</script>
@endsection