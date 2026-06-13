@extends('Usuario_Seguridad_y_Auditoria.Menu')

@section('title', $usuario ? 'Editar usuario' : 'Agregar usuario')

@section('content')

@php
    $persona = $usuario?->persona;
    $administrador = $administrador ?? null;

    $rolActual = null;

    if ($persona) {
        foreach ($rolesDisponibles as $campo => $nombre) {
            if ($persona->{$campo}) {
                $rolActual = $campo;
                break;
            }
        }
    }
@endphp

<style>
    /* Styling system for professional form */
    .form-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 28px;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 16px;
    }
    .form-header h2 {
        font-size: 26px;
        font-weight: 800;
        color: #1e3a8a;
        margin: 0;
    }
    .btn-volver {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #ffffff;
        color: #475569;
        border: 1px solid #cbd5e1;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.03);
    }
    .btn-volver:hover {
        background: #f8fafc;
        color: #0f172a;
        border-color: #94a3b8;
        box-shadow: 0 4px 6px rgba(0,0,0,0.06);
    }
    .btn-volver svg {
        width: 16px;
        height: 16px;
        stroke-width: 2.5;
    }
    .form-container {
        width: 100%;
        max-width: 100%;
    }
    .form-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
        padding: 28px;
        margin-bottom: 24px;
        border: 1px solid #f1f5f9;
        transition: all 0.3s ease;
    }
    .form-card:hover {
        box-shadow: 0 6px 24px rgba(15, 23, 42, 0.06);
    }
    .form-card h3 {
        font-size: 18px;
        font-weight: 750;
        color: #1e3a8a;
        margin-top: 0;
        margin-bottom: 24px;
        border-left: 4px solid #ef4444; /* Crimson Accent */
        padding-left: 12px;
    }
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-group label {
        font-size: 14px;
        font-weight: 650;
        color: #475569;
    }
    .form-group input,
    .form-group select {
        padding: 10px 14px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        font-size: 14.5px;
        color: #1e293b;
        outline: none;
        background: #f8fafc;
        transition: all 0.2s ease;
        width: 100%;
    }
    .form-group input:focus,
    .form-group select:focus {
        border-color: #1e3a8a;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.12);
    }
    .roles-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px;
    }
    .role-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        user-select: none;
    }
    .role-option:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .role-option input[type="radio"],
    .role-option input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #ef4444; /* Crimson Accent */
        cursor: pointer;
    }
    .role-option span {
        font-size: 14px;
        font-weight: 600;
        color: #334155;
    }
    .alert-error {
        background: #fef2f2;
        border-left: 4px solid #ef4444;
        color: #991b1b;
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 24px;
        font-size: 14px;
        font-weight: 600;
    }
    .alert-error ul {
        margin: 0;
        padding-left: 20px;
    }
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 12px;
        border-top: 1px solid #e2e8f0;
        padding-top: 20px;
    }
    .password-wrapper {
        position: relative;
        width: 100%;
    }
    .password-wrapper input {
        padding-right: 44px !important;
    }
    .btn-toggle-password {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: #64748b;
        display: flex;
        align-items: center;
        padding: 0;
    }
    .btn-toggle-password svg {
        width: 20px;
        height: 20px;
        stroke-width: 2;
    }
    .btn-submit {
        background: #ef4444; /* Crimson Red */
        color: #ffffff;
        padding: 12px 28px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 15px;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
        transition: all 0.2s ease;
    }
    .btn-submit:hover {
        background: #dc2626;
        box-shadow: 0 6px 16px rgba(220, 38, 38, 0.3);
        transform: translateY(-1px);
    }
    .btn-cancel {
        background: #e2e8f0;
        color: #475569;
        padding: 12px 28px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 15px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .btn-cancel:hover {
        background: #cbd5e1;
        color: #1e293b;
    }
</style>

<div class="form-container">
    <div class="form-header">
        <h2>{{ $usuario ? 'Editar Usuario' : 'Registrar Nuevo Usuario' }}</h2>
        <a href="{{ route('usuarios.index') }}" class="btn-volver">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Volver</span>
        </a>
    </div>

    @if($errors->any())
        <div class="alert-error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $usuario ? route('usuarios.update', $usuario->Id_usuario) : route('usuarios.store') }}">
        @csrf
        @if($usuario)
            @method('PUT')
        @endif

        <!-- Card 1: Datos de Persona -->
        <div class="form-card">
            <h3>Datos de Persona</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="ci">CI</label>
                    <input type="text" id="ci" name="ci" value="{{ old('ci', $persona->ci ?? '') }}" required placeholder="Número de Documento">
                </div>

                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $persona->nombre ?? '') }}" required placeholder="Nombres">
                </div>

                <div class="form-group">
                    <label for="apellido">Apellido</label>
                    <input type="text" id="apellido" name="apellido" value="{{ old('apellido', $persona->apellido ?? '') }}" required placeholder="Apellidos">
                </div>

                <div class="form-group">
                    <label for="sexo">Sexo</label>
                    <select id="sexo" name="sexo">
                        <option value="">Seleccione</option>
                        <option value="M" @selected(old('sexo', $persona->sexo ?? '') === 'M')>Masculino</option>
                        <option value="F" @selected(old('sexo', $persona->sexo ?? '') === 'F')>Femenino</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $persona->fecha_nacimiento ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" value="{{ old('telefono', $persona->telefono ?? '') }}" placeholder="Ej: 76543210">
                </div>

                <div class="form-group">
                    <label for="correo">Correo Electrónico</label>
                    <input type="email" id="correo" name="correo" value="{{ old('correo', $usuario->correo ?? $persona->correo ?? '') }}" required placeholder="ejemplo@correo.com">
                </div>

                <div class="form-group">
                    <label for="direccion">Dirección</label>
                    <input type="text" id="direccion" name="direccion" value="{{ old('direccion', $persona->direccion ?? '') }}" placeholder="Dirección de Domicilio">
                </div>
            </div>
        </div>

        <!-- Card 2: Datos de Cuenta -->
        <div class="form-card">
            <h3>Datos de Usuario</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="nombre_usuario">Nombre de Usuario</label>
                    <input type="text" id="nombre_usuario" name="nombre_usuario" value="{{ old('nombre_usuario', $usuario->nombre_usuario ?? '') }}" required placeholder="Nombre de usuario único">
                </div>

                @if($usuario)
                    <div class="form-group">
                        <label for="estado">Estado Usuario</label>
                        <select id="estado" name="estado">
                            <option value="activo" @selected(old('estado', $usuario->estado) === 'activo')>Activo</option>
                            <option value="inactivo" @selected(old('estado', $usuario->estado) === 'inactivo')>Inactivo</option>
                        </select>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1; margin-top: 8px; margin-bottom: 8px;">
                        <label class="role-option" style="background: #fdf2f2; border-color: #fca5a5;">
                            <input type="checkbox" id="cambiar_contrasena_check" name="cambiar_contrasena" value="1" 
                                @checked(old('cambiar_contrasena'))
                                onchange="togglePasswordFields(this.checked)">
                            <span style="color: #991b1b; font-weight: 700;">¿Desea cambiar la contraseña de este usuario?</span>
                        </label>
                    </div>
                @endif
            </div>

            <div id="passwordFieldsContainer" style="{{ $usuario ? 'display: none;' : '' }}">
                <div class="form-grid" style="margin-top: 12px;">
                    <div class="form-group">
                        <label for="contrasena">Contraseña {{ $usuario ? '(Nueva contraseña)' : '' }}</label>
                        <div class="password-wrapper">
                            <input type="password" id="contrasena" name="contrasena" placeholder="••••••••" {{ $usuario ? '' : 'required' }}>
                            <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('contrasena')">
                                <svg class="eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg class="eye-closed" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.024 10.024 0 014.168-5.117m1.96-1.785A9.964 9.964 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.071m-4.005-1.956A3.375 3.375 0 0012 9.75M9 9l6 6M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="contrasena_confirmation">Confirmar Contraseña</label>
                        <div class="password-wrapper">
                            <input type="password" id="contrasena_confirmation" name="contrasena_confirmation" placeholder="••••••••">
                            <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('contrasena_confirmation')">
                                <svg class="eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg class="eye-closed" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.024 10.024 0 014.168-5.117m1.96-1.785A9.964 9.964 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.071m-4.005-1.956A3.375 3.375 0 0012 9.75M9 9l6 6M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Roles -->
        <div class="form-card">
            <h3>Rol Asignado</h3>
            <div class="roles-grid">
                @foreach($rolesDisponibles as $campo => $nombre)
                    <label class="role-option">
                        <input type="radio" name="rol" value="{{ $campo }}" @checked(old('rol', $rolActual) === $campo)>
                        <span>{{ $nombre }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Card 4: Datos Superadministrador (Dinamico) -->
        <div id="camposSuperadministrador" class="form-card" style="display:none; background:#fbf7ff; border-color:#d8b4fe;">
            <h3>Datos de Superadministrador</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="cargo_superadmin">Cargo</label>
                    <input type="text" id="cargo_superadmin" name="cargo_superadmin" value="{{ old('cargo_superadmin', $superadministrador->cargo ?? '') }}" placeholder="Ej: Rector, Decano">
                </div>

                <div class="form-group">
                    <label for="estado_superadmin">Estado Superadministrador</label>
                    <select id="estado_superadmin" name="estado_superadmin">
                        <option value="activo" @selected(old('estado_superadmin', $superadministrador->estado ?? 'activo') === 'activo')>Activo</option>
                        <option value="inactivo" @selected(old('estado_superadmin', $superadministrador->estado ?? '') === 'inactivo')>Inactivo</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Card 5: Datos Administrador (Dinamico) -->
        <div id="camposAdministrador" class="form-card" style="display:none; background:#f0f7ff; border-color:#93c5fd;">
            <h3>Datos Administrativos</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="cargo">Cargo</label>
                    <input type="text" id="cargo" name="cargo" value="{{ old('cargo', $administrador->cargo ?? '') }}" placeholder="Ej: Administrador Académico">
                </div>

                <div class="form-group">
                    <label for="area">Área</label>
                    <input type="text" id="area" name="area" value="{{ old('area', $administrador->area ?? '') }}" placeholder="Ej: Dirección de Auditoría">
                </div>

                <div class="form-group">
                    <label for="estado_administrador">Estado Administrador</label>
                    <select id="estado_administrador" name="estado_administrador">
                        <option value="activo" @selected(old('estado_administrador', $administrador->estado ?? 'activo') === 'activo')>Activo</option>
                        <option value="inactivo" @selected(old('estado_administrador', $administrador->estado ?? '') === 'inactivo')>Inactivo</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Card 6: Datos Docente (Dinamico) -->
        <div id="camposDocente" class="form-card" style="display:none; background:#f0fdf4; border-color:#86efac;">
            <h3>Datos de Docente</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="anio_servicio">Años de Servicio</label>
                    <input type="number" id="anio_servicio" name="anio_servicio" value="{{ old('anio_servicio', $docente->anio_servicio ?? '') }}" min="0" placeholder="Ej: 5">
                </div>

                <div class="form-group">
                    <label for="estado_docente">Estado Docente</label>
                    <select id="estado_docente" name="estado_docente">
                        <option value="activo" @selected(old('estado_docente', $docente->estado ?? 'activo') === 'activo')>Activo</option>
                        <option value="inactivo" @selected(old('estado_docente', $docente->estado ?? '') === 'inactivo')>Inactivo</option>
                    </select>
                </div>
            </div>
        </div>


        <!-- Botones de Acción -->
        <div class="form-actions">
            <a href="{{ route('usuarios.index') }}" class="btn-cancel">Cancelar</a>
            <button type="submit" class="btn-submit">{{ $usuario ? 'Actualizar Usuario' : 'Registrar Usuario' }}</button>
        </div>
    </form>
</div>

<script>
// Toggle Password Visibility
function togglePasswordVisibility(id) {
    const input = document.getElementById(id);
    const wrapper = input.closest('.password-wrapper');
    const eyeOpen = wrapper.querySelector('.eye-open');
    const eyeClosed = wrapper.querySelector('.eye-closed');
    
    if (input.type === 'password') {
        input.type = 'text';
        eyeOpen.style.display = 'none';
        eyeClosed.style.display = 'block';
    } else {
        input.type = 'password';
        eyeOpen.style.display = 'block';
        eyeClosed.style.display = 'none';
    }
}

// Toggle Password Fields (Edit User Mode)
function togglePasswordFields(show) {
    const container = document.getElementById('passwordFieldsContainer');
    const passInput = document.getElementById('contrasena');
    const confirmInput = document.getElementById('contrasena_confirmation');
    
    if (show) {
        container.style.display = 'block';
        passInput.required = true;
    } else {
        container.style.display = 'none';
        passInput.required = false;
        passInput.value = '';
        confirmInput.value = '';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const camposSuperadministrador = document.getElementById('camposSuperadministrador');
    const camposAdministrador = document.getElementById('camposAdministrador');
    const camposDocente = document.getElementById('camposDocente');


    function verificarRoles() {
        const selectedRol = document.querySelector('input[name="rol"]:checked');
        const val = selectedRol ? selectedRol.value : null;

        camposSuperadministrador.style.display = (val === 'tipo_Superadministrador') ? 'block' : 'none';
        camposAdministrador.style.display = (val === 'tipo_Administrador') ? 'block' : 'none';
        camposDocente.style.display = (val === 'tipo_Docente') ? 'block' : 'none';

    }

    document.querySelectorAll('input[name="rol"]').forEach(function (radio) {
        radio.addEventListener('change', verificarRoles);
    });

    verificarRoles();

    @if($usuario)
        const changeCheck = document.getElementById('cambiar_contrasena_check');
        if (changeCheck) {
            togglePasswordFields(changeCheck.checked);
        }
    @endif
});
</script>

@endsection