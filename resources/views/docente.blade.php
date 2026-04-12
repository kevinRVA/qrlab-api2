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
        body { background-color: #f4f6f9; display: flex; flex-direction: column; min-height: 100vh; }
        .card { border-radius: 15px; border: none; }
        #qr-container { display: flex; justify-content: center; align-items: center; padding: 20px; background: white; border-radius: 10px; min-height: 250px; }
        /* Color institucional QR-LAB */
        .btn-qrlab {
            background-color: #6b1a2a;
            color: #fff;
            border: none;
        }
        .btn-qrlab:hover, .btn-qrlab:focus {
            background-color: #52131f;
            color: #fff;
        }
        .text-qrlab { color: #6b1a2a !important; }
        /* Footer */
        .footer-qrlab {
            background-color: #6b1a2a;
            color: #fff;
            margin-top: auto;
        }
        .footer-qrlab a {
            color: rgba(255,255,255,0.65);
            text-decoration: none;
        }
        .footer-qrlab a:hover {
            color: #fff;
        }
        .footer-copyright {
            background-color: rgba(0,0,0,0.25);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark shadow-sm" style="background-color: #6b1a2a;">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold" href="#"><i class="fa-solid fa-qrcode"></i> QR-LAB Docente</a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3 small"><i class="fa-solid fa-user-tie"></i> {{ $teacher->name }}</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm" style="background-color: rgba(255,255,255,0.15); color:#fff; border:1px solid rgba(255,255,255,0.4);"><i class="fa-solid fa-right-from-bracket"></i> Salir</button>
            </form>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm h-100 p-4">
                <h5 class="fw-bold mb-4 text-qrlab"><i class="fa-solid fa-chalkboard-user"></i> Configurar Clase</h5>
                
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
    let currentSessionId = null; // Guardará el ID de la clase activa

    async function generarQR() {
        const sectionId = document.getElementById('select-section').value;
        const labName = document.getElementById('select-lab').value;
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
                currentSessionId = data.session_id; // Guardamos el ID

                document.getElementById('qr-placeholder').style.display = 'none';
                document.getElementById('qrcode').innerHTML = '';
                
                new QRCode(document.getElementById("qrcode"), {
                    text: data.qr_url,
                    width: 200, height: 200,
                    colorDark : "#000000", colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.H
                });

                const linkPrueba = document.getElementById('link-prueba');
                linkPrueba.href = data.qr_url;
                linkPrueba.classList.remove('d-none');

                // Ocultar botón de generar y mostrar el de finalizar
                btnGenerar.classList.add('d-none');
                document.getElementById('btn-finalizar').classList.remove('d-none');
                
                // Bloquear los selects para que no los cambie por accidente
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

    // NUEVA FUNCIÓN PARA FINALIZAR
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
                // Restaurar toda la interfaz a su estado original
                document.getElementById('qrcode').innerHTML = '';
                document.getElementById('qr-placeholder').style.display = 'block';
                document.getElementById('link-prueba').classList.add('d-none');
                
                document.getElementById('btn-generar').classList.remove('d-none');
                btnFinalizar.classList.add('d-none');

                document.getElementById('select-section').disabled = false;
                document.getElementById('select-lab').disabled = false;
                document.getElementById('select-section').value = '';
                document.getElementById('select-lab').value = '';

                currentSessionId = null;
                alert('La clase ha sido finalizada. El código QR ya no es válido.');
            }
        } catch (error) {
            console.error(error);
            alert('Error al finalizar la clase.');
        } finally {
            btnFinalizar.innerHTML = '<i class="fa-solid fa-stop"></i> Finalizar Clase';
            btnFinalizar.disabled = false;
        }
    }
</script>

<footer class="footer-qrlab mt-5 pt-5">
    <div class="container-fluid px-4 pb-4">
        <div class="row">
            <div class="col-md-4 mb-4 mb-md-0">
                <h5 class="fw-bold mb-2"><i class="fa-solid fa-qrcode me-2"></i>QR-LAB</h5>
                <p class="small" style="color:rgba(255,255,255,0.65)">
                    Educación y control de asistencia en la UTEC<br><br>
                    La Universidad Tecnológica de El Salvador (UTEC) es una institución comprometida
                    con la excelencia académica y la innovación tecnológica.
                </p>
            </div>
            <div class="col-md-2 mb-4 mb-md-0">
                <h6 class="fw-bold mb-3">Información</h6>
                <ul class="list-unstyled small">
                    <li class="mb-1"><a href="#">Portal Educativo</a></li>
                    <li class="mb-1"><a href="#">Biblioteca UTEC</a></li>
                    <li class="mb-1"><a href="#">Calendario Académico</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4 mb-md-0">
                <h6 class="fw-bold mb-3">Contáctanos</h6>
                <p class="small" style="color:rgba(255,255,255,0.65)">
                    <i class="fa-solid fa-location-dot me-1"></i> Dirección de Educación Virtual. Edificio José Martí 2do y 3er. nivel<br>
                    <i class="fa-solid fa-phone me-1"></i> Teléfono: (503) 2275 8888 ext: 8816, 8773, 8797, 8850<br>
                    <i class="fa-solid fa-envelope me-1"></i> utecvirtual@utec.edu.sv
                </p>
            </div>
            <div class="col-md-2">
                <h6 class="fw-bold mb-3">Síguenos</h6>
                <div class="d-flex gap-3 fs-4">
                    <a href="#"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="#"><i class="fa-brands fa-whatsapp"></i></a>
                    <a href="#"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-copyright text-center py-2 small" style="color:rgba(255,255,255,0.55)">
        Copyright &copy; 2026 - QR-LAB Sistema de Control de Asistencia
    </div>
</footer>

</body>
</html>