@extends('Gestion_Financiera.Menu')

@section('content')

<h1 class="titulo">CU05 - Gestionar Pagos</h1>
<p class="subtitulo">Generar conceptos de pago, asignar aranceles y procesar pagos mediante pasarelas o registro manual.</p>

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
        font-size: 14px;
        text-align: center;
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

    /* Cards de totales */
    .cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.08);
        text-align: center;
        border-top: 4px solid #0b2d6b;
    }

    .card h2 {
        font-size: 14px;
        color: #777;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .card p {
        font-size: 24px;
        font-weight: bold;
        color: #0b2d6b;
        margin-bottom: 4px;
    }

    .card span {
        font-size: 13px;
        color: #6b7280;
    }

    .card .highlight-green {
        color: #16a34a;
        font-weight: bold;
    }

    .card .highlight-amber {
        color: #d97706;
        font-weight: bold;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .full {
            grid-column: 1;
        }
        
        .cards {
            grid-template-columns: 1fr;
        }
    }
</style>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

@php
    $pagosPendientes = $pagosInscripcion->where('estado_pago_inscripcion', 'Pendiente');
    $pagosLiquidados = $pagosInscripcion->where('estado_pago_inscripcion', 'Liquidado');
    
    $cantPendientes = $pagosPendientes->count();
    $montoPendiente = $pagosPendientes->sum('monto');
    
    $cantRealizados = $pagosLiquidados->count();
    $totalPagado = $pagosLiquidados->sum('monto');
    
    $ultimoPago = '-';
    if ($cantRealizados > 0) {
        $ultimoPago = $pagosLiquidados->max('fecha_pago') ?? '-';
    }
@endphp

<!-- Panel de Totales -->
<div class="cards">
    <div class="card">
        <h2>Pagos Pendientes</h2>
        <p>Bs. {{ number_format($montoPendiente, 2) }}</p>
        <span class="highlight-amber">{{ $cantPendientes }} pago(s) pendiente(s)</span>
    </div>
    <div class="card" style="border-top-color: #16a34a;">
        <h2>Total Recaudado</h2>
        <p>Bs. {{ number_format($totalPagado, 2) }}</p>
        <span class="highlight-green">{{ $cantRealizados }} pago(s) liquidado(s)</span>
    </div>
    <div class="card" style="border-top-color: #f59e0b;">
        <h2>Último Pago</h2>
        <p style="font-size: 20px; padding: 3px 0;">{{ $ultimoPago }}</p>
        <span>Fecha de última transacción</span>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- Generar Concepto de Pago -->
    <div class="card-box" style="margin-bottom: 0;">
        <h2 style="color:#0b2d6b; margin-bottom:16px;">Generar Concepto de Pago</h2>

        <form action="{{ route('pagos.store') }}" method="POST" class="form-grid">
            @csrf

            <div class="form-group">
                <label>Concepto de Pago</label>
                <input type="text" name="concepto_pago" placeholder="Ej: Pago Inscripción" required>
            </div>

            <div class="form-group">
                <label>Monto (Bs.)</label>
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
                <textarea name="observaciones" placeholder="Descripción del concepto" rows="2"></textarea>
            </div>

            <div class="form-group full">
                <button type="submit" class="btn-primary">
                    Registrar Concepto
                </button>
            </div>
        </form>
    </div>

    <!-- Asignar Concepto de Pago a Postulante -->
    <div class="card-box" style="margin-bottom: 0;">
        <h2 style="color:#0b2d6b; margin-bottom:16px;">Asignar Concepto de Pago</h2>

        <form action="{{ route('pagos.asignar') }}" method="POST" class="form-grid">
            @csrf

            <div class="form-group">
                <label>Concepto de Pago</label>
                <select name="Id_pago" required>
                    <option value="">Seleccione un concepto...</option>
                    @foreach($conceptos as $concepto)
                        @if($concepto->estado_pago == 'activo')
                            <option value="{{ $concepto->id_pago }}">
                                {{ $concepto->concepto_pago }} - Bs. {{ $concepto->monto }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Postulante / Inscripción</label>
                <select name="Codigo_inscripcion">
                    <option value="">Seleccione una inscripción (para asignación individual)...</option>
                    @foreach($inscripciones as $inscripcion)
                        <option value="{{ $inscripcion->codigo_inscripcion }}">
                            CI: {{ $inscripcion->ci }} - {{ $inscripcion->nombre }} {{ $inscripcion->apellido }} (#{{ $inscripcion->codigo_inscripcion }})
                        </option>
                    @endforeach
                </select>
                <small style="color: #6b7280; margin-top: 4px;">Dejar vacío si va a asignar a todos los postulantes.</small>
            </div>

            <div class="form-group full" style="margin-top: 30px; display: flex; gap: 10px;">
                <button type="submit" class="btn-success" style="flex: 1;">
                    Asignar Individual
                </button>
                <button type="submit" name="asignar_todos" value="1" class="btn-danger" style="flex: 1; background: #d97706;">
                    Asignar a Todos
                </button>
            </div>
        </form>
    </div>
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
                        <td>Bs. {{ number_format($concepto->monto, 2) }}</td>
                        <td>
                            <span class="estado {{ $concepto->estado_pago == 'activo' ? 'estado-ok' : 'estado-error' }}">
                                {{ ucfirst($concepto->estado_pago) }}
                            </span>
                        </td>
                        <td>{{ $concepto->observaciones ?? '-' }}</td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <details style="position: relative;">
                                    <summary class="btn-warning" style="list-style: none; padding: 6px 12px; border-radius: 6px;">Editar</summary>
                                    
                                    <form action="{{ route('pagos.update', $concepto->id_pago) }}" method="POST" class="inline-form" style="position: absolute; background: white; border: 1px solid #ccc; padding: 12px; border-radius: 8px; z-index: 10; width: 280px; top: 30px; left: 0; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                                        @csrf
                                        @method('PUT')

                                        <div style="margin-bottom: 8px;">
                                            <input type="text" name="concepto_pago" value="{{ $concepto->concepto_pago }}" placeholder="Concepto" required style="width:100%;">
                                        </div>
                                        <div style="margin-bottom: 8px;">
                                            <input type="number" step="0.01" name="monto" value="{{ $concepto->monto }}" placeholder="Monto" required style="width:100%;">
                                        </div>
                                        <div style="margin-bottom: 8px;">
                                            <select name="estado_pago" required style="width:100%;">
                                                <option value="activo" {{ $concepto->estado_pago == 'activo' ? 'selected' : '' }}>Activo</option>
                                                <option value="inactivo" {{ $concepto->estado_pago == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                            </select>
                                        </div>
                                        <div style="margin-bottom: 8px;">
                                            <input type="text" name="observaciones" value="{{ $concepto->observaciones }}" placeholder="Observaciones" style="width:100%;">
                                        </div>

                                        <button type="submit" class="btn-warning" style="width: 100%;">
                                            Actualizar
                                        </button>
                                    </form>
                                </details>

                                <form action="{{ route('pagos.destroy', $concepto->id_pago) }}" 
                                      method="POST"
                                      onsubmit="return confirm('¿Seguro que deseas eliminar este concepto de pago?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn-danger" style="padding: 6px 12px; border-radius: 6px;">
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
                        <td>Bs. {{ number_format($item->monto, 2) }}</td>
                        <td>
                            @if($item->estado_pago_inscripcion == 'Liquidado')
                                <span class="estado estado-ok">Liquidado</span>
                            @elseif($item->estado_pago_inscripcion == 'Rechazado')
                                <span class="estado estado-error">Rechazado</span>
                            @else
                                <span class="estado estado-revision">Pendiente</span>
                            @endif
                        </td>
                        <td>
                            @if($item->id_comprobante)
                                <a href="{{ route('emitirComprobante', $item->id_comprobante) }}" target="_blank" style="color: #0b2d6b; font-weight: bold; text-decoration: underline;">
                                    {{ $item->nro_comprobante }}
                                </a>
                            @else
                                Sin comprobante
                            @endif
                        </td>
                        <td>{{ $item->fecha_pago ?? '-' }}</td>
                        <td>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                @if($item->estado_pago_inscripcion !== 'Liquidado')
                                    <!-- Botón PayPal -->
                                    <form action="{{ route('pagos.paypal.pagar', [$item->id_pago, $item->codigo_inscripcion]) }}" method="POST" style="margin:0;">
                                        @csrf
                                        <button type="submit" class="btn-primary" style="background:#0070ba; padding: 6px 12px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                            PayPal
                                        </button>
                                    </form>

                                    <!-- Botón Efectivo -->
                                    <form action="{{ route('pagos.inscripcion.guardar') }}" method="POST" style="margin:0;" onsubmit="return confirm('¿Confirmar pago en efectivo?');">
                                        @csrf
                                        <input type="hidden" name="Id_pago" value="{{ $item->id_pago }}">
                                        <input type="hidden" name="Codigo_inscripcion" value="{{ $item->codigo_inscripcion }}">
                                        <input type="hidden" name="estado_pago_inscripcion" value="Liquidado">
                                        <input type="hidden" name="metodo_pago" value="efectivo">
                                        <input type="hidden" name="fecha_emision" value="{{ date('Y-m-d') }}">
                                        <button type="submit" class="btn-success" style="background:#16a34a; padding: 6px 12px; border-radius: 6px;">
                                            Efectivo
                                        </button>
                                    </form>

                                    <!-- Procesamiento Manual -->
                                    <details style="position: relative;">
                                        <summary class="btn-success" style="list-style: none; padding: 6px 12px; border-radius: 6px; text-align: center;">Manual</summary>
                                        
                                        <form action="{{ route('pagos.inscripcion.guardar') }}" method="POST" class="inline-form" style="position: absolute; background: white; border: 1px solid #ccc; padding: 12px; border-radius: 8px; z-index: 10; width: 280px; top: 30px; right: 0; box-shadow: 0 4px 12px rgba(0,0,0,0.15); text-align: left;">
                                            @csrf
                                            <input type="hidden" name="Id_pago" value="{{ $item->id_pago }}">
                                            <input type="hidden" name="Codigo_inscripcion" value="{{ $item->codigo_inscripcion }}">

                                            <div style="margin-bottom: 8px;">
                                                <label style="font-weight: bold; font-size: 12px; color:#0b2d6b;">Estado</label>
                                                <select name="estado_pago_inscripcion" required style="width: 100%;">
                                                    <option value="Pendiente" {{ $item->estado_pago_inscripcion == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                                    <option value="Liquidado" {{ $item->estado_pago_inscripcion == 'Liquidado' ? 'selected' : '' }}>Liquidado</option>
                                                    <option value="Rechazado" {{ $item->estado_pago_inscripcion == 'Rechazado' ? 'selected' : '' }}>Rechazado</option>
                                                </select>
                                            </div>
                                            <div style="margin-bottom: 8px;">
                                                <label style="font-weight: bold; font-size: 12px; color:#0b2d6b;">Nro Comprobante</label>
                                                <input type="text" name="nro_comprobante" value="{{ $item->nro_comprobante }}" placeholder="Opcional" style="width: 100%;">
                                            </div>
                                            <div style="margin-bottom: 8px;">
                                                <label style="font-weight: bold; font-size: 12px; color:#0b2d6b;">Fecha Emisión</label>
                                                <input type="date" name="fecha_emision" value="{{ $item->fecha_emision ? substr($item->fecha_emision, 0, 10) : date('Y-m-d') }}" style="width: 100%;">
                                            </div>

                                            <button type="submit" class="btn-success" style="width: 100%;">
                                                Guardar
                                            </button>
                                        </form>
                                    </details>
                                @else
                                    @if($item->id_comprobante)
                                        <a href="{{ route('emitirComprobante', $item->id_comprobante) }}" class="btn-secondary" style="padding: 6px 12px; border-radius: 6px; background: #4b5563;" target="_blank">
                                            PDF
                                        </a>
                                    @else
                                        <span style="color: #6b7280; font-size: 13px;">Liquidado</span>
                                    @endif
                                @endif
                            </div>
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