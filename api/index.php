<?php
// index.php

// Incluye conexión y JWT
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/lib/jwt_helper.php';

// Detecta qué recurso llaman en la URL
$uri = $_SERVER['REQUEST_URI'];

if (strpos($uri, '/usuario') !== false) {
    require_once __DIR__ . '/usuario.php';
} elseif (strpos($uri, '/dispositivo') !== false) {
    require_once __DIR__ . '/dispositivo.php';
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Ruta no encontrada']);
}