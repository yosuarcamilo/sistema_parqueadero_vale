<?php
// Construir ruta absoluta para acciones del controlador
$basePath = dirname(dirname($_SERVER['SCRIPT_NAME']));
$motosController = $basePath . '/controladores/motosController.php';

// Parámetros de búsqueda y paginación
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Construir filtro de búsqueda
$where = '';
if ($q !== '') {
    $qLike = '%' . $conn->real_escape_string($q) . '%';
    $where = "WHERE Placa LIKE '$qLike' OR Marca LIKE '$qLike' OR Modelo LIKE '$qLike' OR Color LIKE '$qLike' OR NombrePropietario LIKE '$qLike' OR TelefonoPropietario LIKE '$qLike' OR DireccionPropietario LIKE '$qLike'";
}

// Contar total
$total = 0;
$countResult = $conn->query("SELECT COUNT(*) AS total FROM motos $where");
if ($countResult) {
    $row = $countResult->fetch_assoc();
    $total = (int)$row['total'];
}
$totalPages = max(1, (int)ceil($total / $perPage));

// Consultar registros
$motos = [];
$sql = "SELECT IdMoto, Placa, Marca, Modelo, Color, NombrePropietario, TelefonoPropietario, DireccionPropietario
        FROM motos $where ORDER BY IdMoto DESC LIMIT $perPage OFFSET $offset";

$result = $conn->query($sql);
if ($result) {
    while ($r = $result->fetch_assoc()) { $motos[] = $r; }
}

// Modo edición
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
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
            <input type="hidden" name="vista" value="gestion_motos">
            <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Buscar por placa, marca, modelo, color o propietario">
            <button class="btn btn-secondary" type="submit">Buscar</button>
            <?php if ($q !== ''): ?>
                <a class="btn btn-secondary" href="?vista=gestion_motos">Limpiar</a>
            <?php endif; ?>
        </form>
        <div style="font-size: 14px; color: #666;">Total: <?php echo $total; ?></div>
    </div>
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Placa</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Color</th>
                <th>Propietario</th>
                <th>Teléfono</th>
                <th>Dirección</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($motos)): ?>
                <tr><td colspan="9" style="text-align:center; color:#888;">No hay motos registradas.</td></tr>
            <?php else: ?>
                <?php foreach ($motos as $m): ?>
                    <tr>
                        <td><?php echo (int)$m['IdMoto']; ?></td>
                        <td><?php echo htmlspecialchars($m['Placa'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($m['Marca'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($m['Modelo'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($m['Color'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($m['NombrePropietario'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($m['TelefonoPropietario'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($m['DireccionPropietario'] ?? '—'); ?></td>
                        <td>
                            <button class="btn btn-secondary" type="button" onclick="openEditModal(<?php echo (int)$m['IdMoto']; ?>, <?php echo htmlspecialchars(json_encode($m)); ?>)">Editar</button>
                            <button class="btn btn-primary" type="button" onclick="openModal(<?php echo (int)$m['IdMoto']; ?>)">Ver</button>
                            <button class="btn btn-danger" type="button" onclick="confirmDelete(<?php echo (int)$m['IdMoto']; ?>)">Eliminar</button>
                        </td>
                    </tr>
                    <div id="modal-<?php echo (int)$m['IdMoto']; ?>" class="modal-overlay" style="display:none;">
                        <div class="modal-card">
                            <div class="modal-header">
                                <h3>Detalles de la moto #<?php echo (int)$m['IdMoto']; ?></h3>
                                <button class="btn btn-secondary" onclick="closeModal(<?php echo (int)$m['IdMoto']; ?>)">Cerrar</button>
                            </div>
                            <div class="grid">
                                <div>
                                    <label>Placa</label>
                                    <div><?php echo htmlspecialchars($m['Placa'] ?? '—'); ?></div>
                                </div>
                                <div>
                                    <label>Marca</label>
                                    <div><?php echo htmlspecialchars($m['Marca'] ?? '—'); ?></div>
                                </div>
                                <div>
                                    <label>Modelo</label>
                                    <div><?php echo htmlspecialchars($m['Modelo'] ?? '—'); ?></div>
                                </div>
                                <div>
                                    <label>Color</label>
                                    <div><?php echo htmlspecialchars($m['Color'] ?? '—'); ?></div>
                                </div>
                                <div>
                                    <label>Propietario</label>
                                    <div><?php echo htmlspecialchars($m['NombrePropietario'] ?? '—'); ?></div>
                                </div>
                                <div>
                                    <label>Teléfono</label>
                                    <div><?php echo htmlspecialchars($m['TelefonoPropietario'] ?? '—'); ?></div>
                                </div>
                                <div style="grid-column: 1 / -1;">
                                    <label>Dirección</label>
                                    <div><?php echo htmlspecialchars($m['DireccionPropietario'] ?? '—'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                    <a href="?vista=gestion_motos&page=<?php echo $p; ?>&q=<?php echo urlencode($q); ?>"><?php echo $p; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de edición -->
<div id="edit-modal" class="modal-overlay" style="display:none;">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Editar Moto</h3>
            <button class="btn btn-secondary" onclick="closeEditModal()">Cerrar</button>
        </div>
        <form id="edit-form">
            <input type="hidden" id="edit-id" name="id">
            <input type="hidden" name="accion" value="actualizar">
            <div class="form-group">
                <label for="edit-placa">Placa</label>
                <input type="text" id="edit-placa" name="placa">
            </div>
            <div class="form-group">
                <label for="edit-marca">Marca</label>
                <input type="text" id="edit-marca" name="marca">
            </div>
            <div class="form-group">
                <label for="edit-modelo">Modelo</label>
                <input type="text" id="edit-modelo" name="modelo">
            </div>
            <div class="form-group">
                <label for="edit-color">Color</label>
                <input type="text" id="edit-color" name="color">
            </div>
            <div class="form-group">
                <label for="edit-nombre_propietario">Nombre del Propietario</label>
                <input type="text" id="edit-nombre_propietario" name="nombre_propietario">
            </div>
            <div class="form-group">
                <label for="edit-telefono_propietario">Teléfono del Propietario</label>
                <input type="text" id="edit-telefono_propietario" name="telefono_propietario">
            </div>
            <div class="form-group">
                <label for="edit-direccion_propietario">Dirección del Propietario</label>
                <input type="text" id="edit-direccion_propietario" name="direccion_propietario">
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
    function openModal(id){
        var el = document.getElementById('modal-' + id);
        if(el){ el.style.display = 'flex'; }
    }
    
    function closeModal(id){
        var el = document.getElementById('modal-' + id);
        if(el){ el.style.display = 'none'; }
    }
    
    function openEditModal(id, data) {
        // Rellenar el formulario con los datos actuales
        document.getElementById('edit-id').value = id;
        document.getElementById('edit-placa').value = data.Placa || '';
        document.getElementById('edit-marca').value = data.Marca || '';
        document.getElementById('edit-modelo').value = data.Modelo || '';
        document.getElementById('edit-color').value = data.Color || '';
        document.getElementById('edit-nombre_propietario').value = data.NombrePropietario || '';
        document.getElementById('edit-telefono_propietario').value = data.TelefonoPropietario || '';
        document.getElementById('edit-direccion_propietario').value = data.DireccionPropietario || '';
        
        // Mostrar el modal
        document.getElementById('edit-modal').style.display = 'flex';
    }
    
    function closeEditModal() {
        document.getElementById('edit-modal').style.display = 'none';
    }
    
    function confirmDelete(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción eliminará permanentemente la moto del sistema.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e53935',
            cancelButtonColor: '#666',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Crear formulario dinámicamente para enviar la solicitud de eliminación
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?php echo $motosController; ?>';
                
                const accionInput = document.createElement('input');
                accionInput.type = 'hidden';
                accionInput.name = 'accion';
                accionInput.value = 'eliminar';
                form.appendChild(accionInput);
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = id;
                form.appendChild(idInput);
                
                // Añadir callback para manejar la respuesta
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(form);
                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                title: '¡Eliminado!',
                                text: data.message,
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message,
                                icon: 'error',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error',
                            text: 'Ocurrió un error al procesar la solicitud.',
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                    });
                });
                
                document.body.appendChild(form);
                form.dispatchEvent(new Event('submit'));
            }
        });
    }
    
    // Manejar el envío del formulario de edición
    document.getElementById('edit-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Mostrar confirmación antes de enviar
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¿Deseas actualizar la información de esta moto?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#15ad4d',
            cancelButtonColor: '#666',
            confirmButtonText: 'Sí, actualizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData(this);
                fetch('<?php echo $motosController; ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            title: '¡Actualizado!',
                            text: data.message,
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message,
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error',
                        text: 'Ocurrió un error al procesar la solicitud.',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
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