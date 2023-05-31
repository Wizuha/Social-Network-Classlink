<?php
require_once "../../classlink_app/inc/pdo.php" ;
session_start();
//get user friend list
$sql="SELECT * FROM relation WHERE statut = 2 and (id_demandeur = :user or id_receveur = :user)";
$stmt = $app_pdo->prepare($sql);
$stmt->execute(array(
    ':user' => $_SESSION['id']
));
$friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
$listefriends = array();
foreach($friends as $friend){
    if($friend['id_demandeur'] == $_SESSION['id']){
        $friend = $friend['id_receveur'];
        
    }
    else{
        $friend = $friend['id_demandeur'];
       
    }
    if(isset($friend)){
        $sql="SELECT * FROM profiles WHERE id = :friend";
        $stmt = $app_pdo->prepare($sql);
        $stmt->execute(array(
            ':friend' => $friend
        ));
        $friend = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $listefriends[] = $friend;
        //enregistre les donnees usernames et id dans un tableau
      
       

       
    }
}
echo json_encode($listefriends);






//recupere le nom de l'amie



