<script>
// Initialize the state variable
window.isSidebarCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';

// Apply persisted desktop collapse state immediately on load to prevent layout flicker
(function() {
    if (window.innerWidth > 768) {
        if (window.isSidebarCollapsed) {
            document.body.classList.add('sidebar-collapsed');
        }
    }
})();
</script>

@php
    // Check for database records to disable/enable routes
    $hasGestiones = \Illuminate\Support\Facades\DB::table('gestion')->exists();
    $hasCarreras = \Illuminate\Support\Facades\DB::table('carrera')->exists();
    $hasModalidades = \Illuminate\Support\Facades\DB::table('modalidad')->exists();
    $hasTurnos = \Illuminate\Support\Facades\DB::table('turno')->exists();
    $hasMaterias = \Illuminate\Support\Facades\DB::table('materia')->exists();
    $hasHorarios = \Illuminate\Support\Facades\DB::table('horario')->exists();
    $hasInscripciones = \Illuminate\Support\Facades\DB::table('inscripcion')->exists();

    $disableInscripcion = !$hasGestiones || !$hasCarreras || !$hasModalidades || !$hasTurnos;
    
    $disableRest = !$hasCarreras || !$hasGestiones || !$hasTurnos || !$hasMaterias || !$hasHorarios;
    $disableGrupos = $disableRest;
    $disableAsignarPostulantes = $disableRest;
    $disableAsignarDocentes = $disableRest;
    $disableEvaluaciones = $disableRest;
    $disableAdmision = $disableRest;

    $disablePagos = !$hasInscripciones;
    $disableReportes = !$hasGestiones;

    // Detect active groups to automatically expand the folders
    $academicaActiva = request()->routeIs('carreras-cupos.*') || 
                       request()->routeIs('grupos.*') || 
                       request()->routeIs('postulantes-grupos.*') || 
                       request()->routeIs('gestiones.*') || 
                       request()->routeIs('modalidades.*') || 
                       request()->routeIs('turnos.*') || 
                       request()->routeIs('materias.*') || 
                       request()->routeIs('horarios.*') || 
                       request()->routeIs('asignaciones-docentes.*') || 
                       request()->routeIs('evaluaciones-notas.*') || 
                       request()->routeIs('evaluaciones.*') || 
                       request()->routeIs('notas.*') || 
                       request()->routeIs('admision.*');

    $inscripcionActiva = request()->routeIs('documentos.*') || request()->routeIs('inscripcion.*');
    
    $seguridadActiva = request()->routeIs('usuarios.*') || request()->routeIs('bitacora.*');
    
    $logisticaActiva = request()->routeIs('aulas.*') || 
                       request()->routeIs('docentes.*') || 
                       request()->routeIs('especialidades.*') || 
                       request()->routeIs('reportes.*');
                       
    $financieraActiva = request()->routeIs('pagos.*') || request()->routeIs('gestion-financiera.*') || request()->routeIs('paypal.*');
@endphp

<aside class="sidebar-unified">
    <div class="sidebar-header">
        <img src="{{ asset('images/LogoFICCT (1).png') }}" alt="Logo">
        <div class="header-text">
            <span class="title-main">CUP FICCT</span>
            <span class="title-sub">Preuniversitario</span>
        </div>
    </div>

    <div class="sidebar-scroll">
        <nav class="menu-modulo">
            <!-- Dashboard Link -->
            <a href="{{ route('menu') }}" class="menu-item {{ request()->routeIs('menu') || request()->routeIs('dashboard') ? 'activo' : '' }}">
                <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span>Dashboard</span>
            </a>

            <!-- Folder 1: Gestión Académica -->
            <div class="folder-container {{ $academicaActiva ? 'abierto' : '' }}">
                <button type="button" class="folder-header {{ $academicaActiva ? 'folder-activo-header' : '' }}" onclick="toggleFolder(this)">
                    <span class="header-left">
                        <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        <span>Gestión Académica</span>
                    </span>
                    <svg class="chevron-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
                <div class="folder-content">
                    <a href="{{ route('carreras-cupos.index') }}" class="sub-item {{ request()->routeIs('carreras-cupos.*') ? 'activo' : '' }}">
                        CU06 - Carreras y Cupos
                    </a>
                    <a href="{{ route('gestiones.index') }}" class="sub-item {{ request()->routeIs('gestiones.*') ? 'activo' : '' }}">
                        Gestionar Gestiones
                    </a>
                    <a href="{{ route('modalidades.index') }}" class="sub-item {{ request()->routeIs('modalidades.*') ? 'activo' : '' }}">
                        Gestionar Modalidades
                    </a>
                    <a href="{{ route('turnos.index') }}" class="sub-item {{ request()->routeIs('turnos.*') ? 'activo' : '' }}">
                        Gestionar Turnos
                    </a>
                    <a href="{{ route('materias.index') }}" class="sub-item {{ request()->routeIs('materias.*') ? 'activo' : '' }}">
                        CU14 - Materias
                    </a>
                    <a href="{{ route('horarios.index') }}" class="sub-item {{ request()->routeIs('horarios.*') ? 'activo' : '' }}">
                        CU14 - Horarios
                    </a>
                    <a href="{{ $disableGrupos ? '#' : route('grupos.index') }}" class="sub-item {{ request()->routeIs('grupos.*') ? 'activo' : '' }} {{ $disableGrupos ? 'deshabilitado' : '' }}" title="{{ $disableGrupos ? 'Requiere: Carreras, Gestiones, Turnos, Materias y Horarios.' : '' }}">
                        CU11 - Gestionar Grupos
                    </a>
                    <a href="{{ $disableAsignarDocentes ? '#' : route('asignaciones-docentes.index') }}" class="sub-item {{ request()->routeIs('asignaciones-docentes.*') ? 'activo' : '' }} {{ $disableAsignarDocentes ? 'deshabilitado' : '' }}" title="{{ $disableAsignarDocentes ? 'Requiere: Carreras, Gestiones, Turnos, Materias y Horarios.' : '' }}">
                        CU15 - Asignar Docentes
                    </a>
                    <a href="{{ $disableAsignarPostulantes ? '#' : route('postulantes-grupos.index') }}" class="sub-item {{ request()->routeIs('postulantes-grupos.*') ? 'activo' : '' }} {{ $disableAsignarPostulantes ? 'deshabilitado' : '' }}" title="{{ $disableAsignarPostulantes ? 'Requiere: Carreras, Gestiones, Turnos, Materias y Horarios.' : '' }}">
                        CU12 - Asignar Postulantes
                    </a>
                    <a href="{{ $disableEvaluaciones ? '#' : route('evaluaciones-notas.index') }}" class="sub-item {{ request()->routeIs('evaluaciones-notas.*') || request()->routeIs('evaluaciones.*') || request()->routeIs('notas.*') ? 'activo' : '' }} {{ $disableEvaluaciones ? 'deshabilitado' : '' }}" title="{{ $disableEvaluaciones ? 'Requiere: Carreras, Gestiones, Turnos, Materias y Horarios.' : '' }}">
                        CU16 - Evaluaciones y Notas
                    </a>
                    <a href="{{ $disableAdmision ? '#' : route('admision.index') }}" class="sub-item {{ request()->routeIs('admision.*') ? 'activo' : '' }} {{ $disableAdmision ? 'deshabilitado' : '' }}" title="{{ $disableAdmision ? 'Requiere: Carreras, Gestiones, Turnos, Materias y Horarios.' : '' }}">
                        CU17 - Admisión Final
                    </a>
                </div>
            </div>

            <!-- Folder 2: Inscripción y Documentación -->
            <div class="folder-container {{ $inscripcionActiva ? 'abierto' : '' }}">
                <button type="button" class="folder-header {{ $inscripcionActiva ? 'folder-activo-header' : '' }}" onclick="toggleFolder(this)">
                    <span class="header-left">
                        <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>Inscripción y Documentos</span>
                    </span>
                    <svg class="chevron-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
                <div class="folder-content">
                    <a href="{{ route('documentos.index') }}" class="sub-item {{ request()->routeIs('documentos.*') ? 'activo' : '' }}">
                        CU4 - Requisitos Documentos
                    </a>
                    <a href="{{ $disableInscripcion ? '#' : route('inscripcion.index') }}" class="sub-item {{ request()->routeIs('inscripcion.*') ? 'activo' : '' }} {{ $disableInscripcion ? 'deshabilitado' : '' }}" title="{{ $disableInscripcion ? 'Requiere: Gestiones, Carreras, Modalidades y Turnos.' : '' }}">
                        CU03 - Gestionar Inscripción
                    </a>
                </div>
            </div>

            <!-- Folder 3: Usuario, Seguridad y Auditoría -->
            <div class="folder-container {{ $seguridadActiva ? 'abierto' : '' }}">
                <button type="button" class="folder-header {{ $seguridadActiva ? 'folder-activo-header' : '' }}" onclick="toggleFolder(this)">
                    <span class="header-left">
                        <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        <span>Seguridad y Auditoría</span>
                    </span>
                    <svg class="chevron-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
                <div class="folder-content">
                    <a href="{{ route('usuarios.index') }}" class="sub-item {{ request()->routeIs('usuarios.*') ? 'activo' : '' }}">
                        CU2 - Usuarios y Roles
                    </a>
                    <a href="{{ route('bitacora.index') }}" class="sub-item {{ request()->routeIs('bitacora.*') ? 'activo' : '' }}">
                        CU19 - Bitácora
                    </a>
                </div>
            </div>

            <!-- Folder 4: Logística y Reportes -->
            <div class="folder-container {{ $logisticaActiva ? 'abierto' : '' }}">
                <button type="button" class="folder-header {{ $logisticaActiva ? 'folder-activo-header' : '' }}" onclick="toggleFolder(this)">
                    <span class="header-left">
                        <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <span>Logística y Recursos</span>
                    </span>
                    <svg class="chevron-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
                <div class="folder-content">
                    <a href="{{ route('aulas.index') }}" class="sub-item {{ request()->routeIs('aulas.*') ? 'activo' : '' }}">
                        CU08 - Gestionar Aulas
                    </a>
                    <a href="{{ route('docentes.index') }}" class="sub-item {{ request()->routeIs('docentes.*') ? 'activo' : '' }}">
                        CU09 - Gestionar Docentes
                    </a>
                    <a href="{{ route('especialidades.index') }}" class="sub-item {{ request()->routeIs('especialidades.*') ? 'activo' : '' }}">
                        Gestionar Especialidades
                    </a>
                    <a href="{{ $disableReportes ? '#' : route('reportes.index') }}" class="sub-item {{ request()->routeIs('reportes.*') ? 'activo' : '' }} {{ $disableReportes ? 'deshabilitado' : '' }}" title="{{ $disableReportes ? 'Requiere: Gestiones.' : '' }}">
                        CU18 - Generar Reportes
                    </a>
                </div>
            </div>

            <!-- Folder 5: Gestión Financiera -->
            <div class="folder-container {{ $financieraActiva ? 'abierto' : '' }}">
                <button type="button" class="folder-header {{ $financieraActiva ? 'folder-activo-header' : '' }}" onclick="toggleFolder(this)">
                    <span class="header-left">
                        <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        <span>Gestión Financiera</span>
                    </span>
                    <svg class="chevron-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
                <div class="folder-content">
                    <a href="{{ $disablePagos ? '#' : route('pagos.index') }}" class="sub-item {{ request()->routeIs('pagos.*') || request()->routeIs('gestion-financiera.*') || request()->routeIs('paypal.*') ? 'activo' : '' }} {{ $disablePagos ? 'deshabilitado' : '' }}" title="{{ $disablePagos ? 'Requiere: Inscripciones.' : '' }}">
                        CU05 - Gestionar Pagos
                    </a>
                </div>
            </div>
        </nav>
    </div>

    <div class="logout">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit">
                <svg class="logout-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span>Cerrar sesión</span>
            </button>
        </form>
    </div>
</aside>

<!-- Floating Responsive Sidebar Toggle Button -->
<button type="button" class="sidebar-toggle-btn" onclick="toggleSidebar()" aria-label="Toggle Sidebar">
    <!-- Icon when sidebar is open (Chevron Left arrow) -->
    <svg class="icon-close" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
    </svg>
    <!-- Icon when sidebar is closed (Hamburger Menu) -->
    <svg class="icon-open" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
</button>

<style>
/* CSS Styles for sidebar-unified - Professional Slate Dark Blue with Glassmorphism/Neumorphic Accents */
.sidebar-unified {
    width: 280px;
    background: #0f172a; /* Slate 900 */
    color: #f1f5f9;
    display: flex;
    flex-direction: column;
    position: fixed;
    height: 100vh;
    left: 0;
    top: 0;
    box-shadow: 4px 0 25px rgba(15, 23, 42, 0.25);
    z-index: 999;
    border-right: 1px solid rgba(255,255,255,0.05);
    transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow-x: hidden;
}

.sidebar-header {
    display: flex;
    align-items: center;
    padding: 24px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    gap: 12px;
    background: rgba(255, 255, 255, 0.01);
    transition: padding 0.3s cubic-bezier(0.4, 0, 0.2, 1), justify-content 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar-header img {
    width: 44px;
    height: 44px;
    object-fit: contain;
    filter: drop-shadow(0 4px 6px rgba(0,0,0,0.15));
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    flex-shrink: 0;
}

.header-text {
    display: flex;
    flex-direction: column;
    transition: opacity 0.2s ease, visibility 0.2s ease;
}

.title-main {
    font-size: 18px;
    font-weight: 850;
    letter-spacing: 0.5px;
    color: #ffffff;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.title-sub {
    font-size: 10px;
    color: #ef4444; /* Crimson Accent Subtitle */
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 750;
    margin-top: 1px;
}

.sidebar-scroll {
    flex: 1;
    overflow-y: auto;
    padding: 16px 12px;
    transition: padding 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Custom Scrollbar for Sidebar */
.sidebar-scroll::-webkit-scrollbar {
    width: 4px;
}
.sidebar-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.sidebar-scroll::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.08);
    border-radius: 4px;
}
.sidebar-scroll::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.2);
}

.menu-modulo {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

/* Menu items & button headers styling */
.menu-item,
.folder-header {
    display: flex;
    align-items: center;
    width: 100%;
    padding: 12px 14px;
    color: #94a3b8; /* Slate 400 */
    text-decoration: none;
    font-weight: 600;
    font-size: 14.5px;
    border-radius: 8px;
    border: none;
    background: transparent;
    cursor: pointer;
    text-align: left;
    transition: padding 0.3s cubic-bezier(0.4, 0, 0.2, 1), justify-content 0.3s cubic-bezier(0.4, 0, 0.2, 1), background 0.2s;
    overflow: hidden;
    white-space: nowrap;
}

.menu-item:hover,
.folder-header:hover {
    background: rgba(255,255,255,0.04);
    color: #ffffff;
}

.menu-item.activo,
.folder-activo-header {
    background: rgba(255,255,255,0.03) !important;
    color: #ffffff !important;
}

.menu-item.activo {
    border-left: 3px solid #ef4444; /* Red border on active dashboard item */
    padding-left: 11px;
}

.menu-icon {
    width: 18px;
    height: 18px;
    margin-right: 12px;
    stroke-width: 2.2;
    flex-shrink: 0;
    color: #64748b;
    transition: color 0.2s, margin-right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.menu-item:hover .menu-icon,
.folder-header:hover .menu-icon,
.folder-activo-header .menu-icon,
.menu-item.activo .menu-icon {
    color: #ef4444; /* Accent color on active or hover icons */
}

/* Folder collapse/expand styles */
.folder-container {
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.folder-header {
    justify-content: space-between;
    outline: none;
}

.header-left {
    display: flex;
    align-items: center;
    overflow: hidden;
}

.chevron-icon {
    width: 14px;
    height: 14px;
    stroke-width: 2.5;
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s;
    color: #475569;
    flex-shrink: 0;
}

.folder-container.abierto .chevron-icon {
    transform: rotate(90deg);
    color: #94a3b8;
}

.folder-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1), margin 0.3s, padding 0.3s;
    display: flex;
    flex-direction: column;
    padding-left: 14px;
    margin-left: 22px;
    border-left: 1px solid rgba(255,255,255,0.06);
    gap: 3px;
}

.folder-container.abierto .folder-content {
    max-height: 600px; /* High enough to allow dropdown height expansion */
    margin-top: 4px;
    margin-bottom: 8px;
}

.sub-item {
    display: flex;
    align-items: center;
    padding: 9px 12px;
    color: #64748b; /* Slate 500 */
    text-decoration: none;
    font-size: 13.5px;
    font-weight: 550;
    border-radius: 6px;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.sub-item:hover {
    color: #f1f5f9;
    background: rgba(255,255,255,0.03);
}

.sub-item.activo {
    color: #ffffff !important;
    background: #dc2626 !important; /* Premium active red background */
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    font-weight: 700;
}

.sub-item.deshabilitado {
    color: #334155 !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
    background: transparent !important;
    opacity: 0.4;
}

/* Logout Section styling */
.logout {
    padding: 20px 14px;
    border-top: 1px solid rgba(255,255,255,0.06);
    background: rgba(0, 0, 0, 0.08);
    transition: padding 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.logout form {
    margin: 0;
}

.logout button {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 11px;
    background: rgba(220, 38, 38, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.2);
    border-radius: 8px;
    font-weight: bold;
    font-size: 14px;
    cursor: pointer;
    transition: padding 0.3s cubic-bezier(0.4, 0, 0.2, 1), justify-content 0.3s cubic-bezier(0.4, 0, 0.2, 1), background 0.2s;
    overflow: hidden;
    white-space: nowrap;
}

.logout button:hover {
    background: #dc2626;
    color: #ffffff;
    border-color: #dc2626;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
}

.logout-icon {
    width: 16px;
    height: 16px;
    margin-right: 8px;
    stroke-width: 2.2;
    flex-shrink: 0;
    transition: margin-right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Sidebar Toggle Floating Button styling */
.sidebar-toggle-btn {
    position: fixed;
    top: 20px;
    left: 265px; /* Aligned centered on the 280px border (280px - 15px) */
    width: 30px;
    height: 30px;
    background: #ffffff;
    color: #0f172a;
    border: 1px solid #e2e8f0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(0,0,0,0.08);
    transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1), background 0.2s, transform 0.2s;
    z-index: 1000;
}

.sidebar-toggle-btn svg {
    width: 14px;
    height: 14px;
}

.sidebar-toggle-btn:hover {
    background: #f8fafc;
    color: #dc2626;
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
}

/* Icon toggle rules */
.sidebar-toggle-btn .icon-open { display: none; }
.sidebar-toggle-btn .icon-close { display: block; }

/* ==========================================================================
   DESKTOP COLLAPSED STATE (body.sidebar-collapsed)
   ========================================================================== */
@media (min-width: 769px) {
    body.sidebar-collapsed .sidebar-unified {
        width: 70px;
    }

    body.sidebar-collapsed main {
        margin-left: 70px !important;
    }

    body.sidebar-collapsed .sidebar-toggle-btn {
        left: 55px; /* Aligned centered on the 70px border (70px - 15px) */
    }

    body.sidebar-collapsed .sidebar-toggle-btn .icon-open { display: block; }
    body.sidebar-collapsed .sidebar-toggle-btn .icon-close { display: none; }

    /* Header adaptations */
    body.sidebar-collapsed .sidebar-header {
        justify-content: center;
        padding: 24px 10px;
    }

    body.sidebar-collapsed .header-text {
        opacity: 0;
        visibility: hidden;
        display: none !important;
    }

    /* Scroll area */
    body.sidebar-collapsed .sidebar-scroll {
        padding: 16px 8px;
    }

    /* Menu item centering and text removal */
    body.sidebar-collapsed .menu-item,
    body.sidebar-collapsed .folder-header {
        justify-content: center;
        padding: 12px 0;
    }

    body.sidebar-collapsed .menu-icon {
        margin-right: 0;
    }

    body.sidebar-collapsed .menu-item span,
    body.sidebar-collapsed .folder-header .header-left > span,
    body.sidebar-collapsed .chevron-icon {
        opacity: 0;
        visibility: hidden;
        display: none !important;
    }

    /* Hide submenus completely */
    body.sidebar-collapsed .folder-content {
        max-height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
    }

    /* Highlight header left border when folder contains active element */
    body.sidebar-collapsed .menu-item.activo,
    body.sidebar-collapsed .folder-activo-header {
        border-left: 3px solid #ef4444;
        border-right: 3px solid transparent;
        background: rgba(220, 38, 38, 0.05) !important;
    }

    /* Centering Logout Button */
    body.sidebar-collapsed .logout {
        padding: 20px 10px;
    }

    body.sidebar-collapsed .logout button {
        padding: 11px 0;
        justify-content: center;
    }

    body.sidebar-collapsed .logout-icon {
        margin-right: 0;
    }

    body.sidebar-collapsed .logout button span {
        opacity: 0;
        visibility: hidden;
        display: none !important;
    }
}

/* Smooth transitions for layout elements */
.sidebar-unified,
main,
.sidebar-toggle-btn {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

/* ==========================================================================
   MOBILE RESPONSIVE STATE
   ========================================================================== */
@media (max-width: 768px) {
    .sidebar-unified {
        transform: translateX(-280px);
    }
    
    main {
        margin-left: 0 !important;
        padding-top: 80px !important; /* Make room for floating toggle on small layouts */
    }
    
    .sidebar-toggle-btn {
        left: 20px;
    }
    
    .sidebar-toggle-btn .icon-open { display: block; }
    .sidebar-toggle-btn .icon-close { display: none; }
    
    /* When slide-in is open on mobile */
    body.sidebar-mobile-open .sidebar-unified {
        transform: translateX(0);
        width: 280px;
    }
    
    body.sidebar-mobile-open .sidebar-toggle-btn {
        left: 265px; /* Centered on the 280px drawer border (280px - 15px) */
    }
    
    body.sidebar-mobile-open .sidebar-toggle-btn .icon-open { display: none; }
    body.sidebar-mobile-open .sidebar-toggle-btn .icon-close { display: block; }
    
    /* Semi-transparent blur overlay behind active mobile drawer */
    body.sidebar-mobile-open::before {
        content: '';
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(4px);
        z-index: 998;
        animation: fadeIn 0.25s ease forwards;
    }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>

<script>
// Toggle collapse and expand behavior for sidebar folders
function toggleFolder(button) {
    if (window.isSidebarCollapsed) {
        // First, expand the sidebar
        toggleSidebar();
        
        // Then, expand the clicked folder
        const container = button.closest('.folder-container');
        container.classList.add('abierto');
        return;
    }

    const container = button.closest('.folder-container');
    container.classList.toggle('abierto');
}

// Toggle sidebar open/closed
function toggleSidebar() {
    if (window.innerWidth > 768) {
        // Desktop collapse toggle
        const isCollapsed = document.body.classList.toggle('sidebar-collapsed');
        window.isSidebarCollapsed = isCollapsed;
        localStorage.setItem('sidebar-collapsed', isCollapsed ? 'true' : 'false');
    } else {
        // Mobile menu drawer toggle
        document.body.classList.toggle('sidebar-mobile-open');
    }
}
</script>
