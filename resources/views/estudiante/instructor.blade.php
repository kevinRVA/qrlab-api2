@extends('layouts.app')

{{-- ===== META / HEAD ===== --}}
@section('title', 'QR-LAB | Panel Instructor')
@section('nav_icon', 'fa-qrcode')
@section('nav_subtitle', 'Instructor')
@section('user_icon', 'fa-user-tie')

{{-- CDN de QRCode.js --}}
@push('head_scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
@endpush

{{-- Estilos específicos del docente --}}
@push('styles')
    <style>
        #qr-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            background: white;
            border-radius: 10px;
            min-height: 250px;
        }

        .badge-clase {
            background: #0d6efd;
            color: #fff;
        }

        .badge-parcial {
            background: #fd7e14;
            color: #fff;
        }

        .badge-reposicion {
            background: #7c3aed;
            color: #fff;
        }

        .history-card .card-header {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        }
    </style>
@endpush

{{-- ===== CONTENIDO PRINCIPAL ===== --}}
@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">

            {{-- Formulario de configuración --}}
            <div class="col-md-5 mb-4">
                <div class="card shadow-sm h-100 p-4">
                    <h5 class="fw-bold mb-4 text-qrlab">
                        <i class="fa-solid fa-chalkboard-user"></i> Panel de Instructor
                    </h5>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Sección Asignada</label>
                        <select id="select-section" class="form-select">
                            <option value="">-- Selecciona tu clase --</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}">
                                    {{ $section->subject->name }} (Sec: {{ $section->section_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Laboratorio</label>
                        <select id="select-lab" class="form-select">
                            <option value="">-- Selecciona el laboratorio --</option>
                            @foreach($laboratories as $lab)
                                <option value="{{ $lab->name }}">{{ $lab->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Tipo de Clase</label>
                        <select id="select-tipo-clase" class="form-select">
                            <option value="Clase de Apoyo">&#128218; Clase de Apoyo</option>
                            <option value="Revisión de Parcial">&#128221; Revisión de Parcial</option>
                            <option value="Reposición">&#128260; Reposición</option>
                        </select>
                    </div>

                    <button id="btn-generar" class="btn btn-qrlab w-100 fw-bold py-2" onclick="generarQR()">
                        <i class="fa-solid fa-qrcode"></i> Generar Código QR
                    </button>

                    <button id="btn-finalizar" class="btn btn-danger w-100 fw-bold py-2 d-none" onclick="finalizarClase()">
                        <i class="fa-solid fa-stop"></i> Finalizar Clase
                    </button>
                </div>
            </div>

            {{-- Panel del QR --}}
            <div class="col-md-5 mb-4">
                <div class="card shadow-sm h-100 p-4 text-center">
                    <h5 class="fw-bold mb-3 text-dark">Código de Asistencia</h5>
                    <p class="small text-muted mb-4">
                        Los alumnos deben escanear este código con su celular para registrar su asistencia automáticamente.
                    </p>

                    <div id="qr-container" class="border">
                        <span class="text-muted small" id="qr-placeholder">El código aparecerá aquí</span>
                        <div id="qrcode"></div>
                    </div>

                    <a id="link-prueba" href="#" target="_blank" class="mt-3 small text-decoration-none d-none">
                        <i class="fa-solid fa-link"></i> Abrir link de prueba manualmente
                    </a>
                </div>
            </div>

        </div>

        {{-- Active Sessions Warning Box --}}
        @if(isset($activeSessions) && $activeSessions->count() > 0)
            <div class="row justify-content-center mt-3">
                <div class="col-md-10 col-lg-10 mb-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 border-warning fade-in bg-white"
                        style="border-left: 5px solid #ffca2c !important;">
                        <h5 class="fw-bold text-dark mb-2">
                            <i class="fa-solid fa-triangle-exclamation text-warning me-2"></i> Tienes sesiones abiertas
                        </h5>
                        <p class="small text-muted mb-4">
                            Las siguientes sesiones no fueron finalizadas. Ciérralas para invalidar los códigos QR de
                            asistencia.
                        </p>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-uppercase small fw-bold text-muted">Materia</th>
                                        <th class="text-uppercase small fw-bold text-muted">Laboratorio</th>
                                        <th class="text-uppercase small fw-bold text-muted">Fecha de creación</th>
                                        <th class="text-end text-uppercase small fw-bold text-muted">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activeSessions as $session)
                                        <tr id="row-session-{{ $session->id }}">
                                            <td class="fw-bold text-dark">{{ $session->section->subject->name }} <span
                                                    class="badge bg-light text-dark border ms-1">Sec:
                                                    {{ $session->section->section_code ?? '' }}</span></td>
                                            <td>{{ $session->laboratory_name }}</td>
                                            <td class="text-muted small">{{ $session->created_at->format('d/m/Y h:i A') }}</td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-outline-info me-1 fw-bold"
                                                    onclick="restaurarSesion({{ $session->id }}, '{{ $session->section_id }}', '{{ $session->laboratory_name }}', '{{ url('/asistencia/' . $session->qr_token) }}')">
                                                    <i class="fa-solid fa-qrcode"></i> Ver QR
                                                </button>
                                                <button class="btn btn-sm btn-danger custom-btn-close-session fw-bold shadow-sm"
                                                    onclick="cerrarSesionExterna({{ $session->id }}, this)">
                                                    <i class="fa-solid fa-stop"></i> Cerrar
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── Historial de últimas 10 clases ── --}}
        @if(isset($recentSessions) && $recentSessions->count() > 0)
            <div class="row justify-content-center mt-3 mb-5">
                <div class="col-md-10 col-lg-10">
                    <div class="card border-0 shadow-sm rounded-4 history-card">
                        <div
                            class="card-header py-4 bg-white border-0 rounded-top-4 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="fa-solid fa-clock-rotate-left text-qrlab me-2"></i> Historial de Clases Recientes
                            </h5>
                            <span class="badge bg-light text-dark border px-3 py-2">Últimas {{ $recentSessions->count() }}
                                clases</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4 text-uppercase small fw-bold text-muted">Fecha</th>
                                            <th class="text-uppercase small fw-bold text-muted">Materia / Sección</th>
                                            <th class="text-uppercase small fw-bold text-muted">Laboratorio</th>
                                            <th class="text-uppercase small fw-bold text-muted">Tipo</th>
                                            <th class="text-center text-uppercase small fw-bold text-muted">Alumnos</th>
                                            <th class="text-center pe-4 text-uppercase small fw-bold text-muted">Reporte</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentSessions as $rs)
                                            <tr>
                                                <td class="ps-4 small text-muted">{{ $rs->created_at->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    <span
                                                        class="fw-bold text-dark">{{ $rs->section->subject->name ?? 'N/A' }}</span>
                                                    <span class="badge bg-light text-dark border ms-1">Sec:
                                                        {{ $rs->section->section_code ?? '' }}</span>
                                                </td>
                                                <td><span class="text-muted">{{ $rs->laboratory_name ?? '—' }}</span></td>
                                                <td>
                                                    @php
                                                        $tipoClass = match ($rs->class_type) {
                                                            'Parcial', 'Revisión de Parcial' => 'badge-parcial',
                                                            'Reposicion', 'Reposición' => 'badge-reposicion',
                                                            default => 'badge-clase',
                                                        };
                                                        $tipoIcon = match ($rs->class_type) {
                                                            'Parcial', 'Revisión de Parcial' => '📝',
                                                            'Reposicion', 'Reposición' => '🔄',
                                                            default => '📘',
                                                        };
                                                    @endphp
                                                    <span class="badge rounded-pill px-3 py-2 {{ $tipoClass }}">{{ $tipoIcon }}
                                                        {{ $rs->class_type ?? 'Clase' }}</span>
                                                </td>
                                                <td class="text-center fs-5 fw-bold text-dark">{{ $rs->attendances_count }}</td>
                                                <td class="text-center pe-4">
                                                    @if($rs->attendances_count > 0)
                                                        <a href="{{ route('instructor.sesion.descargar', $rs->id) }}"
                                                            class="btn btn-sm btn-outline-success fw-bold px-3 rounded-pill"
                                                            title="Descargar lista CSV">
                                                            <i class="fa-solid fa-file-csv me-1"></i> CSV
                                                        </a>
                                                    @else
                                                        <span class="text-muted small fst-italic">Sin asistentes</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

{{-- ===== SCRIPTS ===== --}}
@push('scripts')
    <script>
        let currentSessionId = null;

        async function generarQR() {
            const sectionId = document.getElementById('select-section').value;
            const labName = document.getElementById('select-lab').value;
            const classType = document.getElementById('select-tipo-clase').value;
            const btnGenerar = document.getElementById('btn-generar');

            if (!sectionId || !labName) {
                toastr.warning('Por favor selecciona una materia y un laboratorio.');
                return;
            }

            btnGenerar.disabled = true;
            btnGenerar.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generando...';

            try {
                const response = await fetch('/estudiante/instructor/sesion', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ section_id: sectionId, laboratory_name: labName, class_type: classType })
                });

                const data = await response.json();

                if (data.success) {
                    currentSessionId = data.session_id;

                    document.getElementById('qr-placeholder').style.display = 'none';
                    document.getElementById('qrcode').innerHTML = '';

                    new QRCode(document.getElementById("qrcode"), {
                        text: data.qr_url,
                        width: 200, height: 200,
                        colorDark: "#000000", colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });

                    const linkPrueba = document.getElementById('link-prueba');
                    linkPrueba.href = data.qr_url;
                    linkPrueba.classList.remove('d-none');

                    btnGenerar.classList.add('d-none');
                    document.getElementById('btn-finalizar').classList.remove('d-none');

                    document.getElementById('select-section').disabled = true;
                    document.getElementById('select-lab').disabled = true;
                    document.getElementById('select-tipo-clase').disabled = true;

                    toastr.success('Clase iniciada correctamente.');
                } else {
                    toastr.error(data.message || 'Error al generar la sesión.');
                }
            } catch (error) {
                console.error('Error:', error);
                toastr.error('Hubo un error de red al generar la sesión.');
            } finally {
                if (btnGenerar && !btnGenerar.classList.contains('d-none')) {
                    btnGenerar.disabled = false;
                    btnGenerar.innerHTML = '<i class="fa-solid fa-qrcode"></i> Generar Código QR';
                }
            }
        }

        // Función para mostrar el QR de una clase olvidada
        function restaurarSesion(sessionId, sectionId, labName, qrUrl) {
            // Restaurar e inutilizar los selects
            document.getElementById('select-section').value = sectionId;
            document.getElementById('select-lab').value = labName;
            document.getElementById('select-section').disabled = true;
            document.getElementById('select-lab').disabled = true;
            document.getElementById('select-tipo-clase').disabled = true;

            currentSessionId = sessionId;

            // Dibujar el QR
            document.getElementById('qr-placeholder').style.display = 'none';
            document.getElementById('qrcode').innerHTML = '';

            new QRCode(document.getElementById("qrcode"), {
                text: qrUrl,
                width: 200, height: 200,
                colorDark: "#000000", colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });

            const linkPrueba = document.getElementById('link-prueba');
            linkPrueba.href = qrUrl;
            linkPrueba.classList.remove('d-none');

            // Mostrar boton de finalizar
            document.getElementById('btn-generar').classList.add('d-none');
            document.getElementById('btn-finalizar').classList.remove('d-none');

            // Scroll animado hacia el bloque superior
            window.scrollTo({ top: 0, behavior: 'smooth' });
            toastr.info('Sesión restaurada visualmente.');
        }

        async function finalizarClase() {
            if (!currentSessionId) return;

            const btnFinalizar = document.getElementById('btn-finalizar');
            btnFinalizar.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Finalizando...';
            btnFinalizar.disabled = true;

            try {
                const response = await fetch('/estudiante/instructor/sesion/finalizar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ session_id: currentSessionId })
                });

                const data = await response.json();

                if (data.success) {
                    toastr.success('La clase ha sido finalizada.');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error('Error al finalizar la clase.');
                    btnFinalizar.innerHTML = '<i class="fa-solid fa-stop"></i> Finalizar Clase';
                    btnFinalizar.disabled = false;
                }
            } catch (error) {
                console.error(error);
                toastr.error('Error de red al finalizar la clase.');
                btnFinalizar.innerHTML = '<i class="fa-solid fa-stop"></i> Finalizar Clase';
                btnFinalizar.disabled = false;
            }
        }

        // Funcionalidad para cerrar sesiones pasadas olvidadas
        async function cerrarSesionExterna(sessionId, btn) {
            if (!confirm("¿Seguro que deseas cerrar esta sesión?")) return;

            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Cerrando...';
            btn.disabled = true;

            try {
                const response = await fetch('/estudiante/instructor/sesion/finalizar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ session_id: sessionId })
                });

                const data = await response.json();

                if (data.success) {
                    toastr.success('Sesión cerrada correctamente.');
                    btn.closest('tr').classList.add('bg-light', 'text-muted');
                    setTimeout(() => {
                        btn.closest('tr').remove();
                        if (document.querySelectorAll('.custom-btn-close-session').length === 0) {
                            location.reload();
                        }
                    }, 500);
                } else {
                    toastr.error('No se pudo cerrar la sesión.');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch (error) {
                console.error(error);
                toastr.error('Error de conexión.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }
    </script>
@endpush