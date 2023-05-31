<?php
//recuperer les messages privés

require_once "pdo.php";
session_start();
$id= $_SESSION['id'];
$friend= $_POST['friend'];
$sql = "SELECT * FROM private_chat_messages WHERE (recever_id = :id and sender_id = :friend) or (recever_id = :friend and sender_id = :id)";
$stmt = $messaging_pdo->prepare($sql);
$stmt->execute(array(
    ':id' => $id,
    ':friend' => $friend
 
));
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
$final= json_encode($result);
echo $final;