@extends('layouts.app')

@section('title', 'QR-LAB | Administración del Sistema')
@section('nav_icon', 'fa-cogs')
@section('nav_subtitle', 'Administración del Sistema')
@section('user_icon', 'fa-shield-halved')

@section('nav_actions')
    <a href="{{ route('admin.index') }}" class="btn btn-outline-light btn-sm me-1">
        <i class="fa-solid fa-arrow-left"></i>
        <span class="d-none d-sm-inline"> Inicio</span>
    </a>
@endsection

@push('styles')
<style>
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

    .nav-tabs .nav-link {
        color: #64748b;
        font-weight: 500;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 1rem 1.5rem;
    }
    .nav-tabs .nav-link.active {
        color: var(--qr-primary);
        border-bottom-color: var(--qr-primary);
        background: transparent;
    }
    .nav-tabs .nav-link:hover:not(.active) {
        border-bottom-color: #cbd5e1;
    }

    .table-hover tbody tr:hover { background-color: #f8fafc; }
    .action-btn { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; }
</style>
@endpush

@section('content')
<div class="container-fluid mt-4 px-4 pb-5">

    {{-- Breadcrumb --}}
    <div class="section-breadcrumb fade-in">
        <i class="fa-solid fa-house text-qrlab"></i>
        <a href="{{ route('admin.index') }}">Panel Admin</a>
        <i class="fa-solid fa-chevron-right" style="font-size:0.7rem;"></i>
        <span class="fw-semibold text-dark">Administración del Sistema</span>
    </div>

    <div class="card shadow-sm border-0 fade-in fade-in-delay-1">
        <div class="card-header bg-white border-bottom-0 p-0">
            <ul class="nav nav-tabs px-3 pt-2" id="configTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="coordinators-tab" data-bs-toggle="tab" data-bs-target="#coordinators-pane" type="button" role="tab">
                        <i class="fa-solid fa-users-gear me-1"></i> Coordinadores
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="labs-tab" data-bs-toggle="tab" data-bs-target="#labs-pane" type="button" role="tab">
                        <i class="fa-solid fa-computer me-1"></i> Laboratorios
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="configTabsContent">
                
                {{-- TAB: COORDINADORES --}}
                <div class="tab-pane fade show active" id="coordinators-pane" role="tabpanel" tabindex="0">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="mb-1 fw-bold text-dark">Gestión de Coordinadores</h5>
                            <p class="text-muted small mb-0">Crea, edita o elimina a los coordinadores del sistema.</p>
                        </div>
                        <button class="btn btn-primary btn-sm" onclick="abrirModalCoordinador()">
                            <i class="fa-solid fa-plus me-1"></i> Nuevo Coordinador
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle border">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Correo Electrónico</th>
                                    <th>Laboratorios Asignados</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-coordinadores">
                                <tr><td colspan="5" class="text-center py-4"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TAB: LABORATORIOS --}}
                <div class="tab-pane fade" id="labs-pane" role="tabpanel" tabindex="0">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="mb-1 fw-bold text-dark">Gestión de Laboratorios</h5>
                            <p class="text-muted small mb-0">Administra el catálogo de laboratorios disponibles.</p>
                        </div>
                        <button class="btn btn-success btn-sm" onclick="abrirModalLab()">
                            <i class="fa-solid fa-plus me-1"></i> Nuevo Laboratorio
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle border" style="max-width: 600px;">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre del Laboratorio</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-laboratorios">
                                <tr><td colspan="3" class="text-center py-4"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- MODAL COORDINADOR --}}
<div class="modal fade" id="modal-coordinador" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="titulo-modal-coordinador"><i class="fa-solid fa-user-plus me-2"></i>Nuevo Coordinador</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-coordinador">
                    <input type="hidden" id="coord-id">
                    <div class="mb-3">
                        <label class="form-label">Código</label>
                        <input type="text" class="form-control" id="coord-code" required placeholder="Ej: COORD-001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="coord-name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="coord-email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="coord-pass" placeholder="Dejar en blanco para mantener actual">
                        <small class="text-muted d-none" id="coord-pass-help">Al crear un usuario, la contraseña es obligatoria.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarCoordinador()">Guardar</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL ASIGNAR LABORATORIOS --}}
<div class="modal fade" id="modal-asignar-labs" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="fa-solid fa-network-wired me-2"></i>Asignar Laboratorios</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3 text-muted">Selecciona los laboratorios que el coordinador <strong id="assign-coord-name" class="text-dark"></strong> podrá administrar:</p>
                <input type="hidden" id="assign-coord-id">
                <div id="lista-labs-checkboxes" class="row g-2" style="max-height: 300px; overflow-y: auto;">
                    {{-- Checkboxes dinámicos --}}
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-dark" onclick="guardarAsignacion()">Guardar Asignación</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL LABORATORIO --}}
<div class="modal fade" id="modal-laboratorio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="titulo-modal-lab"><i class="fa-solid fa-computer me-2"></i>Nuevo Laboratorio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-laboratorio">
                    <input type="hidden" id="lab-id">
                    <div class="mb-0">
                        <label class="form-label">Nombre del Laboratorio</label>
                        <input type="text" class="form-control" id="lab-name" required placeholder="Ej: Laboratorio 10">
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success btn-sm" onclick="guardarLaboratorio()">Guardar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let coordinadores = [];
    let laboratorios = [];

    const modalCoord = new bootstrap.Modal(document.getElementById('modal-coordinador'));
    const modalLab = new bootstrap.Modal(document.getElementById('modal-laboratorio'));
    const modalAsignar = new bootstrap.Modal(document.getElementById('modal-asignar-labs'));

    document.addEventListener('DOMContentLoaded', () => {
        cargarCoordinadores();
        cargarLaboratorios();
    });

    // ==========================================
    // API: Coordinadores
    // ==========================================
    async function cargarCoordinadores() {
        try {
            const resp = await fetch('/api/admin/system/coordinators');
            coordinadores = await resp.json();
            renderTablaCoordinadores();
        } catch (e) {
            console.error(e);
            toastr.error('Error al cargar coordinadores.');
        }
    }

    function renderTablaCoordinadores() {
        const tbody = document.getElementById('tabla-coordinadores');
        tbody.innerHTML = '';
        if (coordinadores.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-3 text-muted">No hay coordinadores registrados.</td></tr>';
            return;
        }

        coordinadores.forEach(c => {
            const labs = c.coordinator_labs && c.coordinator_labs.length > 0 
                ? c.coordinator_labs.map(l => `<span class="badge bg-secondary me-1 mb-1">${l.name}</span>`).join('') 
                : '<span class="text-muted small">Ninguno</span>';

            tbody.innerHTML += `
                <tr>
                    <td class="fw-bold text-primary">${c.user_code}</td>
                    <td>${c.name}</td>
                    <td>${c.email}</td>
                    <td><div style="max-width: 250px;">${labs}</div></td>
                    <td class="text-end">
                        <button class="btn action-btn btn-outline-dark btn-sm me-1" onclick="abrirModalAsignar(${c.id})" title="Asignar Laboratorios">
                            <i class="fa-solid fa-link"></i>
                        </button>
                        <button class="btn action-btn btn-outline-primary btn-sm me-1" onclick="editarCoordinador(${c.id})" title="Editar">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="btn action-btn btn-outline-danger btn-sm" onclick="eliminarCoordinador(${c.id})" title="Eliminar">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    }

    function abrirModalCoordinador() {
        document.getElementById('form-coordinador').reset();
        document.getElementById('coord-id').value = '';
        document.getElementById('titulo-modal-coordinador').innerHTML = '<i class="fa-solid fa-user-plus me-2"></i>Nuevo Coordinador';
        document.getElementById('coord-pass-help').classList.remove('d-none');
        document.getElementById('coord-pass').required = true;
        modalCoord.show();
    }

    function editarCoordinador(id) {
        const c = coordinadores.find(x => x.id === id);
        if(!c) return;
        document.getElementById('form-coordinador').reset();
        document.getElementById('coord-id').value = c.id;
        document.getElementById('coord-code').value = c.user_code;
        document.getElementById('coord-name').value = c.name;
        document.getElementById('coord-email').value = c.email;
        document.getElementById('coord-pass').required = false;
        document.getElementById('coord-pass-help').classList.add('d-none');
        document.getElementById('titulo-modal-coordinador').innerHTML = '<i class="fa-solid fa-pen me-2"></i>Editar Coordinador';
        modalCoord.show();
    }

    async function guardarCoordinador() {
        const id = document.getElementById('coord-id').value;
        const code = document.getElementById('coord-code').value;
        const name = document.getElementById('coord-name').value;
        const email = document.getElementById('coord-email').value;
        const pass = document.getElementById('coord-pass').value;

        if(!code || !name || !email || (!id && !pass)) {
            toastr.warning('Completa todos los campos requeridos.');
            return;
        }

        const data = { user_code: code, name: name, email: email };
        if(pass) data.password = pass;

        const url = id ? `/api/admin/system/coordinators/${id}` : '/api/admin/system/coordinators';
        const method = id ? 'PUT' : 'POST';

        try {
            const resp = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(data)
            });
            const res = await resp.json();
            if (resp.ok && res.ok) {
                toastr.success(res.message);
                modalCoord.hide();
                cargarCoordinadores();
            } else {
                toastr.error(res.message || 'Error al guardar.');
            }
        } catch(e) { toastr.error('Error de red.'); }
    }

    async function eliminarCoordinador(id) {
        if(!confirm('¿Estás seguro de eliminar este coordinador? Esto no afectará las sesiones de los laboratorios, pero sí quitará su acceso.')) return;
        try {
            const resp = await fetch(`/api/admin/system/coordinators/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const res = await resp.json();
            if(resp.ok && res.ok) {
                toastr.success(res.message);
                cargarCoordinadores();
            } else {
                toastr.error('Error al eliminar.');
            }
        } catch(e) { toastr.error('Error de red.'); }
    }

    // ==========================================
    // API: Asignar Laboratorios
    // ==========================================
    function abrirModalAsignar(id) {
        const c = coordinadores.find(x => x.id === id);
        if(!c) return;
        document.getElementById('assign-coord-id').value = c.id;
        document.getElementById('assign-coord-name').innerText = c.name;

        const container = document.getElementById('lista-labs-checkboxes');
        container.innerHTML = '';
        
        const asignadosIds = c.coordinator_labs ? c.coordinator_labs.map(l => l.id) : [];

        if(laboratorios.length === 0) {
            container.innerHTML = '<div class="col-12"><p class="text-muted small">No hay laboratorios creados.</p></div>';
        } else {
            laboratorios.forEach(l => {
                const isChecked = asignadosIds.includes(l.id) ? 'checked' : '';
                container.innerHTML += `
                    <div class="col-md-6">
                        <div class="form-check border rounded p-2 px-3 bg-light" style="font-size: 0.9rem;">
                            <input class="form-check-input ms-0 me-2" type="checkbox" value="${l.id}" id="chk-lab-${l.id}" name="assign-labs[]" ${isChecked}>
                            <label class="form-check-label w-100" for="chk-lab-${l.id}" style="cursor:pointer;">
                                ${l.name}
                            </label>
                        </div>
                    </div>
                `;
            });
        }
        modalAsignar.show();
    }

    async function guardarAsignacion() {
        const id = document.getElementById('assign-coord-id').value;
        const checkboxes = document.querySelectorAll('input[name="assign-labs[]"]:checked');
        const labsSeleccionados = Array.from(checkboxes).map(cb => cb.value);

        try {
            const resp = await fetch(`/api/admin/system/coordinators/${id}/labs`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ labs: labsSeleccionados })
            });
            const res = await resp.json();
            if (resp.ok && res.ok) {
                toastr.success(res.message);
                modalAsignar.hide();
                cargarCoordinadores(); // Recargar para ver los badges actualizados
            } else {
                toastr.error('Error al asignar.');
            }
        } catch(e) { toastr.error('Error de red.'); }
    }


    // ==========================================
    // API: Laboratorios
    // ==========================================
    async function cargarLaboratorios() {
        try {
            const resp = await fetch('/api/admin/system/labs');
            laboratorios = await resp.json();
            renderTablaLaboratorios();
        } catch (e) {
            console.error(e);
            toastr.error('Error al cargar laboratorios.');
        }
    }

    function renderTablaLaboratorios() {
        const tbody = document.getElementById('tabla-laboratorios');
        tbody.innerHTML = '';
        if (laboratorios.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center py-3 text-muted">No hay laboratorios registrados.</td></tr>';
            return;
        }

        laboratorios.forEach(l => {
            tbody.innerHTML += `
                <tr>
                    <td class="text-muted small">#${l.id}</td>
                    <td class="fw-bold text-dark">${l.name}</td>
                    <td class="text-end">
                        <button class="btn action-btn btn-outline-primary btn-sm me-1" onclick="editarLaboratorio(${l.id})" title="Editar">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="btn action-btn btn-outline-danger btn-sm" onclick="eliminarLaboratorio(${l.id})" title="Eliminar">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    }

    function abrirModalLab() {
        document.getElementById('form-laboratorio').reset();
        document.getElementById('lab-id').value = '';
        document.getElementById('titulo-modal-lab').innerHTML = '<i class="fa-solid fa-computer me-2"></i>Nuevo Laboratorio';
        modalLab.show();
    }

    function editarLaboratorio(id) {
        const l = laboratorios.find(x => x.id === id);
        if(!l) return;
        document.getElementById('form-laboratorio').reset();
        document.getElementById('lab-id').value = l.id;
        document.getElementById('lab-name').value = l.name;
        document.getElementById('titulo-modal-lab').innerHTML = '<i class="fa-solid fa-pen me-2"></i>Editar Laboratorio';
        modalLab.show();
    }

    async function guardarLaboratorio() {
        const id = document.getElementById('lab-id').value;
        const name = document.getElementById('lab-name').value;

        if(!name) {
            toastr.warning('Ingresa el nombre del laboratorio.');
            return;
        }

        const data = { name: name };
        const url = id ? `/api/admin/system/labs/${id}` : '/api/admin/system/labs';
        const method = id ? 'PUT' : 'POST';

        try {
            const resp = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(data)
            });
            const res = await resp.json();
            if (resp.ok && res.ok) {
                toastr.success(res.message);
                modalLab.hide();
                cargarLaboratorios();
            } else {
                toastr.error(res.message || 'Error al guardar.');
            }
        } catch(e) { toastr.error('Error de red.'); }
    }

    async function eliminarLaboratorio(id) {
        if(!confirm('¿Estás seguro de eliminar este laboratorio? Esto no eliminará las sesiones vinculadas pero borrará el laboratorio del catálogo y de los coordinadores.')) return;
        try {
            const resp = await fetch(`/api/admin/system/labs/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const res = await resp.json();
            if(resp.ok && res.ok) {
                toastr.success(res.message);
                cargarLaboratorios();
                // Opcional: recargar coordinadores para actualizar los badges
                cargarCoordinadores();
            } else {
                toastr.error('Error al eliminar.');
            }
        } catch(e) { toastr.error('Error de red.'); }
    }

</script>
@endpush
