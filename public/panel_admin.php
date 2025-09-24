<?php
session_start();
if(!isset($_SESSION['admin_id']) || $_SESSION['rol'] !== 'Administrador'){
    die("Acceso denegado. Solo Administrador.");
}
$usuario_nombre = $_SESSION['admin_nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel de Administración</title>
<style>
body { font-family: Arial, sans-serif; margin:0; display:flex; height:100vh; }
nav { background:#2c3e50; color:#fff; width:240px; padding:20px; display:flex; flex-direction:column; }
nav h2 { margin-bottom:20px; font-size:18px; text-align:center; }
nav a { color:#fff; text-decoration:none; margin:8px 0; padding:10px; border-radius:6px; display:block; transition:0.3s; }
nav a:hover { background:#27ae60; }
main { flex:1; padding:20px; background:#f4f6f8; overflow-y:auto; }
#contenido { background:#fff; padding:20px; border-radius:8px; min-height:300px; }
button { padding:5px 10px; cursor:pointer; }
textarea { width:100%; resize:none; }
table { width:100%; border-collapse:collapse; margin-top:20px; }
th, td { border:1px solid #ddd; padding:8px; text-align:center; }
th { background:#333; color:#fff; }
tr:nth-child(even) { background:#f2f2f2; }
</style>
</head>
<body>

<nav>
  <h2>👤 <?= htmlspecialchars($usuario_nombre) ?></h2>
  <a href="#" onclick="mostrar('dispositivos')">📱 Dispositivos</a>
  <a href="#" onclick="mostrar('marcas')">🏷️ Marcas</a>
  <a href="#" onclick="mostrar('categorias')">📂 Categorías</a>
  <a href="#" onclick="mostrar('ofertas')">💰 Ofertas</a>
  <a href="#" onclick="mostrar('pedidos')">📦 Pedidos</a>
  <a href="#" onclick="mostrar('comentarios')">💬 Comentarios</a>
  <a href="#" onclick="mostrar('contacto')">📧 Contacto</a>
  <a href="#" onclick="mostrar('usuarios')">👥 Usuarios</a>
  <hr>
  <a href="logout.php">🚪 Cerrar Sesión</a>
</nav>

<main>
  <div id="contenido">
    <p>Selecciona una opción del menú para administrar.</p>
  </div>
</main>

<script>
// ================= FUNCIONES AJAX =================

// Función principal para cargar módulos
function mostrar(mod) {
    const modulos = {
        'dispositivos': "../src/admin/dispositivos.php",
        'marcas': "../src/admin/marcas.php",
        'categorias': "../src/admin/categorias.php",
        'ofertas': "../src/admin/ofertas.php",
        'comentarios': "../src/admin/comentarios.php",
        'usuarios': "../src/admin/usuarios.php",
        'contacto': "../src/admin/contacto.php",
        'pedidos': "../src/admin/pedido.php"
    };

    const archivo = modulos[mod];
    if(!archivo) return;

    fetch(archivo)
    .then(res => {
        if(!res.ok) throw new Error("No se pudo cargar el módulo: " + res.status);
        return res.text();
    })
    .then(html => {
        document.getElementById('contenido').innerHTML = html;

        // Inicializar funciones JS específicas
        switch(mod){
            case 'dispositivos':
                if(typeof initDispositivos==='function') initDispositivos();
                break;
            case 'marcas': if(typeof initMarcas==='function') initMarcas(); break;
            case 'categorias': if(typeof initCategorias==='function') initCategorias(); break;
            case 'ofertas': if(typeof initOfertas==='function') initOfertas(); break;
            case 'comentarios': 
            // 🚨 ¡LA LLAMADA A LA FUNCIÓN DEBE ESTAR AQUÍ!
            if(typeof initComentarios === 'function') initComentarios(); 
            break;
            case 'usuarios': if(typeof initUsuarios==='function') initUsuarios(); break;
            case 'contacto': if(typeof initContacto==='function') initContacto(); break;
            case 'pedidos': if(typeof initPedidos==='function') initPedidos(); break;
        }
    })
    .catch(err => {
        document.getElementById('contenido').innerHTML = "<p style='color:red;'>Error al cargar el módulo: "+err.message+"</p>";
    });
}


// ================= MODULO CONTACTO =================

// ================= MODULO CONTACTO =================
function initContacto() {
    document.querySelectorAll('.btn-ver').forEach(btn => {
        console.log("Botón encontrado:", btn, "data-id=", btn.dataset.id);
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            console.log("Seleccionado ID:", id);
            if(!id){
                alert("No se puede seleccionar este mensaje: ID no disponible.");
                return;
            }

            fetch('../src/admin/contacto_detalle.php?id_contacto=' + encodeURIComponent(id))
                .then(res => res.text())
                .then(html => {
                    document.getElementById('contenido').innerHTML = html;
                    initResponder();
                })
                .catch(err => {
                    console.error(err);
                    alert("Error al cargar detalle: " + err.message);
                });
        });
    });
}

// ================= FORMULARIO RESPONDER =================
function initResponder(){
    const form = document.getElementById('form-responder');
    if(form){
        form.addEventListener('submit', function(e){
            e.preventDefault();
            const id = this.dataset.id;
            if(!id){
                alert("No se puede enviar la respuesta: ID no disponible.");
                return;
            }

            const body = 'id_contacto=' + encodeURIComponent(id) +
                         '&respuesta=' + encodeURIComponent(this.respuesta.value) +
                         '&estado=' + encodeURIComponent(this.estado.value);

            fetch('../src/admin/contacto_detalle.php', {
                method:'POST',
                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body: body
            })
            .then(res => res.text())
            .then(msg => {
                alert(msg);
                cargarLista(); // volver a la lista de contactos
            })
            .catch(err => {
                console.error(err);
                alert("Error al enviar la respuesta: " + err.message);
            });
        });
    }

    const btnVolver = document.getElementById('volverLista');
    if(btnVolver){
        btnVolver.addEventListener('click', function(){
            cargarLista();
        });
    }
}

// ================= CARGAR LISTA =================
function cargarLista(){
    fetch('../src/admin/contacto.php')
        .then(res=>res.text())
        .then(html=>{
            document.getElementById('contenido').innerHTML = html;
            initContacto();
        })
        .catch(err=>{
            console.error(err);
            alert("Error al cargar la lista de contactos: " + err.message);
        });
}


// ================= MODULO PEDIDOS =================
// ================= MODULO PEDIDOS =================
function initPedidos() {
    // Función para cargar la lista de pedidos, recibe los filtros
    function cargarPedidos(idUsuario = '', estado = '') {
        const url = `../src/admin/pedido.php?id_usuario=${encodeURIComponent(idUsuario)}&estado=${encodeURIComponent(estado)}`;
        
        fetch(url)
            .then(res => res.text())
            .then(html => {
                document.getElementById('contenido').innerHTML = html;
                
                // Re-inicializamos todos los botones después de cargar el HTML
                initBotonesPedidos(); 
            })
            .catch(err => {
                console.error("Error al cargar la lista de pedidos:", err);
                alert("Error al cargar la lista de pedidos: " + err.message);
            });
    }

    // Función que inicializa los eventos del listado de pedidos
    function initBotonesPedidos() {
        // Inicializa el formulario de filtros
        const formFiltros = document.getElementById('form-filtros');
        if (formFiltros) {
            formFiltros.addEventListener('submit', function(e) {
                e.preventDefault();
                const id_usuario = this.elements.namedItem('id_usuario').value;
                const estado_filtro = this.elements.namedItem('estado').value;
                cargarPedidos(id_usuario, estado_filtro);
            });
        }
        
        // Inicializa los botones "Ver Detalle"
        document.querySelectorAll('.btn-ver-pedido').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                if (!id) return;
                fetch('../src/admin/pedido_detalle.php?id_pedido=' + encodeURIComponent(id))
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('contenido').innerHTML = html;
                        initPedidoDetalle(); // Inicializa los botones del detalle
                    })
                    .catch(err => {
                        console.error("Error al cargar detalle del pedido:", err);
                        alert("Error al cargar detalle del pedido");
                    });
            });
        });
    }

    // Llama a la función para inicializar el módulo completo
    cargarPedidos(); 
}

// Inicializa botones dentro de pedido_detalle.php
// Esta función se llama solo cuando se carga la página de detalle
function initPedidoDetalle() {
    const form = document.getElementById('form-estado');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            const estado = this.estado.value;

            fetch('../src/admin/pedido_detalle.php?id_pedido=' + encodeURIComponent(id), {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'estado=' + encodeURIComponent(estado)
            })
            .then(res => res.text())
            .then(msg => {
                alert(msg);
                mostrar('pedidos'); // Volver a la lista usando la función global
            })
            .catch(err => {
                console.error(err);
                alert("Error al actualizar el estado: " + err.message);
            });
        });
    }

    const btnVolver = document.getElementById('volverListaPedidos');
    if (btnVolver) {
        btnVolver.addEventListener('click', function() {
            mostrar('pedidos'); // Volver a la lista usando la función global
        });
    }
}

// ================= MODULO DISPOSITIVOS =================
// ================= MODULO DISPOSITIVOS =================
function initDispositivos() {
    const API_URL = "/AA2_Martinez_Moreno_Karol_Daniela/src/admin/dispositivos_accion.php";

    // Función para cargar la tabla
    function cargarDispositivos(filtro = '') {
        const url = `${API_URL}?accion=listar&filtro=${encodeURIComponent(filtro)}`;
        
        fetch(url)
            .then(res => res.json())
            .then(data => {
                let html = '';
                if (data.length === 0) {
                    html = '<tr><td colspan="8">No hay dispositivos</td></tr>';
                } else {
                    data.forEach(d => {
                        html += `
                            <tr>
                                <td>${d.id_dispositivo ?? ''}</td>
                                <td>${d.Nombre ?? ''}</td>
                                <td>${d.Marca ?? ''}</td>
                                <td>${d.tipo ?? ''}</td>
                                <td>${d.Categoria ?? ''}</td>
                                <td>${d.precio ?? ''}</td>
                                <td>${d.stock ?? ''}</td>
                                <td>
                                    <button onclick="abrirFormulario(${d.id_dispositivo})">Editar</button>
                                    <button onclick="eliminarDispositivo(${d.id_dispositivo})">Eliminar</button>
                                </td>
                            </tr>
                        `;
                    });
                }
                document.querySelector('#tablaDispositivos tbody').innerHTML = html;
            })
            .catch(err => {
                console.error("Error al cargar dispositivos: ", err);
                document.querySelector('#tablaDispositivos tbody').innerHTML = `
                    <tr>
                        <td colspan="8">Error al cargar los datos. Verifique la consola para más detalles.</td>
                    </tr>
                `;
            });
    }

    // Abrir formulario de agregar o editar
    window.abrirFormulario = function(id = null) {
        let url = "/AA2_Martinez_Moreno_Karol_Daniela/src/admin/dispositivo_form.php";
        if(id) url += "?id_dispositivo=" + encodeURIComponent(id);

        fetch(url)
            .then(res => res.text())
            .then(html => {
                document.getElementById('contenido').innerHTML = html;
                initFormularioDispositivos(); // <-- ¡AQUÍ ESTÁ LA CLAVE!
            })
            .catch(err => console.error(err));
    };

    // Eliminar dispositivo
    window.eliminarDispositivo = function(id) {
        if(!confirm("¿Seguro que quieres eliminar este dispositivo?")) return;

        const formData = new FormData();
        formData.append("accion","eliminar");
        formData.append("id_dispositivo", id);

        fetch(API_URL, { method:'POST', body: formData })
            .then(res => res.json())
            .then(resp => {
                if(resp.status==='ok'){
                    alert("Eliminado correctamente");
                    cargarDispositivos();
                } else {
                    alert("Error: " + resp.msg);
                }
            });
    };

    // Filtrado por nombre/marca
    const filtroInput = document.getElementById('filtroDispositivo');
    if(filtroInput){
        filtroInput.addEventListener('input', function() {
            cargarDispositivos(this.value);
        });
    }

    // Cargar la tabla inicialmente
    cargarDispositivos();
}

// ================= LÓGICA DEL FORMULARIO DE DISPOSITIVOS =================
function initFormularioDispositivos() {
    const API_URL = "/AA2_Martinez_Moreno_Karol_Daniela/src/admin/dispositivos_accion.php";
    
    // Función para cargar los menús desplegables
    function cargarOpciones() {
        // Cargar Marcas
        fetch(`${API_URL}?accion=listar_marcas`)
            .then(res => res.json())
            .then(data => {
                const selectMarca = document.getElementById('marca');
                // Limpiar el select antes de llenarlo
                selectMarca.innerHTML = '';
                data.forEach(m => {
                    const option = document.createElement('option');
                    option.value = m.id_marca;
                    option.textContent = m.nombre_marca;
                    selectMarca.appendChild(option);
                });
            })
            .catch(err => console.error("Error al cargar marcas:", err));

        // Cargar Categorías
        fetch(`${API_URL}?accion=listar_categorias`)
            .then(res => res.json())
            .then(data => {
                const selectCategoria = document.getElementById('categoria');
                // Limpiar el select antes de llenarlo
                selectCategoria.innerHTML = '';
                data.forEach(c => {
                    const option = document.createElement('option');
                    option.value = c.id_categoria;
                    option.textContent = c.nombre_categoria;
                    selectCategoria.appendChild(option);
                });
            })
            .catch(err => console.error("Error al cargar categorías:", err));
    }

    // ⭐ ¡AQUÍ ESTÁ LA LÍNEA QUE FALTABA!
    // Llama a la función para que se ejecute y cargue las opciones
    cargarOpciones();

    // Maneja el envío del formulario
    document.getElementById('formDispositivo').addEventListener('submit', function(e){
        e.preventDefault();

        const precioInput = document.getElementById('precio');
        const precio = parseFloat(precioInput.value);

        if (precio < 0) {
            alert('❌ El precio no puede ser un valor negativo. Por favor, introduce un valor válido.');
            precioInput.focus();
            return;
        }

        const formData = new FormData(this);
        const accion = formData.get('id_dispositivo') ? 'editar' : 'agregar';
        formData.append('accion', accion);

        fetch(API_URL, { method:'POST', body: formData })
        .then(res => res.json())
        .then(resp => {
            if(resp.status === 'ok'){
                alert('Dispositivo guardado correctamente');
                mostrar('dispositivos'); 
            } else {
                alert('Error: ' + resp.msg);
            }
        })
        .catch(err => console.error(err));
    });

    // Botón regresar
    document.getElementById('btnRegresar').addEventListener('click', function() {
        mostrar('dispositivos');
    });

    // Cargar datos para edición si existe el ID en la URL
    const params = new URLSearchParams(window.location.search);
    const idEdit = params.get('id_dispositivo');

    if(idEdit){
        // Cargar datos para edición desde la API
        fetch(API_URL + "?accion=listar")
            .then(res => res.json())
            .then(data => {
                const d = data.find(x => x.id_dispositivo == idEdit);
                if(d){
                    document.getElementById('id_dispositivo').value = d.id_dispositivo;
                    document.getElementById('nombre').value = d.nombre;
                    document.getElementById('marca').value = d.marca;
                    document.getElementById('tipo').value = d.tipo;
                    document.getElementById('categoria').value = d.categoria;
                    document.getElementById('precio').value = d.precio;
                    document.getElementById('stock').value = d.stock ?? '';
                    document.getElementById('tituloFormulario').innerText = "Editar Dispositivo";
                }
            });
    }
    }
// ================= MODULO OFERTAS =================
// ================= MODULO OFERTAS =================
function initOfertas() {
    // 1. Declaración de variables
    const API_URL = "http://localhost/AA2_Martinez_Moreno_Karol_Daniela/src/admin/ofertas_accion.php";
    const FORM_URL = "http://localhost/AA2_Martinez_Moreno_Karol_Daniela/src/admin/oferta_form.php";
    const formFiltros = document.getElementById('form-filtros-ofertas');
    const filtroDispositivoInput = document.getElementById('filtroDispositivo');
    const filtroDescuentoInput = document.getElementById('filtroDescuento');
    const btnLimpiarFiltros = document.getElementById('btnLimpiarFiltros');
    

    // Cargar tabla de ofertas
    function cargarOfertas(filtroDispositivo = '', filtroDescuento = '') {
        const url = `${API_URL}?accion=listar&filtroDispositivo=${encodeURIComponent(filtroDispositivo)}&filtroDescuento=${encodeURIComponent(filtroDescuento)}`;
        
        fetch(url)
            .then(res => res.json())
            .then(data => {
                let html = '';
                if(data.length === 0){
                    html = '<tr><td colspan="9">No hay ofertas</td></tr>';
                } else {
                    data.forEach(o => {
                        html += `<tr>
                            <td>${o.id_oferta}</td>
                            <td>${o.nombre}</td>
                            <td>$${parseFloat(o.precio_original).toFixed(2)}</td>
                            <td>${o.descuento_porcentaje}%</td>
                            <td>$${parseFloat(o.precio_final).toFixed(2)}</td>
                            <td>${o.estado}</td>
                            <td>${o.fecha_inicio ?? ''}</td>
                            <td>${o.fecha_fin ?? ''}</td>
                            <td>
                                <button onclick="abrirFormularioOferta(${o.id_oferta})">Editar</button>
                                <button onclick="eliminarOferta(${o.id_oferta})">Eliminar</button>
                            </td>
                        </tr>`;
                    });
                }
                document.querySelector('#tablaOfertas tbody').innerHTML = html;
            })
            .catch(err => console.error("Error al cargar ofertas: ", err));
    }
    // Función para abrir el formulario y enlazar los botones
    function abrirFormularioOferta(id = null) {
        let url = "../src/admin/oferta_form.php";
        if(id) url += "?id_oferta=" + encodeURIComponent(id);

        fetch(url)
            .then(res => res.text())
            .then(html => {
                // Inserta el HTML del formulario
                document.getElementById('contenido').innerHTML = html;

                // Ahora, una vez que el HTML está en el DOM, inicializa los eventos
                const form = document.getElementById('formOferta');
                const btnRegresar = document.getElementById('btnRegresar');

                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();

                        const formData = new FormData(this);
                        const accion = id ? "editar" : "agregar";
                        formData.append('accion', accion);

                        fetch(API_URL, {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(resp => {
                            if (resp.status === 'ok') {
                                alert('Operación realizada correctamente');
                                mostrar('ofertas'); // vuelve al listado
                            } else {
                                alert('Error: ' + resp.msg);
                            }
                        })
                        .catch(err => console.error(err));
                    });
                }

                if (btnRegresar) {
                    btnRegresar.addEventListener('click', function() {
                        mostrar('ofertas');
                    });
                }
            })
            .catch(err => console.error("Error al cargar el formulario: ", err));
    }

    // Función para eliminar la oferta
    function eliminarOferta(id) {
        if(!confirm("¿Seguro que quieres eliminar esta oferta?")) return;
        const formData = new FormData();
        formData.append('accion', 'eliminar');
        formData.append('id_oferta', id);

        fetch(API_URL, {method:'POST', body: formData})
            .then(res => res.json())
            .then(resp => {
                if(resp.status === 'ok'){
                    alert("Eliminado correctamente");
                    cargarOfertas();
                } else {
                    alert("Error: "+resp.msg);
                }
            })
            .catch(err => console.error("Error al eliminar: ", err));
    }

    // 3. Exponer las funciones al ámbito global para los 'onclick'
    window.abrirFormularioOferta = abrirFormularioOferta;
    window.eliminarOferta = eliminarOferta;

    // 4. Oyentes de eventos de filtrado y carga inicial
    if (filtroDispositivoInput) {
        filtroDispositivoInput.addEventListener('input', function() {
            const dispositivo = this.value;
            const descuento = filtroDescuentoInput.value;
            cargarOfertas(dispositivo, descuento);
        });
    }

    if (formFiltros) {
        formFiltros.addEventListener('submit', function(e) {
            e.preventDefault();
            const dispositivo = filtroDispositivoInput.value;
            const descuento = filtroDescuentoInput.value;
            cargarOfertas(dispositivo, descuento);
        });
    }

    if (btnLimpiarFiltros) {
        btnLimpiarFiltros.addEventListener('click', function() {
            filtroDispositivoInput.value = '';
            filtroDescuentoInput.value = '';
            cargarOfertas();
        });
    }

    // Carga inicial de la tabla
    cargarOfertas();
}

   
// ================= MODULO MARCAS =================
// ================= MODULO MARCAS =================
function initMarcas() {
    const API_URL = "/AA2_Martinez_Moreno_Karol_Daniela/src/admin/marcas_accion.php";

    // Función para cargar la tabla de marcas
    function cargarMarcas(filtro = '') {
        const url = `${API_URL}?accion=listar&filtro=${encodeURIComponent(filtro)}`;

        fetch(url)
            .then(res => res.json())
            .then(resp => {
                let html = '';
                if (resp.status === 'error') {
                    html = `<tr><td colspan="5">Error: ${resp.msg}</td></tr>`;
                } else if (resp.data.length === 0) {
                    html = '<tr><td colspan="5">No hay marcas</td></tr>';
                } else {
                    resp.data.forEach(m => {
                        html += `
                            <tr>
                                <td>${m.id_marca ?? ''}</td>
                                <td>${m.nombre_marca ?? ''}</td>
                                <td>${m.pais_origen ?? ''}</td>
                                <td>${m.descripcion ?? ''}</td>
                                <td>
                                    <button class="editar" onclick="abrirFormularioMarca(${m.id_marca})">Editar</button>
                                    <button class="eliminar" onclick="eliminarMarca(${m.id_marca})">Eliminar</button>
                                </td>
                            </tr>
                        `;
                    });
                }
                document.querySelector('#tablaMarcas tbody').innerHTML = html;
            })
            .catch(err => {
                console.error("Error al cargar marcas: ", err);
                document.querySelector('#tablaMarcas tbody').innerHTML = `
                    <tr><td colspan="5">Error al cargar los datos. Verifique la consola para más detalles.</td></tr>
                `;
            });
    }

    // Lógica para el botón de eliminar
    window.eliminarMarca = function(id) {
        if (!confirm("¿Seguro que quieres eliminar esta marca?")) return;
        const formData = new FormData();
        formData.append("accion", "eliminar");
        formData.append("id_marca", id);

        fetch(API_URL, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(resp => {
                if (resp.status === 'ok') {
                    alert("Marca eliminada correctamente.");
                    cargarMarcas();
                } else {
                    alert("Error: " + resp.msg);
                }
            })
            .catch(err => console.error("Error al eliminar marca: ", err));
    };

    // Abre el formulario de agregar/editar
    window.abrirFormularioMarca = function(id = null) {
        let url = "/AA2_Martinez_Moreno_Karol_Daniela/src/admin/marcas_from.php";
        if (id) {
            url += "?id=" + encodeURIComponent(id);
        }

        fetch(url)
            .then(res => {
                if (!res.ok) {
                    throw new Error("No se pudo cargar el formulario de marca: " + res.statusText);
                }
                return res.text();
            })
            .then(html => {
                document.getElementById('contenido').innerHTML = html;
                initFormularioMarcas(); // Llama a la función de inicialización del formulario
            })
            .catch(err => {
                console.error("Error al cargar el formulario de marca:", err);
                document.getElementById('contenido').innerHTML = `<p style="color: red;">Error: ${err.message}</p>`;
            });
    };

    // Asocia el evento de filtrado
    const filtroInput = document.getElementById('filtroMarca');
    if (filtroInput) {
        filtroInput.addEventListener('input', function() {
            cargarMarcas(this.value);
        });
    }

    // Carga inicial de la tabla
    cargarMarcas();
}

// ================= LÓGICA DEL FORMULARIO DE MARCAS (AGREGAR/EDITAR) =================
// Esta función se llama desde abrirFormularioMarca() después de que el HTML se carga.
function initFormularioMarcas() {
    const API_URL = "/AA2_Martinez_Moreno_Karol_Daniela/src/admin/marcas_accion.php";

    // Maneja el envío del formulario
    document.getElementById('formMarca').addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(this);

        fetch(API_URL, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(resp => {
                if (resp.status === 'ok') {
                    alert('Operación realizada correctamente');
                    mostrar('marcas'); // Vuelve a la tabla de marcas
                } else {
                    alert('Error: ' + resp.msg);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error al procesar la solicitud.');
            });
    });

    // Maneja el botón "Regresar"
    document.getElementById('btnRegresar').addEventListener('click', function() {
        mostrar('marcas'); // Vuelve a la tabla de marcas
    });
}

// ================= MODULO CATEGORIAS =================
function initCategorias() {
    const API_URL = "/AA2_Martinez_Moreno_Karol_Daniela/src/admin/categorias_accion.php";

    // Función para cargar la tabla de categorías
    function cargarCategorias(filtro = '') {
        const url = `${API_URL}?accion=listar&filtro=${encodeURIComponent(filtro)}`;

        fetch(url)
            .then(res => res.json())
            .then(resp => {
                let html = '';
                if (resp.status === 'error') {
                    html = `<tr><td colspan="4">Error: ${resp.msg}</td></tr>`;
                } else if (resp.data.length === 0) {
                    html = '<tr><td colspan="4">No hay categorías</td></tr>';
                } else {
                    resp.data.forEach(c => {
                        html += `
                            <tr>
                                <td>${c.id_categoria ?? ''}</td>
                                <td>${c.nombre_categoria ?? ''}</td>
                                <td>${c.descripcion ?? ''}</td>
                                <td>
                                    <button class="editar" onclick="abrirFormularioCategoria(${c.id_categoria})">Editar</button>
                                    <button class="eliminar" onclick="eliminarCategoria(${c.id_categoria})">Eliminar</button>
                                </td>
                            </tr>
                        `;
                    });
                }
                document.querySelector('#tablaCategorias tbody').innerHTML = html;
            })
            .catch(err => {
                console.error("Error al cargar categorías: ", err);
                document.querySelector('#tablaCategorias tbody').innerHTML = `
                    <tr><td colspan="4">Error al cargar los datos. Verifique la consola para más detalles.</td></tr>
                `;
            });
    }

    // Lógica para el botón de eliminar
    window.eliminarCategoria = function(id) {
        if (!confirm("¿Seguro que quieres eliminar esta categoría?")) return;
        const formData = new FormData();
        formData.append("accion", "eliminar");
        formData.append("id_categoria", id);

        fetch(API_URL, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(resp => {
                if (resp.status === 'ok') {
                    alert("Categoría eliminada correctamente.");
                    cargarCategorias();
                } else {
                    alert("Error: " + resp.msg);
                }
            })
            .catch(err => console.error("Error al eliminar categoría: ", err));
    };

    // Abre el formulario de agregar/editar
    window.abrirFormularioCategoria = function(id = null) {
        let url = "/AA2_Martinez_Moreno_Karol_Daniela/src/admin/categorias_from.php";
        if (id) {
            url += "?id=" + encodeURIComponent(id);
        }

        fetch(url)
            .then(res => {
                if (!res.ok) {
                    throw new Error("No se pudo cargar el formulario de categoría: " + res.statusText);
                }
                return res.text();
            })
            .then(html => {
                document.getElementById('contenido').innerHTML = html;
                initFormularioCategorias(); // Llama a la función de inicialización del formulario
            })
            .catch(err => {
                console.error("Error al cargar el formulario de categoría:", err);
                document.getElementById('contenido').innerHTML = `<p style="color: red;">Error: ${err.message}</p>`;
            });
    };

    // Asocia el evento de filtrado
    const filtroInput = document.getElementById('filtroCategoria');
    if (filtroInput) {
        filtroInput.addEventListener('input', function() {
            cargarCategorias(this.value);
        });
    }

    // Carga inicial de la tabla
    cargarCategorias();
}

// ================= LÓGICA DEL FORMULARIO DE CATEGORÍAS (AGREGAR/EDITAR) =================
// Esta función se llama desde abrirFormularioCategoria() después de que el HTML se carga.
function initFormularioCategorias() {
    const API_URL = "/AA2_Martinez_Moreno_Karol_Daniela/src/admin/categorias_accion.php";

    // Maneja el envío del formulario
    document.getElementById('formCategoria').addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(this);

        fetch(API_URL, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(resp => {
                if (resp.status === 'ok') {
                    alert('Operación realizada correctamente');
                    mostrar('categorias'); // Vuelve a la tabla de categorías
                } else {
                    alert('Error: ' + resp.msg);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error al procesar la solicitud.');
            });
    });

    // Maneja el botón "Regresar"
    document.getElementById('btnRegresar').addEventListener('click', function() {
        mostrar('categorias'); // Vuelve a la tabla de categorías
    });
}


 //Mofulo comentarios //
 function initComentarios(){
    const formFiltro = document.getElementById('form-filtro');
    if(formFiltro){
        formFiltro.addEventListener('submit', function(e){
            e.preventDefault();
            const nombre = this.nombre.value;
            fetch('/AA2_Martinez_Moreno_Karol_Daniela/src/admin/comentarios.php?nombre=' + encodeURIComponent(nombre))
                .then(res => res.text())
                .then(html => {
                    document.getElementById('contenido').innerHTML = html;
                    initComentarios();
                });
        });
    }

    const tablaComentarios = document.getElementById('comentarios-table');
    if (tablaComentarios) {
        tablaComentarios.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-eliminar')) {
                e.preventDefault();
                const id = e.target.dataset.id;
                if (confirm('¿Eliminar este comentario?')) {
                    fetch(`/AA2_Martinez_Moreno_Karol_Daniela/src/admin/comentarios.php?delete=${id}&nombre=${encodeURIComponent(document.querySelector('select[name="nombre"]').value)}`)
                        .then(res => res.text())
                        .then(html => {
                            document.getElementById('contenido').innerHTML = html;
                            initComentarios();
                        });
                }
            }
        });
    }

    const limpiar = document.getElementById('limpiarFiltro');
    if(limpiar){
        limpiar.addEventListener('click', function(){
            fetch('/AA2_Martinez_Moreno_Karol_Daniela/src/admin/comentarios.php')
                .then(res => res.text())
                .then(html => {
                    document.getElementById('contenido').innerHTML = html;
                    initComentarios();
                });
        });
    }
}

</script>
</body>
</html>