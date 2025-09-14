<?php
include __DIR__ . "/../src/conexion.php"; // Conexión a la base de datos

header('Content-Type: application/json'); // Todas las respuestas serán JSON

$action = $_GET['action'] ?? '';

switch($action) {

    // ================= LISTAR TODOS LOS DISPOSITIVOS =================
    case 'list':
        $dispositivos = [];
        $sql = "SELECT d.id_dispositivo, d.Nombre, m.nombre_marca AS Marca, c.nombre_categoria AS Categoria, d.tipo, d.precio, d.fecha_lanzamiento, d.resena
                FROM dispositivo d
                JOIN marca m ON d.Marca = m.id_marca
                JOIN categoria c ON d.Categoria = c.id_categoria";
        $result = $conn->query($sql);

        while($row = $result->fetch_assoc()){
            $id_dispositivo = intval($row['id_dispositivo']);
            $dispositivo = [
                'id' => $id_dispositivo,
                'nombre' => $row['Nombre'],
                'marca' => $row['Marca'],
                'categoria' => $row['Categoria'],
                'tipo' => $row['tipo'],
                'precio' => floatval($row['precio']),
                'fecha_lanzamiento' => $row['fecha_lanzamiento'],
                'resena' => $row['resena'],
                'fotos' => [],
                'oferta' => false,
                'precio_descuento' => floatval($row['precio'])
            ];

            // ================= IMÁGENES =================
            $img_sql = "SELECT imagen_secundaria FROM imagen WHERE id_dispositivo = $id_dispositivo";
            $img_result = $conn->query($img_sql);
            while($img_row = $img_result->fetch_assoc()){
                $dispositivo['fotos'][] = '/AA2_Martinez_Moreno_Karol_Daniela/public/imagenes/' . rawurlencode(basename($img_row['imagen_secundaria']));
            }
           $dispositivo['imagen'] = count($dispositivo['fotos']) > 0
            ? $dispositivo['fotos'][0]
                : 'default.png';

            // ================= OFERTAS =================
            $oferta_sql = "SELECT precio_final FROM ofertas WHERE id_dispositivo = $id_dispositivo AND estado='Activa' LIMIT 1";
            $oferta_result = $conn->query($oferta_sql);
            if($oferta_result->num_rows > 0){
                $oferta_row = $oferta_result->fetch_assoc();
                $dispositivo['oferta'] = true;
                $dispositivo['precio_descuento'] = floatval($oferta_row['precio_final']);
            }

            // ================= COMENTARIOS =================
            $comentarios = [];
            $coment_sql = "SELECT comentario FROM comentarios WHERE id_dispositivo = $id_dispositivo ORDER BY id_comentario ASC";
            $coment_result = $conn->query($coment_sql);
            while($coment_row = $coment_result->fetch_assoc()){
                $comentarios[] = $coment_row['comentario'];
            }
            $dispositivo['comentarios'] = $comentarios;

            $dispositivos[] = $dispositivo;
        }

        echo json_encode($dispositivos);
        break;

    // ================= OBTENER UN DISPOSITIVO POR ID =================
    case 'get':
        $id = intval($_GET['id'] ?? 0);
        $sql = "SELECT d.id_dispositivo, d.Nombre, m.nombre_marca AS Marca, c.nombre_categoria AS Categoria, d.tipo, d.precio, d.fecha_lanzamiento, d.resena
                FROM dispositivo d
                JOIN marca m ON d.Marca = m.id_marca
                JOIN categoria c ON d.Categoria = c.id_categoria
                WHERE d.id_dispositivo = $id LIMIT 1";
        $result = $conn->query($sql);
        $dispositivo = $result->fetch_assoc() ?: [];

        // Procesar imágenes igual que en list
        if(!empty($dispositivo)){
            $img_sql = "SELECT imagen_secundaria FROM imagen WHERE id_dispositivo = $id";
            $img_result = $conn->query($img_sql);
            $fotos = [];
            while($img_row = $img_result->fetch_assoc()){
                $fotos[] = '/AA2_Martinez_Moreno_Karol_Daniela/public/imagenes/' . rawurlencode(basename($img_row['imagen_secundaria']));
            }
            $dispositivo['fotos'] = $fotos;
            $dispositivo['imagen'] = count($fotos) > 0 ? $fotos[0] : '/AA2_Martinez_Moreno_Karol_Daniela/public/imagenes/default.png';
        }

        echo json_encode($dispositivo);
        break;

    // ================= AGREGAR DISPOSITIVO =================
    case 'add':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("INSERT INTO dispositivo (Nombre, Marca, Categoria, tipo, precio, fecha_lanzamiento, resena) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "siisdss",
            $data['nombre'],
            $data['marca_id'],
            $data['categoria_id'],
            $data['tipo'],
            $data['precio'],
            $data['fecha_lanzamiento'],
            $data['resena']
        );
        echo json_encode(['success' => $stmt->execute()]);
        break;

    // ================= EDITAR DISPOSITIVO =================
    case 'edit':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("UPDATE dispositivo SET Nombre=?, Marca=?, Categoria=?, tipo=?, precio=?, fecha_lanzamiento=?, resena=? WHERE id_dispositivo=?");
        $stmt->bind_param(
            "siisdssi",
            $data['nombre'],
            $data['marca_id'],
            $data['categoria_id'],
            $data['tipo'],
            $data['precio'],
            $data['fecha_lanzamiento'],
            $data['resena'],
            $data['id']
        );
        echo json_encode(['success' => $stmt->execute()]);
        break;

    // ================= ELIMINAR DISPOSITIVO =================
        case 'delete':
    $id = intval($_GET['id'] ?? 0);

    // Primero borrar las imágenes relacionadas
    $conn->query("DELETE FROM imagen WHERE id_dispositivo = $id");

    // Luego borrar el dispositivo
    $sql = "DELETE FROM dispositivo WHERE id_dispositivo = $id";
    echo json_encode(['success' => $conn->query($sql)]);
    break;
}
$conn->close();
?>
