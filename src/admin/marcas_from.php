<?php
// marcas_form.php
session_start();
include __DIR__ . "/../conexion.php";

$marca = null;
$id = $_GET['id'] ?? null;

// Si existe un ID, busca la marca para editar
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM marca WHERE id_marca = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $marca = $res->fetch_assoc();
    $stmt->close();
}
?>

<h1><?= $id ? 'Editar Marca' : 'Agregar Marca' ?></h1>

<form id="formMarca">
    <input type="hidden" name="accion" value="<?= $id ? 'editar' : 'agregar' ?>">
    <?php if ($id): ?>
        <input type="hidden" name="id_marca" value="<?= htmlspecialchars($marca['id_marca'] ?? '') ?>">
    <?php endif; ?>
    
    <label for="nombre_marca">Nombre de la Marca:</label>
    <input type="text" id="nombre_marca" name="nombre_marca" value="<?= htmlspecialchars($marca['nombre_marca'] ?? '') ?>" required><br>

    <label for="pais_origen">País de Origen:</label>
    <input type="text" id="pais_origen" name="pais_origen" value="<?= htmlspecialchars($marca['pais_origen'] ?? '') ?>"><br>

    <label for="descripcion">Descripción:</label>
    <textarea id="descripcion" name="descripcion"><?= htmlspecialchars($marca['descripcion'] ?? '') ?></textarea><br>

    <button type="submit"><?= $id ? 'Actualizar' : 'Agregar' ?></button>
    <button type="button" id="btnRegresar">Regresar</button>
</form>

<script>
    document.getElementById('formMarca').addEventListener('submit', function(event) {
        event.preventDefault(); // Evita que el formulario se envíe de forma tradicional

        const formData = new FormData(this);

        // **AQUÍ ESTÁ LA LÍNEA CORREGIDA**
        fetch('/AA2_Martinez_Moreno_Karol_Daniela/src/admin/marcas_accion.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.status === 'ok') {
                alert('Operación realizada correctamente');
                // Llama a la función `mostrar()` de `panel_admin.php`
                window.parent.mostrar('marcas'); 
            } else {
                alert('Error: ' + resp.msg);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error al procesar la solicitud.');
        });
    });

    document.getElementById('btnRegresar').addEventListener('click', function() {
        // Llama a la función `mostrar()` de `panel_admin.php` para regresar al listado
        window.parent.mostrar('marcas');
    });
</script>