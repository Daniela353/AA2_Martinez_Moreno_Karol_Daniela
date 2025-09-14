<?php
session_start();

// Ajusta la ruta a tu conexión según la estructura de carpetas
include __DIR__ . "/../conexion.php"; // Debe apuntar a src/conexion.php

// Verificar que solo vea el administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    echo "Acceso denegado. Debes iniciar sesión como administrador.";
    exit;
}

// Consultar usuarios
$result = $conn->query("SELECT id_usuario, nombre, email, fecha_registro, estado, rol FROM usuario ORDER BY id_usuario DESC");
?>

<h2>👥 Gestión de Usuarios</h2>
<p>Nota: Para agregar un usuario con rol Administrador, debes insertarlo directamente desde la base de datos. Los usuarios solo se pueden ver desde este panel.</p>

<table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse; margin-top:10px;">
    <tr style="background:#333; color:#fff;">
        <th>ID</th>
        <th>Nombre</th>
        <th>Email</th>
        <th>Fecha Registro</th>
        <th>Estado</th>
        <th>Rol</th>
    </tr>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr style="text-align:center;">
        <td><?= $row['id_usuario'] ?></td>
        <td><?= htmlspecialchars($row['nombre']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= $row['fecha_registro'] ?></td>
        <td><?= $row['estado'] ?></td>
        <td><?= $row['rol'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>
