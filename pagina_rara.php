<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Página no encontrada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; height: 100vh; text-align: center; }
        .error-code { font-size: 6rem; font-weight: 900; color: #dc3545; }
        .card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card p-5">
                    <div class="error-code">404</div>
                    <h2 class="fw-bold text-dark mb-3">¡Ups! Te perdiste</h2>
                    <p class="text-muted mb-4">Parece que la página que buscas no existe o fue movida a otra aula.</p>
                    
                    <div class="d-grid gap-2">
                        <a href="login.php" class="btn btn-primary btn-lg rounded-pill fw-bold">
                            <i class="bi bi-house-door-fill"></i> Volver al Inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>