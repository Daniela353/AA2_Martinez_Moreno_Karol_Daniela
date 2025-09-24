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

// 🔐 Verificación de token
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
    return $payload;
}

// ---------------- GET ----------------
if ($method === 'GET') {
    try {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM ofertas WHERE id_oferta=?");
            $stmt->execute([$_GET['id']]);
            $oferta = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($oferta) {
                echo json_encode($oferta);
            } else {
                http_response_code(404);
                echo json_encode(['error'=>'Oferta no encontrada']);
            }
        } elseif (isset($_GET['id_dispositivo'])) {
            $stmt = $pdo->prepare("SELECT * FROM ofertas WHERE id_dispositivo=?");
            $stmt->execute([$_GET['id_dispositivo']]);
            $ofertas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($ofertas);
        } else {
            $stmt = $pdo->query("SELECT * FROM ofertas");
            $ofertas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($ofertas);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
}

// ---------------- POST ----------------
elseif ($method==='POST') {
    verify_admin($token);
    $data = json_decode(file_get_contents("php://input"), true);

    $id_dispositivo = $data['id_dispositivo'] ?? null;
    $precio_original = $data['precio_original'] ?? null;
    $descuento_porcentaje = $data['descuento_porcentaje'] ?? 0;
    $estado = $data['estado'] ?? 'activa';
    $fecha_inicio = $data['fecha_inicio'] ?? null;
    $fecha_fin = $data['fecha_fin'] ?? null;

    if (!$id_dispositivo || !$precio_original || !$fecha_inicio || !$fecha_fin) {
        http_response_code(400);
        echo json_encode(['error'=>'Faltan datos obligatorios']);
        exit;
    }

    // calcular precio final
    $precio_final = $precio_original - ($precio_original * ($descuento_porcentaje / 100));

    try {
        $stmt = $pdo->prepare("INSERT INTO ofertas 
            (id_dispositivo, precio_original, descuento_porcentaje, precio_final, estado, fecha_inicio, fecha_fin)
            VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$id_dispositivo, $precio_original, $descuento_porcentaje, $precio_final, $estado, $fecha_inicio, $fecha_fin]);
        echo json_encode(['message'=>'Oferta creada','id_oferta'=>$pdo->lastInsertId()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
}

// ---------------- PUT ----------------
elseif ($method==='PUT') {
    verify_admin($token);
    $data = json_decode(file_get_contents("php://input"), true);

    $id_oferta = $_GET['id'] ?? $data['id_oferta'] ?? 0;
    $id_dispositivo = $data['id_dispositivo'] ?? null;
    $precio_original = $data['precio_original'] ?? null;
    $descuento_porcentaje = $data['descuento_porcentaje'] ?? null;
    $estado = $data['estado'] ?? null;
    $fecha_inicio = $data['fecha_inicio'] ?? null;
    $fecha_fin = $data['fecha_fin'] ?? null;

    if (!$id_oferta || !$id_dispositivo || !$precio_original) {
        http_response_code(400);
        echo json_encode(['error'=>'Faltan datos obligatorios (id_oferta, id_dispositivo, precio_original)']);
        exit;
    }

    // recalcular precio final
    $precio_final = $precio_original - ($precio_original * ($descuento_porcentaje / 100));

    try {
        $stmt = $pdo->prepare("UPDATE ofertas 
                               SET id_dispositivo=?, precio_original=?, descuento_porcentaje=?, precio_final=?, estado=?, fecha_inicio=?, fecha_fin=? 
                               WHERE id_oferta=?");
        $stmt->execute([$id_dispositivo, $precio_original, $descuento_porcentaje, $precio_final, $estado, $fecha_inicio, $fecha_fin, $id_oferta]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['message'=>'Oferta actualizada']);
        } else {
            http_response_code(404);
            echo json_encode(['error'=>'Oferta no encontrada']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
}

// ---------------- DELETE ----------------
elseif ($method==='DELETE') {
    verify_admin($token);
    $id_oferta = $_GET['id'] ?? 0;

    if (!$id_oferta) {
        http_response_code(400);
        echo json_encode(['error'=>'Falta id_oferta']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM ofertas WHERE id_oferta=?");
        $stmt->execute([$id_oferta]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['message'=>'Oferta eliminada']);
        } else {
            http_response_code(404);
            echo json_encode(['error'=>'Oferta no encontrada']);
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
