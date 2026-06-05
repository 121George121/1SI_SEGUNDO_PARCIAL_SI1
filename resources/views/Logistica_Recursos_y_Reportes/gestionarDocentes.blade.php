@extends('Logistica_Recursos_y_Reportes.Menu')

@section('content')

<h1 class="titulo">CU09 - Gestionar Docentes</h1>
<p class="subtitulo">Registrar docentes, validar documentos, asignar especialidad y actualizar información.</p>

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
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Registrar Docente</h2>

    <form action="{{ route('docentes.store') }}" method="POST" class="form-grid">
        @csrf

        <div class="form-group">
            <label>CI</label>
            <input type="text" name="ci" required>
        </div>

        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="nombre" required>
        </div>

        <div class="form-group">
            <label>Apellido</label>
            <input type="text" name="apellido" required>
        </div>

        <div class="form-group">
            <label>Sexo</label>
            <select name="sexo">
                <option value="">Seleccione</option>
                <option value="M">Masculino</option>
                <option value="F">Femenino</option>
            </select>
        </div>

        <div class="form-group">
            <label>Fecha de Nacimiento</label>
            <input type="date" name="fecha_nacimiento" required>
        </div>

        <div class="form-group">
            <label>Teléfono</label>
            <input type="text" name="telefono">
        </div>

        <div class="form-group">
            <label>Correo</label>
            <input type="email" name="correo">
        </div>

        <div class="form-group">
            <label>Años de Servicio</label>
            <input type="number" name="anio_servicio" min="0" required>
        </div>

        <div class="form-group">
            <label>Estado</label>
            <select name="estado" required>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>

        <div class="form-group">
            <label>Especialidades</label>
            <select name="especialidades[]" multiple>
                @foreach($especialidades as $especialidad)
                    <option value="{{ $especialidad->id_especialidad }}">
                        {{ $especialidad->nombre_especialidad }}
                    </option>
                @endforeach
            </select>
            <small>Mantén presionado CTRL para seleccionar varias especialidades.</small>
        </div>

        <div class="form-group full">
            <label>Dirección</label>
            <textarea name="direccion"></textarea>
        </div>

        <div class="form-group full">
            <button type="submit" class="btn-primary">Registrar Docente</button>
        </div>
    </form>
</div>

<div class="card-box">
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Docentes Registrados</h2>
    <div style="margin-bottom:16px;">
    <label style="font-weight:bold; color:#0b2d6b; display:block; margin-bottom:6px;">
        Buscar Docente Registrado
    </label>

    <input 
        type="text" 
        id="buscarDocente" 
        placeholder="Buscar por CI, nombre, apellido, correo, teléfono, especialidad o estado..."
        style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:14px;"
    >
</div>

    <div class="table-responsive">
        <table id="tablaDocentes">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>CI</th>
                    <th>Docente</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Años Servicio</th>
                    <th>Especialidades</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($docentes as $docente)
                    <tr>
                        <td>{{ $docente->id_docente }}</td>
                        <td>{{ $docente->ci }}</td>
                        <td>{{ $docente->nombre }} {{ $docente->apellido }}</td>
                        <td>{{ $docente->correo ?? 'Sin correo' }}</td>
                        <td>{{ $docente->telefono ?? 'Sin teléfono' }}</td>
                        <td>{{ $docente->anio_servicio }}</td>
                        <td>{{ $docente->especialidades }}</td>
                        <td>
                            <span class="estado estado-{{ $docente->estado }}">
                                {{ ucfirst($docente->estado) }}
                            </span>
                        </td>

                        <td>
                            <div class="acciones">
                                <details>
                                    <summary>Actualizar Docente</summary>

                                    <form action="{{ route('docentes.update', $docente->id_docente) }}" method="POST" class="inline-form">
                                        @csrf
                                        @method('PUT')

                                        <input type="text" name="ci" value="{{ $docente->ci }}" required>
                                        <input type="text" name="nombre" value="{{ $docente->nombre }}" required>
                                        <input type="text" name="apellido" value="{{ $docente->apellido }}" required>

                                        <select name="sexo">
                                            <option value="">Sexo</option>
                                            <option value="M" {{ $docente->sexo == 'M' ? 'selected' : '' }}>M</option>
                                            <option value="F" {{ $docente->sexo == 'F' ? 'selected' : '' }}>F</option>
                                        </select>

                                        <input type="date" name="fecha_nacimiento" value="{{ $docente->fecha_nacimiento }}" required>
                                        <input type="text" name="telefono" value="{{ $docente->telefono }}">
                                        <input type="email" name="correo" value="{{ $docente->correo }}">
                                        <input type="number" name="anio_servicio" min="0" value="{{ $docente->anio_servicio }}" required>

                                        <select name="estado" required>
                                            <option value="activo" {{ $docente->estado == 'activo' ? 'selected' : '' }}>Activo</option>
                                            <option value="inactivo" {{ $docente->estado == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                        </select>

                                        <input type="text" name="direccion" value="{{ $docente->direccion }}">

                                        <select name="especialidades[]" multiple>
                                            @foreach($especialidades as $especialidad)
                                                <option value="{{ $especialidad->id_especialidad }}">
                                                    {{ $especialidad->nombre_especialidad }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <button type="submit" class="btn-warning">Actualizar</button>
                                    </form>
                                </details>

                                <form action="{{ route('docentes.documentos', $docente->id_docente) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn-success">Validar Documentos</button>
                                </form>

                                @if($docente->estado === 'activo')
                                    <form action="{{ route('docentes.deshabilitar', $docente->id_docente) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn-danger">Deshabilitar</button>
                                    </form>
                                @else
                                    <form action="{{ route('docentes.habilitar', $docente->id_docente) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn-success">Habilitar</button>
                                    </form>
                                @endif

                                <form action="{{ route('docentes.destroy', $docente->id_docente) }}" 
                                    method="POST"
                                    onsubmit="return confirm('¿Seguro que deseas eliminar este docente? También se eliminará su usuario y sus especialidades asignadas.')">
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
                        <td colspan="9" style="text-align:center; padding:20px;">
                            No hay docentes registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const buscador = document.getElementById('buscarDocente');
    const tabla = document.getElementById('tablaDocentes');

    if (!buscador || !tabla) return;

    buscador.addEventListener('keyup', function () {
        const texto = buscador.value.toLowerCase();
        const filas = tabla.querySelectorAll('tbody tr');

        filas.forEach(function (fila) {
            const contenidoFila = fila.textContent.toLowerCase();

            if (contenidoFila.includes(texto)) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    });
});
</script>
@endsection