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

    /* Botón finalizar */
    .btn-finalizar {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 0.25rem 0.65rem;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        transition: opacity 0.15s ease, transform 0.1s ease;
        white-space: nowrap;
    }
    .btn-finalizar:hover { opacity: 0.85; transform: scale(1.03); color: #fff; }
    .btn-finalizar:disabled { opacity: 0.5; cursor: not-allowed; }

    /* Alerta banner de cierres */
    .alerta-banner {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        border-left: 5px solid #f59e0b;
        border-radius: 12px;
        padding: 0.9rem 1.25rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }
    .alerta-banner i { color: #b45309; font-size: 1.3rem; flex-shrink: 0; margin-top: 2px; }

    /* Modal de descarga */
    #modal-descarga-pl .modal-content {
        border-radius: 18px; border: none; overflow: hidden;
        box-shadow: 0 20px 60px rgba(107,26,42,0.2);
    }
    #modal-descarga-pl .modal-header {
        background: linear-gradient(135deg, #166534, #15803d);
        color: #fff; border-bottom: none; padding: 1.25rem 1.5rem;
    }
    #modal-descarga-pl .modal-header .btn-close { filter: invert(1); }
    .filtro-chip-pl {
        display: inline-flex; align-items: center; gap: 0.4rem;
        border-radius: 20px; padding: 0.3rem 0.75rem;
        font-size: 0.8rem; font-weight: 500;
    }
    .filtro-chip-pl.activo  { background: #dcfce7; color: #166534; border: 1px solid #86efac; font-weight: 600; }
    .filtro-chip-pl.inactivo{ background: #f8fafc; color: #94a3b8; border: 1px solid #e2e8f0; }
    .btn-confirmar-dl-pl {
        background: linear-gradient(135deg, #166534, #15803d);
        color: #fff; border: none; border-radius: 10px;
        padding: 0.6rem 1.5rem; font-weight: 600;
        transition: opacity 0.15s ease, transform 0.1s ease;
    }
    .btn-confirmar-dl-pl:hover { opacity: 0.9; transform: scale(1.02); color: #fff; }
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

    {{-- Banner de alertas de cierres automáticos (se llena dinámicamente) --}}
    <div id="contenedor-alertas"></div>

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
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-visitas-lab">
                    <tr><td colspan="9" class="text-center py-4 text-muted">Cargando registros...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════════
     MODAL CONFIRMACIÓN DE DESCARGA — PRÁCTICAS LIBRES
══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-descarga-pl" tabindex="-1" aria-labelledby="modal-dl-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:10px;
                                display:flex;align-items:center;justify-content:center;font-size:1rem;">
                        <i class="fa-solid fa-file-excel"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="modal-dl-label">Confirmar Descarga</h5>
                        <p class="mb-0" style="font-size:0.75rem;opacity:0.8;">Revisa los filtros antes de exportar</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-3" style="font-size:0.875rem;">
                    Estás a punto de exportar el registro de visitas con los siguientes filtros aplicados:
                </p>
                <div class="d-flex flex-wrap gap-2 mb-4" id="pl-filtros-chips"></div>
                <div style="background:#f8fafc;border-radius:12px;padding:1rem 1.25rem;border:1px solid #e2e8f0;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-list text-success"></i>
                        <span style="font-size:0.875rem;color:#374151;">Registros a exportar:</span>
                        <strong id="pl-total-registros" class="ms-auto" style="color:var(--qr-primary);font-size:1.1rem;">0</strong>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background:#fafafa;border-top:1px solid #f1f5f9;padding:0.75rem 1.25rem;">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-1"></i> Cancelar
                </button>
                <button type="button" class="btn-confirmar-dl-pl" onclick="ejecutarDescargaVisitas()">
                    <i class="fa-solid fa-download me-1"></i> Sí, Exportar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let todosLosLabs  = [];
    let visitasCache  = [];
    const CSRF        = '{{ csrf_token() }}';

    document.addEventListener('DOMContentLoaded', () => {
        cargarLabsYVisitas();
        verificarAlertasCierreAuto();
    });

    // ── Carga labs + visitas ──────────────────────────────────────────────
    async function cargarLabsYVisitas() {
        const btn = document.getElementById('btn-actualizar');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span class="d-none d-sm-inline"> Cargando...</span>';
        }
        try {
            const resp = await fetch('/api/admin/labs');
            todosLosLabs = await resp.json();

            const sel = document.getElementById('filtro-lab-visitas');
            sel.innerHTML = '<option value="TODOS">Todos los laboratorios</option>';
            todosLosLabs.forEach(l => {
                sel.innerHTML += `<option value="${l.id}">${l.name}</option>`;
            });

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
            toastr.error('No se pudieron cargar los datos del servidor.', 'Error de conexión');
            console.error('Error al cargar labs:', err);
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-rotate-right"></i><span class="d-none d-sm-inline"> Actualizar</span>';
            }
        }
    }

    // ── Carga visitas ─────────────────────────────────────────────────────
    async function cargarVisitas() {
        const labId = document.getElementById('filtro-lab-visitas').value;
        const desde = document.getElementById('filtro-fecha-desde').value;
        const hasta = document.getElementById('filtro-fecha-hasta').value;

        let url = '/api/admin/lab-visitas?';
        if (labId !== 'TODOS') url += `lab_id=${labId}&`;
        if (desde) url += `fecha_desde=${desde}&`;
        if (hasta) url += `fecha_hasta=${hasta}&`;

        const tbody = document.getElementById('tabla-visitas-lab');
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-3 text-muted"><i class="fa-solid fa-spinner fa-spin me-1"></i>Cargando...</td></tr>';

        try {
            const resp   = await fetch(url);
            visitasCache = await resp.json();

            document.getElementById('badge-visitas-hoy').textContent = `${visitasCache.length} visita(s)`;

            if (visitasCache.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">No hay registros para los filtros seleccionados.</td></tr>';
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

                // Botón finalizar solo si la visita está abierta
                const btnFinalizar = !v.exit_time
                    ? `<button class="btn-finalizar" onclick="finalizarVisita(${v.id}, this)" title="Finalizar sesión">
                           <i class="fa-solid fa-stop-circle me-1"></i>Finalizar
                       </button>`
                    : `<span class="text-muted small">—</span>`;

                return `
                <tr id="fila-visita-${v.id}">
                    <td class="text-muted small">${v.fecha ?? '—'}</td>
                    <td class="fw-bold" style="color:var(--qr-primary);">${v.carnet}</td>
                    <td>${v.nombre}</td>
                    <td><span class="badge bg-light text-dark border" style="font-size:0.78rem;">${v.laboratorio}</span></td>
                    <td class="text-center fw-bold">${v.entry_time}</td>
                    <td class="text-center">${salida}</td>
                    <td class="text-center">${duracion}</td>
                    <td class="text-center">${estadoBadge}</td>
                    <td class="text-center">${btnFinalizar}</td>
                </tr>`;
            }).join('');

        } catch (err) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error al cargar los datos.</td></tr>';
            toastr.error('Error al cargar los registros de visitas.', 'Error');
            console.error(err);
        }
    }

    // ── Finalizar visita ──────────────────────────────────────────────────
    async function finalizarVisita(id, btn) {
        if (!confirm('¿Estás seguro de finalizar esta sesión? Se registrará la salida ahora mismo.')) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

        try {
            const resp = await fetch(`/api/admin/lab-visitas/${id}/finalizar`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Accept':       'application/json',
                    'Content-Type': 'application/json',
                }
            });

            const data = await resp.json();

            if (data.ok) {
                toastr.success('Sesión finalizada correctamente.', '✓ Visita cerrada');
                await cargarVisitas(); // Recargar tabla
            } else {
                toastr.warning(data.mensaje || 'No se pudo finalizar.', 'Aviso');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-stop-circle me-1"></i>Finalizar';
            }
        } catch (err) {
            toastr.error('Error al conectar con el servidor.', 'Error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-stop-circle me-1"></i>Finalizar';
            console.error(err);
        }
    }

    // ── Verificar alertas de cierres automáticos ──────────────────────────
    async function verificarAlertasCierreAuto() {
        try {
            const resp   = await fetch('/api/admin/alertas-cierre-auto');
            const alertas = await resp.json();

            const contenedor = document.getElementById('contenedor-alertas');
            contenedor.innerHTML = '';

            if (alertas.length > 0) {
                // Banner en la página
                const listaNombres = alertas.map(a =>
                    `<li><strong>${a.carnet ?? 'N/A'}</strong> — ${a.nombre} <span class="badge" style="background:#ef4444; color:#fff; border-radius:20px; font-size:0.7rem; padding:0.15rem 0.5rem;">${a.total_cierres} cierres</span></li>`
                ).join('');

                contenedor.innerHTML = `
                    <div class="alerta-banner fade-in">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <div>
                            <strong style="color:#92400e;">Alerta — Estudiantes con 3 o más cierres automáticos:</strong>
                            <ul class="mb-0 mt-1 ps-3" style="color:#78350f; font-size:0.875rem;">${listaNombres}</ul>
                        </div>
                    </div>`;

                // Toastr por cada alerta (máximo 5 para no saturar)
                alertas.slice(0, 5).forEach(a => {
                    toastr.warning(
                        `<b>${a.carnet ?? ''} — ${a.nombre}</b> tiene <b>${a.total_cierres}</b> cierres automáticos.`,
                        '⚠️ Alerta de cierre automático',
                        { timeOut: 6000 }
                    );
                });
            }
        } catch (err) {
            console.error('Error al verificar alertas:', err);
        }
    }

    // ── Resetear filtros ──────────────────────────────────────────────────
    function resetFiltrosVisitas() {
        const hoy   = new Date();
        const desde = new Date(hoy); desde.setDate(hoy.getDate() - 6);
        document.getElementById('filtro-fecha-desde').value = desde.toISOString().split('T')[0];
        document.getElementById('filtro-fecha-hasta').value = hoy.toISOString().split('T')[0];
        document.getElementById('filtro-lab-visitas').value = 'TODOS';
        cargarVisitas();
    }

    // ── Exportar CSV con confirmación ────────────────────────────────────────
    function descargarExcelVisitas() {
        if (visitasCache.length === 0) {
            toastr.info('No hay datos para exportar con los filtros actuales.', 'Sin datos');
            return;
        }

        // Rellenar chips de filtros en el modal
        const labSel  = document.getElementById('filtro-lab-visitas');
        const labVal  = labSel.value;
        const labText = labSel.options[labSel.selectedIndex]?.text ?? 'Todos';
        const desde   = document.getElementById('filtro-fecha-desde').value || '';
        const hasta   = document.getElementById('filtro-fecha-hasta').value || '';

        const chips = [
            { label: 'Laboratorio', valor: labVal  !== 'TODOS' ? labText : null, icono: 'fa-computer' },
            { label: 'Desde',       valor: desde   || null,                       icono: 'fa-calendar-days' },
            { label: 'Hasta',       valor: hasta   || null,                       icono: 'fa-calendar-check' },
        ];

        document.getElementById('pl-filtros-chips').innerHTML = chips.map(c => {
            const activo = !!c.valor;
            const texto  = activo ? c.valor : 'Todos';
            return `<span class="filtro-chip-pl ${activo ? 'activo' : 'inactivo'}">
                        <i class="fa-solid ${c.icono}"></i>
                        <strong>${c.label}:</strong> ${texto}
                    </span>`;
        }).join('');

        document.getElementById('pl-total-registros').textContent = `${visitasCache.length} visita(s)`;

        // Mostrar modal
        new bootstrap.Modal(document.getElementById('modal-descarga-pl')).show();
    }

    function ejecutarDescargaVisitas() {
        bootstrap.Modal.getInstance(document.getElementById('modal-descarga-pl')).hide();

        setTimeout(() => {
            const desde = document.getElementById('filtro-fecha-desde').value || '';
            const hasta = document.getElementById('filtro-fecha-hasta').value || '';
            const rango = (desde && hasta) ? `${desde}_al_${hasta}` : 'todas';

            let csv = "\uFEFFEcha;Carnet;Nombre;Laboratorio;Entrada;Salida;Duración;Estado\n";
            visitasCache.forEach(v => {
                const estado = v.no_exit_warning ? 'Sin salida (auto)' : (v.exit_time ? 'Completa' : 'En lab');
                csv += `"${v.fecha||'—'}";"${v.carnet}";"${v.nombre}";"${v.laboratorio}";"${v.entry_time}";"${v.exit_time||'—'}";"${v.duracion||'—'}";"${estado}"\n`;
            });

            const link = document.createElement("a");
            link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }));
            link.download = `PracticasLibres_${rango}.csv`;
            document.body.appendChild(link); link.click(); document.body.removeChild(link);
            toastr.success(`Exportado: PracticasLibres_${rango}.csv`, '\u2713 Descarga lista');
        }, 300);
    }
</script>
@endpush
