<?php
    session_start();
    require '../../classlink_app/inc/pdo.php'; //Besoin du pdo pour se connecter à la bdd
    require '../../classlink_app/inc/functions/token_functions.php'; // Récupère la fonction pour créer un token


    $json = file_get_contents('php://input');

    $data = json_decode($json, true);

    $id = $data["id"];

    $requete = $auth_pdo->prepare("
    UPDATE token SET token = :token WHERE token.user_id = :user
    ");
    $requete->execute([
    ":token" => 'null',
    ":user" => $id
    ]);
    session_destroy();
    exit();
