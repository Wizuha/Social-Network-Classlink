<?php
//create group chat
require_once 'pdo.php';
session_start();
//recuperer les donnees de la requete ajax
try{
    $room = $_POST['room_name'];
    $description = $_POST['description'];
    $sql = "INSERT INTO group_chat(name, description) VALUES (:room, :description)";
    $stmt = $messaging_pdo->prepare($sql);
    $stmt->execute(
        array(
            ':room' => $room,
            ':description' => $description
        )
    );
    $response = array(
        'status' => 'success',
        'msg' => 'Group created successfully'
    );
}

echo json_encode($response);
exit();
