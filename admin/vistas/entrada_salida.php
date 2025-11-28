<?php
// Incluir conexión a la base de datos
require_once __DIR__ . '/../../db/conexion.php';

// Obtener motos activas (sin salida registrada) - adaptado a la nueva estructura
$motos_activas = [];
$sql = "SELECT r.IdRegistro, r.FechaHoraEntrada, m.IdMoto, m.Placa, m.Marca, m.Modelo, m.Color, 
               m.NombrePropietario, m.TelefonoPropietario, m.DireccionPropietario
        FROM registros r
        JOIN motos m ON r.IdMoto = m.IdMoto
        WHERE r.Estado = 'activo'
        ORDER BY r.FechaHoraEntrada ASC";

$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $motos_activas[] = $row;
    }
}

// Obtener todas las motos para el formulario de entrada (solo las que no están activas)
$todas_motos = [];
$sql = "SELECT m.IdMoto, m.Placa, m.Marca, m.Modelo, m.NombrePropietario
        FROM motos m
        WHERE m.IdMoto NOT IN (
            SELECT r.IdMoto 
            FROM registros r 
            WHERE r.Estado = 'activo'
        )
        ORDER BY m.Placa";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $todas_motos[] = $row;
    }
}
?>


<?php if (isset($_SESSION['mensaje_exito'])): ?>
    <div class="msg success">✓ <?php echo htmlspecialchars($_SESSION['mensaje_exito']); unset($_SESSION['mensaje_exito']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['mensaje_error'])): ?>
    <div class="msg error">✗ <?php echo htmlspecialchars($_SESSION['mensaje_error']); unset($_SESSION['mensaje_error']); ?></div>
<?php endif; ?>

<!-- Controles de QZ Tray -->
<?php include 'impresion_qztray.php'; ?>

<div class="grid">
    <!-- Formulario de Entrada -->
    <div class="card">
        <h3>🚪 Registrar Entrada</h3>
        <form id="entrada-form" method="POST" action="../controladores/motosController.php">
            <input type="hidden" name="accion" value="registrar_entrada">
            <input type="hidden" id="selected-moto-id" name="id_moto" value="">
            <div class="form-group">
                <label for="search-moto">Buscar Moto (Placa, Marca, Modelo o Propietario)</label>
                <div class="search-container">
                    <input type="text" id="search-moto" placeholder="Escriba para buscar...">
                    <div class="search-results" id="search-results"></div>
                </div>
            </div>
            <div class="form-group">
                <label for="selected-moto">Moto Seleccionada</label>
                <input type="text" id="selected-moto" readonly placeholder="Ninguna moto seleccionada">
            </div>
            <button type="submit" class="btn btn-primary" id="submit-entrada" disabled>Registrar Entrada</button>
        </form>
    </div>
    
    <!-- Información de Motos Activas -->
    <div class="card">
        <h3>🏍️ Motos en el Parqueadero</h3>
        <p style="color: #666; margin-bottom: 15px;">
            Total de motos activas: <?php echo count($motos_activas); ?>
        </p>
        <div style="max-height: 300px; overflow-y: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Placa</th>
                        <th>Propietario</th>
                        <th>Entrada</th>
                        <th>Tiempo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($motos_activas)): ?>
                        <tr><td colspan="5" style="text-align:center; color:#888;">No hay motos en el parqueadero.</td></tr>
                    <?php else: ?>
                        <?php foreach ($motos_activas as $moto): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($moto['Placa']); ?></td>
                                <td><?php echo htmlspecialchars($moto['NombrePropietario']); ?></td>
                                <td><?php echo date('d/m H:i', strtotime($moto['FechaHoraEntrada'])); ?></td>
                                <td>
                                    <div class="time-display" id="time-<?php echo $moto['IdRegistro']; ?>" 
                                         data-entrada="<?php echo $moto['FechaHoraEntrada']; ?>">
                                        00:00:00
                                    </div>
                                </td>
                                <td>
                                    <button class="btn btn-success" onclick="registrarSalida(<?php echo $moto['IdRegistro']; ?>)">Salida</button>
                                    <button class="btn btn-danger" onclick="eliminarRegistro(<?php echo $moto['IdRegistro']; ?>)">Eliminar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de confirmación de salida -->
<div id="salida-modal" class="modal-overlay" style="display:none;">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Registrar Salida</h3>
            <button class="btn btn-secondary" onclick="closeSalidaModal()">Cerrar</button>
        </div>
        <div id="salida-details">
            <!-- Los detalles se cargarán aquí dinámicamente -->
        </div>
        <form id="salida-form" style="margin-top: 15px;" method="POST" action="../controladores/motosController.php">
            <input type="hidden" id="salida-id-registro" name="id_registro">
            <input type="hidden" name="accion" value="registrar_salida">
            <div class="form-group">
                <label for="metodo_pago">Método de Pago</label>
                <select id="metodo_pago" name="metodo_pago" required>
                    <option value="">-- Seleccionar método --</option>
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Transferencia</option>
                    <option value="otro">Otro</option>
                </select>
            </div>
            <div class="form-group">
                <label for="monto">Monto a Cobrar ($)</label>
                <input type="number" id="monto" name="monto" step="0.01" min="0" required>
            </div>
            <div style="text-align: right;">
                <button class="btn btn-secondary" type="button" onclick="closeSalidaModal()">Cancelar</button>
                <button class="btn btn-success" type="submit">Registrar Salida y Pago</button>
            </div>
        </form>
    </div>
</div>

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Datos de motos para búsqueda
    const motosData = <?php echo json_encode($todas_motos); ?>;
    
    // Elementos del DOM
    const searchInput = document.getElementById('search-moto');
    const searchResults = document.getElementById('search-results');
    const selectedMotoInput = document.getElementById('selected-moto');
    const selectedMotoIdInput = document.getElementById('selected-moto-id');
    const submitButton = document.getElementById('submit-entrada');
    
    // Variables para almacenar datos de tickets
    let ultimoTicketEntrada = null;
    let ultimoTicketSalida = null;
    
    // Función para filtrar motos
    function filterMotos(query) {
        if (!query) return [];
        
        const lowerQuery = query.toLowerCase();
        return motosData.filter(moto => 
            moto.Placa.toLowerCase().includes(lowerQuery) ||
            moto.Marca.toLowerCase().includes(lowerQuery) ||
            moto.Modelo.toLowerCase().includes(lowerQuery) ||
            moto.NombrePropietario.toLowerCase().includes(lowerQuery)
        );
    }
    
    // Función para mostrar resultados de búsqueda
    function showSearchResults(results) {
        if (results.length === 0) {
            searchResults.style.display = 'none';
            return;
        }
        
        searchResults.innerHTML = '';
        results.forEach(moto => {
            const item = document.createElement('div');
            item.className = 'search-item';
            item.innerHTML = `
                <div><strong>${moto.Placa}</strong></div>
                <div>${moto.Marca} ${moto.Modelo}</div>
                <div>${moto.NombrePropietario}</div>
            `;
            item.addEventListener('click', () => {
                selectMoto(moto);
            });
            searchResults.appendChild(item);
        });
        
        searchResults.style.display = 'block';
    }
    
    // Función para seleccionar una moto
    function selectMoto(moto) {
        selectedMotoInput.value = `${moto.Placa} - ${moto.Marca} ${moto.Modelo}`;
        selectedMotoIdInput.value = moto.IdMoto;
        searchInput.value = '';
        searchResults.style.display = 'none';
        submitButton.disabled = false;
    }
    
    // Evento para búsqueda en tiempo real
    searchInput.addEventListener('input', function() {
        const query = this.value;
        const results = filterMotos(query);
        showSearchResults(results);
    });
    
    // Cerrar resultados de búsqueda al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
    
    // Función para actualizar los contadores de tiempo en tiempo real
    function actualizarTiempos() {
        const timeDisplays = document.querySelectorAll('[id^="time-"]');
        const ahora = new Date();
        
        timeDisplays.forEach(display => {
            const fechaEntrada = display.getAttribute('data-entrada');
            const entrada = new Date(fechaEntrada);
            
            // Verificar que la fecha de entrada sea válida
            if (isNaN(entrada.getTime())) {
                display.textContent = '00:00:00';
                return;
            }
            
            // Calcular diferencia en milisegundos
            const diffMs = ahora - entrada;
            
            // Verificar que la diferencia no sea negativa
            if (diffMs < 0) {
                display.textContent = '00:00:00';
                return;
            }
            
            // Convertir a horas, minutos y segundos
            let segundosTotales = Math.floor(diffMs / 1000);
            const horas = Math.floor(segundosTotales / 3600);
            segundosTotales %= 3600;
            const minutos = Math.floor(segundosTotales / 60);
            const segundos = segundosTotales % 60;
            
            // Formatear con ceros a la izquierda si es necesario
            const horasStr = horas.toString().padStart(2, '0');
            const minutosStr = minutos.toString().padStart(2, '0');
            const segundosStr = segundos.toString().padStart(2, '0');
            
            display.textContent = `${horasStr}:${minutosStr}:${segundosStr}`;
        });
    }
    
    // Actualizar tiempos cada segundo
    setInterval(actualizarTiempos, 1000);
    
    // Inicializar tiempos al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        // Ejecutar inmediatamente y luego cada segundo
        actualizarTiempos();
        setInterval(actualizarTiempos, 1000);
    });
    
    // Función para imprimir ticket usando QZ Tray
    function imprimirTicketQZ(datos) {
        // Verificar si QZ Tray está disponible y conectado
        if (typeof qz === 'undefined' || !qz.websocket.isActive()) {
            console.warn("QZ Tray no está disponible o no está conectado");
            return Promise.resolve(false);
        }
        
        // Verificar si la impresora está configurada
        if (!window.printer) {
            console.warn("No se ha encontrado la impresora");
            return Promise.resolve(false);
        }
        
        try {
            let config = qz.configs.create(window.printer);
            
            let data = [];
            
            if (datos.tipo === 'entrada') {
                // Formato de ticket de entrada
                data = [
                    "\x1B\x40", // reset
                    "\x1B\x61\x01", // center align
                    "\x1B\x45\x01", // bold on
                    "\x1D\x21\x11", // double height and width
                    "PARQUEADERO V.S\n",
                    "\x1B\x45\x00", // bold off
                    "\x1D\x21\x00", // normal size
                    "**************************\n",
                    "TICKET DE ENTRADA\n",
                    "**************************\n",
                    "\x1B\x61\x00", // left align
                    "\n",
                    "FECHA/HORA: " + datos.fecha_hora + "\n",
                    "PLACA: " + datos.placa + "\n",
                    "MARCA: " + datos.marca + "\n",
                    "MODELO: " + datos.modelo + "\n",
                    "COLOR: " + datos.color + "\n",
                    "\n",
                    "PROPIETARIO: " + datos.propietario + "\n",
                    "TELEFONO: " + datos.telefono + "\n",
                    "DIRECCION: " + datos.direccion + "\n",
                    "\n",
                    "ID REGISTRO: " + datos.id_registro + "\n",
                    "\n",
                    "\x1B\x61\x01", // center align
                    "**************************\n",
                    "GRACIAS POR SU VISITA\n",
                    "**************************\n",
                    "\n\n\n\n\n", // feed paper
                    "\x1D\x56\x01"   // cut paper
                ];
            } else if (datos.tipo === 'salida') {
                // Formato de ticket de salida
                data = [
                    "\x1B\x40", // reset
                    "\x1B\x61\x01", // center align
                    "\x1B\x45\x01", // bold on
                    "\x1D\x21\x11", // double height and width
                    "PARQUEADERO V.S\n",
                    "\x1B\x45\x00", // bold off
                    "\x1D\x21\x00", // normal size
                    "**************************\n",
                    "TICKET DE SALIDA\n",
                    "**************************\n",
                    "\x1B\x61\x00", // left align
                    "\n",
                    "FECHA/HORA ENTRADA: " + datos.fecha_entrada + "\n",
                    "FECHA/HORA SALIDA: " + datos.fecha_salida + "\n",
                    "TIEMPO: " + datos.tiempo + "\n",
                    "\n",
                    "PLACA: " + datos.placa + "\n",
                    "MARCA: " + datos.marca + "\n",
                    "MODELO: " + datos.modelo + "\n",
                    "\n",
                    "PROPIETARIO: " + datos.propietario + "\n",
                    "\n",
                    "METODO DE PAGO: " + datos.metodo_pago.toUpperCase() + "\n",
                    "\x1B\x45\x01", // bold on
                    "\x1D\x21\x11", // double height and width
                    "TOTAL A PAGAR: $" + parseFloat(datos.monto).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ",") + "\n",
                    "\x1B\x45\x00", // bold off
                    "\x1D\x21\x00", // normal size
                    "\n",
                    "ID PAGO: " + datos.id_pago + "\n",
                    "ID REGISTRO: " + datos.id_registro + "\n",
                    "\n",
                    "\x1B\x61\x01", // center align
                    "**************************\n",
                    "GRACIAS POR SU VISITA\n",
                    "**************************\n",
                    "\n\n\n\n\n", // feed paper
                    "\x1D\x56\x01"   // cut paper
                ];
            }
            
            return qz.print(config, data)
                .then(() => {
                    console.log("Ticket impreso correctamente");
                    return true;
                })
                .catch(err => {
                    console.error("Error al imprimir ticket:", err);
                    return false;
                });
        } catch (err) {
            console.error("Error general al imprimir ticket:", err);
            return Promise.resolve(false);
        }
    }
    
    // Función para imprimir ticket de entrada automáticamente
    function imprimirTicketEntradaAutomatico(datos) {
        // Intentar imprimir el ticket automáticamente
        imprimirTicketQZ(datos)
            .then(resultado => {
                if (resultado) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: 'Entrada registrada e impresión automática del ticket',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire({
                        title: 'Advertencia',
                        text: 'Entrada registrada pero no se pudo imprimir el ticket automáticamente. Verifique la conexión con QZ Tray.',
                        icon: 'warning',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
    }
    
    // Función para imprimir ticket de salida automáticamente
    function imprimirTicketSalidaAutomatico(datos) {
        // Intentar imprimir el ticket automáticamente
        imprimirTicketQZ(datos)
            .then(resultado => {
                if (resultado) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: 'Salida registrada e impresión automática del ticket',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire({
                        title: 'Advertencia',
                        text: 'Salida registrada pero no se pudo imprimir el ticket automáticamente. Verifique la conexión con QZ Tray.',
                        icon: 'warning',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
    }
    
    function registrarSalida(idRegistro) {
        // En una implementación real, aquí se cargarían los detalles del registro
        document.getElementById('salida-id-registro').value = idRegistro;
        
        // Calcular monto automáticamente (ejemplo básico)
        const tiempoEstimado = 1; // horas
        const tarifaPorHora = 6000; // COP
        const monto = tiempoEstimado * tarifaPorHora;
        document.getElementById('monto').value = monto;
        
        // Mostrar modal
        document.getElementById('salida-modal').style.display = 'flex';
    }
    
    function eliminarRegistro(idRegistro) {
        Swal.fire({
            title: '¿Eliminar registro?',
            text: "¿Estás seguro de que deseas eliminar este registro de entrada? Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e53935',
            cancelButtonColor: '#666',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Enviar solicitud para eliminar el registro
                const formData = new FormData();
                formData.append('accion', 'eliminar_registro');
                formData.append('id_registro', idRegistro);
                
                fetch('../controladores/motosController.php', {
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
            }
        });
    }
    
    function closeSalidaModal() {
        document.getElementById('salida-modal').style.display = 'none';
    }
    
    // Manejar el envío del formulario de entrada
    document.getElementById('entrada-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const selectedMotoText = selectedMotoInput.value;
        if (!selectedMotoText) {
            Swal.fire({
                title: 'Error',
                text: 'Por favor seleccione una moto para registrar la entrada.',
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
            return;
        }
        
        Swal.fire({
            title: '¿Registrar entrada?',
            text: `¿Deseas registrar la entrada de la moto ${selectedMotoText} al parqueadero?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#15ad4d',
            cancelButtonColor: '#666',
            confirmButtonText: 'Sí, registrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Enviar el formulario
                const formData = new FormData(this);
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Intentar imprimir ticket automáticamente si hay datos
                        if (data.ticket_data) {
                            // Imprimir ticket automáticamente
                            imprimirTicketEntradaAutomatico(data.ticket_data);
                            
                            // Recargar la página después de un breve retraso
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        } else {
                            Swal.fire({
                                title: '¡Éxito!',
                                text: data.message,
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.reload();
                            });
                        }
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
    
    // Manejar el envío del formulario de salida
    document.getElementById('salida-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: '¿Registrar salida?',
            text: "¿Deseas registrar la salida de esta moto y generar el pago?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#15ad4d',
            cancelButtonColor: '#666',
            confirmButtonText: 'Sí, registrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Enviar el formulario
                const formData = new FormData(this);
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Intentar imprimir ticket automáticamente si hay datos
                        if (data.ticket_data) {
                            // Imprimir ticket automáticamente
                            imprimirTicketSalidaAutomatico(data.ticket_data);
                            
                            // Cerrar modal y recargar la página después de un breve retraso
                            closeSalidaModal();
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        } else {
                            Swal.fire({
                                title: '¡Éxito!',
                                text: data.message,
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                closeSalidaModal();
                                window.location.reload();
                            });
                        }
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