<?php
session_start();
include __DIR__ . "/conexion.php"; // conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id_usuario, nombre, password, rol 
                            FROM usuario 
                            WHERE email = ? AND estado = 'activo'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Guardar datos en la sesión
            $_SESSION['id_usuario'] = $user['id_usuario'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['rol'] = $user['rol'];

            // Registrar log de acceso
            $stmt2 = $conn->prepare("INSERT INTO log_eventos 
                (id_usuario, nombre_usuario, accion, fecha_ingresada, hora_ingresada) 
                VALUES (?, ?, 'login', CURDATE(), CURTIME())");
            $stmt2->bind_param("is", $user['id_usuario'], $user['nombre']);
            $stmt2->execute();

            // 🚀 Redirigir según rol
            if ($user['rol'] === 'Administrador') {
                header("Location: ../public/panel_admin.php");
            } elseif ($user['rol'] === 'cliente') {
                header("Location: ../public/panel_cliente.php");
            } else {
                header("Location: ../public/login.php?error=1");
            }
            exit;

        } else {
            // Contraseña incorrecta
            header("Location: ../public/login.php?error=1");
            exit;
        }
    } else {
        // Usuario no encontrado
        header("Location: ../public/login.php?error=1");
        exit;
    }
}

