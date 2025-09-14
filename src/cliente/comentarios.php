<?php
session_start();
include __DIR__ . "/conexion.php";
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$id_usuario = $_SESSION['id_usuario'] ?? null;
if(!$id_usuario){ echo json_encode(['success'=>false,'error'=>'No autenticado']); exit; }

switch($action){
    case 'listarPorUsuario':
        $res = $conn->query("SELECT c.*, d.Nombre as Dispositivo FROM comentarios c 
                             JOIN dispositivo d ON c.id_dispositivo=d.id_dispositivo
                             WHERE c.id_usuario=$id_usuario ORDER BY c.id_comentario DESC");
        $data = [];
        while($row=$res->fetch_assoc()) $data[]=$row;
        echo json_encode($data);
    break;

    case 'agregar':
        $input=json_decode(file_get_contents('php://input'),true);
        $id_disp=intval($input['id_dispositivo']);
        $comentario=$conn->real_escape_string($input['comentario']);
        $calificacion=intval($input['calificacion'] ?? 5);
        $conn->query("INSERT INTO comentarios (id_dispositivo,id_usuario,comentario,calificacion) 
                      VALUES ($id_disp,$id_usuario,'$comentario',$calificacion)");
        echo json_encode(['success'=>true]);
    break;

    default:
        echo json_encode(['success'=>false,'error'=>'Accion no valida']);
}
