<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/lib/jwt_helper.php';

$database = new Database();
$pdo = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$headers = getallheaders();

// Tomar token desde header o query (solo para pruebas)
$token = $headers['Authorization'] ?? $_GET['token'] ?? '';
if ($token) $token = str_replace('Bearer ', '', $token);

// Función para verificar que sea admin
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
    verify_admin($token); // Solo admin puede listar logs
    try {
        $stmt = $pdo->query("SELECT * FROM log_evento ORDER BY fecha_ingresada DESC, hora_ingresada DESC");
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($logs);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// ---------------- Métodos no permitidos ----------------
else {
    http_response_code(405);
    echo json_encode(['error'=>'Método no permitido']);
}
?>

