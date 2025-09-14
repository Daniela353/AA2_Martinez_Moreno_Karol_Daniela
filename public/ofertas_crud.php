<?php
include __DIR__ . "/../src/conexion.php"; // Conexión a la base de datos 
header('Content-Type: application/json'); // Para responder en JSON
// ====================== FUNCIONES CRUD OFERTAS ======================

// Listar todas las ofertas
function listarOfertas($conn) {
    $sql = "SELECT o.id_oferta, d.Nombre AS Dispositivo, 
                   o.precio_original, o.descuento_porcentaje, o.precio_final, 
                   o.estado, o.fecha_inicio, o.fecha_fin
            FROM ofertas o
            JOIN dispositivo d ON o.id_dispositivo = d.id_dispositivo";
    $result = $conn->query($sql);
    $ofertas = [];
    while($row = $result->fetch_assoc()){
        $ofertas[] = $row;
    }
    return $ofertas;
}

// Obtener una oferta por ID
function obtenerOferta($conn, $id) {
    $id = intval($id);
    $sql = "SELECT o.id_oferta, d.Nombre AS Dispositivo, o.precio_oferta, o.fecha_inicio, o.fecha_fin
            FROM ofertas o
            JOIN dispositivo d ON o.dispositivo_id = d.id_dispositivo
            WHERE o.id_oferta = $id LIMIT 1";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Agregar una nueva oferta
function agregarOferta($conn, $id_dispositivo, $precio_original, $descuento_porcentaje, $precio_final, $estado, $fecha_inicio, $fecha_fin) {
    $stmt = $conn->prepare("INSERT INTO ofertas 
        (id_dispositivo, precio_original, descuento_porcentaje, precio_final, estado, fecha_inicio, fecha_fin) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idddsss", $id_dispositivo, $precio_original, $descuento_porcentaje, $precio_final, $estado, $fecha_inicio, $fecha_fin);
    return $stmt->execute();
}

// Editar una oferta existente
function editarOferta($conn, $id, $id_dispositivo, $precio_original, $descuento_porcentaje, $precio_final, $estado, $fecha_inicio, $fecha_fin) {
    $stmt = $conn->prepare("UPDATE ofertas SET 
        id_dispositivo=?, 
        precio_original=?, 
        descuento_porcentaje=?, 
        precio_final=?, 
        estado=?, 
        fecha_inicio=?, 
        fecha_fin=? 
        WHERE id_oferta=?");
    $stmt->bind_param("idddsssi", $id_dispositivo, $precio_original, $descuento_porcentaje, $precio_final, $estado, $fecha_inicio, $fecha_fin, $id);
    return $stmt->execute();
}

// Eliminar una oferta
function eliminarOferta($conn, $id) {
    $id = intval($id);
    $sql = "DELETE FROM ofertas WHERE id_oferta = $id";
    return $conn->query($sql);
}

// ====================== ACCIONES CRUD ======================
$action = $_GET['action'] ?? '';

switch($action) {
    case 'listar':
        echo json_encode(listarOfertas($conn));
        break;

    case 'obtener':
        $id = intval($_GET['id'] ?? 0);
        echo json_encode(obtenerOferta($conn, $id));
        break;

    case 'agregar':
        $data = json_decode(file_get_contents('php://input'), true);

        $success = agregarOferta(
        $conn,
        intval($data['id_dispositivo']),
        floatval($data['precio_original']),
        floatval($data['descuento_porcentaje']),
        floatval($data['precio_final']),
        $data['estado'],
        $data['fecha_inicio'],
        $data['fecha_fin']
    );
        echo json_encode(['success' => $success]);
        break;

    case 'editar':
        $id = intval($_GET['id'] ?? 0);
       $data = json_decode(file_get_contents('php://input'), true);

        $success = editarOferta(
        $conn,
        $id,
        intval($data['id_dispositivo']),
        floatval($data['precio_original']),
        floatval($data['descuento_porcentaje']),
        floatval($data['precio_final']),
        $data['estado'],
        $data['fecha_inicio'],
        $data['fecha_fin']
    );
        echo json_encode(['success' => $success]);
        break;

    case 'eliminar':
        $id = intval($_GET['id'] ?? 0);
        $success = eliminarOferta($conn, $id);
        echo json_encode(['success' => $success]);
        break;

    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
?>
