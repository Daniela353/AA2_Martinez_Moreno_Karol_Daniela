<?php
session_start();
include __DIR__ . "/../conexion.php";

// Verificar si es administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    echo "Acceso denegado. Debes iniciar sesión como administrador.";
    exit;
}

// ====== AGREGAR DISPOSITIVO ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $nombre = $_POST['Nombre'];
    $marca = intval($_POST['Marca']);
    $tipo = $_POST['tipo'];
    $categoria = intval($_POST['Categoria']);
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $oferta = isset($_POST['oferta']) ? 1 : 0;
    $fecha = $_POST['fecha_lanzamiento'];
    $resena = $_POST['resena'];
    $descripcion = $_POST['descripcion'];
    $componentes = $_POST['componentes'];
    $imagen = $_POST['imagen'];

    $stmt = $conn->prepare("INSERT INTO dispositivo 
        (Nombre, Marca, tipo, Categoria, precio, stock, oferta, fecha_lanzamiento, resena, descripcion, componentes, imagen) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sisiidisssss", $nombre, $marca, $tipo, $categoria, $precio, $stock, $oferta, $fecha, $resena, $descripcion, $componentes, $imagen);
    $stmt->execute();
    $stmt->close();
    header("Location: dispositivos_crud.php");
    exit;
}

// ====== ELIMINAR DISPOSITIVO ======
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM dispositivo WHERE id_dispositivo = $id");
    header("Location: dispositivos_crud.php");
    exit;
}

// ====== EDITAR DISPOSITIVO ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar'])) {
    $id = intval($_POST['id_dispositivo']);
    $nombre = $_POST['Nombre'];
    $marca = intval($_POST['Marca']);
    $tipo = $_POST['tipo'];
    $categoria = intval($_POST['Categoria']);
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $oferta = isset($_POST['oferta']) ? 1 : 0;
    $fecha = $_POST['fecha_lanzamiento'];
    $resena = $_POST['resena'];
    $descripcion = $_POST['descripcion'];
    $componentes = $_POST['componentes'];
    $imagen = $_POST['imagen'];

    $stmt = $conn->prepare("UPDATE dispositivo SET 
        Nombre=?, Marca=?, tipo=?, Categoria=?, precio=?, stock=?, oferta=?, fecha_lanzamiento=?, resena=?, descripcion=?, componentes=?, imagen=? 
        WHERE id_dispositivo=?");
    $stmt->bind_param("sisiidisssssi", $nombre, $marca, $tipo, $categoria, $precio, $stock, $oferta, $fecha, $resena, $descripcion, $componentes, $imagen, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: dispositivos_crud.php");
    exit;
}

// ====== LISTAR DISPOSITIVOS ======
$result = $conn->query("SELECT * FROM dispositivo ORDER BY id_dispositivo DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Dispositivos</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f9f9f9;
    padding: 20px;
}
h1 {
    color: #333;
    margin-bottom: 20px;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 30px;
}
th, td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}
th {
    background: #333;
    color: white;
}
tr:nth-child(even) {
    background: #f2f2f2;
}
form {
    margin-bottom: 20px;
    padding: 20px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
input, textarea {
    padding: 8px;
    margin: 5px;
    width: 90%;
}
button {
    padding: 10px 15px;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
button:hover {
    background: #218838;
}
a {
    color: red;
    text-decoration: none;
}
</style>
</head>
<body>

<h1>📱 Gestión de Dispositivos</h1>

<!-- Formulario Agregar -->
<form method="POST">
    <h3>Agregar nuevo dispositivo</h3>
    <input type="text" name="Nombre" placeholder="Nombre" required>
    <input type="number" step="0.01" name="precio" placeholder="Precio" required>
    <input type="number" name="Marca" placeholder="ID Marca" required>
    <input type="number" name="Categoria" placeholder="ID Categoría" required>
    <input type="text" name="tipo" placeholder="Tipo">
    <input type="number" name="stock" placeholder="Stock">
    <label><input type="checkbox" name="oferta"> Oferta</label>
    <input type="date" name="fecha_lanzamiento">
    <textarea name="resena" placeholder="Reseña"></textarea>
    <textarea name="descripcion" placeholder="Descripción"></textarea>
    <textarea name="componentes" placeholder="Componentes"></textarea>
    <input type="text" name="imagen" placeholder="URL Imagen">
    <button type="submit" name="agregar">Agregar</button>
</form>

<!-- Tabla Dispositivos -->
<table>
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Marca</th>
        <th>Tipo</th>
        <th>Categoría</th>
        <th>Precio</th>
        <th>Stock</th>
        <th>Oferta</th>
        <th>Fecha</th>
        <th>Acciones</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id_dispositivo'] ?></td>
        <td><?= $row['Nombre'] ?></td>
        <td><?= $row['Marca'] ?></td>
        <td><?= $row['tipo'] ?></td>
        <td><?= $row['Categoria'] ?></td>
        <td>$<?= number_format($row['precio'], 2) ?></td>
        <td><?= $row['stock'] ?></td>
        <td><?= $row['oferta'] ? 'Sí' : 'No' ?></td>
        <td><?= $row['fecha_lanzamiento'] ?></td>
        <td>
            <!-- Botón Editar -->
            <form method="POST" style="display:inline-block;">
                <input type="hidden" name="id_dispositivo" value="<?= $row['id_dispositivo'] ?>">
                <input type="hidden" name="Nombre" value="<?= $row['Nombre'] ?>">
                <input type="hidden" name="Marca" value="<?= $row['Marca'] ?>">
                <input type="hidden" name="tipo" value="<?= $row['tipo'] ?>">
                <input type="hidden" name="Categoria" value="<?= $row['Categoria'] ?>">
                <input type="hidden" name="precio" value="<?= $row['precio'] ?>">
                <input type="hidden" name="stock" value="<?= $row['stock'] ?>">
                <input type="hidden" name="oferta" value="<?= $row['oferta'] ?>">
                <input type="hidden" name="fecha_lanzamiento" value="<?= $row['fecha_lanzamiento'] ?>">
                <input type="hidden" name="resena" value="<?= $row['resena'] ?>">
                <input type="hidden" name="descripcion" value="<?= $row['descripcion'] ?>">
                <input type="hidden" name="componentes" value="<?= $row['componentes'] ?>">
                <input type="hidden" name="imagen" value="<?= $row['imagen'] ?>">
                <button type="submit" name="editar">Editar</button>
            </form>

            <!-- Botón Eliminar -->
            <a href="?delete=<?= $row['id_dispositivo'] ?>" onclick="return confirm('¿Seguro que deseas eliminar este dispositivo?')">Eliminar</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<a href="../../public/panel_admin.php">⬅️ Volver al Panel</a>

</body>
</html>

