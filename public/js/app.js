// Selección de elementos
const deviceList = document.getElementById('device-list');
const busquedaInput = document.getElementById('busqueda');
const filtroMarca = document.getElementById('filtroMarca');

// Modal elementos
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

let dispositivosData = [];
let comentarios = {}; // Guardar comentarios por dispositivo

// Función para mostrar tarjetas resumidas
function mostrarDispositivos(dispositivos) {
    deviceList.innerHTML = '';
    dispositivos.forEach(d => {
        const card = document.createElement('div');
        card.classList.add('device-card');

        const img = document.createElement('img');
        img.src = d.imagen;
        img.alt = d.nombre;

        card.appendChild(img);
        card.innerHTML += `<h3>${d.nombre}</h3>
                           <p>Marca: ${d.marca}</p>
                           <p>Precio: $${d.precio}</p>`;

        // Abrir modal al hacer clic en la tarjeta
        card.addEventListener('click', () => abrirModal(d));

        deviceList.appendChild(card);
    });
}

// Función para abrir modal
function abrirModal(d) {
    modal.style.display = 'flex';
    modalImg.src = d.imagen;
    modalNombre.textContent = d.nombre;
    modalMarca.textContent = 'Marca: ' + d.marca;
    modalTipo.textContent = 'Tipo: ' + d.tipo;
    modalPrecio.textContent = 'Precio: $' + d.precio;
    modalFecha.textContent = 'Lanzamiento: ' + d.fecha_lanzamiento;
    modalDescripcion.textContent = d.descripcion;
    modalResena.textContent = d.resena || '';
    modalComponentes.textContent = d.componentes || '';

    // Cargar fotos adicionales
    modalFotos.innerHTML = '';
    if (d.fotos) {
        d.fotos.forEach(foto => {
            const imgExtra = document.createElement('img');
            imgExtra.src = foto;
            imgExtra.alt = d.nombre;
            imgExtra.addEventListener('click', () => {
                modalImg.src = foto; // Cambia la imagen principal al hacer clic
            });
            modalFotos.appendChild(imgExtra);
        });
    }

    // Cargar comentarios existentes
    listaComentarios.innerHTML = '';
    if (comentarios[d.id]) {
        comentarios[d.id].forEach(c => {
            const li = document.createElement('li');
            li.textContent = c;
            listaComentarios.appendChild(li);
        });
    }
    
    // Limpiar textarea
    nuevoComentario.value = '';

    // Manejar botón de agregar comentario
    agregarComentarioBtn.onclick = () => {
        const texto = nuevoComentario.value.trim();
        if (texto) {
            if (!comentarios[d.id]) comentarios[d.id] = [];
            comentarios[d.id].push(texto);
            const li = document.createElement('li');
            li.textContent = texto;
            listaComentarios.appendChild(li);
            nuevoComentario.value = '';
        }
    };
}

// Cerrar modal
cerrar.addEventListener('click', () => { modal.style.display = 'none'; });
window.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });

// Cargar JSON
fetch('dispositivos.json')
    .then(res => res.json())
    .then(data => {
        dispositivosData = data;
        mostrarDispositivos(dispositivosData);
    })
    .catch(err => console.error('Error al cargar JSON:', err));

// Filtros
busquedaInput.addEventListener('input', filtrarDispositivos);
filtroMarca.addEventListener('change', filtrarDispositivos);

function filtrarDispositivos() {
    const busqueda = busquedaInput.value.toLowerCase();
    const marca = filtroMarca.value;
    const filtrados = dispositivosData.filter(d => {
        const coincideNombre = d.nombre.toLowerCase().includes(busqueda);
        const coincideMarca = marca === '' || d.marca === marca;
        return coincideNombre && coincideMarca;
    });
    mostrarDispositivos(filtrados);
}