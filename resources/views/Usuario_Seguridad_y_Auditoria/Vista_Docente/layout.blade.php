<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Docente - CUP FICCT')</title>
    <meta name="description" content="Portal exclusivo para docentes del sistema de admisión preuniversitario CUP FICCT.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f4f8;
            color: #1e293b;
            display: flex;
            min-height: 100vh;
        }

        /* ── SIDEBAR ────────────────────────────── */
        .docente-sidebar {
            width: 260px;
            background: #0f172a;
            color: #f1f5f9;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            left: 0; top: 0;
            z-index: 100;
            box-shadow: 4px 0 20px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 22px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }

        .sidebar-brand img {
            width: 40px; height: 40px;
            object-fit: contain;
        }

        .brand-text { display: flex; flex-direction: column; }
        .brand-title { font-size: 17px; font-weight: 800; color: #fff; }
        .brand-role {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #22d3ee;
            margin-top: 2px;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 16px 10px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .nav-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #475569;
            padding: 12px 12px 6px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .nav-item:hover {
            background: rgba(255,255,255,0.06);
            color: #fff;
        }

        .nav-item.active {
            background: rgba(34,211,238,0.12);
            color: #22d3ee;
            border-left: 3px solid #22d3ee;
            padding-left: 11px;
        }

        .nav-item svg {
            width: 18px; height: 18px;
            flex-shrink: 0;
            opacity: 0.7;
        }

        .nav-item.active svg,
        .nav-item:hover svg {
            opacity: 1;
        }

        .sidebar-footer {
            padding: 16px 10px;
            border-top: 1px solid rgba(255,255,255,0.07);
        }

        .btn-logout {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 11px;
            background: rgba(239,68,68,0.1);
            color: #ef4444;
            border: 1px solid rgba(239,68,68,0.2);
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            background: #ef4444;
            color: #fff;
        }

        /* ── MAIN CONTENT ───────────────────────── */
        .docente-main {
            margin-left: 260px;
            flex: 1;
            padding: 32px;
            min-height: 100vh;
        }

        /* ── TOP BAR ────────────────────────────── */
        .top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .user-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fff;
            padding: 8px 16px;
            border-radius: 50px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }

        .user-chip-avatar {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, #0f172a, #1e3a8a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #22d3ee;
            font-size: 14px;
            font-weight: 800;
        }

        .role-badge {
            background: rgba(34,211,238,0.1);
            color: #0891b2;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        /* ── MOBILE TOGGLE ──────────────────────── */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 16px; left: 16px;
            z-index: 200;
            background: #0f172a;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .docente-sidebar { transform: translateX(-260px); }
            .docente-sidebar.open { transform: translateX(0); }
            .docente-main { margin-left: 0; padding: 20px 16px; padding-top: 70px; }
            .mobile-toggle { display: flex; }
        }

        /* ── ALERT STYLES ───────────────────────── */
        .alert-success {
            background: #d1fae5; color: #065f46;
            padding: 12px 16px; border-radius: 8px;
            margin-bottom: 20px; font-weight: 600;
            border-left: 4px solid #10b981;
        }

        .alert-error {
            background: #fee2e2; color: #991b1b;
            padding: 12px 16px; border-radius: 8px;
            margin-bottom: 20px; font-weight: 600;
            border-left: 4px solid #ef4444;
        }
    </style>
</head>
<body>

{{-- Mobile Toggle --}}
<button class="mobile-toggle" id="mobileSidebarToggle" onclick="toggleSidebarDocente()" aria-label="Abrir menú">
    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
</button>

{{-- Sidebar --}}
<aside class="docente-sidebar" id="docenteSidebar">
    <div class="sidebar-brand">
        <img src="{{ asset('images/LogoFICCT (1).png') }}" alt="Logo CUP FICCT">
        <div class="brand-text">
            <span class="brand-title">CUP FICCT</span>
            <span class="brand-role">Portal Docente</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <span class="nav-label">Mi portal</span>

        <a href="{{ route('docente.perfil') }}"
           class="nav-item {{ request()->routeIs('docente.perfil') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Perfil
        </a>

        <a href="{{ route('docente.materias') }}"
           class="nav-item {{ request()->routeIs('docente.materias') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            Mis materias
        </a>

        <a href="{{ route('docente.grupos') }}"
           class="nav-item {{ request()->routeIs('docente.grupos') || request()->routeIs('docente.grupos.estudiantes') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Mis grupos
        </a>

        <a href="{{ route('docente.notas') }}"
           class="nav-item {{ request()->routeIs('docente.notas') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            Notas
        </a>
    </nav>

    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Cerrar sesión
            </button>
        </form>
    </div>
</aside>

{{-- Main Content --}}
<main class="docente-main">
    {{-- Top bar --}}
    <div class="top-bar">
        <div></div>
        <div class="user-chip">
            <div class="user-chip-avatar">
                {{ strtoupper(substr(Auth::user()->persona?->nombre ?? 'D', 0, 1)) }}
            </div>
            {{ Auth::user()->persona?->nombre }} {{ Auth::user()->persona?->apellido }}
            <span class="role-badge">Docente</span>
        </div>
    </div>

    @yield('content')
</main>

<script>
function toggleSidebarDocente() {
    document.getElementById('docenteSidebar').classList.toggle('open');
}
</script>

</body>
</html>
