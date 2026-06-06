@extends('Inscripcion_y_Documentacion.Menu')

@section('content')

<h1 class="titulo">Documentos del Postulante</h1>
<p class="subtitulo">Validación de documentos requeridos para completar la inscripción.</p>

<style>
    .card-box {
        background: #fff;
        padding: 22px;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.08);
        margin-bottom: 24px;
    }

    .info-postulante {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 20px;
    }

    .info-item {
        background: #f5f7fb;
        padding: 12px;
        border-radius: 8px;
        border-left: 4px solid #0b2d6b;
    }

    .info-item strong {
        color: #0b2d6b;
        display: block;
        margin-bottom: 4px;
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
        vertical-align: top;
    }

    .opciones {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }

    .opciones label {
        background: #f5f7fb;
        padding: 8px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
    }

    .observacion {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 6px;
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

    .btn-primary,
    .btn-secondary {
        border: none;
        padding: 10px 14px;
        border-radius: 7px;
        color: white;
        cursor: pointer;
        font-weight: bold;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary { background: #0b2d6b; }
    .btn-secondary { background: #6b7280; }

    @media (max-width: 768px) {
        .info-postulante {
            grid-template-columns: 1fr;
        }

        .opciones {
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

<div class="card-box">

    <div class="info-postulante">
        <div class="info-item">
            <strong>Código Inscripción</strong>
            {{ $inscripcion->codigo_inscripcion }}
        </div>

        <div class="info-item">
            <strong>CI</strong>
            {{ $inscripcion->ci }}
        </div>

        <div class="info-item">
            <strong>Postulante</strong>
            {{ $inscripcion->nombre }} {{ $inscripcion->apellido }}
        </div>

        <div class="info-item">
            <strong>Estado Inscripción</strong>
            <span class="estado {{ $inscripcion->estado_inscripcion == 'Inscrito' ? 'estado-ok' : 'estado-revision' }}">
                {{ $inscripcion->estado_inscripcion }}
            </span>
        </div>

        <div class="info-item">
            <strong>Estado de Pago</strong>
            @if($pago && strtolower(trim($pago->estado_pago)) == 'liquidado')
                <span class="estado estado-ok">Liquidado</span>
            @elseif($pago)
                <span class="estado estado-revision">{{ $pago->estado_pago }}</span>
            @else
                <span class="estado estado-error">Sin pago</span>
            @endif
        </div>
    </div>

    <form action="{{ route('inscripcion.documentos.guardar', $inscripcion->codigo_inscripcion) }}" method="POST">
        @csrf

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nombre Documento</th>
                        <th>Estado del Documento</th>
                        <th>Observación</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($documentos as $documento)
                        <tr>
                            <td>
                                <strong>{{ $documento->nombre }}</strong><br>
                                <small>{{ $documento->descripcion ?? 'Sin descripción' }}</small>
                            </td>

                            <td>
                                <div class="opciones">
                                    @foreach(['Aprobado', 'Presentado', 'Rechazado', 'No presentado'] as $estado)
                                        <label>
                                            <input 
                                                type="radio"
                                                name="estado_documento[{{ $documento->id_documento }}]"
                                                value="{{ $estado }}"
                                                {{ $documento->estado_documento == $estado ? 'checked' : '' }}
                                                required
                                            >
                                            {{ $estado }}
                                        </label>
                                    @endforeach
                                </div>
                            </td>

                            <td>
                                <input 
                                    type="text"
                                    name="observacion[{{ $documento->id_documento }}]"
                                    value="{{ $documento->observacion }}"
                                    class="observacion"
                                    placeholder="Observación opcional"
                                >
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align:center; padding:20px;">
                                No hay documentos destinados a Postulantes registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:16px; display:flex; gap:8px;">
            <button type="submit" class="btn-primary">
                Guardar Documentos
            </button>

            <a href="{{ route('inscripcion.index') }}" class="btn-secondary">
                Volver
            </a>
        </div>
    </form>
</div>

@endsection