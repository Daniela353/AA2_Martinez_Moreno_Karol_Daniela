<?php
include __DIR__ . "/../conexion.php";

$accion = $_REQUEST['accion'] ?? '';
$filtroDispositivo = $_REQUEST['filtroDispositivo'] ?? '';
$filtroDescuento = $_REQUEST['filtroDescuento'] ?? '';

if ($accion == 'listar') {
    // Consulta base
    $sql = "SELECT o.id_oferta, o.id_dispositivo, o.precio_original, o.descuento_porcentaje,
                   o.precio_final, o.estado, o.fecha_inicio, o.fecha_fin,
                   d.nombre
            FROM ofertas o
            LEFT JOIN dispositivo d ON o.id_dispositivo = d.id_dispositivo";

    // Arrays para guardar las condiciones y los parámetros de los filtros
    $condiciones = [];
    $tipos = "";
    $parametros = [];

    // Lógica para el filtro por nombre de dispositivo
    if (!empty($filtroDispositivo)) {
        $condiciones[] = "d.nombre LIKE ?";
        $tipos .= "s"; // "s" por string
        $parametros[] = "%" . $filtroDispositivo . "%";
    }
    
    // Lógica para el filtro por descuento
    if (!empty($filtroDescuento) && is_numeric($filtroDescuento)) {
        $condiciones[] = "o.descuento_porcentaje >= ?";
        $tipos .= "i"; // "i" por integer (o "d" por double si es decimal)
        $parametros[] = (int)$filtroDescuento;
    }
    
    // Si hay condiciones, las agregamos a la consulta
    if (!empty($condiciones)) {
        $sql .= " WHERE " . implode(" AND ", $condiciones);
    }
    
    // Ordenar los resultados
    $sql .= " ORDER BY o.id_oferta DESC";
    
    // Preparar la consulta
    $stmt = $conn->prepare($sql);
    
    // Vincular los parámetros de forma dinámica
    if (!empty($parametros)) {
        $stmt->bind_param($tipos, ...$parametros);
    }
    
    // Ejecutar la consulta
    $stmt->execute();
    $result = $stmt->get_result();

    // Obtener los datos y enviarlos como JSON
    $data = [];
    while($row = $result->fetch_assoc()){
        $data[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($data);
    $stmt->close();
    exit;
}


if ($accion == 'agregar') {
    $id_dispositivo = $_POST['id_dispositivo'];
    $precio_original = $_POST['precio_original'];
    $descuento_porcentaje = $_POST['descuento_porcentaje'];
    $precio_final = $precio_original - ($precio_original * ($descuento_porcentaje/100));
    $estado = $_POST['estado'] ?? 'inactiva';
    $fecha_inicio = $_POST['fecha_inicio'] ?? null;
    $fecha_fin = $_POST['fecha_fin'] ?? null;

    $stmt = $conn->prepare("INSERT INTO ofertas (id_dispositivo, precio_original, descuento_porcentaje, precio_final, estado, fecha_inicio, fecha_fin)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idddsss", $id_dispositivo, $precio_original, $descuento_porcentaje, $precio_final, $estado, $fecha_inicio, $fecha_fin);

    if($stmt->execute()){
        echo json_encode(["status"=>"ok"]);
    } else {
        echo json_encode(["status"=>"error", "msg"=>$stmt->error]);
    }
    $stmt->close();
    exit;
}

if ($accion == 'editar') {
    $id_oferta = $_POST['id_oferta'];
    $id_dispositivo = $_POST['id_dispositivo'];
    $precio_original = $_POST['precio_original'];
    $descuento_porcentaje = $_POST['descuento_porcentaje'];
    $precio_final = $precio_original - ($precio_original * ($descuento_porcentaje/100));
    $estado = $_POST['estado'] ?? 'inactiva';
    $fecha_inicio = $_POST['fecha_inicio'] ?? null;
    $fecha_fin = $_POST['fecha_fin'] ?? null;

    $stmt = $conn->prepare("UPDATE ofertas SET id_dispositivo=?, precio_original=?, descuento_porcentaje=?, precio_final=?, estado=?, fecha_inicio=?, fecha_fin=? WHERE id_oferta=?");
    $stmt->bind_param("idddsssi", $id_dispositivo, $precio_original, $descuento_porcentaje, $precio_final, $estado, $fecha_inicio, $fecha_fin, $id_oferta);

    if($stmt->execute()){
        echo json_encode(["status"=>"ok"]);
    } else {
        echo json_encode(["status"=>"error", "msg"=>$stmt->error]);
    }
    $stmt->close();
    exit;
}

if ($accion == 'eliminar') {
    $id_oferta = $_POST['id_oferta'];
    $stmt = $conn->prepare("DELETE FROM ofertas WHERE id_oferta=?");
    $stmt->bind_param("i", $id_oferta);

    if($stmt->execute()){
        echo json_encode(["status"=>"ok"]);
    } else {
        echo json_encode(["status"=>"error", "msg"=>$stmt->error]);
    }
    $stmt->close();
    exit;
}
?>
