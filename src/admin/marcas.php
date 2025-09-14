<?php
session_start();
include __DIR__ . "/../conexion.php";

// Verificar rol
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    echo "Acceso denegado. Debes iniciar sesión como administrador.";
    exit;
}

// ====== AGREGAR MARCA ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $nombre = trim($_POST['nombre_marca'] ?? '');
    $pais = trim($_POST['pais_origen'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($nombre) {
        $stmt = $conn->prepare("INSERT INTO marca (nombre_marca, pais_origen, descripcion) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $pais, $descripcion);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: marcas_crud.php");
    exit;
}

// ====== ELIMINAR MARCA ======
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM marca WHERE id_marca = $id");
    header("Location: marcas_crud.php");
    exit;
}

// ====== EDITAR MARCA ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar'])) {
    $id = intval($_POST['id_marca']);
    $nombre = trim($_POST['nombre_marca']);
    $pais = trim($_POST['pais_origen']);
    $descripcion = trim($_POST['descripcion']);

    $stmt = $conn->prepare("UPDATE marca SET nombre_marca=?, pais_origen=?, descripcion=? WHERE id_marca=?");
    $stmt->bind_param("sssi", $nombre, $pais, $descripcion, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: marcas_crud.php");
    exit;
}

// ====== LISTAR MARCAS ======
$result = $conn->query("SELECT * FROM marca ORDER BY id_marca DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Marcas</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f6f8;
    padding: 20px;
}
h1 {
    color: #2c3e50;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    background: #fff;
}
th, td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}
th {
    background: #2c3e50;
    color: #fff;
}
tr:nth-child(even) { background: #f9f9f9; }
form {
    margin-top: 20px;
    padding: 15px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
input, textarea {
    padding: 8px;
    margin: 5px 0;
    width: 100%;
    max-width: 300px;
}
button {
    padding: 8px 12px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
}
.agregar { background: #27ae60; color: white; }
.agregar:hover { background: #2ecc71; }
.editar { background: #f39c12; color: white; }
.editar:hover { background: #e67e22; }
.eliminar { background: #c0392b; color: white; }
.eliminar:hover { background: #e74c3c; }
a { text-decoration: none; color: inherit; }
</style>
</head>
<body>

<h1>🏷️ Gestión de Marcas</h1>

<!-- Formulario Agregar -->
<form method="POST">
    <h3>➕ Agregar nueva marca</h3>
    <input type="text" name="nombre_marca" placeholder="Nombre de la marca" required>
    <input type="text" name="pais_origen" placeholder="País de origen">
    <textarea name="descripcion" placeholder="Descripción"></textarea>
    <button type="submit" name="agregar" class="agregar">Agregar</button>
</form>

<!-- Tabla Marcas -->
<table>
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>País de origen</th>
        <th>Descripción</th>
        <th>Acciones</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id_marca'] ?></td>
        <td><?= htmlspecialchars($row['nombre_marca']) ?></td>
        <td><?= htmlspecialchars($row['pais_origen']) ?></td>
        <td><?= htmlspecialchars($row['descripcion']) ?></td>
        <td>
            <!-- Formulario Editar inline -->
            <form method="POST" style="display:inline-block;">
                <input type="hidden" name="id_marca" value="<?= $row['id_marca'] ?>">
                <input type="text" name="nombre_marca" value="<?= htmlspecialchars($row['nombre_marca']) ?>" required>
                <input type="text" name="pais_origen" value="<?= htmlspecialchars($row['pais_origen']) ?>">
                <input type="text" name="descripcion" value="<?= htmlspecialchars($row['descripcion']) ?>">
                <button type="submit" name="editar" class="editar">Editar</button>
            </form>
            <a href="?delete=<?= $row['id_marca'] ?>" class="eliminar" onclick="return confirm('¿Eliminar esta marca?')">Eliminar</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<a href="../public/panel_admin.php">⬅️ Volver al Panel</a>

</body>
</html>
