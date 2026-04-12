<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR-LAB | Imprimir QR — {{ $lab->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <style>
        * {
            font-family: 'Inter', sans-serif;
            box-sizing: border-box;
            margin: 0; padding: 0;
        }

        body {
            background: #f4f6f9;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        /* === BARRA SUPERIOR (solo en pantalla, no imprime) === */
        .screen-bar {
            width: 100%;
            max-width: 600px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            gap: 1rem;
        }

        .btn-back {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.15s;
        }
        .btn-back:hover { background: #f1f5f9; color: #374151; }

        .btn-print {
            background: #6b1a2a;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1.25rem;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s;
        }
        .btn-print:hover { background: #52131f; }

        /* === TARJETA IMPRIMIBLE === */
        .print-card {
            background: #fff;
            width: 100%;
            max-width: 420px;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .card-header-print {
            background: linear-gradient(135deg, #6b1a2a 0%, #9b2d42 100%);
            color: #fff;
            text-align: center;
            padding: 1.75rem 1.5rem 1.25rem;
        }

        .card-header-print .logo-text {
            font-size: 1.1rem;
            font-weight: 900;
            letter-spacing: 1px;
            opacity: 0.9;
        }

        .card-header-print .lab-name {
            font-size: 1.6rem;
            font-weight: 700;
            margin-top: 0.35rem;
            line-height: 1.2;
        }

        .card-body-print {
            padding: 1.75rem 2rem;
            text-align: center;
        }

        #qr-code {
            display: flex;
            justify-content: center;
            margin: 0 auto 1.25rem;
        }

        #qr-code img,
        #qr-code canvas {
            border-radius: 10px;
            border: 6px solid #fff;
            box-shadow: 0 0 0 3px #6b1a2a;
        }

        .instructions {
            background: #fdf8f9;
            border-left: 4px solid #6b1a2a;
            border-radius: 0 10px 10px 0;
            padding: 0.85rem 1rem;
            text-align: left;
            margin-top: 1rem;
        }

        .instructions h4 {
            font-size: 0.8rem;
            font-weight: 700;
            color: #6b1a2a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .instructions ol {
            padding-left: 1.1rem;
            margin: 0;
        }

        .instructions li {
            font-size: 0.82rem;
            color: #374151;
            margin-bottom: 0.3rem;
            line-height: 1.4;
        }

        .card-footer-print {
            background: #6b1a2a;
            color: rgba(255,255,255,0.75);
            text-align: center;
            padding: 0.6rem;
            font-size: 0.72rem;
        }

        /* === SOLO IMPRESIÓN === */
        @media print {
            body { background: #fff; padding: 0; }
            .screen-bar { display: none !important; }
            .print-card {
                box-shadow: none;
                border-radius: 0;
                max-width: 100%;
                width: 100%;
            }
        }
    </style>
</head>
<body>

{{-- BARRA SUPERIOR (solo pantalla) --}}
<div class="screen-bar">
    <a href="/admin" class="btn-back">← Volver al admin</a>
    <span style="font-size:0.85rem; color:#64748b; font-weight:600;">
        Vista previa de impresión — {{ $lab->name }}
    </span>
    <button class="btn-print" onclick="window.print()">
        🖨️ Imprimir QR
    </button>
</div>

{{-- TARJETA IMPRIMIBLE --}}
<div class="print-card">

    <div class="card-header-print">
        <div class="logo-text">&#x25A0; QR-LAB · UTEC</div>
        <div class="lab-name">{{ $lab->name }}</div>
        <div style="font-size:0.8rem; opacity:0.75; margin-top:0.25rem;">
            Practicas libres — Escanea para registrar tu entrada/salida
        </div>
    </div>

    <div class="card-body-print">
        {{-- QR generado automáticamente por JS --}}
        <div id="qr-code"></div>

        <div class="instructions">
            <h4>📲 Instrucciones de uso</h4>
            <ol>
                <li><strong>1er escaneo</strong> Registra tu <strong>entrada</strong> al laboratorio.</li>
                <li><strong>2do escaneo</strong> Registra tu <strong>salida</strong> del laboratorio.</li>
                <li>Debes estar <strong>iniciado sesión</strong> en QR-LAB para que funcione.</li>
                <li>Si no marcas la salida, el sistema la cerrará automáticamente a las <strong>00:00 hrs</strong>.</li>
                <li>Recuerda si no marcas tus salidas pueden llamarte la atención.</li>
            </ol>
        </div>
    </div>

    <div class="card-footer-print">
        QR-LAB · UTEC 2026
    </div>
</div>

<script>
    // Generamos el QR con la URL completa del laboratorio
    new QRCode(document.getElementById("qr-code"), {
        text: "{{ $qrUrl }}",
        width: 300,
        height: 300,
        colorDark: "#6b1a2a",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });
</script>

</body>
</html>
