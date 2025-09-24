<?php
include __DIR__ . "/../conexion.php";
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar rol de administrador
if (!isset($_SESSION['admin_id']) || $_SESSION['rol'] !== 'Administrador') {
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "msg" => "Acceso denegado"]);
    exit;
}
include __DIR__ . "/../conexion.php";

$accion = $_REQUEST['accion'] ?? '';

switch ($accion) {
    // ... (cases existentes)

    case 'listar_marcas':
        $sql = "SELECT id_marca, nombre_marca FROM marca ORDER BY nombre_marca";
        $res = $conn->query($sql);
        $marcas = [];
        if ($res) {
            while ($fila = $res->fetch_assoc()) {
                $marcas[] = $fila;
            }
        }
        header('Content-Type: application/json'); // ¡Aquí está la solución!
        echo json_encode($marcas);
        exit;

    case 'listar_categorias':
        $sql = "SELECT id_categoria, nombre_categoria FROM categoria ORDER BY nombre_categoria";
        $res = $conn->query($sql);
        $categorias = [];
        if ($res) {
            while ($fila = $res->fetch_assoc()) {
                $categorias[] = $fila;
            }
        }
        header('Content-Type: application/json'); // ¡Y aquí también!
        echo json_encode($categorias);
        exit;

    default:
        // ... (código existente)
        break;
}
if ($accion == 'listar') {
    $filtro = $_GET['filtro'] ?? '';

    // Consulta SQL mejorada con LEFT JOIN
    $sql = "
        SELECT
            d.id_dispositivo,
            d.nombre AS Nombre,
            d.precio,
            d.stock,
            m.nombre_marca AS Marca,
            c.nombre_categoria AS Categoria,
            d.tipo AS tipo
        FROM dispositivo d
        LEFT JOIN marca m ON d.Marca = m.id_marca
        LEFT JOIN categoria c ON d.Categoria = c.id_categoria
        WHERE
            d.nombre LIKE '%$filtro%' OR
            m.nombre_marca LIKE '%$filtro%' OR
            c.nombre_categoria LIKE '%$filtro%' OR
            d.tipo LIKE '%$filtro%'
    ";

    $res = $conn->query($sql);
    $dispositivos = [];
    
    if ($res) {
        while ($fila = $res->fetch_assoc()) {
            $fila['Nombre'] = $fila['Nombre'] ?? '';
            $fila['Marca'] = $fila['Marca'] ?? '';
            $fila['Categoria'] = $fila['Categoria'] ?? '';
            $dispositivos[] = $fila;
        }
        header('Content-Type: application/json');
        echo json_encode($dispositivos);
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(["status" => "error", "message" => "Error en la consulta SQL: " . $conn->error]);
    }
    
    exit;
}


function subirImagen($campo){
    if(isset($_FILES[$campo]) && $_FILES[$campo]['error'] == 0){
        $rutaDestino = __DIR__ . "/../../public/imagenes/";
        $nombreArchivo = time() . "_" . basename($_FILES[$campo]['name']);
        $archivoDestino = $rutaDestino . $nombreArchivo;
        if(move_uploaded_file($_FILES[$campo]['tmp_name'], $archivoDestino)){
            return $nombreArchivo;
        }
    }
    return null;
}

if ($accion == 'agregar') {
    $nombre = $_POST['nombre'] ?? '';
    $marca = $_POST['marca'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $categoria = $_POST['categoria'] ?? '';
    $precio = $_POST['precio'] ?? '';
    $stock = $_POST['stock'] ?? '';
    $imagen = subirImagen('imagen');

    // **CAMBIADA LA SENTENCIA SQL Y EL BIND_PARAM**
    $stmt = $conn->prepare("INSERT INTO dispositivo 
        (Nombre, Marca, tipo, Categoria, precio, stock, imagen)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sisidis", $nombre, $marca, $tipo, $categoria, $precio, $stock, $imagen);

    if($stmt->execute()){
        echo json_encode(["status"=>"ok", "msg" => "Dispositivo agregado correctamente"]);
    } else {
        echo json_encode(["status"=>"error","msg"=>$stmt->error]);
    }
    $stmt->close();
    exit;
}

if ($accion == 'editar') {
    $id = $_POST['id_dispositivo'] ?? null;
    $nombre = $_POST['nombre'] ?? '';
    $marca = $_POST['marca'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $categoria = $_POST['categoria'] ?? '';
    $precio = $_POST['precio'] ?? '';
    $stock = $_POST['stock'] ?? '';

    // Lógica para subir la imagen si se proporciona una nueva
    $imagenNueva = subirImagen('imagen');
    
    // Prepara la consulta SQL base
    $sql = "UPDATE dispositivo SET 
        Nombre = ?, 
        Marca = ?, 
        tipo = ?, 
        Categoria = ?, 
        precio = ?, 
        stock = ?";
    $tipos_param = "sisidi"; // Tipo de parámetros para los 6 campos
    $parametros = [$nombre, $marca, $tipo, $categoria, $precio, $stock];

    // Si se subió una nueva imagen, la agrega a la consulta y a los parámetros
    if ($imagenNueva) {
        $sql .= ", imagen = ?";
        $tipos_param .= "s";
        $parametros[] = $imagenNueva;
    }
    
    // Agrega la condición WHERE al final
    $sql .= " WHERE id_dispositivo = ?";
    $tipos_param .= "i";
    $parametros[] = $id;

    $stmt = $conn->prepare($sql);
    
    // Llama a bind_param dinámicamente
    $stmt->bind_param($tipos_param, ...$parametros);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "ok", "msg" => "Dispositivo actualizado correctamente."]);
    } else {
        echo json_encode(["status" => "error", "msg" => "Error al actualizar: " . $stmt->error]);
    }
    $stmt->close();
    exit;
}

if ($accion == 'eliminar') {
    $id = $_POST['id_dispositivo'] ?? null;
    if (!$id) {
        echo json_encode(["status" => "error", "msg" => "ID de dispositivo no proporcionado."]);
        exit;
    }

    $conn->begin_transaction();

    try {
        // 1. Eliminar la imagen asociada
        $stmt_img = $conn->prepare("DELETE FROM imagen WHERE id_dispositivo = ?");
        $stmt_img->bind_param("i", $id);
        $stmt_img->execute();
        $stmt_img->close();

        // 2. Eliminar el dispositivo
        $stmt_disp = $conn->prepare("DELETE FROM dispositivo WHERE id_dispositivo = ?");
        $stmt_disp->bind_param("i", $id);
        $stmt_disp->execute();
        $stmt_disp->close();

        $conn->commit();
        echo json_encode(["status" => "ok", "msg" => "Dispositivo y su imagen eliminados correctamente."]);

    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        echo json_encode(["status" => "error", "msg" => "Error al eliminar: " . $e->getMessage()]);
    }
    exit;
}
?>
