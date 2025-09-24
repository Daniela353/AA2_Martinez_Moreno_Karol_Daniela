-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-09-2025 a las 18:32:53
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `aa2_martinez_moreno_karol_daniela`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito_compra`
--

CREATE TABLE `carrito_compra` (
  `id_carrito` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_dispositivo` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL CHECK (`cantidad` > 0),
  `fecha_agregado` datetime DEFAULT current_timestamp(),
  `precio` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `id_categoria` int(11) NOT NULL,
  `nombre_categoria` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`id_categoria`, `nombre_categoria`, `descripcion`) VALUES
(1, 'Tecnologia', 'Dispositivos electronicos y gadgets'),
(2, 'Electrodomesticos', 'Productos para el hogar'),
(3, 'Audio', 'Altavoces, auriculares y equipos de sonido'),
(4, 'Video', 'Televisores, proyectores y monitores'),
(5, 'Hogar', 'Articulos y muebles para el hogar');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios`
--

CREATE TABLE `comentarios` (
  `id_comentario` int(11) NOT NULL,
  `id_dispositivo` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `calificacion` tinyint(1) DEFAULT NULL,
  `fecha_comentario` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `comentarios`
--

INSERT INTO `comentarios` (`id_comentario`, `id_dispositivo`, `id_usuario`, `comentario`, `calificacion`, `fecha_comentario`) VALUES
(7, 6, 2, 'Buen sonido ', 5, '2025-09-24 06:50:20'),
(8, 1, 2, 'buen producto , llego rápido', 5, '2025-09-24 07:41:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contacto`
--

CREATE TABLE `contacto` (
  `id_contacto` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `asunto` varchar(150) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha_envio` datetime NOT NULL DEFAULT current_timestamp(),
  `respuesta` text DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'Pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `contacto`
--

INSERT INTO `contacto` (`id_contacto`, `nombre`, `email`, `asunto`, `mensaje`, `fecha_envio`, `respuesta`, `estado`) VALUES
(1, 'Ana Martínez', 'ana.martinez@example.com', 'Consulta sobre disponibilidad', 'Hola, quisiera saber si tienen el modelo X disponible.', '2025-09-19 08:10:41', 'No en este momento no se encuentra ', 'Pendiente'),
(3, 'Ana Pére20', 'ana@mail.com', 'Problema con pedido', 'No recibí mi pedido', '2025-09-22 09:16:21', 'Esta en empaque todavía ', 'Proceso'),
(4, 'juan Pérez ', 'cliente@correo.com', 'soporte', 'tengo problemas con mi pedido ', '2025-09-24 07:57:27', NULL, 'Pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido`
--

CREATE TABLE `detalle_pedido` (
  `id_detalle` int(11) NOT NULL,
  `id_pedido` int(11) DEFAULT NULL,
  `id_dispositivo` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_pedido`
--

INSERT INTO `detalle_pedido` (`id_detalle`, `id_pedido`, `id_dispositivo`, `cantidad`, `precio_unitario`) VALUES
(8, 4, 1, 10, 1299.99),
(9, 6, 8, 11, 1299.99),
(10, 7, 2, 2, 1299.99),
(11, 11, 6, 1, 500000.00),
(12, 11, 7, 1, 350000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dispositivo`
--

CREATE TABLE `dispositivo` (
  `id_dispositivo` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Marca` int(11) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `Categoria` int(11) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `oferta` tinyint(1) DEFAULT NULL,
  `fecha_lanzamiento` date DEFAULT NULL,
  `resena` text DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `componentes` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `dispositivo`
--

INSERT INTO `dispositivo` (`id_dispositivo`, `Nombre`, `Marca`, `tipo`, `Categoria`, `precio`, `stock`, `oferta`, `fecha_lanzamiento`, `resena`, `descripcion`, `componentes`, `imagen`) VALUES
(1, 'Laptop Intel Core i9', 1, 'Laptop', 1, 2480000.00, 100, NULL, '2025-03-01', 'Versión mejorada con más rendimiento', 'Laptop de alto rendimiento para profesionales', '16GB RAM, SSD 512GB, Procesador Intel i7, Tarjeta gráfica NVIDIA GTX 1650', 'imagenes/Laptop_Intel_Core_i7.png'),
(2, 'Smartphone Galaxy S25', 2, 'Smartphone', 1, 2800000.00, 100, NULL, '2025-08-15', 'Rendimiento fluido y cámara profesional para todo tipo de uso.', 'Teléfono inteligente con cámara profesional y alto rendimiento.', '12GB RAM, 256GB Almacenamiento, Procesador Exynos, Pantalla AMOLED 6.7 pulgadas', 'imagenes/Smartphone Galaxy S25.png'),
(3, 'iPhone 15 Pro', 3, 'Smartphone', 1, 5000000.00, 100, NULL, '2025-09-01', 'Elegante y potente, ideal para fotos y gaming.', 'Último smartphone de Apple con cámara avanzada y pantalla Super Retina.', '6GB RAM, 256GB Almacenamiento, Procesador A17 Bionic', 'imagenes/iPhone 15 Pro.jpg'),
(4, 'Xiaomi 14', 4, 'Smartphone', 1, 1800000.00, 100, NULL, '2024-12-20', 'Rendimiento confiable a buen precio.', 'Smartphone económico con buena cámara y batería duradera.', '8GB RAM, 128GB Almacenamiento, Procesador Snapdragon 8', 'imagenes/Xiaomi 14.webp'),
(5, 'Oppo Reno 12 F 5G', 5, 'Smartphone', 1, 1500000.00, 100, NULL, '2024-11-10', 'Cámara profesional y diseño moderno.', 'Smartphone con cámara de 108MP y pantalla AMOLED.', '8GB RAM, 128GB Almacenamiento, Procesador MediaTek Dimensity', 'imagenes/Oppo_Reno_12.png'),
(6, 'Parlante Inalámbrico VTA 25W Sphere Beat', 6, 'Altavoz Inteligente', 3, 500000.00, 100, NULL, '2023-05-05', 'Controla tu hogar y reproduce música con calidad.', 'Altavoz inteligente con asistente de voz integrado.', 'Wi-Fi, Bluetooth, Asistente de voz integrado', 'imagenes/VTA_Smart_Speaker.png'),
(7, 'Estación Inteligente', 7, 'Domótica', 1, 350000.00, 100, NULL, '2024-01-20', 'Controla luces, cámaras y electrodomésticos desde tu smartphone.', 'Centro de control para dispositivos inteligentes del hogar.', 'Wi-Fi, Bluetooth, App móvil', 'imagenes/LifeSmart_Home_Hub.png'),
(8, 'Altavoz inteligente Alexa Echo Dot 5ta generación', 8, 'Altavoz Inteligente', 3, 400000.00, 100, NULL, '2023-07-10', 'Controla tu hogar y escucha música fácilmente.', 'Altavoz inteligente con Alexa integrada.', 'Wi-Fi, Bluetooth, Alexa', 'imagenes/Amazon_Echo_Dot.webp'),
(9, 'Cámara IP Wi-Fi Imou Cruiser SE+', 9, 'Cámara de seguridad', 1, 350000.00, 100, NULL, '2023-02-15', 'Fácil de instalar y controlar desde tu smartphone.', 'Cámara de seguridad para interior y exterior con visión nocturna.', 'Wi-Fi, Visión nocturna, App móvil', 'imagenes/IMOU_Camara_IP.webp'),
(10, 'Televisor HISENSE 50 pulgadas LED UHD 4K 50A6N', 10, 'Televisor', 4, 1800000.00, 100, NULL, '2024-06-05', 'Imagen nítida y conexión a servicios de streaming.', 'Televisor inteligente con resolución 4K.', '4K, Smart TV, Wi-Fi, HDMI', 'imagenes/Hisense_Smart_TV_50.webp'),
(11, 'Nevera No Frost Brutos Grafito', 11, 'Electrodomésticos', 2, 2800000.00, 100, NULL, '2023-10-10', 'Diseño moderno y excelente conservación de alimentos.', 'Refrigerador de alta capacidad con eficiencia energética.', '400L, Clase A+, Hielo automático', 'imagenes/Mabe_Refrigerador.webp'),
(12, 'Estufa con Horno Haceb 4 Puestos', 12, 'Electrodomésticos', 2, 1100000.00, 100, NULL, '2023-08-15', 'Cocina de forma eficiente y segura.', 'Estufa moderna con horno y 4 quemadores.', 'Gas natural, Horno incorporado, 4 quemadores', 'imagenes/Haceb_Estufa.png'),
(17, 'iPhone 16 Pro Max', 3, 'Smartphone', 1, 6449000.00, 100, NULL, NULL, NULL, NULL, NULL, 'imagenes/iPhone 16 Pro Max.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagen`
--

CREATE TABLE `imagen` (
  `id_imagen` int(11) NOT NULL,
  `id_dispositivo` int(11) DEFAULT NULL,
  `tipo_imagen` varchar(50) DEFAULT NULL,
  `imagen_secundaria` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `imagen`
--

INSERT INTO `imagen` (`id_imagen`, `id_dispositivo`, `tipo_imagen`, `imagen_secundaria`) VALUES
(1, 1, 'secundaria', 'imagenes/Laptop_Intel_Core_i7_2.png'),
(2, 1, 'secundaria', 'imagenes/Laptop_Intel_Core_i7_3.png'),
(3, 1, 'secundaria', 'imagenes/Laptop_Intel_Core_i7_4.png'),
(4, 1, 'secundaria', 'imagenes/Laptop_Intel_Core_i7_5.png'),
(5, 2, 'secundaria', 'imagenes/Smartphone Galaxy S25_1.png'),
(6, 2, 'secundaria', 'imagenes/Smartphone Galaxy S25_2.png'),
(7, 2, 'secundaria', 'imagenes/Smartphone Galaxy S25_3.png'),
(8, 2, 'secundaria', 'imagenes/Smartphone Galaxy S25_4.png'),
(9, 3, 'secundaria', 'imagenes/iPhone 15 Pro_1.jpg'),
(10, 3, 'secundaria', 'imagenes/iPhone 15 Pro_2.jpg'),
(11, 3, 'secundaria', 'imagenes/iPhone 15 Pro_3.jpg'),
(12, 3, 'secundaria', 'imagenes/iPhone 15 Pro_4.jpg'),
(13, 3, 'secundaria', 'imagenes/iPhone 15 Pro_5.jpg'),
(14, 4, 'secundaria', 'imagenes/Xiaomi 14_1.webp'),
(15, 4, 'secundaria', 'imagenes/Xiaomi 14_2.webp'),
(16, 5, 'secundaria', 'imagenes/Oppo_Reno_12_1.png'),
(17, 5, 'secundaria', 'imagenes/Oppo_Reno_12_2.png'),
(18, 5, 'secundaria', 'imagenes/Oppo_Reno_12_3.png'),
(19, 6, 'secundaria', 'imagenes/VTA_Smart_Speaker_2.png'),
(20, 6, 'secundaria', 'imagenes/VTA_Smart_Speaker_3.png'),
(21, 6, 'secundaria', 'imagenes/VTA_Smart_Speaker_4.png'),
(22, 7, 'secundaria', 'imagenes/LifeSmart_Home_Hub_1.png'),
(23, 7, 'secundaria', 'imagenes/LifeSmart_Home_Hub_2.png'),
(24, 7, 'secundaria', 'imagenes/LifeSmart_Home_Hub_3.png'),
(26, 8, 'secundaria', 'imagenes/Amazon_Echo_Dot_2.webp'),
(27, 8, 'secundaria', 'imagenes/Amazon_Echo_Dot_3.webp'),
(28, 9, 'secundaria', 'imagenes/IMOU_Camara_IP_1.webp'),
(29, 9, 'secundaria', 'imagenes/IMOU_Camara_IP_2.webp'),
(30, 9, 'secundaria', 'imagenes/IMOU_Camara_IP_3.webp'),
(31, 9, 'secundaria', 'imagenes/IMOU_Camara_IP_4.webp'),
(32, 10, 'secundaria', 'imagenes/Hisense_Smart_TV_50_1.webp'),
(33, 10, 'secundaria', 'imagenes/Hisense_Smart_TV_50_2.webp'),
(34, 10, 'secundaria', 'imagenes/Hisense_Smart_TV_50_3.webp'),
(35, 10, 'secundaria', 'imagenes/Hisense_Smart_TV_50_4.webp'),
(36, 11, 'secundaria', 'imagenes/Mabe_Refrigerador.webp'),
(37, 12, 'secundaria', 'imagenes/Haceb_Estufa_1.png'),
(38, 12, 'secundaria', 'imagenes/Haceb_Estufa_2.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log_evento`
--

CREATE TABLE `log_evento` (
  `id_log` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nombre_usuario` varchar(100) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `fecha_ingresada` date NOT NULL,
  `hora_ingresada` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `log_evento`
--

INSERT INTO `log_evento` (`id_log`, `id_usuario`, `nombre_usuario`, `accion`, `fecha_ingresada`, `hora_ingresada`) VALUES
(1, 1, 'Administrador', 'login', '2025-09-13', '17:26:05'),
(2, 1, 'Administrador', 'login', '2025-09-13', '17:35:53'),
(3, 1, 'Administrador', 'login', '2025-09-13', '17:47:46'),
(4, 1, 'Administrador', 'login', '2025-09-13', '17:58:47'),
(5, 2, 'Juan Pérez', 'login', '2025-09-13', '20:18:07'),
(6, 2, 'Juan Pérez', 'login', '2025-09-13', '20:30:50'),
(7, 2, 'Juan Pérez', 'login', '2025-09-13', '20:51:34'),
(8, 2, 'Juan Pérez', 'login', '2025-09-13', '21:18:31'),
(9, 2, 'Juan Pérez', 'login', '2025-09-13', '21:23:39'),
(10, 2, 'Juan Pérez', 'login', '2025-09-13', '22:02:55'),
(11, 2, 'Juan Pérez', 'login', '2025-09-13', '22:06:38'),
(12, 2, 'Juan Pérez', 'login', '2025-09-13', '22:11:08'),
(13, 1, 'Administrador', 'login', '2025-09-13', '23:28:47'),
(14, 2, 'Juan Pérez', 'login', '2025-09-14', '00:13:43'),
(15, 2, 'Juan Pérez', 'login', '2025-09-14', '00:24:10'),
(16, 2, 'Juan Pérez', 'login', '2025-09-14', '00:27:59'),
(17, 2, 'Juan Pérez', 'login', '2025-09-14', '00:29:37'),
(18, 2, 'Juan Pérez', 'login', '2025-09-14', '00:29:46'),
(19, 2, 'Juan Pérez', 'login', '2025-09-14', '00:46:37'),
(20, 1, 'Administrador', 'login', '2025-09-19', '18:54:11'),
(21, 1, 'Administrador', 'login', '2025-09-19', '19:05:29'),
(22, 1, 'Administrador', 'login', '2025-09-19', '20:19:37'),
(23, 1, 'Administrador', 'login', '2025-09-19', '20:23:07'),
(24, 1, 'Administrador', 'login', '2025-09-19', '20:27:19'),
(25, 1, 'Administrador', 'login', '2025-09-19', '20:30:36'),
(26, 1, 'Administrador', 'login', '2025-09-19', '20:44:33'),
(27, 1, 'Administrador', 'login', '2025-09-19', '20:51:31'),
(28, 1, 'Administrador', 'login', '2025-09-20', '14:09:07'),
(29, 2, 'Juan Pérez', 'login', '2025-09-20', '15:57:00'),
(30, 1, 'Administrador', 'login', '2025-09-20', '16:31:48'),
(31, 1, 'Administrador', 'login', '2025-09-21', '00:00:56'),
(32, 1, 'Administrador', 'login', '2025-09-21', '03:06:36'),
(33, 1, 'Administrador', 'login', '2025-09-21', '20:56:56'),
(34, 1, 'Administrador', 'login', '2025-09-21', '21:12:40'),
(35, 1, 'Administrador', 'login', '2025-09-21', '21:41:50'),
(36, 2, 'Juan Pérez', 'login', '2025-09-22', '10:23:07'),
(37, 1, 'Administrador', 'login', '2025-09-22', '11:04:56'),
(38, 2, 'Juan Pérez', 'login', '2025-09-22', '11:11:45'),
(39, 2, 'Juan Pérez', 'login', '2025-09-22', '11:38:27'),
(40, 7, 'Daniela Lopez ', 'login', '2025-09-24', '08:43:33'),
(41, 7, 'Daniela Lopez ', 'login', '2025-09-24', '08:43:43'),
(42, 7, 'Daniela Lopez ', 'login', '2025-09-24', '08:46:01'),
(43, 2, 'Juan Pérez', 'login', '2025-09-24', '08:50:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marca`
--

CREATE TABLE `marca` (
  `id_marca` int(11) NOT NULL,
  `nombre_marca` varchar(100) DEFAULT NULL,
  `pais_origen` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `marca`
--

INSERT INTO `marca` (`id_marca`, `nombre_marca`, `pais_origen`, `descripcion`) VALUES
(1, 'Dell', 'Estados Unidos', 'Marca actualizada para laptops y equipos de cómputo'),
(2, 'Samsung', 'Corea del Sur', 'Multinacional surcoreana dedicada a electrónica, electrodomésticos y dispositivos móviles.'),
(3, 'Apple', 'Estados Unidos', 'Compañía estadounidense que diseña y comercializa dispositivos electrónicos, software y servicios.'),
(4, 'Xiaomi', 'China', 'Empresa china especializada en teléfonos inteligentes, electrónica de consumo y software.'),
(5, 'Oppo', 'China', 'Compañía china de electrónica y telefonía móvil.'),
(6, 'VTA', 'Colombia', 'Marca de dispositivos electrónicos y altavoces inteligentes.'),
(7, 'LifeSmart Colombia', 'Colombia', 'Empresa enfocada en soluciones de domótica y hogar inteligente.'),
(8, 'Amazon Echo', 'Estados Unidos', 'Marca de altavoces inteligentes y dispositivos para el hogar, desarrollada por Amazon.'),
(9, 'IMOU', 'China', 'Marca especializada en cámaras de seguridad y soluciones de vigilancia inteligentes.'),
(10, 'Hisense', 'China', 'Compañía china que produce televisores, electrodomésticos y equipos electrónicos.'),
(11, 'Mabe', 'México', 'Fabricante de electrodomésticos con presencia en América Latina.'),
(12, 'Haceb', 'Colombia', 'Empresa colombiana especializada en electrodomésticos para el hogar.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ofertas`
--

CREATE TABLE `ofertas` (
  `id_oferta` int(11) NOT NULL,
  `id_dispositivo` int(11) DEFAULT NULL,
  `precio_original` decimal(10,2) DEFAULT NULL,
  `descuento_porcentaje` decimal(5,2) DEFAULT NULL,
  `precio_final` decimal(10,2) DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ofertas`
--

INSERT INTO `ofertas` (`id_oferta`, `id_dispositivo`, `precio_original`, `descuento_porcentaje`, `precio_final`, `estado`, `fecha_inicio`, `fecha_fin`) VALUES
(1, 1, 2480000.00, 10.00, 2232000.00, 'Activa', '2025-09-10', '2025-09-30'),
(2, 3, 5000000.00, 20.00, 4000000.00, 'Activa', '2025-09-10', '2025-09-30'),
(3, 2, 2800000.00, 15.00, 2380000.00, 'Activa', '2025-09-10', '2025-09-30'),
(4, 10, 1800000.00, 18.00, 1476000.00, 'Activa', '2025-09-10', '2025-09-30'),
(5, 8, 400000.00, 15.00, 340000.00, 'Activa', '2025-09-10', '2025-09-30'),
(15, NULL, NULL, 20.00, NULL, 'Activa', '2025-09-22', '2025-10-31'),
(16, 17, 6449000.00, 20.00, 5159200.00, 'Activa', '2025-09-23', '2025-10-31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido`
--

CREATE TABLE `pedido` (
  `id_pedido` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `estado` varchar(50) DEFAULT 'pendiente',
  `total` decimal(10,2) DEFAULT NULL,
  `fecha_orden` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido`
--

INSERT INTO `pedido` (`id_pedido`, `id_usuario`, `estado`, `total`, `fecha_orden`) VALUES
(4, 2, 'En proceso', 450000.00, '2025-09-11'),
(6, 2, 'En proceso', 1299.99, '2025-09-20'),
(7, 2, 'Cancelado', 2599.98, '2025-09-20'),
(8, 2, 'cancelado', 1299.99, '2025-09-20'),
(9, 2, 'pendiente', 4000000.00, '2025-09-22'),
(10, 2, 'pendiente', 12550400.00, '2025-09-23'),
(11, 2, 'pendiente', 850000.00, '2025-09-24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `fecha_registro` date DEFAULT curdate(),
  `estado` varchar(20) DEFAULT 'activo',
  `password` varchar(255) NOT NULL,
  `rol` varchar(50) DEFAULT 'usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre`, `email`, `fecha_registro`, `estado`, `password`, `rol`) VALUES
(1, 'Administrador', 'admin@tienda.com', '2025-09-10', 'activo', '$2y$10$uHW.pPprHboLFnTl0JRV6.Z8Rs./Q5yVoesEC/bCp8GA.sgcFTn8K', 'Administrador'),
(2, 'Juan Pérez', 'cliente@correo.com', '2025-09-10', 'activo', '$2y$10$2gAuHbIh7EnqV/QLAOWHdu2L5Z38RhM8DOkf2yZLb8osDLTF35pbG', 'Cliente'),
(7, 'Daniela Lopez ', 'DanielaLopez@cliente.com', '2025-09-24', 'activo', '$2y$10$dJoTuD3fouk0HRwf5cGDVuYTUtCbMxwSv54mpmGss48MtbHqnm/Wi', 'cliente'),
(8, 'Administrador General', 'admin@sistema.com', '2025-09-24', 'activo', 'admin123', 'Administrador'),
(10, 'Cliente General', 'cliente@sistema.com', '2025-09-24', 'activo', 'cliente123', 'cliente');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carrito_compra`
--
ALTER TABLE `carrito_compra`
  ADD PRIMARY KEY (`id_carrito`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_dispositivo` (`id_dispositivo`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`id_comentario`),
  ADD KEY `id_dispositivo` (`id_dispositivo`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `contacto`
--
ALTER TABLE `contacto`
  ADD PRIMARY KEY (`id_contacto`);

--
-- Indices de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_pedido` (`id_pedido`),
  ADD KEY `id_dispositivo` (`id_dispositivo`);

--
-- Indices de la tabla `dispositivo`
--
ALTER TABLE `dispositivo`
  ADD PRIMARY KEY (`id_dispositivo`),
  ADD KEY `Marca` (`Marca`),
  ADD KEY `Categoria` (`Categoria`);

--
-- Indices de la tabla `imagen`
--
ALTER TABLE `imagen`
  ADD PRIMARY KEY (`id_imagen`),
  ADD KEY `id_dispositivo` (`id_dispositivo`);

--
-- Indices de la tabla `log_evento`
--
ALTER TABLE `log_evento`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `fk_usuario` (`id_usuario`);

--
-- Indices de la tabla `marca`
--
ALTER TABLE `marca`
  ADD PRIMARY KEY (`id_marca`);

--
-- Indices de la tabla `ofertas`
--
ALTER TABLE `ofertas`
  ADD PRIMARY KEY (`id_oferta`),
  ADD KEY `id_dispositivo` (`id_dispositivo`);

--
-- Indices de la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carrito_compra`
--
ALTER TABLE `carrito_compra`
  MODIFY `id_carrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id_comentario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `contacto`
--
ALTER TABLE `contacto`
  MODIFY `id_contacto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `dispositivo`
--
ALTER TABLE `dispositivo`
  MODIFY `id_dispositivo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `imagen`
--
ALTER TABLE `imagen`
  MODIFY `id_imagen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de la tabla `log_evento`
--
ALTER TABLE `log_evento`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de la tabla `marca`
--
ALTER TABLE `marca`
  MODIFY `id_marca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `ofertas`
--
ALTER TABLE `ofertas`
  MODIFY `id_oferta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `pedido`
--
ALTER TABLE `pedido`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carrito_compra`
--
ALTER TABLE `carrito_compra`
  ADD CONSTRAINT `carrito_compra_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`),
  ADD CONSTRAINT `carrito_compra_ibfk_2` FOREIGN KEY (`id_dispositivo`) REFERENCES `dispositivo` (`id_dispositivo`);

--
-- Filtros para la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD CONSTRAINT `comentarios_ibfk_1` FOREIGN KEY (`id_dispositivo`) REFERENCES `dispositivo` (`id_dispositivo`),
  ADD CONSTRAINT `comentarios_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`),
  ADD CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`id_dispositivo`) REFERENCES `dispositivo` (`id_dispositivo`);

--
-- Filtros para la tabla `dispositivo`
--
ALTER TABLE `dispositivo`
  ADD CONSTRAINT `dispositivo_ibfk_1` FOREIGN KEY (`Marca`) REFERENCES `marca` (`id_marca`),
  ADD CONSTRAINT `dispositivo_ibfk_2` FOREIGN KEY (`Categoria`) REFERENCES `categoria` (`id_categoria`);

--
-- Filtros para la tabla `imagen`
--
ALTER TABLE `imagen`
  ADD CONSTRAINT `imagen_ibfk_1` FOREIGN KEY (`id_dispositivo`) REFERENCES `dispositivo` (`id_dispositivo`);

--
-- Filtros para la tabla `log_evento`
--
ALTER TABLE `log_evento`
  ADD CONSTRAINT `fk_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `ofertas`
--
ALTER TABLE `ofertas`
  ADD CONSTRAINT `ofertas_ibfk_1` FOREIGN KEY (`id_dispositivo`) REFERENCES `dispositivo` (`id_dispositivo`);

--
-- Filtros para la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `pedido_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
