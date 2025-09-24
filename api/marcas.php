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

// ✅ función para validar administrador
function verify_admin($token) {
    if (!$token || !jwt_verify($token)) {
        http_response_code(401);
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }
    $payload = jwt_decode($token);
    if ($payload['rol'] !== 'Administrador') {
        http_response_code(403);
        echo json_encode(['error' => 'Acceso denegado']);
        exit;
    }
}

// ---------------- GET (Listar / ID) ----------------
if ($method === 'GET') {
    try {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM marca WHERE id_marca=?");
            $stmt->execute([$_GET['id']]);
            $marca = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($marca) {
                echo json_encode($marca);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Marca no encontrada']);
            }
        } else {
            $stmt = $pdo->query("SELECT * FROM marca");
            $marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($marcas) {
                echo json_encode($marcas);
            } else {
                echo json_encode(['message' => 'No hay marcas registradas']);
            }
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// ---------------- POST (Crear) ----------------
elseif ($method === 'POST') {
    verify_admin($token);
    $data = json_decode(file_get_contents("php://input"), true);
    $nombre = $data['nombre_marca'] ?? '';
    $pais = $data['pais_origen'] ?? '';
    $descripcion = $data['descripcion'] ?? null; // NUEVO

    if (!$nombre) {
        http_response_code(400);
        echo json_encode(['error' => 'Falta el nombre de la marca']);
        exit;
    }

    // Verificar si ya existe
    $stmt = $pdo->prepare("SELECT id_marca FROM marca WHERE nombre_marca=?");
    $stmt->execute([$nombre]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'La marca ya está registrada']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO marca (nombre_marca, pais_origen) VALUES (?,?)");
        $stmt->execute([$nombre, $pais]);
        echo json_encode(['message' => 'Marca creada exitosamente', 'id_marca' => $pdo->lastInsertId()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// ---------------- PUT (Actualizar) ----------------
elseif ($method === 'PUT') {
    verify_admin($token);
    $data = json_decode(file_get_contents("php://input"), true);
    $id_marca = $_GET['id'] ?? $data['id_marca'] ?? 0;

    if (!$id_marca) {
        http_response_code(400);
        echo json_encode(['error' => 'Falta id_marca']);
        exit;
    }

    $nombre = $data['nombre_marca'] ?? null;
    $pais = $data['pais_origen'] ?? null;
    $descripcion = $data['descripcion'] ?? null; // NUEVO

    if (!$nombre && !$pais) {
        http_response_code(400);
        echo json_encode(['error' => 'No hay campos para actualizar']);
        exit;
    }

    $query = "UPDATE marca SET ";
    $params = [];
    if ($nombre) { $query .= "nombre_marca=?, "; $params[] = $nombre; }
    if ($pais) { $query .= "pais_origen=?, "; $params[] = $pais; }
     if ($descripcion) { $query .= "descripcion=?, "; $params[] = $descripcion; }
    
    $query = rtrim($query, ", ") . " WHERE id_marca=?";
    $params[] = $id_marca;

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['message' => 'Marca actualizada exitosamente']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Marca no encontrada o sin cambios']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// ---------------- DELETE (Eliminar) ----------------
elseif ($method === 'DELETE') {
    verify_admin($token);
    $id_marca = $_GET['id'] ?? 0;

    if (!$id_marca) {
        http_response_code(400);
        echo json_encode(['error' => 'Falta id_marca']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM marca WHERE id_marca=?");
        $stmt->execute([$id_marca]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['message' => 'Marca eliminada exitosamente']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'La marca no existe']);
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
