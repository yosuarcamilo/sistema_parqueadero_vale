<?php
session_start();
require_once __DIR__ . '/../db/conexion.php';

// Definir la variable mensaje
$mensaje = '';

// Procesar el formulario de inicio de sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre_usuario']) && isset($_POST['contraseña'])) {
    $usuario = trim($_POST['nombre_usuario']);
    $password = trim($_POST['contraseña']);

    // Consulta para buscar el usuario usando los nombres de campos de la base de datos
    $stmt = $conn->prepare("SELECT Id_usuario, Nombre_usuario, contraseña FROM usuario WHERE Nombre_usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Verificar la contraseña (asumiendo que está hasheada con password_hash)
        if (password_verify($password, $user['contraseña'])) {
            // Autenticación exitosa
            $_SESSION['logged_in'] = true;
            $_SESSION['usuario_id'] = $user['Id_usuario'];
            $_SESSION['usuario'] = $user['Nombre_usuario'];
            header('Location: /parqueadero_vale/admin/index.php');
            exit();
        } else {
            $_SESSION['mensaje_error'] = 'Usuario o contraseña incorrectos.';
            header('Location: /parqueadero_vale/index.php');
            exit();
        }
    } else {
        $_SESSION['mensaje_error'] = 'Usuario o contraseña incorrectos.';
        header('Location: /parqueadero_vale/index.php');
        exit();
    }
    $stmt->close();
}

// Verificar si el usuario ya está logueado
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: /parqueadero_vale/admin/index.php');
    exit();
}
?>