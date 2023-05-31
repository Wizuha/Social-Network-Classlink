<?php
    use GuzzleHttp\Client;
    use GuzzleHttp\RequestOptions;
    require '../inc/pdo.php';
    require '../../vendor/autoload.php';
    session_start();

    $method = filter_input(INPUT_SERVER, "REQUEST_METHOD");
    $submit = filter_input(INPUT_POST, "submit");

    $username = trim(filter_input(INPUT_POST, "username", FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    $security_response_input = trim(filter_input(INPUT_POST, "security-answer", FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    $new_password = trim(filter_input(INPUT_POST, "new-password"));
    $confirm_new_password = trim(filter_input(INPUT_POST, "confirm-new-password"));

    $error = "";
    $existing_user;

    if ($method == "POST") {
        $client = new \GuzzleHttp\Client();
    
        if ($username){
            $_SESSION['username'] = $username;
            $data = [
                "username" => $username
            ];

            $json = json_encode($data);

            $response = $client->post('http://localhost/SocialNetwork-Fullstack-Project/classlink_authentification/sql/forgot_password.php', [     
                'body' => $json
            ]);

            $data = json_decode($response->getBody(), true);
            
            if(isset($data)){
                if($data['statut'] == 'Succès'){
                    $_SESSION['id'] = $data['id'];
                    $_SESSION['existing_user'] = true;
                    $_SESSION['security_question'] = $data['question'];
                    $_SESSION['security_response'] = $data['response'];

                }elseif($data['statut'] == 'Erreur'){
                    $error = "Utilisateur inexistant.";
                }
            }
        }

        if ($security_response_input){
            if ($_SESSION['security_response'] != $security_response_input){
                $error = "Réponse incorrecte";
            }
        }

        if ($new_password && $confirm_new_password){

            if ($new_password == $confirm_new_password){
                $new_password = password_hash(trim($new_password), PASSWORD_DEFAULT);
                $client = new \GuzzleHttp\Client();
                $_SESSION['new_password'] = $new_password;

                $data = array(
                    "id" => $_SESSION['id'],
                    "new_password" => $_SESSION['new_password']
                    // "new_password" => $new_password
                );

                $json = json_encode($data);

                $response = $client->post('http://localhost/SocialNetwork-Fullstack-Project/classlink_authentification/sql/new_password.php', [     
                    'body' => $json
                ]);

                $result = json_decode($response -> getBody(), true);

                if(isset($result)){
                    if($result['statut'] == 'Succès'){
                        session_destroy();
                        header('Location: ./login.php');
    
                    }elseif($result['statut'] == 'Erreur'){
                        $error = $result['message'];
                    }
                }

            }else{
                $error = 'Les mots de passe ne correspondent pas. Veuillez réessayer.';
            }
        }
    }


    // ----- PREMIERE PARTIE DU FORMULAIRE -----

    if ($method == 'GET'|| $error == "Utilisateur inexistant."): ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="../../assets/css/forgot_password.css" rel="stylesheet">
        <title>ClassLink - Mot de passe oublié</title>
    </head>
    <body>

        <div class="container">
            <a href="../../classlink_app/connections/landing_page.html"><img class="logo" src="../../assets/img/logo.svg" alt="logo"></a>
            <div class='background-right'></div>
            <img class="planet" src="../../assets/img/planet.svg" alt="planet_stars">

            <h2>Mot de passe oublié</h2>

            <div class="forgot-password-form">
                <?php if ($error) :?>
                    <p class="error"><?= $error ?></p>
                <?php endif ?>
                <form method="POST">
                    <label class="username-label" for="username">Identifiant : </label>
                    <input class="username-input" type="text" id="username" name="username" placeholder="Identifiant" required>
                    
                    <div class="button-container">
                        <input class="button" type="submit" name="submit" value="Suivant">
                        <a href="./login.php"><button class="cancel-button" type="button">Annuler</button></a>
                    </div>

                </form>
            </div>
        </div>

    </body>
    </html>


    <!-- DEUXIEME PARTIE DU FORMULAIRE -->

    <?php elseif ($method == "POST" && $submit == 'Suivant' && isset($_SESSION['existing_user']) || $error == "Réponse incorrecte") :

        if ($_SESSION['security_question']) {
            switch ($_SESSION['security_question']) {
                case 'first-pet-name':
                    $_SESSION['security_question'] = 'Quel était le nom de votre 1ère animal de compagnie ?';
                    break;
                case 'mother-birth-place':
                    $_SESSION['security_question'] = 'Quel est le lieux de naissance de votre mère.';
                    break;
                case 'first-school-name':
                    $_SESSION['security_question'] = 'Quel est le  nom de votre première école.';
                    break;
                case 'dream-work':
                    $_SESSION['security_question'] = 'Quel est le métier de vos rève ?';
                    break;
                case 'first-love-name':
                    $_SESSION['security_question'] = 'Quel est le nom de votre premier amour ?';
                    break;
            }
        }
    

        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="../../assets/css/forgot_password.css" rel="stylesheet">
            <title>Mot de passe oublié</title>
        </head>
        <body>

            <div class="container">
                <a class="logo" href="./landing_page.html"><img src="../../assets/img/logo.svg" alt="Logo de ClassLink" class="logo"></a>
                <div class='background-right'></div>
                <img class="planet" src="../../assets/img/planet.svg" alt="planet_stars">

                <h2>Mot de passe oublié</h2>

                <div class="security-question-form">
                    <?php if ($error) :?>
                        <p class="error"><?= $error ?></p>
                    <?php endif ?>
                    <form method="POST">
                        <div class="form-block">
                            <div class="block-title">
                                <p>Question de sécurité :</p>
                            </div>
                            <div class="response">
                                <p class="question "> <?= $_SESSION['security_question'] ?></p>
                            </div>
                        </div>

                        <div class="form-block">
                            <label for="security-answer" class="block-title">Réponse :</label>
                            <input id="security-answer" class="security-input" type="text" name="security-answer" placeholder="Réponse" required>
                        </div>

                        <div class="button-container">
                            <input class="button" type="submit" name="submit" value="Confirmer">
                            <a href="./login.php"><button class="cancel-button" type="button">Annuler</button></a>
                        </div>

                    </form>
                </div>
            </div>

        </body>
        </html>


    <!-- TROISIEME PARTIE DU FORMULAIRE -->

    <?php elseif ($method == "POST" && $submit == 'Confirmer' && $_SESSION['security_response'] == $security_response_input || $error == "Les mots de passe ne correspondent pas. Veuillez réessayer.") : 

        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="../../assets/css/forgot_password.css" rel="stylesheet">
            <title>ClassLink - Nouveau Mot de passe</title>
        </head>
        <body>
            
            <div class="container">
                <a class="logo" href="./landing_page.html"><img src="../../assets/img/logo.svg" alt="Logo de ClassLink" class="logo"></a>
                <div class='background-right'></div>
                <img class="planet" src="../../assets/img/planet.svg" alt="planet_stars">

                <h2>Mot de passe oublié</h2>

                <div class="new-password-form">
                    <?php if ($error) :?>
                        <p class="error"><?= $error ?></p>
                    <?php endif ?>
                    <form method="POST">
                        <div class="form-block">
                            <label for="new-password-label" class="new-password-label">Nouveau mot de passe :</label>
                            <input class="new-password-input" type="password" id="new-password" name="new-password" placeholder="Nouveau mot de passe" required>
                        </div>

                        <div class="form-block">
                            <label for="new-password-label" class="new-password-label">Confirmation du mot de passe :</label>
                            <input class="new-password-input" type="password" id="confirm-new-password" name="confirm-new-password" placeholder="Confirmer le mot de passe" required>
                        </div>

                        <div class="button-container">
                            <input class="button" type="submit" name="submit" value="Confirmer">
                            <a href="./login.php"><button class="cancel-button" type="button">Annuler</button></a>
                        </div>
                    </form>
                </div>
            </div>

        </body>
        </html>

    <?php endif; ?>