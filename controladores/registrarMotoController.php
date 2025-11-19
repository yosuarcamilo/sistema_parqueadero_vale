<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

// Este controlador ya no se usa ya que hemos movido la funcionalidad al motosController
// Lo mantenemos por compatibilidad pero redirige a la página principal
header('Location: ../admin/index.php?vista=registrar_moto');
exit();
?>