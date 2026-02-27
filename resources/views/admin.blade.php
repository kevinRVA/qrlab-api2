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
        }

        .card {
            border-radius: 10px;
            border: none;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f5f9;
        }

        .stat-card {
            border-left: 5px solid #0d6efd;
        }

        .chart-container {
            position: relative;
            height: 280px;
            width: 100%;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="#">
                <i class="fa-solid fa-chart-pie"></i> <strong>QR-LAB</strong> | Panel de Administración
            </a>
            <button class="btn btn-outline-light btn-sm" onclick="cargarDatos()">
                <i class="fa-solid fa-rotate-right"></i> Actualizar Datos
            </button>
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
                        <h2 class="text-primary fw-bold mb-2" id="total-alumnos-filtrados">0</h2>
                        <button class="btn btn-success btn-sm" onclick="descargarExcelGlobal()">
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
                                    <th>Fecha</th>
                                    <th>Laboratorio</th>
                                    <th>Docente</th>
                                    <th>Asignatura</th>
                                    <th class="text-center">Alumnos</th>
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

    <script>
        let todasLasSesiones = [];
        let sesionesFiltradas = [];

        // Variables para guardar los 3 gráficos y poder borrarlos/redibujarlos
        let chartLabs = null;
        let chartMaterias = null;
        let chartDocentes = null;

        document.addEventListener("DOMContentLoaded", () => {
            cargarDatos();
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
    </script>

</body>

</html>