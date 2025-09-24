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
// ---------------- GET ----------------
if ($method === 'GET') {
    try {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM dispositivo WHERE id_dispositivo=?");
            $stmt->execute([$_GET['id']]);
            $dispositivo = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($dispositivo) {
                echo json_encode($dispositivo);
            } else {
                http_response_code(404);
                echo json_encode(['error'=>'Dispositivo no encontrado']);
            }
        } else {
            $stmt = $pdo->query("SELECT * FROM dispositivo"); // CORRECCIÓN: tabla singular
            $dispositivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($dispositivos);
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

    $nombre = $data['Nombre'] ?? '';
    $marca = $data['Marca'] ?? null;
    $tipo = $data['tipo'] ?? '';
    $categoria = $data['Categoria'] ?? null;
    $precio = $data['precio'] ?? 0;
    $stock = $data['stock'] ?? 0;
    $oferta = $data['oferta'] ?? 0;
    $fecha_lanzamiento = $data['fecha_lanzamiento'] ?? null;
    $resena = $data['resena'] ?? '';
    $descripcion = $data['descripcion'] ?? '';
    $componentes = $data['componentes'] ?? '';
    $imagen = $data['imagen'] ?? '';

    if (!$nombre) {
        http_response_code(400);
        echo json_encode(['error'=>'Falta el nombre del dispositivo']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO dispositivo
            (Nombre, Marca, tipo, Categoria, precio, stock, oferta, fecha_lanzamiento, resena, descripcion, componentes, imagen)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$nombre,$marca,$tipo,$categoria,$precio,$stock,$oferta,$fecha_lanzamiento,$resena,$descripcion,$componentes,$imagen]);
        echo json_encode(['message'=>'Dispositivo creado','id_dispositivo'=>$pdo->lastInsertId()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
}

// ---------------- PUT ----------------
elseif ($method === 'PUT') {
    verify_admin($token);

    $data = json_decode(file_get_contents("php://input"), true);
    // Tomar id desde query string o body, y forzar int
    $id_dispositivo = isset($_GET['id']) ? (int) trim($_GET['id']) : (int) ($data['id_dispositivo'] ?? 0);

    if (!$id_dispositivo) {
        http_response_code(400);
        echo json_encode(['error' => 'Falta id_dispositivo']);
        exit;
    }

    // Normalizar claves a minúsculas para evitar problemas con mayúsculas/minúsculas
    $data_lower = array_change_key_case($data ?? [], CASE_LOWER);

    // Mapeo: clave_lower => columna_db
    $columns = [
        'nombre' => 'Nombre',
        'marca' => 'Marca',
        'tipo' => 'tipo',
        'categoria' => 'Categoria',
        'precio' => 'precio',
        'stock' => 'stock',
        'oferta' => 'oferta',
        'fecha_lanzamiento' => 'fecha_lanzamiento',
        'resena' => 'resena',
        'descripcion' => 'descripcion',
        'componentes' => 'componentes',
        'imagen' => 'imagen'
    ];

    $updates = [];
    $params = [];

    foreach ($columns as $key => $col) {
        if (array_key_exists($key, $data_lower)) {
            $value = $data_lower[$key];

            // Casts sencillos para algunos campos
            if (in_array($key, ['marca','categoria','stock','oferta'])) {
                $value = ($value === null || $value === '') ? null : (int)$value;
            } elseif ($key === 'precio') {
                $value = ($value === null || $value === '') ? null : (float)$value;
            } elseif ($key === 'fecha_lanzamiento') {
                // opcional: validar formato YYYY-MM-DD (no obligatorio)
                $value = ($value === null || $value === '') ? null : $value;
            }

            $updates[] = "$col = ?";
            $params[]  = $value;
        }
    }

    if (count($updates) === 0) {
        http_response_code(400);
        echo json_encode(['error' => 'No hay campos para actualizar']);
        exit;
    }

    $sql = "UPDATE dispositivo SET " . implode(', ', $updates) . " WHERE id_dispositivo = ?";
    $params[] = $id_dispositivo;

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['message' => 'Dispositivo actualizado']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
// ---------------- DELETE ----------------
// ---------------- DELETE ----------------
// ---------------- DELETE ----------------
    elseif ($method==='DELETE') {
    verify_admin($token);

    $id_dispositivo = $_GET['id'] ?? 0;

    if (!$id_dispositivo) {
        http_response_code(400);
        echo json_encode(['error' => 'Falta id_dispositivo']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM dispositivo WHERE id_dispositivo=?");
        $stmt->execute([$id_dispositivo]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['message' => '✅ Dispositivo eliminado']);
        } else {
            http_response_code(404);
            echo json_encode(['message' => '❌ Dispositivo no existe']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
// ---------------- Método no permitido ----------------
else {
    http_response_code(405);
    echo json_encode(['error'=>'Método no permitido']);
}
?>
