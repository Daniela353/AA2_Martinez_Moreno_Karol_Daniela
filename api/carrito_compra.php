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

// Verificar usuario autenticado
function verify_user($token) {
    if (!$token || !jwt_verify($token)) {
        http_response_code(401);
        echo json_encode(['error'=>'No autorizado']);
        exit;
    }
    return jwt_decode($token);
}

// ---------------- GET ----------------
// Listar productos del carrito del usuario (opcional filtrar por usuario si admin)
if ($method === 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM carrito_compra");
        $carrito = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($carrito);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
}

// ---------------- POST ----------------
// Agregar producto al carrito (cliente)
elseif ($method==='POST') {
    $user = verify_user($token);
    $data = json_decode(file_get_contents("php://input"), true);

    $id_dispositivo = $data['id_dispositivo'] ?? 0;
    $cantidad = $data['cantidad'] ?? 1;

    if (!$id_dispositivo || $cantidad < 1) {
        http_response_code(400);
        echo json_encode(['error'=>'Faltan datos obligatorios o cantidad inválida']);
        exit;
    }

    try {
        // Verificar si ya está en el carrito
        $stmt = $pdo->prepare("SELECT id_carrito FROM carrito_compra WHERE id_usuario=? AND id_dispositivo=?");
        $stmt->execute([$user['id_usuario'], $id_dispositivo]);

        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error'=>'El producto ya está en el carrito']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO carrito_compra (id_usuario, id_dispositivo, cantidad) VALUES (?, ?, ?)");
        $stmt->execute([$user['id_usuario'], $id_dispositivo, $cantidad]);

        echo json_encode(['message'=>'Producto agregado al carrito','id_carrito'=>$pdo->lastInsertId()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
}

// ---------------- PUT ----------------
// Actualizar cantidad de producto en el carrito
elseif ($method==='PUT') {
    $user = verify_user($token);
    $data = json_decode(file_get_contents("php://input"), true);

    $id_carrito = $data['id_carrito'] ?? 0;
    $cantidad = $data['cantidad'] ?? 1;

    if (!$id_carrito || $cantidad < 1) {
        http_response_code(400);
        echo json_encode(['error'=>'Faltan datos obligatorios o cantidad inválida']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE carrito_compra SET cantidad=? WHERE id_carrito=? AND id_usuario=?");
        $stmt->execute([$cantidad, $id_carrito, $user['id_usuario']]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['message'=>'Carrito actualizado']);
        } else {
            http_response_code(404);
            echo json_encode(['error'=>'Producto no encontrado en el carrito']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
}

// ---------------- DELETE ----------------
// Eliminar producto del carrito
elseif ($method==='DELETE') {
    $user = verify_user($token);
    $id_carrito = $_GET['id'] ?? 0;

    if (!$id_carrito) {
        http_response_code(400);
        echo json_encode(['error'=>'Falta id_carrito']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM carrito_compra WHERE id_carrito=? AND id_usuario=?");
        $stmt->execute([$id_carrito, $user['id_usuario']]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['message'=>'Producto eliminado del carrito']);
        } else {
            http_response_code(404);
            echo json_encode(['error'=>'Producto no encontrado en el carrito']);
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
