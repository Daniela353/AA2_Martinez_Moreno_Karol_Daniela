<?php
include __DIR__ . '/../src/conexion.php'; // Asegúrate de que la ruta sea correcta

// 1. Seleccionar a todos los usuarios que no tienen un hash (contraseñas en texto plano)
$stmt = $conn->prepare("SELECT id_usuario, password FROM usuario WHERE password NOT LIKE '$2y$10$%'");
$stmt->execute();
$result = $stmt->get_result();
$usuarios_a_migrar = $result->fetch_all(MYSQLI_ASSOC);

if (count($usuarios_a_migrar) > 0) {
    echo "Iniciando migración de " . count($usuarios_a_migrar) . " usuarios...<br>";
    $update_count = 0;

    foreach ($usuarios_a_migrar as $user) {
        $id = $user['id_usuario'];
        $plain_password = $user['password'];

        // 2. Encriptar la contraseña de texto plano
        $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

        // 3. Actualizar la contraseña en la base de datos con el hash
        $update_stmt = $conn->prepare("UPDATE usuario SET password = ? WHERE id_usuario = ?");
        $update_stmt->bind_param("si", $hashed_password, $id);
        
        if ($update_stmt->execute()) {
            echo "Usuario con ID $id migrado exitosamente.<br>";
            $update_count++;
        } else {
            echo "Error al migrar usuario con ID $id: " . $update_stmt->error . "<br>";
        }
    }
    echo "<br>Migración completada. Total de usuarios actualizados: $update_count.";
} else {
    echo "No se encontraron usuarios con contraseñas en texto plano. La migración no es necesaria.";
}

$conn->close();
?>