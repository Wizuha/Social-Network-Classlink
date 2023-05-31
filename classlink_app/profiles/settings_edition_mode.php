<?php
    session_start();
    require '../inc/pdo.php';
    require '../inc/functions/token_functions.php';
    require '../../vendor/autoload.php';
    use GuzzleHttp\Client;
    use GuzzleHttp\RequestOptions;
    
    if(isset($_SESSION['token'])){
        $check = token_check($_SESSION["token"], $auth_pdo);
        if($check == 'false'){
            header('Location: ../connections/login.php');
            exit();
        } elseif($_SESSION['profile_status'] == 'Inactif') {
            header('Location: ./settings.php');
            exit();        
        }
    }elseif(!isset($_SESSION['token'])){
        header('Location: ../connections/login.php');
        exit();
    }
    
    $method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
    $firstname = trim(filter_input(INPUT_POST, "firstname", FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $lastname = trim(filter_input(INPUT_POST, "lastname", FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $mail = trim(filter_input(INPUT_POST, "mail", FILTER_SANITIZE_EMAIL, FILTER_VALIDATE_EMAIL));
    $birth_date = filter_input(INPUT_POST, "birth-date");
    $gender = filter_input(INPUT_POST, "gender");
    $username = trim(filter_input(INPUT_POST, "username", FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $password = trim(filter_input(INPUT_POST, "password"));
    $confirm_password = trim(filter_input(INPUT_POST, "check_password"));

    if ($method == 'POST' && (!$username || !$password || !$confirm_password)) {
        $error = "Veuillez remplir les champs non facultatif.";
    } elseif ($method == 'POST' && ($password != $confirm_password)) {
        $error =  "Les mots de passe ne correspondent pas.";
    } elseif ($method == 'POST') {
        $data = array(
            'id' => $_SESSION['id'],
            'username' => $username,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'mail' => $mail,
            'birth_date' => $birth_date,
            'gender' => $gender,
            'password' => password_hash($password, PASSWORD_DEFAULT)
        );
        $client = new \GuzzleHttp\Client();
        $json = json_encode($data);
        $response = $client->post('http://localhost/SocialNetwork-Fullstack-Project/classlink_app/profiles/scriptphp/change_settings_controller.php', [
            'body' => $json
        ]);
        $result = json_decode($response->getBody(), true);
        if (isset($result['Statut']) && $result['Statut'] == 'Succès') {
            header('Location: ./settings.php');
            exit();
        } elseif (isset($result['Statut']) && $result['Statut'] == "Erreur") {
            http_response_code(406);
            $error = $result['Message'];
        } else {
            http_response_code(404);
            $error = $result['Message']; 
        }
    }
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/settings_edition_mode.css">
    <title>Profile - Changement des paramètres</title>
</head>
<body>
    <header>

    </header>
    <main>
        <div class="container">
            <div class="form">
                <div class="header"><div><h3>Informations du profil</h3></div></div>
                <div class='containerform'>
                    <?php if (isset($error)): ?>
                        <p><?= $error ?></p>
                    <?php endif; ?>
                    <form method="POST">
                        <div>
                            <label for="lastname">Nom :</label>
                            <input type="text" name="lastname" id="lastname"/>
                        </div>
                        <div>
                            <label for="firstname">Prénom :</label>
                            <input type="text" name="firstname" id="firstname"/>
                        </div>
                        <div>
                            <label for="birth-date">Date de naissance :</label>
                            <input type="date" name="birth-date" id="birth-date"/>
                        </div>
                        <div>
                            <label for="gender">Genre: (facultatif)</label>
                            <select name="gender" id="gender">
                                <option value="" hidden disabled selected>Genre</option>
                                <option value="male">Homme</option>
                                <option value="female">Femme</option>
                                <option value="other">Autre</option>
                            </select>
                        </div>
                        <div>   
                            <label for="mail">Email : (facultatif)</label>
                            <input type="email" name="mail" id="mail"/>
                        </div>
                        <div>
                            <label for="username">Identifiant :</label>
                            <input type="text" name="username" id="username"/>
                        </div>
                        <div>     
                            <label for="password">Mot de passe :</label>
                            <input type="password" name="password" id="password"/>
                        </div>
                        <div>
                            <label for="check_password" >Confirmer le mot de passe :</label>
                            <input type="password" name="check_password" id="check_password"/>
                        </div>

                        <div class="buttons"> <input class='submit' type="submit" value='Enregistrer les modifications'></div>
                    </form>
                    <button class='retour' id="back-button">Retour</button>
                </div>
            </div>
            <div class="planet">
                <div><img src="../../assets/img/planet-stars.svg" alt=""></div>
            </div>
            
        </div>
        
    </main>
    <script>
        const backBtn = document.getElementById('back-button');
        backBtn.addEventListener('click', () => {
            window.history.back();
        })
    </script>
</body>
</html>