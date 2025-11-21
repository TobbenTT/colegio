<?php
session_start();
require '../config/db.php';
if ($_SESSION['rol'] != 'profesor') { header("Location: ../login.php"); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Escáner de Asistencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>const beep = new Audio('https://www.soundjay.com/button/beep-07.wav');</script>
</head>
<body>

    <?php include '../includes/sidebar_profesor.php'; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold"><i class="bi bi-qr-code-scan"></i> Asistencia Rápida (QR)</h3>
            <a href="dashboard.php" class="btn btn-secondary rounded-pill">Volver</a>
        </div>

        <div class="row">
            <div class="col-md-5">
                <div class="card shadow border-0">
                    <div class="card-body text-center">
                        <div id="reader" style="width: 100%;"></div>
                        <div class="mt-3 text-muted small">Apunta el código del alumno a la cámara</div>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card shadow border-0 h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-check-circle"></i> Alumnos Identificados</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-striped mb-0" id="tablaAsistencia">
                                <thead>
                                    <tr>
                                        <th>Hora</th>
                                        <th>Alumno</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="lista-resultados">
                                    </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Variable para evitar escaneos dobles muy rápidos
        let ultimoEscaneo = 0;

        function onScanSuccess(decodedText, decodedResult) {
            // Evitar leer el mismo código 2 veces en menos de 3 segundos
            const ahora = new Date().getTime();
            if (ahora - ultimoEscaneo < 3000) return;
            ultimoEscaneo = ahora;

            // Reproducir sonido
            beep.play();

            // Enviar a PHP
            registrarAsistencia(decodedText);
        }

        function registrarAsistencia(idAlumno) {
            let formData = new FormData();
            formData.append('id_alumno', idAlumno);
            
            // NOTA: En un sistema real, deberías pasar también el ID del Curso y Horario
            // Por simplicidad, asumiremos que marca presente en "Clase Actual"
            
            fetch('procesar_qr.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const lista = document.getElementById('lista-resultados');
                const hora = new Date().toLocaleTimeString();
                
                if(data.status === 'success') {
                    // Agregar fila verde
                    lista.innerHTML = `
                        <tr class="table-success animate__animated animate__fadeIn">
                            <td>${hora}</td>
                            <td class="fw-bold">${data.nombre}</td>
                            <td><span class="badge bg-success">PRESENTE</span></td>
                        </tr>` + lista.innerHTML;
                } else {
                    // Error (Rojo)
                    lista.innerHTML = `
                        <tr class="table-danger animate__animated animate__fadeIn">
                            <td>${hora}</td>
                            <td>ID: ${idAlumno}</td>
                            <td>Error: ${data.msg}</td>
                        </tr>` + lista.innerHTML;
                }
            })
            .catch(err => console.error(err));
        }

        // Iniciar Escáner
        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", 
            { fps: 10, qrbox: {width: 250, height: 250} },
            /* verbose= */ false);
        html5QrcodeScanner.render(onScanSuccess);
    </script>

</body>
</html>