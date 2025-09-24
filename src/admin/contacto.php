<?php
session_start();
include __DIR__."/../conexion.php";

if(!isset($_SESSION['admin_id']) || $_SESSION['rol']!=='Administrador'){
    die("Acceso denegado.");
}

$res = $conn->query("SELECT * FROM contacto ORDER BY fecha_envio DESC");
?>

<div>
    <h1>📧 Mensajes de Contacto</h1>
    <table border="1" cellpadding="5" cellspacing="0" style="width:100%; margin-top:15px;">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Asunto</th>
            <th>Mensaje</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
        <?php while($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id_contacto'] ?></td>
            <td><?= htmlspecialchars($row['nombre']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['asunto']) ?></td>
            <td><?= nl2br(htmlspecialchars($row['mensaje'])) ?></td>
            <td><?= $row['fecha_envio'] ?></td>
            <td><?= htmlspecialchars($row['estado']) ?></td>
            <td>
                <!-- Data-id asegurado -->
                <button class="btn-ver" data-id="<?= $row['id_contacto'] ?>">Seleccionar</button>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
