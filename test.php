<?php
require 'config/db.php';

// Vamos a intentar leer un usuario de la base de datos
$stmt = $pdo->query("SELECT nombre, rol FROM usuarios LIMIT 1");
$usuario = $stmt->fetch();

echo "<h1>Prueba de Conexión</h1>";
echo "Si ves un nombre abajo, la conexión funciona:<br>";
echo "Nombre: " . $usuario['nombre'] . " - Rol: " . $usuario['rol'];
?>