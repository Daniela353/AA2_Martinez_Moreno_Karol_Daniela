<?php
session_start();
include __DIR__ . "/../conexion.php";

// Solo admin
if(!isset($_SESSION['admin_id']) || $_SESSION['rol'] !== 'Administrador'){
    echo "<p style='color:red;'>Acceso denegado.</p>";
    exit;
}

// Mensaje de acción
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

// Filtro
$filtroNombre = $_GET['nombre'] ?? '';

// ELIMINAR COMENTARIO (si se llama directamente por AJAX)
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM comentarios WHERE id_comentario = $id");
    $_SESSION['msg'] = "Comentario eliminado.";
    $filtroNombre = $_GET['nombre'] ?? '';
}

// Consulta
$sql = "SELECT c.id_comentario, c.id_dispositivo, c.id_usuario, c.comentario,
                 c.calificacion, c.fecha_comentario,
                 u.nombre AS usuario,
                 d.nombre AS dispositivo
         FROM comentarios c
         LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
         LEFT JOIN dispositivo d ON c.id_dispositivo = d.id_dispositivo
         WHERE 1=1";

if(!empty($filtroNombre)){
    $filtroNombre = $conn->real_escape_string($filtroNombre);
    $sql .= " AND d.nombre = '$filtroNombre'";
}

$sql .= " ORDER BY c.fecha_comentario DESC";
$result = $conn->query($sql);

// Lista de dispositivos para filtro
$dispositivos = $conn->query("SELECT DISTINCT nombre FROM dispositivo ORDER BY nombre ASC");
?>

<div>
    <h1>📋 Gestión de Comentarios</h1>

    <?php if($msg): ?>
        <p style="color:green;font-weight:bold;"><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>

    <form id="form-filtro">
        <label>Filtrar por dispositivo:</label>
        <select name="nombre">
            <option value="">-- Todos --</option>
            <?php while($d = $dispositivos->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($d['nombre']) ?>"
                    <?= ($filtroNombre == $d['nombre']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($d['nombre']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Aplicar filtro</button>
        <button type="button" id="limpiarFiltro">Limpiar</button>
    </form>

    <table id="comentarios-table" border="1" cellpadding="5" cellspacing="0" style="width:100%; margin-top:15px;">
        <tr>
            <th>ID</th>
            <th>Dispositivo</th>
            <th>Usuario</th>
            <th>Comentario</th>
            <th>Calificación</th>
            <th>Fecha</th>
            <th>Acciones</th>
        </tr>
        <?php if($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id_comentario'] ?></td>
                <td><?= htmlspecialchars($row['dispositivo'] ?? '#'.$row['id_dispositivo']) ?></td>
                <td><?= htmlspecialchars($row['usuario'] ?? 'Usuario '.$row['id_usuario']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['comentario'])) ?></td>
                <td><?= $row['calificacion'] ? str_repeat("⭐", $row['calificacion']) : '-' ?></td>
                <td><?= $row['fecha_comentario'] ?></td>
                <td>
                    <a href="#" class="btn-eliminar" data-id="<?= $row['id_comentario'] ?>">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">No hay comentarios registrados.</td></tr>
        <?php endif; ?>
    </table>
</div>