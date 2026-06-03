@extends('Inscripcion_y_Documentacion.Menu')

@section('title', 'CU4 - Gestionar Documentos')

@section('content')
<style>
.modal-fondo {
    display: none;
    position: fixed;
    z-index: 9999;
    inset: 0;
    background: rgba(0,0,0,0.45);
    justify-content: center;
    align-items: center;
}

.modal-fondo.activo {
    display: flex;
}

.modal-contenido {
    background: white;
    width: 720px;
    max-width: 95%;
    padding: 24px;
    border-radius: 14px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.25);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
}

.modal-header h2 {
    color: #0b2d6b;
    margin: 0;
}

.btn-cerrar {
    background: #dc2626;
    color: white;
    border: none;
    padding: 7px 11px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
}

.btn-secondary {
    background: #e5e7eb;
    color: #111;
    border: none;
    padding: 11px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    margin-left: 8px;
}
    .page-title { font-size: 30px; color: #0b2d6b; font-weight: 800; margin-bottom: 8px; }
    .page-subtitle { color: #555; margin-bottom: 24px; }
    .doc-container { background: #fff; padding: 22px; border-radius: 12px; box-shadow: 0 4px 14px rgba(0,0,0,0.08); margin-bottom: 24px; }
    .doc-form { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
    .form-group { display: flex; flex-direction: column; }
    .form-group label { font-weight: bold; color: #0b2d6b; margin-bottom: 6px; }
    .form-group input, .form-group select, .form-group textarea { padding: 10px; border: 1px solid #ccc; border-radius: 8px; outline: none; }
    .form-group textarea { resize: vertical; min-height: 70px; }
    .full { grid-column: 1 / 3; }
    .btn-primary { background: #0b2d6b; color: white; border: none; padding: 11px 16px; border-radius: 8px; cursor: pointer; font-weight: bold; }
    .btn-primary:hover { background: #082455; }
    .btn-eliminar { background: #dc2626; color: white; border: none; padding: 7px 10px; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; }
    .btn-editar { background: #2563eb; color: white; border: none; padding: 7px 10px; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; }
    .btn-validar { background: #16a34a; color: white; border: none; padding: 7px 10px; border-radius: 6px; cursor: pointer; }
    .btn-observar { background: #f59e0b; color: white; border: none; padding: 7px 10px; border-radius: 6px; cursor: pointer; }
    .alert-success { background: #d1fae5; color: #065f46; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
    .alert-error { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
    .table-responsive { overflow-x: auto; }
    .doc-table { width: 100%; border-collapse: collapse; min-width: 700px; }
    .doc-table th { background: #0b2d6b; color: white; padding: 12px; text-align: left; }
    .doc-table td { padding: 12px; border-bottom: 1px solid #ddd; color: #333; }
    .badge { display: inline-block; background: #dbeafe; color: #1e3a8a; padding: 4px 10px; border-radius: 20px; font-size: 13px; font-weight: bold; }
    .estado { padding: 6px 10px; border-radius: 20px; font-size: 13px; font-weight: bold; display: inline-block; }
    .estado-pendiente { background: #fef3c7; color: #92400e; }
    .estado-validado { background: #d1fae5; color: #065f46; }
    .estado-observado { background: #fee2e2; color: #991b1b; }
    .acciones { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
    .observacion-input { padding: 7px; border: 1px solid #ccc; border-radius: 6px; width: 150px; }
    .section-desc { color: #666; font-size: 14px; margin-bottom: 16px; }
    @media (max-width: 768px) { .doc-form { grid-template-columns: 1fr; } .full { grid-column: 1; } }
</style>

<h1 class="page-title">CU4 - Gestionar Documentos</h1>
<p class="page-subtitle">Registro de requisitos documentales generales y revision de documentos presentados.</p>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

<div class="doc-container">
    <h2 style="color:#0b2d6b; margin-bottom: 8px;">Registrar requisito documental</h2>
    <p class="section-desc">El documento es general. Use <strong>Destinado a</strong> para indicar si aplica a Postulantes o Docentes.</p>

    <form action="{{ route('documentos.store') }}" method="POST" class="doc-form">
        @csrf

        <div class="form-group">
            <label>Tipo de documento</label>
            <input type="text" name="tipo_documento" value="{{ old('tipo_documento') }}" placeholder="Ej: Identidad, Academico" required>
        </div>

        <div class="form-group">
            <label>Nombre del documento</label>
            <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Ej: Carnet de identidad" required>
        </div>

        <div class="form-group">
            <label>Destinado a</label>
            <select name="destinado_a" required>
                <option value="">Seleccione</option>
                <option value="Postulantes" @selected(old('destinado_a') === 'Postulantes')>Postulantes</option>
                <option value="Docentes" @selected(old('destinado_a') === 'Docentes')>Docentes</option>
            </select>
        </div>

        <div class="form-group full">
            <label>Descripcion</label>
            <textarea name="descripcion" placeholder="Descripcion del requisito">{{ old('descripcion') }}</textarea>
        </div>

        <div class="form-group full">
            <button type="submit" class="btn-primary">Registrar requisito</button>
        </div>
    </form>
</div>

<div class="doc-container">
    <h2 style="color:#0b2d6b; margin-bottom: 8px;">Requisitos documentales</h2>
    <p class="section-desc">Catalogo general de documentos requeridos segun el tipo de usuario.</p>

    <div class="table-responsive">
        <table class="doc-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Destinado a</th>
                    <th>Descripcion</th>
                    <th>Fecha registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requisitos as $req)
                    <tr>
                        <td>{{ $req->nombre }}</td>
                        <td>{{ $req->tipo_documento }}</td>
                        <td><span class="badge">{{ $req->destinado_a }}</span></td>
                        <td>{{ $req->descripcion ?? '-' }}</td>
                        <td>{{ $req->fecha_registro }}</td>
                        <td>
                            <div class="acciones">
                                <button 
                                    type="button"
                                    class="btn-editar btn-abrir-editar"
                                    data-url="{{ route('documentos.update', $req->Id_documento) }}"
                                    data-tipo="{{ $req->tipo_documento }}"
                                    data-nombre="{{ $req->nombre }}"
                                    data-destinado="{{ $req->destinado_a }}"
                                    data-descripcion="{{ $req->descripcion ?? '' }}">
                                    Editar
                                </button>

                                <form action="{{ route('documentos.destroy', $req->Id_documento) }}" method="POST" onsubmit="return confirm('Eliminar este requisito documental?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-eliminar">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 20px;">No hay requisitos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($entregas->isNotEmpty())
<div class="doc-container">
    <h2 style="color:#0b2d6b; margin-bottom: 8px;">Documentos presentados</h2>
    <p class="section-desc">Documentos entregados por personas. Aqui se validan u observan.</p>

    <div class="table-responsive">
        <table class="doc-table">
            <thead>
                <tr>
                    <th>CI</th>
                    <th>Persona</th>
                    <th>Documento</th>
                    <th>Destinado a</th>
                    <th>Estado</th>
                    <th>Observacion</th>
                    <th>Fecha revision</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entregas as $item)
                    <tr>
                        <td>{{ $item->ci }}</td>
                        <td>{{ $item->nombre_persona }} {{ $item->apellido }}</td>
                        <td>{{ $item->nombre_documento }}</td>
                        <td>{{ $item->destinado_a }}</td>
                        <td><span class="estado estado-{{ $item->estado }}">{{ ucfirst($item->estado) }}</span></td>
                        <td>{{ $item->observacion ?? 'Sin observacion' }}</td>
                        <td>{{ $item->fecha_revision ?? 'Sin revisar' }}</td>
                        <td>
                            <div class="acciones">
                                <form action="{{ route('documentos.validar', [$item->id_persona, $item->id_documento]) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <button class="btn-validar" type="submit">Validar</button>
                                </form>
                                <form action="{{ route('documentos.observar', [$item->id_persona, $item->id_documento]) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="observacion" class="observacion-input" placeholder="Observacion" required>
                                    <button class="btn-observar" type="submit">Observar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="modal-fondo" id="modalEditar">
    <div class="modal-contenido">
        <div class="modal-header">
            <h2>Editar requisito documental</h2>
            <button type="button" class="btn-cerrar" onclick="cerrarModalEditar()">X</button>
        </div>

        <form id="formEditarDocumento" method="POST" class="doc-form">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Tipo de documento</label>
                <input type="text" name="tipo_documento" id="edit_tipo_documento" required>
            </div>

            <div class="form-group">
                <label>Nombre del documento</label>
                <input type="text" name="nombre" id="edit_nombre" required>
            </div>

            <div class="form-group">
                <label>Destinado a</label>
                <select name="destinado_a" id="edit_destinado_a" required>
                    <option value="Postulantes">Postulantes</option>
                    <option value="Docentes">Docentes</option>
                </select>
            </div>

            <div class="form-group full">
                <label>Descripcion</label>
                <textarea name="descripcion" id="edit_descripcion"></textarea>
            </div>

            <div class="form-group full">
                <button type="submit" class="btn-primary">Guardar cambios</button>
                <button type="button" class="btn-secondary" onclick="cerrarModalEditar()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalEditar = document.getElementById('modalEditar');
        const formEditar = document.getElementById('formEditarDocumento');

        document.querySelectorAll('.btn-abrir-editar').forEach(function (boton) {
            boton.addEventListener('click', function () {
                formEditar.action = this.dataset.url;

                document.getElementById('edit_tipo_documento').value = this.dataset.tipo || '';
                document.getElementById('edit_nombre').value = this.dataset.nombre || '';
                document.getElementById('edit_destinado_a').value = this.dataset.destinado || '';
                document.getElementById('edit_descripcion').value = this.dataset.descripcion || '';

                modalEditar.classList.add('activo');
            });
        });
    });

    function cerrarModalEditar() {
        document.getElementById('modalEditar').classList.remove('activo');
    }
</script>
@endsection
