@extends('Gestion_Academica.Menu')

@section('title', 'CU12 - Asignar Postulantes a Grupos')

@section('content')

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <div>
        <h1 class="titulo" style="margin-bottom: 4px;">CU12 - Asignar Postulantes a Grupos</h1>
        <p class="subtitulo" style="margin-bottom: 0;">Administrar la asignación de postulantes validados a grupos activos.</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <form action="{{ route('postulantes-grupos.general') }}" method="POST" onsubmit="return confirm('¿Seguro que deseas realizar la asignación general automática? Se asignarán todos los postulantes a cualquier grupo con espacio disponible.');">
            @csrf
            <button type="submit" class="btn-success" style="font-size: 15px; padding: 12px 20px; cursor: pointer;">
                👥 Asignar Estudiantes a Grupos General
            </button>
        </form>
        <button class="btn-primary" id="btnAbrirAsignacion" style="font-size: 15px; padding: 12px 20px; cursor: pointer;">
            ➕ Asignar Postulante a Grupo
        </button>
    </div>
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

    .form-group select,
    .form-group input {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
        background-color: white;
    }

    .full {
        grid-column: 1 / 3;
    }

    .btn-primary,
    .btn-success,
    .btn-secondary,
    .btn-danger,
    .btn-warning {
        border: none;
        padding: 10px 14px;
        border-radius: 7px;
        color: white;
        cursor: pointer;
        font-weight: bold;
        text-decoration: none;
        display: inline-block;
        text-align: center;
        transition: opacity 0.2s;
    }

    .btn-primary { background: #0b2d6b; }
    .btn-success { background: #16a34a; }
    .btn-danger { background: #dc2626; }
    .btn-secondary { background: #6b7280; }
    .btn-warning { background: #f59e0b; color: #1e293b; }

    .btn-primary:hover, .btn-success:hover, .btn-danger:hover, .btn-secondary:hover, .btn-warning:hover {
        opacity: 0.9;
    }

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
        z-index: 9999;
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

    /* Modal styling */
    .modal-fondo {
        display: none;
        position: fixed;
        z-index: 999;
        inset: 0;
        background: rgba(0,0,0,0.5);
        justify-content: center;
        align-items: center;
    }

    .modal-fondo.activo {
        display: flex;
    }

    .modal-contenido {
        background: white;
        width: 700px;
        max-width: 95%;
        padding: 24px;
        border-radius: 14px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.25);
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 10px;
    }

    .modal-header h2 {
        color: #0b2d6b;
        margin: 0;
        font-size: 22px;
    }

    .btn-cerrar {
        background: #dc2626;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
    }

    /* Small table in modal */
    .modal-table {
        width: 100%;
        min-width: 100%;
        margin-top: 10px;
        margin-bottom: 10px;
    }

    .modal-table th {
        background: #475569;
        padding: 8px;
        font-size: 13px;
    }

    .modal-table td {
        padding: 8px;
        font-size: 13px;
        border-bottom: 1px solid #eee;
    }

    .modal-table tr:hover {
        background-color: #f8fafc;
    }

    .modal-table input[type="radio"] {
        cursor: pointer;
        width: 16px;
        height: 16px;
    }
</style>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

<div class="card-box">
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Postulantes Asignados a Grupos</h2>

    <div style="margin-bottom:16px;">
        <label style="font-weight:bold;color:#0b2d6b;">Buscar Asignación</label>
        <input type="text" id="buscarAsignacion" placeholder="Buscar por CI, nombre, grupo o estado..."
               style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;margin-top:6px; outline: none;">
    </div>

    <div class="table-responsive">
        <table id="tablaAsignaciones">
            <thead>
                <tr>
                    <th>CI</th>
                    <th>Postulante</th>
                    <th>Grupo Asignado</th>
                    <th>Fecha Asignación</th>
                    <th>Estado</th>
                    <th style="text-align: center;">Acción</th>
                </tr>
            </thead>

            <tbody>
                @forelse($asignaciones as $asignacion)
                    <tr>
                        <td style="font-weight: 600; color: #475569;">{{ $asignacion->ci }}</td>
                        <td style="font-weight: 500; color: #1e293b;">{{ $asignacion->nombre }} {{ $asignacion->apellido }}</td>
                        <td>
                            <span style="display: inline-block; background-color: #dbeafe; color: #1e3a8a; padding: 4px 8px; border-radius: 6px; font-weight: bold; font-size: 13px;">
                                {{ $asignacion->sigla_grupo }}
                            </span>
                        </td>
                        <td style="color: #64748b;">{{ \Carbon\Carbon::parse($asignacion->fecha_asignacion)->format('d/m/Y') }}</td>
                        <td>
                            <span class="estado {{ strtolower(trim($asignacion->estado)) === 'activo' ? 'estado-activo' : 'estado-inactivo' }}">
                                {{ $asignacion->estado }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <form action="{{ route('postulantes-grupos.destroy', [$asignacion->id_grupo, $asignacion->id_postulante]) }}"
                                  method="POST"
                                  onsubmit="return confirm('¿Seguro que deseas eliminar la asignación de este postulante del grupo? Se liberará 1 cupo.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger" style="font-size: 13px; padding: 6px 12px; cursor: pointer;">
                                    Eliminar Asignación
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center; color: #64748b; font-style: italic; padding: 20px;">No hay postulantes asignados a grupos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de Registro de Asignación (Contiene el formulario y la búsqueda de grupos con referencias) -->
<div id="modalAsignacion" class="modal-fondo">
    <div class="modal-contenido">
        <div class="modal-header">
            <h2>Asignar Postulante a Grupo</h2>
            <button type="button" class="btn-cerrar" id="btnCerrarModal">X</button>
        </div>

        <form action="{{ route('postulantes-grupos.store') }}" method="POST" class="form-grid" id="formAsignar">
            @csrf

            <!-- Buscador / Selector de Postulante sin Grupo -->
            <div class="form-group search-select full" data-search-select>
                <label>Postulante (Inscritos y Validados)</label>
                <input type="text" id="inputBuscarPostulante" class="search-input" placeholder="Buscar postulante por nombre o CI..." autocomplete="off" required>
                <input type="hidden" name="Id_postulante" id="hiddenPostulanteId" data-required="true">

                <div class="search-options">
                    @forelse($postulantesSinGrupo as $postulante)
                        <div class="search-option" data-value="{{ $postulante->id_postulante }}">
                            {{ $postulante->ci }} - {{ $postulante->nombre }} {{ $postulante->apellido }}
                        </div>
                    @empty
                        <div style="padding: 10px; color: #777; font-style: italic;">No hay postulantes validados pendientes de asignar.</div>
                    @endforelse
                </div>
            </div>

            <!-- Selector de Modalidad (Preferencia de búsqueda) -->
            <div class="form-group">
                <label>Modalidad de Preferencia</label>
                <select name="Id_modalidad" id="selectModalidad" required>
                    <option value="">Seleccione modalidad...</option>
                    @foreach($modalidades as $modalidad)
                        <option value="{{ $modalidad->id_modalidad }}">{{ $modalidad->nombre_modalidad }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Selector de Turno (Preferencia de búsqueda) -->
            <div class="form-group">
                <label>Turno de Preferencia</label>
                <select name="Id_turno" id="selectTurno" required>
                    <option value="">Seleccione turno...</option>
                    @foreach($turnos as $turno)
                        <option value="{{ $turno->id_turno }}">{{ $turno->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Botón Buscar Grupo con esas Referencias -->
            <div class="form-group full" style="margin-top: 4px;">
                <button type="button" class="btn-warning" id="btnBuscarGrupos">
                    🔍 Buscar Grupo con esas Preferencias
                </button>
            </div>

            <!-- Listado Detallado de Grupos Encontrados con Referencias -->
            <div class="form-group full" style="margin-top: 10px;">
                <label>Seleccione el Grupo de Destino</label>
                <div id="containerGrupos" style="border: 1px solid #ccc; border-radius: 8px; padding: 12px; max-height: 220px; overflow-y: auto; background-color: #fafafa;">
                    <div id="textPlaceholderGrupos" style="color: #64748b; font-style: italic; text-align: center; padding: 10px;">
                        Seleccione Modalidad y Turno, luego haga clic en "Buscar Grupo" para ver las opciones disponibles.
                    </div>
                    
                    <!-- Aquí se renderizará la tabla interactiva de grupos en JS -->
                    <table class="modal-table" id="tablaGruposEncontrados" style="display: none;">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">Elegir</th>
                                <th>Sigla Grupo</th>
                                <th>Modalidad</th>
                                <th>Turno</th>
                                <th style="text-align: center;">Cupo (Estudiantes / Máx)</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyGruposEncontrados">
                            <!-- Filas dinámicas -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="form-group full" style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px; border-top: 1px solid #e5e7eb; padding-top: 14px;">
                <button type="button" class="btn-secondary" id="btnCancelarModal">Cancelar</button>
                <button type="submit" class="btn-primary" id="btnSubmitAsignar" disabled>
                    Confirmar Asignación
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Listado de grupos pasados desde PHP a JS para filtrado rápido reactivo con sus referencias
    const groupsData = {!! json_encode($grupos) !!};

    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modalAsignacion');
        const btnAbrir = document.getElementById('btnAbrirAsignacion');
        const btnCerrar = document.getElementById('btnCerrarModal');
        const btnCancelar = document.getElementById('btnCancelarModal');
        const formAsignar = document.getElementById('formAsignar');

        const inputBuscar = document.getElementById('inputBuscarPostulante');
        const hiddenId = document.getElementById('hiddenPostulanteId');
        const selectModalidad = document.getElementById('selectModalidad');
        const selectTurno = document.getElementById('selectTurno');
        const btnBuscarGrupos = document.getElementById('btnBuscarGrupos');
        
        const containerGrupos = document.getElementById('containerGrupos');
        const placeholderGrupos = document.getElementById('textPlaceholderGrupos');
        const tablaGrupos = document.getElementById('tablaGruposEncontrados');
        const tbodyGrupos = document.getElementById('tbodyGruposEncontrados');
        const btnSubmitAsignar = document.getElementById('btnSubmitAsignar');

        // --- Abrir / Cerrar Modal ---
        btnAbrir.addEventListener('click', function () {
            formAsignar.reset();
            hiddenId.value = '';
            placeholderGrupos.style.display = 'block';
            placeholderGrupos.innerHTML = 'Seleccione Modalidad y Turno, luego haga clic en "Buscar Grupo" para ver las opciones disponibles.';
            tablaGrupos.style.display = 'none';
            tbodyGrupos.innerHTML = '';
            btnSubmitAsignar.disabled = true;
            modal.classList.add('activo');
        });

        const cerrarModal = function () {
            modal.classList.remove('activo');
        };

        btnCerrar.addEventListener('click', cerrarModal);
        btnCancelar.addEventListener('click', cerrarModal);
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                cerrarModal();
            }
        });

        // Configurar buscador/selector dinámico para los postulantes en la asignación
        document.querySelectorAll('[data-search-select]').forEach(function (contenedor) {
            const input = contenedor.querySelector('.search-input');
            const hidden = contenedor.querySelector('input[type="hidden"]');
            const opciones = contenedor.querySelector('.search-options');
            const items = contenedor.querySelectorAll('.search-option');

            input.addEventListener('focus', function () {
                opciones.classList.add('activo');
            });

            input.addEventListener('input', function () {
                hidden.value = '';
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

        // Configurar acción de Buscar Grupo con esas Preferencias
        btnBuscarGrupos.addEventListener('click', function () {
            const idModalidad = selectModalidad.value;
            const idTurno = selectTurno.value;

            if (!idModalidad || !idTurno) {
                alert('Debe seleccionar la Modalidad y el Turno de preferencia primero.');
                return;
            }

            // Filtrar los grupos por las preferencias seleccionadas
            const gruposFiltrados = groupsData.filter(g => g.id_modalidad == idModalidad && g.id_turno == idTurno);

            tbodyGrupos.innerHTML = '';

            if (gruposFiltrados.length > 0) {
                placeholderGrupos.style.display = 'none';
                tablaGrupos.style.display = 'table';

                gruposFiltrados.forEach(g => {
                    const estaLleno = g.cant_estudiantes >= g.capacidad_max;
                    const tr = document.createElement('tr');
                    
                    tr.innerHTML = `
                        <td style="text-align: center; vertical-align: middle;">
                            <input type="radio" name="Id_grupo" value="${g.id_grupo}" ${estaLleno ? 'disabled' : ''} class="radio-grupo" required>
                        </td>
                        <td style="font-weight: bold; color: #1e3a8a;">${g.sigla_grupo}</td>
                        <td>${g.modalidad}</td>
                        <td>${g.turno}</td>
                        <td style="text-align: center; font-weight: 600; color: ${estaLleno ? '#dc2626' : '#16a34a'};">
                            ${g.cant_estudiantes} / ${g.capacidad_max} ${estaLleno ? '[LLENO]' : ''}
                        </td>
                    `;
                    tbodyGrupos.appendChild(tr);
                });

                // Escuchar clicks en los radio buttons para activar el submit
                document.querySelectorAll('.radio-grupo').forEach(radio => {
                    radio.addEventListener('change', function () {
                        btnSubmitAsignar.disabled = false;
                    });
                });
            } else {
                tablaGrupos.style.display = 'none';
                placeholderGrupos.style.display = 'block';
                placeholderGrupos.innerHTML = `<span style="color: #dc2626; font-weight: bold;">❌ No se encontraron grupos activos disponibles para la Modalidad y Turno seleccionados.</span>`;
                btnSubmitAsignar.disabled = true;
            }
        });

        // Buscador interactivo de tabla de asignaciones en la página principal
        const buscadorAsig = document.getElementById('buscarAsignacion');
        const tablaAsig = document.getElementById('tablaAsignaciones');
        if (buscadorAsig && tablaAsig) {
            buscadorAsig.addEventListener('keyup', function () {
                const texto = buscadorAsig.value.toLowerCase();
                const filas = tablaAsig.querySelectorAll('tbody tr');
                filas.forEach(function (fila) {
                    fila.style.display = fila.textContent.toLowerCase().includes(texto) ? '' : 'none';
                });
            });
        }

        // Validación final del formulario antes de enviar
        formAsignar.addEventListener('submit', function (e) {
            if (!hiddenId.value) {
                e.preventDefault();
                alert('Debe buscar y seleccionar un postulante válido de la lista desplegable.');
                return;
            }
            if (!selectModalidad.value || !selectTurno.value) {
                e.preventDefault();
                alert('Debe seleccionar la Modalidad y Turno de preferencia.');
                return;
            }
            
            // Comprobar que algún radio esté seleccionado
            const radioSeleccionado = formAsignar.querySelector('input[name="Id_grupo"]:checked');
            if (!radioSeleccionado) {
                e.preventDefault();
                alert('Debe seleccionar uno de los grupos encontrados de la lista.');
                return;
            }
        });
    });
</script>

@endsection
