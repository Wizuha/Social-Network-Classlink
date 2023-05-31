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
    
    $method = filter_input(INPUT_SERVER, 'METHOD');
    $firstname = trim(filter_input(INPUT_POST, "firstname", FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $lastname = trim(filter_input(INPUT_POST, "lastname", FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $mail = trim(filter_input(INPUT_POST, "mail", FILTER_SANITIZE_EMAIL, FILTER_VALIDATE_EMAIL));
    $birth_date = filter_input(INPUT_POST, "birth-date");
    $gender = filter_input(INPUT_POST, "gender");
    $username = trim(filter_input(INPUT_POST, "username", FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $password = trim(filter_input(INPUT_POST, "password"));
    $confirm_password = trim(filter_input(INPUT_POST, "confirm-password"));

    if ($method == 'POST' && !$username && !$password && !$confirm_password) {
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
        print_r($data);
        $client = new \GuzzleHttp\Client();
        $json = json_encode($data);
        $response = $client->post('http://localhost/SocialNetwork-Fullstack-Project/classlink_app/profiles/scriptphp/change_settings_controller.php', [
            'body' => $json
        ]);
        $result = json_decode($response->getBody(), true);
        if (isset($result['Statut']) == 'Succès') {
            header('Location: ./settings.php');
            exit();
        } elseif (isset($result['Statut']) == 'Erreur' && $result['Message'] == "Ce nom d'utilisateur est déja utilisé.") {
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
    <title>Profile - Changement des paramètres</title>
</head>
<body>
    <div class="profile-informations">
        <h3>Informations du profil</h3>
        <div class="profile-informations-form">
            <?php if (isset($error)): ?>
                <p><?= $error ?></p>
            <?php endif; ?>
            <form method="POST">
                <div class="input-block">
                    <label for="lastname">Nom: (facultatif)</label>
                    <input type="text" id="lastname" name="lastname">
                </div>

                <div class="input-block">
                    <label for="firstname">Prénom: (facultatif)</label>
                    <input type="text" id="firstname" name="firstname">
                </div>

                <div class="input-block">
                    <label for="birth-date">Date de naissance: (facultative)</label>
                    <input type="date" id="birth-date" name="birth-date">
                </div>

                <div class="input-block">
                    <label for="gender">Genre: (facultatif)</label>
                    <select name="gender" id="gender">
                        <option value="" hidden disabled selected>Genre</option>
                        <option value="male">Homme</option>
                        <option value="female">Femme</option>
                        <option value="other">Autre</option>
                    </select>
                </div>

                <div class="input-block">
                    <label for="mail">Email: (facultatif)</label>
                    <input type="email" id="mail" name="mail">
                </div>

                <div class="input-block">
                    <label for="username">Identifiant: </label>
                    <input type="text" id="username" name="username">
                </div>

                <div class="input-block">
                    <label for="password">Mot de passe: </label>
                    <input type="password" id="password" name="password">
                </div>

                <div class="input-block">
                    <label for="confirm-password">Confirmez le mot de passe: </label>
                    <input type="password" id="confirm-password" name="confirm-password">
                </div>

                <input type="submit" value="Enregistrer les informations">
            </form>
            <button id="back-button" name="back-button" class="button">Retour</button>
        </div>
    </div>

    <script>
        /* Dans ce script on crée un variable qui stockera l'element bouton avec l'id 'back-button', on ajoute ensuite*/
        const backButton = document.getElementById('back-button');
        backButton.addEventListener('click', () => {
            window.history.back();
        })
    </script>
</body>
</html>