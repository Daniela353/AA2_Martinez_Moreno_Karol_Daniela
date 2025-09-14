<?php
session_start();
$rol = $_SESSION['rol'] ?? 'Invitado';
$usuario_id = $_SESSION['id_usuario'] ?? null;
$nombre_usuario = $_SESSION['nombre'] ?? '';
$esAdmin = ($rol === 'Administrador');
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contacto / Comentarios</title>
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
form, .mensaje-invitado { background: #fff; padding: 1.5rem; border-radius: 15px; max-width: 500px; margin: 2rem auto; box-shadow: 0 4px 8px rgba(0,0,0,0.1); text-align:center; }
form label { display: block; margin: 0.5rem 0; }
form input, form select, form textarea { width: 100%; padding: 0.5rem; border-radius: 8px; border: 1px solid #ff69b4; margin-top: 0.3rem; }
form button, .mensaje-invitado a { padding: 0.5rem 1rem; border:none; border-radius:8px; background:#ff69b4; color:white; cursor:pointer; margin-top:1rem; text-decoration:none; display:inline-block; }
.mensaje-invitado a:hover { background:#d88ab1; }
#msg { text-align:center; margin-top:1rem; font-weight:bold; color:#d88ab1; }
</style>
</head>
<body>

<aside class="sidebar">
  <img src="imagenes/logo.png" alt="Logo">
  <nav>
    <a href="index.php">Inicio</a>
    <a href="index.php">Dispositivos</a>
    <a href="index.php?oferta=1">Ofertas</a>

    <?php if(!$usuario_id): ?>
      <a href="login.php">Iniciar Sesión</a>

    <?php else: ?>
      <button onclick="if(confirm('¿Deseas cerrar sesión?')) window.location.href='logout.php';">Cerrar sesión</button>
    <?php endif; ?>
  </nav>
</aside>

<div class="main-content">
  <header>
    <h1>Contacto / Comentarios</h1>
    <p>Usuario: <?= htmlspecialchars($nombre_usuario) ?> | Rol: <?= $rol ?></p>
  </header>

  <?php if(!$usuario_id): ?>
      <!-- Mensaje para invitados -->
      <div class="mensaje-invitado">
        <h2>⚠️ Solo usuarios registrados pueden enviar un contacto</h2>
        <p>Por favor, inicia sesión o regístrate para poder enviarnos tus comentarios.</p>
        <a href="login.php">Iniciar sesión</a>
        <a href="registro_clientes.php">Registrarse</a>
      </div>
  <?php else: ?>
      <!-- Formulario visible solo para usuarios logueados -->
      <form id="formComentario">

        <label>Nombre completo:
            <input type="text" name="nombre" value="<?= htmlspecialchars($nombre_usuario) ?>" required>
        </label>

        <label>Correo electrónico:
            <input type="email" name="email" required>
        </label>

        <label>Motivo de contacto:
            <select name="motivo" required>
                <option value="">Seleccione una opción</option>
                <option value="producto">Información sobre un producto</option>
                <option value="soporte">Soporte técnico</option>
                <option value="cotizacion">Cotización</option>
                <option value="garantia">Garantía / Devolución</option>
                <option value="otro">Otro</option>
            </select>
        </label>

        <label>Mensaje / Comentario:<br>
            <textarea name="comentario" rows="4" required></textarea>
        </label>

        <button type="submit">Enviar</button>
      </form>

      <div id="msg"></div>
  <?php endif; ?>
</div>

<?php if($usuario_id): ?>
<script>
const form = document.getElementById('formComentario');
form.addEventListener('submit', async e => {
    e.preventDefault();
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    data.id_usuario = <?= $usuario_id ?? 'null' ?>;

    try {
        const res = await fetch('/AA2_Martinez_Moreno_Karol_Daniela/src/comentarios_crud.php?action=agregar', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify(data)
        });

        if(!res.ok) throw new Error(`HTTP error! status: ${res.status}`);

        const result = await res.json();
        if(result.success){
            document.getElementById('msg').textContent = 'Comentario enviado ✅';
            form.reset();
        } else {
            document.getElementById('msg').textContent = 'Error: ' + (result.error || '');
        }
    } catch(err){
        document.getElementById('msg').textContent = 'Error de conexión: ' + err.message;
    }
});
</script>
<?php endif; ?>

</body>
</html>

