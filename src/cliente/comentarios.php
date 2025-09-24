<?php
session_start();
include __DIR__ . "/../conexion.php";
header('Content-Type: application/json');

// Get action and user ID from the session
$action = $_GET['action'] ?? '';
$id_usuario = $_SESSION['id_usuario'] ?? null;

// Exit if the user is not authenticated
if (!$id_usuario) { 
    echo json_encode(['success' => false, 'error' => 'No autenticado']); 
    exit; 
}

$id_usuario = intval($id_usuario);

// The `switch` block processes the received action
switch ($action) {
    case 'agregar':
        // Decode JSON data from the request body
        $input = json_decode(file_get_contents('php://input'), true);

        // Validate that input data is not empty
        if ($input === null || !isset($input['id_dispositivo'], $input['comentario'], $input['calificacion'])) {
            echo json_encode(['success' => false, 'error' => 'Datos incompletos.']);
            exit;
        }

        // Assign and validate variables
        $id_dispositivo = intval($input['id_dispositivo']);
        $comentario = $input['comentario'];
        $calificacion = intval($input['calificacion']);

        // Validate that the user has purchased the product
        $sql_compra = "SELECT COUNT(*) FROM pedido p
                       JOIN detalle_pedido dp ON p.id_pedido = dp.id_pedido
                       WHERE p.id_usuario = ? AND dp.id_dispositivo = ?";
        
        $stmt_compra = $conn->prepare($sql_compra);
        if (!$stmt_compra) {
            echo json_encode(['success' => false, 'error' => 'Error al preparar la consulta de validación de compra: ' . $conn->error]);
            exit;
        }
        $stmt_compra->bind_param("ii", $id_usuario, $id_dispositivo);
        $stmt_compra->execute();
        $stmt_compra->bind_result($compra_existente);
        $stmt_compra->fetch();
        $stmt_compra->close();

        if ($compra_existente == 0) {
            echo json_encode(['success' => false, 'error' => 'Solo puedes comentar productos que has comprado.']);
            exit;
        }

        // Insert the comment if the purchase exists
        $sql_insert = "INSERT INTO comentarios (id_dispositivo, id_usuario, comentario, calificacion) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        if (!$stmt_insert) {
            echo json_encode(['success' => false, 'error' => 'Error al preparar la consulta de inserción: ' . $conn->error]);
            exit;
        }
        $stmt_insert->bind_param("iisi", $id_dispositivo, $id_usuario, $comentario, $calificacion);

        if ($stmt_insert->execute()) {
            echo json_encode(['success' => true, 'message' => 'Comentario agregado con éxito.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al agregar el comentario: ' . $stmt_insert->error]);
        }
        $stmt_insert->close();
    break;

    case 'listar':
        $id_dispositivo = intval($_GET['id_dispositivo'] ?? 0);

        if ($id_dispositivo === 0) {
            echo json_encode(['success' => false, 'error' => 'ID de dispositivo no proporcionado.']);
            exit;
        }

        $sql = "SELECT c.*, u.nombre AS nombre_usuario 
                FROM comentarios c
                JOIN usuario u ON c.id_usuario = u.id_usuario
                WHERE c.id_dispositivo = ?
                ORDER BY c.fecha_comentario DESC";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo json_encode(['success' => false, 'error' => 'Error al preparar la consulta: ' . $conn->error]);
            exit;
        }

        $stmt->bind_param("i", $id_dispositivo);
        $stmt->execute();
        $result = $stmt->get_result();
        $comentarios = [];
        while ($row = $result->fetch_assoc()) {
            $comentarios[] = $row;
        }

        echo json_encode(['success' => true, 'comentarios' => $comentarios]);
        $stmt->close();
    break;

    case 'editar':
        $input = json_decode(file_get_contents('php://input'), true);

        if ($input === null || !isset($input['id_comentario'], $input['comentario'], $input['calificacion'])) {
            echo json_encode(['success' => false, 'error' => 'Datos incompletos para la edición.']);
            exit;
        }

        $id_comentario = intval($input['id_comentario']);
        $comentario = $input['comentario'];
        $calificacion = intval($input['calificacion']);

        // 1. Validate that the comment belongs to the session user
        $sql_verificar = "SELECT id_usuario FROM comentarios WHERE id_comentario = ?";
        $stmt_verificar = $conn->prepare($sql_verificar);
        if (!$stmt_verificar) {
            echo json_encode(['success' => false, 'error' => 'Error al verificar el comentario.']);
            exit;
        }
        $stmt_verificar->bind_param("i", $id_comentario);
        $stmt_verificar->execute();
        $stmt_verificar->bind_result($propietario_id);
        $stmt_verificar->fetch();
        $stmt_verificar->close();

        if ($propietario_id != $id_usuario) {
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para editar este comentario.']);
            exit;
        }

        // 2. Update the comment in the database
        $sql_update = "UPDATE comentarios SET comentario = ?, calificacion = ? WHERE id_comentario = ?";
        $stmt_update = $conn->prepare($sql_update);
        if (!$stmt_update) {
            echo json_encode(['success' => false, 'error' => 'Error al preparar la consulta de edición.']);
            exit;
        }
        $stmt_update->bind_param("sii", $comentario, $calificacion, $id_comentario);

        if ($stmt_update->execute()) {
            echo json_encode(['success' => true, 'message' => 'Comentario actualizado con éxito.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al actualizar el comentario: ' . $stmt_update->error]);
        }
        $stmt_update->close();
    break;

    default:
        // If the action is invalid
        echo json_encode(['success' => false, 'error' => 'Acción no válida']);
        break;
}

// Close the connection
if (isset($conn)) {
    $conn->close();
}
?>