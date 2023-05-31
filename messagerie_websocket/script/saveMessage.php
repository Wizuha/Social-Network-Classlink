<?php
require_once 'pdo.php';
    session_start();
    //recuperer les donnees de la requete ajax
    
    $room = $_POST['room'];
    $messages = $_POST['msg'];
  
    $date = date('Y-m-d H:i:s');
    
    
    $sql = "INSERT INTO private_chat_messages ( message, sender_id,recever_id, created_at) VALUES (:messages,  :sender, :room,:date); select LAST_INSERT_ID() as id
    ";
    $stmt = $messaging_pdo->prepare($sql);
    $stmt->execute(
        array(
            ':room' => $room,
            ':messages' => $messages,
            ':sender' => $_SESSION['id'],
            ':date' => $date
        )
    );
    $id = $stmt->fetch(PDO::FETCH_ASSOC);
    $id = $id['id'];
    echo $id;

    //encoyer une reponse au client
    if($stmt->rowCount() > 0){
        $response = array(
            'id' => $id,
            'status' => 'success',
            'msg' => 'Message sent successfully'
        );
    }else{
        $response = array(
            'status' => 'error',
            'msg' => 'Message not sent'
        );
    }
    echo json_encode($response);
    exit();