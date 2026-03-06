<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR-LAB | Panel Docente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/55416e97e6.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        body { background-color: #f4f6f9; }
        .card { border-radius: 15px; border: none; }
        #qr-container { display: flex; justify-content: center; align-items: center; padding: 20px; background: white; border-radius: 10px; min-height: 250px; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-primary shadow-sm">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold" href="#"><i class="fa-solid fa-qrcode"></i> QR-LAB Docente</a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3 small"><i class="fa-solid fa-user-tie"></i> {{ $teacher->name }}</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm"><i class="fa-solid fa-right-from-bracket"></i> Salir</button>
            </form>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm h-100 p-4">
                <h5 class="fw-bold mb-4 text-primary"><i class="fa-solid fa-chalkboard-user"></i> Configurar Clase</h5>
                
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

                <button id="btn-generar" class="btn btn-primary w-100 fw-bold py-2" onclick="generarQR()">
                    <i class="fa-solid fa-qrcode"></i> Generar Código QR
                </button>
            </div>
        </div>

        <div class="col-md-5 mb-4">
            <div class="card shadow-sm h-100 p-4 text-center">
                <h5 class="fw-bold mb-3 text-dark">Código de Asistencia</h5>
                <p class="small text-muted mb-4">Los alumnos deben escanear este código con su celular para registrar su asistencia automáticamente.</p>
                
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
</div>

<script>
    async function generarQR() {
        const sectionId = document.getElementById('select-section').value;
        const labName = document.getElementById('select-lab').value;
        const btn = document.getElementById('btn-generar');

        if (!sectionId || !labName) {
            alert('Por favor selecciona una materia y un laboratorio.');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generando...';

        try {
            const response = await fetch('/docente/sesion', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // Seguridad de Laravel
                },
                body: JSON.stringify({ section_id: sectionId, laboratory_name: labName })
            });

            const data = await response.json();

            if (data.success) {
                // Limpiar QR anterior
                document.getElementById('qr-placeholder').style.display = 'none';
                document.getElementById('qrcode').innerHTML = '';
                
                // Dibujar el nuevo QR usando la URL COMPLETA
                new QRCode(document.getElementById("qrcode"), {
                    text: data.qr_url, // Esto ahora es algo como http://192.168.1.5:8000/asistencia/AbCdE
                    width: 200,
                    height: 200,
                    colorDark : "#000000",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.H
                });

                // Mostrar el link para que tú como admin lo puedas probar dándole clic
                const linkPrueba = document.getElementById('link-prueba');
                linkPrueba.href = data.qr_url;
                linkPrueba.classList.remove('d-none');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Hubo un error al generar la sesión.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-qrcode"></i> Generar Código QR';
        }
    }
</script>

</body>
</html>