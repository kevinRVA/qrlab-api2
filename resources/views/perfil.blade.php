<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR-LAB | Mi Perfil Académico</title>
    <meta name="description" content="Consulta tus materias inscritas y tu historial de asistencias en QR-LAB.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/55416e97e6.js" crossorigin="anonymous"></script>
    <style>
        :root {
            --qr-primary: #6b1a2a;
            --qr-primary-dark: #52131f;
            --qr-primary-light: rgba(107, 26, 42, 0.08);
            --qr-accent: #e8a0b4;
            --qr-bg: #f4f6f9;
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--qr-bg);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ===== NAVBAR ===== */
        .navbar-qrlab {
            background-color: var(--qr-primary) !important;
        }

        /* ===== HERO: Nombre del estudiante ===== */
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
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
        }

        .student-hero::after {
            content: '';
            position: absolute;
            bottom: -60px;
            right: 80px;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.04);
        }

        .student-avatar {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            flex-shrink: 0;
        }

        .student-name {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .student-meta {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.75);
            margin-top: 0.25rem;
        }

        .student-badge {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #fff;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 500;
            backdrop-filter: blur(4px);
        }

        /* ===== SECCIÓN CARD ===== */
        .section-card {
            background: #fff;
            border-radius: 14px;
            border: none;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            transition: box-shadow 0.2s ease;
        }

        .section-card:hover {
            box-shadow: 0 4px 20px rgba(107, 26, 42, 0.12);
        }

        .section-header {
            background: #fff;
            border-bottom: 2px solid var(--qr-primary-light);
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .section-header-icon {
            width: 36px;
            height: 36px;
            background: var(--qr-primary-light);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--qr-primary);
            font-size: 0.9rem;
        }

        .section-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #2d3748;
            margin: 0;
        }

        /* ===== TABLA MATERIAS ===== */
        .table-materias thead th {
            background: #fafafa;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1.25rem;
        }

        .table-materias tbody td {
            padding: 0.85rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            font-size: 0.875rem;
        }

        .table-materias tbody tr:last-child td {
            border-bottom: none;
        }

        .table-materias tbody tr:hover td {
            background-color: #fdf8f9;
        }

        .subject-code {
            font-weight: 700;
            font-size: 0.8rem;
            color: var(--qr-primary);
            background: var(--qr-primary-light);
            padding: 0.2rem 0.55rem;
            border-radius: 6px;
            white-space: nowrap;
        }

        .subject-name {
            font-weight: 500;
            color: #1a202c;
        }

        .schedule-badge {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            white-space: nowrap;
        }

        /* ===== TABLA HISTORIAL ===== */
        .table-historial thead th {
            background: #fafafa;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1.25rem;
        }

        .table-historial tbody td {
            padding: 0.85rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            font-size: 0.875rem;
        }

        .table-historial tbody tr:last-child td {
            border-bottom: none;
        }

        .table-historial tbody tr:hover td {
            background-color: #fdf8f9;
        }

        .attendance-dot {
            width: 8px;
            height: 8px;
            background: #22c55e;
            border-radius: 50%;
            display: inline-block;
            margin-right: 4px;
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .badge-presente {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            padding: 0.25rem 0.7rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
            display: block;
            opacity: 0.4;
        }

        .empty-state p {
            font-size: 0.875rem;
            margin: 0;
        }

        /* ===== STATS MINI ===== */
        .stat-mini {
            background: #fff;
            border-radius: 12px;
            padding: 1.1rem 1.4rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 1rem;
            border-left: 4px solid var(--qr-primary);
            transition: transform 0.15s ease;
        }

        .stat-mini:hover {
            transform: translateY(-2px);
        }

        .stat-mini-icon {
            width: 44px;
            height: 44px;
            background: var(--qr-primary-light);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--qr-primary);
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .stat-mini-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a202c;
            line-height: 1;
        }

        .stat-mini-label {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 2px;
        }

        /* ===== FOOTER ===== */
        .footer-qrlab {
            background-color: var(--qr-primary);
            color: #fff;
            margin-top: auto;
        }

        .footer-qrlab a {
            color: rgba(255, 255, 255, 0.65);
            text-decoration: none;
        }

        .footer-qrlab a:hover {
            color: #fff;
        }

        .footer-copyright {
            background-color: rgba(0, 0, 0, 0.25);
        }

        /* ===== ANIMACIÓN ENTRADA ===== */
        @keyframes fadeSlideUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeSlideUp 0.4s ease both;
        }

        .fade-in-delay-1 { animation-delay: 0.1s; }
        .fade-in-delay-2 { animation-delay: 0.2s; }
        .fade-in-delay-3 { animation-delay: 0.3s; }
    </style>
</head>

<body>

{{-- ===== NAVBAR ===== --}}
<nav class="navbar navbar-dark shadow-sm" style="background-color: #6b1a2a;">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="#">
            <i class="fa-solid fa-qrcode me-1"></i> <strong>QR-LAB</strong>
            <span class="d-none d-md-inline text-white-50" style="font-size:0.85rem; font-weight:400;"> | Portal Académico</span>
        </a>
        <div class="d-flex align-items-center gap-2">
            <span class="text-light small d-none d-md-inline">
                <i class="fa-solid fa-circle-user" style="color: #e8a0b4;"></i>
                {{ $user->name }}
            </span>
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-sm"
                    style="background-color: rgba(255,255,255,0.15); color:#fff; border:1px solid rgba(255,255,255,0.4);">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span class="d-none d-sm-inline"> Cerrar sesión</span>
                </button>
            </form>
        </div>
    </div>
</nav>

{{-- ===== CONTENIDO PRINCIPAL ===== --}}
<div class="container-fluid px-4 py-4 pb-5" style="max-width: 1100px;">

    {{-- HERO: Nombre del estudiante --}}
    <div class="student-hero mb-4 fade-in">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="student-avatar">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
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
            <span class="student-badge">
                <i class="fa-solid fa-circle-check me-1" style="color:#86efac;"></i> Estudiante Activo
            </span>
        </div>
    </div>

    {{-- MINI ESTADÍSTICAS --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3 fade-in fade-in-delay-1">
            <div class="stat-mini">
                <div class="stat-mini-icon">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <div>
                    <div class="stat-mini-value">{{ $inscripciones->count() }}</div>
                    <div class="stat-mini-label">Materias inscritas</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 fade-in fade-in-delay-1">
            <div class="stat-mini">
                <div class="stat-mini-icon">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div>
                    <div class="stat-mini-value">{{ $historial->count() }}</div>
                    <div class="stat-mini-label">Asistencias recientes</div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLA: MATERIAS INSCRITAS --}}
    <div class="section-card mb-4 fade-in fade-in-delay-2">
        <div class="section-header">
            <div class="section-header-icon">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <h6 class="section-title">Materias Inscritas</h6>
            <span class="badge ms-auto"
                style="background:var(--qr-primary-light); color:var(--qr-primary); font-weight:600;">
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
                                <td>
                                    <span class="subject-code">
                                        {{ $seccion->subject->code ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="subject-name">
                                        {{ strtoupper($seccion->subject->name ?? 'Sin nombre') }}
                                    </span>
                                </td>
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

    {{-- TABLA: HISTORIAL DE MARCACIONES --}}
    <div class="section-card fade-in fade-in-delay-3">
        <div class="section-header">
            <div class="section-header-icon">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <h6 class="section-title">Historial de Asistencias</h6>
            <span class="badge ms-auto"
                style="background:var(--qr-primary-light); color:var(--qr-primary); font-weight:600;">
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
                                $sesion = $asistencia->session;
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
                                    <div style="font-size:0.75rem; color:#94a3b8;">
                                        {{ $materia->code ?? '' }}
                                    </div>
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

    {{-- AVISO: Salidas sin marcar --}}
    @if($avisosSinSalida > 0)
    <div class="mt-4 fade-in" style="
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        border-left: 5px solid #f59e0b;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;">
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

    {{-- TABLA: VISITAS VOLUNTARIAS A LABORATORIOS --}}
    <div class="section-card mt-4 fade-in" style="animation-delay:0.4s;">
        <div class="section-header">
            <div class="section-header-icon">
                <i class="fa-solid fa-door-open"></i>
            </div>
            <h6 class="section-title">Acceso a Laboratorios</h6>
            <span class="badge ms-auto"
                style="background:var(--qr-primary-light); color:var(--qr-primary); font-weight:600;">
                Últimas {{ $visitasLab->count() }}
            </span>
        </div>

        @if($visitasLab->isEmpty())
            <div class="empty-state">
                <i class="fa-solid fa-door-closed"></i>
                <p>Aún no tienes visitas registradas a laboratorios.<br>
                    <span style="font-size:0.8rem;">Escanea el QR del laboratorio para registrar tu entrada.</span>
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
                                    $diff = $visita->entry_time->diff($visita->exit_time);
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
                                        <span style="background:#fef3c7; color:#92400e; border:1px solid #fcd34d; padding:0.25rem 0.6rem; border-radius:20px; font-size:0.73rem; font-weight:600;">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i>Sin salida
                                        </span>
                                    @elseif($visita->exit_time)
                                        <span style="background:#dcfce7; color:#166534; border:1px solid #86efac; padding:0.25rem 0.6rem; border-radius:20px; font-size:0.73rem; font-weight:600;">
                                            <i class="fa-solid fa-circle-check me-1"></i>Completa
                                        </span>
                                    @else
                                        <span style="background:#dbeafe; color:#1e40af; border:1px solid #93c5fd; padding:0.25rem 0.6rem; border-radius:20px; font-size:0.73rem; font-weight:600;">
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

{{-- ===== FOOTER ===== --}}
<footer class="footer-qrlab mt-5 pt-5">
    <div class="container-fluid px-4 pb-4">
        <div class="row">
            <div class="col-md-4 mb-4 mb-md-0">
                <h5 class="fw-bold mb-2"><i class="fa-solid fa-qrcode me-2"></i>QR-LAB</h5>
                <p class="small" style="color:rgba(255,255,255,0.65)">
                    Educación y control de asistencia en la UTEC<br><br>
                    La Universidad Tecnológica de El Salvador (UTEC) es una institución comprometida
                    con la excelencia académica y la innovación tecnológica.
                </p>
            </div>
            <div class="col-md-2 mb-4 mb-md-0">
                <h6 class="fw-bold mb-3">Información</h6>
                <ul class="list-unstyled small">
                    <li class="mb-1"><a href="#">Portal Educativo</a></li>
                    <li class="mb-1"><a href="#">Biblioteca UTEC</a></li>
                    <li class="mb-1"><a href="#">Calendario Académico</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4 mb-md-0">
                <h6 class="fw-bold mb-3">Contáctanos</h6>
                <p class="small" style="color:rgba(255,255,255,0.65)">
                    <i class="fa-solid fa-location-dot me-1"></i> Dirección de Educación Virtual. Edificio José Martí 2do y 3er. nivel<br>
                    <i class="fa-solid fa-phone me-1"></i> Teléfono: (503) 2275 8888 ext: 8816, 8773, 8797, 8850<br>
                    <i class="fa-solid fa-envelope me-1"></i> utecvirtual@utec.edu.sv
                </p>
            </div>
            <div class="col-md-2">
                <h6 class="fw-bold mb-3">Síguenos</h6>
                <div class="d-flex gap-3 fs-4">
                    <a href="#"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="#"><i class="fa-brands fa-whatsapp"></i></a>
                    <a href="#"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-copyright text-center py-2 small" style="color:rgba(255,255,255,0.55)">
        Copyright &copy; 2026 - QR-LAB Sistema de Control de Asistencia
    </div>
</footer>

</body>
</html>
