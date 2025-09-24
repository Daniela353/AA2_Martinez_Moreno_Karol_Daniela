<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/db.php';   // 📌 Aquí está la clase Database
require_once __DIR__ . '/lib/jwt_helper.php'; // 📌 Aquí está jwt_verify y jwt_decode



$database = new Database();
$pdo = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$headers = getallheaders();
$token = $headers['Authorization'] ?? '';
if ($token) $token = str_replace('Bearer ', '', $token);

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
            $stmt = $pdo->prepare("SELECT id_usuario, nombre, email, fecha_registro, estado, rol FROM usuario WHERE id_usuario=?");
            $stmt->execute([$_GET['id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $user['fecha_registro'] = date('c', strtotime($user['fecha_registro']));
                echo json_encode($user);
            } else {
                http_response_code(404);
                echo json_encode(['error'=>'Usuario no encontrado']);
            }
        } else {
            $stmt = $pdo->query("SELECT id_usuario, nombre, email, fecha_registro, estado, rol FROM usuario");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(!$users){
                echo json_encode(['message'=>'No hay usuarios registrados']);
            } else {
                foreach ($users as &$u) $u['fecha_registro'] = date('c', strtotime($u['fecha_registro']));
                echo json_encode($users);
            }
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
    $nombre = $data['nombre'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $rol = $data['rol'] ?? 'Cliente';

    if (!$nombre || !$email || !$password) {
        http_response_code(400);
        echo json_encode(['error'=>'Faltan datos obligatorios']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE email=?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error'=>'El email ya está registrado']);
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO usuario (nombre,email,password,rol,fecha_registro,estado) VALUES (?,?,?,?,CURDATE(),'activo')");
        $stmt->execute([$nombre,$email,$password_hash,$rol]);
        echo json_encode(['message'=>'Usuario creado','id_usuario'=>$pdo->lastInsertId()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
}

// ---------------- PUT ----------------
elseif ($method==='PUT') {
    verify_admin($token);
    $data = json_decode(file_get_contents("php://input"), true);
    $id_usuario = $_GET['id'] ?? $data['id_usuario'] ?? 0;
    $nombre = $data['nombre'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $rol = $data['rol'] ?? '';

    if (!$id_usuario || !$nombre || !$email) {
        http_response_code(400);
        echo json_encode(['error'=>'Faltan datos obligatorios']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE email=? AND id_usuario != ?");
    $stmt->execute([$email,$id_usuario]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error'=>'El email ya está registrado']);
        exit;
    }

    $query="UPDATE usuario SET nombre=?, email=?";
    $params=[$nombre,$email];
    if($password){ $query.=", password=?"; $params[]=password_hash($password,PASSWORD_DEFAULT);}
    if($rol){ $query.=", rol=?"; $params[]=$rol;}
    $query.=" WHERE id_usuario=?"; $params[]=$id_usuario;

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        echo json_encode(['message'=>'Usuario actualizado']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
}

// ---------------- DELETE ----------------
elseif ($method==='DELETE') {
    verify_admin($token);
    $data=json_decode(file_get_contents("php://input"),true);
    $id_usuario=$_GET['id'] ?? $data['id_usuario'] ?? 0;

    if(!$id_usuario){
        http_response_code(400);
        echo json_encode(['error'=>'Falta id_usuario']);
        exit;
    }

    try {
        $stmt=$pdo->prepare("DELETE FROM usuario WHERE id_usuario=?");
        $stmt->execute([$id_usuario]);
        echo json_encode(['message'=>'Usuario eliminado']);
    } catch(Exception $e){
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



