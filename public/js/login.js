document.getElementById("loginForm").addEventListener("submit", function(e) {
  e.preventDefault();

  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value.trim();

  // Leer usuarios.json
  fetch("data/usuarios.json")
    .then(response => response.json())
    .then(usuarios => {
      const usuario = usuarios.find(
        user => user.email === email && user.password === password
      );

      if (usuario) {
        alert(`Bienvenido ${usuario.nombre} 👋`);

        // Guardar sesión en localStorage
        localStorage.setItem("usuarioActivo", JSON.stringify(usuario));

        // Redirigir según el rol
        if (usuario.rol === "admin") {
          window.location.href = "admin.html";
        } else {
          window.location.href = "index.html";
        }
      } else {
        alert("⚠️ Correo o contraseña incorrectos.");
      }
    })
    .catch(error => {
      console.error("Error cargando usuarios:", error);
    });
})