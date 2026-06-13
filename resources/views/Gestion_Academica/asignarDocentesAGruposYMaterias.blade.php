@extends('Gestion_Academica.Menu')

@section('title', 'CU15 - Asignar Docentes a Grupos y Materias')

@section('content')

<h1 class="titulo">CU15 - Asignar Docentes a Grupos y Materias</h1>
<p class="subtitulo">Asignar docentes disponibles a grupos y materias.</p>

<div style="margin-bottom: 20px;">
    <a href="{{ route('asignaciones-docentes.autogenerar.view') }}" class="btn-primary" style="background: #10b981; display: inline-flex; align-items: center; justify-content: center; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 14px; transition: background 0.2s; border: none; color: white;">
        Asignación Automática de Docentes
    </a>
</div>

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
        grid-template-columns: repeat(3, 1fr);
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
        grid-column: 1 / 4;
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
        min-width: 950px;
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

    .acciones {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .inline-form {
        display: grid;
        grid-template-columns: repeat(3, minmax(120px, 1fr));
        gap: 8px;
    }

    .inline-form select {
        padding: 8px;
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
        .form-grid,
        .inline-form {
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
    <div class="alert-error">
        <ul style="margin-left:18px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card-box">
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Registrar Asignación</h2>

    <form action="{{ route('asignaciones-docentes.store') }}" method="POST" class="form-grid">
        @csrf

        <div class="form-group">
            <label>Grupo</label>
            <select name="Id_grupo" id="reg_grupo" required>
                <option value="">Seleccione grupo</option>
                @foreach($grupos as $grupo)
                    <option value="{{ $grupo->id_grupo }}" {{ old('Id_grupo') == $grupo->id_grupo ? 'selected' : '' }}>
                        {{ $grupo->sigla_grupo }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Materia</label>
            <select name="Id_materia" id="reg_materia" required>
                <option value="">Seleccione materia</option>
                @foreach($materias as $materia)
                    <option value="{{ $materia->id_materia }}" {{ old('Id_materia') == $materia->id_materia ? 'selected' : '' }}>
                        {{ $materia->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Docente</label>
            <select name="Id_docente" id="reg_docente" required>
                <option value="">Seleccione docente</option>
                @foreach($docentes as $docente)
                    <option value="{{ $docente->id_docente }}" {{ old('Id_docente') == $docente->id_docente ? 'selected' : '' }}>
                        {{ $docente->nombre }} {{ $docente->apellido }} - CI: {{ $docente->ci }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group full">
            <button type="submit" class="btn-primary">
                Confirmar Asignación
            </button>
        </div>
    </form>
</div>

<div class="card-box">
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Asignaciones Registradas</h2>

    <div style="margin-bottom:16px;">
        <label style="font-weight:bold;color:#0b2d6b;">Buscar Asignación</label>
        <input type="text"
               id="buscarAsignacion"
               placeholder="Buscar por grupo, materia, docente o CI..."
               style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;margin-top:6px;">
    </div>

    <div class="table-responsive">
        <table id="tablaAsignaciones">
            <thead>
                <tr>
                    <th>Grupo</th>
                    <th>Materia</th>
                    <th>Docente</th>
                    <th>CI</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($asignaciones as $asignacion)
                    <tr>
                        <td>{{ $asignacion->sigla_grupo }}</td>
                        <td>{{ $asignacion->nombre_materia }}</td>
                        <td>{{ $asignacion->nombre }} {{ $asignacion->apellido }}</td>
                        <td>{{ $asignacion->ci }}</td>

                        <td>
                            <div class="acciones">
                                <details>
                                    <summary>Editar Asignación</summary>

                                    <form action="{{ route('asignaciones-docentes.update', [$asignacion->id_grupo, $asignacion->id_materia]) }}"
                                          method="POST"
                                          class="inline-form">
                                        @csrf
                                        @method('PUT')

                                        <select name="Id_grupo" required>
                                            @foreach($grupos as $grupo)
                                                <option value="{{ $grupo->id_grupo }}"
                                                    {{ $asignacion->id_grupo == $grupo->id_grupo ? 'selected' : '' }}>
                                                    {{ $grupo->sigla_grupo }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <select name="Id_materia" required>
                                            @foreach($materias as $materia)
                                                <option value="{{ $materia->id_materia }}"
                                                    {{ $asignacion->id_materia == $materia->id_materia ? 'selected' : '' }}>
                                                    {{ $materia->nombre }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <select name="Id_docente" required>
                                            @foreach($docentes as $docente)
                                                <option value="{{ $docente->id_docente }}"
                                                    {{ $asignacion->id_docente == $docente->id_docente ? 'selected' : '' }}>
                                                    {{ $docente->nombre }} {{ $docente->apellido }} - CI: {{ $docente->ci }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <button type="submit" class="btn-warning">
                                            Actualizar
                                        </button>
                                    </form>
                                </details>

                                <form action="{{ route('asignaciones-docentes.destroy', [$asignacion->id_grupo, $asignacion->id_materia]) }}"
                                      method="POST"
                                      onsubmit="return confirm('¿Seguro que deseas eliminar esta asignación?');">
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
                        <td colspan="5" style="text-align:center;">
                            No hay asignaciones registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Buscador de Asignaciones
    const buscador = document.getElementById('buscarAsignacion');
    const tabla = document.getElementById('tablaAsignaciones');

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

    // 2. Filtros Interactivos del Formulario de Registro Manual
    const selectGrupo = document.getElementById('reg_grupo');
    const selectMateria = document.getElementById('reg_materia');
    const selectDocente = document.getElementById('reg_docente');

    if (selectGrupo && selectMateria && selectDocente) {
        const specialities = @json($docenteMaterias);
        const existingAssignments = @json($existingAssignments);

        function updateFormFilters() {
            const valGrupo = selectGrupo.value;
            const valMateria = selectMateria.value;
            const valDocente = selectDocente.value;

            // --- A. Filtrar GRUPOS ---
            for (let i = 1; i < selectGrupo.options.length; i++) {
                const opt = selectGrupo.options[i];
                const gId = opt.value;
                let allowed = true;

                // Si hay materia seleccionada, el grupo no debe tener esa materia asignada
                if (valMateria) {
                    const isAssigned = existingAssignments.some(a => a.id_grupo == gId && a.id_materia == valMateria);
                    if (isAssigned) allowed = false;
                }

                opt.disabled = !allowed;
                opt.style.display = allowed ? '' : 'none';
            }

            // --- B. Filtrar MATERIAS ---
            for (let i = 1; i < selectMateria.options.length; i++) {
                const opt = selectMateria.options[i];
                const mId = opt.value;
                let allowed = true;

                // El docente debe estar habilitado para la materia (si hay docente seleccionado)
                if (valDocente) {
                    const hasSpeciality = specialities.some(s => s.id_docente == valDocente && s.id_materia == mId);
                    if (!hasSpeciality) allowed = false;
                }

                // El grupo seleccionado no debe tener esta materia registrada (si hay grupo seleccionado)
                if (valGrupo) {
                    const isAssigned = existingAssignments.some(a => a.id_grupo == valGrupo && a.id_materia == mId);
                    if (isAssigned) allowed = false;
                }

                opt.disabled = !allowed;
                opt.style.display = allowed ? '' : 'none';
            }

            // --- C. Filtrar DOCENTES ---
            for (let i = 1; i < selectDocente.options.length; i++) {
                const opt = selectDocente.options[i];
                const dId = opt.value;
                let allowed = true;

                // El docente debe tener especialidad en la materia seleccionada (si hay materia seleccionada)
                if (valMateria) {
                    const hasSpeciality = specialities.some(s => s.id_docente == dId && s.id_materia == valMateria);
                    if (!hasSpeciality) allowed = false;
                }

                opt.disabled = !allowed;
                opt.style.display = allowed ? '' : 'none';
            }

            // Resetear valor si la opción seleccionada quedó deshabilitada
            if (selectGrupo.selectedOptions[0] && selectGrupo.selectedOptions[0].disabled) {
                selectGrupo.value = "";
            }
            if (selectMateria.selectedOptions[0] && selectMateria.selectedOptions[0].disabled) {
                selectMateria.value = "";
            }
            if (selectDocente.selectedOptions[0] && selectDocente.selectedOptions[0].disabled) {
                selectDocente.value = "";
            }
        }

        // Registrar eventos
        selectGrupo.addEventListener('change', updateFormFilters);
        selectMateria.addEventListener('change', updateFormFilters);
        selectDocente.addEventListener('change', updateFormFilters);

        // Inicializar al cargar la página (por si hay valores previos del old())
        updateFormFilters();
    }
});
</script>

@endsection