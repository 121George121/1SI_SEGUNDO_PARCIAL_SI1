@extends('Gestion_Financiera.Menu')

@section('content')

<h1 class="titulo">CU05 - Gestionar Pagos</h1>
<p class="subtitulo">Generar conceptos de pago y procesar pagos de inscripción.</p>

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
    .btn-danger,
    .btn-secondary {
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
    .btn-success { background: #16a34a; }
    .btn-warning { background: #f59e0b; }
    .btn-danger { background: #dc2626; }
    .btn-secondary { background: #6b7280; }

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

    .acciones {
        display: flex;
        flex-direction: column;
        gap: 8px;
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

    .estado-revision {
        background: #fef3c7;
        color: #92400e;
    }

    .estado-error {
        background: #fee2e2;
        color: #991b1b;
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
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Generar Concepto de Pago</h2>

    <form action="{{ route('pagos.store') }}" method="POST" class="form-grid">
        @csrf

        <div class="form-group">
            <label>Concepto de Pago</label>
            <input type="text" name="concepto_pago" placeholder="Ej: Pago Inscripción" required>
        </div>

        <div class="form-group">
            <label>Monto</label>
            <input type="number" step="0.01" name="monto" placeholder="Ej: 350" required>
        </div>

        <div class="form-group">
            <label>Estado</label>
            <select name="estado_pago" required>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>

        <div class="form-group full">
            <label>Observaciones</label>
            <textarea name="observaciones" placeholder="Descripción del pago"></textarea>
        </div>

        <div class="form-group full">
            <button type="submit" class="btn-primary">
                Registrar Concepto
            </button>
        </div>
    </form>
</div>

<div class="card-box">
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Conceptos de Pago Registrados</h2>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Concepto</th>
                    <th>Monto</th>
                    <th>Estado</th>
                    <th>Observaciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($conceptos as $concepto)
                    <tr>
                        <td>{{ $concepto->id_pago }}</td>
                        <td>{{ $concepto->concepto_pago }}</td>
                        <td>{{ $concepto->monto }}</td>
                        <td>
                            <span class="estado {{ $concepto->estado_pago == 'activo' ? 'estado-ok' : 'estado-error' }}">
                                {{ $concepto->estado_pago }}
                            </span>
                        </td>
                        <td>{{ $concepto->observaciones ?? '-' }}</td>
                        <td>
                            <div class="acciones">
                                <details>
                                    <summary>Editar Concepto</summary>

                                    <form action="{{ route('pagos.update', $concepto->id_pago) }}" method="POST" class="inline-form">
                                        @csrf
                                        @method('PUT')

                                        <input type="text" name="concepto_pago" value="{{ $concepto->concepto_pago }}" required>
                                        <input type="number" step="0.01" name="monto" value="{{ $concepto->monto }}" required>

                                        <select name="estado_pago" required>
                                            <option value="activo" {{ $concepto->estado_pago == 'activo' ? 'selected' : '' }}>Activo</option>
                                            <option value="inactivo" {{ $concepto->estado_pago == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                        </select>

                                        <input type="text" name="observaciones" value="{{ $concepto->observaciones }}">

                                        <button type="submit" class="btn-warning">
                                            Actualizar
                                        </button>
                                    </form>
                                </details>

                                <form action="{{ route('pagos.destroy', $concepto->id_pago) }}" 
                                      method="POST"
                                      onsubmit="return confirm('¿Seguro que deseas eliminar este concepto de pago?')">
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
                        <td colspan="6" style="text-align:center; padding:20px;">
                            No hay conceptos de pago registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card-box">
    <h2 style="color:#0b2d6b; margin-bottom:16px;">Pagos por Inscripción</h2>

    <div style="margin-bottom:16px;">
        <label style="font-weight:bold; color:#0b2d6b; display:block; margin-bottom:6px;">
            Buscar Pago
        </label>

        <input 
            type="text" 
            id="buscarPago" 
            placeholder="Buscar por código, CI, postulante, concepto, carrera o estado..."
            style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:14px;"
        >
    </div>

    <div class="table-responsive">
        <table id="tablaPagos">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>CI</th>
                    <th>Postulante</th>
                    <th>Carrera</th>
                    <th>Estado Inscripción</th>
                    <th>Concepto</th>
                    <th>Monto</th>
                    <th>Estado Pago</th>
                    <th>Comprobante</th>
                    <th>Fecha Pago</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($pagosInscripcion as $item)
                    <tr>
                        <td>{{ $item->codigo_inscripcion }}</td>
                        <td>{{ $item->ci }}</td>
                        <td>{{ $item->nombre }} {{ $item->apellido }}</td>
                        <td>{{ $item->carrera_principal ?? 'Sin carrera' }}</td>
                        <td>
                            <span class="estado {{ $item->estado_inscripcion == 'Inscrito' ? 'estado-ok' : 'estado-revision' }}">
                                {{ $item->estado_inscripcion }}
                            </span>
                        </td>
                        <td>{{ $item->concepto_pago }}</td>
                        <td>{{ $item->monto }}</td>
                        <td>
                            @if($item->estado_pago_inscripcion == 'Liquidado')
                                <span class="estado estado-ok">Liquidado</span>
                            @elseif($item->estado_pago_inscripcion == 'Rechazado')
                                <span class="estado estado-error">Rechazado</span>
                            @else
                                <span class="estado estado-revision">Pendiente</span>
                            @endif
                        </td>
                        <td>{{ $item->nro_comprobante ?? 'Sin comprobante' }}</td>
                        <td>{{ $item->fecha_pago ?? '-' }}</td>
                        <td>
                            <details>
                                <summary>Procesar Pago</summary>

                                <form action="{{ route('pagos.inscripcion.guardar') }}" method="POST" class="inline-form">
                                    @csrf

                                    <input type="hidden" name="Id_pago" value="{{ $item->id_pago }}">
                                    <input type="hidden" name="Codigo_inscripcion" value="{{ $item->codigo_inscripcion }}">

                                    <select name="estado_pago_inscripcion" required>
                                        <option value="Pendiente" {{ $item->estado_pago_inscripcion == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="Liquidado" {{ $item->estado_pago_inscripcion == 'Liquidado' ? 'selected' : '' }}>Liquidado</option>
                                        <option value="Rechazado" {{ $item->estado_pago_inscripcion == 'Rechazado' ? 'selected' : '' }}>Rechazado</option>
                                    </select>

                                    <input 
                                        type="text" 
                                        name="nro_comprobante" 
                                        value="{{ $item->nro_comprobante }}"
                                        placeholder="Nro comprobante"
                                    >

                                    <input 
                                        type="date" 
                                        name="fecha_emision" 
                                        value="{{ $item->fecha_emision ? substr($item->fecha_emision, 0, 10) : date('Y-m-d') }}"
                                    >

                                    <button type="submit" class="btn-success">
                                        Guardar Pago
                                    </button>
                                </form>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="text-align:center; padding:20px;">
                            No hay pagos para mostrar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const buscador = document.getElementById('buscarPago');
    const tabla = document.getElementById('tablaPagos');

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