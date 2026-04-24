<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'QR-LAB')</title>
    <meta name="description" content="@yield('meta_description', 'Sistema de Control de Asistencia - UTEC')">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- CDNs --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/55416e97e6.js" crossorigin="anonymous"></script>
    {{-- Toastr --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    {{-- CDNs opcionales por vista --}}
    @stack('head_scripts')

    <style>
        /* ===================================================
           VARIABLES DE MARCA UTEC / QR-LAB
        =================================================== */
        :root {
            --qr-primary:       #6b1a2a;
            --qr-primary-dark:  #52131f;
            --qr-primary-light: rgba(107, 26, 42, 0.08);
            --qr-accent:        #e8a0b4;
            --qr-bg:            #f4f6f9;
        }

        * { font-family: 'Inter', sans-serif; }

        body {
            background-color: var(--qr-bg);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ===== NAVBAR ===== */
        .navbar-qrlab {
            background-color: var(--qr-primary) !important;
        }

        /* ===== BOTONES ===== */
        .btn-qrlab {
            background-color: var(--qr-primary);
            color: #fff;
            border: none;
        }
        .btn-qrlab:hover,
        .btn-qrlab:focus {
            background-color: var(--qr-primary-dark);
            color: #fff;
        }

        /* ===== TEXTO Y UTILIDADES ===== */
        .text-qrlab  { color: var(--qr-primary) !important; }
        .border-qrlab { border-color: var(--qr-primary) !important; }

        /* ===== CARDS ===== */
        .card {
            border-radius: 10px;
            border: none;
        }
        .stat-card {
            border-left: 5px solid var(--qr-primary);
        }

        /* ===== FOOTER ===== */
        .footer-qrlab {
            background-color: var(--qr-primary);
            color: #fff;
            margin-top: auto;
        }
        .footer-qrlab a {
            color: rgba(255, 255, 255, 0.65);
            text-decoration: none;
        }
        .footer-qrlab a:hover { color: #fff; }
        .footer-copyright {
            background-color: rgba(0, 0, 0, 0.25);
        }

        /* ===== ANIMACIONES ===== */
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-in           { animation: fadeSlideUp 0.4s ease both; }
        .fade-in-delay-1   { animation-delay: 0.1s; }
        .fade-in-delay-2   { animation-delay: 0.2s; }
        .fade-in-delay-3   { animation-delay: 0.3s; }
    </style>

    {{-- Estilos adicionales por vista --}}
    @stack('styles')
</head>

<body>

{{-- ===== NAVBAR ===== --}}
<nav class="navbar navbar-dark shadow-sm navbar-qrlab">
    <div class="container-fluid px-4">
        {{-- Marca / Logo --}}
        <a class="navbar-brand" href="#">
            <i class="fa-solid @yield('nav_icon', 'fa-qrcode') me-1"></i>
            <strong>QR-LAB</strong>
            @hasSection('nav_subtitle')
                <span class="d-none d-md-inline text-white-50" style="font-size:0.85rem; font-weight:400;">
                    | @yield('nav_subtitle')
                </span>
            @endif
        </a>

        {{-- Derecha: usuario + cerrar sesión --}}
        <div class="d-flex align-items-center gap-2">
            {{-- Slot extra para botones adicionales (ej: "Actualizar" en admin) --}}
            @yield('nav_actions')

            @auth
                <span class="text-light small d-none d-md-inline">
                    <i class="fa-solid @yield('user_icon', 'fa-circle-user')" style="color: var(--qr-accent);"></i>
                    {{ Auth::user()->name }}
                </span>
                <a href="{{ route('logout') }}" class="btn btn-sm"
                    style="background-color: rgba(255,255,255,0.15); color:#fff; border:1px solid rgba(255,255,255,0.4);">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span class="d-none d-sm-inline"> Salir</span>
                </a>
            @endauth
        </div>
    </div>
</nav>

{{-- ===== CONTENIDO PRINCIPAL ===== --}}
<main class="flex-grow-1">
    @yield('content')
</main>

{{-- ===== FOOTER ===== --}}
<footer class="footer-qrlab mt-5 pt-5">
    <div class="container-fluid px-4 pb-4">
        <div class="row">
            <div class="col-md-4 mb-4 mb-md-0">
                <h5 class="fw-bold mb-2"><i class="fa-solid fa-qrcode me-2"></i>QR-LAB</h5>
                <p class="small" style="color:rgba(255,255,255,0.65)">
                    Control de asistencia en los laboratorios de la UTEC<br><br>
                    La Universidad Tecnológica de El Salvador (UTEC) es una institución comprometida
                    con la excelencia académica y la innovación tecnológica.
                </p>
            </div>
            <div class="col-md-2 mb-4 mb-md-0">
                <h6 class="fw-bold mb-3">Información</h6>
                <ul class="list-unstyled small">
                    <li class="mb-1"><a href="https://portal.utec.edu.sv/">Portal Educativo</a></li>
                    <li class="mb-1"><a href="https://biblioteca.utec.edu.sv/web/">Biblioteca UTEC</a></li>
                    <li class="mb-1"><a href="https://www.utec.edu.sv/Inicio/Catalogos-e-Instructivos/Calendario-Academico">Calendario Académico</a></li>
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
                    <a href="https://www.facebook.com/universidadtecnologica"><i class="fa-brands fa-facebook"></i></a>
                    <a href="https://twitter.com/utecedusv"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="https://api.whatsapp.com/send/?phone=50361000777&text&type=phone_number&app_absent=0"><i class="fa-brands fa-whatsapp"></i></a>
                    <a href="https://www.instagram.com/nuevoingresoutec/"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-copyright text-center py-2 small" style="color:rgba(255,255,255,0.55)">
        Copyright &copy; 2026 - QR-LAB Sistema de Control de Asistencia
    </div>
</footer>

{{-- Scripts de Bootstrap + jQuery + Toastr --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    // Configuración global de Toastr
    toastr.options = {
        closeButton:       true,
        progressBar:       true,
        positionClass:     'toast-top-right',
        timeOut:           4000,
        extendedTimeOut:   1500,
        preventDuplicates: true,
    };
</script>

{{-- Scripts adicionales por vista --}}
@stack('scripts')

{{-- Notificaciones Toastr específicas de la vista --}}
@stack('toastr')

@auth
<script>
    // Inactividad para cerrar sesión automáticamente
    (function() {
        let timeout;
        // Tiempo de expiración configurado (menos 1 minuto por seguridad)
        const timeoutMs = ({{ config('session.lifetime') ?: 120 }} * 60 * 1000) - 60000;
        
        function restartTimer() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                window.location.href = "{{ route('logout') }}";
            }, timeoutMs > 0 ? timeoutMs : 300000); // Mínimo 5 minutos por fallback
        }

        window.onload = restartTimer;
        document.onmousemove = restartTimer;
        document.onkeypress = restartTimer;
        document.ontouchstart = restartTimer;
        document.onclick = restartTimer;
        document.onscroll = restartTimer;
    })();
</script>
@endauth

</body>
</html>
