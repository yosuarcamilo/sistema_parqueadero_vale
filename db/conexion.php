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
?>