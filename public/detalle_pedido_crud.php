<?php
// ================== Conexión ==================
include __DIR__ . "/../src/conexion.php"; // Conexión a la base de datos
header('Content-Type: application/json'); // Respuesta en JSON


// ====================== FUNCIONES CRUD DETALLE PEDIDO ======================

// Listar todos los detalles o por pedido
function listarDetallePedido($conn, $id_pedido = null) {
    $sql = "SELECT dp.id_detalle, dp.id_pedido, d.Nombre AS Dispositivo, dp.cantidad, dp.precio_unitario
            FROM detalle_pedido dp
            JOIN dispositivo d ON dp.id_dispositivo = d.id_dispositivo";
    if ($id_pedido) {
        $id_pedido = intval($id_pedido);
        $sql .= " WHERE dp.id_pedido = $id_pedido";
    }
    $result = $conn->query($sql);
    $detalles = [];
    while ($row = $result->fetch_assoc()) $detalles[] = $row;
    return $detalles;
}

// Obtener un detalle por ID
function obtenerDetalle($conn, $id_detalle) {
    $id_detalle = intval($id_detalle);
    $sql = "SELECT dp.id_detalle, dp.id_pedido, d.Nombre AS Dispositivo, dp.cantidad, dp.precio_unitario
            FROM detalle_pedido dp
            JOIN dispositivo d ON dp.id_dispositivo = d.id_dispositivo
            WHERE dp.id_detalle = $id_detalle LIMIT 1";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Agregar un nuevo detalle de pedido
function agregarDetalle($conn, $id_pedido, $id_dispositivo, $cantidad, $precio_unitario) {
    $stmt = $conn->prepare("INSERT INTO detalle_pedido (id_pedido, id_dispositivo, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $id_pedido, $id_dispositivo, $cantidad, $precio_unitario);
    return $stmt->execute();
}

// Editar un detalle existente
function editarDetalle($conn, $id_detalle, $id_dispositivo, $cantidad, $precio_unitario) {
    $stmt = $conn->prepare("UPDATE detalle_pedido SET id_dispositivo=?, cantidad=?, precio_unitario=? WHERE id_detalle=?");
    $stmt->bind_param("iiid", $id_dispositivo, $cantidad, $precio_unitario, $id_detalle);
    return $stmt->execute();
}

// Eliminar un detalle
function eliminarDetalle($conn, $id_detalle) {
    $id_detalle = intval($id_detalle);
    return $conn->query("DELETE FROM detalle_pedido WHERE id_detalle = $id_detalle");
}

// ================== ACCIONES CRUD ==================
$action = $_GET['action'] ?? '';

switch($action) {
    case 'listar':
        $id_pedido = $_GET['id_pedido'] ?? null;
        echo json_encode(listarDetallePedido($conn, $id_pedido));
        break;

    case 'obtener':
        $id_detalle = intval($_GET['id'] ?? 0);
        echo json_encode(obtenerDetalle($conn, $id_detalle));
        break;

    case 'agregar':
        $data = json_decode(file_get_contents('php://input'), true);
        $success = agregarDetalle(
            $conn,
            intval($data['id_pedido'] ?? 0),
            intval($data['id_dispositivo'] ?? 0),
            intval($data['cantidad'] ?? 1),
            floatval($data['precio_unitario'] ?? 0)
        );
        echo json_encode(['success' => $success]);
        break;

    case 'editar':
        $id_detalle = intval($_GET['id'] ?? 0);
        $data = json_decode(file_get_contents('php://input'), true);
        $success = editarDetalle(
            $conn,
            $id_detalle,
            intval($data['id_dispositivo'] ?? 0),
            intval($data['cantidad'] ?? 1),
            floatval($data['precio_unitario'] ?? 0)
        );
        echo json_encode(['success' => $success]);
        break;

    case 'eliminar':
        $id_detalle = intval($_GET['id'] ?? 0);
        $success = eliminarDetalle($conn, $id_detalle);
        echo json_encode(['success' => $success]);
        break;

    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
?>
?>
