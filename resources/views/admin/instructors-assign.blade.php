@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800"><i class="fa-solid fa-user-plus me-2 text-primary"></i>Asignar Instructor a Sección</h2>
        <a href="{{ route('admin.instructors') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    {{-- Buscador de Estudiantes --}}
                    <div class="mb-2">
                        <label class="form-label fw-bold text-qrlab">Buscar Estudiante</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-search"></i></span>
                            <input type="text" id="search-input" class="form-control border-start-0 ps-0" placeholder="Ingrese nombre o carnet del estudiante..." onkeypress="if(event.key === 'Enter') { event.preventDefault(); buscarEstudiantes(); }">
                            <button type="button" class="btn btn-qrlab px-4 fw-bold" onclick="buscarEstudiantes()">
                                Buscar
                            </button>
                        </div>
                        <small class="text-muted mt-2 d-block">Ingrese al menos 2 caracteres para realizar la búsqueda.</small>
                    </div>
                </div>
            </div>

            {{-- Resultados de Búsqueda --}}
            <div class="card border-0 shadow-sm rounded-4" id="resultados-card" style="display: none;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold text-dark"><i class="fa-solid fa-list-check me-2 text-qrlab"></i>Resultados de Búsqueda</h5>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Carnet</th>
                                    <th>Nombre del Estudiante</th>
                                    <th>Correo Electrónico</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="table-students-body">
                                {{-- Resultados inyectados por JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL SELECCIONAR SECCION --}}
<div class="modal fade" id="modal-asignar-seccion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-qrlab text-white border-0" style="background-color: var(--qr-primary);">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-link me-2"></i>Asignar a Sección</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4 text-center">
                    <div class="fs-5 fw-bold text-dark" id="modal-student-name">Nombre Estudiante</div>
                    <div class="badge bg-secondary mt-1" id="modal-student-code">00000000</div>
                </div>
                
                <form id="form-asignar" onsubmit="event.preventDefault(); guardarAsignacion();">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small text-uppercase">Seleccione la Sección</label>
                        <select class="form-select form-select-lg" id="select-section" required>
                            <option value="">-- Elige una opción --</option>
                            @foreach($sections as $s)
                                <option value="{{ $s->id }}">
                                    {{ $s->subject ? $s->subject->name : 'Materia' }} ({{ $s->section_code }}) - Prof. {{ $s->teacher ? $s->teacher->name : 'Docente' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-qrlab btn-lg fw-bold" id="btn-guardar">
                            <i class="fa-solid fa-save me-2"></i>Confirmar Asignación
                        </button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentStudentId = null;
    let modalAsignar;

    document.addEventListener('DOMContentLoaded', () => {
        modalAsignar = new bootstrap.Modal(document.getElementById('modal-asignar-seccion'));

        // Revisar si viene un student_id en la URL
        const urlParams = new URLSearchParams(window.location.search);
        const studentId = urlParams.get('student_id');
        
        if (studentId) {
            document.getElementById('search-input').focus();
        }
    });

    async function buscarEstudiantes() {
        const query = document.getElementById('search-input').value.trim();
        const tbody = document.getElementById('table-students-body');
        const resultadosCard = document.getElementById('resultados-card');
        
        if (query.length < 2) {
            toastr.warning('Ingrese al menos 2 caracteres para buscar.');
            return;
        }

        // Mostrar estado de carga
        resultadosCard.style.display = 'block';
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4"><i class="fa-solid fa-spinner fa-spin text-qrlab me-2"></i> Buscando estudiantes...</td></tr>';

        try {
            const resp = await fetch(`/api/admin/system/students/search?q=${encodeURIComponent(query)}`);
            const data = await resp.json();
            
            tbody.innerHTML = '';
            
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">No se encontraron estudiantes con ese criterio.</td></tr>';
            } else {
                data.forEach(s => {
                    const row = `
                        <tr>
                            <td><span class="badge bg-light text-dark border">${s.user_code}</span></td>
                            <td class="fw-bold text-dark">${s.name}</td>
                            <td class="text-muted">${s.email}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" onclick="abrirModalSeccion(${s.id}, '${s.name.replace(/'/g, "\\'")}', '${s.user_code}')">
                                    <i class="fa-solid fa-hand-pointer me-1"></i> Asignar
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            }
        } catch (e) {
            console.error(e);
            toastr.error('Error al buscar estudiantes.');
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-danger">Error de conexión. Intente nuevamente.</td></tr>';
        }
    }

    function abrirModalSeccion(id, nombre, carnet) {
        currentStudentId = id;
        document.getElementById('modal-student-name').innerText = nombre;
        document.getElementById('modal-student-code').innerText = carnet;
        document.getElementById('select-section').value = ''; // Resetear el select
        modalAsignar.show();
    }

    async function guardarAsignacion() {
        const sectionId = document.getElementById('select-section').value;
        const btnGuardar = document.getElementById('btn-guardar');

        if (!currentStudentId || !sectionId) {
            toastr.warning('Seleccione una sección válida.');
            return;
        }

        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Guardando...';

        try {
            const resp = await fetch('/api/admin/system/instructors/assign', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ user_id: currentStudentId, section_id: sectionId })
            });

            const data = await resp.json();
            if (data.ok) {
                toastr.success(data.mensaje);
                modalAsignar.hide();
                setTimeout(() => {
                    window.location.href = "{{ route('admin.instructors') }}";
                }, 1000);
            } else {
                toastr.error(data.mensaje || 'Error al asignar');
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = '<i class="fa-solid fa-save me-2"></i>Confirmar Asignación';
            }
        } catch (e) {
            console.error(e);
            toastr.error('Error de red');
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="fa-solid fa-save me-2"></i>Confirmar Asignación';
        }
    }
</script>
@endpush
