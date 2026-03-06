<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR-LAB | Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/55416e97e6.js" crossorigin="anonymous"></script>
    <style>
        body { background-color: #f4f6f9; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .login-card { width: 100%; max-width: 400px; border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .brand-icon { font-size: 3.5rem; color: #0d6efd; }
        .input-group-text { border-right: none; background-color: #fff; }
        .form-control { border-left: none; }
        .form-control:focus { box-shadow: none; border-color: #dee2e6; }
    </style>
</head>
<body>

    <div class="container d-flex justify-content-center px-4">
        <div class="card login-card p-4 p-sm-5">
            <div class="text-center mb-4">
                <i class="fa-solid fa-qrcode brand-icon mb-3"></i>
                <h3 class="fw-bold mb-1">QR-LAB</h3>
                <p class="text-muted small">Sistema de Control de Asistencia</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show text-sm py-2" role="alert">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ $errors->first() }}
                    <button type="button" class="btn-close pb-2" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Correo Institucional</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-envelope text-muted"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="usuario@qrlab.com" value="{{ old('email') }}" required autofocus>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label small fw-bold text-secondary">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock text-muted"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold fs-5 shadow-sm">
                    Ingresar <i class="fa-solid fa-arrow-right ms-1"></i>
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>