@extends('layouts.app')

{{-- ===== META / HEAD ===== --}}
@section('title', 'QR-LAB | Panel Docente')
@section('nav_icon', 'fa-qrcode')
@section('nav_subtitle', 'Docente')
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
                    <i class="fa-solid fa-chalkboard-user"></i> Configurar Clase
                </h5>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Materia y Sección</label>
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
                    <label class="form-label small fw-bold text-muted">Laboratorio Asignado</label>
                    <select id="select-lab" class="form-select">
                        <option value="">-- Selecciona el laboratorio --</option>
                        @foreach($laboratories as $lab)
                            <option value="{{ $lab->name }}">{{ $lab->name }}</option>
                        @endforeach
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
    <div class="row justify-content-center mt-2">
        <div class="col-md-10 mb-4">
            <div class="card shadow-sm p-4 border-warning fade-in" style="border-left: 5px solid #ffca2c;">
                <h5 class="fw-bold text-dark mb-3">
                    <i class="fa-solid fa-triangle-exclamation text-warning"></i> Tienes sesiones abiertas
                </h5>
                <p class="small text-muted mb-3">
                    Las siguientes sesiones no fueron finalizadas. Ciérralas para invalidar los códigos QR de asistencia.
                </p>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Materia</th>
                                <th>Laboratorio</th>
                                <th>Fecha de creación</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeSessions as $session)
                            <tr id="row-session-{{ $session->id }}">
                                <td>{{ $session->section->subject->name }} (Sec: {{ $session->section->section_code ?? '' }})</td>
                                <td>{{ $session->laboratory_name }}</td>
                                <td>{{ $session->created_at->format('d/m/Y h:i A') }}</td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-info text-white me-1" onclick="restaurarSesion({{ $session->id }}, '{{ $session->section_id }}', '{{ $session->laboratory_name }}', '{{ url('/asistencia/' . $session->qr_token) }}')">
                                        <i class="fa-solid fa-qrcode"></i> Ver QR
                                    </button>
                                    <button class="btn btn-sm btn-danger custom-btn-close-session" onclick="cerrarSesionExterna({{ $session->id }}, this)">
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
</div>
@endsection

{{-- ===== SCRIPTS ===== --}}
@push('scripts')
<script>
    let currentSessionId = null;

    async function generarQR() {
        const sectionId = document.getElementById('select-section').value;
        const labName   = document.getElementById('select-lab').value;
        const btnGenerar = document.getElementById('btn-generar');

        if (!sectionId || !labName) {
            alert('Por favor selecciona una materia y un laboratorio.');
            return;
        }

        btnGenerar.disabled = true;
        btnGenerar.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generando...';

        try {
            const response = await fetch('/docente/sesion', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ section_id: sectionId, laboratory_name: labName })
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
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Hubo un error al generar la sesión.');
        } finally {
            btnGenerar.disabled = false;
            btnGenerar.innerHTML = '<i class="fa-solid fa-qrcode"></i> Generar Código QR';
        }
    }

    // Función para mostrar el QR de una clase olvidada
    function restaurarSesion(sessionId, sectionId, labName, qrUrl) {
        // Restaurar e inutilizar los selects
        document.getElementById('select-section').value = sectionId;
        document.getElementById('select-lab').value = labName;
        document.getElementById('select-section').disabled = true;
        document.getElementById('select-lab').disabled = true;
        
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
    }

    async function finalizarClase() {
        if (!currentSessionId) return;

        const btnFinalizar = document.getElementById('btn-finalizar');
        btnFinalizar.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Finalizando...';
        btnFinalizar.disabled = true;

        try {
            const response = await fetch('/docente/sesion/finalizar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ session_id: currentSessionId })
            });

            const data = await response.json();

            if (data.success) {
                alert('La clase ha sido finalizada. El código QR ya no es válido.');
                location.reload(); // Recargar la vista para que desaparezca de las sesiones abiertas limpiamente
            }
        } catch (error) {
            console.error(error);
            alert('Error al finalizar la clase.');
        } finally {
            btnFinalizar.innerHTML = '<i class="fa-solid fa-stop"></i> Finalizar Clase';
            btnFinalizar.disabled = false;
        }
    }

    // Funcionalidad para cerrar sesiones pasadas olvidadas
    async function cerrarSesionExterna(sessionId, btn) {
        if (!confirm("¿Seguro que deseas cerrar esta sesión?")) return;
        
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        btn.disabled = true;

        try {
            const response = await fetch('/docente/sesion/finalizar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ session_id: sessionId })
            });

            const data = await response.json();

            if (data.success) {
                btn.closest('tr').remove();
                if (document.querySelectorAll('.custom-btn-close-session').length === 0) {
                    location.reload();
                }
            } else {
                alert('No se pudo cerrar la sesión.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        } catch (error) {
            console.error(error);
            alert('Error de conexión.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
</script>
@endpush
