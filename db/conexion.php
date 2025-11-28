<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'parqueadero_vale';

// Crear conexión
$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexión
if ($conn->connect_error) {
    die("Error al conectar a la base de datos: " . $conn->connect_error);
}

// Establecer la zona horaria de Colombia para la conexión MySQL
$conn->query("SET time_zone = '-05:00'");

// Establecer la zona horaria de Colombia para PHP
date_default_timezone_set('America/Bogota');
?>