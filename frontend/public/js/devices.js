const deviceList = document.getElementById('device-list');
const busquedaInput = document.getElementById('busqueda');
const filtroMarca = document.getElementById('filtroMarca');

const modal = document.getElementById('modal');
const modalImg = document.getElementById('modal-img');
const modalNombre = document.getElementById('modal-nombre');
const modalMarca = document.getElementById('modal-marca');
const modalTipo = document.getElementById('modal-tipo');
const modalPrecio = document.getElementById('modal-precio');
const modalFecha = document.getElementById('modal-fecha');
const modalDescripcion = document.getElementById('modal-descripcion');
const modalResena = document.getElementById('modal-resena');
const modalComponentes = document.getElementById('modal-componentes');
const modalFotos = document.getElementById('modal-fotos');
const cerrar = document.getElementById('cerrar');

const listaComentarios = document.getElementById('lista-comentarios');
const nuevoComentario = document.getElementById('nuevo-comentario');
const agregarComentarioBtn = document.getElementById('agregar-comentario');

// =============================
// 📌 Cargar datos desde JSON
// =============================
fetch('data/dispositivos.json')
  .then(response => response.json())
  .then(dispositivosData => {

    // Renderizar dispositivos en tarjetas
    function mostrarDispositivos(dispositivos) {
      deviceList.innerHTML = '';

      dispositivos.forEach(d => {
        const card = document.createElement('div');
        card.classList.add('device-card');

        // Imagen
        const img = document.createElement('img');
        img.src = d.imagen;
        img.alt = d.nombre;
        img.addEventListener('click', () => abrirModal(d));
        card.appendChild(img);

        // Info básica
        const info = document.createElement('div');
        info.innerHTML = `
          <h3>${d.nombre}</h3>
          <p><strong>Marca:</strong> ${d.marca}</p>
          <p><strong>Tipo:</strong> ${d.tipo}</p>
          <p><strong>Precio:</strong> $${d.precio}</p>
          <p><strong>Lanzamiento:</strong> ${d.fecha_lanzamiento}</p>
        `;
        card.appendChild(info);

        deviceList.appendChild(card);
      });
    }

    // Abrir modal con detalles del dispositivo
    function abrirModal(d) {
      modal.style.display = 'flex';
      modalImg.src = d.imagen;
      modalNombre.textContent = d.nombre;
      modalMarca.textContent = `Marca: ${d.marca}`;
      modalTipo.textContent = `Tipo: ${d.tipo}`;
      modalPrecio.textContent = `Precio: $${d.precio}`;
      modalFecha.textContent = `Lanzamiento: ${d.fecha_lanzamiento}`;
      modalDescripcion.textContent = d.descripcion;
      modalResena.textContent = d.resena || 'Sin reseñas disponibles.';
      modalComponentes.textContent = d.componentes || 'No especificados.';

      // Fotos adicionales
      modalFotos.innerHTML = '';
      if (d.fotos?.length) {
        d.fotos.forEach(foto => {
          const imgExtra = document.createElement('img');
          imgExtra.src = foto;
          imgExtra.addEventListener('click', () => modalImg.src = foto);
          modalFotos.appendChild(imgExtra);
        });
      }

      // Comentarios
      actualizarComentarios(d);

      agregarComentarioBtn.onclick = () => {
        const texto = nuevoComentario.value.trim();
        if (texto) {
          d.comentarios.push(texto);
          nuevoComentario.value = '';
          actualizarComentarios(d);
        }
      };
    }

    // Listar comentarios
    function actualizarComentarios(d) {
      listaComentarios.innerHTML = '';
      d.comentarios.forEach(c => {
        const li = document.createElement('li');
        li.textContent = c;
        listaComentarios.appendChild(li);
      });
    }

    // Filtrar por nombre y marca
    function filtrarDispositivos() {
      const busqueda = busquedaInput.value.toLowerCase();
      const marca = filtroMarca.value;

      const filtrados = dispositivosData.filter(d => {
        const coincideNombre = d.nombre.toLowerCase().includes(busqueda);
        const coincideMarca = !marca || d.marca === marca;
        return coincideNombre && coincideMarca;
      });

      mostrarDispositivos(filtrados);
    }

    // =============================
    // 📌 Inicialización
    // =============================
    mostrarDispositivos(dispositivosData);

    // Eventos
    busquedaInput.addEventListener('input', filtrarDispositivos);
    filtroMarca.addEventListener('change', filtrarDispositivos);

    cerrar.addEventListener('click', () => modal.style.display = 'none');
    window.addEventListener('click', e => {
      if (e.target === modal) modal.style.display = 'none';
    });

  })
  .catch(error => console.error('Error al cargar JSON:', error));