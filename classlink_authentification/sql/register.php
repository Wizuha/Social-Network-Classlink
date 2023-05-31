<?php
require '../../classlink_app/inc/pdo.php';

$json = file_get_contents('php://input');


$data = json_decode($json, true);
$username = $data["username"];
$birth_date = $data ['birth_date'];
$password = $data["password"];
$first_name = $data["firstname"];
$last_name = $data['lastname'];
$mail = $data["mail"];
$gender = $data["gender"];
$question = $data["question"];
$response = $data["answer"];
$data = array(
    'username' => $username
);
$json = json_encode($data);


$requete0 = $auth_pdo->prepare('
SELECT * FROM users 
WHERE username = :username
');

$requete0->execute([
    ':username' => $username
 ]);

$result = $requete0->fetch(PDO::FETCH_ASSOC);
if(!$result){
        $request_register = $auth_pdo->prepare("
        INSERT INTO users (username,password,first_name,last_name,mail,birth_date,gender,question,response)
        VALUES (:username,:password,:first_name,:last_name,:mail,:birth_date,:gender,:question,:response);
        ");

        $request_register->execute([
            ':username'=>$username,
            ':password'=>$password,
            ':first_name'=>$first_name,
            ':last_name'=>$last_name,
            ':mail'=>$mail,
            ':birth_date'=>$birth_date,
            ':gender'=>$gender,
            ':question'=>$question,
            ':response'=>$response
        ]);
        $last_insert_id = $auth_pdo -> lastInsertId();

        $request_register = $auth_pdo->prepare("
        INSERT INTO token (user_id, token) VALUES (:user_id, :token)
        ");

            $request_register->execute([
                ':user_id' => $last_insert_id,
                ':token' => 'null'
            ]);
            
            $data = [
                'statut' => "Succès",
                'message' => 'Inscription réussite',
                'id' => $last_insert_id
            ];

            $json = json_encode($data);
            echo $json;
            exit();
        } 
        else{
            $data = array(
                'statut' => "Erreur",
                'message' => 'utilisateur déjà existant'
                
            );
            $json = json_encode($data);
            echo $json;
            exit();
        }
