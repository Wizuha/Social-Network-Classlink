<?php
require_once 'pdo.php';
    session_start();
    //recuperer les donnees de la relation_id de mon ami et moi
    $id = $_POST['friend'];
    $sql = "SELECT * FROM relation WHERE (id_demandeur = :id and id_receveur = :friend) or (id_demandeur = :friend and id_receveur = :id)";
    $stmt = $app_pdo->prepare($sql);
    $stmt->execute(array(
        ':id' => $_SESSION['id'],
        ':friend' => $id
    ));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $relation_id = json_encode($result['id']);
    echo $relation_id;


   

  


    exit();