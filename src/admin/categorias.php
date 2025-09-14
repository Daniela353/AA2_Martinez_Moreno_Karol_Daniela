<?php
session_start();
include __DIR__ . "/../conexion.php";

// Verificar rol
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    echo "Acceso denegado. Debes iniciar sesión como administrador.";
    exit;
}

// ====== AGREGAR CATEGORÍA ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $nombre = trim($_POST['nombre_categoria'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($nombre) {
        $stmt = $conn->prepare("INSERT INTO categoria (nombre_categoria, descripcion) VALUES (?, ?)");
        $stmt->bind_param("ss", $nombre, $descripcion);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: categorias_crud.php");
    exit;
}

// ====== ELIMINAR CATEGORÍA ======
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM categoria WHERE id_categoria = $id");
    header("Location: categorias_crud.php");
    exit;
}

// ====== EDITAR CATEGORÍA ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar'])) {
    $id = intval($_POST['id_categoria']);
    $nombre = trim($_POST['nombre_categoria']);
    $descripcion = trim($_POST['descripcion']);

    $stmt = $conn->prepare("UPDATE categoria SET nombre_categoria=?, descripcion=? WHERE id_categoria=?");
    $stmt->bind_param("ssi", $nombre, $descripcion, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: categorias_crud.php");
    exit;
}

// ====== LISTAR CATEGORÍAS ======
$result = $conn->query("SELECT * FROM categoria ORDER BY id_categoria DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Categorías</title>
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

<h1>📂 Gestión de Categorías</h1>

<!-- Formulario Agregar -->
<form method="POST">
    <h3>➕ Agregar nueva categoría</h3>
    <input type="text" name="nombre_categoria" placeholder="Nombre de la categoría" required>
    <textarea name="descripcion" placeholder="Descripción"></textarea>
    <button type="submit" name="agregar" class="agregar">Agregar</button>
</form>

<!-- Tabla Categorías -->
<table>
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Descripción</th>
        <th>Acciones</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id_categoria'] ?></td>
        <td><?= htmlspecialchars($row['nombre_categoria']) ?></td>
        <td><?= htmlspecialchars($row['descripcion']) ?></td>
        <td>
            <!-- Formulario Editar inline -->
            <form method="POST" style="display:inline-block;">
                <input type="hidden" name="id_categoria" value="<?= $row['id_categoria'] ?>">
                <input type="text" name="nombre_categoria" value="<?= htmlspecialchars($row['nombre_categoria']) ?>" required>
                <input type="text" name="descripcion" value="<?= htmlspecialchars($row['descripcion']) ?>">
                <button type="submit" name="editar" class="editar">Editar</button>
            </form>
            <a href="?delete=<?= $row['id_categoria'] ?>" class="eliminar" onclick="return confirm('¿Eliminar esta categoría?')">Eliminar</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<a href="../public/panel_admin.php">⬅️ Volver al Panel</a>

</body>
</html>
