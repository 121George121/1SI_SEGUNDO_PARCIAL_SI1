@extends('Gestion_Academica.Menu')

@section('title', 'CU16 - Gestionar Evaluaciones y Notas')

@section('content')

@php
    $tab = request('tab', 'evaluaciones');
@endphp

<h1 class="titulo">CU16 - Gestionar Evaluaciones y Notas</h1>
<p class="subtitulo">Registrar evaluaciones, registrar notas y consultar resultados académicos.</p>

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
        vertical-align: middle;
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

    .estado-aprobado {
        background: #d1fae5;
        color: #065f46;
    }

    .estado-reprobado {
        background: #fee2e2;
        color: #991b1b;
    }

    .estado-observado {
        background: #fef3c7;
        color: #92400e;
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
        grid-template-columns: repeat(2, minmax(130px, 1fr));
        gap: 8px;
    }

    .inline-form input,
    .inline-form select {
        padding: 7px;
        border: 1px solid #ccc;
        border-radius: 6px;
    }

    .tab-btn {
        text-decoration: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: bold;
        font-size: 14px;
        transition: all 0.25s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
    }
    .tab-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
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

<!-- Navegación de Pestañas -->
<div class="tab-navigation" style="display: flex; gap: 10px; margin-bottom: 24px; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px;">
    <a href="{{ route('evaluaciones-notas.index', ['tab' => 'evaluaciones']) }}" 
       class="tab-btn" 
       style="background-color: {{ $tab === 'evaluaciones' ? '#0b2d6b' : '#e2e8f0' }}; 
              color: {{ $tab === 'evaluaciones' ? 'white' : '#334155' }};">
        GESTIONAR EVALUACIONES
    </a>
    <a href="{{ route('evaluaciones-notas.index', ['tab' => 'notas']) }}" 
       class="tab-btn" 
       style="background-color: {{ $tab === 'notas' ? '#0b2d6b' : '#e2e8f0' }}; 
              color: {{ $tab === 'notas' ? 'white' : '#334155' }};">
        CALIFICAR ESTUDIANTES
    </a>
</div>

<!-- CONTENIDO PESTAÑA: EVALUACIONES -->
@if($tab === 'evaluaciones')
    <div class="card-box">
        <h2 style="color:#0b2d6b;margin-bottom:16px;">Registrar Evaluación</h2>

        <form action="{{ route('evaluaciones.store') }}" method="POST" class="form-grid formulario-evaluacion">
            @csrf

            <div class="form-group full">
                <label>Grupo y Materia</label>
                <select class="select-grupo-materia" required style="background-color: white;">
                    <option value="">Seleccione grupo y materia</option>
                    @foreach($gruposMaterias as $gm)
                        <option value="{{ $gm->id_grupo }}|{{ $gm->id_materia }}"
                            data-grupo="{{ $gm->id_grupo }}"
                            data-materia="{{ $gm->id_materia }}">
                            {{ $gm->sigla_grupo }} - {{ $gm->nombre_materia }}
                            | Docente: {{ $gm->nombre_docente }} {{ $gm->apellido_docente }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="Id_grupo" class="input-id-grupo" value="{{ old('Id_grupo') }}">
                <input type="hidden" name="Id_materia" class="input-id-materia" value="{{ old('Id_materia') }}">
            </div>

            <div class="form-group">
                <label>Número de Evaluación</label>
                <input type="number" name="numero_evaluacion" min="1" value="{{ old('numero_evaluacion') }}" required>
            </div>

            <div class="form-group">
                <label>Porcentaje</label>
                <input type="number" step="0.01" name="porcentaje" min="1" max="100" value="{{ old('porcentaje') }}" required>
            </div>

            <div class="form-group">
                <label>Fecha</label>
                <input type="date" name="fecha" value="{{ old('fecha') }}" required>
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="estado" required style="background-color: white;">
                    <option value="activo">activo</option>
                    <option value="inactivo">inactivo</option>
                </select>
            </div>

            <div class="form-group full">
                <button type="submit" class="btn-primary" style="cursor: pointer;">
                    Registrar Evaluación
                </button>
            </div>
        </form>
    </div>

    <div class="card-box">
        <h2 style="color:#0b2d6b;margin-bottom:16px;">Evaluaciones Registradas</h2>

        <div style="margin-bottom:16px;">
            <label style="font-weight:bold;color:#0b2d6b;">Buscar Evaluación</label>
            <input type="text" id="buscarEvaluacion" placeholder="Buscar por grupo, materia, evaluación, fecha o estado..."
                   style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;margin-top:6px; outline:none;">
        </div>

        <div class="table-responsive">
            <table id="tablaEvaluaciones">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Grupo</th>
                        <th>Materia</th>
                        <th>Nro. Evaluación</th>
                        <th>Porcentaje</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($evaluaciones as $evaluacion)
                        <tr>
                            <td>{{ $evaluacion->id_evaluacion }}</td>
                            <td>{{ $evaluacion->sigla_grupo }}</td>
                            <td>{{ $evaluacion->nombre_materia }}</td>
                            <td>{{ $evaluacion->numero_evaluacion }}</td>
                            <td>{{ $evaluacion->porcentaje }}%</td>
                            <td>{{ $evaluacion->fecha }}</td>
                            <td>
                                <span class="estado {{ strtolower(trim($evaluacion->estado)) === 'activo' ? 'estado-activo' : 'estado-inactivo' }}">
                                    {{ $evaluacion->estado }}
                                </span>
                            </td>
                            <td>
                                <div class="acciones">
                                    <details>
                                        <summary>Editar Evaluación</summary>
                                        <form action="{{ route('evaluaciones.update', $evaluacion->id_evaluacion) }}" method="POST" class="inline-form formulario-evaluacion" style="margin-top: 10px;">
                                            @csrf
                                            @method('PUT')

                                            <select class="select-grupo-materia" required style="background-color: white;">
                                                @foreach($gruposMaterias as $gm)
                                                    <option value="{{ $gm->id_grupo }}|{{ $gm->id_materia }}"
                                                        data-grupo="{{ $gm->id_grupo }}"
                                                        data-materia="{{ $gm->id_materia }}"
                                                        {{ (int)$evaluacion->id_grupo === (int)$gm->id_grupo && (int)$evaluacion->id_materia === (int)$gm->id_materia ? 'selected' : '' }}>
                                                        {{ $gm->sigla_grupo }} - {{ $gm->nombre_materia }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <input type="hidden" name="Id_grupo" class="input-id-grupo" value="{{ $evaluacion->id_grupo }}">
                                            <input type="hidden" name="Id_materia" class="input-id-materia" value="{{ $evaluacion->id_materia }}">

                                            <input type="number" name="numero_evaluacion" min="1" value="{{ $evaluacion->numero_evaluacion }}" required placeholder="Nro Eval">
                                            <input type="number" step="0.01" name="porcentaje" min="1" max="100" value="{{ $evaluacion->porcentaje }}" required placeholder="Porcentaje">
                                            <input type="date" name="fecha" value="{{ $evaluacion->fecha }}" required>

                                            <select name="estado" required style="background-color: white;">
                                                <option value="activo" {{ strtolower(trim($evaluacion->estado)) === 'activo' ? 'selected' : '' }}>activo</option>
                                                <option value="inactivo" {{ strtolower(trim($evaluacion->estado)) === 'inactivo' ? 'selected' : '' }}>inactivo</option>
                                            </select>

                                            <button type="submit" class="btn-warning" style="grid-column: 1 / -1; margin-top: 6px;">
                                                Actualizar
                                            </button>
                                        </form>
                                    </details>

                                    <form action="{{ route('evaluaciones.destroy', $evaluacion->id_evaluacion) }}"
                                          method="POST"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar esta evaluación? También se eliminarán sus notas.');">
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
                            <td colspan="8" style="text-align:center;">No hay evaluaciones registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

<!-- CONTENIDO PESTAÑA: NOTAS -->
@if($tab === 'notas')
    <!-- Buscador/Selector de Materia y Grupo -->
    <div class="card-box">
        <h2 style="color:#0b2d6b;margin-bottom:16px;">Seleccionar Materia, Grupo y Evaluación</h2>
        
        <form id="filtroNotasForm" action="{{ route('evaluaciones-notas.index') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; align-items: flex-end;">
            <input type="hidden" name="tab" value="notas">
            
            <div class="form-group">
                <label>Grupo y Materia</label>
                <select name="grupo_materia" id="selectGrupoMateriaFiltro" required style="padding: 10px; border: 1px solid #ccc; border-radius: 8px; background-color: white;">
                    <option value="">Seleccione grupo y materia</option>
                    @foreach($gruposMaterias as $gm)
                        @php
                            $val = $gm->id_grupo . '|' . $gm->id_materia;
                            $isSelected = (request('id_grupo') == $gm->id_grupo && request('id_materia') == $gm->id_materia) || request('grupo_materia') == $val;
                        @endphp
                        <option value="{{ $val }}" {{ $isSelected ? 'selected' : '' }} data-grupo="{{ $gm->id_grupo }}" data-materia="{{ $gm->id_materia }}">
                            {{ $gm->sigla_grupo }} - {{ $gm->nombre_materia }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="id_grupo" id="id_grupo_filtro" value="{{ request('id_grupo') }}">
                <input type="hidden" name="id_materia" id="id_materia_filtro" value="{{ request('id_materia') }}">
            </div>

            <div class="form-group">
                <label>Evaluación</label>
                <select name="id_evaluacion" id="selectEvaluacionFiltro" required style="padding: 10px; border: 1px solid #ccc; border-radius: 8px; background-color: white;" {{ $evaluacionesFiltradas->isEmpty() ? 'disabled' : '' }}>
                    <option value="">Seleccione evaluación</option>
                    @foreach($evaluacionesFiltradas as $ev)
                        <option value="{{ $ev->Id_evaluacion }}" {{ request('id_evaluacion') == $ev->Id_evaluacion ? 'selected' : '' }}>
                            Eval. {{ $ev->numero_evaluacion }} ({{ $ev->porcentaje }}%) - Fecha: {{ $ev->fecha }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="submit" class="btn-primary" style="height: 40px; width: 100%; cursor: pointer;">
                    Cargar Estudiantes
                </button>
            </div>
        </form>
    </div>

    <!-- Planilla de Notas en Lote -->
    @if(request()->filled('id_evaluacion') && !$estudiantesPlanilla->isEmpty())
        <div class="card-box">
            <h2 style="color:#0b2d6b;margin-bottom:16px;">Asignación de Calificaciones en Lote</h2>
            <p style="margin-bottom: 18px; font-size: 14px; color: #475569; background-color: #f8fafc; padding: 12px; border-left: 4px solid #0b2d6b; border-radius: 4px;">
                Calificando la <strong>Evaluación {{ $evaluacionesFiltradas->firstWhere('Id_evaluacion', request('id_evaluacion'))->numero_evaluacion ?? '' }}</strong> 
                ({{ $evaluacionesFiltradas->firstWhere('Id_evaluacion', request('id_evaluacion'))->porcentaje ?? '' }}%) de la materia 
                <strong>{{ $gruposMaterias->firstWhere('id_grupo', request('id_grupo'))->nombre_materia ?? '' }}</strong> en el grupo 
                <strong>{{ $gruposMaterias->firstWhere('id_grupo', request('id_grupo'))->sigla_grupo ?? '' }}</strong>.
            </p>

            <form action="{{ route('notas.lote.store') }}" method="POST">
                @csrf
                <input type="hidden" name="Id_evaluacion" value="{{ request('id_evaluacion') }}">
                <input type="hidden" name="Id_grupo" value="{{ request('id_grupo') }}">
                <input type="hidden" name="id_materia" value="{{ request('id_materia') }}">

                <div class="table-responsive">
                    <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
                        <thead>
                            <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                <th style="padding: 12px;">CI</th>
                                <th style="padding: 12px;">Postulante / Estudiante</th>
                                <th style="padding: 12px; width: 180px;">Nota (0-100)</th>
                                <th style="padding: 12px; width: 220px;">Estado Académico</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($estudiantesPlanilla as $estudiante)
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 12px; font-weight: bold; color: #475569;">{{ $estudiante->ci }}</td>
                                    <td style="padding: 12px; font-weight: 500; color: #1e293b;">{{ $estudiante->nombre }} {{ $estudiante->apellido }}</td>
                                    <td style="padding: 12px;">
                                        <input type="number" 
                                               step="0.01" 
                                               name="notas[{{ $estudiante->id_postulante }}]" 
                                               value="{{ old('notas.'.$estudiante->id_postulante, $estudiante->nota) }}" 
                                               min="0" 
                                               max="100" 
                                               placeholder="Ingresar nota" 
                                               style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; outline: none;"
                                               class="input-nota"
                                               data-postulante="{{ $estudiante->id_postulante }}"
                                               oninput="actualizarEstadoAutomatico(this)">
                                    </td>
                                    <td style="padding: 12px;">
                                        <select name="estados[{{ $estudiante->id_postulante }}]" 
                                                id="estado-{{ $estudiante->id_postulante }}"
                                                style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; background-color: white; outline: none;">
                                            <option value="Observado" {{ old('estados.'.$estudiante->id_postulante, $estudiante->estado_academico ?? 'Observado') === 'Observado' ? 'selected' : '' }}>Observado</option>
                                            <option value="Aprobado" {{ old('estados.'.$estudiante->id_postulante, $estudiante->estado_academico) === 'Aprobado' ? 'selected' : '' }}>Aprobado</option>
                                            <option value="Reprobado" {{ old('estados.'.$estudiante->id_postulante, $estudiante->estado_academico) === 'Reprobado' ? 'selected' : '' }}>Reprobado</option>
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 24px; text-align: right;">
                    <button type="submit" class="btn-primary" style="padding: 12px 28px; font-size: 15px; cursor: pointer; border-radius: 8px;">
                        Guardar Planilla de Notas
                    </button>
                </div>
            </form>
        </div>
    @elseif(request()->filled('id_evaluacion'))
        <div class="card-box" style="text-align: center; padding: 40px; color: #64748b; font-style: italic;">
            No se encontraron estudiantes registrados en el grupo seleccionado.
        </div>
    @endif

    <!-- Tabla General de Notas Registradas -->
    <div class="card-box">
        <h2 style="color:#0b2d6b;margin-bottom:16px;">Notas Registradas General</h2>

        <div style="margin-bottom:16px;">
            <label style="font-weight:bold;color:#0b2d6b;">Buscar Nota</label>
            <input type="text" id="buscarNota" placeholder="Buscar por postulante, grupo, materia, nota o estado..."
                   style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;margin-top:6px; outline:none;">
        </div>

        <div class="table-responsive">
            <table id="tablaNotas">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Postulante</th>
                        <th>CI</th>
                        <th>Grupo</th>
                        <th>Materia</th>
                        <th>Evaluación</th>
                        <th>Nota</th>
                        <th>Estado Académico</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notas as $nota)
                        <tr>
                            <td>{{ $nota->id_nota }}</td>
                            <td>{{ $nota->nombre }} {{ $nota->apellido }}</td>
                            <td>{{ $nota->ci }}</td>
                            <td>{{ $nota->sigla_grupo }}</td>
                            <td>{{ $nota->nombre_materia }}</td>
                            <td>Eval. {{ $nota->numero_evaluacion }} ({{ $nota->porcentaje }}%)</td>
                            <td><strong>{{ $nota->nota }}</strong></td>
                            <td>
                                @php
                                    $estadoAcademico = strtolower(trim($nota->estado_academico));
                                    $claseEstadoAcademico = 'estado-observado';

                                    if ($estadoAcademico === 'aprobado') {
                                        $claseEstadoAcademico = 'estado-aprobado';
                                    } elseif ($estadoAcademico === 'reprobado') {
                                        $claseEstadoAcademico = 'estado-reprobado';
                                    }
                                @endphp
                                <span class="estado {{ $claseEstadoAcademico }}">
                                    {{ $nota->estado_academico }}
                                </span>
                            </td>
                            <td>{{ $nota->fecha }}</td>
                            <td>
                                <div class="acciones">
                                    <form action="{{ route('notas.destroy', $nota->id_nota) }}"
                                          method="POST"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar esta nota?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger" style="padding: 6px 12px; font-size: 12px;">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align:center;">No hay notas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Código para procesar formulario-evaluacion
    document.querySelectorAll('.formulario-evaluacion').forEach(function (form) {
        const selectGrupoMateria = form.querySelector('.select-grupo-materia');
        const inputGrupo = form.querySelector('.input-id-grupo');
        const inputMateria = form.querySelector('.input-id-materia');

        function actualizarGrupoMateria() {
            if (!selectGrupoMateria || !inputGrupo || !inputMateria) return;

            const opcion = selectGrupoMateria.options[selectGrupoMateria.selectedIndex];

            if (!opcion || !opcion.value) {
                inputGrupo.value = '';
                inputMateria.value = '';
                return;
            }

            const parts = opcion.value.split('|');
            inputGrupo.value = parts[0] || '';
            inputMateria.value = parts[1] || '';
        }

        selectGrupoMateria?.addEventListener('change', actualizarGrupoMateria);
        actualizarGrupoMateria();

        form.addEventListener('submit', function (e) {
            actualizarGrupoMateria();

            if (!inputGrupo.value || !inputMateria.value) {
                e.preventDefault();
                alert('Debe seleccionar un grupo y materia válidos.');
            }
        });
    });

    // Filtros dinámicos encadenados en Calificar Estudiantes
    const selectGM = document.getElementById('selectGrupoMateriaFiltro');
    if (selectGM) {
        selectGM.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const idGrupoInput = document.getElementById('id_grupo_filtro');
            const idMateriaInput = document.getElementById('id_materia_filtro');
            const selectEv = document.getElementById('selectEvaluacionFiltro');
            
            if (selectedOption && selectedOption.value) {
                const parts = selectedOption.value.split('|');
                idGrupoInput.value = parts[0];
                idMateriaInput.value = parts[1];
                if (selectEv) {
                    selectEv.value = "";
                }
                document.getElementById('filtroNotasForm').submit();
            } else {
                idGrupoInput.value = "";
                idMateriaInput.value = "";
                document.getElementById('filtroNotasForm').submit();
            }
        });
    }

    // Buscador general de texto
    function activarBuscador(inputId, tablaId) {
        const buscador = document.getElementById(inputId);
        const tabla = document.getElementById(tablaId);

        if (!buscador || !tabla) return;

        buscador.addEventListener('keyup', function () {
            const texto = buscador.value.toLowerCase();
            const filas = tabla.querySelectorAll('tbody tr');

            filas.forEach(function (fila) {
                const contenido = fila.textContent.toLowerCase();
                fila.style.display = contenido.includes(texto) ? '' : 'none';
            });
        });
    }

    activarBuscador('buscarEvaluacion', 'tablaEvaluaciones');
    activarBuscador('buscarNota', 'tablaNotas');
});

// Asignación de estado automático según la nota
function actualizarEstadoAutomatico(input) {
    const val = parseFloat(input.value);
    const idPostulante = input.dataset.postulante;
    const selectEstado = document.getElementById('estado-' + idPostulante);
    
    if (selectEstado) {
        if (isNaN(val) || input.value === '') {
            selectEstado.value = 'Observado';
        } else if (val >= 51) {
            selectEstado.value = 'Aprobado';
        } else {
            selectEstado.value = 'Reprobado';
        }
    }
}
</script>

@endsection