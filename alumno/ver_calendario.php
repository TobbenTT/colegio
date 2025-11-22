<?php
session_start();
require '../config/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calendario Académico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
</head>
<body>

    <?php include '../includes/sidebar_alumno.php'; ?>

    <div class="main-content">
        <h3 class="fw-bold mb-4"><i class="bi bi-calendar-event"></i> Calendario Institucional</h3>
        
        <div class="card shadow border-0">
            <div class="card-body p-4">
                <div id='calendar'></div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es', // Español
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: '../includes/api_eventos.php', // AQUÍ CARGA LOS DATOS DE LA BD
                eventClick: function(info) {
                    alert('Evento: ' + info.event.title + '\n' + (info.event.extendedProps.descripcion || ''));
                }
            });
            calendar.render();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>