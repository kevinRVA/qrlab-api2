@extends('layouts.app')

{{-- ===== META / HEAD ===== --}}
@section('title', 'QR-LAB | Mi Perfil Académico')
@section('meta_description', 'Consulta tus materias inscritas y tu historial de asistencias en QR-LAB.')
@section('nav_icon', 'fa-qrcode')
@section('nav_subtitle', 'Portal Académico')
@section('user_icon', 'fa-circle-user')

{{-- Estilos específicos del estudiante --}}
@push('styles')
<style>
    /* ── Hero ── */
    .student-hero {
        background: linear-gradient(135deg, var(--qr-primary) 0%, #9b2d42 100%);
        color: #fff;
        border-radius: 16px;
        padding: 2rem 2.5rem;
        box-shadow: 0 8px 32px rgba(107, 26, 42, 0.25);
        position: relative;
        overflow: hidden;
    }
    .student-hero::before {
        content: ''; position: absolute; top: -40px; right: -40px;
        width: 200px; height: 200px; border-radius: 50%;
        background: rgba(255,255,255,0.05);
    }
    .student-hero::after {
        content: ''; position: absolute; bottom: -60px; right: 80px;
        width: 150px; height: 150px; border-radius: 50%;
        background: rgba(255,255,255,0.04);
    }
    .student-avatar {
        width: 64px; height: 64px;
        background: rgba(255,255,255,0.2); border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.8rem; border: 2px solid rgba(255,255,255,0.3); flex-shrink: 0;
    }
    .student-name  { font-size: 1.5rem; font-weight: 700; letter-spacing: 0.5px; margin: 0; }
    .student-meta  { font-size: 0.85rem; color: rgba(255,255,255,0.75); margin-top: 0.25rem; }
    .student-badge {
        background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);
        color: #fff; padding: 0.3rem 0.8rem; border-radius: 20px;
        font-size: 0.78rem; font-weight: 500; backdrop-filter: blur(4px);
    }

    /* ── Botón abrir cámara en el hero ── */
    .btn-camara-qr {
        background: rgba(255,255,255,0.18);
        border: 1.5px solid rgba(255,255,255,0.5);
        color: #fff;
        border-radius: 30px;
        padding: 0.45rem 1.2rem;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.15s ease;
        backdrop-filter: blur(6px);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .btn-camara-qr:hover { background: rgba(255,255,255,0.28); transform: scale(1.03); color: #fff; }

    /* ── Stats mini ── */
    .stat-mini {
        background: #fff; border-radius: 12px; padding: 1.1rem 1.4rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        display: flex; align-items: center; gap: 1rem;
        border-left: 4px solid var(--qr-primary);
        transition: transform 0.15s ease;
    }
    .stat-mini:hover { transform: translateY(-2px); }
    .stat-mini-icon {
        width: 44px; height: 44px; background: var(--qr-primary-light);
        border-radius: 10px; display: flex; align-items: center; justify-content: center;
        color: var(--qr-primary); font-size: 1.1rem; flex-shrink: 0;
    }
    .stat-mini-value { font-size: 1.5rem; font-weight: 700; color: #1a202c; line-height: 1; }
    .stat-mini-label { font-size: 0.75rem; color: #64748b; margin-top: 2px; }

    /* ── Section card ── */
    .section-card {
        background: #fff; border-radius: 14px; border: none;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06); overflow: hidden;
        transition: box-shadow 0.2s ease;
    }
    .section-card:hover { box-shadow: 0 4px 20px rgba(107,26,42,0.12); }
    .section-header {
        background: #fff; border-bottom: 2px solid var(--qr-primary-light);
        padding: 1rem 1.5rem; display: flex; align-items: center; gap: 0.6rem;
    }
    .section-header-icon {
        width: 36px; height: 36px; background: var(--qr-primary-light);
        border-radius: 8px; display: flex; align-items: center; justify-content: center;
        color: var(--qr-primary); font-size: 0.9rem;
    }
    .section-title { font-size: 0.95rem; font-weight: 600; color: #2d3748; margin: 0; }

    /* ── Tablas ── */
    .table-materias thead th,
    .table-historial thead th {
        background: #fafafa; color: #64748b; font-size: 0.75rem; font-weight: 600;
        text-transform: uppercase; letter-spacing: 0.5px;
        border-bottom: 1px solid #e2e8f0; padding: 0.75rem 1.25rem;
    }
    .table-materias tbody td,
    .table-historial tbody td {
        padding: 0.85rem 1.25rem; border-bottom: 1px solid #f1f5f9;
        vertical-align: middle; font-size: 0.875rem;
    }
    .table-materias tbody tr:last-child td,
    .table-historial tbody tr:last-child td { border-bottom: none; }
    .table-materias tbody tr:hover td,
    .table-historial tbody tr:hover td { background-color: #fdf8f9; }

    .subject-code {
        font-weight: 700; font-size: 0.8rem; color: var(--qr-primary);
        background: var(--qr-primary-light); padding: 0.2rem 0.55rem;
        border-radius: 6px; white-space: nowrap;
    }
    .subject-name    { font-weight: 500; color: #1a202c; }
    .schedule-badge  {
        background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;
        padding: 0.2rem 0.6rem; border-radius: 6px;
        font-size: 0.75rem; font-weight: 500; white-space: nowrap;
    }

    /* ── Badge asistencia ── */
    .attendance-dot {
        width: 8px; height: 8px; background: #22c55e;
        border-radius: 50%; display: inline-block; margin-right: 4px;
        animation: pulse-dot 2s infinite;
    }
    @keyframes pulse-dot { 0%, 100% { opacity:1; } 50% { opacity:0.5; } }
    .badge-presente {
        background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;
        padding: 0.25rem 0.7rem; border-radius: 20px;
        font-size: 0.75rem; font-weight: 600; white-space: nowrap;
    }

    /* ── Empty state ── */
    .empty-state { text-align: center; padding: 3rem 1rem; color: #94a3b8; }
    .empty-state i { font-size: 2.5rem; margin-bottom: 0.75rem; display: block; opacity: 0.4; }
    .empty-state p { font-size: 0.875rem; margin: 0; }

    /* ── Modal de cámara QR ── */
    #modal-camara .modal-content {
        border-radius: 18px;
        overflow: hidden;
        border: none;
        box-shadow: 0 20px 60px rgba(107,26,42,0.25);
    }
    #modal-camara .modal-header {
        background: linear-gradient(135deg, var(--qr-primary), #9b2d42);
        color: #fff;
        border-bottom: none;
        padding: 1.25rem 1.5rem;
    }
    #modal-camara .modal-header .btn-close { filter: invert(1); }
    #modal-camara .modal-body { padding: 0; }

    /* Contenedor del reader */
    #qr-reader {
        width: 100%;
        max-width: 100%;
    }
    #qr-reader video { width: 100% !important; }

    .camara-status {
        text-align: center;
        padding: 0.75rem 1rem;
        font-size: 0.85rem;
        border-top: 1px solid #f1f5f9;
    }
    .camara-status.detectando { color: #1e40af; }
    .camara-status.exito      { color: #166534; background: #f0fdf4; }
    .camara-status.error-cam  { color: #991b1b; background: #fef2f2; }

    /* Pulso de escaneo */
    .scan-overlay {
        position: absolute;
        inset: 0;
        pointer-events: none;
        border: 3px solid rgba(107,26,42,0.4);
        border-radius: 6px;
        animation: scan-pulse 2s infinite;
    }
    @keyframes scan-pulse {
        0%, 100% { border-color: rgba(107,26,42,0.3); }
        50%       { border-color: rgba(107,26,42,0.8); }
    }
</style>
@endpush

{{-- ===== CONTENIDO PRINCIPAL ===== --}}
@section('content')
<div class="container-fluid px-4 py-4 pb-5" style="max-width: 1100px;">

    {{-- Hero: nombre del estudiante --}}
    <div class="student-hero mb-4 fade-in">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="student-avatar"><i class="fa-solid fa-user-graduate"></i></div>
            <div class="flex-grow-1">
                <p class="student-name">{{ strtoupper($user->name) }}</p>
                <p class="student-meta">
                    @if($user->user_code)
                        <i class="fa-solid fa-id-card me-1"></i> {{ $user->user_code }}
                        @if($user->career) &nbsp;·&nbsp; @endif
                    @endif
                    @if($user->career)
                        <i class="fa-solid fa-graduation-cap me-1"></i> {{ $user->career }}
                    @endif
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap align-items-center">
                @if($user->is_instructor)
                <a href="{{ route('instructor.index') }}" class="btn-camara-qr" style="background: rgba(255,255,255,0.3); border-color: rgba(255,255,255,0.7); color: white; text-decoration: none;">
                    <i class="fa-solid fa-chalkboard-user"></i> Panel de Instructor
                </a>
                @endif
                {{-- Botón abrir cámara QR --}}
                <button class="btn-camara-qr" data-bs-toggle="modal" data-bs-target="#modal-camara" id="btn-abrir-camara">
                    <i class="fa-solid fa-camera"></i> Marcar Asistencia
                </button>
                <span class="student-badge">
                    <i class="fa-solid fa-circle-check me-1" style="color:#86efac;"></i> Estudiante Activo
                </span>
            </div>
        </div>
    </div>

    {{-- Mini estadísticas --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3 fade-in fade-in-delay-1">
            <div class="stat-mini">
                <div class="stat-mini-icon"><i class="fa-solid fa-book-open"></i></div>
                <div>
                    <div class="stat-mini-value">{{ $inscripciones->count() }}</div>
                    <div class="stat-mini-label">Materias inscritas</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 fade-in fade-in-delay-1">
            <div class="stat-mini">
                <div class="stat-mini-icon"><i class="fa-solid fa-calendar-check"></i></div>
                <div>
                    <div class="stat-mini-value">{{ $historial->count() }}</div>
                    <div class="stat-mini-label">Asistencias recientes</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla: Materias inscritas --}}
    <div class="section-card mb-4 fade-in fade-in-delay-2">
        <div class="section-header">
            <div class="section-header-icon"><i class="fa-solid fa-list-check"></i></div>
            <h6 class="section-title">Materias Inscritas</h6>
            <span class="badge ms-auto" style="background:var(--qr-primary-light); color:var(--qr-primary); font-weight:600;">
                {{ $inscripciones->count() }} materia(s)
            </span>
        </div>

        @if($inscripciones->isEmpty())
            <div class="empty-state">
                <i class="fa-solid fa-book-open"></i>
                <p>No tienes materias inscritas en este período.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-materias mb-0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Materia</th>
                            <th class="text-center">Sección</th>
                            <th>Horario</th>
                            <th>Docente</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inscripciones as $inscripcion)
                            @php $seccion = $inscripcion->section; @endphp
                            <tr>
                                <td><span class="subject-code">{{ $seccion->subject->code ?? 'N/A' }}</span></td>
                                <td><span class="subject-name">{{ strtoupper($seccion->subject->name ?? 'Sin nombre') }}</span></td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border" style="font-size:0.8rem;">
                                        {{ $seccion->section_code ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    @if($seccion->schedule)
                                        <span class="schedule-badge">
                                            <i class="fa-regular fa-clock me-1"></i>{{ $seccion->schedule }}
                                        </span>
                                    @else
                                        <span class="text-muted">Sin horario</span>
                                    @endif
                                </td>
                                <td class="text-muted" style="font-size:0.82rem;">
                                    <i class="fa-solid fa-chalkboard-user me-1"></i>
                                    {{ $seccion->teacher->name ?? 'N/A' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Tabla: Historial de asistencias --}}
    <div class="section-card fade-in fade-in-delay-3">
        <div class="section-header">
            <div class="section-header-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
            <h6 class="section-title">Historial de Asistencias</h6>
            <span class="badge ms-auto" style="background:var(--qr-primary-light); color:var(--qr-primary); font-weight:600;">
                Últimas {{ $historial->count() }}
            </span>
        </div>

        @if($historial->isEmpty())
            <div class="empty-state">
                <i class="fa-solid fa-calendar-xmark"></i>
                <p>Aún no tienes asistencias registradas.<br>
                    <span style="font-size:0.8rem;">Escanea el código QR de tu clase para registrarte.</span>
                </p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-historial mb-0">
                    <thead>
                        <tr>
                            <th>Fecha y Hora</th>
                            <th>Materia</th>
                            <th>Sección</th>
                            <th>Laboratorio</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($historial as $asistencia)
                            @php
                                $sesion  = $asistencia->session;
                                $seccion = $sesion?->section;
                                $materia = $seccion?->subject;
                            @endphp
                            <tr>
                                <td>
                                    <div style="font-weight:500; color:#1a202c; font-size:0.85rem;">
                                        {{ \Carbon\Carbon::parse($asistencia->created_at)->format('d/m/Y') }}
                                    </div>
                                    <div style="font-size:0.75rem; color:#94a3b8;">
                                        <i class="fa-regular fa-clock me-1"></i>
                                        {{ \Carbon\Carbon::parse($asistencia->created_at)->format('H:i') }}
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight:500; color:#1a202c; font-size:0.85rem;">
                                        {{ strtoupper($materia->name ?? 'Materia desconocida') }}
                                    </div>
                                    <div style="font-size:0.75rem; color:#94a3b8;">{{ $materia->code ?? '' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border" style="font-size:0.78rem;">
                                        {{ $seccion->section_code ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    @if($sesion?->laboratory_name)
                                        <i class="fa-solid fa-computer me-1 text-muted"></i>
                                        <span style="font-size:0.83rem;">{{ $sesion->laboratory_name }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge-presente">
                                        <span class="attendance-dot"></span>Presente
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Aviso: salidas sin marcar (HTML) --}}
    @if($avisosSinSalida > 0)
    <div class="mt-4 fade-in" style="
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        border-left: 5px solid #f59e0b; border-radius: 12px;
        padding: 1rem 1.25rem; display: flex; align-items: center; gap: 0.75rem;">
        <i class="fa-solid fa-triangle-exclamation" style="color:#b45309; font-size:1.5rem; flex-shrink:0;"></i>
        <div>
            <strong style="color:#92400e;">Aviso:</strong>
            <span style="color:#78350f; font-size:0.875rem;">
                Tienes <strong>{{ $avisosSinSalida }}</strong> visita(s) a laboratorio donde no marcaste tu salida.
                El sistema las cerró automáticamente a las 00:00. Recuerda escanear el QR al salir.
            </span>
        </div>
    </div>
    @endif

    {{-- Tabla: Visitas voluntarias a laboratorios --}}
    <div class="section-card mt-4 fade-in" style="animation-delay:0.4s;">
        <div class="section-header">
            <div class="section-header-icon"><i class="fa-solid fa-door-open"></i></div>
            <h6 class="section-title">Acceso a Laboratorios</h6>
            <span class="badge ms-auto" style="background:var(--qr-primary-light); color:var(--qr-primary); font-weight:600;">
                Últimas {{ $visitasLab->count() }}
            </span>
        </div>

        @if($visitasLab->isEmpty())
            <div class="empty-state">
                <i class="fa-solid fa-door-closed"></i>
                <p>Aún no tienes visitas registradas a laboratorios.<br>
                    <span style="font-size:0.8rem;">Escanea el QR del laboratorio o usa el botón de cámara para registrar tu entrada.</span>
                </p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-historial mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Laboratorio</th>
                            <th class="text-center">Entrada</th>
                            <th class="text-center">Salida</th>
                            <th class="text-center">Duración</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($visitasLab as $visita)
                            @php
                                $duracion = null;
                                if ($visita->entry_time && $visita->exit_time) {
                                    $diff     = $visita->entry_time->diff($visita->exit_time);
                                    $duracion = $diff->h . 'h ' . $diff->i . 'min';
                                }
                            @endphp
                            <tr>
                                <td style="font-size:0.83rem; color:#64748b;">
                                    {{ \Carbon\Carbon::parse($visita->entry_time)->format('d/m/Y') }}
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border" style="font-size:0.78rem;">
                                        <i class="fa-solid fa-computer me-1"></i>
                                        {{ $visita->laboratory->name ?? 'Lab desconocido' }}
                                    </span>
                                </td>
                                <td class="text-center fw-bold" style="color:#1a202c;">
                                    {{ \Carbon\Carbon::parse($visita->entry_time)->format('H:i') }}
                                </td>
                                <td class="text-center">
                                    @if($visita->exit_time)
                                        {{ \Carbon\Carbon::parse($visita->exit_time)->format('H:i') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($duracion)
                                        <span style="background:#ede9fe; color:#7c3aed; padding:0.2rem 0.55rem; border-radius:20px; font-size:0.75rem; font-weight:600;">
                                            {{ $duracion }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($visita->no_exit_warning)
                                        <span style="background:#fef3c7; color:#92400e; border:1px solid #fcd34d; padding:0.25rem 0.6rem; border-radius:20px; font-size:0.73rem; font-weight:600; white-space:nowrap;">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i>Sin salida
                                        </span>
                                    @elseif($visita->exit_time)
                                        <span style="background:#dcfce7; color:#166534; border:1px solid #86efac; padding:0.25rem 0.6rem; border-radius:20px; font-size:0.73rem; font-weight:600; white-space:nowrap;">
                                            <i class="fa-solid fa-circle-check me-1"></i>Completa
                                        </span>
                                    @else
                                        <span style="background:#dbeafe; color:#1e40af; border:1px solid #93c5fd; padding:0.25rem 0.6rem; border-radius:20px; font-size:0.73rem; font-weight:600; white-space:nowrap;">
                                            <i class="fa-solid fa-spinner fa-spin me-1"></i>En lab
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════════
     MODAL DE CÁMARA QR
══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-camara" tabindex="-1" aria-labelledby="modal-camara-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content">

            {{-- Header --}}
            <div class="modal-header">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:36px; height:36px; background:rgba(255,255,255,0.2); border-radius:10px;
                                display:flex; align-items:center; justify-content:center; font-size:1rem;">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="modal-camara-label">Escáner de Asistencia</h5>
                        <p class="mb-0" style="font-size:0.75rem; opacity:0.75;">Apunta la cámara al código QR</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            {{-- Cuerpo: visor de cámara --}}
            <div class="modal-body p-0" style="position:relative; background:#000; min-height:300px;">
                <div id="qr-reader" style="width:100%;"></div>

                {{-- Instrucciones superpuestas --}}
                <div id="camara-instrucciones" style="
                    position:absolute; inset:0;
                    display:flex; flex-direction:column;
                    align-items:center; justify-content:center;
                    background:rgba(0,0,0,0.65); color:#fff; text-align:center; padding:2rem;
                ">
                    <i class="fa-solid fa-camera" style="font-size:3rem; margin-bottom:1rem; opacity:0.7;"></i>
                    <p style="font-size:0.9rem; margin:0;">Iniciando cámara...</p>
                    <p style="font-size:0.75rem; opacity:0.6; margin-top:0.5rem;">Asegúrate de permitir el acceso a la cámara.</p>
                </div>
            </div>

            {{-- Estado de detección --}}
            <div class="camara-status detectando" id="camara-status-text">
                <i class="fa-solid fa-spinner fa-spin me-1"></i> Esperando cámara...
            </div>

            {{-- Footer con ayuda --}}
            <div class="modal-footer" style="background:#fafafa; border-top:1px solid #f1f5f9; padding:0.75rem 1.25rem;">
                <div style="font-size:0.78rem; color:#64748b;">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Apunta al QR de tu clase o al QR del laboratorio para registrar tu asistencia.
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-1"></i> Cerrar
                </button>
            </div>

        </div>
    </div>
</div>

@endsection

{{-- ═══════════════════════════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════════════════════════════ --}}
@push('head_scripts')
{{-- Librería html5-qrcode --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@endpush

@push('scripts')
<script>
// ── Escáner QR ─────────────────────────────────────────────────────────────
let html5QrCode = null;
let escaneando  = false;

const modalEl   = document.getElementById('modal-camara');
const statusEl  = document.getElementById('camara-status-text');
const instrEl   = document.getElementById('camara-instrucciones');

/**
 * Inicia el escáner cuando se abre el modal.
 */
modalEl.addEventListener('show.bs.modal', () => {
    iniciarEscaner();
});

/**
 * Detiene el escáner cuando se cierra el modal.
 */
modalEl.addEventListener('hide.bs.modal', () => {
    detenerEscaner();
});

async function iniciarEscaner() {
    if (escaneando) return;

    setStatus('Iniciando cámara...', 'detectando');

    try {
        html5QrCode = new Html5Qrcode('qr-reader', { verbose: false });

        await html5QrCode.start(
            { facingMode: 'environment' },          // Cámara trasera preferida
            {
                fps: 10,
                qrbox: { width: 260, height: 260 },
                aspectRatio: 1.0,
            },
            onQrDetectado,
            onQrError
        );

        escaneando = true;
        instrEl.style.display = 'none';             // Ocultar instrucciones iniciales
        setStatus('🔍 Buscando código QR...', 'detectando');

    } catch (err) {
        console.error('Error al iniciar cámara:', err);
        instrEl.innerHTML = `
            <i class="fa-solid fa-camera-slash" style="font-size:2.5rem; margin-bottom:1rem; color:#ef4444;"></i>
            <p style="color:#fca5a5; font-size:0.9rem; margin:0;">No se pudo acceder a la cámara.</p>
            <p style="color:#fca5a5; font-size:0.75rem; margin-top:0.5rem; opacity:0.8;">
                Verifica que tu navegador tiene permiso para usar la cámara.
            </p>`;
        instrEl.style.display = 'flex';
        setStatus('Error al acceder a la cámara.', 'error-cam');
    }
}

async function detenerEscaner() {
    if (html5QrCode && escaneando) {
        try {
            await html5QrCode.stop();
            html5QrCode.clear();
        } catch (e) {
            // Ignorar errores al parar
        }
        escaneando  = false;
        html5QrCode = null;
    }
}

/**
 * Callback cuando se detecta un QR válido.
 * Detiene la cámara INMEDIATAMENTE para evitar registros duplicados.
 */
function onQrDetectado(decodedText) {
    // Guard: si ya procesamos un QR, ignorar cualquier llamada adicional
    if (!escaneando) return;
    escaneando = false;  // Bloquear futuros callbacks ANTES de cualquier await

    setStatus('✓ QR detectado. Deteniendo cámara...', 'exito');

    // Capturar la instancia y limpiar el puntero global de inmediato
    const instancia = html5QrCode;
    html5QrCode = null;

    (async () => {
        // Detener cámara antes de redirigir
        if (instancia) {
            try {
                await instancia.stop();
                instancia.clear();
            } catch (e) { /* ignorar error al parar */ }
        }

        let url = decodedText.trim();
        if (!url.startsWith('http')) {
            url = window.location.origin + '/lab-qr/' + url;
        }

        setStatus('✓ Redirigiendo a registro de asistencia...', 'exito');
        toastr.success('QR detectado. Registrando asistencia...', '✓ QR Escaneado', { timeOut: 2500 });

        setTimeout(() => { window.location.href = url; }, 600);
    })();
}


/**
 * Callback de error de escaneo (se llama constantemente, solo loguear debug).
 */
function onQrError(errorMsg) {
    // No mostrar errores de "no QR found" para no saturar
}

function setStatus(texto, clase) {
    statusEl.textContent = texto;
    statusEl.className   = 'camara-status ' + clase;

    if (clase === 'detectando' && texto.includes('Buscando')) {
        statusEl.innerHTML = '<i class="fa-solid fa-qrcode me-1"></i> ' + texto;
    }
}
</script>
@endpush

{{-- ═══════════════════════════════════════════════════════════════
     TOASTR — Notificaciones al cargar la página
══════════════════════════════════════════════════════════════════ --}}
@push('toastr')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($avisosSinSalida > 0)
        toastr.warning(
            'Tienes <strong>{{ $avisosSinSalida }}</strong> visita(s) donde no marcaste tu salida. El sistema las cerró automáticamente.',
            '⚠️ Salidas sin registrar',
            {
                timeOut:         8000,
                extendedTimeOut: 3000,
                closeButton:     true,
                progressBar:     true,
            }
        );
    @endif

    @if($cierresAutoCerrados >= 3)
        setTimeout(function() {
            toastr.error(
                'Tu sesión en prácticas libres ha sido cerrada automáticamente <strong>{{ $cierresAutoCerrados }} veces</strong>. Recuerda escanear el QR al salir del laboratorio.',
                '🚫 Atención: Cierres automáticos',
                {
                    timeOut:         10000,
                    extendedTimeOut: 4000,
                    closeButton:     true,
                    progressBar:     true,
                }
            );
        }, 1200);
    @endif
});
</script>
@endpush
