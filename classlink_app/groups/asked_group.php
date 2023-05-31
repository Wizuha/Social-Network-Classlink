<?php
session_start();
require '../../classlink_app/inc/pdo.php'; //Besoin du pdo pour se connecter à la bdd

$group_ID = $_GET['id'];

$verify = $app_pdo -> prepare('
SELECT * FROM asked_groups 
WHERE group_id = :group_id
AND profile_id = :profile_id
');
$verify->execute([
    ':group_id'=>$_SESSION['id'],
    ':profile_id'=>$group_ID
]);
$result = $verify->fetch(PDO::FETCH_ASSOC);
if(!$result){
    $request_add_member = $app_pdo -> prepare('
    INSERT INTO asked_groups (group_id,profile_id,statut)
    VALUES (:group_id, :profile_id,:statut);
    ');

    $request_add_member -> execute([
        ':profile_id'=> $_SESSION['id'],
        ':group_id'=> $group_ID,
        'statut'=> 1
    ]);
    echo 'La demande a bien était envoyé';
}
else{
    echo 'La demande à déjà était envoyé !';
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="2;url=./group_dashboard.php">
    <title>Document</title>
</head>
<body>
    
</body>
</html>