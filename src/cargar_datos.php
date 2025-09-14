<?php
include "conexion.php";

echo "Conexión exitosa<br><br>";

// ================== LIMPIAR TABLAS ==================
$tablas = ["comentarios", "ofertas", "imagen", "dispositivo", "categoria", "marca", "usuario"];
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
foreach ($tablas as $tabla) {
    $conn->query("TRUNCATE TABLE $tabla");
}
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

echo "✅ Tablas limpiadas<br><br>";

// ================== FUNCIONES ==================
function normalize($str) {
    $str = trim(mb_strtolower($str, 'UTF-8'));
    $str = preg_replace('/[áàäâ]/u', 'a', $str);
    $str = preg_replace('/[éèëê]/u', 'e', $str);
    $str = preg_replace('/[íìïî]/u', 'i', $str);
    $str = preg_replace('/[óòöô]/u', 'o', $str);
    $str = preg_replace('/[úùüû]/u', 'u', $str);
    $str = preg_replace('/[ñ]/u', 'n', $str);
    $str = preg_replace('/\s+/u', ' ', $str);
    $str = preg_replace('/[^\P{C}\n]+/u', '', $str); // eliminar caracteres invisibles
    return $str;
}

function load_json($path) {
    $json = file_get_contents($path);
    if ($json === false) die("❌ No se pudo leer $path");
    // eliminar BOM si existe
    if (substr($json, 0, 3) === "\xEF\xBB\xBF") $json = substr($json, 3);
    $json = preg_replace('/^[\x00-\x1F\x80-\xFF]+/u', '', $json);
    $data = json_decode($json, true);
    if ($data === null) die("❌ Error JSON $path: " . json_last_error_msg());
    return $data;
}

// ================== CARGAR USUARIOS ==================
$usuarios = load_json(__DIR__ . "/../public/data/usuarios.json");
foreach ($usuarios as $u) {
    $stmt = $conn->prepare("INSERT INTO usuario (nombre, email, fecha_registro, estado, password, rol)
                            VALUES (?, ?, NOW(), 'activo', ?, ?)");
    $stmt->bind_param("ssss", $u['nombre'], $u['email'], $u['password'], $u['rol']);
    $stmt->execute();
    echo "✅ Usuario cargado: " . $u['nombre'] . "<br>";
}
echo "<br>";

// ================== CARGAR MARCAS ==================
$marcas = load_json(__DIR__ . "/../public/data/marcas.json");
foreach ($marcas as $m) {
    $nombre_marca = trim($m['nombre']);       // <- Cambiado de nombre_marca
    $pais_origen = $m['pais'] ?? '';          // <- Cambiado de pais_origen
    $descripcion = $m['descripcion'] ?? '';

    // Verificar si ya existe
    $stmt_check = $conn->prepare("SELECT id_marca FROM marca WHERE LOWER(nombre_marca) = LOWER(?)");
    $stmt_check->bind_param("s", $nombre_marca);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();

    if ($res_check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO marca (nombre_marca, pais_origen, descripcion) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre_marca, $pais_origen, $descripcion);
        $stmt->execute();
        $id_marca = $stmt->insert_id;
        echo "✅ Marca cargada: $nombre_marca<br>";
    } else {
        $row = $res_check->fetch_assoc();
        $id_marca = $row['id_marca'];
    }

    // Guardar ID real
    $marcas_existentes[normalize($nombre_marca)] = $id_marca;
};

// ================== CARGAR CATEGORÍAS ==================
$categorias = load_json(__DIR__ . "/../public/data/categorias.json");
$categorias_existentes = [];

foreach ($categorias as $c) {
    $nombre_categoria = trim($c['nombre_categoria']);
    $descripcion = $c['descripcion'] ?? '';

    $stmt_check = $conn->prepare("SELECT id_categoria FROM categoria WHERE LOWER(nombre_categoria) = LOWER(?)");
    $stmt_check->bind_param("s", $nombre_categoria);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();

    if ($res_check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO categoria (nombre_categoria, descripcion) VALUES (?, ?)");
        $stmt->bind_param("ss", $nombre_categoria, $descripcion);
        $stmt->execute();
        $id_categoria = $stmt->insert_id;
        echo "✅ Categoría cargada: $nombre_categoria<br>";
    } else {
        $row = $res_check->fetch_assoc();
        $id_categoria = $row['id_categoria'];
    }

    $categorias_existentes[normalize($nombre_categoria)] = $id_categoria;
}

echo "<br>";

// ================== CARGAR DISPOSITIVOS ==================
$dispositivos = load_json(__DIR__ . "/../public/data/dispositivos.json");

foreach ($dispositivos as $d) {
    $nombre = $d['nombre'] ?? '(sin nombre)';
    $categoria_original = $d['categoria'] ?? '';
    $marca = $d['marca'] ?? '';

    // Validaciones básicas
    if ($categoria_original === '' || $marca === '') {
        echo "❌ Dispositivo inválido ($nombre): falta categoría o marca<br>";
        continue;
    }

    $categoria_key = normalize($categoria_original);
    $marca_key = normalize($marca);

    if (!isset($categorias_existentes[$categoria_key])) {
        echo "❌ Dispositivo inválido ($nombre): categoría incorrecta ('$categoria_original')<br>";
        continue;
    }
    if (!isset($marcas_existentes[$marca_key])) {
        echo "❌ Dispositivo inválido ($nombre): marca incorrecta ('$marca')<br>";
        continue;
    }

    // Variables para bind_param
    $marca_id = $marcas_existentes[$marca_key];
    $categoria_id = $categorias_existentes[$categoria_key];
    $tipo = $d['tipo'] ?? '';
    $precio = floatval($d['precio'] ?? 0);
    $fecha_lanzamiento = $d['fecha_lanzamiento'] ?? '';
    $descripcion = $d['descripcion'] ?? '';
    $resena = $d['resena'] ?? '';
    $componentes = $d['componentes'] ?? '';
    $imagen = $d['imagen'] ?? '';

    $stmt = $conn->prepare("
        INSERT INTO dispositivo
        (Nombre, Marca, Categoria, tipo, precio, fecha_lanzamiento, descripcion, resena, componentes, imagen)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    // Tipos: s = string, i = int, d = decimal
    $stmt->bind_param(
        "siisdsssss",
        $nombre,
        $marca_id,
        $categoria_id,
        $tipo,
        $precio,
        $fecha_lanzamiento,
        $descripcion,
        $resena,
        $componentes,
        $imagen
    );

    $stmt->execute();

    echo "✅ Dispositivo insertado: $nombre (Marca ID: $marca_id, Categoría ID: $categoria_id)<br>";
}

// ================== CARGAR IMÁGENES ==================
foreach ($dispositivos as $d) {
    $nombre = $d['nombre'] ?? '(sin nombre)';

    // Buscar ID del dispositivo
    $stmt = $conn->prepare("SELECT id_dispositivo FROM dispositivo WHERE LOWER(nombre)=LOWER(?)");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $id_dispositivo = $row['id_dispositivo'];

        // Revisar si tiene fotos secundarias
        if (!empty($d['fotos']) && is_array($d['fotos'])) {
            foreach ($d['fotos'] as $foto) {
                $tipo_imagen = 'secundaria';
                $imagen_secundaria = $foto;

                $stmt_img = $conn->prepare("
                    INSERT INTO imagen (id_dispositivo, tipo_imagen, imagen_secundaria)
                    VALUES (?, ?, ?)
                ");
                $stmt_img->bind_param("iss", $id_dispositivo, $tipo_imagen, $imagen_secundaria);
                $stmt_img->execute();

                echo "✅ Imagen cargada para $nombre: $imagen_secundaria<br>";
            }
        }
    } else {
        echo "⚠️ No se encontró el dispositivo para cargar imágenes: $nombre<br>";
    }
}


// ================== CARGAR OFERTAS ==================
$ofertas = load_json(__DIR__ . "/../public/data/ofertas.json");

foreach ($ofertas as $o) {
    $nombre_dispositivo = $o['nombre'] ?? '';
    $stmt = $conn->prepare("SELECT id_dispositivo FROM dispositivo WHERE LOWER(nombre)=LOWER(?)");
    $stmt->bind_param("s", $nombre_dispositivo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $id_dispositivo = $row['id_dispositivo'];
        $descuento = intval(str_replace("%", "", $o['descuento'] ?? 0));
        $precio_original = $o['precio'] ?? 0;
        $precio_final = $o['precio_final'] ?? 0;

        $stmt_oferta = $conn->prepare("
            INSERT INTO ofertas (id_dispositivo, precio_original, descuento_porcentaje, precio_final, estado, fecha_inicio, fecha_fin)
            VALUES (?, ?, ?, ?, 'Activa', '2025-09-10', '2025-09-30')
        ");
        $stmt_oferta->bind_param("idid", $id_dispositivo, $precio_original, $descuento, $precio_final);
        $stmt_oferta->execute();

        echo "✅ Oferta cargada para: $nombre_dispositivo<br>";
    } else {
        echo "⚠️ No se encontró el dispositivo para oferta: $nombre_dispositivo<br>";
    }
}

$conn->close();
echo "<br>🎉 Carga completada con éxito";
?>
