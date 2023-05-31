<?php
require_once 'pdo.php';
$message = $_POST['message'];
$id = $_POST['id'];

//mettre a jour le message et lheure dans la base de donnée avec le pdo
$sql= "UPDATE private_chat_messages SET message = :message  WHERE id = :id";

    $stmt = $messaging_pdo->prepare($sql);
    $stmt->execute(array(
        ':message' => $_POST['message'],
        ':id' => $_POST['id']
    
    ));
    if($stmt->rowCount() > 0){
        echo "success";
    }else{
        echo "error";
        
    }
    
    exit();
?>