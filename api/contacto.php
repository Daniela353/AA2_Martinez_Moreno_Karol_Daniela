<?php
/**
 * Archivo: contacto.php
 *
 * Nota importante sobre flujo de permisos:
 * - Tanto el cliente como el administrador pueden crear un contacto/mensaje (POST).
 * - Solo el administrador puede actualizar un contacto/mensaje (PUT) o responderlo.
 * - Solo el administrador puede eliminar un contacto/mensaje (DELETE).
 * - GET puede ser usado por ambos, pero normalmente el cliente solo ve sus propios mensajes.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/lib/jwt_helper.php';

$database = new Database();
$pdo = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$headers = getallheaders();
$token = $headers['Authorization'] ?? '';
if ($token) $token = str_replace('Bearer ', '', $token);

// 🔐 Verificar token
function verify_user($token) {
    if (!$token || !jwt_verify($token)) {
        http_response_code(401);
        echo json_encode(['error'=>'No autorizado']);
        exit;
    }
    return jwt_decode($token);
}

function verify_admin($token) {
    $user = verify_user($token);
    if ($user['rol'] !== 'Administrador') {
        http_response_code(403);
        echo json_encode(['error'=>'Acceso denegado, solo administradores']);
        exit;
    }
    return $user;
}

// ---------------- GET ----------------
if ($method === 'GET') {
    try {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM contacto WHERE id_contacto=?");
            $stmt->execute([$_GET['id']]);
            $contacto = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($contacto) {
                echo json_encode($contacto);
            } else {
                http_response_code(404);
                echo json_encode(['error'=>'Mensaje no encontrado']);
            }
        } else {
            $stmt = $pdo->query("SELECT * FROM contacto");
            $contactos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($contactos);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
}

// ---------------- POST ----------------
elseif ($method === 'POST') {
    $user = verify_user($token); // puede ser cliente o admin
    $data = json_decode(file_get_contents("php://input"), true);

    $nombre = $data['nombre'] ?? '';
    $email = $data['email'] ?? '';
    $asunto = $data['asunto'] ?? '';
    $mensaje = $data['mensaje'] ?? '';

    if (!$nombre || !$email || !$asunto || !$mensaje) {
        http_response_code(400);
        echo json_encode(['error'=>'Faltan datos obligatorios']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO contacto 
            (nombre, email, asunto, mensaje) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $email, $asunto, $mensaje]);
        echo json_encode(['message'=>'Mensaje creado', 'id_contacto'=>$pdo->lastInsertId()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
}

// ---------------- PUT ----------------
elseif ($method === 'PUT') {
    $admin = verify_admin($token); // solo admin puede actualizar respuesta/estado
    $data = json_decode(file_get_contents("php://input"), true);

    $id_contacto = $_GET['id'] ?? $data['id_contacto'] ?? 0;
    $respuesta = $data['respuesta'] ?? '';
    $estado = $data['estado'] ?? '';

    if (!$id_contacto || !$respuesta || !$estado) {
        http_response_code(400);
        echo json_encode(['error'=>'Faltan datos obligatorios para actualizar']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE contacto SET respuesta=?, estado=? WHERE id_contacto=?");
        $stmt->execute([$respuesta, $estado, $id_contacto]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['message'=>'Mensaje actualizado correctamente']);
        } else {
            http_response_code(404);
            echo json_encode(['error'=>'Mensaje no encontrado']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
}

// ---------------- DELETE ----------------
elseif ($method === 'DELETE') {
    verify_admin($token); // solo admin puede eliminar
    $id_contacto = $_GET['id'] ?? 0;

    if (!$id_contacto) {
        http_response_code(400);
        echo json_encode(['error'=>'Falta id_contacto']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM contacto WHERE id_contacto=?");
        $stmt->execute([$id_contacto]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['message'=>'Mensaje eliminado']);
        } else {
            http_response_code(404);
            echo json_encode(['error'=>'Mensaje no encontrado']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
}

// ---------------- Método no permitido ----------------
else {
    http_response_code(405);
    echo json_encode(['error'=>'Método no permitido']);
}
?>