<?php
// config/db.php

$host = 'localhost';
$db   = 'colegio_bd'; // El nombre exacto de la base de datos que creamos
$user = 'root';       // Usuario por defecto de XAMPP
$pass = '';           // XAMPP suele traer la contraseña vacía por defecto
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Muestra errores reales si fallan consultas
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve datos como arrays asociativos
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Si quieres verificar que conecta, descomenta la linea de abajo:
    // echo "¡Conexión exitosa!"; 
} catch (\PDOException $e) {
    // Si falla, muestra el error y detiene todo
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>