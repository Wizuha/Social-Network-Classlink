<?php
    session_start();
    require '../../vendor/autoload.php';
    require '../inc/pdo.php';
    use GuzzleHttp\Client;
    use GuzzleHttp\RequestOptions;
    $client = new \GuzzleHttp\Client();
    $data = [
        'id' => $_SESSION['id']
    ];
    $json = json_encode($data);

    $response = $client->post('http://localhost/SocialNetwork-Fullstack-Project/classlink_authentification/sql/logout.php', [
        'body' => $json
    ]);

?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="5;url=./login.php">
    <link href="../../assets/css/logout.css" rel="stylesheet">
    <title>Déconnexion</title>
</head>
<body>
    <a href="../dashboard.php"><img src="../../assets/img/logo.svg" alt="Logo de ClassLink" class="logo"></a>

    <div class="thanks">
        <p class="thanks-msg"><span class="1stpt">Merci de votre visite.</span> <span class="2ndpt">À bientôt sur <span class="site-name">ClassLink</span> !</span></p>
        <p class="redirection">Vous allez être redirigé dans <span id="countdown">5</span> secondes...</p>
    </div>

    <div class="purple-block">

    </div>

    <img src="../../assets/img/planet_stars.svg" alt="Image d'une planête entourée d'étoite" class="planet-stars">
</body>
<script src="../../assets/js/logout.js"></script>
</html>
