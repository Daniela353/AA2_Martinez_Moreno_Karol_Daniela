<?php
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['rol'] !== 'Administrador') {
    die("Acceso denegado.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel - Dispositivos</title>
  <style>
    body { font-family: Arial; padding:20px; color:#000; background:#fff; }
    h2 { margin-bottom:20px; }
    table { width:100%; border-collapse: collapse; margin-top:10px; }
    th, td { border:1px solid #ccc; padding:8px; text-align:left; }
    th { background:#111; color:#fff; }
    tr:nth-child(even) { background:#f9f9f9; }
    button { padding:5px 10px; margin:0 3px; cursor:pointer; }
    #filtroDispositivo { padding:5px; margin-right:10px; width:250px; }
  </style>
</head>
<body>
  <h2>📋 Gestión de Dispositivos</h2>

  <!-- Input de filtro -->
  <label for="filtroDispositivo">Filtrar por nombre o marca:</label>
  <input type="text" id="filtroDispositivo" placeholder="Nombre o ID de marca">

  <!-- Botón agregar -->
  <button onclick="abrirFormulario()">➕ Agregar dispositivo</button>

  <table id="tablaDispositivos">
    <thead>
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Marca</th>
        <th>Tipo</th>
        <th>Categoría</th>
        <th>Precio</th>
        <th>Stock</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <tr><td colspan="8">Cargando datos...</td></tr>
    </tbody>
  </table>
</body>
</html>
