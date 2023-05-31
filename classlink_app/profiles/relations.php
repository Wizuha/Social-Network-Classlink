<?php 
session_start();
require '../inc/pdo.php';
require '../../vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

$method = filter_input(INPUT_SERVER, "REQUEST_METHOD");



$client = new \GuzzleHttp\Client();
if(isset($_SESSION)) {

$requete = $app_pdo->prepare("
SELECT * 
FROM relations
LEFT JOIN profiles ON profiles.id = relations.user_profile_id 
WHERE profiles.id = :id;
    ");
    $requete->execute([
        ":id" => $_SESSION['id']
    ]);
    $result = $requete->fetch(PDO::FETCH_ASSOC);
    if($result){
    $user_profile_id = $result['user_profile_id'];
    $friend = $result['friend_profile_id'];
    } else {
        echo 'erreur';
    }
    

    echo $user_profile_id;
    echo $friend;

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
<div>
    <form method="POST">
        <input type="submit">
    </form>
    <form action="sendfriendly.php" method="POST">
        <input type="submit">
    </form>
          
</div>


</body>
</html>