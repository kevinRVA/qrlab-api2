@extends('layouts.app')

@section('title', 'QR-LAB | Asistencia de Clases')
@section('nav_icon', 'fa-clipboard-user')
@section('nav_subtitle', 'Asistencia de Clases')
@section('user_icon', 'fa-shield-halved')

@section('nav_actions')
    <a href="{{ route('admin.index') }}" class="btn btn-outline-light btn-sm me-1">
        <i class="fa-solid fa-arrow-left"></i>
        <span class="d-none d-sm-inline"> Inicio</span>
    </a>
    <button id="btn-actualizar" class="btn btn-outline-light btn-sm" onclick="cargarDatos()">
        <i class="fa-solid fa-rotate-right"></i>
        <span class="d-none d-sm-inline"> Actualizar</span>
    </button>
@endsection

@push('head_scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@push('styles')
<style>
    .table-hover tbody tr:hover { background-color: #f1f5f9; }
    .chart-container { position: relative; height: 280px; width: 100%; }
    .cursor-pointer { cursor: pointer; user-select: none; }
    .cursor-pointer:hover { background-color: #e9ecef !important; }

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

    /* Modal de descarga */
    #modal-descarga .modal-content {
        border-radius: 18px;
        border: none;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(107,26,42,0.2);
    }
    #modal-descarga .modal-header {
        background: linear-gradient(135deg, #166534, #15803d);
        color: #fff;
        border-bottom: none;
        padding: 1.25rem 1.5rem;
    }
    #modal-descarga .modal-header .btn-close { filter: invert(1); }
    .filtro-chip {
        display: inline-flex; align-items: center; gap: 0.4rem;
        background: #f0fdf4; color: #166534;
        border: 1px solid #bbf7d0;
        border-radius: 20px; padding: 0.3rem 0.75rem;
        font-size: 0.8rem; font-weight: 500;
    }
    .filtro-chip.activo {
        background: #dcfce7; border-color: #86efac; font-weight: 600;
    }
    .filtro-chip.inactivo {
        background: #f8fafc; color: #94a3b8; border-color: #e2e8f0;
    }
    .btn-confirmar-descarga {
        background: linear-gradient(135deg, #166534, #15803d);
        color: #fff; border: none; border-radius: 10px;
        padding: 0.6rem 1.5rem; font-weight: 600;
        transition: opacity 0.15s ease, transform 0.1s ease;
    }
    .btn-confirmar-descarga:hover { opacity: 0.9; transform: scale(1.02); color: #fff; }
</style>
@endpush

@section('content')
<div class="container-fluid mt-4 px-4 pb-5">

    {{-- Breadcrumb --}}
    <div class="section-breadcrumb fade-in">
        <i class="fa-solid fa-house text-qrlab"></i>
        <a href="{{ route('admin.index') }}">Panel Admin</a>
        <i class="fa-solid fa-chevron-right" style="font-size:0.7rem;"></i>
        <span class="fw-semibold text-dark">Asistencia de Clases</span>
    </div>

    {{-- ── Filtros + contador ── --}}
    <div class="row mb-4">
        <div class="col-md-9">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted"><i class="fa-solid fa-filter"></i> Filtros de Búsqueda</h6>
                    <div class="row g-2 mt-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Laboratorio</label>
                            <select id="filtro-lab" class="form-select form-select-sm" onchange="aplicarFiltros()">
                                <option value="TODOS">Todos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Docente</label>
                            <select id="filtro-docente" class="form-select form-select-sm" onchange="aplicarFiltros()">
                                <option value="TODOS">Todos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Asignatura</label>
                            <select id="filtro-materia" class="form-select form-select-sm" onchange="aplicarFiltros()">
                                <option value="TODOS">Todas</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Fecha</label>
                            <input type="date" id="filtro-fecha" class="form-control form-control-sm" onchange="aplicarFiltros()">
                        </div>
                        <div class="col-md-1">
                            <button class="btn btn-danger btn-sm w-100" onclick="limpiarFiltros()" title="Limpiar Filtros">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm stat-card h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    <h6 class="text-muted mb-1">Total Alumnos (Filtrados)</h6>
                    <h2 class="text-qrlab fw-bold mb-2" id="total-alumnos-filtrados">0</h2>
                    <button class="btn btn-qrlab btn-sm" onclick="descargarExcelGlobal()">
                        <i class="fa-solid fa-file-excel"></i> Descargar Resumen
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Gráficos ── --}}
    <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 text-primary"><i class="fa-solid fa-computer"></i> Alumnos por Laboratorio</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container"><canvas id="graficoLabs"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 text-success"><i class="fa-solid fa-book"></i> Uso por Asignatura</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container"><canvas id="graficoMaterias"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 text-warning text-dark"><i class="fa-solid fa-chalkboard-user"></i> Top Docentes</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container"><canvas id="graficoDocentes"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tabla de sesiones ── --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-dark"><i class="fa-solid fa-list"></i> Historial Detallado de Sesiones</h6>
                    <span class="badge bg-secondary" id="contador-sesiones">0 sesiones</span>
                </div>
                <div class="card-body p-0 table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover mb-0 text-sm align-middle">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="cursor-pointer" onclick="ordenarTabla('fecha')">
                                    Fecha <i class="fa-solid fa-sort text-muted ms-1" id="icono-fecha"></i>
                                </th>
                                <th class="cursor-pointer" onclick="ordenarTabla('laboratorio')">
                                    Laboratorio <i class="fa-solid fa-sort text-muted ms-1" id="icono-laboratorio"></i>
                                </th>
                                <th class="cursor-pointer" onclick="ordenarTabla('docente')">
                                    Docente <i class="fa-solid fa-sort text-muted ms-1" id="icono-docente"></i>
                                </th>
                                <th class="cursor-pointer" onclick="ordenarTabla('asignatura')">
                                    Asignatura <i class="fa-solid fa-sort text-muted ms-1" id="icono-asignatura"></i>
                                </th>
                                <th class="text-center cursor-pointer" onclick="ordenarTabla('alumnos')">
                                    Alumnos <i class="fa-solid fa-sort text-muted ms-1" id="icono-alumnos"></i>
                                </th>
                                <th class="text-center">Detalle</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-sesiones">
                            <tr><td colspan="6" class="text-center py-4">Cargando datos...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     MODAL DE CONFIRMACIÓN DE DESCARGA
══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-descarga" tabindex="-1" aria-labelledby="modal-descarga-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:10px;
                                display:flex;align-items:center;justify-content:center;font-size:1rem;">
                        <i class="fa-solid fa-file-excel"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="modal-descarga-label">Confirmar Descarga</h5>
                        <p class="mb-0" style="font-size:0.75rem;opacity:0.8;">Revisa los filtros antes de descargar</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4">
                {{-- Descripción --}}
                <p class="text-muted mb-3" style="font-size:0.875rem;" id="modal-desc-texto">
                    Estás a punto de descargar el reporte con los siguientes filtros aplicados:
                </p>

                {{-- Chips de filtros --}}
                <div class="d-flex flex-wrap gap-2 mb-4" id="modal-filtros-chips"></div>

                {{-- Resumen de datos --}}
                <div style="background:#f8fafc;border-radius:12px;padding:1rem 1.25rem;border:1px solid #e2e8f0;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-chart-bar text-success"></i>
                        <span style="font-size:0.875rem;color:#374151;">Registros a exportar:</span>
                        <strong id="modal-total-registros" class="ms-auto" style="color:var(--qr-primary);font-size:1.1rem;">0</strong>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="background:#fafafa;border-top:1px solid #f1f5f9;padding:0.75rem 1.25rem;">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-1"></i> Cancelar
                </button>
                <button type="button" class="btn-confirmar-descarga" id="btn-confirmar-dl" onclick="ejecutarDescarga()">
                    <i class="fa-solid fa-download me-1"></i> Sí, Descargar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let todasLasSesiones = [];
    let sesionesFiltradas = [];
    let chartLabs = null, chartMaterias = null, chartDocentes = null;
    let columnaOrdenActual = '', ordenAscendente = true;

    document.addEventListener("DOMContentLoaded", () => cargarDatos());

    async function cargarDatos() {
        const btn = document.getElementById('btn-actualizar');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span class="d-none d-sm-inline"> Cargando...</span>';
        }
        try {
            const respuesta = await fetch('/api/admin/sesiones');
            todasLasSesiones = await respuesta.json();
            sesionesFiltradas = [...todasLasSesiones];
            llenarFiltrosDinamicos();
            actualizarUI();
        } catch (error) {
            console.error("Error al cargar:", error);
            document.getElementById('tabla-sesiones').innerHTML =
                '<tr><td colspan="6" class="text-center text-danger">Error de conexión.</td></tr>';
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-rotate-right"></i><span class="d-none d-sm-inline"> Actualizar</span>';
            }
        }
    }

    function llenarFiltrosDinamicos() {
        const labs     = [...new Set(todasLasSesiones.map(s => s.laboratory_name || 'Sin Asignar'))].sort();
        const docentes = [...new Set(todasLasSesiones.map(s => s.teacher_name))].sort();
        const materias = [...new Set(todasLasSesiones.map(s => s.subject))].sort();

        const selLab = document.getElementById('filtro-lab');
        selLab.innerHTML = '<option value="TODOS">Todos</option>';
        labs.forEach(l => selLab.innerHTML += `<option value="${l}">${l}</option>`);

        const selDoc = document.getElementById('filtro-docente');
        selDoc.innerHTML = '<option value="TODOS">Todos</option>';
        docentes.forEach(d => selDoc.innerHTML += `<option value="${d}">${d}</option>`);

        const selMat = document.getElementById('filtro-materia');
        selMat.innerHTML = '<option value="TODOS">Todas</option>';
        materias.forEach(m => selMat.innerHTML += `<option value="${m}">${m}</option>`);
    }

    function aplicarFiltros() {
        const labSel = document.getElementById('filtro-lab').value;
        const docSel = document.getElementById('filtro-docente').value;
        const matSel = document.getElementById('filtro-materia').value;
        const fechaSel = document.getElementById('filtro-fecha').value;

        sesionesFiltradas = todasLasSesiones.filter(s => {
            const matchLab   = labSel === "TODOS" || (s.laboratory_name || 'Sin Asignar') === labSel;
            const matchDoc   = docSel === "TODOS" || s.teacher_name === docSel;
            const matchMat   = matSel === "TODOS" || s.subject === matSel;
            const matchFecha = fechaSel === "" || s.created_at.split('T')[0] === fechaSel;
            return matchLab && matchDoc && matchMat && matchFecha;
        });
        actualizarUI();
    }

    function limpiarFiltros() {
        document.getElementById('filtro-lab').value    = "TODOS";
        document.getElementById('filtro-docente').value = "TODOS";
        document.getElementById('filtro-materia').value = "TODOS";
        document.getElementById('filtro-fecha').value  = "";
        aplicarFiltros();
    }

    function actualizarUI() {
        renderizarTabla();
        renderizarGraficos();
        const total = sesionesFiltradas.reduce((sum, s) => sum + s.attendances_count, 0);
        document.getElementById('total-alumnos-filtrados').innerText = total;
        document.getElementById('contador-sesiones').innerText = `${sesionesFiltradas.length} sesiones`;
    }

    function renderizarTabla() {
        const tbody = document.getElementById('tabla-sesiones');
        tbody.innerHTML = '';
        if (sesionesFiltradas.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No se encontraron clases.</td></tr>';
            return;
        }
        sesionesFiltradas.forEach(sesion => {
            const fecha = new Date(sesion.created_at).toLocaleDateString('es-ES', {
                year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit'
            });
            const botonDescarga = sesion.is_active
                ? `<span class="badge bg-warning text-dark"><i class="fa-solid fa-spinner fa-spin"></i> Activa</span>`
                : `<button class="btn btn-sm btn-outline-primary" title="Descargar Lista"
                    onclick="confirmarDescargaIndividual(${sesion.id}, '${sesion.subject.replace(/'/g, "\\'")}',' ${new Date(sesion.created_at).toLocaleDateString('es-ES')}')"
                   ><i class="fa-solid fa-download"></i></button>`;
            tbody.innerHTML += `
            <tr>
                <td class="small">${fecha}</td>
                <td class="fw-bold text-dark">${sesion.laboratory_name || 'Sin Asignar'}</td>
                <td>${sesion.teacher_name}</td>
                <td class="text-primary">${sesion.subject} <span class="badge bg-light text-dark border">Sec: ${sesion.section}</span></td>
                <td class="text-center fw-bold fs-6">${sesion.attendances_count}</td>
                <td class="text-center">${botonDescarga}</td>
            </tr>`;
        });
    }

    function renderizarGraficos() {
        const ctxLabs     = document.getElementById('graficoLabs').getContext('2d');
        const ctxMaterias = document.getElementById('graficoMaterias').getContext('2d');
        const ctxDocentes = document.getElementById('graficoDocentes').getContext('2d');

        const dataLabs = {}, dataMaterias = {}, dataDocentes = {};
        sesionesFiltradas.forEach(s => {
            const lab = s.laboratory_name || 'Sin Asignar';
            dataLabs[lab]              = (dataLabs[lab]              || 0) + s.attendances_count;
            dataMaterias[s.subject]    = (dataMaterias[s.subject]    || 0) + s.attendances_count;
            dataDocentes[s.teacher_name] = (dataDocentes[s.teacher_name] || 0) + s.attendances_count;
        });

        const paletaFondo = [
            'rgba(13,110,253,0.7)','rgba(25,135,84,0.7)','rgba(255,193,7,0.7)',
            'rgba(220,53,69,0.7)','rgba(111,66,193,0.7)','rgba(13,202,240,0.7)',
            'rgba(253,126,20,0.7)','rgba(214,51,132,0.7)','rgba(32,201,151,0.7)'
        ];
        const paletaBorde = paletaFondo.map(c => c.replace('0.7', '1'));

        if (chartLabs)     chartLabs.destroy();
        if (chartMaterias) chartMaterias.destroy();
        if (chartDocentes) chartDocentes.destroy();

        const labelsLabs = Object.keys(dataLabs);
        chartLabs = new Chart(ctxLabs, {
            type: 'bar',
            data: { labels: labelsLabs, datasets: [{ data: Object.values(dataLabs),
                backgroundColor: labelsLabs.map((_, i) => paletaFondo[i % paletaFondo.length]),
                borderColor: labelsLabs.map((_, i) => paletaBorde[i % paletaBorde.length]),
                borderWidth: 1, borderRadius: 5 }] },
            options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        const labelsMaterias = Object.keys(dataMaterias);
        chartMaterias = new Chart(ctxMaterias, {
            type: 'doughnut',
            data: { labels: labelsMaterias, datasets: [{ data: Object.values(dataMaterias),
                backgroundColor: labelsMaterias.map((_, i) => paletaFondo[(i+3) % paletaFondo.length]),
                borderColor: '#ffffff', borderWidth: 2 }] },
            options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

        const arr = Object.entries(dataDocentes).sort((a, b) => b[1] - a[1]);
        chartDocentes = new Chart(ctxDocentes, {
            type: 'bar',
            data: { labels: arr.map(x => x[0]), datasets: [{ data: arr.map(x => x[1]),
                backgroundColor: arr.map((_, i) => paletaFondo[(i+6) % paletaFondo.length]),
                borderColor: arr.map((_, i) => paletaBorde[(i+6) % paletaBorde.length]),
                borderWidth: 1, borderRadius: 5 }] },
            options: { maintainAspectRatio: false, indexAxis: 'y',
                plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
        });
    }

    // Descarga global con confirmación
    let _pendingDescargarFn = null;

    function descargarExcelGlobal() {
        if (sesionesFiltradas.length === 0) {
            toastr.info('No hay datos para descargar con los filtros actuales.', 'Sin datos');
            return;
        }
        abrirModalDescarga();
    }

    // Descarga individual por sesión con confirmación
    function confirmarDescargaIndividual(sesionId, materia, fecha) {
        const modal = new bootstrap.Modal(document.getElementById('modal-descarga'));

        document.getElementById('modal-desc-texto').textContent =
            'Estás a punto de descargar la lista de asistencia de la siguiente sesión:';

        document.getElementById('modal-filtros-chips').innerHTML = `
            <span class="filtro-chip activo"><i class="fa-solid fa-book"></i> ${materia}</span>
            <span class="filtro-chip activo"><i class="fa-regular fa-calendar"></i> ${fecha}</span>
            <span class="filtro-chip activo" style="background:#ede9fe;color:#7c3aed;border-color:#c4b5fd;">
                <i class="fa-solid fa-users"></i> Lista completa de alumnos
            </span>`;

        document.getElementById('modal-total-registros').textContent = 'Lista de asistentes';

        _pendingDescargarFn = () => {
            window.location.href = `/api/admin/descargar-reporte/${sesionId}`;
        };

        modal.show();
    }

    function abrirModalDescarga() {
        const modal = new bootstrap.Modal(document.getElementById('modal-descarga'));

        // Leer filtros activos
        const labVal  = document.getElementById('filtro-lab').value;
        const docVal  = document.getElementById('filtro-docente').value;
        const matVal  = document.getElementById('filtro-materia').value;
        const fecVal  = document.getElementById('filtro-fecha').value;

        const chips = [
            { label: 'Laboratorio', valor: labVal,  icono: 'fa-computer',           defecto: 'TODOS' },
            { label: 'Docente',     valor: docVal,  icono: 'fa-chalkboard-user',    defecto: 'TODOS' },
            { label: 'Asignatura',  valor: matVal,  icono: 'fa-book',               defecto: 'TODOS' },
            { label: 'Fecha',       valor: fecVal,  icono: 'fa-calendar-day',       defecto: '' },
        ];

        document.getElementById('modal-desc-texto').textContent =
            'Estás a punto de descargar el resumen de asistencias con los siguientes filtros:';

        document.getElementById('modal-filtros-chips').innerHTML = chips.map(c => {
            const activo = c.valor && c.valor !== c.defecto;
            const texto  = activo ? c.valor : 'Todos';
            return `<span class="filtro-chip ${activo ? 'activo' : 'inactivo'}">
                        <i class="fa-solid ${c.icono}"></i>
                        <strong>${c.label}:</strong> ${texto}
                    </span>`;
        }).join('');

        const total = sesionesFiltradas.reduce((s, x) => s + x.attendances_count, 0);
        document.getElementById('modal-total-registros').textContent =
            `${sesionesFiltradas.length} sesiones / ${total} alumnos`;

        _pendingDescargarFn = ejecutarDescargaGlobal;
        modal.show();
    }

    function ejecutarDescarga() {
        bootstrap.Modal.getInstance(document.getElementById('modal-descarga')).hide();
        if (_pendingDescargarFn) {
            setTimeout(_pendingDescargarFn, 250); // Esperar que cierre el modal
            _pendingDescargarFn = null;
        }
    }

    function ejecutarDescargaGlobal() {
        let csv = "\uFEFFecha;Laboratorio;Docente;Asignatura;Seccion;Estado;Total Alumnos\n";
        sesionesFiltradas.forEach(s => {
            const fecha  = new Date(s.created_at).toLocaleString('es-ES');
            const estado = s.is_active ? "En curso" : "Finalizada";
            csv += `"${fecha}";"${s.laboratory_name||'Sin Asignar'}";"${s.teacher_name}";"${s.subject}";"${s.section}";"${estado}";"${s.attendances_count}"\n`;
        });
        const link = document.createElement("a");
        link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }));
        link.download = "Asistencia_Clases_QRLAB.csv";
        document.body.appendChild(link); link.click(); document.body.removeChild(link);
        toastr.success('Reporte descargado correctamente.', '✓ Descarga lista');
    }

    function ordenarTabla(columna) {
        ordenAscendente = columnaOrdenActual === columna ? !ordenAscendente : true;
        columnaOrdenActual = columna;
        sesionesFiltradas.sort((a, b) => {
            let vA, vB;
            switch (columna) {
                case 'fecha':       vA = new Date(a.created_at).getTime(); vB = new Date(b.created_at).getTime(); break;
                case 'laboratorio': vA = (a.laboratory_name||'Sin Asignar').toLowerCase(); vB = (b.laboratory_name||'Sin Asignar').toLowerCase(); break;
                case 'docente':     vA = a.teacher_name.toLowerCase(); vB = b.teacher_name.toLowerCase(); break;
                case 'asignatura':  vA = a.subject.toLowerCase(); vB = b.subject.toLowerCase(); break;
                case 'alumnos':     vA = a.attendances_count; vB = b.attendances_count; break;
                default: return 0;
            }
            if (vA < vB) return ordenAscendente ? -1 : 1;
            if (vA > vB) return ordenAscendente ?  1 : -1;
            return 0;
        });
        ['fecha','laboratorio','docente','asignatura','alumnos'].forEach(col => {
            const ic = document.getElementById(`icono-${col}`);
            if (!ic) return;
            ic.className = col === columna
                ? (ordenAscendente ? 'fa-solid fa-sort-up text-primary ms-1' : 'fa-solid fa-sort-down text-primary ms-1')
                : 'fa-solid fa-sort text-muted ms-1';
        });
        renderizarTabla();
    }
</script>
@endpush
