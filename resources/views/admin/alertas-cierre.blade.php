@extends('layouts.app')

@section('title', 'QR-LAB | Alertas de Cierres Automáticos')
@section('nav_icon', 'fa-triangle-exclamation')
@section('nav_subtitle', 'Alertas de Sesiones')
@section('user_icon', 'fa-shield-halved')

@section('nav_actions')
    <a href="{{ route('admin.practicas-libres') }}" class="btn btn-outline-light btn-sm me-1">
        <i class="fa-solid fa-arrow-left"></i>
        <span class="d-none d-sm-inline"> Prácticas Libres</span>
    </a>
    <button id="btn-actualizar" class="btn btn-outline-light btn-sm" onclick="cargarAlertas()">
        <i class="fa-solid fa-rotate-right"></i>
        <span class="d-none d-sm-inline"> Actualizar</span>
    </button>
@endsection

@push('styles')
<style>
    /* Breadcrumb */
    .section-breadcrumb {
        background: #fff; border-radius: 12px; padding: 0.75rem 1.25rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05); display: flex; align-items: center;
        gap: 0.5rem; font-size: 0.875rem; color: #64748b; margin-bottom: 1.5rem;
    }
    .section-breadcrumb a { color: var(--qr-primary); text-decoration: none; font-weight: 500; }
    .section-breadcrumb a:hover { text-decoration: underline; }

    /* Tarjeta de alerta */
    .alerta-card {
        background: #fff; border-radius: 14px; border-left: 5px solid #ef4444;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 1.25rem 1.5rem;
        transition: box-shadow 0.2s, transform 0.15s; margin-bottom: 1rem;
    }
    .alerta-card:hover { box-shadow: 0 4px 20px rgba(239,68,68,0.15); transform: translateY(-1px); }
    .alerta-card.severo { border-left-color: #7f1d1d; }

    /* Badge de cierres */
    .badge-cierres {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #fff; border-radius: 20px; padding: 0.3rem 0.85rem;
        font-size: 0.8rem; font-weight: 700;
        display: inline-flex; align-items: center; justify-content: center; gap: 0.3rem;
        white-space: nowrap;
    }
    .badge-cierres.severo { background: linear-gradient(135deg, #7f1d1d, #991b1b); }

    /* Stats bar */
    .stat-alerta {
        background: #fff; border-radius: 12px; padding: 1rem 1.4rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        display: flex; align-items: center; gap: 1rem;
        border-left: 4px solid var(--qr-primary);
    }
    .stat-alerta-icon {
        width: 40px; height: 40px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
    }

    /* Empty state */
    .empty-alert { text-align: center; padding: 4rem 1rem; color: #94a3b8; }
    .empty-alert i { font-size: 3rem; margin-bottom: 1rem; display: block; opacity: 0.3; }

    /* Búsqueda */
    .search-box {
        background: #fff; border-radius: 12px; border: 1.5px solid #e2e8f0;
        padding: 0.5rem 1rem; display: flex; align-items: center; gap: 0.6rem;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        transition: border-color 0.15s;
    }
    .search-box:focus-within { border-color: var(--qr-primary); }
    .search-box input {
        border: none; outline: none; flex: 1; font-size: 0.875rem; background: transparent;
    }

    /* Botón historial — centrado */
    .btn-historial {
        display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem;
        white-space: nowrap;
    }
</style>
@endpush

@section('content')
<div class="container-fluid mt-4 px-4 pb-5">

    {{-- Breadcrumb --}}
    <div class="section-breadcrumb fade-in">
        <i class="fa-solid fa-house text-qrlab"></i>
        <a href="{{ route('admin.index') }}">Panel Admin</a>
        <i class="fa-solid fa-chevron-right" style="font-size:0.7rem;"></i>
        <a href="{{ route('admin.practicas-libres') }}">Prácticas Libres</a>
        <i class="fa-solid fa-chevron-right" style="font-size:0.7rem;"></i>
        <span class="fw-semibold text-dark">Alertas de Cierres Automáticos</span>
    </div>

    {{-- Encabezado --}}
    <div class="d-flex align-items-center gap-2 mb-4 fade-in">
        <div style="width:5px;height:28px;background:#ef4444;border-radius:3px;"></div>
        <h5 class="mb-0 fw-bold text-dark">
            <i class="fa-solid fa-triangle-exclamation me-1" style="color:#ef4444;"></i>
            Estudiantes con Cierres Automáticos de Sesión
        </h5>
        <span class="badge ms-auto" style="background:#ef4444;" id="badge-total-alertas">Cargando...</span>
    </div>

    {{-- Descripción --}}
    <div class="alert alert-warning d-flex align-items-start gap-2 mb-4 fade-in"
         style="border-radius:12px;border-left:5px solid #f59e0b;">
        <i class="fa-solid fa-circle-info mt-1" style="color:#b45309;flex-shrink:0;"></i>
        <div style="font-size:0.875rem;color:#78350f;">
            <strong>¿Qué significa esto?</strong> Estos estudiantes tienen
            <strong>3 o más visitas a laboratorio</strong> donde el sistema cerró su sesión
            automáticamente a las 00:00 porque no escanearon el QR de salida.
            Una vez que el estudiante escanee su salida correctamente, la alerta se resolverá.
        </div>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4 fade-in">
        <div class="col-6 col-md-3">
            <div class="stat-alerta">
                <div class="stat-alerta-icon" style="background:#fef2f2;color:#ef4444;">
                    <i class="fa-solid fa-user-xmark"></i>
                </div>
                <div>
                    <div style="font-size:1.4rem;font-weight:700;color:#1a202c;line-height:1;"
                         id="stat-total-estudiantes">—</div>
                    <div style="font-size:0.75rem;color:#64748b;margin-top:2px;">Estudiantes en alerta</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-alerta">
                <div class="stat-alerta-icon" style="background:#fff7ed;color:#ea580c;">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <div style="font-size:1.4rem;font-weight:700;color:#1a202c;line-height:1;"
                         id="stat-total-cierres">—</div>
                    <div style="font-size:0.75rem;color:#64748b;margin-top:2px;">Cierres automáticos totales</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Barra de búsqueda --}}
    <div class="search-box mb-4 fade-in" style="max-width:480px;">
        <i class="fa-solid fa-magnifying-glass text-muted"></i>
        <input type="text" id="busqueda-alerta" placeholder="Buscar por nombre o código de estudiante..."
               oninput="filtrarAlertas()" autocomplete="off">
        <button class="btn btn-sm px-0 text-muted" onclick="document.getElementById('busqueda-alerta').value='';filtrarAlertas();"
                title="Limpiar búsqueda">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    {{-- Lista de estudiantes en alerta --}}
    <div id="contenedor-alertas-detalle">
        <div class="text-center py-5 text-muted">
            <i class="fa-solid fa-spinner fa-spin fa-2x mb-3 d-block opacity-50"></i>
            Cargando estudiantes con alertas...
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    let alertasData   = [];  // datos completos de la API
    let alertasFiltradas = []; // subconjunto según búsqueda

    document.addEventListener('DOMContentLoaded', () => cargarAlertas());

    // ── Carga desde API ───────────────────────────────────────────────────
    async function cargarAlertas() {
        const btn = document.getElementById('btn-actualizar');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span class="d-none d-sm-inline"> Cargando...</span>';
        }

        try {
            const resp  = await fetch('/api/admin/alertas-cierre-auto');
            alertasData = await resp.json();
            alertasFiltradas = [...alertasData];

            document.getElementById('badge-total-alertas').textContent =
                `${alertasData.length} estudiante(s) en alerta`;
            document.getElementById('stat-total-estudiantes').textContent = alertasData.length;

            const totalCierres = alertasData.reduce((s, a) => s + a.total_cierres, 0);
            document.getElementById('stat-total-cierres').textContent = totalCierres;

            renderizarAlertas(alertasFiltradas);

        } catch (err) {
            document.getElementById('contenedor-alertas-detalle').innerHTML = `
                <div class="text-center py-4 text-danger">
                    <i class="fa-solid fa-circle-xmark fa-2x mb-2 d-block"></i>
                    Error al cargar las alertas. Intenta de nuevo.
                </div>`;
            toastr.error('No se pudieron cargar las alertas.', 'Error');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-rotate-right"></i><span class="d-none d-sm-inline"> Actualizar</span>';
            }
        }
    }

    // ── Filtrar por búsqueda de nombre o código ───────────────────────────
    function filtrarAlertas() {
        const q = document.getElementById('busqueda-alerta').value.trim().toLowerCase();

        alertasFiltradas = q
            ? alertasData.filter(a =>
                (a.nombre ?? '').toLowerCase().includes(q) ||
                (a.carnet  ?? '').toLowerCase().includes(q)
              )
            : [...alertasData];

        renderizarAlertas(alertasFiltradas);
    }

    // ── Renderización de tarjetas ─────────────────────────────────────────
    function renderizarAlertas(lista) {
        const contenedor = document.getElementById('contenedor-alertas-detalle');

        if (lista.length === 0 && alertasData.length === 0) {
            contenedor.innerHTML = `
                <div class="empty-alert">
                    <i class="fa-solid fa-circle-check" style="color:#22c55e;"></i>
                    <p style="font-size:0.95rem;font-weight:600;color:#374151;margin:0;">
                        ¡Sin alertas activas!
                    </p>
                    <p style="font-size:0.85rem;margin-top:0.5rem;">
                        Ningún estudiante tiene 3 o más cierres automáticos pendientes.
                    </p>
                </div>`;
            toastr.success('No hay estudiantes en estado de alerta.', '✓ Sin alertas');
            return;
        }

        if (lista.length === 0) {
            contenedor.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="fa-solid fa-magnifying-glass fa-2x mb-2 d-block opacity-40"></i>
                    <p class="mb-0">No se encontraron estudiantes con ese nombre o código.</p>
                </div>`;
            return;
        }

        contenedor.innerHTML = lista.map(a => {
            const severo     = a.total_cierres >= 5;
            const claseCard  = severo ? 'severo' : '';
            const claseBadge = severo ? 'severo' : '';
            const iconoAlerta = severo
                ? '<i class="fa-solid fa-skull-crossbones"></i>'
                : '<i class="fa-solid fa-triangle-exclamation"></i>';

            return `
            <div class="alerta-card ${claseCard} fade-in">
                <div class="d-flex align-items-center gap-3 flex-wrap">

                    {{-- Avatar --}}
                    <div style="width:48px;height:48px;
                                background:${severo ? '#fef2f2' : '#fff7ed'};
                                border-radius:50%;display:flex;align-items:center;
                                justify-content:center;font-size:1.3rem;flex-shrink:0;
                                color:${severo ? '#ef4444' : '#ea580c'};">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>

                    {{-- Info --}}
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <strong style="font-size:0.95rem;color:#1a202c;">${a.nombre}</strong>
                            <span class="badge bg-light text-dark border" style="font-size:0.75rem;">
                                ${a.carnet ?? 'Sin carnet'}
                            </span>
                            ${severo
                                ? '<span class="badge" style="background:#fef2f2;color:#ef4444;border:1px solid #fca5a5;font-size:0.72rem;">⚠️ Caso severo</span>'
                                : ''}
                        </div>
                        <p class="mb-0 mt-1" style="font-size:0.8rem;color:#64748b;">
                            <i class="fa-solid fa-clock me-1"></i>
                            El sistema cerró su sesión automáticamente en
                            <strong>${a.total_cierres}</strong> ocasiones.
                        </p>
                    </div>

                    {{-- Badge cierres --}}
                    <div class="text-center" style="flex-shrink:0;">
                        <div class="badge-cierres ${claseBadge}">
                            ${iconoAlerta} ${a.total_cierres} cierres
                        </div>
                        <div style="font-size:0.7rem;color:#94a3b8;margin-top:4px;">automáticos</div>
                    </div>

                    {{-- Acción --}}
                    <div style="flex-shrink:0;">
                        <a href="{{ route('admin.practicas-libres') }}"
                           class="btn btn-sm btn-outline-secondary btn-historial"
                           title="Ver historial en prácticas libres">
                            <i class="fa-solid fa-list"></i> Historial
                        </a>
                    </div>

                </div>
            </div>`;
        }).join('');
    }
</script>
@endpush

@push('toastr')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Las notificaciones se disparan desde cargarAlertas() una vez cargados los datos
});
</script>
@endpush
