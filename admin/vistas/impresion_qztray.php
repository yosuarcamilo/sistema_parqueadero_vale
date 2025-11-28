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
    // Variables para controlar el estado de QZ Tray
    let qzConnected = false;
    let printer = null;

    // Elementos del DOM
    const btnConectarQZ = document.getElementById('btnConectarQZ');
    const btnImprimirTest = document.getElementById('btnImprimirTest');
    const statusText = document.getElementById('status-text');

    // Configurar QZ Tray para evitar problemas de certificado
    if (typeof qz !== 'undefined') {
        // Configurar para no requerir certificado firmado
        qz.security.setCertificatePromise(function(resolve, reject) {
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
        if (connected) {
            statusText.textContent = '🟢 Conectado';
            statusText.style.color = 'green';
            btnImprimirTest.disabled = false;
        } else {
            statusText.textContent = message || '🔴 Desconectado';
            statusText.style.color = 'red';
            btnImprimirTest.disabled = true;
        }
    }

    // Función para conectar con QZ Tray
    btnConectarQZ.onclick = function() {
        if (typeof qz === 'undefined') {
            alert("❌ QZ Tray no está disponible. Verifique que esté instalado y ejecutándose.");
            return;
        }

        if (!qz.websocket.isActive()) {
            qz.websocket.connect()
                .then(() => {
                    console.log("Conectado correctamente con QZ Tray");
                    updateConnectionStatus(true);
                    btnConectarQZ.textContent = '🔄 Reconectar';
                    
                    // Buscar la impresora
                    return qz.printers.find("POS-80-Series");
                })
                .then(foundPrinter => {
                    if (foundPrinter) {
                        printer = foundPrinter;
                        window.printer = foundPrinter; // Hacer global para acceso desde otros scripts
                        console.log("Impresora encontrada:", printer);
                        alert("🟢 Conectado con QZ Tray y impresora encontrada");
                    } else {
                        throw new Error("Impresora POS-80-Series no encontrada");
                    }
                })
                .catch(err => {
                    console.error("Error de conexión:", err);
                    updateConnectionStatus(false, '❌ Error de conexión');
                    alert("❌ Error al conectar: " + err.message);
                });
        } else {
            // Reconectar si ya está conectado
            qz.websocket.disconnect()
                .then(() => {
                    return qz.websocket.connect();
                })
                .then(() => {
                    console.log("Reconectado correctamente con QZ Tray");
                    updateConnectionStatus(true);
                    
                    // Buscar la impresora nuevamente
                    return qz.printers.find("POS-80-Series");
                })
                .then(foundPrinter => {
                    if (foundPrinter) {
                        printer = foundPrinter;
                        window.printer = foundPrinter; // Hacer global para acceso desde otros scripts
                        console.log("Impresora encontrada:", printer);
                        alert("🟢 Reconectado con QZ Tray y impresora encontrada");
                    } else {
                        throw new Error("Impresora POS-80-Series no encontrada");
                    }
                })
                .catch(err => {
                    console.error("Error al reconectar:", err);
                    updateConnectionStatus(false, '❌ Error de reconexión');
                    alert("❌ Error al reconectar: " + err.message);
                });
        }
    };

    // Función para imprimir ticket de prueba
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

    // Verificar conexión al cargar la página
    window.addEventListener('load', function() {
        if (typeof qz !== 'undefined') {
            console.log("QZ Tray library loaded");
            // Configurar para no requerir certificado
            qz.security.setCertificatePromise(function(resolve, reject) {
                resolve(null);
            });
            
            // Configurar para no requerir firma
            qz.security.setSignaturePromise(function(toSign) {
                return function(resolve, reject) {
                    resolve(null);
                };
            });
            
            if (qz.websocket.isActive()) {
                updateConnectionStatus(true);
                btnConectarQZ.textContent = '🔄 Reconectar';
                
                // Buscar la impresora automáticamente
                qz.printers.find("POS-80-Series")
                    .then(foundPrinter => {
                        if (foundPrinter) {
                            printer = foundPrinter;
                            window.printer = foundPrinter; // Hacer global para acceso desde otros scripts
                            console.log("Impresora encontrada automáticamente:", printer);
                        }
                    })
                    .catch(err => {
                        console.warn("No se pudo encontrar la impresora automáticamente:", err);
                    });
            }
        } else {
            console.error("QZ Tray library not found");
            updateConnectionStatus(false, '❌ Librería QZ Tray no encontrada');
        }
    });
</script>