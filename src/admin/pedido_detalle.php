<?php
session_start();
include __DIR__."/../conexion.php";

if (!isset($_SESSION['admin_id']) || $_SESSION['rol'] !== 'Administrador') {
    die("Acceso denegado.");
}

$id = intval($_GET['id_pedido'] ?? 0);

// Comprueba si se está intentando actualizar el estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['estado'])) {
    $estado = $_POST['estado'];

    // Usar sentencia preparada para prevenir inyección SQL
    $stmt = $conn->prepare("UPDATE pedido SET estado = ? WHERE id_pedido = ?");
    $stmt->bind_param("si", $estado, $id);

    if ($stmt->execute()) {
        echo "Estado actualizado correctamente.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    exit;
}

// Obtener pedido
$stmt = $conn->prepare("SELECT * FROM pedido WHERE id_pedido = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$pedido = $res->fetch_assoc();
$stmt->close();

if (!$pedido) {
    echo "Pedido no encontrado.";
    exit;
}

// Obtener detalles del pedido
$stmt_detalles = $conn->prepare("SELECT * FROM detalle_pedido WHERE id_pedido = ?");
$stmt_detalles->bind_param("i", $id);
$stmt_detalles->execute();
$detalles = $stmt_detalles->get_result();
$stmt_detalles->close();
?>

<h2>Detalle del Pedido #<?= $pedido['id_pedido'] ?></h2>
<p><strong>ID Usuario:</strong> <?= $pedido['id_usuario'] ?></p>
<p><strong>Total:</strong> <?= $pedido['total'] ?></p>
<p><strong>Fecha:</strong> <?= $pedido['fecha_orden'] ?></p>
<p><strong>Estado Actual:</strong> <?= htmlspecialchars($pedido['estado']) ?></p>

<h3>Productos</h3>
<table border="1" cellpadding="5" cellspacing="0" style="width:100%;">
    <tr>
        <th>ID Detalle</th>
        <th>ID Dispositivo</th>
        <th>Cantidad</th>
        <th>Precio Unitario</th>
    </tr>
    <?php while($det = $detalles->fetch_assoc()): ?>
    <tr>
        <td><?= $det['id_detalle'] ?></td>
        <td><?= $det['id_dispositivo'] ?></td>
        <td><?= $det['cantidad'] ?></td>
        <td><?= $det['precio_unitario'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>

<form id="form-estado" data-id="<?= $pedido['id_pedido'] ?>" style="margin-top:10px;">
    <label>Cambiar Estado:</label>
    <select name="estado">
        <option value="Pendiente" <?= $pedido['estado']=='Pendiente'?'selected':'' ?>>Pendiente</option>
        <option value="En proceso" <?= $pedido['estado']=='En proceso'?'selected':'' ?>>En proceso</option>
        <option value="Enviado" <?= $pedido['estado']=='Enviado'?'selected':'' ?>>Enviado</option>
        <option value="Entregado" <?= $pedido['estado']=='Entregado'?'selected':'' ?>>Entregado</option>
        <option value="Cancelado" <?= $pedido['estado']=='Cancelado'?'selected':'' ?>>Cancelado</option>
    </select>
    <button type="submit">Actualizar</button>
</form>
<button id="volverListaPedidos" style="margin-top:10px;">Volver a la lista</button>