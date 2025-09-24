<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel - Ofertas</title>
<style>
body { font-family: Arial; padding:20px; color:#000; background:#fff; }
h2 { margin-bottom:20px; }
table { width:100%; border-collapse: collapse; margin-top:10px; }
th, td { border:1px solid #ccc; padding:8px; text-align:center; }
th { background:#111; color:#fff; }
tr:nth-child(even){ background:#f9f9f9; }
button { padding:5px 10px; margin:0 3px; cursor:pointer; }
input { padding:5px; margin:5px 0; }
</style>
</head>
<body>

<h2>🎉 Gestión de Ofertas</h2>

<button onclick="abrirFormularioOferta()">➕ Agregar Oferta</button>
<br><br>

<form id="form-filtros-ofertas">
    <input type="text" id="filtroDispositivo" placeholder="Filtrar por dispositivo...">
    <input type="number" id="filtroDescuento" placeholder="Descuento min. (%)" min="0">
    <button type="submit">🔍 Filtrar</button>
    <button type="button" id="btnLimpiarFiltros">🧹 Limpiar Filtros</button>
</form>

<table id="tablaOfertas">
<thead>
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
</thead>
<tbody>
<tr><td colspan="9">Cargando datos...</td></tr>
</tbody>
</table>

</body>
</html>