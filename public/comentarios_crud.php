<?php
session_start();
include __DIR__ . "/../src/conexion.php";          // Conexión a la base de datos
include __DIR__ . "/../src/comentarios_crud.php";  // Funciones CRUD

header('Content-Type: application/json'); // Respuesta JSON

$action = $_GET['action'] ?? '';

// ================== DETERMINAR ROL ==================
if(isset($_SESSION['admin_id'])){
    $id_usuario = $_SESSION['admin_id'];
    $rol = 'Administrador';
} elseif(isset($_SESSION['usuario_id'])){
    $id_usuario = $_SESSION['usuario_id'];
    $rol = 'Cliente';
} else {
    // No hay sesión activa, bloquear acceso
    echo json_encode(['success' => false, 'error' => 'Debes iniciar sesión']);
    exit;
}

// ================== ACCIONES ==================
switch($action){

    // ================= LISTAR =================
    case 'listar':
        echo json_encode(listarComentarios($conn));
        break;

    // ================= OBTENER =================
    case 'obtener':
        $id = intval($_GET['id'] ?? 0);
        echo json_encode(obtenerComentario($conn, $id));
        break;

    // ================= AGREGAR =================
    case 'agregar':
        $data = json_decode(file_get_contents('php://input'), true);

        $response = agregarComentario(
            $conn,
            intval($data['id_dispositivo'] ?? 0),
            $id_usuario,
            $rol,
            $data['comentario'] ?? '',
            $data['nombre_invitado'] ?? null,
            $data['email_invitado'] ?? null
        );

        echo json_encode($response);
        break;

    // ================= EDITAR =================
    case 'editar':
        $id = intval($_GET['id'] ?? 0);
        $data = json_decode(file_get_contents('php://input'), true);

        $response = editarComentario(
            $conn,
            $id,
            intval($data['id_dispositivo'] ?? 0),
            $id_usuario,
            $data['comentario'] ?? ''
        );
        echo json_encode($response);
        break;

    // ================= ELIMINAR =================
    case 'eliminar':
        $id = intval($_GET['id'] ?? 0);
        echo json_encode(eliminarComentario($conn, $id));
        break;

    // ================= ACCIÓN NO VÁLIDA =================
    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
?>
