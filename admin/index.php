<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

// Incluir conexión si es necesario
require_once __DIR__ . '/../db/conexion.php';

// Obtener estadísticas reales de la base de datos
$stats = [
    'motos_registradas' => 0,
    'propietarios' => 0,
    'registros_activos' => 0,
    'total_recaudado' => 0
];

// Contar motos registradas
$result = $conn->query("SELECT COUNT(*) as total FROM motos");
if ($result) {
    $stats['motos_registradas'] = $result->fetch_assoc()['total'];
}

// Contar propietarios únicos (ignorando mayúsculas/minúsculas)
$result = $conn->query("SELECT COUNT(DISTINCT LOWER(NombrePropietario)) as total FROM motos WHERE NombrePropietario IS NOT NULL AND NombrePropietario != ''");
if ($result) {
    $stats['propietarios'] = $result->fetch_assoc()['total'];
}

// Contar registros activos
$result = $conn->query("SELECT COUNT(*) as total FROM registros WHERE Estado = 'activo'");
if ($result) {
    $stats['registros_activos'] = $result->fetch_assoc()['total'];
}

// Calcular total recaudado
$result = $conn->query("SELECT SUM(Monto) as total FROM pagos");
if ($result) {
    $total = $result->fetch_assoc()['total'];
    $stats['total_recaudado'] = $total ? number_format($total, 0, ',', '.') : '0';
}

// Obtener vista actual (por defecto dashboard)
$vista_actual = isset($_GET['vista']) ? $_GET['vista'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Parqueadero V.S</title>
    <link rel="stylesheet" href="../css/admin/index.css">
    <link rel="stylesheet" href="../css/admin/vistas/registrar_moto.css">
    <link rel="stylesheet" href="../css/admin/vistas/gestion_motos.css">
    <link rel="stylesheet" href="../css/admin/vistas/gestion_propietarios.css">
    <link rel="stylesheet" href="../css/admin/vistas/entrada_salida.css">
    <link rel="stylesheet" href="../css/admin/vistas/pagos.css">
    <link rel="stylesheet" href="../css/admin/vistas/tickets.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>🏍️ Parqueadero V.S</h1>
            <div class="user-info">
                👤 <?php echo htmlspecialchars($_SESSION['usuario'] ?? 'Usuario'); ?>
            </div>
        </div>
        
        <div class="sidebar-menu">
            <div class="menu-item <?php echo $vista_actual === 'dashboard' ? 'active' : ''; ?>" onclick="cambiarVista('dashboard')">
                <span>📊 Dashboard</span>
            </div>
            <div class="menu-item <?php echo $vista_actual === 'registrar_moto' ? 'active' : ''; ?>" onclick="cambiarVista('registrar_moto')">
                <span>🏍️ Registrar Moto/Propietario</span>
            </div>
            <div class="menu-item <?php echo $vista_actual === 'gestion_motos' ? 'active' : ''; ?>" onclick="cambiarVista('gestion_motos')">
                <span>📋 Gestionar Motos</span>
            </div>
            <div class="menu-item <?php echo $vista_actual === 'gestion_propietarios' ? 'active' : ''; ?>" onclick="cambiarVista('gestion_propietarios')">
                <span>👥 Gestionar Propietarios</span>
            </div>
            <div class="menu-item <?php echo $vista_actual === 'entrada_salida' ? 'active' : ''; ?>" onclick="cambiarVista('entrada_salida')">
                <span>🚪 Entrada/Salida</span>
            </div>
            <div class="menu-item <?php echo $vista_actual === 'pagos' ? 'active' : ''; ?>" onclick="cambiarVista('pagos')">
                <span>💰 Pagos</span>
            </div>
            <div class="menu-item <?php echo $vista_actual === 'tickets' ? 'active' : ''; ?>" onclick="cambiarVista('tickets')">
                <span>🎫 Tickets</span>
            </div>  
            <div class="menu-item <?php echo $vista_actual === 'exportar' ? 'active' : ''; ?>" onclick="cambiarVista('exportar')">
                <span>📤 Exportar Excel</span>
            </div>
        </div>

        <div class="logout-section">
            <a href="../controladores/logoutController.php" class="logout-btn">🚪 Cerrar Sesión</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <h2 id="titulo-vista">
                <?php
                $titulos = [
                    'dashboard' => '📊 Dashboard',
                    'registrar_moto' => '🏍️ Registrar Moto/Propietario',
                    'gestion_motos' => '📋 Gestionar Motos',
                    'gestion_propietarios' => '👥 Gestionar Propietarios',
                    'entrada_salida' => '🚪 Entrada/Salida',
                    'registros' => '📝 Registros Activos',
                    'pagos' => '💰 Pagos',
                    'tickets' => '🎫 Tickets',
                    'usuarios' => '👤 Gestionar Usuarios',
                    'exportar' => '📤 Exportar Excel'
                ];
                echo $titulos[$vista_actual] ?? 'Dashboard';
                ?>
            </h2>
        </div>

        <div class="content-area" id="content-area">
            <?php
            // Cargar la vista correspondiente
            switch($vista_actual) {
                case 'dashboard':
                    include 'vistas/dashboard.php';
                    break;
                case 'registrar_moto':
                    include 'vistas/registrar_moto.php';
                    break;
                case 'gestion_motos':
                    include 'vistas/gestion_motos.php';
                    break;
                case 'gestion_propietarios':
                    include 'vistas/gestion_propietarios.php';
                    break;
                case 'entrada_salida':
                    include 'vistas/entrada_salida.php';
                    break;
                case 'registros':
                    include 'vistas/registros.php';
                    break;
                case 'pagos':
                    include 'vistas/pagos.php';
                    break;
                case 'tickets':
                    include 'vistas/tickets.php';
                    break;
                case 'usuarios':
                    include 'vistas/usuarios.php';
                    break;
                case 'exportar':
                    include 'vistas/exportar.php';
                    break;
                default:
                    include 'vistas/dashboard.php';
            }
            ?>
        </div>
    </div>

<script src="../js/admin/index.js"></script>

</body>
</html>