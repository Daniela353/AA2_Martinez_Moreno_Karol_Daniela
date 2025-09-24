<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/lib/jwt_helper.php';

$database = new Database();
$pdo = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

// 🔐 Obtener token desde headers
$headers = getallheaders();
$token = $headers['Authorization'] ?? '';
if ($token) $token = str_replace('Bearer ', '', $token);

// Verificar token (solo en POST, PUT, DELETE)
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
            $stmt = $pdo->prepare("SELECT * FROM imagen WHERE id_imagen=?");
            $stmt->execute([$_GET['id']]);
            $imagen = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($imagen) {
                echo json_encode($imagen);
            } else {
                http_response_code(404);
                echo json_encode(['error'=>'Imagen no encontrada']);
            }
        } elseif (isset($_GET['id_dispositivo'])) {
            $stmt = $pdo->prepare("SELECT * FROM imagen WHERE id_dispositivo=?");
            $stmt->execute([$_GET['id_dispositivo']]);
            $imagenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($imagenes);
        } else {
            $stmt = $pdo->query("SELECT * FROM imagen");
            $imagenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($imagenes);
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

    $id_dispositivo = $data['id_dispositivo'] ?? null;
    $tipo_imagen = $data['tipo_imagen'] ?? null;

    if (!$id_dispositivo || !$tipo_imagen) {
        http_response_code(400);
        echo json_encode(['error'=>'Faltan datos obligatorios']);
        exit;
    }

    try {
        // verificar si ya existe
        $check = $pdo->prepare("SELECT * FROM imagen WHERE id_dispositivo=? AND tipo_imagen=?");
        $check->execute([$id_dispositivo, $tipo_imagen]);
        if ($check->fetch()) {
            echo json_encode(['error'=>'La imagen ya existe']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO imagen (id_dispositivo, tipo_imagen) VALUES (?, ?)");
        $stmt->execute([$id_dispositivo, $tipo_imagen]);
        echo json_encode(['message'=>'Imagen creada','id_imagen'=>$pdo->lastInsertId()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
}

// ---------------- PUT ----------------
// ---------------- PUT ----------------
elseif ($method==='PUT') {
    $user = verify_user($token);
    $data = json_decode(file_get_contents("php://input"), true);

    $id_imagen = $_GET['id'] ?? $data['id_imagen'] ?? 0;
    $id_dispositivo = $data['id_dispositivo'] ?? null;
    $tipo_imagen = $data['tipo_imagen'] ?? null;
    $imagen_secundaria = $data['imagen_secundaria'] ?? null;

    if (!$id_imagen || !$id_dispositivo || !$tipo_imagen) {
        http_response_code(400);
        echo json_encode(['error'=>'Faltan datos obligatorios (id_imagen, id_dispositivo, tipo_imagen)']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE imagen 
                               SET id_dispositivo=?, tipo_imagen=?, imagen_secundaria=? 
                               WHERE id_imagen=?");
        $stmt->execute([$id_dispositivo, $tipo_imagen, $imagen_secundaria, $id_imagen]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['message'=>'Imagen actualizada']);
        } else {
            http_response_code(404);
            echo json_encode(['error'=>'Imagen no encontrada']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
}

// ---------------- DELETE ----------------
elseif ($method==='DELETE') {
    $user = verify_user($token);
    $id_imagen = $_GET['id'] ?? 0;

    if (!$id_imagen) {
        http_response_code(400);
        echo json_encode(['error'=>'Falta id_imagen']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM imagen WHERE id_imagen=?");
        $stmt->execute([$id_imagen]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['message'=>'Imagen eliminada']);
        } else {
            http_response_code(404);
            echo json_encode(['error'=>'Imagen no encontrada']);
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
