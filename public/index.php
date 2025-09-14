<?php
session_start();
include __DIR__ . "/../src/conexion.php";

$rol = $_SESSION['rol'] ?? 'Invitado';
$usuario_id = $_SESSION['id_usuario'] ?? null;
$nombre_usuario = $_SESSION['nombre'] ?? '';
$esAdmin = ($rol === 'Administrador');

$filtroOferta = isset($_GET['oferta']) ? true : false;

// Traer dispositivos con fotos, marca y posibles ofertas activas
$stmt = $conn->prepare("
    SELECT d.*, i.imagen_secundaria, m.nombre_marca,
           o.id_oferta, o.precio_original, o.precio_final, o.descuento_porcentaje
    FROM dispositivo d
    LEFT JOIN imagen i ON d.id_dispositivo = i.id_dispositivo
    LEFT JOIN marca m ON d.Marca = m.id_marca
    LEFT JOIN ofertas o ON d.id_dispositivo = o.id_dispositivo AND o.estado='Activa'
    ORDER BY d.id_dispositivo ASC
");
$stmt->execute();
$result = $stmt->get_result();

$dispositivos = [];
while($row = $result->fetch_assoc()){
    if($filtroOferta && !$row['id_oferta']) continue;

    $id = $row['id_dispositivo'];
    if(!isset($dispositivos[$id])){
        $dispositivos[$id] = $row;
        $dispositivos[$id]['fotos'] = [];
        $dispositivos[$id]['oferta'] = $row['id_oferta'] ? 1 : 0;
        if($row['id_oferta']){
            $dispositivos[$id]['precio_original'] = $row['precio_original'];
            $dispositivos[$id]['precio_descuento'] = $row['precio_final'];
        }
    }
    if($row['imagen_secundaria']){
        $dispositivos[$id]['fotos'][] = $row['imagen_secundaria'];
    }
}
$dispositivos = array_values($dispositivos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dispositivos Inteligentes</title>
<style>
body { font-family: 'Montserrat', sans-serif; background-color: #fff5f8; margin: 0; display: flex; }
/* Sidebar */
.sidebar { width: 240px; background-color: #d88ab1; color: white; height: 100vh; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; align-items: center; padding: 1rem; }
.sidebar img { width: 200px; margin-bottom: 0.8rem; }
.sidebar nav a, .sidebar nav button { color: white; text-decoration: none; display: block; padding: 0.6rem; border-radius: 8px; margin: 0.4rem 0; background:none; border:none; cursor:pointer; text-align:center; }
.sidebar nav a:hover, .sidebar nav button:hover { background: #ffb6c1; }
/* Main content */
.main-content { margin-left: 240px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
header { background-color: #ffb6c1; color: white; text-align: center; padding: 1rem; border-bottom-left-radius: 20px; border-bottom-right-radius: 20px; }
.filters { display: flex; justify-content: center; gap: 1rem; margin: 1rem 0; }
.filters input, .filters select { padding: 0.5rem; border-radius: 8px; border: 1px solid #ff69b4; }
#device-list { display: flex; flex-wrap: wrap; justify-content: center; gap: 1.5rem; padding: 1rem; }
/* Tarjetas */
.device-card { background-color: #fff; border-radius: 15px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 230px; text-align: center; padding: 1rem; transition: transform 0.3s, box-shadow 0.3s; cursor: pointer; position: relative; }
.device-card:hover { transform: translateY(-7px); box-shadow: 0 6px 12px rgba(0,0,0,0.2); }
.device-card img { width: 100%; border-radius: 10px; margin-bottom: 0.8rem; }
.device-card h3 { margin: 0.5rem 0; color: #ff69b4; }
.precio { font-weight: 600; margin: 0.3rem 0; }
.precio-original { text-decoration: line-through; color: #888; font-size: 0.9rem; }
.precio-descuento { font-weight: 600; color: #ff69b4; margin-left: 5px; }
.oferta-badge { position: absolute; top: 10px; right: 10px; background-color: red; color: white; padding: 5px 10px; border-radius: 8px; font-weight: bold; font-size: 0.85rem; z-index: 10; }
.admin-btn { margin:0.3rem; padding:0.3rem 0.6rem; border:none; border-radius:6px; background:#2d89ef; color:white; cursor:pointer; }
.admin-btn:hover { background:#1b5fa7; }
/* Modal */
.modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(255,240,245,0.95); justify-content: center; align-items: center; overflow-y: auto; z-index:1000; }
.modal-content { background:white; padding:2rem; border-radius:15px; max-width:600px; width:90%; text-align:center; position: relative; }
.close { position:absolute; top:10px; right:15px; font-size:1.5rem; cursor:pointer; color:#d88ab1; }
#galeria { display: flex; align-items: center; justify-content: center; gap: 1rem; margin-bottom: 1rem; }
#galeria button { background: #ff69b4; color: white; border: none; border-radius: 50%; width: 35px; height: 35px; font-size: 1.2rem; cursor: pointer; display: flex; align-items: center; justify-content: center; }
#fotoActual { max-width: 80%; border-radius: 10px; }
</style>
</head>
<body>
<aside class="sidebar">
  <img src="imagenes/logo.png" alt="Logo">
  <nav>
    <a href="index.php">Inicio</a>
    <a href="index.php">Dispositivos</a>
    <a href="index.php?oferta=1">Ofertas</a>
    <a href="contacto_form.php">Contacto</a>

    <?php if(!$usuario_id): ?>
      <a href="login.php">Iniciar Sesión</a>
    <?php else: ?>
      <button onclick="if(confirm('¿Deseas cerrar sesión?')) window.location.href='logout.php';">Cerrar sesión</button>
    <?php endif; ?>
  </nav>
</aside>

<div class="main-content">
  <header>
    <h1>Dispositivos Inteligentes</h1>
    <p>Usuario: <?= htmlspecialchars($nombre_usuario) ?> | Rol: <?= $rol ?></p>
  </header>

  <div class="filters">
    <input type="text" id="busqueda" placeholder="Buscar dispositivo...">
    <select id="filtroMarca">
      <option value="">Todas las marcas</option>
      <?php
        $marcas = [];
        foreach($dispositivos as $d) $marcas[$d['nombre_marca']] = true;
        foreach(array_keys($marcas) as $m) echo "<option value=\"$m\">$m</option>";
      ?>
    </select>
  </div>

  <div id="device-list"></div>

  <?php if($esAdmin): ?>
    <button class="admin-btn" onclick="alert('Agregar dispositivo');">Agregar Dispositivo</button>
  <?php endif; ?>
</div>

<!-- Modal -->
<div id="deviceModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <div id="galeria">
      <button id="prevFoto">&lt;</button>
      <img id="fotoActual" src="" alt="">
      <button id="nextFoto">&gt;</button>
    </div>
    <h2 id="modal-title"></h2>
    <p id="modal-marca"></p>
    <p id="modal-tipo"></p>
    <p id="modal-precio"></p>
    <p id="modal-lanzamiento"></p>
    <p id="modal-descripcion"></p>
  </div>
</div>

<script>
let dispositivos = <?= json_encode($dispositivos) ?>;
const esAdmin = <?= $esAdmin ? 'true':'false' ?>;

let dispositivoActual, fotosActuales=[], indiceFoto=0;

function mostrarDispositivos(lista){
  const container = document.getElementById('device-list');
  container.innerHTML='';
  if(lista.length===0){ container.innerHTML='<p>No hay dispositivos</p>'; return; }

  lista.forEach(d=>{
    const card = document.createElement('div');
    card.classList.add('device-card');
    card.onclick = ()=>abrirModal(d);

    let imgSrc = d.imagen;
    if(!imgSrc && d.fotos && d.fotos.length>0) imgSrc=d.fotos[0];

    let html='';
    if(d.oferta==1) html += '<div class="oferta-badge">OFERTA</div>';

    html += `
      <img src="${imgSrc}" alt="${d.Nombre}">
      <h3>${d.Nombre}</h3>
      <p>Marca: ${d.nombre_marca}</p>
      <p>Tipo: ${d.tipo}</p>
      <p>Lanzamiento: ${d.fecha_lanzamiento}</p>
    `;
    if(d.oferta==1){
      html += `<p class="precio-original">$${Number(d.precio_original).toLocaleString()}</p>
               <p class="precio-descuento">$${Number(d.precio_descuento).toLocaleString()}</p>`;
    }else{
      html += `<p class="precio">$${Number(d.precio).toLocaleString()}</p>`;
    }

    if(esAdmin){
      html += `<button class="admin-btn" onclick="event.stopPropagation(); alert('Editar ID: ${d.id_dispositivo}')">Editar</button>
               <button class="admin-btn" onclick="event.stopPropagation(); if(confirm('Eliminar dispositivo?')) alert('Eliminar ID: ${d.id_dispositivo}')">Eliminar</button>`;
    }

    card.innerHTML = html;
    container.appendChild(card);
  });
}

// Modal y carrusel
const modal = document.getElementById('deviceModal');
const fotoActual = document.getElementById('fotoActual');
const prevFotoBtn = document.getElementById('prevFoto');
const nextFotoBtn = document.getElementById('nextFoto');

function abrirModal(d){
  dispositivoActual = d;
  fotosActuales = d.fotos && d.fotos.length>0 ? d.fotos : [d.imagen];
  indiceFoto = 0;
  fotoActual.src = fotosActuales[indiceFoto];

  document.getElementById('modal-title').textContent = d.Nombre;
  document.getElementById('modal-marca').textContent = 'Marca: '+d.nombre_marca;
  document.getElementById('modal-tipo').textContent = 'Tipo: '+d.tipo;
  if(d.oferta==1){
    document.getElementById('modal-precio').textContent = `Precio: $${Number(d.precio_descuento).toLocaleString()} (Antes: $${Number(d.precio_original).toLocaleString()})`;
  }else{
    document.getElementById('modal-precio').textContent = `Precio: $${Number(d.precio).toLocaleString()}`;
  }
  document.getElementById('modal-lanzamiento').textContent = 'Lanzamiento: '+d.fecha_lanzamiento;
  document.getElementById('modal-descripcion').textContent = d.resena || '';

  modal.style.display = 'flex';
  document.body.style.overflow='hidden';
}

prevFotoBtn.onclick = ()=>{ indiceFoto=(indiceFoto-1+fotosActuales.length)%fotosActuales.length; fotoActual.src=fotosActuales[indiceFoto]; }
nextFotoBtn.onclick = ()=>{ indiceFoto=(indiceFoto+1)%fotosActuales.length; fotoActual.src=fotosActuales[indiceFoto]; }

document.querySelector('.close').onclick = ()=>{ modal.style.display='none'; document.body.style.overflow='auto'; }
window.onclick = e=>{ if(e.target===modal){ modal.style.display='none'; document.body.style.overflow='auto'; } }

// Filtros
document.getElementById('busqueda').addEventListener('input', ()=>{
  const q = document.getElementById('busqueda').value.toLowerCase();
  const marca = document.getElementById('filtroMarca').value;
  mostrarDispositivos(dispositivos.filter(d=>{
    return d.Nombre.toLowerCase().includes(q) && (marca==='' || d.nombre_marca===marca);
  }));
});

document.getElementById('filtroMarca').addEventListener('change', ()=>{
  const q = document.getElementById('busqueda').value.toLowerCase();
  const marca = document.getElementById('filtroMarca').value;
  mostrarDispositivos(dispositivos.filter(d=>{
    return d.Nombre.toLowerCase().includes(q) && (marca==='' || d.nombre_marca===marca);
  }));
});

// Mostrar inicialmente todos
mostrarDispositivos(dispositivos);
</script>
</body>
</html>
