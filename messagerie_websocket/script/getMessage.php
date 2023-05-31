<?php
require_once 'pdo.php';
    session_start();
    //recuperer les donnees de la requete ajax
    $room = $_GET['room'];

    $sql = "SELECT * FROM private_chats WHERE relation_id = '$room'";
    $stmt = $messaging_pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $final= json_encode($result);
    echo $final;
  


    exit();