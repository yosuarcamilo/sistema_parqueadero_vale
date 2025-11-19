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
if ($q !== '') {
    $qLike = '%' . $conn->real_escape_string($q) . '%';
    $where = "WHERE NombrePropietario LIKE '$qLike' OR TelefonoPropietario LIKE '$qLike' OR DireccionPropietario LIKE '$qLike'";
}

// Contar total de registros únicos por nombre de propietario (ignorando mayúsculas/minúsculas)
$total = 0;
$countResult = $conn->query("SELECT COUNT(DISTINCT LOWER(NombrePropietario)) as total FROM motos WHERE NombrePropietario IS NOT NULL AND NombrePropietario != '' $where");
if ($countResult) {
    $row = $countResult->fetch_assoc();
    $total = (int)$row['total'];
}
$totalPages = max(1, (int)ceil($total / $perPage));

// Consultar propietarios únicos con sus datos
$propietarios = [];
$sql = "SELECT NombrePropietario, TelefonoPropietario, DireccionPropietario, COUNT(*) as CantidadMotos 
        FROM motos 
        WHERE NombrePropietario IS NOT NULL AND NombrePropietario != '' 
        $where 
        GROUP BY LOWER(NombrePropietario)
        ORDER BY NombrePropietario ASC 
        LIMIT $perPage OFFSET $offset";

$result = $conn->query($sql);
if ($result) {
    while ($r = $result->fetch_assoc()) { 
        $propietarios[] = $r; 
    }
}
?>


<?php if (isset($_SESSION['mensaje_exito'])): ?>
    <div class="msg success">✓ <?php echo htmlspecialchars($_SESSION['mensaje_exito']); unset($_SESSION['mensaje_exito']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['mensaje_error'])): ?>
    <div class="msg error">✗ <?php echo htmlspecialchars($_SESSION['mensaje_error']); unset($_SESSION['mensaje_error']); ?></div>
<?php endif; ?>

<div class="card">
    <div class="toolbar">
        <form method="GET" action="" class="search">
            <input type="hidden" name="vista" value="gestion_propietarios">
            <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Buscar por nombre, teléfono o dirección">
            <button class="btn btn-secondary" type="submit">Buscar</button>
            <?php if ($q !== ''): ?>
                <a class="btn btn-secondary" href="?vista=gestion_propietarios">Limpiar</a>
            <?php endif; ?>
        </form>
        <div style="font-size: 14px; color: #666;">Total: <?php echo $total; ?> propietarios</div>
    </div>
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Dirección</th>
                <th>Cantidad de Motos</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($propietarios)): ?>
                <tr><td colspan="5" style="text-align:center; color:#888;">No hay propietarios registrados.</td></tr>
            <?php else: ?>
                <?php foreach ($propietarios as $p): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['NombrePropietario'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($p['TelefonoPropietario'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($p['DireccionPropietario'] ?? '—'); ?></td>
                        <td><?php echo (int)$p['CantidadMotos']; ?></td>
                        <td>
                            <button class="btn btn-secondary" type="button" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($p)); ?>)">Editar</button>
                            <button class="btn btn-primary" type="button" onclick="openViewModal(<?php echo htmlspecialchars(json_encode($p)); ?>)">Ver</button>
                            <button class="btn btn-danger" type="button" onclick="confirmDelete(<?php echo htmlspecialchars(json_encode($p['NombrePropietario'])); ?>)">Eliminar</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <?php if ($p === $page): ?>
                    <span class="active" style="padding:8px 12px; border-radius:6px;"><?php echo $p; ?></span>
                <?php else: ?>
                    <a href="?vista=gestion_propietarios&page=<?php echo $p; ?>&q=<?php echo urlencode($q); ?>"><?php echo $p; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de visualización -->
<div id="view-modal" class="modal-overlay" style="display:none;">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Detalles del Propietario</h3>
            <button class="btn btn-secondary" onclick="closeViewModal()">Cerrar</button>
        </div>
        <div class="grid" id="view-details">
            <!-- Los detalles se cargarán aquí dinámicamente -->
        </div>
    </div>
</div>

<!-- Modal de edición -->
<div id="edit-modal" class="modal-overlay" style="display:none;">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Editar Propietario</h3>
            <button class="btn btn-secondary" onclick="closeEditModal()">Cerrar</button>
        </div>
        <form id="edit-form">
            <input type="hidden" id="edit-nombre-original" name="nombre_original">
            <div class="form-group">
                <label for="edit-nombre">Nombre</label>
                <input type="text" id="edit-nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="edit-telefono">Teléfono</label>
                <input type="text" id="edit-telefono" name="telefono">
            </div>
            <div class="form-group">
                <label for="edit-direccion">Dirección</label>
                <input type="text" id="edit-direccion" name="direccion">
            </div>
            <div style="text-align: right;">
                <button class="btn btn-secondary" type="button" onclick="closeEditModal()">Cancelar</button>
                <button class="btn btn-primary" type="submit">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function openViewModal(data) {
        const details = document.getElementById('view-details');
        details.innerHTML = `
            <div>
                <label style="font-size:12px; color:#777;">Nombre</label>
                <div style="font-weight:600;">${data.NombrePropietario || '—'}</div>
            </div>
            <div>
                <label style="font-size:12px; color:#777;">Teléfono</label>
                <div style="font-weight:600;">${data.TelefonoPropietario || '—'}</div>
            </div>
            <div>
                <label style="font-size:12px; color:#777;">Dirección</label>
                <div style="font-weight:600;">${data.DireccionPropietario || '—'}</div>
            </div>
            <div>
                <label style="font-size:12px; color:#777;">Cantidad de Motos</label>
                <div style="font-weight:600;">${data.CantidadMotos || '0'}</div>
            </div>
        `;
        document.getElementById('view-modal').style.display = 'flex';
    }
    
    function closeViewModal() {
        document.getElementById('view-modal').style.display = 'none';
    }
    
    function openEditModal(data) {
        document.getElementById('edit-nombre-original').value = data.NombrePropietario || '';
        document.getElementById('edit-nombre').value = data.NombrePropietario || '';
        document.getElementById('edit-telefono').value = data.TelefonoPropietario || '';
        document.getElementById('edit-direccion').value = data.DireccionPropietario || '';
        document.getElementById('edit-modal').style.display = 'flex';
    }
    
    function closeEditModal() {
        document.getElementById('edit-modal').style.display = 'none';
    }
    
    function confirmDelete(nombre) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `Esta acción eliminará permanentemente todos los registros del propietario "${nombre}" y sus motos asociadas.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e53935',
            cancelButtonColor: '#666',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Funcionalidad no implementada',
                    text: 'La eliminación de propietarios aún no está implementada en esta versión.',
                    icon: 'info',
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    }
    
    // Manejar el envío del formulario de edición
    document.getElementById('edit-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Mostrar confirmación antes de enviar
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¿Deseas actualizar la información de este propietario?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#15ad4d',
            cancelButtonColor: '#666',
            confirmButtonText: 'Sí, actualizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Funcionalidad no implementada',
                    text: 'La actualización de propietarios aún no está implementada en esta versión.',
                    icon: 'info',
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    });
    
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