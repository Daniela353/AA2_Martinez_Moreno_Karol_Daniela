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

// Función para verificar que sea Administrador
function verify_admin($token) {
    if (!$token || !jwt_verify($token)) {
        http_response_code(401);
        echo json_encode(['error'=>'No autorizado']);
        exit;
    }
    $payload = jwt_decode($token);
    if ($payload['rol'] !== 'Administrador') {
        http_response_code(403);
        echo json_encode(['error'=>'Acceso denegado']);
        exit;
    }
}

// ---------------- GET ----------------
if ($method === 'GET') {
    try {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM categoria WHERE id_categoria=?");
            $stmt->execute([$_GET['id']]);
            $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($categoria) {
                echo json_encode($categoria);
            } else {
                http_response_code(404);
                echo json_encode(['error'=>'Categoría no encontrada']);
            }
        } else {
            $stmt = $pdo->query("SELECT * FROM categoria");
            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(!$categorias){
                echo json_encode(['message'=>'No hay categorías registradas']);
            } else {
                echo json_encode($categorias);
            }
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
}

// ---------------- POST ----------------
elseif ($method === 'POST') {
    verify_admin($token);
    $data = json_decode(file_get_contents("php://input"), true);
    $nombre = $data['nombre_categoria'] ?? '';
    $descripcion = $data['descripcion'] ?? '';

    if (!$nombre) {
        http_response_code(400);
        echo json_encode(['error'=>'Falta nombre_categoria']);
        exit;
    }

    // Validar si ya existe
    $stmt = $pdo->prepare("SELECT id_categoria FROM categoria WHERE nombre_categoria=?");
    $stmt->execute([$nombre]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error'=>'La categoría ya existe']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO categoria (nombre_categoria, descripcion) VALUES (?,?)");
        $stmt->execute([$nombre,$descripcion]);
        echo json_encode(['message'=>'Categoría creada exitosamente','id_categoria'=>$pdo->lastInsertId()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
}

// ---------------- PUT ----------------
elseif ($method === 'PUT') {
    verify_admin($token);
    $data = json_decode(file_get_contents("php://input"), true);
    $id_categoria = $_GET['id'] ?? $data['id_categoria'] ?? 0;

    if (!$id_categoria) {
        http_response_code(400);
        echo json_encode(['error'=>'Falta id_categoria']);
        exit;
    }

    $nombre = $data['nombre_categoria'] ?? null;
    $descripcion = $data['descripcion'] ?? null;

    if (!$nombre && !$descripcion) {
        http_response_code(400);
        echo json_encode(['error'=>'No hay campos para actualizar']);
        exit;
    }

    $query = "UPDATE categoria SET ";
    $params = [];
    if ($nombre) { $query .= "nombre_categoria=?, "; $params[] = $nombre; }
    if ($descripcion) { $query .= "descripcion=?, "; $params[] = $descripcion; }
    $query = rtrim($query, ", ") . " WHERE id_categoria=?";
    $params[] = $id_categoria;

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['message'=>'Categoría actualizada exitosamente']);
        } else {
            http_response_code(404);
            echo json_encode(['error'=>'Categoría no encontrada o sin cambios']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
}

// ---------------- DELETE ----------------
elseif ($method === 'DELETE') {
    verify_admin($token);
    $id_categoria = $_GET['id'] ?? 0;

    if (!$id_categoria) {
        http_response_code(400);
        echo json_encode(['error'=>'Falta id_categoria']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM categoria WHERE id_categoria=?");
        $stmt->execute([$id_categoria]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['message'=>'Categoría eliminada exitosamente']);
        } else {
            http_response_code(404);
            echo json_encode(['error'=>'Categoría no encontrada']);
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
