<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/lib/jwt_helper.php';

$input = json_decode(file_get_contents('php://input'), true);

$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Email y password requeridos']);
    exit;
}

$database = new Database();
$pdo = $database->getConnection();

try {
    $stmt = $pdo->prepare("SELECT id_usuario, nombre, password, rol 
                           FROM usuario 
                           WHERE email = ? AND estado = 'activo'
                           LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Comparación directa de contraseñas (como en tu login original)
        if ($password === $user['password']) {
            $payload = [
                'id_usuario' => $user['id_usuario'],
                'nombre' => $user['nombre'],
                'rol' => $user['rol']
            ];
            $token = jwt_create($payload, 3600); // token válido 1 hora
            echo json_encode([
                'status' => 'success',
                'token' => $token,
                'usuario' => $payload
            ]);
            exit;
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Contraseña incorrecta']);
            exit;
        }
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Usuario no encontrado o inactivo']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en el servidor: '.$e->getMessage()]);
    exit;
}