<?php
include __DIR__ . "/../src/conexion.php"; // Conexión a la base de datos

// ====================== FUNCIONES CRUD CATEGORÍAS ======================

// Listar todas las categorías
function listarCategorias($conn) {
    $sql = "SELECT id_categoria, nombre_categoria, descripcion FROM categoria";
    $result = $conn->query($sql);
    $categorias = [];
    while($row = $result->fetch_assoc()){
        $categorias[] = $row;
    }
    return $categorias;
}

// Obtener una categoría por ID
function obtenerCategoria($conn, $id) {
    $id = intval($id);
    $sql = "SELECT id_categoria, nombre_categoria, descripcion FROM categoria WHERE id_categoria = $id LIMIT 1";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Agregar una nueva categoría
function agregarCategoria($conn, $nombre_categoria, $descripcion) {
    $stmt = $conn->prepare("INSERT INTO categoria (nombre_categoria, descripcion) VALUES (?, ?)");
    $stmt->bind_param("ss", $nombre_categoria, $descripcion);
    return $stmt->execute();
}

// Editar una categoría existente
function editarCategoria($conn, $id, $nombre_categoria, $descripcion) {
    $stmt = $conn->prepare("UPDATE categoria SET nombre_categoria=?, descripcion=? WHERE id_categoria=?");
    $stmt->bind_param("ssi", $nombre_categoria, $descripcion, $id);
    return $stmt->execute();
}

// Eliminar una categoría
function eliminarCategoria($conn, $id) {
    $id = intval($id);
    $sql = "DELETE FROM categoria WHERE id_categoria = $id";
    return $conn->query($sql);
}

// ====================== MANEJAR PETICIONES HTTP ======================
header("Content-Type: application/json");
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":
        if (isset($_GET['id'])) {
            $categoria = obtenerCategoria($conn, $_GET['id']);
            echo json_encode($categoria);
        } else {
            echo json_encode(listarCategorias($conn));
        }
        break;

    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);
        if (agregarCategoria($conn, $data['nombre_categoria'], $data['descripcion'])) {
            echo json_encode(["message" => "Categoría agregada con éxito"]);
        } else {
            echo json_encode(["error" => "Error al agregar categoría"]);
        }
        break;

    case "PUT":
        $data = json_decode(file_get_contents("php://input"), true);
        if (editarCategoria($conn, $data['id_categoria'], $data['nombre_categoria'], $data['descripcion'])) {
            echo json_encode(["message" => "Categoría actualizada con éxito"]);
        } else {
            echo json_encode(["error" => "Error al actualizar categoría"]);
        }
        break;

    case "DELETE":
        $data = json_decode(file_get_contents("php://input"), true);
        if (eliminarCategoria($conn, $data['id_categoria'])) {
            echo json_encode(["message" => "Categoría eliminada con éxito"]);
        } else {
            echo json_encode(["error" => "Error al eliminar categoría"]);
        }
        break;

    default:
        echo json_encode(["error" => "Método no permitido"]);
        break;
}
?>
