<?php
// ================== Conexión ==================
include __DIR__ . "/../src/conexion.php"; // Conexión a la base de datos
header('Content-Type: application/json'); // Respuesta en JSON
session_start(); // Iniciar sesión

// ====================== FUNCIONES CRUD COMENTARIOS ======================

// Listar todos los comentarios
function listarComentarios($conn){
    $sql = "SELECT 
                com.id_comentario,
                d.Nombre AS Dispositivo,
                CASE 
                    WHEN com.rol='Administrador' THEN com.nombre_usuario
                    WHEN com.rol='Cliente' THEN com.nombre_usuario
                    ELSE com.nombre_invitado
                END AS Usuario,
                com.comentario,
                com.fecha_comentario,
                com.rol
            FROM comentarios com
            LEFT JOIN dispositivo d ON com.id_dispositivo=d.id_dispositivo
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
    $sql = "SELECT 
                com.id_comentario,
                d.Nombre AS Dispositivo,
                CASE 
                    WHEN com.rol = 'Administrador' THEN a.Nombre
                    WHEN com.rol = 'Cliente' THEN u.Nombre
                    ELSE com.nombre_invitado
                END AS Usuario,
                com.comentario,
                com.fecha_comentario,
                com.rol
            FROM comentarios com
            LEFT JOIN usuario u ON com.id_usuario = u.id_usuario AND com.rol='Cliente'
            LEFT JOIN admin a ON com.id_usuario = a.id_admin AND com.rol='Administrador'
            LEFT JOIN dispositivo d ON com.id_dispositivo = d.id_dispositivo
            WHERE com.id_comentario = $id LIMIT 1";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Validar existencia de usuario/admin y dispositivo
function usuarioExiste($conn, $id_usuario) {
    $id_usuario = intval($id_usuario);
    $result = $conn->query("SELECT * FROM usuario WHERE id_usuario=$id_usuario AND estado='activo' LIMIT 1");
    return $result->num_rows > 0;
}

function adminExiste($conn, $id_admin) {
    $id_admin = intval($id_admin);
    $result = $conn->query("SELECT * FROM admin WHERE id_admin=$id_admin AND estado='activo' LIMIT 1");
    return $result->num_rows > 0;
}

function dispositivoExiste($conn, $id_dispositivo) {
    $id_dispositivo = intval($id_dispositivo);
    $result = $conn->query("SELECT * FROM dispositivo WHERE id_dispositivo=$id_dispositivo LIMIT 1");
    return $result->num_rows > 0;
}

// Agregar un nuevo comentario
function agregarComentario($conn, $id_dispositivo, $id_usuario, $rol, $comentario){
    if (!dispositivoExiste($conn, $id_dispositivo)) {
        return ['success'=>false,'error'=>'Dispositivo no válido'];
    }

    if($rol === 'Cliente' && !usuarioExiste($conn, $id_usuario)){
        return ['success'=>false,'error'=>'Usuario no válido'];
    }

    if($rol === 'Administrador' && !adminExiste($conn, $id_usuario)){
        return ['success'=>false,'error'=>'Administrador no válido'];
    }

    $stmt = $conn->prepare("INSERT INTO comentarios (id_dispositivo, id_usuario, comentario, rol) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $id_dispositivo, $id_usuario, $comentario, $rol);
    return ['success'=>$stmt->execute()];
}

// Editar un comentario existente
function editarComentario($conn, $id, $id_dispositivo, $id_usuario, $comentario) {
    if (!usuarioExiste($conn, $id_usuario) && !adminExiste($conn, $id_usuario)) {
        return ['success' => false, 'error' => 'Usuario o administrador no válido'];
    }

    if (!dispositivoExiste($conn, $id_dispositivo)) {
        return ['success' => false, 'error' => 'Dispositivo no válido'];
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
        $data = json_decode(file_get_contents('php://input'), true);

        $id_dispositivo = intval($data['id_dispositivo'] ?? 0);
        $comentario = $data['comentario'] ?? '';
        $nombre_invitado = $data['nombre_invitado'] ?? null;
        $email_invitado = $data['email_invitado'] ?? null;

        // Determinar el id_usuario y rol desde la sesión
        if (isset($_SESSION['admin_id'])) {
            $id_usuario = $_SESSION['admin_id'];
            $rol = 'Administrador';
        } elseif (isset($_SESSION['usuario_id'])) {
            $id_usuario = $_SESSION['usuario_id'];
            $rol = 'Cliente';
        } else {
            $id_usuario = null;
            $rol = 'Invitado';
        }

        $response = agregarComentario($conn, $id_dispositivo, $id_usuario, $rol, $comentario, $nombre_invitado, $email_invitado);
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
