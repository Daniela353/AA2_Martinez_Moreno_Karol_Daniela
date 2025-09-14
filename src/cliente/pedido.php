<?php
session_start();
include __DIR__ . "/conexion.php";

header('Content-Type: application/json');

// Activar errores para depuración (quitar en producción)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Acción recibida por GET
$action = $_GET['action'] ?? '';
$id_usuario = $_SESSION['id_usuario'] ?? null;

// Validar sesión
if (!$id_usuario) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

// Convertir id_usuario a entero para seguridad
$id_usuario = intval($id_usuario);

switch ($action) {

    // ================= LISTAR PEDIDOS =================
    case 'listar':
        $sql = "SELECT * FROM pedido WHERE id_usuario=$id_usuario ORDER BY id_pedido DESC";
        $res = $conn->query($sql);

        if (!$res) {
            echo json_encode(['success' => false, 'error' => $conn->error]);
            exit;
        }

        $pedidos = [];
        while ($p = $res->fetch_assoc()) {
            $pedidos[] = $p;
        }

        echo json_encode(['success' => true, 'pedidos' => $pedidos]);
        break;

    // ================= CREAR PEDIDO =================
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

        // Calcular total del pedido
        $total = 0;
        foreach ($productos as $p) {
            $precio = floatval($p['precio_descuento'] ?? $p['precio'] ?? 0);
            $cantidad = intval($p['cantidad'] ?? 1);
            $total += $precio * $cantidad;
        }

        // Insertar pedido en la tabla pedido
        $stmtPedido = $conn->prepare("INSERT INTO pedido (id_usuario, total, estado, fecha_orden) VALUES (?, ?, 'pendiente', CURDATE())");
        if (!$stmtPedido) {
            echo json_encode(['success' => false, 'error' => 'Error al preparar pedido: ' . $conn->error]);
            exit;
        }
        $stmtPedido->bind_param("id", $id_usuario, $total);
        if (!$stmtPedido->execute()) {
            echo json_encode(['success' => false, 'error' => 'Error al crear el pedido: ' . $stmtPedido->error]);
            exit;
        }
        $id_pedido = $stmtPedido->insert_id;
        $stmtPedido->close();

        // Insertar detalles del pedido
        $stmtDetalle = $conn->prepare("INSERT INTO detalle_pedido (id_pedido, id_dispositivo, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        if (!$stmtDetalle) {
            echo json_encode(['success' => false, 'error' => 'Error al preparar detalle: ' . $conn->error]);
            exit;
        }

        foreach ($productos as $p) {
            $precio = floatval($p['precio_descuento'] ?? $p['precio'] ?? 0);
            $cantidad = intval($p['cantidad'] ?? 1);
            $id_disp = intval($p['id_dispositivo'] ?? 0);

            if ($id_disp <= 0) continue; // ignorar IDs inválidos

            $stmtDetalle->bind_param("iiid", $id_pedido, $id_disp, $cantidad, $precio);
            $stmtDetalle->execute();
        }
        $stmtDetalle->close();

        // Vaciar carrito
        $stmtDelete = $conn->prepare("DELETE FROM carrito_compra WHERE id_usuario = ?");
        if ($stmtDelete) {
            $stmtDelete->bind_param("i", $id_usuario);
            $stmtDelete->execute();
            $stmtDelete->close();
        }

        echo json_encode(['success' => true, 'id_pedido' => $id_pedido]);
        break;

    // ================= ACCIÓN NO VÁLIDA =================
    default:
        echo json_encode(['success' => false, 'error' => 'Acción no válida']);
        break;
}
?>
