<?php
session_start();
include __DIR__ . "/../conexion.php";

// ================== AGREGAR COMENTARIO ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_comentario'])) {
    $id_dispositivo = intval($_POST['id_dispositivo']);
    $comentario = trim($_POST['comentario']);
    $calificacion = isset($_POST['calificacion']) ? intval($_POST['calificacion']) : null;

    if (isset($_SESSION['id_usuario'])) {
        // Cliente registrado
        $id_usuario = $_SESSION['id_usuario'];
        $stmt = $conn->prepare("INSERT INTO comentarios (id_dispositivo, id_usuario, comentario, calificacion) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisi", $id_dispositivo, $id_usuario, $comentario, $calificacion);
    } else {
        // Invitado
        $nombre = trim($_POST['nombre_invitado']);
        $email = trim($_POST['email_invitado']);
        $stmt = $conn->prepare("INSERT INTO comentarios (id_dispositivo, id_usuario, comentario, nombre_invitado, email_invitado) VALUES (?, NULL, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $id_dispositivo, $comentario, $nombre, $email, $email);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: comentarios_crud.php?id_dispositivo=$id_dispositivo");
    exit;
}

// ================== ELIMINAR COMENTARIO ==================
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM comentarios WHERE id_comentario = $id");
    $id_dispositivo = intval($_GET['id_dispositivo']);
    header("Location: comentarios_crud.php?id_dispositivo=$id_dispositivo");
    exit;
}

// ================== LISTAR COMENTARIOS ==================
$id_dispositivo = intval($_GET['id_dispositivo'] ?? 0);
$result = $conn->query("SELECT * FROM comentarios WHERE id_dispositivo = $id_dispositivo ORDER BY fecha_comentario DESC");

// ================== CALCULAR PROMEDIO ==================
$promedioRes = $conn->query("SELECT AVG(calificacion) as promedio FROM comentarios WHERE id_dispositivo = $id_dispositivo AND calificacion IS NOT NULL");
$promedio = $promedioRes->fetch_assoc()['promedio'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Comentarios del Dispositivo <?= $id_dispositivo ?></title>
<style>
body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px; }
h1 { color: #333; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
th { background: #333; color: white; }
tr:nth-child(even) { background: #f2f2f2; }
form { margin-top: 20px; padding: 15px; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
textarea, input, select { width: 95%; padding: 8px; margin: 5px 0; }
button { padding: 10px 15px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
button:hover { background: #0056b3; }
a { color: red; text-decoration: none; }
.stars { color: gold; }
</style>
</head>
<body>

<h1>💬 Comentarios del dispositivo <?= $id_dispositivo ?></h1>
<p>⭐ Calificación promedio: <b><?= number_format($promedio,1) ?>/5</b></p>

<!-- Formulario Agregar -->
<form method="POST">
    <h3>Agregar comentario</h3>
    <input type="hidden" name="id_dispositivo" value="<?= $id_dispositivo ?>">
    <textarea name="comentario" placeholder="Escribe tu comentario..." required></textarea>
    
    <?php if (!isset($_SESSION['id_usuario'])): ?>
        <input type="text" name="nombre_invitado" placeholder="Tu nombre" required>
        <input type="email" name="email_invitado" placeholder="Tu correo" required>
    <?php else: ?>
        <p>Comentando como <b><?= $_SESSION['nombre'] ?? 'Cliente' ?></b></p>
        <label>Calificación:</label>
        <select name="calificacion" required>
            <option value="">-- Selecciona --</option>
            <option value="1">⭐ 1</option>
            <option value="2">⭐ 2</option>
            <option value="3">⭐ 3</option>
            <option value="4">⭐ 4</option>
            <option value="5">⭐ 5</option>
        </select>
    <?php endif; ?>
    
    <button type="submit" name="agregar_comentario">Comentar</button>
</form>

<!-- Listado de Comentarios -->
<table>
    <tr>
        <th>ID</th>
        <th>Usuario</th>
        <th>Comentario</th>
        <th>Calificación</th>
        <th>Fecha</th>
        <th>Acciones</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id_comentario'] ?></td>
        <td>
            <?php if ($row['id_usuario']): ?>
                Cliente #<?= $row['id_usuario'] ?>
            <?php else: ?>
                <?= htmlspecialchars($row['nombre_invitado']) ?> (Invitado)
            <?php endif; ?>
        </td>
        <td><?= nl2br(htmlspecialchars($row['comentario'])) ?></td>
        <td>
            <?php if ($row['calificacion']): ?>
                <span class="stars"><?= str_repeat("⭐", $row['calificacion']) ?></span>
            <?php else: ?>
                -
            <?php endif; ?>
        </td>
        <td><?= $row['fecha_comentario'] ?></td>
        <td>
            <a href="?delete=<?= $row['id_comentario'] ?>&id_dispositivo=<?= $id_dispositivo ?>" onclick="return confirm('¿Eliminar este comentario?')">Eliminar</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<a href="dispositivos_crud.php">⬅️ Volver a dispositivos</a>

</body>
</html>
