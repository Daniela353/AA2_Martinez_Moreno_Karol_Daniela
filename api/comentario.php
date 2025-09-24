<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/lib/jwt_helper.php';

$database = new Database();
$pdo = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$headers = getallheaders();
$token = $headers['Authorization'] ?? '';
if ($token) $token = str_replace('Bearer ', '', $token);

// Verificar token
function verify_user($token) {
    if (!$token || !jwt_verify($token)) {
        http_response_code(401);
        echo json_encode(['error'=>'No autorizado']);
        exit;
    }
    return jwt_decode($token);
}

// ---------------- GET ----------------
if ($method === 'GET') {
    try {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM comentarios WHERE id_comentario=?");
            $stmt->execute([$_GET['id']]);
            $comentario = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($comentario) {
                echo json_encode($comentario);
            } else {
                http_response_code(404);
                echo json_encode(['error'=>'Comentario no encontrado']);
            }
        } elseif (isset($_GET['id_dispositivo'])) {
            $stmt = $pdo->prepare("SELECT * FROM comentarios WHERE id_dispositivo=?");
            $stmt->execute([$_GET['id_dispositivo']]);
            $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($comentarios);
        } else {
            $stmt = $pdo->query("SELECT * FROM comentarios");
            $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($comentarios);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
}

// ---------------- POST ----------------
elseif ($method==='POST') {
    $user = verify_user($token);
    $data = json_decode(file_get_contents("php://input"), true);

    $id_dispositivo = $data['id_dispositivo'] ?? 0;
    $comentario = $data['comentario'] ?? '';
    $calificacion = $data['calificacion'] ?? null;

    if (!$id_dispositivo || !$comentario) {
        http_response_code(400);
        echo json_encode(['error'=>'Faltan datos obligatorios']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO comentarios 
            (id_dispositivo, id_usuario, comentario, calificacion) 
            VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_dispositivo, $user['id_usuario'], $comentario, $calificacion]);
        echo json_encode(['message'=>'Comentario creado','id_comentario'=>$pdo->lastInsertId()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
}

// ---------------- PUT ----------------
elseif ($method==='PUT') {
    $user = verify_user($token);
    $data = json_decode(file_get_contents("php://input"), true);

    $id_comentario = $_GET['id'] ?? $data['id_comentario'] ?? 0;
    $comentario = $data['comentario'] ?? '';
    $calificacion = $data['calificacion'] ?? null;

    if (!$id_comentario || !$comentario) {
        http_response_code(400);
        echo json_encode(['error'=>'Faltan datos obligatorios']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE comentarios SET comentario=?, calificacion=? 
                               WHERE id_comentario=? AND id_usuario=?");
        $stmt->execute([$comentario, $calificacion, $id_comentario, $user['id_usuario']]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['message'=>'Comentario actualizado']);
        } else {
            http_response_code(404);
            echo json_encode(['error'=>'Comentario no encontrado o no autorizado']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
}

// ---------------- DELETE ----------------
elseif ($method==='DELETE') {
    $user = verify_user($token);
    $id_comentario = $_GET['id'] ?? 0;

    if (!$id_comentario) {
        http_response_code(400);
        echo json_encode(['error'=>'Falta id_comentario']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM comentarios WHERE id_comentario=? AND id_usuario=?");
        $stmt->execute([$id_comentario, $user['id_usuario']]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['message'=>'Comentario eliminado']);
        } else {
            http_response_code(404);
            echo json_encode(['error'=>'Comentario no encontrado o no autorizado']);
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
