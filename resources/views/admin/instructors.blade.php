@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800"><i class="fa-solid fa-chalkboard-user me-2 text-primary"></i>Gestión de Instructores</h2>
        <button class="btn btn-primary" onclick="abrirModalAsignar()">
            <i class="fa-solid fa-plus me-2"></i>Asignar Instructor
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Carnet</th>
                            <th>Nombre</th>
                            <th>Secciones Asignadas</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-instructores">
                        <tr><td colspan="4" class="text-center py-4"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL ASIGNAR INSTRUCTOR --}}
<div class="modal fade" id="modal-asignar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa-solid fa-user-plus me-2"></i>Asignar Instructor a Sección</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-asignar">
                    <div class="mb-3">
                        <label class="form-label">Seleccionar Estudiante</label>
                        <select class="form-select" id="select-student" required>
                            <option value="">Seleccione un estudiante...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Seleccionar Sección (Materia - Docente)</label>
                        <select class="form-select" id="select-section" required>
                            <option value="">Seleccione una sección...</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarAsignacion()">Guardar Asignación</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let allStudents = [];
    let allSections = [];
    const modalAsignar = new bootstrap.Modal(document.getElementById('modal-asignar'));

    document.addEventListener('DOMContentLoaded', () => {
        cargarDatos();
    });

    async function cargarDatos() {
        try {
            const resp = await fetch('/api/admin/system/instructors');
            const data = await resp.json();
            allStudents = data.students;
            allSections = data.sections;
            renderTabla();
            poblarSelects();
        } catch (e) {
            console.error(e);
            toastr.error('Error al cargar datos.');
        }
    }

    function renderTabla() {
        const tbody = document.getElementById('tabla-instructores');
        tbody.innerHTML = '';
        
        // Filtrar solo los estudiantes que tienen is_instructor = 1
        const instructores = allStudents.filter(s => s.is_instructor == 1 || (s.instructor_sections && s.instructor_sections.length > 0));

        if (instructores.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">No hay instructores asignados.</td></tr>';
            return;
        }

        instructores.forEach(inst => {
            let seccionesHtml = '';
            if (inst.instructor_sections && inst.instructor_sections.length > 0) {
                inst.instructor_sections.forEach(sec => {
                    seccionesHtml += `<span class="badge bg-info text-dark me-1 mb-1">
                        ${sec.subject.name} (${sec.section_code}) - Prof. ${sec.teacher.name}
                        <i class="fa-solid fa-xmark ms-1" style="cursor:pointer" onclick="removerAsignacion(${inst.id}, ${sec.id})" title="Remover"></i>
                    </span>`;
                });
            } else {
                seccionesHtml = '<span class="text-muted small">Sin secciones</span>';
            }

            tbody.innerHTML += `
                <tr>
                    <td><span class="badge bg-secondary">${inst.user_code}</span></td>
                    <td><div class="fw-bold text-dark">${inst.name}</div><div class="small text-muted">${inst.email}</div></td>
                    <td>${seccionesHtml}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary" onclick="abrirModalAsignar(${inst.id})">
                            <i class="fa-solid fa-plus"></i> Sección
                        </button>
                    </td>
                </tr>
            `;
        });
    }

    function poblarSelects() {
        const selStudent = document.getElementById('select-student');
        const selSection = document.getElementById('select-section');
        
        selStudent.innerHTML = '<option value="">Seleccione un estudiante...</option>';
        allStudents.forEach(s => {
            selStudent.innerHTML += `<option value="${s.id}">${s.user_code} - ${s.name}</option>`;
        });

        selSection.innerHTML = '<option value="">Seleccione una sección...</option>';
        allSections.forEach(s => {
            selSection.innerHTML += `<option value="${s.id}">${s.subject ? s.subject.name : 'Materia'} (${s.section_code}) - Prof. ${s.teacher ? s.teacher.name : 'Docente'}</option>`;
        });
    }

    function abrirModalAsignar(studentId = null) {
        document.getElementById('form-asignar').reset();
        if (studentId) {
            document.getElementById('select-student').value = studentId;
        }
        modalAsignar.show();
    }

    async function guardarAsignacion() {
        const studentId = document.getElementById('select-student').value;
        const sectionId = document.getElementById('select-section').value;

        if (!studentId || !sectionId) {
            toastr.warning('Seleccione estudiante y sección');
            return;
        }

        try {
            const resp = await fetch('/api/admin/system/instructors/assign', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ user_id: studentId, section_id: sectionId })
            });

            const data = await resp.json();
            if (data.ok) {
                toastr.success(data.mensaje);
                modalAsignar.hide();
                cargarDatos();
            } else {
                toastr.error(data.mensaje || 'Error al asignar');
            }
        } catch (e) {
            console.error(e);
            toastr.error('Error de red');
        }
    }

    async function removerAsignacion(studentId, sectionId) {
        if (!confirm('¿Seguro que deseas remover a este instructor de la sección?')) return;

        try {
            const resp = await fetch('/api/admin/system/instructors/remove', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ user_id: studentId, section_id: sectionId })
            });

            const data = await resp.json();
            if (data.ok) {
                toastr.success(data.mensaje);
                cargarDatos();
            } else {
                toastr.error(data.mensaje || 'Error al remover');
            }
        } catch (e) {
            console.error(e);
            toastr.error('Error de red');
        }
    }
</script>
@endpush
