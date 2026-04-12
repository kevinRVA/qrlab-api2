<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR-LAB | Acceso a Laboratorio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/55416e97e6.js" crossorigin="anonymous"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            background: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .result-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 20px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
        }

        @keyframes popIn {
            from { opacity: 0; transform: scale(0.92) translateY(20px); }
            to   { opacity: 1; transform: scale(1)    translateY(0);     }
        }

        /* === ENTRADA (verde) === */
        .card-entrada .card-top {
            background: linear-gradient(135deg, #16a34a, #22c55e);
        }

        /* === SALIDA (azul) === */
        .card-salida .card-top {
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
        }

        /* === ERROR (rojo guinda) === */
        .card-error .card-top {
            background: linear-gradient(135deg, #6b1a2a, #9b2d42);
        }

        .card-top {
            padding: 2rem 1.5rem;
            text-align: center;
            color: #fff;
        }

        .card-top .main-icon {
            font-size: 3.5rem;
            margin-bottom: 0.75rem;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
        }

        .card-top h2 {
            font-size: 1.3rem;
            font-weight: 700;
            margin: 0 0 0.25rem;
        }

        .card-top .subtitle {
            font-size: 0.85rem;
            opacity: 0.85;
            margin: 0;
        }

        .card-body-custom {
            background: #fff;
            padding: 1.5rem;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .info-row:last-of-type { border-bottom: none; }

        .info-row .info-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .info-row .info-label { font-size: 0.72rem; color: #94a3b8; text-transform: uppercase; font-weight: 600; }
        .info-row .info-value { font-size: 0.9rem; font-weight: 600; color: #1a202c; }

        .duration-pill {
            background: #dbeafe;
            color: #1d4ed8;
            border-radius: 20px;
            padding: 0.2rem 0.7rem;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .btn-perfil {
            background-color: #6b1a2a;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 0.65rem 1rem;
            font-weight: 600;
            width: 100%;
            transition: background 0.2s;
        }
        .btn-perfil:hover { background-color: #52131f; color: #fff; }

        .branding {
            text-align: center;
            margin-top: 1.25rem;
            font-size: 0.75rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>

<div class="result-card
    @if($tipo === 'entrada') card-entrada
    @elseif($tipo === 'salida') card-salida
    @else card-error @endif">

    {{-- ===== TOP ===== --}}
    <div class="card-top">
        @if($tipo === 'entrada')
            <div class="main-icon"><i class="fa-solid fa-right-to-bracket"></i></div>
            <h2>¡Entrada Registrada!</h2>
            <p class="subtitle">{{ $laboratorio ?? '' }}</p>
        @elseif($tipo === 'salida')
            <div class="main-icon"><i class="fa-solid fa-right-from-bracket"></i></div>
            <h2>¡Salida Registrada!</h2>
            <p class="subtitle">{{ $laboratorio ?? '' }}</p>
        @else
            <div class="main-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <h2>Atención</h2>
            <p class="subtitle">No se pudo registrar el acceso</p>
        @endif
    </div>

    {{-- ===== BODY ===== --}}
    <div class="card-body-custom">

        @if($tipo === 'entrada')
            <div class="info-row">
                <div class="info-icon" style="background:#dcfce7; color:#16a34a;">
                    <i class="fa-regular fa-clock"></i>
                </div>
                <div>
                    <div class="info-label">Hora de entrada</div>
                    <div class="info-value">{{ $hora ?? '--:--' }}</div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-icon" style="background:#fef9c3; color:#ca8a04;">
                    <i class="fa-solid fa-circle-info"></i>
                </div>
                <div>
                    <div class="info-label">Recordatorio</div>
                    <div class="info-value" style="font-size:0.82rem; color:#64748b;">
                        Escanea de nuevo el QR al salir del laboratorio.
                    </div>
                </div>
            </div>

        @elseif($tipo === 'salida')
            <div class="info-row">
                <div class="info-icon" style="background:#dbeafe; color:#1d4ed8;">
                    <i class="fa-regular fa-clock"></i>
                </div>
                <div>
                    <div class="info-label">Hora de salida</div>
                    <div class="info-value">{{ $hora ?? '--:--' }}</div>
                </div>
            </div>
            @if(isset($duracion))
            <div class="info-row">
                <div class="info-icon" style="background:#ede9fe; color:#7c3aed;">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
                <div>
                    <div class="info-label">Tiempo en el laboratorio</div>
                    <div class="info-value">
                        <span class="duration-pill">{{ $duracion }}</span>
                    </div>
                </div>
            </div>
            @endif

        @else
            <div class="info-row">
                <div class="info-icon" style="background:#fee2e2; color:#dc2626;">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>
                <div>
                    <div class="info-label">Motivo</div>
                    <div class="info-value" style="font-size:0.85rem; color:#64748b;">{{ $mensaje }}</div>
                </div>
            </div>
        @endif

        <div class="mt-3">
            <a href="/perfil" class="btn btn-perfil">
                <i class="fa-solid fa-user me-1"></i> Ver mi perfil
            </a>
        </div>

        <form action="{{ route('logout') }}" method="POST" class="mt-2">
            @csrf
            <button type="submit" class="btn btn-link text-muted small w-100 text-decoration-none">
                Cerrar sesión
            </button>
        </form>
    </div>
</div>

<div class="branding">
    <i class="fa-solid fa-qrcode me-1"></i> QR-LAB · UTEC
</div>

</body>
</html>
