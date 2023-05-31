<?php
    require '../../inc/pdo.php';

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $id = $data['id'];
    $new_username = $data['username'];
    $new_password = $data['password'];
    $new_lastname = $data['lastname'];
    $new_firstname = $data['firstname'];
    $new_mail = $data['mail'];
    $new_gender = $data['gender'];

    $account_informations_request = $auth_pdo->prepare('
        SELECT * FROM users
        WHERE id = :id
    ');

    $account_informations_request->execute([
        ':id' => $id
    ]);

    $account_informations_request_result = $account_informations_request->fetch(PDO::FETCH_ASSOC);



    $verify_existing_user_request = $auth_pdo->prepare('
        SELECT * FROM users
        WHERE username = :username
    ');

    $verify_existing_user_request->execute([
        ':username' => $new_username
    ]);

    $verify_existing_user_request_result = $verify_existing_user_request->fetch(PDO::FETCH_ASSOC);

    if ($account_informations_request_result && !$verify_existing_user_request_result ) {
        
        if ($new_username == null) {
            $new_username = $account_informations_request_result['username'];
        }
    
        if ($new_password == null) {
            $new_password = $account_informations_request_result['password'];
        }
    
        if ($new_lastname == null) {
            $new_lastname = $account_informations_request_result['last_name'];
        }
    
        if ($new_firstname == null) {
            $new_firstname = $account_informations_request_result['first_name'];
        }
    
        if ($new_mail == null) {
            $new_mail = $account_informations_request_result['mail'];
        }
    
        if ($new_gender == null) {
            $new_gender = $account_informations_request_result['gender'];
        }
    
        $informations_change_request = $auth_pdo ->prepare('
            UPDATE users
            SET username = :username, password = :password, last_name = :last_name, first_name = :first_name, mail = :mail, gender = :gender
            WHERE id = :id
        ');

        $informations_change_request->execute([
            ':id' => $id,
            ':username' => $new_username,
            ':password' => $new_password,
            ':last_name' => $new_lastname,
            ':first_name' => $new_firstname,
            ':mail' => $new_mail,
            ':gender' => $new_gender
        ]);

        $informations_change_request2 = $app_pdo->prepare('
            UPDATE profiles
            SET username = :username, last_name = :last_name, first_name = :first_name, mail = :mail, gender = :gender
            WHERE id = :id
        ');

        $informations_change_request2->execute([
            ':id' => $id,
            ':username' => $new_username,
            ':last_name' => $new_lastname,
            ':first_name' => $new_firstname,
            ':mail' => $new_mail,
            ':gender' => $new_gender
        ]);
        
        $response = array(
            'Statut' => 'Succès',
            'Message' => "Changement Validés"
        );

        $json = json_encode($response);
        echo $json;
    } elseif ($verify_existing_user_request_result) {
        $response = array(
            'Statut' => 'Erreur',
            'Message' => "Ce nom d'utilisateur est déja utilisé."
        );
        
        $json = json_encode($response);
        echo $json;
    } else {
        $response = array(
            'Statut' => 'Erreur',
            'Message' => "Echec de la requete"
        );
        
        $json = json_encode($response);
        echo $json;
    }
?>