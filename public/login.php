<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include __DIR__ . '/../src/conexion.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id_usuario, nombre, password, rol 
                            FROM usuario 
                            WHERE email=? AND estado='activo'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        // 🔑 Comparación en texto plano
        if ($password === $user['password']) {
            // Guardar sesión
            $_SESSION['id_usuario'] = $user['id_usuario'];
            $_SESSION['nombre_usuario'] = $user['nombre'];
            $_SESSION['rol'] = $user['rol'];

            // Registrar log
            $stmt2 = $conn->prepare("INSERT INTO log_evento 
                (id_usuario, nombre_usuario, accion, fecha_ingresada, hora_ingresada) 
                VALUES (?, ?, 'login', CURDATE(), CURTIME())");
            $stmt2->bind_param("is", $user['id_usuario'], $user['nombre']);
            $stmt2->execute();

            // 🚦 Redirigir según rol
            if ($user['rol'] === 'Administrador') {
                header("Location: ../public/panel_admin.php");
                exit;
            } elseif ($user['rol'] === 'Cliente') {
                header("Location: ../public/panel_cliente.php");
                exit;
            } else {
                header("Location: ../public/index.php"); 
                exit;
            }

        } else {
            $msg = "Contraseña incorrecta.";
        }
    } else {
        $msg = "Usuario no encontrado o inactivo.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login - Tienda</title>
<style>
body {
    font-family: Arial, sans-serif;
    background-color: #eca8ceff;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}
.login-container {
    background-color: #fff;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    width: 350px;
    text-align: center;
}
.login-container img {
    width: 120px;
    margin-bottom: 20px;
}
h2 {
    color: #333;
    margin-bottom: 20px;
}
input {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
}
button {
    width: 100%;
    padding: 12px;
    background-color: #28a745;
    border: none;
    color: #fff;
    font-size: 16px;
    font-weight: bold;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}
button:hover {
    background-color: #218838;
}
.msg {
    color: red;
    margin-top: 10px;
    font-size: 14px;
}
</style>
</head>
<body>
<div class="login-container">
    <img src="imagenes/logo.png" alt="Logo">
    <h2>Bienvenidos al inicio de sesión</h2>
    <form method="POST" action="">
        <input type="email" name="email" placeholder="Correo electrónico" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Ingresar</button>
    </form>
    <?php if ($msg): ?>
        <div class="msg"><?= $msg ?></div>
    <?php endif; ?>
</div>
</body>
</html>



