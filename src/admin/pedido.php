<?php
session_start();
include __DIR__."/../conexion.php";

if(!isset($_SESSION['admin_id']) || $_SESSION['rol']!=='Administrador'){
    die("Acceso denegado.");
}

// Filtrar por ID usuario o estado si se enviaron
$id_usuario = isset($_GET['id_usuario']) ? intval($_GET['id_usuario']) : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';

$sql = "SELECT * FROM pedido WHERE 1=1";
$params = [];
$types = "";

if ($id_usuario > 0) {
    $sql .= " AND id_usuario = ?";
    $params[] = $id_usuario;
    $types .= "i";
}
if (!empty($estado)) {
    $sql .= " AND estado = ?";
    $params[] = $estado;
    $types .= "s";
}
$sql .= " ORDER BY fecha_orden DESC";

// Prepara la sentencia
$stmt = $conn->prepare($sql);

if (!empty($types)) {
    // Enlaza los parámetros dinámicamente
    $stmt->bind_param($types, ...$params);
}

// Ejecuta y obtiene el resultado
$stmt->execute();
$res = $stmt->get_result();
?>

<h1>📦 Pedidos</h1>

<form id="form-filtros" style="margin-bottom:15px;">
    <input type="number" name="id_usuario" placeholder="ID Usuario" value="<?= htmlspecialchars($id_usuario) ?>">
    <select name="estado">
        <option value="">Todos los estados</option>
        <option value="Pendiente" <?= $estado=='Pendiente'?'selected':'' ?>>Pendiente</option>
        <option value="En proceso" <?= $estado=='En proceso'?'selected':'' ?>>En proceso</option>
        <option value="Enviado" <?= $estado=='Enviado'?'selected':'' ?>>Enviado</option>
        <option value="Entregado" <?= $estado=='Entregado'?'selected':'' ?>>Entregado</option>
        <option value="Cancelado" <?= $estado=='Cancelado'?'selected':'' ?>>Cancelado</option>
    </select>
    <button type="submit">Filtrar</button>
</form>

<table border="1" cellpadding="5" cellspacing="0" style="width:100%;">
    <thead>
        <tr>
            <th>ID Pedido</th>
            <th>ID Usuario</th>
            <th>Total</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($res->num_rows > 0): ?>
            <?php while($row = $res->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id_pedido'] ?></td>
                <td><?= $row['id_usuario'] ?></td>
                <td><?= $row['total'] ?></td>
                <td><?= $row['fecha_orden'] ?></td>
                <td><?= htmlspecialchars($row['estado']) ?></td>
                <td>
                    <button class="btn-ver-pedido" data-id="<?= $row['id_pedido'] ?>">Ver Detalle</button>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No se encontraron pedidos.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>