@extends('Inscripcion_y_Documentacion.Menu')

@section('content')

<h1 class="titulo">CU03 - Gestionar Inscripción</h1>
<p class="subtitulo">Registro, modificación y eliminación de inscripciones de postulantes.</p>

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

    .info-ci {
        display: none;
        padding: 10px;
        border-radius: 8px;
        margin-top: 8px;
        font-weight: bold;
    }

    .search-select {
        position: relative;
    }

    .search-input {
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

    .table-responsive {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1100px;
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

    details summary {
        cursor: pointer;
        font-weight: bold;
        color: #0b2d6b;
        margin-bottom: 8px;
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
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Registrar Inscripción</h2>

    <form action="{{ route('inscripcion.store') }}" method="POST" class="form-grid">
        @csrf

        <div class="form-group">
            <label>CI del Postulante</label>
            <input type="text" name="ci" id="ci_postulante" value="{{ old('ci') }}" required>
            <div id="mensaje_ci" class="info-ci"></div>
        </div>

        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="nombre" id="nombre_postulante" value="{{ old('nombre') }}" required>
        </div>

        <div class="form-group">
            <label>Apellido</label>
            <input type="text" name="apellido" id="apellido_postulante" value="{{ old('apellido') }}" required>
        </div>

        <div class="form-group">
            <label>Sexo</label>
            <select name="sexo" id="sexo_postulante">
                <option value="">Seleccione</option>
                <option value="M">Masculino</option>
                <option value="F">Femenino</option>
            </select>
        </div>

        <div class="form-group">
            <label>Fecha de Nacimiento</label>
            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento_postulante" required>
        </div>

        <div class="form-group">
            <label>Teléfono</label>
            <input type="text" name="telefono" id="telefono_postulante">
        </div>

        <div class="form-group">
            <label>Correo</label>
            <input type="email" name="correo" id="correo_postulante" required>
        </div>

        <div class="form-group full">
            <label>Dirección</label>
            <textarea name="direccion" id="direccion_postulante"></textarea>
        </div>

        <div class="form-group search-select" data-search-select>
    <label>Carrera Principal</label>

    <input 
        type="text" 
        class="search-input" 
        placeholder="Buscar carrera principal..." 
        autocomplete="off"
        required
    >

    <input 
        type="hidden" 
        name="Id_carrera_principal"
        required
    >

    <div class="search-options">
        @foreach($carreras as $carrera)
            <div class="search-option" data-value="{{ $carrera->id_carrera }}">
                {{ $carrera->nombre_carrera }}
            </div>
        @endforeach
    </div>
</div>

    <div class="form-group search-select" data-search-select>
        <label>Carrera Secundaria</label>

        <input 
            type="text" 
            class="search-input" 
            placeholder="Buscar carrera secundaria..." 
            autocomplete="off"
        >

        <input 
            type="hidden" 
            name="Id_carrera_secundaria"
        >

        <div class="search-options">
            @foreach($carreras as $carrera)
                <div class="search-option" data-value="{{ $carrera->id_carrera }}">
                    {{ $carrera->nombre_carrera }}
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

            <div class="form-group full">
                <button type="submit" class="btn-primary">Registrar Inscripción</button>
            </div>
        </form>
    </div>

<div class="card-box">
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Inscripciones Registradas</h2>

    <div style="margin-bottom:16px;">
        <label style="font-weight:bold; color:#0b2d6b; display:block; margin-bottom:6px;">
            Buscar Inscripción
        </label>

        <input 
            type="text" 
            id="buscarInscripcion" 
            placeholder="Buscar por CI, nombre, carrera, gestión, código o estado..."
            style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:14px;"
        >
    </div>

    <div class="table-responsive">
        <table id="tablaInscripciones">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>CI</th>
                    <th>Postulante</th>
                    <th>Carrera Principal</th>
                    <th>Carrera Secundaria</th>
                    <th>Gestión</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($inscripciones as $item)
                    <tr>
                        <td>{{ $item->codigo_inscripcion }}</td>
                        <td>{{ $item->ci }}</td>
                        <td>{{ $item->nombre }} {{ $item->apellido }}</td>
                        <td>{{ $item->carrera_principal ?? 'Sin carrera principal' }}</td>
                        <td>{{ $item->carrera_secundaria ?? 'Sin carrera secundaria' }}</td>
                        <td>
                            @if($item->anio)
                                {{ $item->anio }} - {{ $item->periodo }}
                            @else
                                Sin gestión
                            @endif
                        </td>
                        <td>{{ $item->fecha_inscripcion }}</td>
                        <td>{{ ucfirst($item->estado_inscripcion) }}</td>
                        <td>
                            <div class="acciones">
                                <a href="{{ route('inscripcion.documentos.form', $item->codigo_inscripcion) }}" class="btn-success">
                                    Validar Documentos
                                </a>
                                <details>
                                    <summary>Editar Inscripción</summary>

                                    <form action="{{ route('inscripcion.update', $item->id_inscripcion) }}" method="POST" class="inline-form">
                                        @csrf
                                        @method('PUT')

                                        <input type="text" name="ci" value="{{ $item->ci }}" required>
                                        <input type="text" name="nombre" value="{{ $item->nombre }}" required>
                                        <input type="text" name="apellido" value="{{ $item->apellido }}" required>

                                        <select name="sexo">
                                            <option value="">Sexo</option>
                                            <option value="M" {{ $item->sexo == 'M' ? 'selected' : '' }}>M</option>
                                            <option value="F" {{ $item->sexo == 'F' ? 'selected' : '' }}>F</option>
                                        </select>

                                        <input type="date" name="fecha_nacimiento" value="{{ substr($item->fecha_nacimiento, 0, 10) }}" required>
                                        <input type="text" name="telefono" value="{{ $item->telefono }}">
                                        <input type="email" name="correo" value="{{ $item->correo }}" required>
                                        <input type="text" name="direccion" value="{{ $item->direccion }}">

                                        <select name="estado" required>
                                            <option value="En_Revision" {{ $item->estado_inscripcion == 'En_Revision' ? 'selected' : '' }}>En_Revision</option>
                                        </select>

                                        <select name="Id_carrera_principal" required>
                                            @foreach($carreras as $carrera)
                                                <option value="{{ $carrera->id_carrera }}" 
                                                    {{ $item->id_carrera_principal == $carrera->id_carrera ? 'selected' : '' }}>
                                                    {{ $carrera->nombre_carrera }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <select name="Id_carrera_secundaria">
                                            <option value="">Sin carrera secundaria</option>
                                            @foreach($carreras as $carrera)
                                                <option value="{{ $carrera->id_carrera }}" 
                                                    {{ $item->id_carrera_secundaria == $carrera->id_carrera ? 'selected' : '' }}>
                                                    {{ $carrera->nombre_carrera }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <select name="Id_gestion" required>
                                            @foreach($gestiones as $gestion)
                                                <option value="{{ $gestion->id_gestion }}" {{ $item->id_gestion == $gestion->id_gestion ? 'selected' : '' }}>
                                                    {{ $gestion->anio }} - {{ $gestion->periodo }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <button type="submit" class="btn-warning">Actualizar</button>
                                    </form>
                                </details>

                                <form 
                                    action="{{ route('inscripcion.destroy', $item->id_inscripcion) }}" 
                                    method="POST"
                                    onsubmit="return confirm('¿Seguro que deseas eliminar esta inscripción?')"
                                >
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
                        <td colspan="8" style="text-align:center; padding:20px;">
                            No hay inscripciones registradas.
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

    contenedor.closest('form')?.addEventListener('submit', function (e) {
        if (!hidden.value) {
            e.preventDefault();
            alert('Debe seleccionar una opción de la lista.');
            input.focus();
        }
    });

    document.addEventListener('click', function (e) {
        if (!contenedor.contains(e.target)) {
            opciones.classList.remove('activo');
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const inputCi = document.getElementById('ci_postulante');
    const mensaje = document.getElementById('mensaje_ci');

    if (inputCi) {
        inputCi.addEventListener('blur', function () {
            const ci = inputCi.value.trim();

            if (!ci) return;

            fetch("{{ url('/inscripcion/buscar-ci') }}/" + encodeURIComponent(ci))
                .then(response => response.json())
                .then(data => {
                    if (!data.existe) {
                        mensaje.style.display = 'block';
                        mensaje.style.background = '#fef3c7';
                        mensaje.style.color = '#92400e';
                        mensaje.innerText = 'CI no encontrado. Se registrará un nuevo postulante.';
                        return;
                    }

                    const persona = data.persona;

                    document.getElementById('nombre_postulante').value = persona.nombre ?? '';
                    document.getElementById('apellido_postulante').value = persona.apellido ?? '';
                    document.getElementById('sexo_postulante').value = persona.sexo ?? '';
                    document.getElementById('fecha_nacimiento_postulante').value = persona.fecha_nacimiento ? persona.fecha_nacimiento.substring(0, 10) : '';
                    document.getElementById('telefono_postulante').value = persona.telefono ?? '';
                    document.getElementById('correo_postulante').value = persona.correo ?? '';
                    document.getElementById('direccion_postulante').value = persona.direccion ?? '';

                    mensaje.style.display = 'block';
                    mensaje.style.background = '#d1fae5';
                    mensaje.style.color = '#065f46';

                    if (data.inscripcion) {
                        mensaje.innerText = 'Persona encontrada. Ya tiene inscripción. Al guardar se actualizará.';
                    } else {
                        mensaje.innerText = 'Persona encontrada. Al guardar se registrará como postulante e inscripción.';
                    }
                })
                .catch(error => {
                    mensaje.style.display = 'block';
                    mensaje.style.background = '#fee2e2';
                    mensaje.style.color = '#991b1b';
                    mensaje.innerText = 'Error al buscar CI.';
                });
        });
    }

    const buscador = document.getElementById('buscarInscripcion');
    const tabla = document.getElementById('tablaInscripciones');

    if (buscador && tabla) {
        buscador.addEventListener('keyup', function () {
            const texto = buscador.value.toLowerCase();
            const filas = tabla.querySelectorAll('tbody tr');

            filas.forEach(function (fila) {
                const contenidoFila = fila.textContent.toLowerCase();

                fila.style.display = contenidoFila.includes(texto) ? '' : 'none';
            });
        });
    }
});
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
        const esRequerido = hidden.hasAttribute('required');

        if (esRequerido && !hidden.value) {
            e.preventDefault();
            alert('Debe seleccionar una carrera principal de la lista.');
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