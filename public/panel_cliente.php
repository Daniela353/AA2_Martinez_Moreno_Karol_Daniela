<<?php
// Mover todo el código PHP al principio del archivo.
session_start();
ini_set('display_errors', 1); // Activar la visualización de errores para depurar
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/../src/conexion.php'; 

$rol = $_SESSION['rol'] ?? 'Invitado';
$usuario_id = $_SESSION['id_usuario'] ?? null;
$usuario_nombre = $_SESSION['nombre_usuario'] ?? 'Cliente';

if(!$usuario_id){
    die("Debes iniciar sesión para acceder al panel de cliente.");
}

$filtroOferta = isset($_GET['oferta']) ? true : false;

// --- CONSULTA PARA OBTENER TODAS LAS CATEGORÍAS (ID y NOMBRE) ---
// Asegúrate de usar un método compatible con tu controlador.
$stmtCategorias = $conn->prepare("SELECT id_categoria, nombre_categoria FROM categoria ORDER BY nombre_categoria");
$stmtCategorias->execute();
$resultCategorias = $stmtCategorias->get_result(); // O $stmtCategorias->fetchAll() si es PDO
$categorias = [];
if ($resultCategorias) {
    while($row = $resultCategorias->fetch_assoc()){
        $categorias[] = $row;
    }
}

// --- CONSULTA PRINCIPAL PARA DISPOSITIVOS ---
$stmt = $conn->prepare("
    SELECT
        d.*,
        m.nombre_marca,
        c.nombre_categoria,
        o.id_oferta,
        o.precio_original,
        o.precio_final,
        GROUP_CONCAT(i.imagen_secundaria) AS fotos_secundarias
    FROM dispositivo d
    LEFT JOIN imagen i ON d.id_dispositivo = i.id_dispositivo
    LEFT JOIN marca m ON d.Marca = m.id_marca
    LEFT JOIN categoria c ON d.Categoria = c.id_categoria
    LEFT JOIN ofertas o ON d.id_dispositivo = o.id_dispositivo AND o.estado='Activa'
    GROUP BY d.id_dispositivo
    ORDER BY d.id_dispositivo ASC
");
$stmt->execute();
$result = $stmt->get_result(); // O $stmt->fetchAll() si es PDO

$dispositivos = [];
if ($result) {
    while($row = $result->fetch_assoc()){
        if ($filtroOferta && !isset($row['id_oferta'])) {
            continue;
        }

        $row['precio'] = (float)$row['precio'];
        if (isset($row['id_oferta']) && $row['id_oferta'] != null) {
            $row['precio_original'] = (float)$row['precio_original'];
            $row['precio_descuento'] = (float)$row['precio_final'];
            $row['oferta'] = 1;
        } else {
            $row['oferta'] = 0;
        }

        $row['id_categoria'] = $row['Categoria'];

        // Manejo de las fotos secundarias, si existen.
        if (isset($row['fotos_secundarias']) && $row['fotos_secundarias'] !== null) {
            $row['fotos'] = explode(',', $row['fotos_secundarias']);
        } else {
            $row['fotos'] = [];
        }
        unset($row['fotos_secundarias']);

        $dispositivos[] = $row;
    }
}
// Ahora $dispositivos es un array que siempre existe y es válido.
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
.filters {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin: 1rem 0;
    flex-wrap: wrap;
}
.filters input, .filters select {
    padding: 0.5rem;
    border-radius: 8px;
    border: 1px solid #ff69b4;
}
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

.filters-and-search {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

/* Estilos para el bloque de filtros de rangos de precio */
.price-ranges {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    border: 1px solid #ff69b4;
    border-radius: 10px;
    background-color: #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* Estilos para cada checkbox y su etiqueta */
.price-ranges label {
    width: 100%;
    text-align: left;
}

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

.tabla-pedidos {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.tabla-pedidos th, .tabla-pedidos td {
    border: 1px solid #ddd;
    padding: 12px;
    text-align: left;
}

.tabla-pedidos th {
    background-color: #f2f2f2;
    font-weight: bold;
}

/* --- Estilos para los comentarios en el modal --- */
#comentarios-container {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #e0e0e0;
}

#lista-comentarios {
    display: flex;
    flex-direction: column;
    gap: 15px; /* Espacio entre comentarios */
}

.comentario-item {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    background-color: #f9f9f9;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.comentario-item .calificacion {
    font-size: 1.2rem;
    color: #FFC107; /* Color de las estrellas */
    margin: 0 0 5px;
}

.comentario-item .autor {
    font-weight: bold;
    color: #555;
    margin: 0 0 5px;
    font-size: 0.9rem;
}

.comentario-item .texto {
    color: #333;
    margin: 0;
    line-height: 1.4;
}

#lista-comentarios p {
    text-align: center;
    color: #888;
    font-style: italic;
}

/* Estilos para el texto del modal de comentarios */
#comentarioModal h3 {
    text-align: center;
    margin-bottom: 15px;
}

#comentarioText {
    resize: vertical;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

#calificacionStars {
    text-align: center;
}

#calificacionStars span {
    font-size: 2rem;
    transition: color 0.2s ease;
}

#btnComentar {
    margin-top: 10px;
    background-color: #4CAF50;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    width: 100%;
}


/* Estilos para el formulario de contacto */
.contact-form-container {
    background-color: #fff;
    padding: 2.5rem;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    max-width: 500px;
    width: 100%;
    margin: 2rem auto;
    text-align: left;
    transition: transform 0.3s ease;
}

.contact-form-container:hover {
    transform: translateY(-5px);
}

.contact-form-container h2 {
    text-align: center;
    color: #333;
    font-size: 1.8rem;
    margin-bottom: 0.5rem;
}

.contact-form-container p {
    text-align: center;
    color: #666;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.2rem;
}

.form-group label {
    display: block;
    font-weight: bold;
    color: #555;
    margin-bottom: 0.5rem;
}

.contact-form-container input,
.contact-form-container select,
.contact-form-container textarea {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
    color: #333;
    transition: border-color 0.3s;
    box-sizing: border-box; /* Asegura que el padding no cambie el ancho */
}

.contact-form-container input:focus,
.contact-form-container select:focus,
.contact-form-container textarea:focus {
    border-color: #ff69b4;
    outline: none;
    box-shadow: 0 0 5px rgba(255,105,180,0.5);
}

.contact-form-container button {
    width: 100%;
    padding: 1rem;
    background-color: #ff69b4;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.2s;
}

.contact-form-container button:hover {
    background-color: #ff85c1;
    transform: translateY(-2px);
}

.form-status-message {
    text-align: center;
    margin-top: 1rem;
    font-size: 1rem;
    font-weight: bold;
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
    <a href="#" onclick="mostrarSeccion('pedidos')">Mis Pedidos</a>
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
        <select id="filtroCategoria">
            <option value="">Todas las categorías</option>
            <?php
            foreach($categorias as $c) {
                echo "<option value=\"{$c['id_categoria']}\">{$c['nombre_categoria']}</option>";
            }
            ?>
        </select>
        <input type="number" id="precioMin" placeholder="Precio mínimo" min="0">
        <input type="number" id="precioMax" placeholder="Precio máximo" min="0">
        <button type="button" id="btnLimpiarFiltros">Limpiar filtros</button>
    </div>

    <div id="device-list"></div>

    <div id="contacto-section">
    <form id="contactoForm" class="contact-form-container">
        <h2>📧 Contacto</h2>
        <p>¿Tienes alguna pregunta o problema? ¡Contáctanos!</p>
        <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" placeholder="Tu nombre" required>
        </div>
        <div class="form-group">
            <label for="email">Correo</label>
            <input type="email" id="email" name="email" placeholder="Tu correo electrónico" required>
        </div>
        <div class="form-group">
            <label for="motivo">Motivo</label>
            <select id="motivo" name="motivo" required>
                <option value="">Selecciona un motivo</option>
                <option value="soporte">Soporte técnico</option>
                <option value="informacion">Solicitud de información</option>
                <option value="reclamo">Reclamo / Queja</option>
                <option value="otro">Otro</option>
            </select>
        </div>
        <div class="form-group">
            <label for="mensaje">Mensaje</label>
            <textarea id="mensaje" name="mensaje" rows="4" placeholder="Escribe tu mensaje aquí..." required></textarea>
        </div>
        <button type="submit">Enviar Mensaje</button>
        <div id="contactoResult" class="form-status-message"></div>
    </form>
</div>

    <div id="pedidos-section" class="main-content" style="display: none;">
        <h2>Mis Pedidos</h2>
        <table id="pedidos-table" class="tabla-pedidos">
            <thead>
                <tr>
                    <th>ID de Pedido</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

    <div id="detallePedidoModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Detalles del Pedido</h2>
            <div id="detallePedidoList"></div>
            <div class="pedido-total">Total: <span id="detallePedidoTotal"></span></div>
            <button id="btnRegresarPedidos">Regresar a Pedidos</button>
        </div>
    </div>

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

            <div style="margin: 10px 0;">
                <label for="cantidad">Cantidad:</label>
                <input type="number" id="cantidad" value="1" min="1" style="width: 60px;">
            </div>

            <button id="btnAgregarCarrito">Añadir al Carrito</button>
            <button id="btnComentar">Comentar</button>

            <div class="modal-body-content">
                <div id="comentarios-container">
                    <h4>Comentarios y Calificaciones</h4>
                    <div id="lista-comentarios">
                        <p>Cargando comentarios...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
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

    <div id="carritoModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
        <div class="modal-content" style="background:white; padding:20px; max-width:400px; width:90%; position:relative;">
            <span class="close" style="position:absolute; top:5px; right:10px; cursor:pointer;">&times;</span>
            <h2>🛒 Carrito de Compras</h2>
            <div id="carritoModalList">
                <p>Cargando...</p>
            </div>
            
            <div class="carrito-resumen" style="margin-top: 20px; padding-top: 10px; border-top: 1px solid #ddd;">
                <p>Subtotal: <span id="carrito-subtotal">$0.00</span></p>
                <p>Descuento (20%): <span id="carrito-descuento">$0.00</span></p>
                <h3>Total: <span id="carrito-total">$0.00</span></h3>
            </div>
            
            <button id="btnSimularPedido" style="width: 100%;">Confirmar Pedido</button>
            
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', (event) => {
        // --- URLs base para las peticiones fetch ---
        const carritoURL = 'http://localhost/AA2_Martinez_Moreno_Karol_Daniela/src/cliente/carrito.php';
        const pedidosURL = 'http://localhost/AA2_Martinez_Moreno_Karol_Daniela/src/cliente/pedido.php';
        const comentariosURL = 'http://localhost/AA2_Martinez_Moreno_Karol_Daniela/src/cliente/comentarios.php';
        const contactoURL = 'http://localhost/AA2_Martinez_Moreno_Karol_Daniela/src/cliente/contacto.php';  
        
        // Esta línea inyecta los datos desde PHP.
        const dispositivosData = <?= json_encode($dispositivos); ?>;
        
        // --- Variables globales ---
        let dispositivoActual = null;
        let indiceFoto = 0;
        let fotosModal = [];
        let carritoData = [];
        let calificacionActual = 0;
        let idComentarioAEditar = null;

        // --- Referencias a elementos del DOM ---
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
        const cantidadInput = document.getElementById('cantidad');
        const closeDeviceModal = modalDispositivo.querySelector('.close');

        const carritoModal = document.getElementById('carritoModal');
        const carritoModalList = document.getElementById('carritoModalList');
        const btnSimularPedido = document.getElementById('btnSimularPedido');
        const closeCarritoModal = carritoModal.querySelector('.close');
        const carritoSubtotalSpan = document.getElementById('carrito-subtotal');
        const carritoDescuentoSpan = document.getElementById('carrito-descuento');
        const carritoTotalSpan = document.getElementById('carrito-total');

        const modalComentario = document.getElementById('comentarioModal');
        const enviarComentario = modalComentario.querySelector('#enviarComentario');
        const comentarioText = modalComentario.querySelector('#comentarioText');
        const estrellas = modalComentario.querySelectorAll('#calificacionStars span');
        const closeComentarioModal = modalComentario.querySelector('.close');

        const detallePedidoModal = document.getElementById('detallePedidoModal');
        const btnRegresarPedidos = document.getElementById('btnRegresarPedidos');
        const detallePedidoList = document.getElementById('detallePedidoList');
        const detallePedidoTotal = document.getElementById('detallePedidoTotal');
        const closeDetallePedidoModal = detallePedidoModal.querySelector('.close');

        const menuCarritoLink = document.getElementById('menuCarrito');
        const abrirCarritoBtn = document.getElementById('abrirCarrito');
        
        // -------------------------------------------------------------
        // LÓGICA DE APERTURA Y CIERRE DE MODALES
        // -------------------------------------------------------------
        function abrirCarritoModal() {
            if (carritoModal) {
                carritoModal.style.display = 'flex';
                cargarCarritoModal();
            }
        }

        if (menuCarritoLink) {
            menuCarritoLink.addEventListener('click', (e) => {
                e.preventDefault();
                abrirCarritoModal();
            });
        }

        if (abrirCarritoBtn) {
            abrirCarritoBtn.addEventListener('click', abrirCarritoModal);
        }
        
        if (closeCarritoModal) {
            closeCarritoModal.onclick = () => {
                carritoModal.style.display = 'none';
            };
        }

        if (closeDeviceModal) {
            closeDeviceModal.onclick = () => {
                modalDispositivo.style.display = 'none';
            };
        }

        if (closeComentarioModal) {
            closeComentarioModal.onclick = () => {
                modalComentario.style.display = 'none';
            };
        }

        window.mostrarSeccion = (seccion) => {
            document.getElementById('device-list').style.display = 'none';
            document.getElementById('contacto-section').style.display = 'none';
            document.getElementById('pedidos-section').style.display = 'none';
            document.getElementById('detallePedidoModal').style.display = 'none';

            switch (seccion) {
                case 'dispositivos':
                    document.getElementById('device-list').style.display = 'flex';
                    aplicarFiltros();
                    break;
                case 'ofertas':
                    document.getElementById('device-list').style.display = 'flex';
                    const ofertasData = dispositivosData.filter(d => d.oferta == 1);
                    if (ofertasData.length > 0) {
                        mostrarDispositivos(ofertasData);
                    } else {
                        const deviceListContainer = document.getElementById('device-list');
                        deviceListContainer.innerHTML = '<p style="text-align: center;">No hay ofertas disponibles en este momento.</p>';
                        deviceListContainer.style.display = 'flex';
                    }
                    break;
                case 'pedidos':
                    document.getElementById('pedidos-section').style.display = 'block';
                    cargarPedidos();
                    break;
                case 'contacto':
                    document.getElementById('contacto-section').style.display = 'block';
                    break;
                default:
                    break;
            }
        };

        // -------------------------------------------------------------
        // LÓGICA DEL CARRITO
        // -------------------------------------------------------------
        function cargarCarritoModal() {
            fetch(`${carritoURL}?action=listar`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        carritoData = data.items;
                        carritoModalList.innerHTML = '';
                        let subtotal = 0;
                        
                        if (carritoData.length === 0) {
                            carritoModalList.innerHTML = '<p>El carrito está vacío.</p>';
                            subtotal = 0;
                        } else {
                            carritoData.forEach(item => {
                                const li = document.createElement('li');
                                li.classList.add('carrito-item');
                                
                                const precioItem = parseFloat(item.precio);
                                subtotal += item.cantidad * precioItem;
                                
                                li.innerHTML = `
                                    <div class="carrito-item-info">
                                        <span>${item.Nombre}</span>
                                        <div class="carrito-item-controls">
                                            <label for="cantidad-${item.id_dispositivo}">Cantidad:</label>
                                            <input type="number" id="cantidad-${item.id_dispositivo}" value="${item.cantidad}" min="1" 
                                                onchange="actualizarCantidad(${item.id_carrito}, this.value)">
                                        </div>
                                    </div>
                                    <span>$${(item.cantidad * precioItem).toLocaleString()}</span>
                                    <button onclick="eliminarDelCarrito(${item.id_carrito})">Eliminar</button>
                                `;
                                carritoModalList.appendChild(li);
                            });
                        }
                        
                        const descuento = subtotal * 0.20;
                        const totalFinal = subtotal - descuento;
                        
                        if (carritoSubtotalSpan) carritoSubtotalSpan.textContent = `$${subtotal.toLocaleString()}`;
                        if (carritoDescuentoSpan) carritoDescuentoSpan.textContent = `$${descuento.toLocaleString()}`;
                        if (carritoTotalSpan) carritoTotalSpan.textContent = `$${totalFinal.toLocaleString()}`;
                        
                    } else {
                        console.error('Error del servidor:', data.error);
                        alert('Error al cargar el carrito: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error en la solicitud fetch:', error);
                    alert('Ocurrió un error inesperado al cargar el carrito.');
                });
        }

        if (btnAgregarCarrito) {
            btnAgregarCarrito.onclick = () => {
                if (!dispositivoActual) return;

                const cantidad = parseInt(cantidadInput.value);
                if (isNaN(cantidad) || cantidad < 1) {
                    alert("La cantidad mínima es 1.");
                    return;
                }

                fetch(`${carritoURL}?action=agregar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id_dispositivo: dispositivoActual.id_dispositivo,
                        cantidad: cantidad
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('Dispositivo agregado al carrito');
                        modalDispositivo.style.display = 'none';
                        cargarCarritoModal();
                    } else {
                        alert('Error: ' + (data.error || 'No se pudo agregar'));
                    }
                })
                .catch(error => {
                    console.error('Error en la solicitud fetch:', error);
                    alert('Ocurrió un error inesperado. Por favor, revisa la consola.');
                });
            };
        }

        window.eliminarDelCarrito = (id_carrito) => {
            fetch(`${carritoURL}?action=eliminar&id_carrito=${id_carrito}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Producto eliminado del carrito.');
                        cargarCarritoModal();
                    } else {
                        alert('No se pudo eliminar: ' + (data.error || 'Error desconocido'));
                    }
                })
                .catch(err => console.error('Error eliminando del carrito:', err));
        };

        window.actualizarCantidad = (id_carrito, nuevaCantidad) => {
            const cantidad = parseInt(nuevaCantidad);
            if (isNaN(cantidad) || cantidad < 1) {
                alert("La cantidad debe ser un número mayor a 0.");
                cargarCarritoModal();
                return;
            }

            fetch(`${carritoURL}?action=actualizar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_carrito: id_carrito,
                    cantidad: cantidad
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    console.log("Cantidad actualizada con éxito.");
                    cargarCarritoModal();
                } else {
                    alert('Error: ' + (data.error || 'No se pudo actualizar la cantidad.'));
                    cargarCarritoModal();
                }
            })
            .catch(error => {
                console.error('Error en la solicitud fetch:', error);
                alert('Ocurrió un error inesperado al actualizar la cantidad.');
                cargarCarritoModal();
            });
        };

        if (btnSimularPedido) {
            btnSimularPedido.onclick = () => {
                if (carritoData.length === 0) {
                    alert('El carrito está vacío. Agrega productos para simular un pedido.');
                    return;
                }

                try {
                    fetch(`${pedidosURL}?action=crear`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            productos: carritoData
                        })
                    })
                    .then(res => {
                        if (!res.ok) {
                            return res.text().then(text => {
                                throw new Error(`Error ${res.status}: ${res.statusText}. Respuesta del servidor: ${text}`);
                            });
                        }
                        return res.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert(`¡Pedido simulado con éxito! ID del pedido: ${data.id_pedido}. Revisa la tabla de pedidos.`);
                            carritoModal.style.display = 'none';
                            cargarCarritoModal();
                        } else {
                            alert('Error al simular el pedido: ' + data.error);
                        }
                    })
                    .catch(err => {
                        console.error('Error en la solicitud:', err);
                        alert('Hubo un problema con la solicitud. Revisa la consola para más detalles.');
                    });
                } catch (err) {
                    console.error('Error inesperado:', err);
                    alert('Ocurrió un error inesperado. Revisa la consola para más detalles.');
                }
            };
        }

        // -------------------------------------------------------------
        // LÓGICA DE MODAL DE DETALLES DE DISPOSITIVO Y COMENTARIOS
        // -------------------------------------------------------------
        function abrirModal(d) {
            dispositivoActual = d;
            indiceFoto = 0;
            fotosModal = [];

            let mainImgUrl = d.imagen;
            const extensionRegex = /\.(jpg|jpeg|png|webp|gif)$/i;
            if (mainImgUrl) {
                if (mainImgUrl.indexOf('imagenes/') === -1) mainImgUrl = 'imagenes/' + mainImgUrl;
                if (!extensionRegex.test(mainImgUrl)) mainImgUrl += '.jpg';
                fotosModal.push(mainImgUrl);
            }

            if (d.fotos) {
                try {
                    const fotosArray = typeof d.fotos === 'string' ? JSON.parse(d.fotos) : d.fotos;
                    if (Array.isArray(fotosArray)) {
                        fotosArray.forEach(fotoUrl => {
                            if (fotoUrl) {
                                let finalUrl = fotoUrl;
                                if (finalUrl.indexOf('imagenes/') === -1) finalUrl = 'imagenes/' + finalUrl;
                                if (!extensionRegex.test(finalUrl)) finalUrl += '.jpg';
                                fotosModal.push(finalUrl);
                            }
                        });
                    }
                } catch (e) {
                    console.error("Error parseando fotos:", e);
                }
            }

            actualizarFotoModal();

            prevFoto.style.display = fotosModal.length > 1 ? 'flex' : 'none';
            nextFoto.style.display = fotosModal.length > 1 ? 'flex' : 'none';

            prevFoto.onclick = () => {
                indiceFoto = (indiceFoto - 1 + fotosModal.length) % fotosModal.length;
                actualizarFotoModal();
            };

            nextFoto.onclick = () => {
                indiceFoto = (indiceFoto + 1) % fotosModal.length;
                actualizarFotoModal();
            };

            modalTitulo.textContent = d.Nombre;
            modalMarca.textContent = 'Marca: ' + d.nombre_marca;
            modalTipo.textContent = 'Tipo: ' + d.tipo;
            modalPrecio.textContent = '$' + Number(d.precio).toLocaleString();
            modalLanzamiento.textContent = 'Lanzamiento: ' + d.fecha_lanzamiento;
            modalDescripcion.textContent = d.descripcion;

            cargarComentarios(d.id_dispositivo);

            modalDispositivo.style.display = 'flex';
        }

        function cargarComentarios(id_dispositivo) {
            const listaComentarios = document.getElementById('lista-comentarios');
            listaComentarios.innerHTML = '<p>Cargando comentarios...</p>';
            
            const usuarioActualId = <?= json_encode($usuario_id); ?>;

            fetch(`${comentariosURL}?action=listar&id_dispositivo=${id_dispositivo}`)
                .then(response => response.json())
                .then(data => {
                    listaComentarios.innerHTML = '';
                    if (data.success && data.comentarios.length > 0) {
                        data.comentarios.forEach(comentario => {
                            const divComentario = document.createElement('div');
                            divComentario.classList.add('comentario-item');
                            
                            const estrellasHTML = '★'.repeat(comentario.calificacion) + '☆'.repeat(5 - comentario.calificacion);
                            
                            let botonesHTML = '';
                            if (comentario.id_usuario == usuarioActualId) {
                                botonesHTML = `<button class="btn-editar-comentario" data-id="${comentario.id_comentario}" 
                                                data-comentario="${comentario.comentario}" data-calificacion="${comentario.calificacion}">
                                                Editar
                                                </button>`;
                            }

                            divComentario.innerHTML = `
                                <p class="calificacion">${estrellasHTML}</p>
                                <p class="autor">Por: ${comentario.nombre_usuario}</p>
                                <p class="texto">${comentario.comentario}</p>
                                ${botonesHTML}
                            `;
                            listaComentarios.appendChild(divComentario);
                        });

                        document.querySelectorAll('.btn-editar-comentario').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const idComentario = this.dataset.id;
                                const textoComentario = this.dataset.comentario;
                                const calificacion = this.dataset.calificacion;
                                abrirModalEdicion(idComentario, textoComentario, calificacion);
                            });
                        });
                    } else {
                        listaComentarios.innerHTML = '<p>Aún no hay comentarios para este producto.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error al cargar comentarios:', error);
                    listaComentarios.innerHTML = '<p>Error al cargar los comentarios.</p>';
                });
        }

        function abrirModalEdicion(id, texto, calificacion) {
            idComentarioAEditar = id;
            comentarioText.value = texto;
            calificacionActual = Number(calificacion);
            actualizarEstrellas(calificacionActual);
            enviarComentario.textContent = 'Guardar Cambios';
            modalComentario.style.display = 'flex';
        }

        if (enviarComentario) {
            enviarComentario.onclick = () => {
                const comentario = comentarioText.value;
                if (!comentario || calificacionActual === 0) {
                    alert("Por favor, escribe un comentario y selecciona una calificación.");
                    return;
                }

                let action = idComentarioAEditar ? 'editar' : 'agregar';
                let bodyData = {};

                if (action === 'editar') {
                    bodyData = {
                        id_comentario: idComentarioAEditar,
                        comentario: comentario,
                        calificacion: calificacionActual
                    };
                } else {
                    if (!dispositivoActual) {
                        alert("No se ha seleccionado un dispositivo.");
                        return;
                    }
                    bodyData = {
                        id_dispositivo: dispositivoActual.id_dispositivo,
                        comentario: comentario,
                        calificacion: calificacionActual
                    };
                }

                fetch(`${comentariosURL}?action=${action}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(bodyData)
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message || data.error);
                    if (data.success) {
                        cargarComentarios(dispositivoActual.id_dispositivo);
                        modalComentario.style.display = 'none';
                        comentarioText.value = '';
                        calificacionActual = 0;
                        actualizarEstrellas(0);
                        idComentarioAEditar = null;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("Ocurrió un error de conexión.");
                });
            };
        }

        if (btnComentar) {
            btnComentar.onclick = () => {
                idComentarioAEditar = null;
                comentarioText.value = '';
                calificacionActual = 0;
                actualizarEstrellas(0);
                enviarComentario.textContent = 'Enviar';
                modalComentario.style.display = 'flex';
            };
        }
        
        estrellas.forEach(span => {
            span.addEventListener('mouseover', () => actualizarEstrellas(Number(span.dataset.value)));
            span.addEventListener('mouseout', () => actualizarEstrellas(calificacionActual));
            span.addEventListener('click', () => {
                calificacionActual = Number(span.dataset.value);
                actualizarEstrellas(calificacionActual);
            });
        });

        function actualizarEstrellas(valor) {
            estrellas.forEach(s => {
                s.textContent = Number(s.dataset.value) <= valor ? '★' : '☆';
                s.style.color = Number(s.dataset.value) <= valor ? '#ff69b4' : '#000';
            });
        }

        function actualizarFotoModal() {
            if (fotosModal.length === 0) {
                fotoActual.src = 'imagenes/default.png';
                return;
            }
            fotoActual.src = fotosModal[indiceFoto];
        }

        // -------------------------------------------------------------
        // LÓGICA DE FILTROS Y BÚSQUEDA
        // -------------------------------------------------------------
        function mostrarDispositivos(lista) {
            const container = document.getElementById('device-list');
            container.innerHTML = '';
            if (lista.length === 0) {
                container.innerHTML = '<p>No hay dispositivos</p>';
                return;
            }

            lista.forEach(d => {
                const card = document.createElement('div');
                card.classList.add('device-card');
                card.onclick = () => abrirModal(d);

                let imgSrc = d.imagen;
                const extensionRegex = /\.(jpg|jpeg|png|webp|gif)$/i;

                if (imgSrc && imgSrc.indexOf('imagenes/') === -1) {
                    imgSrc = 'imagenes/' + imgSrc;
                }
                if (imgSrc && !extensionRegex.test(imgSrc)) {
                    imgSrc += '.jpg';
                }

                if (!imgSrc && d.fotos && d.fotos.length > 0) {
                    imgSrc = d.fotos[0];
                    if (imgSrc.indexOf('imagenes/') === -1) {
                        imgSrc = 'imagenes/' + imgSrc;
                    }
                    if (!extensionRegex.test(imgSrc)) {
                        imgSrc += '.jpg';
                    }
                }
                if (!imgSrc) {
                    imgSrc = 'imagenes/default.png';
                }

                let html = '';
                if (d.oferta == 1) html += '<div class="oferta-badge">OFERTA</div>';
                html += `<img src="${imgSrc}" alt="${d.Nombre}">
                        <h3>${d.Nombre}</h3>
                        <p>Marca: ${d.nombre_marca}</p>
                        <p>Tipo: ${d.tipo}</p>
                        <p>Lanzamiento: ${d.fecha_lanzamiento}</p>`;
                
                html += `<p class="precio">$${Number(d.precio).toLocaleString()}</p>`;
                
                card.innerHTML = html;
                container.appendChild(card);
            });
        }

        function aplicarFiltros() {
            const texto = document.getElementById('busqueda').value.toLowerCase();
            const marca = document.getElementById('filtroMarca').value;
            const categoria = document.getElementById('filtroCategoria').value;

            const precioMinRaw = document.getElementById('precioMin').value.trim();
            const precioMaxRaw = document.getElementById('precioMax').value.trim();

            const precioMin = precioMinRaw ? parseFloat(precioMinRaw) : 0;
            const precioMax = precioMaxRaw ? parseFloat(precioMaxRaw) : Infinity;

            const precioMinFinal = isNaN(precioMin) ? 0 : precioMin;
            const precioMaxFinal = isNaN(precioMax) ? Infinity : precioMax;

            const dispositivosFiltrados = dispositivosData.filter(d => {
                const precioActual = parseFloat(d.precio);

                const cumpleNombre = d.Nombre.toLowerCase().includes(texto);
                const cumpleMarca = marca === "" || d.nombre_marca === marca;
                const cumpleCategoria = categoria === "" || d.id_categoria == categoria;
                const cumplePrecio = precioActual >= precioMinFinal && precioActual <= precioMaxFinal;

                return cumpleNombre && cumpleMarca && cumpleCategoria && cumplePrecio;
            });

            mostrarDispositivos(dispositivosFiltrados);
        }

        function limpiarFiltros() {
            document.getElementById('busqueda').value = '';
            document.getElementById('filtroMarca').value = '';
            document.getElementById('filtroCategoria').value = '';
            document.getElementById('precioMin').value = '';
            document.getElementById('precioMax').value = '';

            aplicarFiltros();
        }

        document.getElementById('busqueda').addEventListener('input', aplicarFiltros);
        document.getElementById('filtroMarca').addEventListener('change', aplicarFiltros);
        document.getElementById('filtroCategoria').addEventListener('change', aplicarFiltros);
        document.getElementById('precioMin').addEventListener('input', aplicarFiltros);
        document.getElementById('precioMax').addEventListener('input', aplicarFiltros);
        document.getElementById('btnLimpiarFiltros').addEventListener('click', limpiarFiltros);

        aplicarFiltros();

        // -------------------------------------------------------------
        // LÓGICA DE PEDIDOS
        // -------------------------------------------------------------
        if (closeDetallePedidoModal) {
            closeDetallePedidoModal.onclick = () => {
                detallePedidoModal.style.display = 'none';
                document.getElementById('pedidos-section').style.display = 'block';
            };
        }

        if (btnRegresarPedidos) {
            btnRegresarPedidos.onclick = () => {
                detallePedidoModal.style.display = 'none';
                document.getElementById('pedidos-section').style.display = 'block';
            };
        }

        function cargarPedidos() {
            const pedidosTableBody = document.querySelector('#pedidos-table tbody');
            pedidosTableBody.innerHTML = '';

            fetch(`${pedidosURL}?action=listar`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.pedidos.length > 0) {
                        data.pedidos.forEach(pedido => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${pedido.id_pedido}</td>
                                <td>${pedido.fecha_orden}</td>
                                <td>${pedido.estado}</td>
                                <td>$${Number(pedido.total).toLocaleString('es-CO')}</td>
                                <td>
                                    <button onclick="verDetallePedido(${pedido.id_pedido})">Ver Detalles</button>
                                </td>
                            `;
                            pedidosTableBody.appendChild(row);
                        });
                    } else {
                        pedidosTableBody.innerHTML = '<tr><td colspan="5">No tienes pedidos realizados aún.</td></tr>';
                    }
                })
                .catch(err => {
                    console.error('Error al cargar pedidos:', err);
                    pedidosTableBody.innerHTML = '<tr><td colspan="5">Error al cargar el historial de pedidos.</td></tr>';
                });
        }

        window.verDetallePedido = (id_pedido) => {
            document.getElementById('pedidos-section').style.display = 'none';
            detallePedidoModal.style.display = 'flex';
            detallePedidoList.innerHTML = '<p>Cargando detalles...</p>';
            detallePedidoTotal.textContent = '';

            fetch(`${pedidosURL}?action=detalle&id_pedido=${id_pedido}`)
                .then(res => res.json())
                .then(data => {
                    detallePedidoList.innerHTML = '';
                    if (data.success && data.detalles.length > 0) {
                        let totalPedido = 0;
                        data.detalles.forEach(detalle => {
                            const div = document.createElement('div');
                            div.classList.add('detalle-item');
                            const nombreProducto = detalle.Nombre || 'Producto no disponible'; 
                            const cantidad = detalle.cantidad;
                            const precioUnitario = Number(detalle.precio_unitario);
                            const subtotal = cantidad * precioUnitario;
                            div.innerHTML = `
                                <h4>${nombreProducto}</h4>
                                <p>Cantidad: <span>${cantidad}</span></p>
                                <p>Precio Unitario: $${precioUnitario.toLocaleString('es-CO')}</p>
                                <p>Subtotal: $${subtotal.toLocaleString('es-CO')}</p>
                            `;
                            detallePedidoList.appendChild(div);
                            totalPedido += subtotal;
                        });
                        detallePedidoTotal.textContent = `$${Number(totalPedido).toLocaleString('es-CO')}`;
                    } else {
                        detallePedidoList.innerHTML = '<p>No se encontraron detalles para este pedido.</p>';
                    }
                })
                .catch(err => {
                    console.error('Error al cargar los detalles del pedido:', err);
                    detallePedidoList.innerHTML = '<p>Error al cargar los detalles del pedido.</p>';
                });
        };
//Logica de contacto// 
const contactoForm = document.getElementById('contactoForm');
        const contactoResult = document.getElementById('contactoResult');

        if (contactoForm) {
            contactoForm.addEventListener('submit', function(event) {
                event.preventDefault(); // Evita que la página se recargue
                const formData = new FormData(this);

                // Validar los campos en JavaScript antes de enviar
                if (!formData.get('nombre') || !formData.get('email') || !formData.get('motivo') || !formData.get('mensaje')) {
                    contactoResult.textContent = 'Todos los campos son obligatorios.';
                    contactoResult.style.color = 'red';
                    return;
                }

                // Ahora fetch puede acceder a contactoURL sin problemas
                fetch(contactoURL, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        contactoResult.textContent = data.message;
                        contactoResult.style.color = 'green';
                        contactoForm.reset();
                    } else {
                        contactoResult.textContent = 'Error: ' + (data.error || 'Mensaje no enviado.');
                        contactoResult.style.color = 'red';
                    }
                })
                .catch(error => {
                    console.error('Error en la solicitud fetch:', error);
                    contactoResult.textContent = 'Ocurrió un error inesperado. Por favor, revisa tu conexión.';
                    contactoResult.style.color = 'red';
                });
            });
        }
    });
</script>
</script>
</body>
</html>
