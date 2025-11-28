<div id="qz-tray-container" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;">
    <h3>🖨️ Control de Impresora QZ Tray</h3>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button id="btnConectarQZ" class="btn btn-primary" style="background-color: #4CAF50; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer;">
            🔌 Conectar Impresora
        </button>
        <button id="btnImprimirTest" class="btn btn-secondary" style="background-color: #2196F3; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer;" disabled>
            📄 Imprimir Ticket de Prueba
        </button>
        <div id="qz-status" style="padding: 10px; font-weight: bold;">
            <span id="status-text">🟡 Sin conexión</span>
        </div>
    </div>
</div>

<script src="../imprimir/qz-tray.js"></script>
<script>
// Esperar a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si QZ Tray está disponible
    if (typeof qz === 'undefined') {
        console.error("QZ Tray library not found");
        return;
    }

    // Variables para controlar el estado de QZ Tray
    let qzConnected = false;
    let printer = null;

    // Elementos del DOM (verificar que existen antes de usarlos)
    const btnConectarQZ = document.getElementById('btnConectarQZ');
    const btnImprimirTest = document.getElementById('btnImprimirTest');
    const statusText = document.getElementById('status-text');

    // Verificar que los elementos existen
    if (!btnConectarQZ || !btnImprimirTest || !statusText) {
        console.warn("Algunos elementos de QZ Tray no se encontraron en el DOM");
        return;
    }

    // Configurar QZ Tray para evitar problemas de certificado
    if (typeof qz !== 'undefined') {
        // Configurar para no requerir certificado firmado
        qz.security.setCertificatePromise(function(resolve, reject) {
            // Para entornos de desarrollo, podemos resolver con null
            // En producción, se debería usar un certificado válido
            resolve(null); // No requerir certificado
        });
        
        // Configurar para no requerir firma
        qz.security.setSignaturePromise(function(toSign) {
            return function(resolve, reject) {
                resolve(null); // No requerir firma
            };
        });
    }

    // Función para actualizar el estado de conexión
    function updateConnectionStatus(connected, message = '') {
        qzConnected = connected;
        if (statusText) {
            if (connected) {
                statusText.textContent = '🟢 Conectado';
                statusText.style.color = 'green';
            } else {
                statusText.textContent = message || '🔴 Desconectado';
                statusText.style.color = 'red';
            }
        }
        
        if (btnImprimirTest) {
            btnImprimirTest.disabled = !connected;
        }
    }

    // Función para conectar con QZ Tray
    if (btnConectarQZ) {
        btnConectarQZ.onclick = function() {
            if (typeof qz === 'undefined') {
                alert("❌ QZ Tray no está disponible. Verifique que esté instalado y ejecutándose.");
                return;
            }

            // Si ya está conectado, mostrar mensaje
            if (qz.websocket.isActive()) {
                // Buscar la impresora nuevamente
                qz.printers.find("POS-80-Series")
                    .then(foundPrinter => {
                        if (!foundPrinter) {
                            // Intentar con otro nombre de impresora
                            return qz.printers.find("Eleph_Label_P1");
                        }
                        return foundPrinter;
                    })
                    .then(foundPrinter => {
                        if (foundPrinter) {
                            printer = foundPrinter;
                            window.printer = foundPrinter; // Hacer global para acceso desde otros scripts
                            console.log("Impresora encontrada:", printer);
                            alert("🟢 Impresora encontrada y lista para usar");
                        } else {
                            alert("❌ No se encontró la impresora. Verifique que esté conectada.");
                        }
                    })
                    .catch(err => {
                        console.error("Error al buscar la impresora:", err);
                        alert("❌ Error al buscar la impresora: " + err.message);
                    });
                return;
            }

            // Conectar con QZ Tray
            qz.websocket.connect()
                .then(() => {
                    console.log("Conectado correctamente con QZ Tray");
                    updateConnectionStatus(true);
                    btnConectarQZ.textContent = '🔄 Reconectar';
                    
                    // Buscar la impresora
                    return qz.printers.find("POS-80-Series");
                })
                .then(foundPrinter => {
                    if (!foundPrinter) {
                        // Intentar con otro nombre de impresora
                        return qz.printers.find("Eleph_Label_P1");
                    }
                    return foundPrinter;
                })
                .then(foundPrinter => {
                    if (foundPrinter) {
                        printer = foundPrinter;
                        window.printer = foundPrinter; // Hacer global para acceso desde otros scripts
                        console.log("Impresora encontrada:", printer);
                        alert("🟢 Conectado con QZ Tray y impresora encontrada");
                    } else {
                        alert("❌ No se encontró la impresora. Verifique que esté conectada.");
                    }
                })
                .catch(err => {
                    console.error("Error de conexión:", err);
                    updateConnectionStatus(false, '❌ Error de conexión');
                    alert("❌ Error al conectar: " + err.message);
                });
        };
    }

    // Función para imprimir ticket de prueba
    if (btnImprimirTest) {
        btnImprimirTest.onclick = function() {
            if (!qzConnected || !qz.websocket.isActive()) {
                alert("Conéctate primero con QZ Tray");
                return;
            }

            if (!printer) {
                alert("No se ha encontrado la impresora");
                return;
            }

            try {
                let config = qz.configs.create(printer);

                let data = [
                    "\x1B\x40", // reset
                    "\x1B\x61\x01", // center align
                    "\x1B\x45\x01", // bold on
                    "\x1D\x21\x11", // double height and width
                    "PARQUEADERO V.S\n",
                    "\x1B\x45\x00", // bold off
                    "\x1D\x21\x00", // normal size
                    "**************************\n",
                    "TICKET DE PRUEBA\n",
                    "**************************\n",
                    "\x1B\x61\x00", // left align
                    "\n",
                    "Fecha/Hora: " + new Date().toLocaleString() + "\n",
                    "Prueba de conexión QZ Tray\n",
                    "\n",
                    "\x1B\x61\x01", // center align
                    "**************************\n",
                    "GRACIAS POR SU VISITA\n",
                    "**************************\n",
                    "\n\n\n\n\n", // feed paper
                    "\x1D\x56\x01"   // cut paper
                ];

                qz.print(config, data)
                    .then(() => {
                        console.log("Ticket de prueba impreso correctamente");
                        alert("🟢 TICKET DE PRUEBA IMPRESO");
                    })
                    .catch(err => {
                        console.error("Error al imprimir:", err);
                        alert("❌ ERROR AL IMPRIMIR: " + err.message);
                    });
            } catch (err) {
                console.error("Error general:", err);
                alert("❌ ERROR GENERAL: " + err.message);
            }
        };
    }

    // Verificar conexión al cargar la página
    // Intentar conectar automáticamente si QZ Tray está disponible
    setTimeout(function() {
        if (typeof qz !== 'undefined' && !qz.websocket.isActive()) {
            qz.websocket.connect()
                .then(() => {
                    console.log("Conectado automáticamente con QZ Tray");
                    updateConnectionStatus(true);
                    if (btnConectarQZ) {
                        btnConectarQZ.textContent = '🔄 Reconectar';
                    }
                    
                    // Buscar la impresora automáticamente
                    return qz.printers.find("POS-80-Series");
                })
                .then(foundPrinter => {
                    if (!foundPrinter) {
                        // Intentar con otro nombre de impresora
                        return qz.printers.find("Eleph_Label_P1");
                    }
                    return foundPrinter;
                })
                .then(foundPrinter => {
                    if (foundPrinter) {
                        printer = foundPrinter;
                        window.printer = foundPrinter; // Hacer global para acceso desde otros scripts
                        console.log("Impresora encontrada automáticamente:", printer);
                    }
                })
                .catch(err => {
                    console.warn("No se pudo conectar automáticamente:", err);
                });
        }
    }, 1000); // Esperar 1 segundo para que se cargue completamente
});
</script>