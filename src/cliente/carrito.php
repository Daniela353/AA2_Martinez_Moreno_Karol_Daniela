<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include __DIR__ . "/../conexion.php";

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$id_usuario = $_SESSION['id_usuario'] ?? null;

if (!$id_usuario) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}
$id_usuario = intval($id_usuario);
switch ($action) {

    case 'listar':
        // Se obtiene el precio directamente de la tabla 'dispositivo'
        $res = $conn->query("
            SELECT c.id_carrito, c.cantidad, c.id_dispositivo, d.Nombre, d.precio
            FROM carrito_compra c
            JOIN dispositivo d ON c.id_dispositivo = d.id_dispositivo
            WHERE c.id_usuario = $id_usuario
        ");
        $data = [];
        while($row = $res->fetch_assoc()){
            $data[] = $row;
        }
        echo json_encode(['success' => true, 'items' => $data]);
        break;

    case 'agregar':
        $input = json_decode(file_get_contents('php://input'), true);
        
        $id_dispositivo = $input['id_dispositivo'] ?? null;
        $cantidad = $input['cantidad'] ?? 1;
        
        if (!$id_dispositivo || $cantidad <= 0) {
            echo json_encode(['success' => false, 'error' => 'Datos de entrada inválidos.']);
            exit;
        }

        $id_dispositivo = intval($id_dispositivo);
        $cantidad = intval($cantidad);

        $conn->begin_transaction();
        try {
            // CORRECCIÓN: Se obtiene el precio directamente de la tabla 'dispositivo'
            $sql_stock = "SELECT stock, precio FROM dispositivo WHERE id_dispositivo = ?";
            $stmt_stock = $conn->prepare($sql_stock);
            if (!$stmt_stock) {
                throw new Exception("Error al preparar la consulta de stock: " . $conn->error);
            }
            $stmt_stock->bind_param("i", $id_dispositivo);
            $stmt_stock->execute();
            $result_stock = $stmt_stock->get_result();
            $dispositivo = $result_stock->fetch_assoc();
            $stmt_stock->close();

            if (!$dispositivo) {
                // CORRECCIÓN: Si el dispositivo no se encuentra, se lanza una excepción
                throw new Exception("Dispositivo no encontrado.");
            }
            
            $precio_final = $dispositivo['precio'];

            // Verificar si el producto ya existe en el carrito
            $sql_verificar = "SELECT id_carrito, cantidad FROM carrito_compra WHERE id_usuario = ? AND id_dispositivo = ?";
            $stmt_verificar = $conn->prepare($sql_verificar);
            if (!$stmt_verificar) {
                throw new Exception("Error al preparar la consulta de verificación: " . $conn->error);
            }
            $stmt_verificar->bind_param("ii", $id_usuario, $id_dispositivo);
            $stmt_verificar->execute();
            $res_verificar = $stmt_verificar->get_result();
            $item_existente = $res_verificar->fetch_assoc();
            $stmt_verificar->close();

            if ($item_existente) {
                $nueva_cantidad = $item_existente['cantidad'] + $cantidad;
                if ($dispositivo['stock'] < $nueva_cantidad) {
                    throw new Exception('No hay suficiente stock disponible. Stock actual: ' . $dispositivo['stock']);
                }

                $sql_update = "UPDATE carrito_compra SET cantidad = ?, precio = ? WHERE id_carrito = ?";
                $stmt_update = $conn->prepare($sql_update);
                if (!$stmt_update) {
                    throw new Exception("Error al preparar la consulta de actualización: " . $conn->error);
                }
                $stmt_update->bind_param("idi", $nueva_cantidad, $precio_final, $item_existente['id_carrito']);
                if (!$stmt_update->execute()) {
                    throw new Exception("Error al actualizar la cantidad: " . $stmt_update->error);
                }
                $stmt_update->close();
            } else {
                if ($dispositivo['stock'] < $cantidad) {
                    throw new Exception('No hay suficiente stock disponible. Stock actual: ' . $dispositivo['stock']);
                }

                $sql_insert = "INSERT INTO carrito_compra (id_usuario, id_dispositivo, cantidad, precio) VALUES (?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);
                if (!$stmt_insert) {
                    throw new Exception("Error al preparar la consulta de inserción: " . $conn->error);
                }
                $stmt_insert->bind_param("iiid", $id_usuario, $id_dispositivo, $cantidad, $precio_final);
                if (!$stmt_insert->execute()) {
                    throw new Exception("Error al agregar al carrito: " . $stmt_insert->error);
                }
                $stmt_insert->close();
            }

            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Producto agregado.']);

        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    case 'eliminar':
        $id_carrito = intval($_GET['id_carrito']);
        $stmt = $conn->prepare("DELETE FROM carrito_compra WHERE id_carrito = ? AND id_usuario = ?");
        if (!$stmt) {
            echo json_encode(['success' => false, 'error' => 'Error al preparar la consulta de eliminación.']);
            exit;
        }
        $stmt->bind_param("ii", $id_carrito, $id_usuario);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Producto no encontrado en el carrito o ya eliminado.']);
        }
        $stmt->close();
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Acción no válida']);
        break;

    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $id_carrito = $input['id_carrito'] ?? null;
            $cantidad = $input['cantidad'] ?? null;

            if ($id_carrito && $cantidad && $cantidad > 0) {
                // Corregido: Usar $conn (MySQLi) en lugar de $pdo
                // Corregido: Usar la tabla 'carrito_compra' y verificar que el usuario sea el dueño del carrito
                $query = "UPDATE carrito_compra SET cantidad = ? WHERE id_carrito = ? AND id_usuario = ?";
                $stmt = $conn->prepare($query);

                if ($stmt) {
                    $stmt->bind_param("iii", $cantidad, $id_carrito, $id_usuario);
                    $stmt->execute();

                    if ($stmt->affected_rows > 0) {
                        echo json_encode(['success' => true]);
                    } else {
                        // El producto no se actualizó, podría no existir o la cantidad era la misma
                        echo json_encode(['success' => false, 'error' => 'No se pudo actualizar la cantidad. El item no existe o no te pertenece.']);
                    }
                    $stmt->close();
                } else {
                    echo json_encode(['success' => false, 'error' => 'Error al preparar la consulta de actualización: ' . $conn->error]);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Datos de actualización inválidos.']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Método no permitido.']);
        }
        break;

}
?>