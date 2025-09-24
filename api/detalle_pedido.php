<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/lib/jwt_helper.php';

$database = new Database();
$pdo = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

// 🔑 Obtener token
$headers = getallheaders();
$token = $headers['Authorization'] ?? '';
if ($token) $token = str_replace('Bearer ', '', $token);

// 🔐 Función para validar token
function verify_user($token) {
    if (!$token || !jwt_verify($token)) {
        http_response_code(401);
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }
    return jwt_decode($token);
}

// ---------------- GET ----------------
if ($method === 'GET') {
    try {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM detalle_pedido WHERE id_detalle=?");
            $stmt->execute([$_GET['id']]);
            $detalle = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($detalle) {
                echo json_encode($detalle);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Detalle no encontrado']);
            }
        } elseif (isset($_GET['id_pedido'])) {
            $stmt = $pdo->prepare("SELECT * FROM detalle_pedido WHERE id_pedido=?");
            $stmt->execute([$_GET['id_pedido']]);
            $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($detalles);
        } else {
            $stmt = $pdo->query("SELECT * FROM detalle_pedido");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// ---------------- POST ----------------
elseif ($method === 'POST') {
    $user = verify_user($token);
    $data = json_decode(file_get_contents("php://input"), true);

    $id_pedido = $data['id_pedido'] ?? 0;
    $id_dispositivo = $data['id_dispositivo'] ?? 0;
    $cantidad = $data['cantidad'] ?? 0;
    $precio_unitario = $data['precio_unitario'] ?? null;

    if (!$id_pedido || !$id_dispositivo || !$cantidad) {
        http_response_code(400);
        echo json_encode(['error' => 'Faltan datos obligatorios']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO detalle_pedido (id_pedido, id_dispositivo, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_pedido, $id_dispositivo, $cantidad, $precio_unitario]);
        echo json_encode(['message' => 'Detalle agregado', 'id_detalle' => $pdo->lastInsertId()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// ---------------- PUT ----------------
elseif ($method === 'PUT') {
    $user = verify_user($token);
    $data = json_decode(file_get_contents("php://input"), true);

    $id_detalle = $_GET['id'] ?? $data['id_detalle'] ?? 0;
    $cantidad = $data['cantidad'] ?? null;
    $precio_unitario = $data['precio_unitario'] ?? null;

    if (!$id_detalle || !$cantidad) {
        http_response_code(400);
        echo json_encode(['error' => 'Faltan datos obligatorios']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE detalle_pedido SET cantidad=?, precio_unitario=? WHERE id_detalle=?");
        $stmt->execute([$cantidad, $precio_unitario, $id_detalle]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['message' => 'Detalle actualizado']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Detalle no encontrado']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// ---------------- DELETE ----------------
elseif ($method === 'DELETE') {
    $user = verify_user($token);
    $id_detalle = $_GET['id'] ?? 0;

    if (!$id_detalle) {
        http_response_code(400);
        echo json_encode(['error' => 'Falta id_detalle']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM detalle_pedido WHERE id_detalle=?");
        $stmt->execute([$id_detalle]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['message' => 'Detalle eliminado']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Detalle no encontrado']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// ---------------- Método no permitido ----------------
else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>
