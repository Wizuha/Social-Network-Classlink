<?php
    require '../../classlink_app/inc/pdo.php';

    $json = file_get_contents('php://input'); // récupère les données brutes envoyées avec la requete POST et les assignent à $json
    $data = json_decode($json, true);

    $id = $data['id'];
    $new_password = $data['new_password'];

    $new_password_request = $auth_pdo -> prepare("
    UPDATE users SET password = :new_password WHERE id = :id;
    ");

    $new_password_request -> execute([
        ":id" => $id,
        ":new_password" => $new_password
    ]);

    $new_password_result = $new_password_request -> fetch(PDO::FETCH_ASSOC);

    if(isset($new_password_result)){
        $new_password_data = array(
            'statut' => 'Succès', 
        );
        
        $new_password_json = json_encode($new_password_data);
        echo $new_password_json;
        exit();
    }else{
        $new_password_data = array(
            'statut' => 'Erreur',
            'message' => 'Une erreur est survenue.'
        );

        $new_password_json = json_encode($new_password_data);
        echo $new_password_json;
        exit();
    }