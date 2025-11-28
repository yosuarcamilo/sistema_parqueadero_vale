<?php
class ImpresionQZTray {
    private $config;
    
    public function __construct() {
        // Cargar configuración
        $this->config = require_once __DIR__ . '/../config/impresora.php';
    }
    
    /**
     * Genera el contenido del ticket de entrada en formato ESC/POS
     */
    public function generarTicketEntrada($datos) {
        $contenido = [
            "\x1B\x40", // Reset printer
            "\x1B\x61\x01", // Center align
            "\x1B\x45\x01", // Bold on
            "\x1D\x21\x11", // Double height and width
            "PARQUEADERO V.S\n",
            "\x1B\x45\x00", // Bold off
            "\x1D\x21\x00", // Normal size
            str_repeat("*", 42) . "\n",
            "TICKET DE ENTRADA\n",
            str_repeat("*", 42) . "\n",
            "\x1B\x61\x00", // Left align
            "\n",
            "FECHA/HORA: " . date('d/m/Y H:i:s') . "\n",
            "PLACA: " . $datos['placa'] . "\n",
            "MARCA: " . $datos['marca'] . "\n",
            "MODELO: " . $datos['modelo'] . "\n",
            "COLOR: " . $datos['color'] . "\n",
            "\n",
            "PROPIETARIO: " . $datos['propietario'] . "\n",
            "TELEFONO: " . $datos['telefono'] . "\n",
            "DIRECCION: " . $datos['direccion'] . "\n",
            "\n",
            "ID REGISTRO: " . $datos['id_registro'] . "\n",
            "\n",
            "\x1B\x61\x01", // Center align
            str_repeat("*", 42) . "\n",
            "GRACIAS POR SU VISITA\n",
            str_repeat("*", 42) . "\n",
            "\n\n\n\n\n", // Feed paper
            "\x1D\x56\x01"   // Cut paper
        ];
        
        return $contenido;
    }
    
    /**
     * Genera el contenido del ticket de salida en formato ESC/POS
     */
    public function generarTicketSalida($datos) {
        $contenido = [
            "\x1B\x40", // Reset printer
            "\x1B\x61\x01", // Center align
            "\x1B\x45\x01", // Bold on
            "\x1D\x21\x11", // Double height and width
            "PARQUEADERO V.S\n",
            "\x1B\x45\x00", // Bold off
            "\x1D\x21\x00", // Normal size
            str_repeat("*", 42) . "\n",
            "TICKET DE SALIDA\n",
            str_repeat("*", 42) . "\n",
            "\x1B\x61\x00", // Left align
            "\n",
            "FECHA/HORA ENTRADA: " . $datos['fecha_entrada'] . "\n",
            "FECHA/HORA SALIDA: " . date('d/m/Y H:i:s') . "\n",
            "TIEMPO: " . $datos['tiempo'] . "\n",
            "\n",
            "PLACA: " . $datos['placa'] . "\n",
            "MARCA: " . $datos['marca'] . "\n",
            "MODELO: " . $datos['modelo'] . "\n",
            "\n",
            "PROPIETARIO: " . $datos['propietario'] . "\n",
            "\n",
            "METODO DE PAGO: " . strtoupper($datos['metodo_pago']) . "\n",
            "\x1B\x45\x01", // Bold on
            "\x1D\x21\x11", // Double height and width
            "TOTAL A PAGAR: $" . number_format($datos['monto'], 0, ',', '.') . "\n",
            "\x1B\x45\x00", // Bold off
            "\x1D\x21\x00", // Normal size
            "\n",
            "ID PAGO: " . $datos['id_pago'] . "\n",
            "ID REGISTRO: " . $datos['id_registro'] . "\n",
            "\n",
            "\x1B\x61\x01", // Center align
            str_repeat("*", 42) . "\n",
            "GRACIAS POR SU VISITA\n",
            str_repeat("*", 42) . "\n",
            "\n\n\n\n\n", // Feed paper
            "\x1D\x56\x01"   // Cut paper
        ];
        
        return $contenido;
    }
    
    /**
     * Imprime un ticket de entrada
     */
    public function imprimirTicketEntrada($datos) {
        try {
            // Generar el contenido del ticket
            $contenido = $this->generarTicketEntrada($datos);
            
            // En un entorno real, aquí se enviaría el contenido a QZ Tray
            // Por ahora, solo registramos en el log que se intentó imprimir
            error_log("Ticket de entrada generado para ID registro: " . $datos['id_registro']);
            
            return true;
        } catch (Exception $e) {
            error_log("Error al imprimir ticket de entrada: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Imprime un ticket de salida
     */
    public function imprimirTicketSalida($datos) {
        try {
            // Generar el contenido del ticket
            $contenido = $this->generarTicketSalida($datos);
            
            // En un entorno real, aquí se enviaría el contenido a QZ Tray
            // Por ahora, solo registramos en el log que se intentó imprimir
            error_log("Ticket de salida generado para ID pago: " . $datos['id_pago']);
            
            return true;
        } catch (Exception $e) {
            error_log("Error al imprimir ticket de salida: " . $e->getMessage());
            return false;
        }
    }
}
?>