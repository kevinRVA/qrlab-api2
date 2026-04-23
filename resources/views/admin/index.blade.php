@extends('layouts.app')

@section('title', 'QR-LAB | Panel Administrador')
@section('nav_icon', 'fa-shield-halved')
@section('nav_subtitle', 'Panel de Administración')
@section('user_icon', 'fa-shield-halved')

@push('styles')
<style>
    /* ── Hub cards ── */
    .hub-card {
        border-radius: 20px;
        border: none;
        overflow: hidden;
        cursor: pointer;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        text-decoration: none;
        display: block;
    }
    .hub-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 60px rgba(107, 26, 42, 0.2) !important;
    }
    .hub-card-body {
        padding: 2.8rem 2.5rem;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 1.2rem;
        min-height: 280px;
    }
    .hub-icon-wrap {
        width: 72px;
        height: 72px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        flex-shrink: 0;
    }
    .hub-card-title {
        font-size: 1.45rem;
        font-weight: 700;
        color: #1a202c;
        margin: 0;
        line-height: 1.2;
    }
    .hub-card-desc {
        font-size: 0.9rem;
        color: #64748b;
        margin: 0;
        line-height: 1.5;
    }
    .hub-card-arrow {
        margin-top: auto;
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        transition: gap 0.2s ease;
    }
    .hub-card:hover .hub-card-arrow { gap: 0.75rem; }

    /* Variante azul (Asistencia) */
    .hub-card-blue .hub-icon-wrap  { background: rgba(37, 99, 235, 0.12); color: #2563eb; }
    .hub-card-blue .hub-card-arrow { color: #2563eb; }
    .hub-card-blue:hover            { border: 2px solid rgba(37, 99, 235, 0.25) !important; }

    /* Variante guinda (Prácticas Libres) */
    .hub-card-qrlab .hub-icon-wrap  { background: var(--qr-primary-light); color: var(--qr-primary); }
    .hub-card-qrlab .hub-card-arrow { color: var(--qr-primary); }
    .hub-card-qrlab:hover            { border: 2px solid rgba(107,26,42,0.25) !important; }

    /* ── Stats rápidas ── */
    .quick-stat {
        background: #fff;
        border-radius: 14px;
        padding: 1.2rem 1.5rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        border-left: 4px solid var(--qr-primary);
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .quick-stat-icon {
        width: 46px; height: 46px;
        background: var(--qr-primary-light);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        color: var(--qr-primary);
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .quick-stat-value { font-size: 1.6rem; font-weight: 700; color: #1a202c; line-height: 1; }
    .quick-stat-label { font-size: 0.75rem; color: #64748b; margin-top: 3px; }

    /* ── Welcome banner ── */
    .welcome-banner {
        background: linear-gradient(135deg, var(--qr-primary) 0%, #9b2d42 100%);
        border-radius: 20px;
        padding: 2.5rem 3rem;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .welcome-banner::before {
        content: '';
        position: absolute; top: -50px; right: -50px;
        width: 250px; height: 250px; border-radius: 50%;
        background: rgba(255,255,255,0.06);
    }
    .welcome-banner::after {
        content: '';
        position: absolute; bottom: -70px; right: 120px;
        width: 180px; height: 180px; border-radius: 50%;
        background: rgba(255,255,255,0.04);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 1200px;">

    {{-- ── Welcome banner ── --}}
    <div class="welcome-banner mb-4 fade-in">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h4 class="fw-bold mb-1" style="font-size:1.6rem;">
                    Bienvenido, {{ Auth::user()->name }} 👋
                </h4>
                <p class="mb-0" style="color:rgba(255,255,255,0.75); font-size:0.95rem;">
                    Panel de Administración — QR-LAB · UTEC
                </p>
            </div>
            <span class="badge p-2 px-3" style="background:rgba(255,255,255,0.15); font-size:0.8rem; border:1px solid rgba(255,255,255,0.3); border-radius:20px;">
                <i class="fa-solid fa-circle me-1" style="color:#86efac; font-size:0.5rem; vertical-align:middle;"></i>
                Sistema en línea
            </span>
        </div>
    </div>

    {{-- ── Stats rápidas ── --}}
    <div class="row g-3 mb-5 fade-in fade-in-delay-1">
        <div class="col-6 col-md-3">
            <div class="quick-stat">
                <div class="quick-stat-icon"><i class="fa-solid fa-users"></i></div>
                <div>
                    <div class="quick-stat-value" id="stat-estudiantes">—</div>
                    <div class="quick-stat-label">Estudiantes</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="quick-stat">
                <div class="quick-stat-icon"><i class="fa-solid fa-chalkboard-user"></i></div>
                <div>
                    <div class="quick-stat-value" id="stat-docentes">—</div>
                    <div class="quick-stat-label">Docentes</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="quick-stat">
                <div class="quick-stat-icon"><i class="fa-solid fa-circle-play"></i></div>
                <div>
                    <div class="quick-stat-value" id="stat-sesiones-activas">—</div>
                    <div class="quick-stat-label">Sesiones activas</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="quick-stat">
                <div class="quick-stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
                <div>
                    <div class="quick-stat-value" id="stat-asistencias">—</div>
                    <div class="quick-stat-label">Asistencias totales</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Título ── --}}
    <div class="d-flex align-items-center gap-2 mb-4 fade-in fade-in-delay-2">
        <div style="width:5px; height:28px; background:var(--qr-primary); border-radius:3px;"></div>
        <h5 class="mb-0 fw-bold text-dark">Selecciona un módulo</h5>
    </div>

    {{-- ── Hub cards ── --}}
    <div class="row g-4 fade-in fade-in-delay-2">

        {{-- Card: Asistencia de Clases --}}
        <div class="col-md-4">
            <a href="{{ route('admin.asistencia') }}" class="hub-card shadow-sm hub-card-blue" style="border: 2px solid transparent;">
                <div class="hub-card-body">
                    <div class="hub-icon-wrap">
                        <i class="fa-solid fa-clipboard-user"></i>
                    </div>
                    <div>
                        <p class="hub-card-title">Asistencias programadas a laboratorios</p>
                        <p class="hub-card-desc mt-2">
                            Visualiza el historial detallado de sesiones de clase, filtra por laboratorio,
                            docente o asignatura y descarga reportes en Excel.
                        </p>
                    </div>
                    <div class="hub-card-arrow" style="color:#2563eb;">
                        Ver módulo <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </div>
            </a>
        </div>

        {{-- Card: Prácticas Libres --}}
        <div class="col-md-4">
            <a href="{{ route('admin.practicas-libres') }}" class="hub-card shadow-sm hub-card-qrlab" style="border: 2px solid transparent;">
                <div class="hub-card-body">
                    <div class="hub-icon-wrap">
                        <i class="fa-solid fa-door-open"></i>
                    </div>
                    <div>
                        <p class="hub-card-title">Prácticas Libres</p>
                        <p class="hub-card-desc mt-2">
                            Consulta el registro de entradas y salidas voluntarias a los laboratorios.
                            Filtra por laboratorio y fecha e imprime los QR estáticos de cada lab.
                        </p>
                    </div>
                    <div class="hub-card-arrow">
                        Ver módulo <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </div>
            </a>
        </div>

        {{-- Card: Administración del Sistema (SOLO ADMIN) --}}
        @if(Auth::user()->role === 'admin')
        <div class="col-md-4">
            <a href="{{ route('admin.configuracion') }}" class="hub-card shadow-sm" style="border: 2px solid transparent; background-color: #fff;">
                <div class="hub-card-body">
                    <div class="hub-icon-wrap" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
                        <i class="fa-solid fa-cogs"></i>
                    </div>
                    <div>
                        <p class="hub-card-title">Administración del Sistema</p>
                        <p class="hub-card-desc mt-2">
                            Gestiona a los coordinadores del sistema, crea nuevos laboratorios y asigna los laboratorios a cada coordinador.
                        </p>
                    </div>
                    <div class="hub-card-arrow" style="color:#10b981;">
                        Ver módulo <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="{{ route('admin.instructors') }}" class="hub-card shadow-sm" style="border: 2px solid transparent; background-color: #fff;">
                <div class="hub-card-body">
                    <div class="hub-icon-wrap" style="background: rgba(99, 102, 241, 0.12); color: #6366f1;">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                    <div>
                        <p class="hub-card-title">Gestión de Instructores</p>
                        <p class="hub-card-desc mt-2">
                            Asigna estudiantes como instructores de clases específicas para que puedan generar y asistir en las sesiones.
                        </p>
                    </div>
                    <div class="hub-card-arrow" style="color:#6366f1;">
                        Ver módulo <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </div>
            </a>
        </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
    // Carga las estadísticas del header desde la API de sesiones
    document.addEventListener('DOMContentLoaded', async () => {
        try {
            const resp = await fetch('/api/admin/sesiones');
            const sesiones = await resp.json();

            const activasCount    = sesiones.filter(s => s.is_active).length;
            const asistenciasTotal = sesiones.reduce((n, s) => n + s.attendances_count, 0);

            document.getElementById('stat-sesiones-activas').textContent = activasCount;
            document.getElementById('stat-asistencias').textContent      = asistenciasTotal;
        } catch (e) { console.error(e); }

        // Stats de usuarios desde el servidor (pasadas como variables Blade)
        document.getElementById('stat-estudiantes').textContent = '{{ $stats["students"] }}';
        document.getElementById('stat-docentes').textContent    = '{{ $stats["teachers"] }}';
    });
</script>
@endpush
