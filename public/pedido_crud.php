<?php
// ================== Conexión ==================
include __DIR__ . "/../src/conexion.php"; // Conexión a la base de datos
header('Content-Type: application/json'); // Respuesta en JSON

// ================== FUNCIONES CRUD PEDIDOS ==================

// Listar todos los pedidos
// ================== FUNCIONES ==================
function listarPedidos($conn) {
    $result = $conn->query("SELECT * FROM pedido");
    $pedidos = [];
    while($row = $result->fetch_assoc()) $pedidos[] = $row;
    return $pedidos;
}

function obtenerPedido($conn, $id) {
    $id = intval($id);
    $result = $conn->query("SELECT * FROM pedido WHERE id_pedido=$id LIMIT 1");
    return $result->fetch_assoc();
}

function usuarioExiste($conn, $id_usuario) {
    $id_usuario = intval($id_usuario);
    $result = $conn->query("SELECT * FROM usuario WHERE id_usuario=$id_usuario AND estado='activo' LIMIT 1");
    return $result->num_rows > 0;
}

function agregarPedido($conn, $id_usuario, $total, $estado, $fecha_orden) {
    $stmt = $conn->prepare("INSERT INTO pedido (id_usuario, total, estado, fecha_orden) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idss", $id_usuario, $total, $estado, $fecha_orden);
    return $stmt->execute();
}

function editarPedido($conn, $id, $id_usuario, $total, $estado, $fecha_orden) {
    $stmt = $conn->prepare("UPDATE pedido SET id_usuario=?, total=?, estado=?, fecha_orden=? WHERE id_pedido=?");
    $stmt->bind_param("idssi", $id_usuario, $total, $estado, $fecha_orden, $id);
    return $stmt->execute();
}

function eliminarPedido($conn, $id) {
    $id = intval($id);
    return $conn->query("DELETE FROM pedido WHERE id_pedido=$id");
}

// ================== CRUD ==================
$action = $_GET['action'] ?? '';

switch($action) {
    case 'listar':
        echo json_encode(listarPedidos($conn));
        break;

    case 'obtener':
        $id = intval($_GET['id'] ?? 0);
        echo json_encode(obtenerPedido($conn, $id));
        break;

    
    case 'agregar':
        $data = json_decode(file_get_contents('php://input'), true);
        $id_usuario = intval($data['id_usuario'] ?? 0);

        if (!usuarioExiste($conn, $id_usuario)) {
        echo json_encode(['success' => false, 'error' => 'Usuario no válido']);
        exit;
    }

        $success = agregarPedido(
        $conn,
        $id_usuario,
        floatval($data['total'] ?? 0),
        $data['estado'] ?? 'pendiente',
        $data['fecha_orden'] ?? date('Y-m-d')
    );

    echo json_encode(['success' => $success]);
    break;

    case 'editar':
        $id = intval($_GET['id'] ?? 0);
        $data = json_decode(file_get_contents('php://input'), true);
        $success = editarPedido(
            $conn,
            $id,
            intval($data['id_usuario'] ?? 0),
            floatval($data['total'] ?? 0),
            $data['estado'] ?? 'pendiente',
            $data['fecha_orden'] ?? date('Y-m-d')
        );
        echo json_encode(['success' => $success]);
        break;

    case 'eliminar':
        $id = intval($_GET['id'] ?? 0);
        $success = eliminarPedido($conn, $id);
        echo json_encode(['success' => $success]);
        break;

    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
?>

