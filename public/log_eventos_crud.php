<?php
// ================== Conexión ==================
include __DIR__ . "/../src/conexion.php"; // Conexión a la base de datos
header('Content-Type: application/json'); // Respuesta en JSON

// ====================== FUNCIONES CRUD LOG_eventos======================

// Listar todos los logs
function listarLogs($conn) {
    $sql = "SELECT * FROM log_eventos ORDER BY id_log DESC";
    $result = $conn->query($sql);
    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    return $logs;
}

// Obtener un log por ID
function obtenerLog($conn, $id) {
    $id = intval($id);
    $sql = "SELECT * FROM log_eventos WHERE id_log = $id LIMIT 1";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Agregar un nuevo log
function agregarLog($conn, $id_usuario, $nombre_usuario, $accion) {
    $stmt = $conn->prepare(
        "INSERT INTO log_eventos (id_usuario, nombre_usuario, accion, fecha_ingresada, hora_ingresada)
         VALUES (?, ?, ?, CURDATE(), CURTIME())"
    );
    $stmt->bind_param("iss", $id_usuario, $nombre_usuario, $accion);
    return $stmt->execute();
}

// Eliminar un log
function eliminarLog($conn, $id) {
    $id = intval($id);
    $sql = "DELETE FROM log_eventos WHERE id_log = $id";
    return $conn->query($sql);
}

// ================== ACCIONES CRUD ==================
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'listar':
        echo json_encode(listarLogs($conn));
        break;

    case 'obtener':
        $id = intval($_GET['id'] ?? 0);
        echo json_encode(obtenerLog($conn, $id));
        break;

    case 'agregar':
        $data = json_decode(file_get_contents('php://input'), true);
        $success = agregarLog(
            $conn,
            intval($data['id_usuario'] ?? 0),
            $data['nombre_usuario'] ?? 'Usuario',
            $data['accion'] ?? 'login'
        );
        echo json_encode(['success' => $success]);
        break;

    case 'eliminar':
        $id = intval($_GET['id'] ?? 0);
        $success = eliminarLog($conn, $id);
        echo json_encode(['success' => $success]);
        break;

    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
?>
