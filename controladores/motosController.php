<?php
session_start();

// Proteger ruta
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

require_once __DIR__ . '/../db/conexion.php';
require_once __DIR__ . '/../librerias/ImpresionTicket.php';
require_once __DIR__ . '/../librerias/ImpresionQZTray.php';

function redirect_with($type, $message) {
    $_SESSION[$type] = $message;
    // Conservar parámetros de búsqueda y página si vinieron en REFERER
    $query = 'vista=gestion_motos';
    if (!empty($_SERVER['HTTP_REFERER'])) {
        $url = parse_url($_SERVER['HTTP_REFERER']);
        if (!empty($url['query'])) {
            parse_str($url['query'], $params);
            if (isset($params['q'])) { $query .= '&q=' . urlencode($params['q']); }
            if (isset($params['page'])) { $query .= '&page=' . (int)$params['page']; }
            if (isset($params['edit'])) { /* omit edit on redirect */ }
        }
    }
    header('Location: ../admin/index.php?' . $query);
    exit();
}

// Agregar encabezado para JSON cuando sea necesario
function send_json_response($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Verificar si es una solicitud AJAX
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        send_json_response(['status' => 'error', 'message' => 'Solicitud inválida.']);
    }
    redirect_with('mensaje_error', 'Solicitud inválida.');
}

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

// Verificar si es una solicitud AJAX
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

try {
    if ($accion === 'crear') {
        $placa  = isset($_POST['placa']) ? trim($_POST['placa']) : '';
        $marca  = isset($_POST['marca']) ? trim($_POST['marca']) : '';
        $modelo = isset($_POST['modelo']) ? trim($_POST['modelo']) : '';
        $color  = isset($_POST['color']) ? trim($_POST['color']) : '';
        $nombre_propietario = isset($_POST['nombre_propietario']) ? trim($_POST['nombre_propietario']) : '';
        $telefono_propietario = isset($_POST['telefono_propietario']) ? trim($_POST['telefono_propietario']) : '';
        $direccion_propietario = isset($_POST['direccion_propietario']) ? trim($_POST['direccion_propietario']) : '';

        // Verificar placa única si viene informada
        if ($placa !== '') {
            $stmt = $conn->prepare('SELECT IdMoto FROM motos WHERE Placa = ?');
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta: ' . $conn->error);
            }
            $stmt->bind_param('s', $placa);
            if (!$stmt->execute()) {
                throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
            }
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) {
                $error_msg = 'La placa ya existe.';
                if ($is_ajax) {
                    send_json_response(['status' => 'error', 'message' => $error_msg]);
                }
                redirect_with('mensaje_error', $error_msg);
            }
            $stmt->close();
        }

        // Convertir vacíos a NULL
        $placa  = $placa  === '' ? null : $placa;
        $marca  = $marca  === '' ? null : $marca;
        $modelo = $modelo === '' ? null : $modelo;
        $color  = $color  === '' ? null : $color;
        $nombre_propietario = $nombre_propietario === '' ? null : $nombre_propietario;
        $telefono_propietario = $telefono_propietario === '' ? null : $telefono_propietario;
        $direccion_propietario = $direccion_propietario === '' ? null : $direccion_propietario;

        $stmt = $conn->prepare('INSERT INTO motos (Placa, Marca, Modelo, Color, NombrePropietario, TelefonoPropietario, DireccionPropietario) VALUES (?, ?, ?, ?, ?, ?, ?)');
        if (!$stmt) {
            throw new Exception('Error al preparar la inserción: ' . $conn->error);
        }
        $stmt->bind_param('sssssss', $placa, $marca, $modelo, $color, $nombre_propietario, $telefono_propietario, $direccion_propietario);
        if (!$stmt->execute()) {
            throw new Exception('No fue posible crear la moto: ' . $stmt->error);
        }
        $id_moto = $conn->insert_id;
        $stmt->close();
        
        $success_msg = 'Moto creada correctamente.';
        if ($is_ajax) {
            send_json_response(['status' => 'success', 'message' => $success_msg, 'id_moto' => $id_moto]);
        }
        redirect_with('mensaje_exito', $success_msg);
    }

    if ($accion === 'actualizar') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) { 
            $error_msg = 'ID inválido.';
            if ($is_ajax) {
                send_json_response(['status' => 'error', 'message' => $error_msg]);
            }
            redirect_with('mensaje_error', $error_msg);
        }

        $placa  = isset($_POST['placa']) ? trim($_POST['placa']) : '';
        $marca  = isset($_POST['marca']) ? trim($_POST['marca']) : '';
        $modelo = isset($_POST['modelo']) ? trim($_POST['modelo']) : '';
        $color  = isset($_POST['color']) ? trim($_POST['color']) : '';
        $nombre_propietario = isset($_POST['nombre_propietario']) ? trim($_POST['nombre_propietario']) : '';
        $telefono_propietario = isset($_POST['telefono_propietario']) ? trim($_POST['telefono_propietario']) : '';
        $direccion_propietario = isset($_POST['direccion_propietario']) ? trim($_POST['direccion_propietario']) : '';

        // Validar placa única si viene
        if ($placa !== '') {
            $stmt = $conn->prepare('SELECT IdMoto FROM motos WHERE Placa = ? AND IdMoto <> ?');
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta: ' . $conn->error);
            }
            $stmt->bind_param('si', $placa, $id);
            if (!$stmt->execute()) {
                throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
            }
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) {
                $error_msg = 'Otra moto ya tiene esa placa.';
                if ($is_ajax) {
                    send_json_response(['status' => 'error', 'message' => $error_msg]);
                }
                redirect_with('mensaje_error', $error_msg);
            }
            $stmt->close();
        }

        // Convertir vacíos a NULL
        $placa  = $placa  === '' ? null : $placa;
        $marca  = $marca  === '' ? null : $marca;
        $modelo = $modelo === '' ? null : $modelo;
        $color  = $color  === '' ? null : $color;
        $nombre_propietario = $nombre_propietario === '' ? null : $nombre_propietario;
        $telefono_propietario = $telefono_propietario === '' ? null : $telefono_propietario;
        $direccion_propietario = $direccion_propietario === '' ? null : $direccion_propietario;

        $stmt = $conn->prepare('UPDATE motos SET Placa = ?, Marca = ?, Modelo = ?, Color = ?, NombrePropietario = ?, TelefonoPropietario = ?, DireccionPropietario = ? WHERE IdMoto = ?');
        if (!$stmt) {
            throw new Exception('Error al preparar la actualización: ' . $conn->error);
        }
        $stmt->bind_param('sssssssi', $placa, $marca, $modelo, $color, $nombre_propietario, $telefono_propietario, $direccion_propietario, $id);
        if (!$stmt->execute()) {
            throw new Exception('No fue posible actualizar la moto: ' . $stmt->error);
        }
        $stmt->close();
        
        $success_msg = 'Moto actualizada.';
        if ($is_ajax) {
            send_json_response(['status' => 'success', 'message' => $success_msg]);
        }
        redirect_with('mensaje_exito', $success_msg);
    }

    if ($accion === 'eliminar') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) { 
            $error_msg = 'ID inválido.';
            if ($is_ajax) {
                send_json_response(['status' => 'error', 'message' => $error_msg]);
            }
            redirect_with('mensaje_error', $error_msg);
        }

        $stmt = $conn->prepare('DELETE FROM motos WHERE IdMoto = ?');
        if (!$stmt) {
            throw new Exception('Error al preparar la eliminación: ' . $conn->error);
        }
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            throw new Exception('No fue posible eliminar la moto: ' . $stmt->error);
        }
        $stmt->close();
        
        $success_msg = 'Moto eliminada correctamente.';
        if ($is_ajax) {
            send_json_response(['status' => 'success', 'message' => $success_msg]);
        }
        redirect_with('mensaje_exito', $success_msg);
    }
    
    // Nueva funcionalidad: Registrar entrada de moto
    if ($accion === 'registrar_entrada') {
        $id_moto = isset($_POST['id_moto']) ? (int)$_POST['id_moto'] : 0;
        
        if ($id_moto <= 0) {
            $error_msg = 'ID de moto inválido.';
            if ($is_ajax) {
                send_json_response(['status' => 'error', 'message' => $error_msg]);
            }
            redirect_with('mensaje_error', $error_msg);
        }
        
        // Verificar que la moto exista
        $stmt = $conn->prepare('SELECT IdMoto FROM motos WHERE IdMoto = ?');
        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $conn->error);
        }
        $stmt->bind_param('i', $id_moto);
        if (!$stmt->execute()) {
            throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
        }
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            $stmt->close();
            $error_msg = 'La moto no existe.';
            if ($is_ajax) {
                send_json_response(['status' => 'error', 'message' => $error_msg]);
            }
            redirect_with('mensaje_error', $error_msg);
        }
        $stmt->close();
        
        // Verificar que la moto no tenga ya un registro activo
        $stmt = $conn->prepare('SELECT IdRegistro FROM registros WHERE IdMoto = ? AND Estado = "activo"');
        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $conn->error);
        }
        $stmt->bind_param('i', $id_moto);
        if (!$stmt->execute()) {
            throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
        }
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $stmt->close();
            $error_msg = 'La moto ya se encuentra en el parqueadero.';
            if ($is_ajax) {
                send_json_response(['status' => 'error', 'message' => $error_msg]);
            }
            redirect_with('mensaje_error', $error_msg);
        }
        $stmt->close();
        
        // Registrar la entrada
        $fecha_hora_entrada = date('Y-m-d H:i:s');
        $stmt = $conn->prepare('INSERT INTO registros (IdMoto, FechaHoraEntrada, Estado) VALUES (?, ?, "activo")');
        if (!$stmt) {
            throw new Exception('Error al preparar el registro: ' . $conn->error);
        }
        $stmt->bind_param('is', $id_moto, $fecha_hora_entrada);
        if (!$stmt->execute()) {
            throw new Exception('No fue posible registrar la entrada: ' . $stmt->error);
        }
        $id_registro = $conn->insert_id;
        $stmt->close();
        
        // Obtener datos de la moto para el ticket
        $stmt = $conn->prepare('SELECT Placa, Marca, Modelo, Color, NombrePropietario, TelefonoPropietario, DireccionPropietario FROM motos WHERE IdMoto = ?');
        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $conn->error);
        }
        $stmt->bind_param('i', $id_moto);
        if (!$stmt->execute()) {
            throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
        }
        $res = $stmt->get_result();
        $moto = $res->fetch_assoc();
        $stmt->close();
        
        // Preparar datos para la respuesta AJAX
        $datos_ticket = null;
        if ($moto) {
            $datos_ticket = [
                'tipo' => 'entrada',
                'placa' => $moto['Placa'] ?? 'N/A',
                'marca' => $moto['Marca'] ?? 'N/A',
                'modelo' => $moto['Modelo'] ?? 'N/A',
                'color' => $moto['Color'] ?? 'N/A',
                'propietario' => $moto['NombrePropietario'] ?? 'N/A',
                'telefono' => $moto['TelefonoPropietario'] ?? 'N/A',
                'direccion' => $moto['DireccionPropietario'] ?? 'N/A',
                'id_registro' => $id_registro,
                'fecha_hora' => date('d/m/Y H:i:s')
            ];
        }
        
        $success_msg = 'Entrada registrada correctamente.';
        if ($is_ajax) {
            send_json_response([
                'status' => 'success', 
                'message' => $success_msg,
                'ticket_data' => $datos_ticket
            ]);
        }
        redirect_with('mensaje_exito', $success_msg);
    }
    
    // Nueva funcionalidad: Registrar salida de moto
    if ($accion === 'registrar_salida') {
        $id_registro = isset($_POST['id_registro']) ? (int)$_POST['id_registro'] : 0;
        $metodo_pago = isset($_POST['metodo_pago']) ? trim($_POST['metodo_pago']) : '';
        $monto = isset($_POST['monto']) ? (float)$_POST['monto'] : 0;
        
        if ($id_registro <= 0) {
            $error_msg = 'ID de registro inválido.';
            if ($is_ajax) {
                send_json_response(['status' => 'error', 'message' => $error_msg]);
            }
            redirect_with('mensaje_error', $error_msg);
        }
        
        if (empty($metodo_pago)) {
            $error_msg = 'Método de pago es requerido.';
            if ($is_ajax) {
                send_json_response(['status' => 'error', 'message' => $error_msg]);
            }
            redirect_with('mensaje_error', $error_msg);
        }
        
        if ($monto <= 0) {
            $error_msg = 'Monto debe ser mayor a cero.';
            if ($is_ajax) {
                send_json_response(['status' => 'error', 'message' => $error_msg]);
            }
            redirect_with('mensaje_error', $error_msg);
        }
        
        // Verificar que el registro exista y esté activo
        $stmt = $conn->prepare('SELECT IdRegistro FROM registros WHERE IdRegistro = ? AND Estado = "activo"');
        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $conn->error);
        }
        $stmt->bind_param('i', $id_registro);
        if (!$stmt->execute()) {
            throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
        }
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            $stmt->close();
            $error_msg = 'El registro no existe o ya ha sido cerrado.';
            if ($is_ajax) {
                send_json_response(['status' => 'error', 'message' => $error_msg]);
            }
            redirect_with('mensaje_error', $error_msg);
        }
        $stmt->close();
        
        // Registrar la salida
        $fecha_hora_salida = date('Y-m-d H:i:s');
        $stmt = $conn->prepare('UPDATE registros SET FechaHoraSalida = ?, Estado = "inactivo" WHERE IdRegistro = ?');
        if (!$stmt) {
            throw new Exception('Error al preparar la actualización: ' . $conn->error);
        }
        $stmt->bind_param('si', $fecha_hora_salida, $id_registro);
        if (!$stmt->execute()) {
            throw new Exception('No fue posible registrar la salida: ' . $stmt->error);
        }
        $stmt->close();
        
        // Registrar el pago
        $stmt = $conn->prepare('INSERT INTO pagos (IdRegistro, Monto, MetodoPago) VALUES (?, ?, ?)');
        if (!$stmt) {
            throw new Exception('Error al preparar el pago: ' . $conn->error);
        }
        $stmt->bind_param('ids', $id_registro, $monto, $metodo_pago);
        if (!$stmt->execute()) {
            throw new Exception('No fue posible registrar el pago: ' . $stmt->error);
        }
        $id_pago = $conn->insert_id;
        $stmt->close();
        
        // Obtener datos del registro y la moto para el ticket
        $stmt = $conn->prepare('SELECT r.FechaHoraEntrada, r.FechaHoraSalida, m.Placa, m.Marca, m.Modelo, m.NombrePropietario FROM registros r JOIN motos m ON r.IdMoto = m.IdMoto WHERE r.IdRegistro = ?');
        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $conn->error);
        }
        $stmt->bind_param('i', $id_registro);
        if (!$stmt->execute()) {
            throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
        }
        $res = $stmt->get_result();
        $registro = $res->fetch_assoc();
        $stmt->close();
        
        // Calcular tiempo transcurrido
        $datos_ticket = null;
        if ($registro) {
            $entrada = new DateTime($registro['FechaHoraEntrada']);
            $salida = new DateTime($registro['FechaHoraSalida']);
            $intervalo = $entrada->diff($salida);
            $tiempo = $intervalo->format('%H:%I:%S');
            
            $datos_ticket = [
                'tipo' => 'salida',
                'fecha_entrada' => date('d/m/Y H:i', strtotime($registro['FechaHoraEntrada'])),
                'fecha_salida' => date('d/m/Y H:i', strtotime($registro['FechaHoraSalida'])),
                'tiempo' => $tiempo,
                'placa' => $registro['Placa'] ?? 'N/A',
                'marca' => $registro['Marca'] ?? 'N/A',
                'modelo' => $registro['Modelo'] ?? 'N/A',
                'propietario' => $registro['NombrePropietario'] ?? 'N/A',
                'metodo_pago' => $metodo_pago,
                'monto' => $monto,
                'id_pago' => $id_pago,
                'id_registro' => $id_registro
            ];
        }
        
        $success_msg = 'Salida y pago registrados correctamente.';
        if ($is_ajax) {
            send_json_response([
                'status' => 'success', 
                'message' => $success_msg,
                'ticket_data' => $datos_ticket
            ]);
        }
        redirect_with('mensaje_exito', $success_msg);
    }
    
    // Nueva funcionalidad: Eliminar registro de entrada
    if ($accion === 'eliminar_registro') {
        $id_registro = isset($_POST['id_registro']) ? (int)$_POST['id_registro'] : 0;
        
        if ($id_registro <= 0) {
            $error_msg = 'ID de registro inválido.';
            if ($is_ajax) {
                send_json_response(['status' => 'error', 'message' => $error_msg]);
            }
            redirect_with('mensaje_error', $error_msg);
        }
        
        // Verificar que el registro exista y esté activo
        $stmt = $conn->prepare('SELECT IdRegistro FROM registros WHERE IdRegistro = ? AND Estado = "activo"');
        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $conn->error);
        }
        $stmt->bind_param('i', $id_registro);
        if (!$stmt->execute()) {
            throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
        }
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            $stmt->close();
            $error_msg = 'El registro no existe o ya ha sido cerrado.';
            if ($is_ajax) {
                send_json_response(['status' => 'error', 'message' => $error_msg]);
            }
            redirect_with('mensaje_error', $error_msg);
        }
        $stmt->close();
        
        // Eliminar el registro
        $stmt = $conn->prepare('DELETE FROM registros WHERE IdRegistro = ?');
        if (!$stmt) {
            throw new Exception('Error al preparar la eliminación: ' . $conn->error);
        }
        $stmt->bind_param('i', $id_registro);
        if (!$stmt->execute()) {
            throw new Exception('No fue posible eliminar el registro: ' . $stmt->error);
        }
        $stmt->close();
        
        $success_msg = 'Registro eliminado correctamente.';
        if ($is_ajax) {
            send_json_response(['status' => 'success', 'message' => $success_msg]);
        }
        redirect_with('mensaje_exito', $success_msg);
    }

    $error_msg = 'Acción no reconocida.';
    if ($is_ajax) {
        send_json_response(['status' => 'error', 'message' => $error_msg]);
    }
    redirect_with('mensaje_error', $error_msg);
} catch (Throwable $e) {
    $error_msg = 'Error: ' . $e->getMessage();
    if ($is_ajax) {
        send_json_response(['status' => 'error', 'message' => $error_msg]);
    }
    redirect_with('mensaje_error', $error_msg);
}
?>