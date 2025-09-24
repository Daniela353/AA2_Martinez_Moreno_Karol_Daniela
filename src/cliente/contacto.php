<?php
header('Content-Type: application/json');
session_start();

include __DIR__ . '/../conexion.php'; // Asegúrate de que la ruta sea correcta

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método de solicitud no válido.']);
    exit;
}

// Obtener datos del formulario enviados por JavaScript
$nombre = $_POST['nombre'] ?? '';
$email = $_POST['email'] ?? '';
$asunto = $_POST['motivo'] ?? ''; // <-- CAMBIADO DE '$motivo' a '$asunto'
$mensaje = $_POST['mensaje'] ?? '';

// Validar que los campos no estén vacíos
if (empty($nombre) || empty($email) || empty($asunto) || empty($mensaje)) {
    echo json_encode(['success' => false, 'error' => 'Todos los campos son obligatorios.']);
    exit;
}

try {
    // Preparar la consulta SQL para evitar inyección SQL
    // AHORA LA COLUMNA ES 'asunto'
    $stmt = $conn->prepare("INSERT INTO contacto (nombre, email, asunto, mensaje) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre, $email, $asunto, $mensaje);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => '¡Mensaje enviado con éxito!']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al guardar el mensaje.']);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Capturar cualquier excepción y devolver un error detallado
    echo json_encode(['success' => false, 'error' => 'Excepción: ' . $e->getMessage()]);
}

?>