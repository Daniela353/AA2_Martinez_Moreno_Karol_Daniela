<?php
session_start();
include __DIR__ . "/../conexion.php";

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

$id_usuario = $_SESSION['id_usuario'] ?? null;
if(!$id_usuario){
    echo json_encode(['success'=>false,'error'=>'No autenticado']);
    exit;
}

switch($action){

    case 'listar':
        $res = $conn->query("
            SELECT c.id_carrito, c.cantidad, d.Nombre, d.precio, o.precio_final, o.id_oferta
            FROM carrito_compra c
            JOIN dispositivo d ON c.id_dispositivo=d.id_dispositivo
            LEFT JOIN ofertas o ON d.id_dispositivo=o.id_dispositivo AND o.estado='Activa'
            WHERE c.id_usuario=$id_usuario
        ");
        $data = [];
        while($row=$res->fetch_assoc()){
            $row['precio'] = $row['id_oferta'] ? $row['precio_final'] : $row['precio'];
            $data[]=$row;
        }
        echo json_encode($data);
        break;

    case 'agregar':
        $input = json_decode(file_get_contents('php://input'), true);
        if(!$input || !isset($input['id_dispositivo'])){
            echo json_encode(['success'=>false,'error'=>'Datos inválidos']);
            exit;
        }
        $id_disp = intval($input['id_dispositivo']);
        $cantidad = intval($input['cantidad'] ?? 1);

        $check = $conn->query("SELECT id_carrito, cantidad FROM carrito_compra WHERE id_usuario=$id_usuario AND id_dispositivo=$id_disp");
        if($check->num_rows > 0){
            $row = $check->fetch_assoc();
            $nueva_cant = $row['cantidad'] + $cantidad;
            $conn->query("UPDATE carrito_compra SET cantidad=$nueva_cant WHERE id_carrito=".$row['id_carrito']);
        } else {
            $conn->query("INSERT INTO carrito_compra (id_usuario,id_dispositivo,cantidad) VALUES ($id_usuario,$id_disp,$cantidad)");
        }

        echo json_encode(['success'=>true]);
        break;

    case 'eliminar':
        $id_carrito = intval($_GET['id_carrito']);
        $conn->query("DELETE FROM carrito_compra WHERE id_carrito=$id_carrito AND id_usuario=$id_usuario");
        echo json_encode(['success'=>true]);
        break;

    default:
        echo json_encode(['success'=>false,'error'=>'Acción no válida']);
}