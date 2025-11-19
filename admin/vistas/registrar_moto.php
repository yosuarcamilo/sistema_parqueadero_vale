
    <div class="form-container">
        <div class="form-header">
            <h3>🏍️ Registrar Moto y Propietario</h3>
    </div>
    
    <div class="form-body">
        <?php if (isset($_SESSION['mensaje_exito'])): ?>
            <div class="message success">
                ✓ <?php echo htmlspecialchars($_SESSION['mensaje_exito']); ?>
            </div>
            <?php unset($_SESSION['mensaje_exito']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['mensaje_error'])): ?>
                <div class="message error">
                    ✗ <?php echo htmlspecialchars($_SESSION['mensaje_error']); ?>
                </div>
                <?php unset($_SESSION['mensaje_error']); ?>
                <?php endif; ?>
                
                <div class="note">
                    <strong>Nota:</strong> Todos los campos son opcionales. Puedes registrar solo la información que tengas disponible.
        </div>
        
        <?php 
            // Construir ruta absoluta del proyecto para evitar problemas de base URL
            $basePath = dirname(dirname($_SERVER['SCRIPT_NAME'])); // /parqueadero_vale
            $actionUrl = $basePath . '/controladores/motosController.php';
        ?>
        <form id="registroForm" method="POST" action="<?php echo $actionUrl; ?>">
            <input type="hidden" name="accion" value="crear">
            <!-- Sección de Datos de la Moto -->
            <div class="form-section">
                <div class="form-section-title">
                    <span>🏍️ Datos de la Moto</span>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="placa">Placa</label>
                        <input type="text" id="placa" name="placa" placeholder="ABC-123">
                    </div>
                    <div class="form-group">
                        <label for="marca">Marca</label>
                        <input type="text" id="marca" name="marca" placeholder="Honda, Yamaha, etc.">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="modelo">Modelo</label>
                        <input type="text" id="modelo" name="modelo" placeholder="Ej: CBR250">
                    </div>
                    <div class="form-group">
                        <label for="color">Color</label>
                        <input type="text" id="color" name="color" placeholder="Rojo, Negro, etc.">
                    </div>
                </div>
            </div>

            <!-- Sección de Datos del Propietario -->
            <div class="form-section">
                <div class="form-section-title">
                    <span>👤 Datos del Propietario</span>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre_propietario">Nombre Completo</label>
                        <input type="text" id="nombre_propietario" name="nombre_propietario" placeholder="Juan Pérez">
                    </div>
                    <div class="form-group">
                        <label for="telefono_propietario">Teléfono</label>
                        <input type="text" id="telefono_propietario" name="telefono_propietario" placeholder="300 123 4567">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="direccion_propietario">Dirección</label>
                        <input type="text" id="direccion_propietario" name="direccion_propietario" placeholder="Calle 123 #45-67">
                    </div>
                </div>
            </div>
            
            <!-- Botón de Envío -->
            <div style="text-align: center; margin-top: 30px;">
                <button type="submit" class="btn-submit">
                    💾 Registrar Moto
                </button>
            </div>
        </form>
    </div>
</div>

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.getElementById('registroForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Crear FormData con los datos del formulario
    const formData = new FormData(this);
    
    // Enviar solicitud AJAX
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
            // Mostrar SweetAlert de éxito
            Swal.fire({
                title: '¡Éxito!',
                text: data.message,
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Recargar la página para mostrar el formulario limpio
                    window.location.reload();
                }
            });
        } else {
            // Mostrar SweetAlert de error
            Swal.fire({
                title: 'Error',
                text: data.message,
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        }
    })
    .catch(error => {
        // Mostrar SweetAlert de error en caso de fallo de red
        Swal.fire({
            title: 'Error',
            text: 'Ocurrió un error al procesar la solicitud: ' + error.message,
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
    });
});
</script>
