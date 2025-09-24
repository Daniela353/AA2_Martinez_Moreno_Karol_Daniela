<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/lib/jwt_helper.php';

$database = new Database();
$pdo = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

// Función para extraer token Bearer
function get_bearer_token() {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        return str_replace('Bearer ', '', $headers['Authorization']);
    }
    return null;
}

// Verificar usuario autenticado
function verify_user($token) {
    if (!$token || !jwt_verify($token)) {
        http_response_code(401);
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }
    return jwt_decode($token);
}

$token = get_bearer_token();

// ---------------- GET ----------------
if ($method === 'GET') {
    try {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM pedido WHERE id_pedido=?");
            $stmt->execute([$_GET['id']]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($pedido) {
                echo json_encode($pedido);
            } else {
                http_response_code(404);
                echo json_encode(['error'=>'Pedido no encontrado']);
            }
        } elseif (isset($_GET['id_usuario'])) {
            $stmt = $pdo->prepare("SELECT * FROM pedido WHERE id_usuario=?");
            $stmt->execute([$_GET['id_usuario']]);
            $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($pedidos);
        } else {
            $stmt = $pdo->query("SELECT * FROM pedido");
            $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($pedidos);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
}

// ---------------- POST ----------------
elseif ($method === 'POST') {
    $user = verify_user($token);
    $data = json_decode(file_get_contents("php://input"), true);

    $estado = $data['estado'] ?? 'pendiente';
    $total = $data['total'] ?? 0;

    try {
        $stmt = $pdo->prepare("INSERT INTO pedido (id_usuario, estado, total, fecha_orden) VALUES (?, ?, ?, CURDATE())");
        $stmt->execute([$user['id_usuario'], $estado, $total]);

        echo json_encode([
            'message' => 'Pedido creado exitosamente',
            'id_pedido' => $pdo->lastInsertId()
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
}

// ---------------- PUT ----------------
elseif ($method === 'PUT') {
    $user = verify_user($token);
    $data = json_decode(file_get_contents("php://input"), true);

    $id_pedido = $_GET['id'] ?? $data['id_pedido'] ?? 0;
    $estado = $data['estado'] ?? null;
    $total = $data['total'] ?? null;

    if (!$id_pedido) {
        http_response_code(400);
        echo json_encode(['error'=>'Falta id_pedido']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE pedido SET estado=?, total=? WHERE id_pedido=? AND id_usuario=?");
        $stmt->execute([$estado, $total, $id_pedido, $user['id_usuario']]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['message'=>'Pedido actualizado']);
        } else {
            http_response_code(404);
            echo json_encode(['error'=>'Pedido no encontrado o no autorizado']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
}

// ---------------- DELETE ----------------
elseif ($method === 'DELETE') {
    $user = verify_user($token);
    $id_pedido = $_GET['id'] ?? 0;

    if (!$id_pedido) {
        http_response_code(400);
        echo json_encode(['error'=>'Falta id_pedido']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM pedido WHERE id_pedido=? AND id_usuario=?");
        $stmt->execute([$id_pedido, $user['id_usuario']]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['message'=>'Pedido eliminado']);
        } else {
            http_response_code(404);
            echo json_encode(['error'=>'Pedido no encontrado o no autorizado']);
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
