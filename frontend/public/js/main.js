const deviceList = document.getElementById('device-list');

// Cargar JSON resumido
fetch('data/dispositivos.json')
    .then(response => response.json())
    .then(dispositivos => {
        dispositivos.forEach(d => {
            const card = document.createElement('div');
            card.classList.add('device-card');

            card.innerHTML = `
                <img src="${d.imagen}" alt="${d.nombre}">
                <h3>${d.nombre}</h3>
                <p>Marca: ${d.marca}</p>
                <p>Precio: $${d.precio}</p>
            `;

            // Al hacer clic, ir a devices.html (detalle completo)
            card.addEventListener('click', () => {
                window.location.href = `devices.html?id=${d.id}`;
            });

            deviceList.appendChild(card);
        });
    })
    .catch(error => console.error('Error al cargar JSON:', error));