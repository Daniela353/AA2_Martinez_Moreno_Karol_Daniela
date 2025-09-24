<?php
session_start();
include __DIR__ . "/../conexion.php";

// Verificar administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    echo "Acceso denegado";
    exit;
}

$id_oferta = $_GET['id_oferta'] ?? null;

// Datos para editar
$oferta = [
    "id_oferta" => "",
    "id_dispositivo" => "",
    "precio_original" => "",
    "descuento_porcentaje" => "",
    "estado" => "inactiva",
    "fecha_inicio" => "",
    "fecha_fin" => ""
];

if ($id_oferta) {
    $stmt = $conn->prepare("SELECT * FROM ofertas WHERE id_oferta=?");
    $stmt->bind_param("i", $id_oferta);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        $oferta = $result->fetch_assoc();
    }
    $stmt->close();
}

// Lista de dispositivos
$disp = $conn->query("SELECT id_dispositivo, nombre FROM dispositivo");
?>

<div style="padding:20px; background:#fff; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1);">
    <h2><?= $id_oferta ? "Editar Oferta" : "Agregar Oferta" ?></h2>

    <form id="formOferta">
        <input type="hidden" name="id_oferta" value="<?= $oferta['id_oferta'] ?>">

        <label>Dispositivo:</label>
        <select name="id_dispositivo" required>
            <option value="">-- Selecciona un dispositivo --</option>
            <?php while($d = $disp->fetch_assoc()): ?>
                <option value="<?= $d['id_dispositivo'] ?>" <?= $d['id_dispositivo']==$oferta['id_dispositivo'] ? "selected" : "" ?>>
                    <?= $d['nombre'] ?>
                </option>
            <?php endwhile; ?>
        </select><br>

        <input type="number" step="0.01" name="precio_original" placeholder="Precio original" required value="<?= $oferta['precio_original'] ?>">
        <input type="number" step="0.01" name="descuento_porcentaje" placeholder="% Descuento" required value="<?= $oferta['descuento_porcentaje'] ?>">

        <select name="estado">
            <option value="activa" <?= $oferta['estado']=="activa" ? "selected" : "" ?>>Activa</option>
            <option value="inactiva" <?= $oferta['estado']=="inactiva" ? "selected" : "" ?>>Inactiva</option>
        </select><br>

        <label>Fecha inicio:</label>
        <input type="date" name="fecha_inicio" value="<?= $oferta['fecha_inicio'] ?>">
        <label>Fecha fin:</label>
        <input type="date" name="fecha_fin" value="<?= $oferta['fecha_fin'] ?>"><br><br>

        <button type="submit"><?= $id_oferta ? "Actualizar Oferta" : "Agregar Oferta" ?></button>
        <button type="button" id="btnRegresar">⬅️ Volver</button>
    </form>
</div>

<script>
const form = document.getElementById('formOferta');

form.addEventListener('submit', function(e){
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('accion', '<?= $id_oferta ? "editar" : "agregar" ?>');

    fetch('/AA2_Martinez_Moreno_Karol_Daniela/src/admin/ofertas_accion.php', {
        method:'POST',
        body: formData
    })
    .then(res => res.json())
    .then(resp => {
        if(resp.status === 'ok'){
            alert('Operación realizada correctamente');
            mostrar('ofertas'); // vuelve al listado
        } else {
            alert('Error: ' + resp.msg);
        }
    })
    .catch(err => console.error(err));
});

// Botón volver
document.getElementById('btnRegresar').addEventListener('click', function(){
    mostrar('ofertas');
});
</script>
