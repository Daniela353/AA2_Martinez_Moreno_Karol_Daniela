<?php
session_start();
include __DIR__ . "/../conexion.php";

if(!isset($_SESSION['admin_id']) || $_SESSION['rol']!=='Administrador'){
    die("Acceso denegado.");
}

// POST = actualizar mensaje
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['id_contacto'])){
    $id = intval($_POST['id_contacto']);
    $respuesta = $conn->real_escape_string($_POST['respuesta'] ?? '');
    $estado = $conn->real_escape_string($_POST['estado'] ?? 'Pendiente');

    $sql = "UPDATE contacto SET respuesta='$respuesta', estado='$estado' WHERE id_contacto=$id";
    if($conn->query($sql)){
        echo "Mensaje actualizado correctamente.";
    } else {
        echo "Error: ".$conn->error;
    }
    exit;
}

// GET = mostrar detalle
$id = intval($_GET['id_contacto']);
$res = $conn->query("SELECT * FROM contacto WHERE id_contacto=$id");
$mensaje = $res->fetch_assoc();
?>

<h2>Detalle del mensaje</h2>
<p><strong>Nombre:</strong> <?= htmlspecialchars($mensaje['nombre']) ?></p>
<p><strong>Email:</strong> <?= htmlspecialchars($mensaje['email']) ?></p>
<p><strong>Asunto:</strong> <?= htmlspecialchars($mensaje['asunto']) ?></p>
<p><strong>Mensaje:</strong><br><?= nl2br(htmlspecialchars($mensaje['mensaje'])) ?></p>

<form id="form-responder" data-id="<?= $id ?>">
    <label for="respuesta">Respuesta:</label>
    <textarea name="respuesta" id="respuesta" rows="4"><?= htmlspecialchars($mensaje['respuesta']) ?></textarea>

    <label for="estado">Estado:</label>
    <select name="estado" id="estado">
        <option value="Pendiente" <?= $mensaje['estado']=='Pendiente'?'selected':'' ?>>Pendiente</option>
        <option value="Proceso" <?= $mensaje['estado']=='Proceso'?'selected':'' ?>>Proceso</option>
        <option value="Resuelto" <?= $mensaje['estado']=='Resuelto'?'selected':'' ?>>Resuelto</option>
    </select>

    <button type="submit">Guardar</button>
</form>

<button id="volverLista">Volver a la lista</button>
