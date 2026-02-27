<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR-LAB | Portal Docente</title>
    <script src="https://kit.fontawesome.com/55416e97e6.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>

<body class="bg-light">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-danger text-white text-center">
                        <h4><i class="fa-solid"></i>QR-LAB | Iniciar Sesión</h4>
                    </div>
                    <div class="card-body">

                        <div id="seccion-formulario">
                            <div class="mb-3">
                                <label><i class="fa-solid fa-user"></i> Nombre del Docente</label>
                                <input type="text" id="teacher_name" class="form-control"
                                    placeholder="Ej. Kevin Vanegas">
                            </div>
                            <div class="mb-3">
                                <label><i class="fa-solid fa-user"></i> Código de Docente</label>
                                <input type="text" id="teacher_code" class="form-control" placeholder="Ej. DOC-7788">
                            </div>
                            <div class="mb-3">
                                <label>Laboratorio</label>
                                <select id="laboratory_name" class="form-control">
                                    <option value="">Seleccione el laboratorio...</option>
                                    @foreach($laboratorios as $lab)
                                        <option value="{{ $lab->name }}">{{ $lab->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label><i class="fa-solid fa-book"></i> Asignatura</label>
                                <input type="text" id="subject" class="form-control" placeholder="Ej. Programación II">
                            </div>
                            <div class="mb-3">
                                <label><i class="fa-solid fa-user"></i> Sección</label>
                                <input type="text" id="section" class="form-control" placeholder="Ej. 01">
                            </div>
                            <button class="btn btn-success w-100" onclick="iniciarClase()">Generar QR e Iniciar
                                Clase</button>
                        </div>

                        <div id="seccion-qr" class="text-center" style="display: none;">
                            <h5 class="text-success mb-3">¡Clase Iniciada!</h5>
                            <p>Pida a los estudiantes que escaneen este código:</p>

                            <div id="contenedor-qr" class="d-flex justify-content-center mb-4"></div>

                            <button class="btn btn-danger w-100" onclick="finalizarClase()">Finalizar Clase</button>
                        </div>

                        <div id="seccion-resultados" class="text-center" style="display: none;">
                            <h5 class="text-primary mb-3">Clase Finalizada</h5>
                            <p>Total de estudiantes registrados: <strong id="total-alumnos" class="fs-4">0</strong></p>

                            <a id="btn-descargar" href="#" class="btn btn-warning w-100 mb-3"><i
                                    class="fa-solid fa-arrow-down-to-bracket"></i> Descargar Reporte
                                (Excel)</a>

                            <button class="btn btn-success w-100" onclick="location.reload()">Iniciar Nueva
                                Clase</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let tokenActual = ''; // Aquí guardaremos el token temporalmente

        // Función 1: Enviar datos a la API y mostrar el QR
        async function iniciarClase() {
            const datos = {
                teacher_name: document.getElementById('teacher_name').value,
                teacher_code: document.getElementById('teacher_code').value,
                subject: document.getElementById('subject').value,
                section: document.getElementById('section').value,
                // Agregamos el laboratorio que seleccionó el profe:
                laboratory_name: document.getElementById('laboratory_name').value
            };

            try {
                const respuesta = await fetch('/api/docente/iniciar-clase', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(datos)
                });

                const resultado = await respuesta.json();

                if (respuesta.ok) {
                    tokenActual = resultado.qr_token;

                    // Ocultamos formulario, mostramos QR
                    document.getElementById('seccion-formulario').style.display = 'none';
                    document.getElementById('seccion-qr').style.display = 'block';

                    // Dibujamos el QR con el token recibido
                    new QRCode(document.getElementById("contenedor-qr"), {
                        text: tokenActual,
                        width: 250,
                        height: 250
                    });
                } else {
                    alert("Error al iniciar clase. Verifica los campos.");
                }
            } catch (error) {
                console.error("Error:", error);
            }
        }

        // Función 2: Cerrar la clase y ver total de alumnos
        async function finalizarClase() {
            try {
                const respuesta = await fetch('/api/docente/finalizar-clase/' + tokenActual, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' }
                });

                const resultado = await respuesta.json();

                if (respuesta.ok) {
                    document.getElementById('seccion-qr').style.display = 'none';
                    document.getElementById('seccion-resultados').style.display = 'block';
                    document.getElementById('total-alumnos').innerText = resultado.total_students;

                    // NUEVO: Le damos la URL de descarga al botón
                    document.getElementById('btn-descargar').href = resultado.download_url;
                }
            } catch (error) {
                console.error("Error:", error);
            }

        }
    </script>

</body>

</html>