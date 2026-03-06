<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR-LAB | Asistencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/55416e97e6.js" crossorigin="anonymous"></script>
    <style>
        body { background-color: #f4f6f9; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .result-card { width: 100%; max-width: 400px; border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; }
        .icon-success { font-size: 5rem; color: #198754; }
        .icon-error { font-size: 5rem; color: #dc3545; }
        .bg-success-subtle { background-color: #d1e7dd !important; }
        .bg-danger-subtle { background-color: #f8d7da !important; }
    </style>
</head>
<body>

    <div class="container d-flex justify-content-center px-4">
        <div class="card result-card p-4 p-sm-5">
            
            @if($tipo == 'exito')
                <div class="mb-4">
                    <i class="fa-solid fa-circle-check icon-success"></i>
                </div>
                <h3 class="fw-bold text-success mb-2">¡Asistencia Exitosa!</h3>
                <p class="text-muted">{{ $mensaje }}</p>
                
                @if(isset($clase))
                    <div class="card bg-success-subtle border-0 rounded-3 p-3 mb-4">
                        <span class="small fw-bold text-success text-uppercase tracking-wider">Materia Registrada</span>
                        <h5 class="mb-0 mt-1 text-dark"><i class="fa-solid fa-book-open"></i> {{ $clase }}</h5>
                    </div>
                @endif
                
            @else
                <div class="mb-4">
                    <i class="fa-solid fa-circle-xmark icon-error"></i>
                </div>
                <h3 class="fw-bold text-danger mb-2">Atención</h3>
                <p class="text-muted mb-4">{{ $mensaje }}</p>
            @endif

            <hr class="text-muted opacity-25 mb-4">

            <a href="/perfil" class="btn btn-outline-secondary w-100 fw-bold">
                <i class="fa-solid fa-user"></i> Ver mi perfil
            </a>
            
            <form action="{{ route('logout') }}" method="POST" class="mt-2">
                @csrf
                <button type="submit" class="btn btn-link text-muted small w-100 text-decoration-none">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </div>

</body>
</html>