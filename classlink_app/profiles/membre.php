<?php
session_start();
require '../inc/pdo.php';
require '../../vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
       
if(isset($_SESSION['id'])) {
    $afficher_membres = $app_pdo->prepare("SELECT * FROM profiles WHERE id <> ?;");
    $afficher_membres->execute(array($_SESSION['id']));
} else {
    $afficher_membres = $app_pdo->prepare("SELECT * FROM profiles;");
    $afficher_membres->execute();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../../assets/css/profile.css" rel="stylesheet"></link>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <title>Membre</title>
</head>

<header>
        <li>
            <a href="" data-bs-toggle="modal" data-bs-target="#exampleModal">Menu</a>
            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">MENU</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <a href="./profile.php">Mon profil</a>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
                </div>
            </div>
            </div>
        </li>
    </header>

<body>
    
<div class='container'>

    <?php 
        foreach($afficher_membres as $ap) {
    ?>
        <div class='member'>
            <?= $ap['id'] .' ';
            echo $ap['last_name'] .' ';
            echo $ap['first_name'] . '<br>'; ?>
        </div>

            <a href="voir_profile.php?id=<?= $ap['id']?>">Voir</a>

        <?php
            }
        ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
        

</body>
</html>