<<?php
// ================== Conexión ==================
include __DIR__ . "/../src/conexion.php"; // Ajusta según tu ruta
header('Content-Type: application/json'); // Respuesta en JSON

// ====================== FUNCIONES CRUD ======================

// Listar todos los comentarios
function listarComentarios($conn) {
    $sql = "SELECT com.id_comentario, d.Nombre AS Dispositivo, 
                   COALESCE(u.Nombre, com.nombre_invitado) AS Usuario, 
                   com.comentario, com.fecha_comentario
            FROM comentarios com
            LEFT JOIN usuario u ON com.id_usuario = u.id_usuario
            LEFT JOIN dispositivo d ON com.id_dispositivo = d.id_dispositivo
            ORDER BY com.fecha_comentario DESC";
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
    $sql = "SELECT com.id_comentario, d.Nombre AS Dispositivo, 
                   COALESCE(u.Nombre, com.nombre_invitado) AS Usuario, 
                   com.comentario, com.fecha_comentario
            FROM comentarios com
            LEFT JOIN usuario u ON com.id_usuario = u.id_usuario
            LEFT JOIN dispositivo d ON com.id_dispositivo = d.id_dispositivo
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

// Agregar un nuevo comentario (usuarios o invitados)
function agregarComentario($conn, $id_dispositivo, $id_usuario, $nombre_invitado, $email_invitado, $comentario) {
    if (!dispositivoExiste($conn, $id_dispositivo)) {
        return ['success' => false, 'error' => 'Dispositivo no válido'];
    }

    if($id_usuario && !usuarioExiste($conn, $id_usuario)) {
        return ['success' => false, 'error' => 'Usuario no válido'];
    }

    $stmt = $conn->prepare("INSERT INTO comentarios (id_dispositivo, id_usuario, nombre_invitado, email_invitado, comentario) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $id_dispositivo, $id_usuario, $nombre_invitado, $email_invitado, $comentario);
    return ['success' => $stmt->execute()];
}

// Editar un comentario existente
function editarComentario($conn, $id, $comentario) {
    $id = intval($id);
    $stmt = $conn->prepare("UPDATE comentarios SET comentario=? WHERE id_comentario=?");
    $stmt->bind_param("si", $comentario, $id);
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
        $data = json_decode(file_get_contents('php://input'), true);
        $response = agregarComentario(
            $conn,
            intval($data['id_dispositivo'] ?? 0),
            intval($data['id_usuario'] ?? 0),
            $data['nombre_invitado'] ?? null,
            $data['email_invitado'] ?? null,
            $data['comentario'] ?? ''
        );
        echo json_encode($response);
        break;

    case 'editar':
        $id = intval($_GET['id'] ?? 0);
        $data = json_decode(file_get_contents('php://input'), true);
        $response = editarComentario($conn, $id, $data['comentario'] ?? '');
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
