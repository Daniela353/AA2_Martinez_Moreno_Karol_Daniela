<?php
session_start();
include __DIR__ . "/../conexion.php";

header('Content-Type: application/json');

$accion = $_REQUEST['accion'] ?? '';

switch ($accion) {
    case 'listar':
        $filtro = $_GET['filtro'] ?? '';
        $sql = "SELECT * FROM categoria WHERE nombre_categoria LIKE ? ORDER BY nombre_categoria";
        $stmt = $conn->prepare($sql);
        $filtro_param = "%" . $filtro . "%";
        $stmt->bind_param("s", $filtro_param);
        $stmt->execute();
        $res = $stmt->get_result();

        $categorias = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $categorias[] = $row;
            }
            echo json_encode(["status" => "ok", "data" => $categorias]);
        } else {
            echo json_encode(["status" => "error", "msg" => "Error al obtener datos: " . $conn->error]);
        }
        $stmt->close();
        break;

    case 'agregar':
        $nombre = $_POST['nombre_categoria'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        
        // --- 1. Verificación de categoría existente ---
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM categoria WHERE nombre_categoria = ?");
        $stmt_check->bind_param("s", $nombre);
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($count > 0) {
            // La categoría ya existe, enviar un mensaje de error
            echo json_encode(["status" => "error", "msg" => "La categoría ya existe en la base de datos."]);
            break;
        }

        // --- 2. Si no existe, proceder con la inserción ---
        $stmt = $conn->prepare("INSERT INTO categoria (nombre_categoria, descripcion) VALUES (?, ?)");
        $stmt->bind_param("ss", $nombre, $descripcion);
        if ($stmt->execute()) {
            echo json_encode(["status" => "ok", "msg" => "Categoría agregada."]);
        } else {
            echo json_encode(["status" => "error", "msg" => "Error al agregar: " . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'editar':
        $id = $_POST['id_categoria'] ?? null;
        $nombre = $_POST['nombre_categoria'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        
        if (!$id) {
            echo json_encode(["status" => "error", "msg" => "ID de categoría no proporcionado."]);
            exit;
        }
        
        $stmt = $conn->prepare("UPDATE categoria SET nombre_categoria = ?, descripcion = ? WHERE id_categoria = ?");
        $stmt->bind_param("ssi", $nombre, $descripcion, $id);
        if ($stmt->execute()) {
            echo json_encode(["status" => "ok", "msg" => "Categoría actualizada."]);
        } else {
            echo json_encode(["status" => "error", "msg" => "Error al actualizar: " . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'eliminar':
        $id = $_POST['id_categoria'] ?? null;
        if (!$id) {
            echo json_encode(["status" => "error", "msg" => "ID de categoría no proporcionado."]);
            exit;
        }
        $stmt = $conn->prepare("DELETE FROM categoria WHERE id_categoria = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(["status" => "ok", "msg" => "Categoría eliminada."]);
        } else {
            echo json_encode(["status" => "error", "msg" => "Error al eliminar: " . $stmt->error]);
        }
        $stmt->close();
        break;
}
?>