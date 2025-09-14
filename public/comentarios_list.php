<?php
session_start();
$usuario_id = $_SESSION['id_usuario'] ?? 0; // 0 = invitado
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Contacto / Comentarios</title>
<style>
body { font-family: Arial, sans-serif; background-color: #fff5f8; padding: 2rem; }
form { max-width: 500px; margin: auto; background: #ffe4f1; padding: 1.5rem; border-radius: 10px; }
label { display: block; margin-bottom: 0.5rem; }
input, textarea, select, button { width: 100%; padding: 0.5rem; margin-bottom: 1rem; border-radius: 6px; border: 1px solid #ff69b4; }
button { background-color: #ff69b4; color: white; border: none; cursor: pointer; }
button:hover { background-color: #d88ab1; }
#msg { text-align: center; margin-top: 1rem; font-weight: bold; }
</style>
</head>
<body>

<h2 style="text-align:center;">Enviar comentario</h2>

<form id="formComentario">
    <label>Dispositivo:
        <select name="id_dispositivo" required>
            <?php
            include __DIR__ . "/../src/conexion.php";
            $res = $conn->query("SELECT id_dispositivo, Nombre FROM dispositivo ORDER BY Nombre ASC");
            while($row = $res->fetch_assoc()){
                echo "<option value='{$row['id_dispositivo']}'>{$row['Nombre']}</option>";
            }
            ?>
        </select>
    </label>

    <?php if($usuario_id==0): ?>
    <label>Nombre (invitado):
        <input type="text" name="nombre_invitado" required>
    </label>
    <label>Email (invitado):
        <input type="email" name="email_invitado" required>
    </label>
    <?php endif; ?>

    <label>Comentario:
        <textarea name="comentario" rows="4" required></textarea>
    </label>

    <button type="submit">Enviar</button>
</form>

<div id="msg"></div>

<script>
const form = document.getElementById('formComentario');

form.addEventListener('submit', async e => {
    e.preventDefault();
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    data.id_usuario = <?= $usuario_id ?>;

    const res = await fetch('../src/comentarios_crud.php?action=agregar', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify(data)
    });
    const result = await res.json();
    const msgDiv = document.getElementById('msg');

    if(result.success){
        msgDiv.textContent = 'Comentario enviado ✅';
        msgDiv.style.color = 'green';
        form.reset();
    } else {
        msgDiv.textContent = 'Error: ' + (result.error || 'No se pudo enviar');
        msgDiv.style.color = 'red';
    }
});
</script>

</body>
</html>
