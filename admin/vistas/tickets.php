<?php
// Incluir conexión a la base de datos
require_once __DIR__ . '/../../db/conexion.php';

// Parámetros de búsqueda y paginación
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'todos'; // 'todos', 'entrada', 'salida'
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Construir filtro de búsqueda
$where = '';
$params = [];
$types = '';
$conditions = [];

// Filtro de búsqueda
if ($q !== '') {
    $qLike = '%' . $q . '%';
    $conditions[] = "(m.Placa LIKE ? OR m.NombrePropietario LIKE ? OR r.IdRegistro LIKE ?)";
    $params = array_merge($params, [$qLike, $qLike, $qLike]);
    $types .= 'sss';
}

// Filtro por tipo
if ($tipo === 'entrada') {
    $conditions[] = "r.FechaHoraSalida IS NULL";
} elseif ($tipo === 'salida') {
    $conditions[] = "r.FechaHoraSalida IS NOT NULL";
}

if (!empty($conditions)) {
    $where = "WHERE " . implode(" AND ", $conditions);
}

// Contar total de registros
$total = 0;
$countSql = "SELECT COUNT(*) as total 
             FROM registros r
             JOIN motos m ON r.IdMoto = m.IdMoto
             LEFT JOIN pagos p ON r.IdRegistro = p.IdRegistro
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

// Consultar registros con información relacionada (simulando tickets)
$tickets = [];
$sql = "SELECT r.IdRegistro as IdTicket, 
               CONCAT('REG-', r.IdRegistro) as CodigoTicket, 
               r.FechaHoraEntrada as FechaHoraEmision, 
               r.Estado,
               p.Monto, p.FechaHoraPago, p.MetodoPago,
               m.IdMoto, m.Placa, m.Marca, m.Modelo,
               m.NombrePropietario as Propietario,
               r.FechaHoraEntrada, r.FechaHoraSalida,
               CASE 
                   WHEN r.FechaHoraSalida IS NOT NULL THEN 'Salida'
                   ELSE 'Entrada'
               END as TipoTicket
        FROM registros r
        JOIN motos m ON r.IdMoto = m.IdMoto
        LEFT JOIN pagos p ON r.IdRegistro = p.IdRegistro
        $where
        ORDER BY r.FechaHoraEntrada DESC
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
        $tickets[] = $row;
    }
}

// Calcular estadísticas
$estadisticas = [
    'total' => 0,
    'activos' => 0,
    'anulados' => 0,
    'impresos' => 0
];

$statsSql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN r.Estado = 'activo' THEN 1 ELSE 0 END) as activos,
                SUM(CASE WHEN r.Estado = 'inactivo' THEN 1 ELSE 0 END) as anulados,
                SUM(CASE WHEN r.FechaHoraSalida IS NOT NULL THEN 1 ELSE 0 END) as impresos
             FROM registros r
             JOIN motos m ON r.IdMoto = m.IdMoto
             LEFT JOIN pagos p ON r.IdRegistro = p.IdRegistro
             $where";

if ($types !== '') {
    $stmt = $conn->prepare($statsSql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $statsResult = $stmt->get_result();
} else {
    $statsResult = $conn->query($statsSql);
}

if ($statsResult) {
    $estadisticas = $statsResult->fetch_assoc();
}
?>


<?php if (isset($_SESSION['mensaje_exito'])): ?>
    <div class="msg success">✓ <?php echo htmlspecialchars($_SESSION['mensaje_exito']); unset($_SESSION['mensaje_exito']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['mensaje_error'])): ?>
    <div class="msg error">✗ <?php echo htmlspecialchars($_SESSION['mensaje_error']); unset($_SESSION['mensaje_error']); ?></div>
<?php endif; ?>

<div class="card">
    <h3>🎫 Gestión de Tickets</h3>
    <p style="color: #666; margin-bottom: 20px;">
        Visualización de registros de entrada y salida como tickets.
    </p>
    
    <!-- Estadísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Registros</div>
            <div class="stat-value"><?php echo $estadisticas['total']; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Activos</div>
            <div class="stat-value"><?php echo $estadisticas['activos']; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Completados</div>
            <div class="stat-value"><?php echo $estadisticas['impresos']; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Inactivos</div>
            <div class="stat-value"><?php echo $estadisticas['anulados']; ?></div>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="filters">
        <form method="GET" action="" style="display: flex; gap: 10px; flex: 1;">
            <input type="hidden" name="vista" value="tickets">
            <select name="tipo" class="filter-select" onchange="this.form.submit()">
                <option value="todos" <?php echo $tipo === 'todos' ? 'selected' : ''; ?>>Todos los registros</option>
                <option value="entrada" <?php echo $tipo === 'entrada' ? 'selected' : ''; ?>>Solo entradas</option>
                <option value="salida" <?php echo $tipo === 'salida' ? 'selected' : ''; ?>>Solo salidas</option>
            </select>
            <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" 
                   placeholder="Buscar por ID, placa o propietario" 
                   style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
            <button class="btn btn-secondary" type="submit">Buscar</button>
            <?php if ($q !== '' || $tipo !== 'todos'): ?>
                <a class="btn btn-secondary" href="?vista=tickets">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- Tabla de registros como tickets -->
    <div style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>ID Registro</th>
                    <th>Fecha/Hora</th>
                    <th>Moto</th>
                    <th>Propietario</th>
                    <th>Monto</th>
                    <th>Estado</th>
                    <th>Tipo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tickets)): ?>
                    <tr>
                        <td colspan="8" style="text-align:center; color:#888;">
                            No se encontraron registros.
                            <?php if ($q !== '' || $tipo !== 'todos'): ?>
                                <br><a href="?vista=tickets">Ver todos los registros</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ticket['CodigoTicket']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($ticket['FechaHoraEmision'])); ?></td>
                            <td>
                                <div><?php echo htmlspecialchars($ticket['Placa']); ?></div>
                                <div style="font-size: 12px; color: #666;">
                                    <?php echo htmlspecialchars($ticket['Marca'] . ' ' . $ticket['Modelo']); ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($ticket['Propietario']); ?></td>
                            <td>
                                <?php if ($ticket['Monto']): ?>
                                    <strong>$<?php echo number_format($ticket['Monto'], 2); ?></strong>
                                <?php else: ?>
                                    <span style="color: #999;">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $ticket['Estado'] === 'activo' ? 'activo' : 'anulado'; ?>">
                                    <?php 
                                    switch($ticket['Estado']) {
                                        case 'activo': echo 'Activo'; break;
                                        case 'inactivo': echo 'Inactivo'; break;
                                        default: echo $ticket['Estado'];
                                    }
                                    ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($ticket['TipoTicket'] === 'Salida'): ?>
                                    <span class="badge badge-salida">Salida</span>
                                <?php else: ?>
                                    <span class="badge badge-entrada">Entrada</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                    <button class="btn btn-info" style="padding: 6px 10px; font-size: 12px;" 
                                            onclick="verTicket(<?php echo $ticket['IdTicket']; ?>, '<?php echo htmlspecialchars($ticket['CodigoTicket']); ?>', '<?php echo htmlspecialchars($ticket['Placa']); ?>', '<?php echo htmlspecialchars($ticket['Propietario']); ?>', '<?php echo $ticket['Monto'] ? number_format($ticket['Monto'], 2) : 'N/A'; ?>', '<?php echo date('d/m/Y H:i', strtotime($ticket['FechaHoraEmision'])); ?>', '<?php echo $ticket['TipoTicket']; ?>', '<?php echo date('d/m/Y H:i', strtotime($ticket['FechaHoraEntrada'])); ?>', '<?php echo $ticket['FechaHoraSalida'] ? date('d/m/Y H:i', strtotime($ticket['FechaHoraSalida'])) : 'Pendiente'; ?>')">
                                        Ver
                                    </button>
                                </div>
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
                    <a href="?vista=tickets&page=<?php echo $p; ?>&q=<?php echo urlencode($q); ?>&tipo=<?php echo urlencode($tipo); ?>"><?php echo $p; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de vista previa de ticket -->
<div id="ticket-modal" class="modal-overlay" style="display:none;">
    <div class="modal-card" style="width: min(500px, 95vw);">
        <div class="modal-header">
            <h3>🎫 Vista Previa de Registro</h3>
            <button class="btn btn-secondary" onclick="cerrarModal()">Cerrar</button>
        </div>
        <div id="ticket-preview-content" style="text-align: center;">
            <!-- Contenido del ticket se cargará aquí -->
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <button class="btn btn-success" onclick="imprimirDesdeModal()">Imprimir</button>
        </div>
    </div>
</div>

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Función para ver ticket en formato real
    function verTicket(idTicket, codigo, placa, propietario, monto, fecha, tipo, entrada, salida) {
        const ticketContent = `
            <div class="ticket-preview">
                <div class="ticket-header">
                    <h4 class="ticket-title">PARKING VALE</h4>
                    <p class="ticket-subtitle">Sistema de Parqueadero</p>
                </div>
                <div class="ticket-content">
                    <div class="ticket-row">
                        <span class="ticket-label">Registro ID:</span>
                        <span>${idTicket}</span>
                    </div>
                    <div class="ticket-row">
                        <span class="ticket-label">Código:</span>
                        <span>${codigo}</span>
                    </div>
                    <div class="ticket-row">
                        <span class="ticket-label">Tipo:</span>
                        <span>${tipo}</span>
                    </div>
                    <div class="ticket-row">
                        <span class="ticket-label">Fecha/Hora:</span>
                        <span>${fecha}</span>
                    </div>
                    <div class="ticket-row">
                        <span class="ticket-label">Entrada:</span>
                        <span>${entrada}</span>
                    </div>
                    <div class="ticket-row">
                        <span class="ticket-label">Salida:</span>
                        <span>${salida}</span>
                    </div>
                    <div class="ticket-row">
                        <span class="ticket-label">Placa:</span>
                        <span>${placa}</span>
                    </div>
                    <div class="ticket-row">
                        <span class="ticket-label">Propietario:</span>
                        <span>${propietario}</span>
                    </div>
                    <div class="ticket-row">
                        <span class="ticket-label">Monto:</span>
                        <span>$${monto}</span>
                    </div>
                </div>
                <div class="ticket-code">
                    ${codigo}
                </div>
                <div class="ticket-footer">
                    <p>Gracias por su visita</p>
                    <p>¡Vuelva pronto!</p>
                </div>
            </div>
        `;
        
        document.getElementById('ticket-preview-content').innerHTML = ticketContent;
        document.getElementById('ticket-modal').style.display = 'flex';
    }
    
    // Función para imprimir
    function imprimirDesdeModal() {
        Swal.fire({
            title: '¡Impreso!',
            text: 'El registro ha sido enviado a la impresora.',
            icon: 'success',
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            cerrarModal();
        });
    }
    
    // Funciones para el modal
    function cerrarModal() {
        document.getElementById('ticket-modal').style.display = 'none';
    }
    
    // Cerrar modal al hacer clic fuera
    window.onclick = function(event) {
        const modal = document.getElementById('ticket-modal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }
    
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