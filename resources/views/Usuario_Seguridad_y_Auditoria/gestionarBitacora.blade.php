@extends('Usuario_Seguridad_y_Auditoria.Menu')

@section('title', 'Gestionar Bitácora')

@section('content')

<h2 style="font-size:26px;font-weight:bold;margin-bottom:12px;color:#0b2d6b;">
    CU19 - Auditoría de Bitácora
</h2>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
@endif

<!-- Panel de Filtros -->
<div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 24px;">
    <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 16px; color: #0b2d6b; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">Filtros de Búsqueda</h3>
    
    <form action="{{ route('bitacora.index') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
        <!-- Búsqueda texto -->
        <div style="display: flex; flex-direction: column; gap: 6px;">
            <label style="font-size: 12px; font-weight: bold; color: #64748b;">Texto de búsqueda</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por descripción, IP..." style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; outline: none;">
        </div>

        <!-- Módulo / Tipo -->
        <div style="display: flex; flex-direction: column; gap: 6px;">
            <label style="font-size: 12px; font-weight: bold; color: #64748b;">Módulo / Tipo</label>
            <select name="tipo" style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; outline: none; background-color: white;">
                <option value="">Todos los módulos</option>
                @foreach($tipos as $t)
                    <option value="{{ $t }}" {{ request('tipo') == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </div>

        <!-- Usuario -->
        <div style="display: flex; flex-direction: column; gap: 6px;">
            <label style="font-size: 12px; font-weight: bold; color: #64748b;">Usuario</label>
            <select name="usuario_id" style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; outline: none; background-color: white;">
                <option value="">Todos los usuarios</option>
                @foreach($usuarios as $u)
                    <option value="{{ $u->Id_usuario }}" {{ request('usuario_id') == $u->Id_usuario ? 'selected' : '' }}>{{ $u->nombre_usuario }}</option>
                @endforeach
            </select>
        </div>

        <!-- Estado -->
        <div style="display: flex; flex-direction: column; gap: 6px;">
            <label style="font-size: 12px; font-weight: bold; color: #64748b;">Estado</label>
            <select name="estado" style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; outline: none; background-color: white;">
                <option value="">Todos</option>
                <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>

        <!-- Fecha Inicio -->
        <div style="display: flex; flex-direction: column; gap: 6px;">
            <label style="font-size: 12px; font-weight: bold; color: #64748b;">Fecha Inicio</label>
            <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; outline: none;">
        </div>

        <!-- Fecha Fin -->
        <div style="display: flex; flex-direction: column; gap: 6px;">
            <label style="font-size: 12px; font-weight: bold; color: #64748b;">Fecha Fin</label>
            <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; outline: none;">
        </div>

        <!-- Botones de Acción de Filtro -->
        <div style="grid-column: 1 / -1; display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
            <a href="{{ route('bitacora.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; justify-content: center; height: 40px; min-width: 130px; font-weight: bold;">
                Limpiar Filtros
            </a>
            <button type="submit" class="btn btn-primary" style="height: 40px; min-width: 130px; font-weight: bold; cursor: pointer;">
                Aplicar Filtros
            </button>
        </div>
    </form>
</div>

<!-- Tabla de Logs -->
<div style="overflow-x: auto; background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 12px;">
    <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
        <thead>
            <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                <th style="padding: 14px 12px; font-weight: bold; color: #475569; font-size: 13px;">ID</th>
                <th style="padding: 14px 12px; font-weight: bold; color: #475569; font-size: 13px;">Usuario</th>
                <th style="padding: 14px 12px; font-weight: bold; color: #475569; font-size: 13px;">Módulo / Tipo</th>
                <th style="padding: 14px 12px; font-weight: bold; color: #475569; font-size: 13px;">Descripción</th>
                <th style="padding: 14px 12px; font-weight: bold; color: #475569; font-size: 13px;">Fecha / Hora</th>
                <th style="padding: 14px 12px; font-weight: bold; color: #475569; font-size: 13px;">Dirección IP</th>
                <th style="padding: 14px 12px; font-weight: bold; color: #475569; font-size: 13px;">Estado</th>
                <th style="padding: 14px 12px; font-weight: bold; color: #475569; font-size: 13px; text-align: center;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">
                    <td style="padding: 12px; font-weight: 600; color: #0b2d6b; font-size: 14px;">#{{ $log->Id_bitacora }}</td>
                    <td style="padding: 12px; font-size: 14px; color: #334155;">
                        {{ $log->usuario ? $log->usuario->nombre_usuario : 'Sistema' }}
                    </td>
                    <td style="padding: 12px; font-size: 13px;">
                        @php
                            $tipoUpper = strtoupper(trim($log->tipo));
                            $bg = '#e2e8f0';
                            $fg = '#475569';
                            if ($tipoUpper === 'GET') { $bg = '#dbeafe'; $fg = '#1e3a8a'; }
                            elseif ($tipoUpper === 'POST') { $bg = '#dcfce7'; $fg = '#15803d'; }
                            elseif ($tipoUpper === 'PUT' || $tipoUpper === 'PATCH') { $bg = '#fef9c3'; $fg = '#a16207'; }
                            elseif ($tipoUpper === 'DELETE') { $bg = '#fee2e2'; $fg = '#b91c1c'; }
                            elseif ($tipoUpper === 'LOG') { $bg = '#f1f5f9'; $fg = '#334155'; }
                        @endphp
                        <span style="display: inline-block; background-color: {{ $bg }}; color: {{ $fg }}; padding: 4px 8px; border-radius: 6px; font-weight: bold; font-size: 11px;">
                            {{ $log->tipo }}
                        </span>
                    </td>
                    <td style="padding: 12px; font-size: 14px; color: #334155; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        {{ $log->descripcion }}
                    </td>
                    <td style="padding: 12px; font-size: 13px; color: #64748b;">
                        {{ \Carbon\Carbon::parse($log->fecha)->format('d/m/Y') }} <span style="color: #94a3b8; font-size: 11px;">{{ $log->hora }}</span>
                    </td>
                    <td style="padding: 12px; font-size: 13px; color: #64748b;">
                        {{ $log->detalle ? $log->detalle->direccion_ip : '127.0.0.1' }}
                    </td>
                    <td style="padding: 12px; font-size: 13px;">
                        @if(strtolower(trim($log->estado)) === 'activo')
                            <span style="background-color: #d1fae5; color: #065f46; padding: 4px 8px; border-radius: 6px; font-weight: bold; font-size: 12px;">
                                Activo
                            </span>
                        @else
                            <span style="background-color: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 6px; font-weight: bold; font-size: 12px;">
                                Inactivo
                            </span>
                        @endif
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        <button type="button" class="btn btn-secondary" onclick="verDetalle('{{ json_encode($log) }}')" style="font-size: 12px; font-weight: bold; padding: 6px 10px; margin-right: 4px;">
                            Ver Detalle
                        </button>
                        
                        @if(strtolower(trim($log->estado)) === 'activo')
                            <form action="{{ route('bitacora.destroy', $log->Id_bitacora) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('¿Está seguro de desactivar esta entrada de bitácora?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" style="font-size: 12px; font-weight: bold; padding: 6px 10px;">
                                    Desactivar
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 30px; color: #64748b; font-style: italic;">
                        No se encontraron registros de auditoría en la bitácora con los filtros aplicados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Modal para Detalle de Bitácora -->
<div id="detalleModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
    <div class="modal-content" style="background-color: #fefefe; margin: 8% auto; padding: 24px; border-radius: 12px; width: 85%; max-width: 650px; box-shadow: 0 4px 24px rgba(0,0,0,0.15); position: relative; font-family: Arial, sans-serif;">
        <span onclick="cerrarModal()" style="color: #94a3b8; float: right; font-size: 28px; font-weight: bold; cursor: pointer; position: absolute; right: 20px; top: 12px; line-height: 1;">&times;</span>
        
        <h3 style="margin-bottom: 20px; color: #0b2d6b; font-size: 20px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; font-weight: bold;">
            Detalles Completos de Auditoría
        </h3>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
            <div>
                <p style="margin-bottom: 8px; font-size: 14px; color: #475569;"><strong style="color: #1e293b;">Módulo / Tipo:</strong> <span id="modal-tipo" style="display: inline-block; background-color: #dbeafe; color: #1e3a8a; padding: 2px 6px; border-radius: 4px; font-weight: 500; font-size: 12px;"></span></p>
                <p style="margin-bottom: 8px; font-size: 14px; color: #475569;"><strong style="color: #1e293b;">Fecha / Hora:</strong> <span id="modal-fecha-hora"></span></p>
                <p style="margin-bottom: 8px; font-size: 14px; color: #475569;"><strong style="color: #1e293b;">Usuario:</strong> <span id="modal-usuario" style="font-weight: 600;"></span></p>
            </div>
            <div>
                <p style="margin-bottom: 8px; font-size: 14px; color: #475569;"><strong style="color: #1e293b;">Dirección IP:</strong> <span id="modal-ip" style="font-family: monospace; font-size: 13px; background-color: #f8fafc; padding: 2px 6px; border-radius: 4px; border: 1px solid #e2e8f0;"></span></p>
                <p style="margin-bottom: 8px; font-size: 14px; color: #475569;"><strong style="color: #1e293b;">Hora Inicio / Fin:</strong> <span id="modal-horas"></span></p>
                <p style="margin-bottom: 8px; font-size: 14px; color: #475569;"><strong style="color: #1e293b;">Estado:</strong> <span id="modal-estado"></span></p>
            </div>
        </div>

        <div style="border-top: 1px solid #f1f5f9; padding-top: 15px; margin-bottom: 15px;">
            <p style="font-size: 14px; font-weight: bold; color: #1e293b; margin-bottom: 6px;">Descripción de la Actividad:</p>
            <p id="modal-descripcion" style="background: #f8fafc; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; color: #334155; font-size: 13.5px; line-height: 1.5; margin: 0;"></p>
        </div>

        <div style="border-top: 1px solid #f1f5f9; padding-top: 15px;">
            <p style="font-size: 14px; font-weight: bold; color: #1e293b; margin-bottom: 6px;">Acción Detallada (Detalle Bitácora):</p>
            <p id="modal-accion" style="background: #f8fafc; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; color: #334155; font-size: 13px; line-height: 1.5; font-family: monospace; white-space: pre-wrap; margin: 0; max-height: 150px; overflow-y: auto;"></p>
        </div>
        
        <div style="text-align: right; margin-top: 24px; border-top: 1px solid #f1f5f9; padding-top: 16px;">
            <button type="button" class="btn btn-secondary" onclick="cerrarModal()" style="font-weight: bold; padding: 8px 16px;">
                Cerrar
            </button>
        </div>
    </div>
</div>

<script>
    function verDetalle(logJson) {
        const log = JSON.parse(logJson);
        
        document.getElementById('modal-tipo').innerText = log.tipo || '-';
        
        // Formatear Fecha
        let fechaFormateada = log.fecha;
        if (log.fecha) {
            const parts = log.fecha.split('-');
            if (parts.length === 3) {
                fechaFormateada = `${parts[2]}/${parts[1]}/${parts[0]}`;
            }
        }
        document.getElementById('modal-fecha-hora').innerText = `${fechaFormateada} - ${log.hora || ''}`;
        document.getElementById('modal-usuario').innerText = log.usuario ? log.usuario.nombre_usuario : 'Sistema / Desconocido';
        
        // Detalle
        const detalle = log.detalle || {};
        document.getElementById('modal-ip').innerText = detalle.direccion_ip || '127.0.0.1';
        document.getElementById('modal-horas').innerText = `${detalle.hora_inicio || '-'} / ${detalle.hora_fin || '-'}`;
        
        // Estado
        const estadoElem = document.getElementById('modal-estado');
        if (log.estado && log.estado.toLowerCase().trim() === 'activo') {
            estadoElem.innerHTML = `<span style="background-color: #d1fae5; color: #065f46; padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 11px;">Activo</span>`;
        } else {
            estadoElem.innerHTML = `<span style="background-color: #fee2e2; color: #991b1b; padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 11px;">Inactivo</span>`;
        }
        
        document.getElementById('modal-descripcion').innerText = log.descripcion || '-';
        document.getElementById('modal-accion').innerText = detalle.accion || log.descripcion || 'Sin detalles registrados.';
        
        document.getElementById('detalleModal').style.display = 'block';
    }

    function cerrarModal() {
        document.getElementById('detalleModal').style.display = 'none';
    }

    // Cerrar si hace click fuera
    window.onclick = function(event) {
        const modal = document.getElementById('detalleModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }
</script>

@endsection
