<?php
    require '../../classlink_app/inc/pdo.php';

    $json = file_get_contents('php://input'); // récupère les données brutes envoyées avec la requete POST et les assignent à $json
    $data = json_decode($json, true); // décode la chaine $json en un tableau associatif et l'assigne à $data ; l'argument 'true' specifie qu'on renvoie un tableau plutot qu'un objet
    $username = $data['username'];

    $existing_user_request = $auth_pdo->prepare("
    SELECT * FROM users WHERE username = :username
    ");

    $existing_user_request->execute([
        ":username" => $username
    ]);

    $existing_user_result = $existing_user_request->fetch(PDO::FETCH_ASSOC);

    if ($existing_user_result){
        $id = $existing_user_result['id'];
        $question_request = $auth_pdo->prepare("
        SELECT question, response FROM users WHERE id = :id
        ");

        $question_request->execute([
            ":id" => $id
        ]);

        $question_request_result = $question_request->fetch(PDO::FETCH_ASSOC);

        $data = array(
            'statut' => 'Succès', 
            'id' => $id,
            'question' => $question_request_result['question'],
            'response' => $question_request_result['response']
        );
        
        $json = json_encode($data);
        echo $json;
        exit();
        
    }else{
        $data = array(
            'statut' => 'Erreur',
            'message' => 'Utilisateur inexistant'
        );

        $json = json_encode($data);
        echo $json;
        exit();
    }