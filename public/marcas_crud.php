<?php
// ================== Conexión ==================
include __DIR__ . "/../src/conexion.php"; // Conexión a la base de datos
header('Content-Type: application/json'); // Respuesta en JSON
// ====================== FUNCIONES CRUD MARCAS ======================

// ====================== FUNCIONES CRUD MARCAS ======================

// Listar todas las marcas
function listarMarcas($conn) {
    $result = $conn->query("SELECT * FROM marca");
    $marcas = [];
    while($row = $result->fetch_assoc()) $marcas[] = $row;
    return $marcas;
}

// Obtener una marca por ID
function obtenerMarca($conn, $id) {
    $id = intval($id);
    $result = $conn->query("SELECT * FROM marca WHERE id_marca=$id LIMIT 1");
    return $result->fetch_assoc();
}

// Agregar una marca
function agregarMarca($conn, $nombre_marca, $pais_origen, $descripcion) {
    $stmt = $conn->prepare("INSERT INTO marca (nombre_marca, pais_origen, descripcion) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre_marca, $pais_origen, $descripcion);
    return $stmt->execute();
}

// Editar una marca existente
function editarMarca($conn, $id, $nombre_marca, $pais_origen, $descripcion) {
    $stmt = $conn->prepare("UPDATE marca SET nombre_marca=?, pais_origen=?, descripcion=? WHERE id_marca=?");
    $stmt->bind_param("sssi", $nombre_marca, $pais_origen, $descripcion, $id);
    return $stmt->execute();
}

// Eliminar una marca
function eliminarMarca($conn, $id) {
    $id = intval($id);
    return $conn->query("DELETE FROM marca WHERE id_marca=$id");
}

// ================== ACCIONES ==================
$action = $_GET['action'] ?? '';

switch($action) {
    case 'listar':
        echo json_encode(listarMarcas($conn));
        break;

    case 'obtener':
        $id = intval($_GET['id'] ?? 0);
        echo json_encode(obtenerMarca($conn, $id));
        break;

    case 'agregar':
        $data = json_decode(file_get_contents('php://input'), true);
        $success = agregarMarca(
            $conn,
            $data['nombre_marca'] ?? '',
            $data['pais_origen'] ?? '',
            $data['descripcion'] ?? ''
        );
        echo json_encode(['success' => $success]);
        break;

    case 'editar':
        $id = intval($_GET['id'] ?? 0);
        $data = json_decode(file_get_contents('php://input'), true);
        $success = editarMarca(
            $conn,
            $id,
            $data['nombre_marca'] ?? '',
            $data['pais_origen'] ?? '',
            $data['descripcion'] ?? ''
        );
        echo json_encode(['success' => $success]);
        break;

    case 'eliminar':
        $id = intval($_GET['id'] ?? 0);
        echo json_encode(['success' => eliminarMarca($conn, $id)]);
        break;

    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
?>