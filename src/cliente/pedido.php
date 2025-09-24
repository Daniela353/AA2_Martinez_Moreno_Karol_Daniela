<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include __DIR__ . "/../conexion.php";

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$id_usuario = $_SESSION['id_usuario'] ?? null;

if (!$id_usuario) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}
$id_usuario = intval($id_usuario);

switch ($action) {

    case 'listar':
        $sql = "SELECT p.id_pedido, p.fecha_orden, p.estado, SUM(dp.cantidad * dp.precio_unitario) AS total
                FROM pedido p
                JOIN detalle_pedido dp ON p.id_pedido = dp.id_pedido
                WHERE p.id_usuario = ?
                GROUP BY p.id_pedido
                ORDER BY p.fecha_orden DESC";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo json_encode(['success' => false, 'error' => 'Error al preparar la consulta: ' . $conn->error]);
            exit;
        }

        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        $pedidos = [];
        while ($row = $result->fetch_assoc()) {
            $pedidos[] = $row;
        }

        echo json_encode(['success' => true, 'pedidos' => $pedidos]);
        $stmt->close();
        break;

    case 'crear':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            echo json_encode(['success' => false, 'error' => 'JSON inválido']);
            exit;
        }

        $productos = $input['productos'] ?? [];
        if (empty($productos)) {
            echo json_encode(['success' => false, 'error' => 'Carrito vacío']);
            exit;
        }

        $conn->begin_transaction();

        try {
            $stmtPedido = $conn->prepare("INSERT INTO pedido (id_usuario, estado, fecha_orden, total) VALUES (?, 'pendiente', CURDATE(), ?)");
            $total = 0;
            foreach ($productos as $p) {
                $precio = floatval($p['precio_final'] ?? $p['precio'] ?? 0);
                $cantidad = intval($p['cantidad'] ?? 1);
                $total += $precio * $cantidad;
            }

            $stmtPedido->bind_param("id", $id_usuario, $total);
            if (!$stmtPedido->execute()) {
                throw new Exception('Error al crear el pedido: ' . $stmtPedido->error);
            }
            $id_pedido = $stmtPedido->insert_id;
            $stmtPedido->close();

            $stmtDetalle = $conn->prepare("INSERT INTO detalle_pedido (id_pedido, id_dispositivo, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
            foreach ($productos as $p) {
                $precio = floatval($p['precio_final'] ?? $p['precio'] ?? 0);
                $cantidad = intval($p['cantidad'] ?? 1);
                $id_disp = intval($p['id_dispositivo'] ?? 0);

                if ($id_disp <= 0) continue;

                $stmtDetalle->bind_param("iiid", $id_pedido, $id_disp, $cantidad, $precio);
                if (!$stmtDetalle->execute()) {
                    throw new Exception('Error al insertar detalle: ' . $stmtDetalle->error);
                }
            }
            $stmtDetalle->close();

            $stmtDelete = $conn->prepare("DELETE FROM carrito_compra WHERE id_usuario = ?");
            if (!$stmtDelete) {
                throw new Exception('Error al preparar la consulta de eliminación del carrito: ' . $conn->error);
            }
            $stmtDelete->bind_param("i", $id_usuario);
            $stmtDelete->execute();
            $stmtDelete->close();

            $conn->commit();
            echo json_encode(['success' => true, 'id_pedido' => $id_pedido]);

        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    case 'detalle':
        $id_pedido = $_GET['id_pedido'] ?? null;
        if (!$id_pedido) {
            echo json_encode(['success' => false, 'error' => 'ID de pedido no proporcionado.']);
            exit;
        }
        
        // CORRECCIÓN: Se agrega dp.id_dispositivo a la consulta
        $sql = "SELECT dp.id_dispositivo, dp.cantidad, dp.precio_unitario, d.Nombre, d.imagen, d.precio
        FROM detalle_pedido dp
        JOIN dispositivo d ON dp.id_dispositivo = d.id_dispositivo
        WHERE dp.id_pedido = ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo json_encode(['success' => false, 'error' => 'Error al preparar la consulta: ' . $conn->error]);
            exit;
        }

        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
        $result = $stmt->get_result();

        $detalles = [];
        while ($row = $result->fetch_assoc()) {
            $detalles[] = $row;
        }

        echo json_encode(['success' => true, 'detalles' => $detalles]);
        $stmt->close();
        break;
    
    case 'editar_cantidad':
        // Asegúrate de que los datos de la solicitud existan
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Método de solicitud no válido.']);
            exit;
        }
        
        // Decodifica el JSON enviado desde JavaScript
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Verifica si la decodificación fue exitosa y si los datos necesarios están presentes
        if ($input === null) {
            echo json_encode(['success' => false, 'error' => 'No se pudo decodificar el JSON.']);
            exit;
        }

        $id_pedido = $input['id_pedido'] ?? null;
        $id_dispositivo = $input['id_dispositivo'] ?? null;
        $nuevaCantidad = $input['cantidad'] ?? null;

        // Valida que los datos necesarios estén presentes y sean válidos
        if (!$id_pedido || !$id_dispositivo || $nuevaCantidad === null || $nuevaCantidad <= 0) {
            echo json_encode(['success' => false, 'error' => 'Datos de entrada inválidos.']);
            exit;
        }

        $conn->begin_transaction();
        try {
            // 1. Obtener la cantidad actual y el precio unitario del detalle del pedido
            $sql_select = "SELECT cantidad, precio_unitario FROM detalle_pedido WHERE id_pedido = ? AND id_dispositivo = ?";
            $stmt_select = $conn->prepare($sql_select);
            $stmt_select->bind_param("ii", $id_pedido, $id_dispositivo);
            $stmt_select->execute();
            $result = $stmt_select->get_result();
            if ($result->num_rows === 0) {
                throw new Exception("Detalle de pedido no encontrado.");
            }
            $row = $result->fetch_assoc();
            $cantidad_anterior = $row['cantidad'];
            $precio_unitario = $row['precio_unitario'];
            $stmt_select->close();

            // 2. Actualizar la cantidad en el detalle del pedido
            $sql_update_detalle = "UPDATE detalle_pedido SET cantidad = ? WHERE id_pedido = ? AND id_dispositivo = ?";
            $stmt_update_detalle = $conn->prepare($sql_update_detalle);
            $stmt_update_detalle->bind_param("iii", $nuevaCantidad, $id_pedido, $id_dispositivo);
            if (!$stmt_update_detalle->execute()) {
                throw new Exception("Error al actualizar el detalle: " . $stmt_update_detalle->error);
            }
            $stmt_update_detalle->close();

            // 3. Actualizar el total del pedido
            $cambio_total = ($nuevaCantidad - $cantidad_anterior) * $precio_unitario;

            $sql_update_pedido_total = "UPDATE pedido SET total = total + ? WHERE id_pedido = ?";
            $stmt_update_pedido_total = $conn->prepare($sql_update_pedido_total);
            $stmt_update_pedido_total->bind_param("di", $cambio_total, $id_pedido);
            if (!$stmt_update_pedido_total->execute()) {
                throw new Exception("Error al actualizar el total del pedido: " . $stmt_update_pedido_total->error);
            }
            $stmt_update_pedido_total->close();

            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Cantidad actualizada con éxito.']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
        break; // CORRECCIÓN: Se agrega el break que faltaba

    default:
        echo json_encode(['success' => false, 'error' => 'Acción no válida']);
        break;

}
?>