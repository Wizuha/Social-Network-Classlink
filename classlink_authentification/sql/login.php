<?php
    session_start();
    require '../../classlink_app/inc/pdo.php'; //Besoin du pdo pour se connecter à la bdd
    require '../../classlink_app/inc/functions/token_functions.php'; // Récupère la fonction pour créer un token

    $json = file_get_contents('php://input');

    $data = json_decode($json, true);

    $username = $data["username"];
    $password = $data['password'];
    $requete = $auth_pdo->prepare("
        SELECT * FROM users WHERE username = :username
    ");

    $requete->execute([
        ":username" => $username
    ]);


    $result = $requete->fetch(PDO::FETCH_ASSOC);

    $id = $result['id'];


    if ($result){
        if(password_verify($password, $result["password"])){
            $check_account_status = $app_pdo->prepare("
                SELECT status FROM profiles
                WHERE id = :id
            ");

            $check_account_status->execute([
                ':id' => $id
            ]);

            $check_account_status_result = $check_account_status->fetch(PDO::FETCH_ASSOC);
            
            $token = token();
            $requete_token = $auth_pdo->prepare("
                        UPDATE token SET token = :token WHERE token.user_id = (SELECT id FROM users WHERE username = :username);
                        ");
                        $requete_token->execute([
                            ":token" => $token,
                            ":username" => $username
                        ]);
            $data = array(
                    'statut' => 'Succès',
                    'message' => $token, 
                    'id' => $id,
                    'profile_status' => $check_account_status_result['status']
                );
            $json = json_encode($data);
            echo $json;
            exit();
        }else{
            $data = array(
            'statut' => 'Erreur',
            'message' => 'Identifiants incorrects'
            );
                $json = json_encode($data);
                echo $json;
                exit();
        }
    }else{
        $data = array(
            'statut' => 'Erreur',
            'message' => 'Utilisateur inexistant'
            );
                $json = json_encode($data);
                echo $json;
                exit();
    }