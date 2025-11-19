<?php
require_once __DIR__ . '/vendor/autoload.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\CapabilityProfile;

class ImpresionTicket {
    private $connector;
    private $printer;
    private $config;
    
    public function __construct() {
        // Cargar configuración
        $this->config = require_once __DIR__ . '/../config/impresora.php';
        
        try {
            // Conectar a la impresora
            $this->connector = new WindowsPrintConnector($this->config['nombre_impresora']);
            $profile = CapabilityProfile::load($this->config['perfil_impresora']);
            $this->printer = new Printer($this->connector, $profile);
        } catch (Exception $e) {
            error_log("Error al conectar con la impresora: " . $e->getMessage());
            // En caso de error, usar un conector de archivo para pruebas
            $this->connector = new FilePrintConnector("php://stdout");
            $this->printer = new Printer($this->connector);
        }
    }
    
    public function imprimirTicketEntrada($datos) {
        try {
            // Encabezado del ticket
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->setTextSize(2, 2);
            $this->printer->text("PARQUEADERO V.S\n");
            $this->printer->setTextSize(1, 1);
            $this->printer->text("************************\n");
            $this->printer->text("TICKET DE ENTRADA\n");
            $this->printer->text("************************\n");
            $this->printer->feed();
            
            // Información de la moto
            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printer->text("FECHA/HORA: " . date('d/m/Y H:i:s') . "\n");
            $this->printer->text("PLACA: " . $datos['placa'] . "\n");
            $this->printer->text("MARCA: " . $datos['marca'] . "\n");
            $this->printer->text("MODELO: " . $datos['modelo'] . "\n");
            $this->printer->text("COLOR: " . $datos['color'] . "\n");
            $this->printer->feed();
            
            // Información del propietario
            $this->printer->text("PROPIETARIO: " . $datos['propietario'] . "\n");
            $this->printer->text("TELEFONO: " . $datos['telefono'] . "\n");
            $this->printer->text("DIRECCION: " . $datos['direccion'] . "\n");
            $this->printer->feed();
            
            // Información adicional
            $this->printer->text("ID REGISTRO: " . $datos['id_registro'] . "\n");
            $this->printer->feed();
            
            // Mensaje de agradecimiento
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text("************************\n");
            $this->printer->text("GRACIAS POR SU VISITA\n");
            $this->printer->text("************************\n");
            
            // Cortar papel
            $this->printer->cut();
            
            // Cerrar conexión
            $this->printer->close();
            
            return true;
        } catch (Exception $e) {
            error_log("Error al imprimir ticket de entrada: " . $e->getMessage());
            return false;
        }
    }
    
    public function imprimirTicketSalida($datos) {
        try {
            // Encabezado del ticket
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->setTextSize(2, 2);
            $this->printer->text("PARQUEADERO V.S\n");
            $this->printer->setTextSize(1, 1);
            $this->printer->text("************************\n");
            $this->printer->text("TICKET DE SALIDA\n");
            $this->printer->text("************************\n");
            $this->printer->feed();
            
            // Información de la moto
            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printer->text("FECHA/HORA ENTRADA: " . $datos['fecha_entrada'] . "\n");
            $this->printer->text("FECHA/HORA SALIDA: " . date('d/m/Y H:i:s') . "\n");
            $this->printer->text("TIEMPO: " . $datos['tiempo'] . "\n");
            $this->printer->feed();
            
            $this->printer->text("PLACA: " . $datos['placa'] . "\n");
            $this->printer->text("MARCA: " . $datos['marca'] . "\n");
            $this->printer->text("MODELO: " . $datos['modelo'] . "\n");
            $this->printer->feed();
            
            // Información del propietario
            $this->printer->text("PROPIETARIO: " . $datos['propietario'] . "\n");
            $this->printer->feed();
            
            // Información de pago
            $this->printer->text("METODO DE PAGO: " . strtoupper($datos['metodo_pago']) . "\n");
            $this->printer->setTextSize(2, 2);
            $this->printer->text("TOTAL A PAGAR: $" . number_format($datos['monto'], 0, ',', '.') . "\n");
            $this->printer->setTextSize(1, 1);
            $this->printer->feed();
            
            // Información adicional
            $this->printer->text("ID PAGO: " . $datos['id_pago'] . "\n");
            $this->printer->text("ID REGISTRO: " . $datos['id_registro'] . "\n");
            $this->printer->feed();
            
            // Mensaje de agradecimiento
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text("************************\n");
            $this->printer->text("GRACIAS POR SU VISITA\n");
            $this->printer->text("************************\n");
            
            // Cortar papel
            $this->printer->cut();
            
            // Cerrar conexión
            $this->printer->close();
            
            return true;
        } catch (Exception $e) {
            error_log("Error al imprimir ticket de salida: " . $e->getMessage());
            return false;
        }
    }
    
    public function __destruct() {
        if ($this->printer) {
            try {
                $this->printer->close();
            } catch (Exception $e) {
                // Ignorar errores al cerrar
            }
        }
    }
}