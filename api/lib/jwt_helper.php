<?php
// jwt_helper.php

// Cambia esta clave por algo seguro para tu proyecto
define('JWT_SECRET_KEY', 'mi_clave_secreta_1234');

// Crear token JWT
function jwt_create($payload, $exp = 3600) {
    $header = json_encode(['typ'=>'JWT','alg'=>'HS256']);
    $payload['iat'] = time();
    $payload['exp'] = time() + $exp;
    
    $base64UrlHeader = str_replace(['+','/','=' ], ['-','_',''], base64_encode($header));
    $base64UrlPayload = str_replace(['+','/','=' ], ['-','_',''], base64_encode(json_encode($payload)));
    
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET_KEY, true);
    $base64UrlSignature = str_replace(['+','/','=' ], ['-','_',''], base64_encode($signature));
    
    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

// Verificar token JWT
function jwt_verify($token) {
    $parts = explode('.', $token);
    if(count($parts) != 3) return false;

    list($header, $payload, $signature) = $parts;
    $valid_sig = hash_hmac('sha256', "$header.$payload", JWT_SECRET_KEY, true);
    $valid_sig = str_replace(['+','/','=' ], ['-','_',''], base64_encode($valid_sig));

    if ($valid_sig !== $signature) return false;

    $payload_decoded = json_decode(base64_decode(str_replace(['-','_'], ['+','/'], $payload)), true);

    if($payload_decoded['exp'] < time()) return false;

    return true;
}

// Decodificar token
function jwt_decode($token) {
    $parts = explode('.', $token);
    if(count($parts) != 3) return null;

    $payload = $parts[1];
    $payload_decoded = json_decode(base64_decode(str_replace(['-','_'], ['+','/'], $payload)), true);
    return $payload_decoded;
}
?>
