<?php
require_once 'librerias/vendor/autoload.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\CapabilityProfile;

echo "Test de conexión con impresora POS-80-Series\n";
echo "==========================================\n\n";

// Cargar configuración
$config = require_once 'config/impresora.php';
echo "Configuración de impresora:\n";
print_r($config);
echo "\n";

try {
    echo "1. Intentando conexión SMB con WindowsPrintConnector...\n";
    $connector = new WindowsPrintConnector("smb://localhost/" . $config['puerto']);
    echo "   ✓ Conexión SMB establecida\n";
    
    $profile = CapabilityProfile::load($config['perfil_impresora']);
    $printer = new Printer($connector, $profile);
    echo "   ✓ Impresora inicializada\n";
    
    // Enviar un texto de prueba
    $printer->initialize();
    $printer->text("Test de conexión - " . date('Y-m-d H:i:s') . "\n");
    $printer->cut();
    $printer->close();
    
    echo "   ✓ Texto de prueba enviado correctamente\n";
    echo "   ✓ Ticket impreso (verifique físicamente)\n";
    
} catch (Exception $e) {
    echo "   ✗ Error en conexión SMB: " . $e->getMessage() . "\n";
    
    try {
        echo "\n2. Intentando conexión directa por puerto...\n";
        $connector = new FilePrintConnector($config['puerto']);
        echo "   ✓ Conexión directa establecida\n";
        
        $profile = CapabilityProfile::load($config['perfil_impresora']);
        $printer = new Printer($connector, $profile);
        echo "   ✓ Impresora inicializada\n";
        
        // Enviar un texto de prueba
        $printer->initialize();
        $printer->text("Test de conexión directa - " . date('Y-m-d H:i:s') . "\n");
        $printer->cut();
        $printer->close();
        
        echo "   ✓ Texto de prueba enviado correctamente\n";
        echo "   ✓ Ticket impreso (verifique físicamente)\n";
        
    } catch (Exception $e2) {
        echo "   ✗ Error en conexión directa: " . $e2->getMessage() . "\n";
        
        echo "\n3. Usando salida estándar como fallback...\n";
        try {
            $connector = new FilePrintConnector("php://stdout");
            $printer = new Printer($connector);
            $printer->initialize();
            $printer->text("Test de fallback - " . date('Y-m-d H:i:s') . "\n");
            $printer->cut();
            $printer->close();
            echo "   ✓ Texto de prueba enviado a salida estándar\n";
        } catch (Exception $e3) {
            echo "   ✗ Error en fallback: " . $e3->getMessage() . "\n";
        }
    }
}

echo "\nTest de conexión completado.\n";
?>