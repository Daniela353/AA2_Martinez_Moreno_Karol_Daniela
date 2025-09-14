<?php
session_start();
include __DIR__ . "/../src/conexion.php";

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = $_POST['nombre'] ?? '';
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($nombre && $email && $password) {
        // Encriptar contraseña
        $hash = password_hash($password, PASSWORD_BCRYPT);

        // Rol cliente y estado activo por defecto
        $rol    = "cliente";
        $estado = "activo";

        $stmt = $conn->prepare("INSERT INTO usuario (nombre, email, password, rol, estado, fecha_registro) VALUES (?,?,?,?,?, CURDATE())");
        $stmt->bind_param("sssss", $nombre, $email, $hash, $rol, $estado);

        if ($stmt->execute()) {
            $msg = "✅ Registro exitoso. Ahora puedes iniciar sesión.";
        } else {
            if ($conn->errno === 1062) {
                $msg = "⚠️ El email ya está registrado.";
            } else {
                $msg = "❌ Error: " . $conn->error;
            }
        }
    } else {
        $msg = "⚠️ Todos los campos son obligatorios.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro de Clientes</title>
<style>
    body { 
        font-family: 'Arial', sans-serif; 
        background-color: #eca8ceff; 
        margin: 0; padding: 0; 
        display: flex; justify-content: center; align-items: center; 
        height: 100vh; 
    }
    .register-container { 
        background-color: #fff; 
        padding: 40px; 
        border-radius: 10px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
        width: 380px; 
        text-align: center;
    }
    .register-container img {
        width: 120px;
        margin-bottom: 20px;
    }
    h2 { color: #333; margin-bottom: 20px; }
    label { display: block; margin-bottom: 10px; text-align: left; color: #555; font-weight: 500; }
    input { 
        width: 100%; padding: 10px; margin-bottom: 15px; 
        border: 1px solid #ccc; border-radius: 5px; font-size: 14px;
    }
    button { 
        width: 100%; padding: 12px; 
        background-color: #28a745; /* verde */
        border: none; color: #fff; 
        font-size: 16px; font-weight: bold; 
        border-radius: 5px; cursor: pointer; 
        transition: background 0.3s;
    }
    button:hover { background-color: #218838; }
    .msg { margin-top: 15px; font-size: 14px; }
</style>
</head>
<body>
<div class="register-container">
    <!-- Logo -->
    <img src="imagenes/logo.png" alt="Logo">

    <h2>Registro de Cliente</h2>

    <form method="POST" action="registro_clientes.php">
        <label>Nombre:
            <input type="text" name="nombre" required>
        </label>
        <label>Email:
            <input type="email" name="email" required>
        </label>
        <label>Contraseña:
            <input type="password" name="password" required>
        </label>
        <button type="submit">Registrarme</button>
    </form>

    <div class="msg">
        <?php if ($msg) echo htmlspecialchars($msg); ?>
    </div>

    <p style="margin-top: 20px; font-size: 14px;">
        ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
    </p>
</div>
</body>
</html>
