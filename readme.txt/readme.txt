AA2_Martinez_Moreno_Karol_Daniela

Descripción del proyecto:
	Este proyecto corresponde al desarrollo de un sistema de gestión integral que incluye módulos de dispositivos, pedidos, carrito de compras, comentarios, mensajes de contacto y registro de clientes.
	La aplicación está implementada con backend en PHP, base de datos MySQL/MariaDB y frontend en HTML, CSS y JavaScript (o el framework utilizado).
	Asimismo, el sistema proporciona una API REST segura con autenticación JWT, gestionando roles de Administrador y Cliente para garantizar control de acceso, seguridad y manejo eficiente de la información. gestionando roles de Administrador y Cliente para garantizar control de acceso, seguridad y manejo eficiente de la información.

2. Requerimientos:
	Servidor web con PHP 8.1 o superior (XAMPP recomendado)
	Base de datos MySQL / MariaDB
	Navegador web actualizado (Chrome, Firefox, Edge)
	Postman para probar la API
	IDE recomendado: VS Code, PHPStorm o Apache NetBeans para edición de código



3. Instalación y configuración:
	a) Descargar o clonar el repositorio:
	https://github.com/Daniela353/AA2_Martinez_Moreno_Karol_Daniela.git

	b) Copiar la carpeta del proyecto en el directorio de XAMPP:
	C:\xampp\htdocs\AA2_Martinez_Moreno_Karol_Daniela

	c) Importar la base de datos:

		1. Abrir phpMyAdmin
		2. Crear la base de datos: aa2_martinez_moreno_karol_daniela
		3. Importar el archivo aa2_martinez_moreno_karol_daniela.sql con todas las tablas y datos de prueba

	d) Configurar conexión a la base de datos en api/config/db.php modificando usuario y contraseña según la configuración de MySQL:

	$host = 'localhost';
	$db_name = 'aa2_martinez_moreno_karol_daniela';
	$username = 'root';
	$password = '';


	e) Iniciar XAMPP y activar Apache y MySQL.

4. Lenguaje y herramientas utilizadas:

	1. Backend: PHP 8.1
	2. Base de datos: MySQL 
	3. API REST con JWT
	4. Frontend: HTML, CSS, JavaScript
	5. Herramientas de prueba: Postman, navegador web , 
	6. IDE recomendado: VS Code, PHPStorm  Apache NetBeans

5. Estructura del proyecto:
	C:\xampp\htdocs\AA2_Martinez_Moreno_Karol_Daniela
│
├─ frontend/
│   ├─ public/
│   │   ├─ css/          → estilos
│   │   ├─ data/         → JSON de prueba (dispositivos, ofertas, usuarios)
│   │   ├─ imagenes/     → imágenes del frontend
│   │   └─ js/           → scripts (app.js, login.js, main.js, etc.)
│   ├─ admin.html
│   ├─ cliente.html
│   ├─ contacto.html
│   └─ ... otros HTML
│
├─ public/
│   ├─ categorias_crud.php
│   ├─ comentarios_crud.php
│   ├─ dispositivos_crud.php
│   ├─ login.php
│   ├─ logout.php
│   └─ ... otros PHP del frontend
│
├─ src/
│   ├─ admin/            → Archivos PHP del backend para admin
│   │   ├─ categorias.php
│   │   ├─ comentarios.php
│   │   └─ ... otros
│   ├─ cliente/           → Archivos PHP del backend para cliente
│   │   ├─ carrito.php
│   │   ├─ comentarios.php
│   │   └─ ... otros
│   ├─ cargar_datos.php
│   ├─ conexion.php
│   ├─ login_action.php
│   └─ ... otros
│
├─ postman_services/
--- API_AA2_Martinez_Moreno_Karol_Daniela     → Colecciones de pruebas en Postman
├─ api/
│   ├─ config/
│   │   └─ db.php
│   ├─ lib/
│   │   └─ jwt_helper.php
│   ├─ auth.php
│   ├─ dispositivos.php
│   └─ ... otros PHP de la API
│
├─ aa2_martinez_moreno_karol_daniela.sql
│
├─ Esquema de la base de datos aa2_martinez_moreno_karol_daniela.pdf
│
├─ informe_final.docx        → Informe completo del proyecto , pruebas de frontend integrado con backend y pruebas de Api Rest 
└─ readme.txt - Documentación general del proyecto
└─ URL del repositorio del proyecto AA2.txt



6. Uso:
	1.Abrir el navegador y acceder a las rutas del frontend.
	2.Para probar la API, usar Postman con los endpoints definidos en cada archivo PHP:
		usuario.php, 
		dispositivo.php, 
		marcas.php, 
		categorias.php, 
		comentarios.php, 
		imagen.php, 
		ofertas.php, 
		pedido.php, 
		detalle_pedido.php, 
		carrito_compra.php, 
		contacto.php, 
		log_evento.php, 
		registro.php.

	3.Los endpoints que requieren autenticación deben incluir el header:

		Authorization: Bearer <token>
		
	4.Roles:

		Administrador: Puede crear, actualizar y eliminar usuarios, marcas, categorías, dispositivos y logs.
		Cliente: Puede registrarse, comentar, comprar, administrar su carrito y ver sus propios pedidos.

	5.Flujos principales:

		1. Registro de clientes → Insertar directamente en tabla usuario con rol Cliente.
		2. Gestión de dispositivos → CRUD administrado por admin.
		3. Carrito y pedidos → CRUD permitido al cliente.
		4. Comentarios y contacto → Clientes pueden crear; admin puede responder y eliminar.
		5. Logs → Solo admin puede listar; borrado manual recomendado solo con script seguro.
		
		
	6.El proyecto puede abrirse en Apache NetBeans o cualquier IDE compatible con PHP para edición de código, pero para ejecutar la aplicación y probar la API es necesario que Apache y MySQL estén corriendo desde XAMPP


7. Notas importantes:
	1. Roles y permisos: El sistema distingue entre dos roles principales: Administrador y Cliente.
			Administrador: Tiene permisos exclusivos para crear, actualizar y eliminar registros críticos, tales como usuarios, marcas y logs de eventos.
			Cliente: Puede registrar su cuenta, realizar comentarios, gestionar pedidos y administrar su carrito de compras.

	2. Autenticación y seguridad:
			El sistema utiliza JWT (JSON Web Token) para la autenticación y autorización de los usuarios.
			Los tokens tienen un tiempo de expiración configurable (por defecto X horas), garantizando sesiones seguras.

	3. Respuestas de la API:
			Todos los endpoints de la API devuelven información en formato JSON, asegurando consistencia y compatibilidad con herramientas de prueba como Postman.
			Para realizar pruebas en Postman, se debe utilizar el token generado al iniciar sesión.

	4. Estructura del proyecto:
			La organización de carpetas y archivos presentada debe mantenerse para asegurar la correcta ejecución de la aplicación.	
			Se recomienda no modificar la estructura de directorios ni los nombres de archivos críticos para evitar errores en la conexión del backend y la base de datos.