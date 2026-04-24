@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800"><i class="fa-solid fa-chalkboard-user me-2 text-primary"></i>Gestión de Instructores</h2>
        <a href="{{ route('admin.instructors.assign') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-2"></i>Asignar Instructor
        </a>
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


@endsection

@push('scripts')
<script>
    let allStudents = [];
    document.addEventListener('DOMContentLoaded', () => {
        cargarDatos();
    });

    async function cargarDatos() {
        try {
            const resp = await fetch('/api/admin/system/instructors');
            const data = await resp.json();
            allStudents = data.students;
            renderTabla();
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
                        <a href="{{ route('admin.instructors.assign') }}?student_id=${inst.id}" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-plus"></i> Sección
                        </a>
                    </td>
                </tr>
            `;
        });
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
