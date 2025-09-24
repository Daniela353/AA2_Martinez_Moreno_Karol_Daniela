<?php
session_start();
include __DIR__ . "/../conexion.php";

// Verificar rol de administrador (recomendable)
// if (!isset($_SESSION['admin_id']) || $_SESSION['rol'] !== 'Administrador') {
//     header('Content-Type: application/json');
//     echo json_encode(["status" => "error", "msg" => "Acceso denegado"]);
//     exit;
// }

header('Content-Type: application/json');

$accion = $_REQUEST['accion'] ?? '';

if ($accion === 'listar') {
    $filtro = $_GET['filtro'] ?? '';
    $sql = "SELECT * FROM marca WHERE nombre_marca LIKE ? OR pais_origen LIKE ? ORDER BY nombre_marca";
    $stmt = $conn->prepare($sql);
    $filtro_param = "%" . $filtro . "%";
    $stmt->bind_param("ss", $filtro_param, $filtro_param);
    $stmt->execute();
    $res = $stmt->get_result();

    $marcas = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $marcas[] = $row;
        }
        echo json_encode(["status" => "ok", "data" => $marcas]);
    } else {
        echo json_encode(["status" => "error", "msg" => "Error al obtener datos: " . $conn->error]);
    }
    $stmt->close();
    exit;
}

if ($accion === 'eliminar') {
    $id = $_POST['id_marca'] ?? null;
    if (!$id) {
        echo json_encode(["status" => "error", "msg" => "ID de marca no proporcionado."]);
        exit;
    }
    $stmt = $conn->prepare("DELETE FROM marca WHERE id_marca = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(["status" => "ok", "msg" => "Marca eliminada."]);
    } else {
        echo json_encode(["status" => "error", "msg" => "Error al eliminar: " . $stmt->error]);
    }
    $stmt->close();
    exit;
}

// ================= AGREGAR =================
if ($accion === 'agregar') {
    $nombre = trim($_POST['nombre_marca'] ?? '');
    $pais = trim($_POST['pais_origen'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($nombre) {
        // 1. Verificar si la marca ya existe
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM marca WHERE nombre_marca = ?");
        $stmt_check->bind_param("s", $nombre);
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($count > 0) {
            // La marca ya existe, enviar un mensaje de error
            echo json_encode(["status" => "error", "msg" => "La marca ya existe en la base de datos."]);
        } else {
            // 2. Si no existe, proceder con la inserción
            $stmt = $conn->prepare("INSERT INTO marca (nombre_marca, pais_origen, descripcion) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nombre, $pais, $descripcion);
            if ($stmt->execute()) {
                echo json_encode(["status" => "ok", "msg" => "Marca agregada exitosamente."]);
            } else {
                echo json_encode(["status" => "error", "msg" => $stmt->error]);
            }
            $stmt->close();
        }
    } else {
        echo json_encode(["status" => "error", "msg" => "Nombre de marca obligatorio"]);
    }
    exit;
}

// ================= EDITAR =================
if ($accion === 'editar') {
    $id = intval($_POST['id_marca']);
    $nombre = trim($_POST['nombre_marca']);
    $pais = trim($_POST['pais_origen']);
    $descripcion = trim($_POST['descripcion']);

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE marca SET nombre_marca=?, pais_origen=?, descripcion=? WHERE id_marca=?");
        $stmt->bind_param("sssi", $nombre, $pais, $descripcion, $id);
        if ($stmt->execute()) {
            echo json_encode(["status" => "ok"]);
        } else {
            echo json_encode(["status" => "error", "msg" => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "msg" => "ID de marca no válido"]);
    }
    exit;
}

// ================= ELIMINAR =================
if ($accion === 'eliminar') {
    $id = intval($_POST['id_marca']);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM marca WHERE id_marca=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(["status" => "ok"]);
        } else {
            echo json_encode(["status" => "error", "msg" => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "msg" => "ID de marca no válido"]);
    }
    exit;
}
?>