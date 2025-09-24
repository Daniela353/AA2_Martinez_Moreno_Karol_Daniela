<?php
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['rol'] !== 'Administrador') {
    die("Acceso denegado.");
}
include __DIR__ . "/../conexion.php";

$dispositivo = null;
$id_dispositivo = $_GET['id_dispositivo'] ?? null;

// Si es modo edición, carga los datos
if ($id_dispositivo) {
    $stmt = $conn->prepare("SELECT * FROM dispositivo WHERE id_dispositivo = ?");
    $stmt->bind_param("i", $id_dispositivo);
    $stmt->execute();
    $res = $stmt->get_result();
    $dispositivo = $res->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Formulario Dispositivo</title>
<style>
body { font-family: Arial, sans-serif; padding: 20px; background-color: #fff; color: #000; }
h2 { margin-bottom: 20px; text-align: center; }
form { max-width: 500px; margin: auto; border: 1px solid #ccc; padding: 20px; border-radius: 8px; }
label { display: block; margin-top: 10px; }
input, select, textarea { width: 100%; padding: 8px; margin-top: 5px; }
button { margin-top: 15px; padding: 8px 12px; cursor: pointer; }
#btnRegresar { background-color: #ccc; margin-right: 10px; }
</style>
</head>
<body>
<h2 id="tituloFormulario"><?= $id_dispositivo ? 'Editar Dispositivo' : 'Agregar Dispositivo' ?></h2>

<form id="formDispositivo" enctype="multipart/form-data">
    <input type="hidden" name="id_dispositivo" id="id_dispositivo" value="<?= htmlspecialchars($dispositivo['id_dispositivo'] ?? '') ?>">
    <label>Nombre:</label>
    <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($dispositivo['Nombre'] ?? '') ?>" required>
    
    <label>Marca:</label>
    <select name="marca" id="marca" required>
        </select>
    
    <label>Tipo:</label>
    <input type="text" name="tipo" id="tipo" value="<?= htmlspecialchars($dispositivo['tipo'] ?? '') ?>" required>
    
    <label>Categoría:</label>
    <select name="categoria" id="categoria" required>
        </select>
    
    <label>Precio:</label>
    <input type="number" step="0.01" name="precio" id="precio" value="<?= htmlspecialchars($dispositivo['precio'] ?? '') ?>" required>
    
    <label>Stock:</label>
    <input type="number" name="stock" id="stock" value="<?= htmlspecialchars($dispositivo['stock'] ?? '') ?>" required>
    
    <label>Imagen:</label>
    <input type="file" name="imagen" id="imagen" accept="image/*">
    <button type="submit">Guardar</button>
    <button type="button" id="btnRegresar">⬅ Regresar</button>
</form>
</body>
</html>
