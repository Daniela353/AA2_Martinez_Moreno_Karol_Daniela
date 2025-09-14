<?php
session_start();
$usuario_id = $_SESSION['id_usuario'] ?? null;
$usuario_nombre = $_SESSION['nombre'] ?? "Administrador";
$rol = $_SESSION['rol'] ?? '';

if(!$usuario_id || $rol !== 'Administrador'){  
    die("Acceso denegado. Debes iniciar sesión como administrador.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel de Administración</title>
<style>
  body { font-family: Arial, sans-serif; margin:0; display:flex; height:100vh;}
  nav { background:#2c3e50; color:#fff; width:240px; padding:20px; display:flex; flex-direction:column;}
  nav h2 { margin-bottom:20px; font-size:18px; text-align:center; }
  nav a { color:#fff; text-decoration:none; margin:8px 0; padding:10px; border-radius:6px; display:block; transition:0.3s; }
  nav a:hover { background:#27ae60; }
  main { flex:1; padding:20px; background:#f4f6f8; overflow-y:auto; }
  h1 { margin-top:0; color:#2c3e50; }
  #contenido { background:#fff; padding:20px; border-radius:8px; min-height:300px; }
</style>
</head>
<body>

<nav>
  <h2>👤 <?= htmlspecialchars($usuario_nombre) ?></h2>
  <a href="#" onclick="mostrar('dispositivos')">📱 Dispositivos</a>
  <a href="#" onclick="mostrar('marcas')">🏷️ Marcas</a>
  <a href="#" onclick="mostrar('categorias')">📂 Categorías</a>
  <a href="#" onclick="mostrar('ofertas')">💰 Ofertas</a>
  <a href="#" onclick="mostrar('comentarios')">💬 Comentarios</a>
  <a href="#" onclick="mostrar('usuarios')">👥 Usuarios</a>
  <hr>
  <a href="logout.php">🚪 Cerrar Sesión</a>
</nav>

<main>
  <h1>Panel de Administración</h1>
  <div id="contenido">
    <p>Selecciona una opción del menú para administrar.</p>
  </div>
</main>

<script>
// Función principal para cargar módulos
function mostrar(mod) {
    let archivo = "";
    switch(mod){
        case 'dispositivos': archivo = "../src/admin/dispositivos.php"; break;
        case 'marcas': archivo = "../src/admin/marcas.php"; break;
        case 'categorias': archivo = "../src/admin/categorias.php"; break;
        case 'ofertas': archivo = "../src/admin/ofertas.php"; break;
        case 'comentarios': archivo = "../src/admin/comentarios.php"; break;
        case 'usuarios': archivo = "../src/admin/usuarios.php"; break;
        default: archivo = ""; break;
    }

    if(archivo){
        fetch(archivo)
        .then(res => {
            if(!res.ok) throw new Error("No se pudo cargar el módulo: "+res.status);
            return res.text();
        })
        .then(html => {
            document.getElementById('contenido').innerHTML = html;

            // Inicializar funciones JS específicas si existen
            switch(mod){
                case 'dispositivos': if(typeof initDispositivos === 'function') initDispositivos(); break;
                case 'marcas': if(typeof initMarcas === 'function') initMarcas(); break;
                case 'categorias': if(typeof initCategorias === 'function') initCategorias(); break;
                case 'ofertas': if(typeof initOfertas === 'function') initOfertas(); break;
                case 'comentarios': if(typeof initComentarios === 'function') initComentarios(); break;
                case 'usuarios': if(typeof initUsuarios === 'function') initUsuarios(); break;
            }
        })
        .catch(err => {
            document.getElementById('contenido').innerHTML = "<p style='color:red;'>Error al cargar el módulo: "+err.message+"</p>";
        });
    }
}

// Funciones de inicialización para cada módulo
function initDispositivos(){ console.log("Init Dispositivos"); /* Aquí tu JS de agregar/editar/eliminar */ }
function initMarcas(){ console.log("Init Marcas"); }
function initCategorias(){ console.log("Init Categorías"); }
function initOfertas(){ console.log("Init Ofertas"); }
function initComentarios(){ console.log("Init Comentarios"); }
function initUsuarios(){ console.log("Init Usuarios"); }

</script>

</body>
</html>
