<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR-LAB | Dashboard Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/55416e97e6.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f4f6f9;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .card {
            border-radius: 10px;
            border: none;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f5f9;
        }

        .stat-card {
            border-left: 5px solid #6b1a2a;
        }

        .chart-container {
            position: relative;
            height: 280px;
            width: 100%;
        }

        /* Agrega esto a tus estilos */
        .cursor-pointer {
            cursor: pointer;
            user-select: none;
        }

        .cursor-pointer:hover {
            background-color: #e9ecef !important;
        }


        /* Navbar y footer color institucional */
        .navbar-qrlab {
            background-color: #6b1a2a !important;
        }
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
        <a class="navbar-brand" href="#">
            <i class="fa-solid fa-chart-pie"></i> <strong>QR-LAB</strong> | Panel de Administración
        </a>
        <div class="d-flex align-items-center">
            <span class="text-light me-3 small d-none d-md-inline">
                <i class="fa-solid fa-shield-halved" style="color:#e8a0b4;"></i> {{ Auth::user()->name }}
            </span>
            <button class="btn btn-outline-light btn-sm me-2" onclick="cargarDatos()">
                <i class="fa-solid fa-rotate-right"></i> <span class="d-none d-sm-inline">Actualizar</span>
            </button>
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-sm" style="background-color: rgba(255,255,255,0.15); color:#fff; border:1px solid rgba(255,255,255,0.4);">
                    <i class="fa-solid fa-right-from-bracket"></i> <span class="d-none d-sm-inline">Salir</span>
                </button>
            </form>
        </div>
    </div>
</nav>

    <div class="container-fluid mt-4 px-4 pb-5">

        <div class="row mb-4">
            <div class="col-md-9">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="text-muted"><i class="fa-solid fa-filter"></i> Filtros de Búsqueda</h6>
                        <div class="row g-2 mt-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Laboratorio</label>
                                <select id="filtro-lab" class="form-select form-select-sm" onchange="aplicarFiltros()">
                                    <option value="TODOS">Todos</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Docente</label>
                                <select id="filtro-docente" class="form-select form-select-sm"
                                    onchange="aplicarFiltros()">
                                    <option value="TODOS">Todos</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Asignatura</label>
                                <select id="filtro-materia" class="form-select form-select-sm"
                                    onchange="aplicarFiltros()">
                                    <option value="TODOS">Todas</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1">Fecha</label>
                                <input type="date" id="filtro-fecha" class="form-control form-control-sm"
                                    onchange="aplicarFiltros()">
                            </div>
                            <div class="col-md-1">
                                <button class="btn btn-danger btn-sm w-100" onclick="limpiarFiltros()"
                                    title="Limpiar Filtros">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm stat-card h-100">
                    <div class="card-body d-flex flex-column justify-content-center text-center">
                        <h6 class="text-muted mb-1">Total Alumnos (Filtrados)</h6>
                        <h2 class="text-qrlab fw-bold mb-2" id="total-alumnos-filtrados">0</h2>
                        <button class="btn btn-qrlab btn-sm" onclick="descargarExcelGlobal()">
                            <i class="fa-solid fa-file-excel"></i> Descargar Resumen
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 text-primary"><i class="fa-solid fa-computer"></i> Alumnos por Laboratorio</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="graficoLabs"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 text-success"><i class="fa-solid fa-book"></i> Uso por Asignatura</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="graficoMaterias"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 text-warning text-dark"><i class="fa-solid fa-chalkboard-user"></i> Top Docentes
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="graficoDocentes"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-dark"><i class="fa-solid fa-list"></i> Historial Detallado de Sesiones</h6>
                        <span class="badge bg-secondary" id="contador-sesiones">0 sesiones</span>
                    </div>
                    <div class="card-body p-0 table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover mb-0 text-sm align-middle">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="cursor-pointer" onclick="ordenarTabla('fecha')">
                                        Fecha <i class="fa-solid fa-sort text-muted ms-1" id="icono-fecha"></i>
                                    </th>
                                    <th class="cursor-pointer" onclick="ordenarTabla('laboratorio')">
                                        Laboratorio <i class="fa-solid fa-sort text-muted ms-1"
                                            id="icono-laboratorio"></i>
                                    </th>
                                    <th class="cursor-pointer" onclick="ordenarTabla('docente')">
                                        Docente <i class="fa-solid fa-sort text-muted ms-1" id="icono-docente"></i>
                                    </th>
                                    <th class="cursor-pointer" onclick="ordenarTabla('asignatura')">
                                        Asignatura <i class="fa-solid fa-sort text-muted ms-1"
                                            id="icono-asignatura"></i>
                                    </th>
                                    <th class="text-center cursor-pointer" onclick="ordenarTabla('alumnos')">
                                        Alumnos <i class="fa-solid fa-sort text-muted ms-1" id="icono-alumnos"></i>
                                    </th>
                                    <th class="text-center">Detalle</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-sesiones">
                                <tr>
                                    <td colspan="6" class="text-center py-4">Cargando datos...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- SECCIÓN: ACCESO VOLUNTARIO A LABORATORIOS                          --}}
    {{-- ================================================================== --}}
    <div class="container-fluid px-4 pb-5 mt-5" id="seccion-lab-acceso">

        {{-- Encabezado de sección --}}
        <div class="d-flex align-items-center gap-2 mb-3">
            <div style="width:5px; height:28px; background:#6b1a2a; border-radius:3px;"></div>
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fa-solid fa-door-open text-qrlab me-1"></i>
                Acceso Voluntario a Laboratorios
            </h5>
            <span class="badge ms-2" style="background:#6b1a2a;" id="badge-visitas-hoy">0 visitas hoy</span>
        </div>

        {{-- Filtros + botones de QR --}}
        <div class="row g-3 mb-3">
            <div class="col-md-8">
                <div class="card shadow-sm h-100">
                    <div class="card-body py-3">
                        <h6 class="text-muted mb-3"><i class="fa-solid fa-filter"></i> Filtros</h6>
                        <div class="row g-2 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label small mb-1">Laboratorio</label>
                                <select id="filtro-lab-visitas" class="form-select form-select-sm" onchange="cargarVisitas()">
                                    <option value="TODOS">Todos los laboratorios</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Fecha</label>
                                <input type="date" id="filtro-fecha-visitas" class="form-control form-control-sm"
                                    value="{{ date('Y-m-d') }}" onchange="cargarVisitas()">
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-sm btn-outline-secondary w-100" onclick="resetFiltrosVisitas()">
                                    <i class="fa-solid fa-rotate-left"></i> Hoy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body py-3">
                        <h6 class="text-muted mb-2"><i class="fa-solid fa-qrcode"></i> QR por Laboratorio</h6>
                        <div id="lista-qr-labs" style="max-height:100px; overflow-y:auto;">
                            <span class="text-muted small">Cargando labs...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla de visitas --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 text-dark">
                    <i class="fa-solid fa-clipboard-list text-qrlab"></i>
                    Registro de Visitas
                </h6>
            </div>
            <div class="card-body p-0 table-responsive" style="max-height:420px; overflow-y:auto;">
                <table class="table table-hover mb-0 align-middle" style="font-size:0.875rem;">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Carnet</th>
                            <th>Nombre</th>
                            <th>Laboratorio</th>
                            <th class="text-center">Entrada</th>
                            <th class="text-center">Salida</th>
                            <th class="text-center">Duración</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-visitas-lab">
                        <tr><td colspan="7" class="text-center py-4 text-muted">Cargando registros...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    {{-- FIN SECCIÓN LAB --}}

    <script>
        let todasLasSesiones = [];
        let sesionesFiltradas = [];

        // Variables para guardar los 3 gráficos y poder borrarlos/redibujarlos
        let chartLabs = null;
        let chartMaterias = null;
        let chartDocentes = null;

        // Variables para controlar el orden de la tabla
        let columnaOrdenActual = '';
        let ordenAscendente = true;

        document.addEventListener("DOMContentLoaded", () => {
            cargarDatos();
            cargarLabsYVisitas();  // === NUEVO: seción labs ===
        });

        // 1. Extraer datos
        async function cargarDatos() {
            try {
                const respuesta = await fetch('/api/admin/sesiones');
                todasLasSesiones = await respuesta.json();
                sesionesFiltradas = [...todasLasSesiones];

                llenarFiltrosDinamicos();
                actualizarUI();
            } catch (error) {
                console.error("Error al cargar:", error);
                document.getElementById('tabla-sesiones').innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error de conexión.</td></tr>';
            }
        }

        // 2. Llenar Filtros
        function llenarFiltrosDinamicos() {
            const labs = [...new Set(todasLasSesiones.map(s => s.laboratory_name || 'Sin Asignar'))].sort();
            const docentes = [...new Set(todasLasSesiones.map(s => s.teacher_name))].sort();
            const materias = [...new Set(todasLasSesiones.map(s => s.subject))].sort();

            const selLab = document.getElementById('filtro-lab');
            selLab.innerHTML = '<option value="TODOS">Todos</option>';
            labs.forEach(l => selLab.innerHTML += `<option value="${l}">${l}</option>`);

            const selDoc = document.getElementById('filtro-docente');
            selDoc.innerHTML = '<option value="TODOS">Todos</option>';
            docentes.forEach(d => selDoc.innerHTML += `<option value="${d}">${d}</option>`);

            const selMat = document.getElementById('filtro-materia');
            selMat.innerHTML = '<option value="TODOS">Todas</option>';
            materias.forEach(m => selMat.innerHTML += `<option value="${m}">${m}</option>`);
        }

        // 3. Aplicar Filtros
        function aplicarFiltros() {
            const labSel = document.getElementById('filtro-lab').value;
            const docSel = document.getElementById('filtro-docente').value;
            const matSel = document.getElementById('filtro-materia').value;
            const fechaSel = document.getElementById('filtro-fecha').value;

            sesionesFiltradas = todasLasSesiones.filter(sesion => {
                const matchLab = (labSel === "TODOS") || ((sesion.laboratory_name || 'Sin Asignar') === labSel);
                const matchDoc = (docSel === "TODOS") || (sesion.teacher_name === docSel);
                const matchMat = (matSel === "TODOS") || (sesion.subject === matSel);

                const fechaSesion = sesion.created_at.split('T')[0];
                const matchFecha = (fechaSel === "") || (fechaSesion === fechaSel);

                return matchLab && matchDoc && matchMat && matchFecha;
            });

            actualizarUI();
        }

        function limpiarFiltros() {
            document.getElementById('filtro-lab').value = "TODOS";
            document.getElementById('filtro-docente').value = "TODOS";
            document.getElementById('filtro-materia').value = "TODOS";
            document.getElementById('filtro-fecha').value = "";
            aplicarFiltros();
        }

        // 4. Actualizar todo
        function actualizarUI() {
            renderizarTabla();
            renderizarGraficos(); // Llama a la nueva función de los 3 gráficos

            const totalAlumnos = sesionesFiltradas.reduce((sum, s) => sum + s.attendances_count, 0);
            document.getElementById('total-alumnos-filtrados').innerText = totalAlumnos;
            document.getElementById('contador-sesiones').innerText = `${sesionesFiltradas.length} sesiones`;
        }

        // 5. Tabla
        function renderizarTabla() {
            const tbody = document.getElementById('tabla-sesiones');
            tbody.innerHTML = '';

            if (sesionesFiltradas.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No se encontraron clases.</td></tr>';
                return;
            }

            sesionesFiltradas.forEach(sesion => {
                const fecha = new Date(sesion.created_at).toLocaleDateString('es-ES', {
                    year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit'
                });

                const botonDescarga = sesion.is_active
                    ? `<span class="badge bg-warning text-dark"><i class="fa-solid fa-spinner fa-spin"></i> Activa</span>`
                    : `<a href="/api/admin/descargar-reporte/${sesion.id}" class="btn btn-sm btn-outline-primary" title="Descargar Lista"><i class="fa-solid fa-download"></i></a>`;

                tbody.innerHTML += `
                <tr>
                    <td class="small">${fecha}</td>
                    <td class="fw-bold text-dark">${sesion.laboratory_name || 'Sin Asignar'}</td>
                    <td>${sesion.teacher_name}</td>
                    <td class="text-primary">${sesion.subject} <span class="badge bg-light text-dark border">Sec: ${sesion.section}</span></td>
                    <td class="text-center fw-bold fs-6">${sesion.attendances_count}</td>
                    <td class="text-center">${botonDescarga}</td>
                </tr>
            `;
            });
        }

        // 6. DIBUJAR LOS 3 GRÁFICOS
        function renderizarGraficos() {
            // Obtenemos los 3 lienzos (canvas)
            const ctxLabs = document.getElementById('graficoLabs').getContext('2d');
            const ctxMaterias = document.getElementById('graficoMaterias').getContext('2d');
            const ctxDocentes = document.getElementById('graficoDocentes').getContext('2d');

            // Estructuras para agrupar
            const dataLabs = {};
            const dataMaterias = {};
            const dataDocentes = {};

            sesionesFiltradas.forEach(s => {
                let lab = s.laboratory_name || 'Sin Asignar';
                let mat = s.subject;
                let doc = s.teacher_name;

                // Sumamos alumnos a cada categoría
                dataLabs[lab] = (dataLabs[lab] || 0) + s.attendances_count;
                dataMaterias[mat] = (dataMaterias[mat] || 0) + s.attendances_count;
                dataDocentes[doc] = (dataDocentes[doc] || 0) + s.attendances_count;
            });

            // Paleta de colores
            const paletaFondo = [
                'rgba(13, 110, 253, 0.7)', 'rgba(25, 135, 84, 0.7)', 'rgba(255, 193, 7, 0.7)',
                'rgba(220, 53, 69, 0.7)', 'rgba(111, 66, 193, 0.7)', 'rgba(13, 202, 240, 0.7)',
                'rgba(253, 126, 20, 0.7)', 'rgba(214, 51, 132, 0.7)', 'rgba(32, 201, 151, 0.7)'
            ];
            const paletaBorde = paletaFondo.map(color => color.replace('0.7', '1'));

            // Destruimos si ya existían para evitar parpadeos y solapamientos
            if (chartLabs) chartLabs.destroy();
            if (chartMaterias) chartMaterias.destroy();
            if (chartDocentes) chartDocentes.destroy();

            // 📊 GRÁFICO 1: LABORATORIOS (Barras Verticales)
            const labelsLabs = Object.keys(dataLabs);
            chartLabs = new Chart(ctxLabs, {
                type: 'bar',
                data: {
                    labels: labelsLabs,
                    datasets: [{
                        data: Object.values(dataLabs),
                        backgroundColor: labelsLabs.map((_, i) => paletaFondo[i % paletaFondo.length]),
                        borderColor: labelsLabs.map((_, i) => paletaBorde[i % paletaBorde.length]),
                        borderWidth: 1, borderRadius: 5
                    }]
                },
                options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
            });

            // 🍩 GRÁFICO 2: MATERIAS (Dona)
            const labelsMaterias = Object.keys(dataMaterias);
            chartMaterias = new Chart(ctxMaterias, {
                type: 'doughnut',
                data: {
                    labels: labelsMaterias,
                    datasets: [{
                        data: Object.values(dataMaterias),
                        // Desfasamos los colores (+3) para que no se vea igual al de laboratorios
                        backgroundColor: labelsMaterias.map((_, i) => paletaFondo[(i + 3) % paletaFondo.length]),
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
            });

            // 🏆 GRÁFICO 3: DOCENTES (Barras Horizontales Top)
            // Ordenamos los docentes de mayor a menor asistencia
            const arrayDocentes = Object.entries(dataDocentes).sort((a, b) => b[1] - a[1]);
            const labelsDocentes = arrayDocentes.map(item => item[0]);
            const valoresDocentes = arrayDocentes.map(item => item[1]);

            chartDocentes = new Chart(ctxDocentes, {
                type: 'bar',
                data: {
                    labels: labelsDocentes,
                    datasets: [{
                        data: valoresDocentes,
                        backgroundColor: labelsDocentes.map((_, i) => paletaFondo[(i + 6) % paletaFondo.length]),
                        borderColor: labelsDocentes.map((_, i) => paletaBorde[(i + 6) % paletaBorde.length]),
                        borderWidth: 1, borderRadius: 5
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    indexAxis: 'y', // ESTO LO VUELVE HORIZONTAL
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true } }
                }
            });
        }

        // 7. Descargar Excel Global
        function descargarExcelGlobal() {
            if (sesionesFiltradas.length === 0) {
                alert("No hay datos para descargar con estos filtros."); return;
            }

            let csvContent = "\uFEFF";
            csvContent += "Fecha;Laboratorio;Docente;Asignatura;Seccion;Estado;Total Alumnos\n";

            sesionesFiltradas.forEach(s => {
                const fecha = new Date(s.created_at).toLocaleString('es-ES');
                const estado = s.is_active ? "En curso" : "Finalizada";
                const lab = s.laboratory_name || "Sin Asignar";
                csvContent += `"${fecha}";"${lab}";"${s.teacher_name}";"${s.subject}";"${s.section}";"${estado}";"${s.attendances_count}"\n`;
            });

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            link.setAttribute("href", URL.createObjectURL(blob));
            link.setAttribute("download", "Resumen_Filtrado_QRLAB.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        // --- NUEVAS FUNCIONES DE ORDENAMIENTO ---

        function ordenarTabla(columna) {
            // 1. Verificar si hacemos clic en la misma columna para invertir el orden
            if (columnaOrdenActual === columna) {
                ordenAscendente = !ordenAscendente;
            } else {
                columnaOrdenActual = columna;
                ordenAscendente = true; // Por defecto empezamos de menor a mayor / A-Z
            }

            // 2. Ordenar el arreglo filtrado
            sesionesFiltradas.sort((a, b) => {
                let valorA, valorB;

                // Extraemos los valores dependiendo de la columna seleccionada
                switch (columna) {
                    case 'fecha':
                        valorA = new Date(a.created_at).getTime();
                        valorB = new Date(b.created_at).getTime();
                        break;
                    case 'laboratorio':
                        valorA = (a.laboratory_name || 'Sin Asignar').toLowerCase();
                        valorB = (b.laboratory_name || 'Sin Asignar').toLowerCase();
                        break;
                    case 'docente':
                        valorA = a.teacher_name.toLowerCase();
                        valorB = b.teacher_name.toLowerCase();
                        break;
                    case 'asignatura':
                        valorA = a.subject.toLowerCase();
                        valorB = b.subject.toLowerCase();
                        break;
                    case 'alumnos':
                        valorA = a.attendances_count;
                        valorB = b.attendances_count;
                        break;
                    default:
                        return 0;
                }

                // Lógica de comparación
                if (valorA < valorB) return ordenAscendente ? -1 : 1;
                if (valorA > valorB) return ordenAscendente ? 1 : -1;
                return 0;
            });

            // 3. Cambiar los iconos (flechita arriba o abajo)
            actualizarIconosOrden(columna);

            // 4. Redibujar la tabla con el nuevo orden
            renderizarTabla();
        }

        function actualizarIconosOrden(columnaActiva) {
            const columnas = ['fecha', 'laboratorio', 'docente', 'asignatura', 'alumnos'];

            columnas.forEach(col => {
                const icono = document.getElementById(`icono-${col}`);
                if (icono) {
                    if (col === columnaActiva) {
                        // Si es la columna activa, ponemos flecha arriba o abajo en color primario
                        icono.className = ordenAscendente
                            ? 'fa-solid fa-sort-up text-primary ms-1'
                            : 'fa-solid fa-sort-down text-primary ms-1';
                    } else {
                        // Si no es la activa, la devolvemos a su estado neutral
                        icono.className = 'fa-solid fa-sort text-muted ms-1';
                    }
                }
            });
        }

        // ================================================================
        // JAVASCRIPT — SECCIÓN ACCESO VOLUNTARIO A LABORATORIOS
        // ================================================================

        let todosLosLabs = [];

        async function cargarLabsYVisitas() {
            try {
                const resp = await fetch('/api/admin/labs');
                todosLosLabs = await resp.json();

                // Llenar el select de filtro
                const sel = document.getElementById('filtro-lab-visitas');
                sel.innerHTML = '<option value="TODOS">Todos los laboratorios</option>';
                todosLosLabs.forEach(l => {
                    sel.innerHTML += `<option value="${l.id}">${l.name}</option>`;
                });

                // Llenar la lista de botones de QR
                const listaQr = document.getElementById('lista-qr-labs');
                if (todosLosLabs.length === 0) {
                    listaQr.innerHTML = '<span class="text-muted small">No hay laboratorios configurados.</span>';
                } else {
                    listaQr.innerHTML = todosLosLabs.map(l =>
                        l.qr_token
                        ? `<a href="${l.print_url}" target="_blank" class="badge me-1 mb-1 text-decoration-none"
                              style="background:#6b1a2a; color:#fff; padding:0.3rem 0.55rem; border-radius:6px; font-size:0.72rem;">
                              <i class="fa-solid fa-qrcode"></i> ${l.name}
                           </a>`
                        : `<span class="badge bg-secondary me-1 mb-1" style="font-size:0.72rem;">${l.name} (sin QR)</span>`
                    ).join('');
                }

                // Cargar las visitas de hoy
                await cargarVisitas();

            } catch (err) {
                console.error('Error al cargar labs:', err);
            }
        }

        async function cargarVisitas() {
            const labId   = document.getElementById('filtro-lab-visitas').value;
            const fecha   = document.getElementById('filtro-fecha-visitas').value;

            let url = '/api/admin/lab-visitas?';
            if (labId !== 'TODOS') url += `lab_id=${labId}&`;
            if (fecha) url += `fecha=${fecha}`;

            const tbody = document.getElementById('tabla-visitas-lab');
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-3 text-muted"><i class="fa-solid fa-spinner fa-spin me-1"></i>Cargando...</td></tr>';

            try {
                const resp = await fetch(url);
                const visitas = await resp.json();

                document.getElementById('badge-visitas-hoy').textContent = `${visitas.length} visita(s)`;

                if (visitas.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No hay registros para los filtros seleccionados.</td></tr>';
                    return;
                }

                tbody.innerHTML = visitas.map(v => {
                    // Badge de estado
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

                    const salida   = v.exit_time  || '<span class="text-muted">—</span>';
                    const duracion = v.duracion    || '<span class="text-muted">—</span>';

                    return `
                    <tr>
                        <td class="fw-bold" style="color:#6b1a2a;">${v.carnet}</td>
                        <td>${v.nombre}</td>
                        <td><span class="badge bg-light text-dark border" style="font-size:0.78rem;">${v.laboratorio}</span></td>
                        <td class="text-center fw-bold">${v.entry_time}</td>
                        <td class="text-center">${salida}</td>
                        <td class="text-center">${duracion}</td>
                        <td class="text-center">${estadoBadge}</td>
                    </tr>`;
                }).join('');

            } catch (err) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error al cargar los datos.</td></tr>';
                console.error(err);
            }
        }

        function resetFiltrosVisitas() {
            const hoy = new Date().toISOString().split('T')[0];
            document.getElementById('filtro-fecha-visitas').value = hoy;
            document.getElementById('filtro-lab-visitas').value   = 'TODOS';
            cargarVisitas();
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