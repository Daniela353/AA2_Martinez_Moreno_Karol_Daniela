<?php
// categorias_form.php
session_start();
include __DIR__ . "/../conexion.php";

$categoria = null;
$id = $_GET['id'] ?? null;

// Si existe un ID, busca la categoría para editar
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM categoria WHERE id_categoria = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $categoria = $res->fetch_assoc();
    $stmt->close();
}
?>

<h1><?= $id ? 'Editar Categoría' : 'Agregar Categoría' ?></h1>

<form id="formCategoria">
    <input type="hidden" name="accion" value="<?= $id ? 'editar' : 'agregar' ?>">
    <?php if ($id): ?>
        <input type="hidden" name="id_categoria" value="<?= htmlspecialchars($categoria['id_categoria'] ?? '') ?>">
    <?php endif; ?>
    
    <label for="nombre_categoria">Nombre de la Categoría:</label>
    <input type="text" id="nombre_categoria" name="nombre_categoria" value="<?= htmlspecialchars($categoria['nombre_categoria'] ?? '') ?>" required><br>

    <label for="descripcion">Descripción:</label>
    <textarea id="descripcion" name="descripcion"><?= htmlspecialchars($categoria['descripcion'] ?? '') ?></textarea><br>

    <button type="submit"><?= $id ? 'Actualizar' : 'Agregar' ?></button>
    <button type="button" id="btnRegresar">Regresar</button>
</form>

<script>
    document.getElementById('formCategoria').addEventListener('submit', function(event) {
        event.preventDefault(); 

        const formData = new FormData(this);

        fetch('/AA2_Martinez_Moreno_Karol_Daniela/src/admin/categorias_accion.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.status === 'ok') {
                alert('Operación realizada correctamente');
                window.parent.mostrar('categorias'); 
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
        window.parent.mostrar('categorias');
    });
</script>