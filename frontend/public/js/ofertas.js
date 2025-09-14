const ofertasList = document.getElementById("ofertasList");
const busquedaOfertas = document.getElementById("busquedaOfertas");
const filtroMarcaOfertas = document.getElementById("filtroMarcaOfertas");

// Mostrar ofertas
function mostrarOfertas(lista) {
  ofertasList.innerHTML = '';
  if(lista.length === 0){
    ofertasList.innerHTML = '<p>No hay ofertas disponibles.</p>';
    return;
  }
  lista.forEach(oferta => {
    const card = document.createElement('div');
    card.classList.add('device-card');
    card.innerHTML = `
      <img src="${oferta.imagen}" alt="${oferta.nombre}">
      <h3>${oferta.nombre}</h3>
      <p>Marca: ${oferta.marca}</p>
      <p><del>$${oferta.precio}</del></p>
      <p><strong>Oferta: $${oferta.precio_final} (${oferta.descuento})</strong></p>
    `;
    ofertasList.appendChild(card);
  });
}

// Filtrar ofertas
function filtrarOfertas() {
  const texto = busquedaOfertas.value.toLowerCase();
  const marca = filtroMarcaOfertas.value;

  const filtrados = ofertas.filter(o => 
    o.nombre.toLowerCase().includes(texto) && (marca === '' || o.marca === marca)
  );

  mostrarOfertas(filtrados);
}

// Eventos
busquedaOfertas.addEventListener('keyup', filtrarOfertas);
filtroMarcaOfertas.addEventListener('change', filtrarOfertas);