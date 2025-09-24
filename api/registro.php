<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/db.php';

// Conexión a la base de datos
$database = new Database();
$pdo = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// ---------------- POST ----------------
if ($method === 'POST') {

    $nombre = $input['nombre'] ?? '';
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    if (!$nombre || !$email || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'Faltan datos obligatorios: nombre, email o password']);
        exit;
    }

    // Validar si el email ya existe
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE email=?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'El email ya está registrado']);
        exit;
    }

    // Insertar nuevo usuario como Cliente
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO usuario (nombre,email,password,rol,fecha_registro,estado) 
                               VALUES (?,?,?, 'Cliente', CURDATE(), 'activo')");
        $stmt->execute([$nombre, $email, $password_hash]);
        echo json_encode([
            'message' => 'Registro exitoso',
            'id_usuario' => $pdo->lastInsertId(),
            'rol' => 'Cliente'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>
