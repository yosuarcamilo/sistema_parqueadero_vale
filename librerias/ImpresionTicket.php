<?php
require_once __DIR__ . '/vendor/autoload.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\CapabilityProfile;

class ImpresionTicket {
    private $config;
    
    public function __construct() {
        // Cargar configuración
        $this->config = require_once __DIR__ . '/../config/impresora.php';
    }
    
    private function crearImpresora() {
        try {
            // Usar conexión directa por puerto que hemos verificado que funciona
            $connector = new FilePrintConnector($this->config['puerto']);
            $profile = CapabilityProfile::load($this->config['perfil_impresora']);
            return new Printer($connector, $profile);
        } catch (Exception $e) {
            error_log("Error al conectar con la impresora: " . $e->getMessage());
            // En caso de error, usar un conector de archivo para pruebas
            $connector = new FilePrintConnector("php://stdout");
            return new Printer($connector);
        }
    }
    
    public function imprimirTicketEntrada($datos) {
        $printer = null;
        try {
            // Crear una nueva instancia de impresora para esta impresión
            $printer = $this->crearImpresora();
            
            // Inicializar la impresora
            $printer->initialize();
            
            // Encabezado del ticket
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(2, 2);
            $printer->text("PARQUEADERO V.S\n");
            $printer->setTextSize(1, 1);
            $printer->text(str_repeat("*", 42) . "\n");
            $printer->text("TICKET DE ENTRADA\n");
            $printer->text(str_repeat("*", 42) . "\n");
            $printer->feed();
            
            // Información de la moto
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("FECHA/HORA: " . date('d/m/Y H:i:s') . "\n");
            $printer->text("PLACA: " . $datos['placa'] . "\n");
            $printer->text("MARCA: " . $datos['marca'] . "\n");
            $printer->text("MODELO: " . $datos['modelo'] . "\n");
            $printer->text("COLOR: " . $datos['color'] . "\n");
            $printer->feed();
            
            // Información del propietario
            $printer->text("PROPIETARIO: " . $datos['propietario'] . "\n");
            $printer->text("TELEFONO: " . $datos['telefono'] . "\n");
            $printer->text("DIRECCION: " . $datos['direccion'] . "\n");
            $printer->feed();
            
            // Información adicional
            $printer->text("ID REGISTRO: " . $datos['id_registro'] . "\n");
            $printer->feed();
            
            // Mensaje de agradecimiento
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text(str_repeat("*", 42) . "\n");
            $printer->text("GRACIAS POR SU VISITA\n");
            $printer->text(str_repeat("*", 42) . "\n");
            
            // Cortar papel
            $printer->cut();
            
            return true;
        } catch (Exception $e) {
            error_log("Error al imprimir ticket de entrada: " . $e->getMessage());
            return false;
        } finally {
            // Cerrar conexión si se creó
            if ($printer) {
                try {
                    $printer->close();
                } catch (Exception $e) {
                    // Ignorar errores al cerrar
                }
            }
        }
    }
    
    public function imprimirTicketSalida($datos) {
        $printer = null;
        try {
            // Crear una nueva instancia de impresora para esta impresión
            $printer = $this->crearImpresora();
            
            // Inicializar la impresora
            $printer->initialize();
            
            // Encabezado del ticket
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(2, 2);
            $printer->text("PARQUEADERO V.S\n");
            $printer->setTextSize(1, 1);
            $printer->text(str_repeat("*", 42) . "\n");
            $printer->text("TICKET DE SALIDA\n");
            $printer->text(str_repeat("*", 42) . "\n");
            $printer->feed();
            
            // Información de la moto
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("FECHA/HORA ENTRADA: " . $datos['fecha_entrada'] . "\n");
            $printer->text("FECHA/HORA SALIDA: " . date('d/m/Y H:i:s') . "\n");
            $printer->text("TIEMPO: " . $datos['tiempo'] . "\n");
            $printer->feed();
            
            $printer->text("PLACA: " . $datos['placa'] . "\n");
            $printer->text("MARCA: " . $datos['marca'] . "\n");
            $printer->text("MODELO: " . $datos['modelo'] . "\n");
            $printer->feed();
            
            // Información del propietario
            $printer->text("PROPIETARIO: " . $datos['propietario'] . "\n");
            $printer->feed();
            
            // Información de pago
            $printer->text("METODO DE PAGO: " . strtoupper($datos['metodo_pago']) . "\n");
            $printer->setTextSize(2, 2);
            $printer->text("TOTAL A PAGAR: $" . number_format($datos['monto'], 0, ',', '.') . "\n");
            $printer->setTextSize(1, 1);
            $printer->feed();
            
            // Información adicional
            $printer->text("ID PAGO: " . $datos['id_pago'] . "\n");
            $printer->text("ID REGISTRO: " . $datos['id_registro'] . "\n");
            $printer->feed();
            
            // Mensaje de agradecimiento
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text(str_repeat("*", 42) . "\n");
            $printer->text("GRACIAS POR SU VISITA\n");
            $printer->text(str_repeat("*", 42) . "\n");
            
            // Cortar papel
            $printer->cut();
            
            return true;
        } catch (Exception $e) {
            error_log("Error al imprimir ticket de salida: " . $e->getMessage());
            return false;
        } finally {
            // Cerrar conexión si se creó
            if ($printer) {
                try {
                    $printer->close();
                } catch (Exception $e) {
                    // Ignorar errores al cerrar
                }
            }
        }
    }
}