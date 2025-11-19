<?php
session_start();
require_once __DIR__ . '/../db/conexion.php';

// Procesar el formulario de recuperación de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_password'])) {
    $usuario = trim($_POST['nombre_usuario']);
    $nueva_password = trim($_POST['nueva_contraseña']);
    $confirmar_password = trim($_POST['confirmar_contraseña']);

    // Validar que las contraseñas coincidan
    if ($nueva_password !== $confirmar_password) {
        $_SESSION['mensaje_error'] = 'Las contraseñas no coinciden.';
        header('Location: ../admin/vistas/recuperar_contraseña.php');
        exit();
    }

    // Validar longitud mínima de contraseña
    if (strlen($nueva_password) < 6) {
        $_SESSION['mensaje_error'] = 'La contraseña debe tener al menos 6 caracteres.';
        header('Location: ../admin/vistas/recuperar_contraseña.php');
        exit();
    }

    // Buscar el usuario en la base de datos
    $stmt = $conn->prepare("SELECT Id_usuario, Nombre_usuario FROM usuario WHERE Nombre_usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Usuario existe, actualizar la contraseña
        $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE usuario SET contraseña = ? WHERE Id_usuario = ?");
        $update_stmt->bind_param("si", $password_hash, $user['Id_usuario']);
        
        if ($update_stmt->execute()) {
            $_SESSION['mensaje_exito'] = 'Contraseña actualizada exitosamente. Ahora puedes iniciar sesión con tu nueva contraseña.';
            header('Location: ../admin/vistas/recuperar_contraseña.php');
            exit();
        } else {
            $_SESSION['mensaje_error'] = 'Error al actualizar la contraseña. Intente nuevamente.';
            header('Location: ../admin/vistas/recuperar_contraseña.php');
            exit();
        }
        $update_stmt->close();
    } else {
        // Usuario no existe
        $_SESSION['mensaje_error'] = 'El usuario no existe en el sistema.';
        header('Location: ../admin/vistas/recuperar_contraseña.php');
        exit();
    }
    $stmt->close();
}
?>

