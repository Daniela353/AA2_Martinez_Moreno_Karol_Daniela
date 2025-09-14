<?php
session_start();
include __DIR__ . "/../conexion.php";

// Verificar si es administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    echo "Acceso denegado. Debes iniciar sesión como administrador.";
    exit;
}

// ====== AGREGAR OFERTA ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $id_dispositivo = intval($_POST['id_dispositivo']);
    $precio_original = floatval($_POST['precio_original']);
    $descuento_porcentaje = floatval($_POST['descuento_porcentaje']);
    $precio_final = $precio_original - ($precio_original * ($descuento_porcentaje / 100));
    $estado = $_POST['estado'] ?? 'inactiva';
    $fecha_inicio = $_POST['fecha_inicio'] ?? null;
    $fecha_fin = $_POST['fecha_fin'] ?? null;

    $stmt = $conn->prepare("INSERT INTO ofertas (id_dispositivo, precio_original, descuento_porcentaje, precio_final, estado, fecha_inicio, fecha_fin) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idddsss", $id_dispositivo, $precio_original, $descuento_porcentaje, $precio_final, $estado, $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $stmt->close();
}

// ====== ELIMINAR OFERTA ======
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM ofertas WHERE id_oferta = $id");
}

// ====== LISTAR OFERTAS ======
$result = $conn->query("SELECT o.*, d.nombre 
                        FROM ofertas o 
                        LEFT JOIN dispositivo d ON o.id_dispositivo = d.id_dispositivo 
                        ORDER BY o.id_oferta DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Ofertas</title>
<style>
body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px; }
h1 { color: #333; margin-bottom: 20px; }
table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
th { background: #333; color: white; }
tr:nth-child(even) { background: #f2f2f2; }
form { margin-bottom: 20px; padding: 20px; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
input, select { padding: 8px; margin: 5px; }
button { padding: 10px 15px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; }
button:hover { background: #218838; }
a { color: red; text-decoration: none; }
</style>
</head>
<body>

<h1>🎉 Gestión de Ofertas</h1>

<!-- Formulario Agregar -->
<form method="POST">
    <h3>Agregar nueva oferta</h3>
    <label>Dispositivo:</label>
    <select name="id_dispositivo" required>
        <option value="">-- Selecciona un dispositivo --</option>
        <?php
        $disp = $conn->query("SELECT id_dispositivo, nombre FROM dispositivo");
        while ($d = $disp->fetch_assoc()): ?>
            <option value="<?= $d['id_dispositivo'] ?>"><?= $d['nombre'] ?></option>
        <?php endwhile; ?>
    </select><br>
    <input type="number" step="0.01" name="precio_original" placeholder="Precio original" required>
    <input type="number" step="0.01" name="descuento_porcentaje" placeholder="% Descuento" required>
    <select name="estado">
        <option value="activa">Activa</option>
        <option value="inactiva">Inactiva</option>
    </select><br>
    <label>Fecha inicio:</label>
    <input type="date" name="fecha_inicio">
    <label>Fecha fin:</label>
    <input type="date" name="fecha_fin"><br>
    <button type="submit" name="agregar">Agregar Oferta</button>
</form>

<!-- Tabla Ofertas -->
<table>
    <tr>
        <th>ID</th>
        <th>Dispositivo</th>
        <th>Precio Original</th>
        <th>Descuento (%)</th>
        <th>Precio Final</th>
        <th>Estado</th>
        <th>Inicio</th>
        <th>Fin</th>
        <th>Acciones</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id_oferta'] ?></td>
        <td><?= $row['nombre'] ?></td>
        <td>$<?= number_format($row['precio_original'], 2) ?></td>
        <td><?= $row['descuento_porcentaje'] ?>%</td>
        <td>$<?= number_format($row['precio_final'], 2) ?></td>
        <td><?= ucfirst($row['estado']) ?></td>
        <td><?= $row['fecha_inicio'] ?></td>
        <td><?= $row['fecha_fin'] ?></td>
        <td>
            <a href="?delete=<?= $row['id_oferta'] ?>" onclick="return confirm('¿Seguro que deseas eliminar esta oferta?')">Eliminar</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<a href="../../public/panel_admin.php">⬅️ Volver al Panel</a>

</body>
</html>
