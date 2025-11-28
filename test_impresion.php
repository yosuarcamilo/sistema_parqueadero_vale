<?php
require_once 'librerias/ImpresionTicket.php';

try {
    echo "Iniciando test de impresión...\n";
    
    // Crear instancia de la clase de impresión
    $impresion = new ImpresionTicket();
    echo "✓ Clase ImpresionTicket creada correctamente\n";
    
    // Datos de prueba para ticket de entrada
    $datos_entrada = [
        'placa' => 'ABC-123',
        'marca' => 'Honda',
        'modelo' => 'CBR250',
        'color' => 'Rojo',
        'propietario' => 'Juan Pérez',
        'telefono' => '300 123 4567',
        'direccion' => 'Calle 123 #45-67',
        'id_registro' => '001'
    ];
    
    echo "Imprimiendo ticket de entrada...\n";
    $resultado = $impresion->imprimirTicketEntrada($datos_entrada);
    
    if ($resultado) {
        echo "✓ Ticket de entrada impreso correctamente\n";
    } else {
        echo "✗ Error al imprimir ticket de entrada\n";
    }
    
    // Esperar un momento entre impresiones
    sleep(2);
    
    // Datos de prueba para ticket de salida
    $datos_salida = [
        'fecha_entrada' => date('d/m/Y H:i'),
        'tiempo' => '01:30:45',
        'placa' => 'ABC-123',
        'marca' => 'Honda',
        'modelo' => 'CBR250',
        'propietario' => 'Juan Pérez',
        'metodo_pago' => 'efectivo',
        'monto' => 6000,
        'id_pago' => '001',
        'id_registro' => '001'
    ];
    
    echo "Imprimiendo ticket de salida...\n";
    $resultado = $impresion->imprimirTicketSalida($datos_salida);
    
    if ($resultado) {
        echo "✓ Ticket de salida impreso correctamente\n";
    } else {
        echo "✗ Error al imprimir ticket de salida\n";
    }
    
    echo "Test de impresión completado.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>