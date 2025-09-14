<?php
// ================== Conexión ==================
include __DIR__ . "/../src/conexion.php"; // Conexión a la base de datos
header('Content-Type: application/json'); // Respuesta en JSON

// ====================== FUNCIONES CRUD COMENTARIOS ======================


// ====================== FUNCIONES CRUD COMENTARIOS ======================

// Listar todos los comentarios
function listarComentarios($conn) {
    $sql = "SELECT com.id_comentario, d.Nombre AS Dispositivo, u.Nombre AS Usuario, com.comentario, com.fecha_comentario
            FROM comentarios com
            JOIN dispositivo d ON com.id_dispositivo = d.id_dispositivo
            JOIN usuario u ON com.id_usuario = u.id_usuario";
    $result = $conn->query($sql);
    $comentarios = [];
    while($row = $result->fetch_assoc()){
        $comentarios[] = $row;
    }
    return $comentarios;
}

// Obtener un comentario por ID
function obtenerComentario($conn, $id) {
    $id = intval($id);
    $sql = "SELECT com.id_comentario, d.Nombre AS Dispositivo, u.Nombre AS Usuario, com.comentario, com.fecha_comentario
            FROM comentarios com
            JOIN dispositivo d ON com.id_dispositivo = d.id_dispositivo
            JOIN usuario u ON com.id_usuario = u.id_usuario
            WHERE com.id_comentario = $id LIMIT 1";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Validar existencia de usuario y dispositivo
function usuarioExiste($conn, $id_usuario) {
    $id_usuario = intval($id_usuario);
    $result = $conn->query("SELECT * FROM usuario WHERE id_usuario=$id_usuario AND estado='activo' LIMIT 1");
    return $result->num_rows > 0;
}

function dispositivoExiste($conn, $id_dispositivo) {
    $id_dispositivo = intval($id_dispositivo);
    $result = $conn->query("SELECT * FROM dispositivo WHERE id_dispositivo=$id_dispositivo LIMIT 1");
    return $result->num_rows > 0;
}

// Agregar un nuevo comentario
function agregarComentario($conn, $id_dispositivo, $id_usuario, $comentario, $nombre_invitado=null, $email_invitado=null) {
    if (!dispositivoExiste($conn, $id_dispositivo)) {
        return ['success' => false, 'error' => 'Dispositivo no válido'];
    }

    if($id_usuario){ // usuario registrado
        $stmt = $conn->prepare("INSERT INTO comentarios (id_dispositivo, id_usuario, comentario) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id_dispositivo, $id_usuario, $comentario);
    } else { // invitado
        $stmt = $conn->prepare("INSERT INTO comentarios (id_dispositivo, comentario, nombre_invitado, email_invitado) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $id_dispositivo, $comentario, $nombre_invitado, $email_invitado);
    }

    return ['success' => $stmt->execute()];
}

// Editar un comentario existente
function editarComentario($conn, $id, $id_dispositivo, $id_usuario, $comentario) {
    if (!usuarioExiste($conn, $id_usuario) || !dispositivoExiste($conn, $id_dispositivo)) {
        return ['success' => false, 'error' => 'Usuario o dispositivo no válido'];
    }

    $stmt = $conn->prepare("UPDATE comentarios SET id_dispositivo=?, id_usuario=?, comentario=? WHERE id_comentario=?");
    $stmt->bind_param("iisi", $id_dispositivo, $id_usuario, $comentario, $id);
    return ['success' => $stmt->execute()];
}

// Eliminar un comentario
function eliminarComentario($conn, $id) {
    $id = intval($id);
    return ['success' => $conn->query("DELETE FROM comentarios WHERE id_comentario=$id")];
}

// ================== ACCIONES ==================
$action = $_GET['action'] ?? '';

switch($action) {
    case 'listar':
        echo json_encode(listarComentarios($conn));
        break;

    case 'obtener':
        $id = intval($_GET['id'] ?? 0);
        echo json_encode(obtenerComentario($conn, $id));
        break;

    case 'agregar':
        case 'agregar':
    $data = json_decode(file_get_contents('php://input'), true);
    $response = agregarComentario(
        $conn,
        intval($data['id_dispositivo'] ?? 0),
        $data['id_usuario'] ?? null,
        $data['comentario'] ?? '',
        $data['nombre_invitado'] ?? null,
        $data['email_invitado'] ?? null
    );
    echo json_encode($response);
    break;

    case 'editar':
        $id = intval($_GET['id'] ?? 0);
        $data = json_decode(file_get_contents('php://input'), true);
        $response = editarComentario(
            $conn,
            $id,
            intval($data['id_dispositivo'] ?? 0),
            intval($data['id_usuario'] ?? 0),
            $data['comentario'] ?? ''
        );
        echo json_encode($response);
        break;

    case 'eliminar':
        $id = intval($_GET['id'] ?? 0);
        echo json_encode(eliminarComentario($conn, $id));
        break;

    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
?>