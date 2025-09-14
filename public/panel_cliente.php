<?php
session_start();
include __DIR__ . "/../src/conexion.php";

$rol = $_SESSION['rol'] ?? 'Invitado';
$usuario_id = $_SESSION['id_usuario'] ?? null;
$usuario_nombre = $_SESSION['nombre_usuario'] ?? 'Cliente';

if(!$usuario_id){
    die("Debes iniciar sesión para acceder al panel de cliente.");
}

$filtroOferta = isset($_GET['oferta']) ? true : false;

// Traer dispositivos con fotos, marca y posibles ofertas activas
$stmt = $conn->prepare("
    SELECT d.*, i.imagen_secundaria, m.nombre_marca,
           o.id_oferta, o.precio_original, o.precio_final
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
<title>Panel Cliente</title>
<style>
/* --- ESTILOS GENERALES --- */
body { font-family: 'Montserrat', sans-serif; background-color: #fff5f8; margin: 0; display: flex; }
.sidebar { width: 240px; background-color: #d88ab1; color: white; height: 100vh; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; align-items: center; padding: 1rem; }
.sidebar img { width: 200px; margin-bottom: 0.8rem; }
.sidebar nav a, .sidebar nav button { color: white; text-decoration: none; display: block; padding: 0.6rem; border-radius: 8px; margin: 0.4rem 0; background:none; border:none; cursor:pointer; text-align:center; }
.sidebar nav a:hover, .sidebar nav button:hover { background: #ffb6c1; }
.main-content { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
header { background-color: #ffb6c1; color: white; text-align: center; padding: 1rem; border-bottom-left-radius: 20px; border-bottom-right-radius: 20px; }
.filters { display: flex; justify-content: center; gap: 1rem; margin: 1rem 0; flex-wrap: wrap; }
.filters input, .filters select { padding: 0.5rem; border-radius: 8px; border: 1px solid #ff69b4; }
#device-list { display: flex; flex-wrap: wrap; justify-content: flex-start; gap: 1.5rem; padding: 1rem 2rem; }
.device-card { background-color: #fff; border-radius: 15px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 230px; text-align: center; padding: 1rem; transition: transform 0.3s, box-shadow 0.3s; cursor: pointer; position: relative; display: flex; flex-direction: column; }
.device-card:hover { transform: translateY(-7px); box-shadow: 0 6px 12px rgba(0,0,0,0.2); }
.device-card img { width: 100%; height: 150px; object-fit: cover; border-radius: 10px; margin-bottom: 0.8rem; }
.device-card h3 { margin: 0.5rem 0; color: #ff69b4; font-size: 1rem; }
.precio { font-weight: 600; margin: 0.3rem 0; }
.precio-original { text-decoration: line-through; color: #888; font-size: 0.9rem; }
.precio-descuento { font-weight: 600; color: #ff69b4; margin-left: 5px; }
.oferta-badge { position: absolute; top: 10px; right: 10px; background-color: red; color: white; padding: 5px 10px; border-radius: 8px; font-weight: bold; font-size: 0.85rem; z-index: 10; }
button { margin:0.3rem; padding:0.3rem 0.6rem; border:none; border-radius:6px; background:#27ae60; color:white; cursor:pointer; }
button:hover { background:#2ecc71; }

/* --- FLECHAS MODAL --- */
#prevFoto, #nextFoto {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    font-size: 2rem;
    background-color: rgba(255,105,180,0.8); /* rosa semitransparente */
    color: white;
    border: none;
    border-radius: 50%;
    width: 45px;
    height: 45px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1001;
    transition: background 0.3s;
}
#prevFoto:hover, #nextFoto:hover { background-color: rgba(0,0,0,0.7); }

/* --- CARRITO --- */
#carrito-list, #carrito-list-modal {
  background:white;
  padding:1rem;
  border-radius:15px;
  box-shadow:0 4px 12px rgba(0,0,0,0.2);
}

.carrito-item {
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin:0.5rem 0;
  padding:0.3rem 0.5rem;
  border-bottom:1px solid #eee;
}

.carrito-item button {
  background:#ff69b4;
  color:white;
  border:none;
  padding:0.2rem 0.5rem;
  border-radius:5px;
  cursor:pointer;
}

.carrito-item button:hover {
  background:#ff85c1;
}

/* --- MODAL --- */
.modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(255,240,245,0.95); justify-content: center; align-items: center; overflow-y: auto; z-index:1000; }
.modal-content { background:white; padding:2rem; border-radius:15px; max-width:600px; width:90%; text-align:center; position: relative; }
.close { position:absolute; top:10px; right:15px; font-size:1.5rem; cursor:pointer; color:#d88ab1; }

/* --- CONTACTO --- */
#contacto-section { display:none; width: 100%; max-width: 600px; margin: 1rem auto; }
#contacto-section form { background: #fff; padding: 2rem; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); width: 100%; text-align: center; }
</style>
</head>
<body>

<aside class="sidebar">
  <img src="imagenes/logo.png" alt="Logo">
  <nav>
    <a href="#" onclick="mostrarSeccion('dispositivos')">Inicio</a>
    <a href="#" onclick="mostrarSeccion('dispositivos')">Dispositivos</a>
    <a href="#" onclick="mostrarSeccion('ofertas')">Ofertas</a>
    <a href="#" onclick="mostrarSeccion('pedidos')">📦 Mis Pedidos</a>
    <a href="#" id="menuCarrito">🛒 Carrito</a>
    <a href="#" onclick="mostrarSeccion('contacto')">📧 Contacto</a>
    <button onclick="if(confirm('¿Deseas cerrar sesión?')) window.location.href='logout.php';">Cerrar sesión</button>
  </nav>
</aside>

<div class="main-content">
  <header>
    <h1>Dispositivos Inteligentes</h1>
    <p>Usuario: <?= htmlspecialchars($usuario_nombre) ?> | Rol: <?= htmlspecialchars($rol) ?></p>
  </header>

  <div class="filters" id="filters-container">
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

  <!-- Contacto -->
  <div id="contacto-section">
    <form id="contactoForm">
      <h2>📧 Contacto</h2>
      <input type="text" name="nombre" placeholder="Nombre" required>
      <input type="email" name="email" placeholder="Email" required>
      <select name="motivo" required>
        <option value="">Selecciona un motivo</option>
        <option value="soporte">Soporte</option>
        <option value="informacion">Información</option>
        <option value="reclamo">Reclamo</option>
        <option value="otro">Otro</option>
      </select>
      <textarea name="mensaje" rows="4" placeholder="Mensaje" required></textarea>
      <button type="submit">Enviar</button>
      <div id="contactoResult" style="margin-top:10px;"></div>
    </form>
  </div>

  <!-- Modal dispositivo con galería -->
  <div id="deviceModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <div style="position:relative;">
        <img id="fotoActual" src="" alt="" style="width:100%; height:300px; object-fit:cover; border-radius:10px;">
        <button id="prevFoto">&#10094;</button>
        <button id="nextFoto">&#10095;</button>
      </div>
      <h2 id="modal-title"></h2>
      <p id="modal-marca"></p>
      <p id="modal-tipo"></p>
      <p id="modal-precio"></p>
      <p id="modal-lanzamiento"></p>
      <p id="modal-descripcion"></p>
      <button id="btnAgregarCarrito">Añadir al Carrito</button>
      <button id="btnComentar">Comentar</button>
    </div>
  </div>

  <!-- Modal comentarios -->
  <div id="comentarioModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h3>Agregar comentario</h3>
      <textarea id="comentarioText" rows="4" style="width:100%; margin-bottom:10px;" placeholder="Escribe tu comentario..."></textarea>
      <div id="calificacionStars" style="margin-bottom:10px; font-size:1.5rem; cursor:pointer;">
        <span data-value="1">☆</span>
        <span data-value="2">☆</span>
        <span data-value="3">☆</span>
        <span data-value="4">☆</span>
        <span data-value="5">☆</span>
      </div>
      <button id="enviarComentario">Enviar</button>
    </div>
  </div>

  <!-- Modal carrito -->
<div id="carritoModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
  <div class="modal-content" style="background:white; padding:20px; max-width:400px; width:90%; position:relative;">
    <span class="close" style="position:absolute; top:5px; right:10px; cursor:pointer;">&times;</span>
    <h2>🛒 Carrito de Compras</h2>
    <div id="carritoModalList">
      <p>Cargando...</p>
    </div>
  </div>
</div>

<!-- Botón flotante para abrir carrito -->
<button id="abrirCarrito" style="position:fixed; bottom:20px; right:20px; background:#ff69b4; color:white; border:none; padding:0.6rem 1rem; border-radius:50px; cursor:pointer;">🛒 Ver Carrito</button>




<script>
const baseURL = 'http://localhost/AA2_Martinez_Moreno_Karol_Daniela/src/cliente/carrito.php';
const dispositivosData = <?= json_encode($dispositivos) ?>;

let dispositivoActual = null;
let indiceFoto = 0;
let fotosModal = [];

// --- Referencias modal dispositivo ---
const modalDispositivo = document.getElementById('deviceModal');
const fotoActual = modalDispositivo.querySelector('#fotoActual');
const prevFoto = modalDispositivo.querySelector('#prevFoto');
const nextFoto = modalDispositivo.querySelector('#nextFoto');
const modalTitulo = modalDispositivo.querySelector('#modal-title');
const modalMarca = modalDispositivo.querySelector('#modal-marca');
const modalTipo = modalDispositivo.querySelector('#modal-tipo');
const modalPrecio = modalDispositivo.querySelector('#modal-precio');
const modalLanzamiento = modalDispositivo.querySelector('#modal-lanzamiento');
const modalDescripcion = modalDispositivo.querySelector('#modal-descripcion');
const btnAgregarCarrito = modalDispositivo.querySelector('#btnAgregarCarrito');
const btnComentar = modalDispositivo.querySelector('#btnComentar');
const menuCarrito = document.getElementById('menuCarrito');

// --- Modal carrito ---
const carritoModal = document.getElementById('carritoModal');
const carritoModalList = document.getElementById('carritoModalList');
const btnAbrirCarrito = document.getElementById('abrirCarrito');

// --- Modal comentarios ---
const modalComentario = document.getElementById('comentarioModal');
const enviarComentario = modalComentario.querySelector('#enviarComentario');
const comentarioText = modalComentario.querySelector('#comentarioText');
const estrellas = modalComentario.querySelectorAll('#calificacionStars span');
let calificacionActual = 0;


// Abrir modal carrito desde botón flotante
btnAbrirCarrito.onclick = () => {
    carritoModal.style.display = 'flex';
    cargarCarritoModal();
};

// Abrir modal carrito desde menú lateral
menuCarrito.addEventListener('click', (e) => {
    e.preventDefault();
    carritoModal.style.display = 'flex';
    cargarCarritoModal();
});

// Cerrar modal carrito
carritoModal.querySelector('.close').onclick = () => {
    carritoModal.style.display = 'none';
};



// Función para cargar carrito
function cargarCarritoModal(){
    carritoModalList.innerHTML = '<p>Cargando...</p>';
    fetch(`${baseURL}?action=listar`)
      .then(res => res.json())
      .then(data => {
          carritoModalList.innerHTML = '';
          if(!data || data.length === 0){
              carritoModalList.innerHTML = '<p>Carrito vacío</p>';
              return;
          }
          data.forEach(item => {
              const div = document.createElement('div');
              div.innerHTML = `${item.Nombre} x${item.cantidad} - $${item.precio} <button onclick="eliminarCarrito(${item.id_carrito})">Eliminar</button>`;
              carritoModalList.appendChild(div);
          });
      })
      .catch(err => console.error('Error al cargar carrito:', err));
}

// Función eliminar
function eliminarCarrito(id){
    fetch(`${baseURL}?action=eliminar&id_carrito=${id}`)
      .then(res => res.json())
      .then(data => {
          if(data.success) cargarCarritoModal();
          else alert('No se pudo eliminar');
      });
}

// --- Función abrir modal dispositivo ---
function abrirModal(d){
    dispositivoActual = d;
    indiceFoto = 0;
    fotosModal = [];

    if(d.imagen) fotosModal.push(d.imagen);
    if(d.fotos){
        try {
            const fotosArray = typeof d.fotos === 'string' ? JSON.parse(d.fotos) : d.fotos;
            fotosModal = fotosModal.concat(fotosArray.filter(Boolean));
        } catch(e){ console.error("Error parseando fotos:", e); }
    }

    actualizarFotoModal();

    prevFoto.style.display = fotosModal.length > 1 ? 'flex' : 'none';
    nextFoto.style.display = fotosModal.length > 1 ? 'flex' : 'none';

    modalTitulo.textContent = d.Nombre;
    modalMarca.textContent = 'Marca: ' + d.nombre_marca;
    modalTipo.textContent = 'Tipo: ' + d.tipo;
    modalPrecio.textContent = d.oferta==1 ? '$'+Number(d.precio_descuento).toLocaleString() : '$'+Number(d.precio).toLocaleString();
    modalLanzamiento.textContent = 'Lanzamiento: ' + d.fecha_lanzamiento;
    modalDescripcion.textContent = d.descripcion;

    modalDispositivo.style.display = 'flex';
}

// --- Actualizar foto modal ---
function actualizarFotoModal(){
    if(fotosModal.length === 0){
        fotoActual.src = 'imagenes/default.png';
        return;
    }
    if(indiceFoto < 0) indiceFoto = fotosModal.length - 1;
    if(indiceFoto >= fotosModal.length) indiceFoto = 0;
    fotoActual.src = fotosModal[indiceFoto];
}

// --- Eventos modal dispositivo ---
prevFoto.onclick = () => { indiceFoto--; actualizarFotoModal(); };
nextFoto.onclick = () => { indiceFoto++; actualizarFotoModal(); };
modalDispositivo.querySelector('.close').onclick = () => modalDispositivo.style.display = 'none';

// --- Agregar al carrito ---
btnAgregarCarrito.onclick = () => {
    if(!dispositivoActual) return;
    fetch(`${baseURL}?action=agregar`, {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({id_dispositivo: dispositivoActual.id_dispositivo, cantidad:1})
    })
    .then(res=>res.json())
    .then(data=>{
        if(data.success){
            alert('Dispositivo agregado al carrito');
            cargarCarritoModal();
        } else alert('Error: '+(data.error||'No se pudo agregar'));
    })
    .catch(err => console.error('Error fetch:', err));
};

// --- Abrir modal comentarios ---
btnComentar.onclick = () => modalComentario.style.display = 'flex';
modalComentario.querySelector('.close').onclick = () => modalComentario.style.display = 'none';

// --- Funciones estrellas ---
estrellas.forEach(span => {
    span.addEventListener('mouseover', () => actualizarEstrellas(Number(span.dataset.value)));
    span.addEventListener('mouseout', () => actualizarEstrellas(calificacionActual));
    span.addEventListener('click', () => { calificacionActual = Number(span.dataset.value); actualizarEstrellas(calificacionActual); });
});
function actualizarEstrellas(valor){
    estrellas.forEach(s => {
        s.textContent = Number(s.dataset.value) <= valor ? '★' : '☆';
        s.style.color = Number(s.dataset.value) <= valor ? '#ff69b4' : '#000';
    });
}

// --- Mostrar dispositivos ---
function mostrarDispositivos(lista){
    const container = document.getElementById('device-list');
    container.innerHTML = '';
    if(lista.length === 0){ container.innerHTML = '<p>No hay dispositivos</p>'; return; }

    lista.forEach(d=>{
        const card = document.createElement('div');
        card.classList.add('device-card');
        card.onclick = ()=>abrirModal(d);

        let imgSrc = d.imagen;
        if(!imgSrc && d.fotos){
            try { 
                const fotosArray = typeof d.fotos === 'string' ? JSON.parse(d.fotos) : d.fotos;
                imgSrc = fotosArray[0] || 'imagenes/default.png';
            } catch(e){ imgSrc = 'imagenes/default.png'; }
        }

        let html = '';
        if(d.oferta==1) html += '<div class="oferta-badge">OFERTA</div>';
        html += `<img src="${imgSrc}" alt="${d.Nombre}">
                 <h3>${d.Nombre}</h3>
                 <p>Marca: ${d.nombre_marca}</p>
                 <p>Tipo: ${d.tipo}</p>
                 <p>Lanzamiento: ${d.fecha_lanzamiento}</p>`;
        if(d.oferta==1){
            html += `<p class="precio-original">$${Number(d.precio_original).toLocaleString()}</p>
                     <p class="precio-descuento">$${Number(d.precio_descuento).toLocaleString()}</p>`;
        } else {
            html += `<p class="precio">$${Number(d.precio).toLocaleString()}</p>`;
        }
        card.innerHTML = html;
        container.appendChild(card);
    });
}

// --- Filtrar dispositivos ---
function aplicarFiltros(){
    const texto = document.getElementById('busqueda').value.toLowerCase();
    const marca = document.getElementById('filtroMarca').value;
    mostrarDispositivos(dispositivosData.filter(d=>{
        const cumpleNombre = d.Nombre.toLowerCase().includes(texto);
        const cumpleMarca = !marca || d.nombre_marca === marca;
        return cumpleNombre && cumpleMarca;
    }));
}
document.getElementById('busqueda').addEventListener('input', aplicarFiltros);
document.getElementById('filtroMarca').addEventListener('change', aplicarFiltros);

// --- Inicial ---
mostrarDispositivos(dispositivosData);

</script>








</body>
</html>
