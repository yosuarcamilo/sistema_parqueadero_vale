<?php
// Configuración de la impresora térmica POS-80-Series
return [
    'nombre_impresora' => 'POS-80-Series',
    'perfil_impresora' => 'default',
    'ancho_caracteres' => 42, // Caracteres por línea para impresora térmica de 80mm
    'ruta_dll_windows' => 'C:\\Windows\\System32\\php_printer.dll', // Ruta de la DLL si se usa
    'puerto' => 'USB001' // Puerto asignado a la impresora
];