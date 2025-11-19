<?php
// Incluir conexión a la base de datos
require_once __DIR__ . '/../../db/conexion.php';

// Parámetros de búsqueda y paginación
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Construir filtro de búsqueda
$where = '';
$params = [];
$types = '';
if ($q !== '') {
    $qLike = '%' . $q . '%';
    $where = "WHERE p.Monto LIKE ? OR m.Placa LIKE ? OR m.NombrePropietario LIKE ? OR p.MetodoPago LIKE ?";
    $params = [$qLike, $qLike, $qLike, $qLike];
    $types = 'ssss';
}

// Contar total de registros
$total = 0;
$countSql = "SELECT COUNT(*) as total 
             FROM pagos p
             JOIN registros r ON p.IdRegistro = r.IdRegistro
             JOIN motos m ON r.IdMoto = m.IdMoto
             $where";
if ($types !== '') {
    $stmt = $conn->prepare($countSql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($countSql);
}
if ($result) {
    $row = $result->fetch_assoc();
    $total = (int)$row['total'];
}
$totalPages = max(1, (int)ceil($total / $perPage));

// Consultar pagos con información relacionada
$pagos = [];
$sql = "SELECT p.IdPago, p.Monto, p.FechaHoraPago, p.MetodoPago,
               m.IdMoto, m.Placa, m.Marca, m.Modelo,
               m.NombrePropietario as Propietario,
               r.FechaHoraEntrada, r.FechaHoraSalida
        FROM pagos p
        JOIN registros r ON p.IdRegistro = r.IdRegistro
        JOIN motos m ON r.IdMoto = m.IdMoto
        $where
        ORDER BY p.FechaHoraPago DESC
        LIMIT $perPage OFFSET $offset";

if ($types !== '') {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pagos[] = $row;
    }
}

// Calcular totales
$totalMonto = 0;
$totalesPorMetodo = [
    'efectivo' => 0,
    'tarjeta' => 0,
    'transferencia' => 0,
    'otro' => 0
];

$totalSql = "SELECT SUM(p.Monto) as total_monto, p.MetodoPago
             FROM pagos p
             JOIN registros r ON p.IdRegistro = r.IdRegistro
             JOIN motos m ON r.IdMoto = m.IdMoto
             $where
             GROUP BY p.MetodoPago";

if ($types !== '') {
    $stmt = $conn->prepare($totalSql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $totalResult = $stmt->get_result();
} else {
    $totalResult = $conn->query($totalSql);
}

if ($totalResult) {
    while ($row = $totalResult->fetch_assoc()) {
        $totalMonto += $row['total_monto'];
        $totalesPorMetodo[$row['MetodoPago']] = $row['total_monto'];
    }
}

// Verificar si se solicitó ver el historial de una moto
$historialMoto = null;
if (isset($_GET['ver_historial']) && !empty($_GET['id_moto'])) {
    $idMoto = (int)$_GET['id_moto'];
    
    // Obtener información de la moto
    $motoSql = "SELECT IdMoto, Placa, Marca, Modelo, NombrePropietario, TelefonoPropietario, DireccionPropietario 
                FROM motos WHERE IdMoto = ?";
    $stmt = $conn->prepare($motoSql);
    $stmt->bind_param('i', $idMoto);
    $stmt->execute();
    $motoResult = $stmt->get_result();
    $motoInfo = $motoResult->fetch_assoc();
    
    if ($motoInfo) {
        // Obtener historial de registros y pagos de la moto
        $historialSql = "SELECT r.IdRegistro, r.FechaHoraEntrada, r.FechaHoraSalida, r.Estado,
                                p.IdPago, p.Monto, p.FechaHoraPago, p.MetodoPago
                         FROM registros r
                         LEFT JOIN pagos p ON r.IdRegistro = p.IdRegistro
                         WHERE r.IdMoto = ?
                         ORDER BY r.FechaHoraEntrada DESC";
        $stmt = $conn->prepare($historialSql);
        $stmt->bind_param('i', $idMoto);
        $stmt->execute();
        $historialResult = $stmt->get_result();
        
        $historialMoto = [
            'info' => $motoInfo,
            'registros' => []
        ];
        
        while ($row = $historialResult->fetch_assoc()) {
            $historialMoto['registros'][] = $row;
        }
    }
}
?>


<?php if (isset($_SESSION['mensaje_exito'])): ?>
    <div class="msg success">✓ <?php echo htmlspecialchars($_SESSION['mensaje_exito']); unset($_SESSION['mensaje_exito']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['mensaje_error'])): ?>
    <div class="msg error">✗ <?php echo htmlspecialchars($_SESSION['mensaje_error']); unset($_SESSION['mensaje_error']); ?></div>
<?php endif; ?>

<?php if ($historialMoto): ?>
    <!-- Modal de historial -->
    <div class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3>📋 Historial de Moto</h3>
                <button class="btn btn-secondary" onclick="window.location.href='?vista=pagos&page=<?php echo $page; ?>&q=<?php echo urlencode($q); ?>'">Cerrar</button>
            </div>
            
            <div class="historial-info">
                <div class="historial-item">
                    <div class="historial-label">Placa</div>
                    <div class="historial-value"><?php echo htmlspecialchars($historialMoto['info']['Placa']); ?></div>
                </div>
                <div class="historial-item">
                    <div class="historial-label">Marca/Modelo</div>
                    <div class="historial-value"><?php echo htmlspecialchars($historialMoto['info']['Marca'] . ' ' . $historialMoto['info']['Modelo']); ?></div>
                </div>
                <div class="historial-item">
                    <div class="historial-label">Propietario</div>
                    <div class="historial-value"><?php echo htmlspecialchars($historialMoto['info']['NombrePropietario']); ?></div>
                </div>
                <div class="historial-item">
                    <div class="historial-label">Teléfono</div>
                    <div class="historial-value"><?php echo htmlspecialchars($historialMoto['info']['TelefonoPropietario'] ?? 'N/A'); ?></div>
                </div>
            </div>
            
            <h4>Registro de Actividad</h4>
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID Registro</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Estado</th>
                            <th>Pago</th>
                            <th>Monto</th>
                            <th>Método</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($historialMoto['registros'])): ?>
                            <tr>
                                <td colspan="7" style="text-align:center; color:#888;">No hay registros para esta moto.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($historialMoto['registros'] as $registro): ?>
                                <tr>
                                    <td><?php echo $registro['IdRegistro']; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($registro['FechaHoraEntrada'])); ?></td>
                                    <td><?php echo $registro['FechaHoraSalida'] ? date('d/m/Y H:i', strtotime($registro['FechaHoraSalida'])) : 'Pendiente'; ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $registro['Estado']; ?>">
                                            <?php echo $registro['Estado'] === 'activo' ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $registro['IdPago'] ? $registro['IdPago'] : 'N/A'; ?></td>
                                    <td><?php echo $registro['Monto'] ? '$' . number_format($registro['Monto'], 2) : 'N/A'; ?></td>
                                    <td>
                                        <?php if ($registro['MetodoPago']): ?>
                                            <span class="badge badge-<?php echo $registro['MetodoPago']; ?>">
                                                <?php 
                                                switch($registro['MetodoPago']) {
                                                    case 'efectivo': echo 'Efectivo'; break;
                                                    case 'tarjeta': echo 'Tarjeta'; break;
                                                    case 'transferencia': echo 'Transferencia'; break;
                                                    case 'otro': echo 'Otro'; break;
                                                    default: echo $registro['MetodoPago'];
                                                }
                                                ?>
                                            </span>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <h3>💰 Pagos Realizados</h3>
        <p style="color: #666; margin-bottom: 20px;">
            Gestión y visualización de todos los pagos realizados en el parqueadero.
        </p>
        
        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Pagos</div>
                <div class="stat-value"><?php echo count($pagos); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Monto Total</div>
                <div class="stat-value">$<?php echo number_format($totalMonto, 2); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Efectivo</div>
                <div class="stat-value">$<?php echo number_format($totalesPorMetodo['efectivo'], 2); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Tarjeta</div>
                <div class="stat-value">$<?php echo number_format($totalesPorMetodo['tarjeta'], 2); ?></div>
            </div>
        </div>
        
        <!-- Barra de búsqueda -->
        <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
            <form method="GET" action="" style="display: flex; gap: 10px; flex: 1;">
                <input type="hidden" name="vista" value="pagos">
                <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" 
                       placeholder="Buscar por monto, placa, propietario o método de pago" 
                       style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                <button class="btn btn-secondary" type="submit">Buscar</button>
                <?php if ($q !== ''): ?>
                    <a class="btn btn-secondary" href="?vista=pagos">Limpiar</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Tabla de pagos -->
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID Pago</th>
                        <th>Fecha/Hora</th>
                        <th>Moto</th>
                        <th>Propietario</th>
                        <th>Período</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pagos)): ?>
                        <tr>
                            <td colspan="8" style="text-align:center; color:#888;">
                                No se encontraron pagos.
                                <?php if ($q !== ''): ?>
                                    <br><a href="?vista=pagos">Ver todos los pagos</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pagos as $pago): ?>
                            <tr>
                                <td><?php echo $pago['IdPago']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($pago['FechaHoraPago'])); ?></td>
                                <td>
                                    <div><?php echo htmlspecialchars($pago['Placa']); ?></div>
                                    <div style="font-size: 12px; color: #666;">
                                        <?php echo htmlspecialchars($pago['Marca'] . ' ' . $pago['Modelo']); ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($pago['Propietario']); ?></td>
                                <td>
                                    <div><?php echo date('d/m H:i', strtotime($pago['FechaHoraEntrada'])); ?></div>
                                    <div><?php echo date('d/m H:i', strtotime($pago['FechaHoraSalida'])); ?></div>
                                </td>
                                <td>
                                    <strong>$<?php echo number_format($pago['Monto'], 2); ?></strong>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $pago['MetodoPago']; ?>">
                                        <?php 
                                        switch($pago['MetodoPago']) {
                                            case 'efectivo': echo 'Efectivo'; break;
                                            case 'tarjeta': echo 'Tarjeta'; break;
                                            case 'transferencia': echo 'Transferencia'; break;
                                            case 'otro': echo 'Otro'; break;
                                            default: echo $pago['MetodoPago'];
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?vista=pagos&ver_historial=1&id_moto=<?php echo $pago['IdMoto']; ?>&page=<?php echo $page; ?>&q=<?php echo urlencode($q); ?>" 
                                       class="btn btn-info" style="padding: 6px 10px; font-size: 12px;">
                                        Ver Historial
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <?php if ($p === $page): ?>
                        <span class="active" style="padding:8px 12px; border-radius:6px;"><?php echo $p; ?></span>
                    <?php else: ?>
                        <a href="?vista=pagos&page=<?php echo $p; ?>&q=<?php echo urlencode($q); ?>"><?php echo $p; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Mostrar mensajes de éxito o error si existen
    <?php if (isset($_SESSION['mensaje_exito'])): ?>
        Swal.fire({
            title: '¡Éxito!',
            text: '<?php echo addslashes($_SESSION['mensaje_exito']); ?>',
            icon: 'success',
            showConfirmButton: false,
            timer: 1500
        });
        <?php unset($_SESSION['mensaje_exito']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['mensaje_error'])): ?>
        Swal.fire({
            title: 'Error',
            text: '<?php echo addslashes($_SESSION['mensaje_error']); ?>',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
        <?php unset($_SESSION['mensaje_error']); ?>
    <?php endif; ?>
</script>