@extends('Gestion_Academica.Menu')

@section('title', 'Gestionar Carreras y Cupos')

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
        outline: none;
    }

    .full {
        grid-column: 1 / 3;
    }

    .estado {
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: bold;
        display: inline-block;
        text-align: center;
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
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .full {
            grid-column: 1;
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
    top: 100%;
    margin-top: 6px;
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
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
@endif

<div class="card-box">
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Registrar Carrera y Asignar Cupo</h2>

    @if($gestiones->count() == 0)
        <div class="alert alert-error">
            No existe una gestión activa. Primero registra una gestión en la tabla gestion.
        </div>
    @endif

    <form action="{{ route('carreras-cupos.store') }}" method="POST" class="form-grid">
        @csrf

        <div class="form-group">
            <label>Nombre de la Carrera</label>
            <input type="text" name="nombre_carrera" value="{{ old('nombre_carrera') }}" placeholder="Ej: Ingeniería de Sistemas" required>
        </div>

        <div class="form-group">
            <label>Estado</label>
            <select name="estado" required>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>

        <div class="form-group search-select" data-search-select>
            <label>Gestión</label>

            <input type="text" class="search-input" placeholder="Buscar gestión..." autocomplete="off" required>
            <input type="hidden" name="id_gestion">

            <div class="search-options">
                @foreach($gestiones as $gestion)
                    <div class="search-option" data-value="{{ $gestion->id_gestion }}">
                        {{ $gestion->anio }} - {{ $gestion->periodo }}
                    </div>
                @endforeach
            </div>
        </div>

        <div class="form-group">
            <label>Cantidad de Cupos</label>
            <input type="number" name="cantidad_cupos" min="0" value="{{ old('cantidad_cupos') }}" required>
        </div>

        <div class="form-group full">
            <label>Descripción</label>
            <textarea name="descripcion" placeholder="Opcional: Descripción breve de la carrera">{{ old('descripcion') }}</textarea>
        </div>

        <div class="form-group full">
            <button type="submit" class="btn btn-primary" style="align-self: flex-start;">Registrar Carrera y Cupos</button>
        </div>
    </form>
</div>

<div class="card-box">
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Carreras y Cupos Registrados</h2>

    <div style="overflow-x: auto;">
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
                        <td><strong>{{ $item->nombre_carrera }}</strong></td>
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
                                <span style="color: #888; font-style: italic;">Sin gestión asignada</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $item->cantidad_cupos ?? 0 }}</strong>
                        </td>

                        <td>
                            <div class="acciones">
                                <button type="button" 
                                    class="btn btn-secondary btn-abrir-editar-carrera"
                                    data-id="{{ $item->id_carrera }}"
                                    data-nombre="{{ $item->nombre_carrera }}"
                                    data-estado="{{ $item->estado }}"
                                    data-descripcion="{{ $item->descripcion ?? '' }}">
                                    Editar Carrera
                                </button>

                                <button type="button"
                                class="btn btn-success btn-abrir-actualizar-cupos"
                                data-id="{{ $item->id_carrera }}"
                                data-nombre="{{ $item->nombre_carrera }}"
                                data-id-gestion="{{ $item->id_gestion ?? '' }}"
                                data-gestion-text="{{ $item->id_gestion ? $item->anio . ' - ' . $item->periodo : '' }}"
                                data-cupos="{{ $item->cantidad_cupos ?? 0 }}">
                                Actualizar Cupos
                                </button>

                                @if($item->estado === 'activo')
                                    <form action="{{ route('carreras-cupos.deshabilitar', $item->id_carrera) }}" method="POST" onsubmit="return confirm('¿Deshabilitar esta carrera?');" style="display:inline;">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-danger">Deshabilitar</button>
                                    </form>
                                @else
                                    <form action="{{ route('carreras-cupos.habilitar', $item->id_carrera) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-success">Habilitar</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:20px; color: #666;">
                            No hay carreras registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal 1: Editar Carrera -->
<div class="modal-fondo" id="modalEditarCarrera">
    <div class="modal-contenido">
        <div class="modal-header">
            <h2>Editar Información de Carrera</h2>
            <button type="button" class="btn-cerrar" onclick="cerrarModalEditarCarrera()">X</button>
        </div>

        <form id="formEditarCarrera" method="POST" class="form-grid">
            @csrf
            @method('PUT')

            <div class="form-group full">
                <label>Nombre de la Carrera</label>
                <input type="text" name="nombre_carrera" id="edit_nombre_carrera" required>
            </div>

            <div class="form-group full">
                <label>Estado</label>
                <select name="estado" id="edit_estado" required>
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>

            <div class="form-group full">
                <label>Descripción</label>
                <textarea name="descripcion" id="edit_descripcion" rows="3"></textarea>
            </div>

            <div class="form-group full" style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 14px;">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <button type="button" class="btn btn-secondary" onclick="cerrarModalEditarCarrera()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Actualizar Cupos -->
<div class="modal-fondo" id="modalActualizarCupos">
    <div class="modal-contenido">
        <div class="modal-header">
            <h2>Actualizar Cupos: <span id="cupos_nombre_carrera" style="color: #1e3a8a;"></span></h2>
            <button type="button" class="btn-cerrar" onclick="cerrarModalActualizarCupos()">X</button>
        </div>

        <form id="formActualizarCupos" method="POST" class="form-grid">
            @csrf
            @method('PUT')

            <div class="form-group full search-select" data-search-select>
                <label>Gestión Académica</label>

                <input 
                    type="text" 
                    id="edit_cupos_gestion_text"
                    class="search-input" 
                    placeholder="Buscar gestión..." 
                    autocomplete="off"
                    required
                >

                <input 
                    type="hidden" 
                    id="edit_cupos_id_gestion"
                    name="id_gestion"
                >

                <div class="search-options">
                    @foreach($gestiones as $gestion)
                        <div class="search-option" data-value="{{ $gestion->id_gestion }}">
                            {{ $gestion->anio }} - {{ $gestion->periodo }}
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="form-group full">
                <label>Cantidad de Cupos</label>
                <input type="number" name="cantidad_cupos" id="edit_cupos_cantidad" min="0" required>
            </div>

            <div class="form-group full" style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 14px;">
                <button type="submit" class="btn btn-success">Actualizar Cupos</button>
                <button type="button" class="btn btn-secondary" onclick="cerrarModalActualizarCupos()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalEditar = document.getElementById('modalEditarCarrera');
        const formEditar = document.getElementById('formEditarCarrera');

        document.querySelectorAll('.btn-abrir-editar-carrera').forEach(function (boton) {
            boton.addEventListener('click', function () {
                // Set dynamic route action
                formEditar.action = `{{ url('/carreras-cupos') }}/${this.dataset.id}`;

                document.getElementById('edit_nombre_carrera').value = this.dataset.nombre || '';
                document.getElementById('edit_estado').value = this.dataset.estado || 'activo';
                document.getElementById('edit_descripcion').value = this.dataset.descripcion || '';

                modalEditar.classList.add('activo');
            });
        });

        const modalCupos = document.getElementById('modalActualizarCupos');
        const formCupos = document.getElementById('formActualizarCupos');
        const spanNombre = document.getElementById('cupos_nombre_carrera');

        document.querySelectorAll('.btn-abrir-actualizar-cupos').forEach(function (boton) {
            boton.addEventListener('click', function () {
                // Set dynamic route action
                formCupos.action = `{{ url('/carreras-cupos') }}/${this.dataset.id}/cupos`;

                spanNombre.textContent = this.dataset.nombre || '';

                document.getElementById('edit_cupos_gestion_text').value = this.dataset.gestionText || '';
                document.getElementById('edit_cupos_id_gestion').value = this.dataset.idGestion || '';
                document.getElementById('edit_cupos_cantidad').value = this.dataset.cupos || '0';

                modalCupos.classList.add('activo');
            });
        });
    });

    function cerrarModalEditarCarrera() {
        document.getElementById('modalEditarCarrera').classList.remove('activo');
    }

    function cerrarModalActualizarCupos() {
        document.getElementById('modalActualizarCupos').classList.remove('activo');
    }
</script>
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

    contenedor.closest('form')?.addEventListener('submit', function (e) {
        if (!hidden.value) {
            e.preventDefault();
            alert('Debe seleccionar una gestión de la lista.');
            input.focus();
        }
    });

    document.addEventListener('click', function (e) {
        if (!contenedor.contains(e.target)) {
            opciones.classList.remove('activo');
        }
    });
});
</script>
@endsection