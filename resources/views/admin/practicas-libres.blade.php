@extends('layouts.app')

@section('title', 'QR-LAB | Prácticas Libres')
@section('nav_icon', 'fa-door-open')
@section('nav_subtitle', 'Prácticas Libres')
@section('user_icon', 'fa-shield-halved')

@section('nav_actions')
    <a href="{{ route('admin.index') }}" class="btn btn-outline-light btn-sm me-1">
        <i class="fa-solid fa-arrow-left"></i>
        <span class="d-none d-sm-inline"> Inicio</span>
    </a>
    <button id="btn-actualizar" class="btn btn-outline-light btn-sm" onclick="cargarLabsYVisitas()">
        <i class="fa-solid fa-rotate-right"></i>
        <span class="d-none d-sm-inline"> Actualizar</span>
    </button>
@endsection

@push('styles')
<style>
    /* Breadcrumb bar */
    .section-breadcrumb {
        background: #fff;
        border-radius: 12px;
        padding: 0.75rem 1.25rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: #64748b;
        margin-bottom: 1.5rem;
    }
    .section-breadcrumb a { color: var(--qr-primary); text-decoration: none; font-weight: 500; }
    .section-breadcrumb a:hover { text-decoration: underline; }

    /* Lab QR buttons grid */
    .lab-qr-grid { display: flex; flex-wrap: wrap; gap: 0.4rem; }

    .table-hover tbody tr:hover { background-color: #f1f5f9; }
</style>
@endpush

@section('content')
<div class="container-fluid mt-4 px-4 pb-5">

    {{-- Breadcrumb --}}
    <div class="section-breadcrumb fade-in">
        <i class="fa-solid fa-house text-qrlab"></i>
        <a href="{{ route('admin.index') }}">Panel Admin</a>
        <i class="fa-solid fa-chevron-right" style="font-size:0.7rem;"></i>
        <span class="fw-semibold text-dark">Prácticas Libres</span>
    </div>

    {{-- Encabezado de sección --}}
    <div class="d-flex align-items-center gap-2 mb-4 fade-in">
        <div style="width:5px; height:28px; background:var(--qr-primary); border-radius:3px;"></div>
        <h5 class="mb-0 fw-bold text-dark">
            <i class="fa-solid fa-door-open text-qrlab me-1"></i>
            Prácticas Libres — Registro de Visitas
        </h5>
        <span class="badge ms-2" style="background:var(--qr-primary);" id="badge-visitas-hoy">0 visitas hoy</span>
    </div>

    {{-- Filtros + botones de QR --}}
    <div class="row g-3 mb-4 fade-in fade-in-delay-1">
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-body py-3">
                    <h6 class="text-muted mb-3"><i class="fa-solid fa-filter"></i> Filtros</h6>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Laboratorio</label>
                            <select id="filtro-lab-visitas" class="form-select form-select-sm" onchange="cargarVisitas()">
                                <option value="TODOS">Todos los laboratorios</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Desde</label>
                            <input type="date" id="filtro-fecha-desde" class="form-control form-control-sm"
                                value="{{ now()->subDays(6)->format('Y-m-d') }}" onchange="cargarVisitas()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Hasta</label>
                            <input type="date" id="filtro-fecha-hasta" class="form-control form-control-sm"
                                value="{{ date('Y-m-d') }}" onchange="cargarVisitas()">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-sm btn-outline-secondary w-100" onclick="resetFiltrosVisitas()" title="Últimos 7 días">
                                <i class="fa-solid fa-rotate-left"></i> 7 días
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body py-3">
                    <h6 class="text-muted mb-2">
                        <i class="fa-solid fa-qrcode"></i> QR por Laboratorio
                        <span class="text-muted small fw-normal ms-1">(clic para imprimir)</span>
                    </h6>
                    <div id="lista-qr-labs" class="lab-qr-grid" style="max-height:90px; overflow-y:auto;">
                        <span class="text-muted small">Cargando labs...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de visitas --}}
    <div class="card shadow-sm fade-in fade-in-delay-2">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-dark">
                <i class="fa-solid fa-clipboard-list text-qrlab"></i> Registro de Visitas
            </h6>
            <button class="btn btn-sm btn-outline-success" onclick="descargarExcelVisitas()">
                <i class="fa-solid fa-file-excel"></i>
                <span class="d-none d-sm-inline"> Exportar</span>
            </button>
        </div>
        <div class="card-body p-0 table-responsive" style="max-height:500px; overflow-y:auto;">
            <table class="table table-hover mb-0 align-middle" style="font-size:0.875rem;">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>Fecha</th>
                        <th>Carnet</th>
                        <th>Nombre</th>
                        <th>Laboratorio</th>
                        <th class="text-center">Entrada</th>
                        <th class="text-center">Salida</th>
                        <th class="text-center">Duración</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody id="tabla-visitas-lab">
                    <tr><td colspan="8" class="text-center py-4 text-muted">Cargando registros...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    let todosLosLabs  = [];
    let visitasCache  = [];

    document.addEventListener('DOMContentLoaded', () => cargarLabsYVisitas());

    async function cargarLabsYVisitas() {
        const btn = document.getElementById('btn-actualizar');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span class="d-none d-sm-inline"> Cargando...</span>';
        }
        try {
            const resp = await fetch('/api/admin/labs');
            todosLosLabs = await resp.json();

            // Llenar el select de filtro
            const sel = document.getElementById('filtro-lab-visitas');
            sel.innerHTML = '<option value="TODOS">Todos los laboratorios</option>';
            todosLosLabs.forEach(l => {
                sel.innerHTML += `<option value="${l.id}">${l.name}</option>`;
            });

            // Botones de QR
            const listaQr = document.getElementById('lista-qr-labs');
            if (todosLosLabs.length === 0) {
                listaQr.innerHTML = '<span class="text-muted small">No hay laboratorios configurados.</span>';
            } else {
                listaQr.innerHTML = todosLosLabs.map(l =>
                    l.qr_token
                    ? `<a href="${l.print_url}" target="_blank" class="badge text-decoration-none"
                          style="background:var(--qr-primary); color:#fff; padding:0.3rem 0.6rem; border-radius:8px; font-size:0.72rem;">
                          <i class="fa-solid fa-qrcode me-1"></i>${l.name}
                       </a>`
                    : `<span class="badge bg-light text-muted border" style="font-size:0.72rem;">${l.name} (sin QR)</span>`
                ).join('');
            }

            await cargarVisitas();
        } catch (err) {
            console.error('Error al cargar labs:', err);
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-rotate-right"></i><span class="d-none d-sm-inline"> Actualizar</span>';
            }
        }
    }

    async function cargarVisitas() {
        const labId = document.getElementById('filtro-lab-visitas').value;

        const desde = document.getElementById('filtro-fecha-desde').value;
        const hasta = document.getElementById('filtro-fecha-hasta').value;

        let url = '/api/admin/lab-visitas?';
        if (labId !== 'TODOS') url += `lab_id=${labId}&`;
        if (desde) url += `fecha_desde=${desde}&`;
        if (hasta) url += `fecha_hasta=${hasta}&`;

        const tbody = document.getElementById('tabla-visitas-lab');
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-3 text-muted"><i class="fa-solid fa-spinner fa-spin me-1"></i>Cargando...</td></tr>';

        try {
            const resp    = await fetch(url);
            visitasCache  = await resp.json();

            document.getElementById('badge-visitas-hoy').textContent = `${visitasCache.length} visita(s)`;

            if (visitasCache.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No hay registros para los filtros seleccionados.</td></tr>';
                return;
            }

            tbody.innerHTML = visitasCache.map(v => {
                let estadoBadge;
                if (v.no_exit_warning) {
                    estadoBadge = `<span class="badge" style="background:#fef3c7; color:#92400e; border:1px solid #fcd34d; font-size:0.72rem;">
                                       <i class="fa-solid fa-triangle-exclamation"></i> Sin salida (auto)
                                   </span>`;
                } else if (v.exit_time) {
                    estadoBadge = `<span class="badge" style="background:#dcfce7; color:#166534; border:1px solid #86efac; font-size:0.72rem;">
                                       <i class="fa-solid fa-circle-check"></i> Completa
                                   </span>`;
                } else {
                    estadoBadge = `<span class="badge" style="background:#dbeafe; color:#1e40af; border:1px solid #93c5fd; font-size:0.72rem;">
                                       <i class="fa-solid fa-spinner fa-spin"></i> En laboratorio
                                   </span>`;
                }

                const salida   = v.exit_time || '<span class="text-muted">—</span>';
                const duracion = v.duracion  || '<span class="text-muted">—</span>';

                return `
                <tr>
                    <td class="text-muted small">${v.fecha ?? '—'}</td>
                    <td class="fw-bold" style="color:var(--qr-primary);">${v.carnet}</td>
                    <td>${v.nombre}</td>
                    <td><span class="badge bg-light text-dark border" style="font-size:0.78rem;">${v.laboratorio}</span></td>
                    <td class="text-center fw-bold">${v.entry_time}</td>
                    <td class="text-center">${salida}</td>
                    <td class="text-center">${duracion}</td>
                    <td class="text-center">${estadoBadge}</td>
                </tr>`;
            }).join('');

        } catch (err) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error al cargar los datos.</td></tr>';
            console.error(err);
        }
    }

    function resetFiltrosVisitas() {
        const hoy   = new Date();
        const desde = new Date(hoy); desde.setDate(hoy.getDate() - 6);
        document.getElementById('filtro-fecha-desde').value = desde.toISOString().split('T')[0];
        document.getElementById('filtro-fecha-hasta').value = hoy.toISOString().split('T')[0];
        document.getElementById('filtro-lab-visitas').value = 'TODOS';
        cargarVisitas();
    }

    function descargarExcelVisitas() {
        if (visitasCache.length === 0) { alert("No hay datos para exportar."); return; }

        const desde = document.getElementById('filtro-fecha-desde').value || '';
        const hasta = document.getElementById('filtro-fecha-hasta').value || '';
        const rango = (desde && hasta) ? `${desde}_al_${hasta}` : 'todas';

        let csv = "\uFEFFecha;Carnet;Nombre;Laboratorio;Entrada;Salida;Duración;Estado\n";
        visitasCache.forEach(v => {
            const estado = v.no_exit_warning ? 'Sin salida (auto)' : (v.exit_time ? 'Completa' : 'En lab');
            csv += `"${v.fecha||'—'}";"${v.carnet}";"${v.nombre}";"${v.laboratorio}";"${v.entry_time}";"${v.exit_time||'—'}";"${v.duracion||'—'}";"${estado}"\n`;
        });

        const link = document.createElement("a");
        link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }));
        link.download = `PracticasLibres_${rango}.csv`;
        document.body.appendChild(link); link.click(); document.body.removeChild(link);
    }
</script>
@endpush
