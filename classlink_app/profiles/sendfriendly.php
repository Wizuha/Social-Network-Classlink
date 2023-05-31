<?php
session_start();
require '../inc/pdo.php';
require '../../vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

$method = filter_input(INPUT_SERVER, "REQUEST_METHOD");

$_SESSION['id'] = 78;
$toto = 56;
$client = new \GuzzleHttp\Client();
if(isset($_SESSION)) {

$requete = $app_pdo->prepare("
insert into awaiting_relations (seeker_profile_id,receiver_profile_id) values (:id,:toto)

    ");
    $requete->execute([
        ":id" => $_SESSION['id'],
        ":toto" => $toto
    ]);
    $result = $requete->fetch(PDO::FETCH_ASSOC);
    if($result){
    $user_profile_id = $result['user_profile_id'];
    $friend = $result['friend_profile_id'];
    } else {
        echo 'erreur';
    }
    var_dump($result);

    echo $user_profile_id;
    echo $friend;

}

?>