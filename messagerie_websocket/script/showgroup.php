<?php
//voir les groupes
require_once 'pdo.php';
session_start();
//recuperer les donnees de la requete ajax
$sql = "SELECT * FROM group_chat";
$stmt = $messaging_pdo->prepare($sql);
$stmt->execute();
$reponse = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($reponse);
exit();