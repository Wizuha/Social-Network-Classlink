<?php
    session_start();
    require '../../vendor/autoload.php';
    require '../../classlink_app/inc/pdo.php';
    use GuzzleHttp\Client;
    use GuzzleHttp\RequestOptions;

    $error = ""; // Préparation d'une variable qui contiendra le message d'erreur afficher.
    $method = filter_input(INPUT_SERVER, "REQUEST_METHOD");
    $submit = filter_input(INPUT_POST, "submit"); // Vérification du contenu de l'input submit
    // Utilisation de trim pour supprimer les espaces en surplus
    $firstname = trim(filter_input(INPUT_POST, "firstname", FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $lastname = trim(filter_input(INPUT_POST, "lastname", FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $mail = trim(filter_input(INPUT_POST, "mail", FILTER_SANITIZE_EMAIL, FILTER_VALIDATE_EMAIL));
    $birth_date = filter_input(INPUT_POST, "birth-date");
    $gender = filter_input(INPUT_POST, "gender");
    $username = trim(filter_input(INPUT_POST, "username", FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $password = trim(filter_input(INPUT_POST, "password"));
    $confirm_password = trim(filter_input(INPUT_POST, "confirm-password"));
    $security_question = filter_input(INPUT_POST, "security-question");
    $security_answer = trim(filter_input(INPUT_POST, "security-answer"));

    if ($method == 'GET' || ($method == 'POST' && ($password != $confirm_password))): /* Cette condition envoie la première partie du formulaire
        , si le formulaire n'a pas été soumis ou que les 2 mots de passe ne correspondent pas. */
        if ($password != $confirm_password) {
            $error = 'Les mots de passe ne correspondent pas.';
        }
        if (isset($_SESSION['error']) == true) {
            $error = 'Utilisateur déjà existant.';
        }
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="../../assets/css/register.css" rel="stylesheet">
            <title>ClassLink - Inscription</title>
        </head>
        <body>
            <div class="container">
                
                <a href="./landing_page.html"><img src="../../assets/img/logo.svg" alt="Logo de ClassLink" class="logo"></a>

                <div class="white-form-moon">
                    <div class="white-block">

                    </div>
                    
                    <div class="moon-form">
                        <img src="../../assets/img/moon.svg" alt="Image représentant une lune entouré d'étoile" id="moon">

                        <div class="form-block">
                            <h2 class="page-title">S'inscrire</h2>
                            <?php if ($error):
                                if (isset($_SESSION['error']) == true) {
                                    $_SESSION['error'] = false;
                                    unset($_SESSION['error']);    
                                } ?>
                                <p class="error"><?= $error ?></p>
                            <?php endif; ?>
                            
                            <form method="POST" class="inscription-form">
                                <div class="full-name">
                                    <div class="input-block">
                                        <label for="firstname" class="hidden">Prénom: </label>
                                        <input type="text" id="firstname" name="firstname" class="input" placeholder="Prénom (Facultatif)">
                                    </div>

                                    <div class="input-block">
                                        <label for="lastname" class="hidden">Nom: </label>
                                        <input type="text" id="lastname" name="lastname" class="input" placeholder="Nom (Facultatif)">
                                    </div>
                                </div>
                                

                                <div class="input-block">
                                    <label for="mail" class="hidden">Adresse Email: </label>
                                    <input type="email" id="mail" name="mail" class="input" placeholder="Adresse Email (Facultatif)">
                                </div>
                                

                                <div class="birth-date-gender">
                                    <div class="input-block">
                                        <label for="birth-date" class="hidden">Date de naissance: </label>
                                        <input type="date" name="birth-date" id="birth-date" class="input" placeholder="Date de naissance">
                                    </div>

                                    <div class="input-block">
                                        <label for="gender" class="hidden">Genre: </label>
                                        <select name="gender" id="gender" class="input" placeholder="Genre (Facultatif)">
                                            <option value="" selected disabled id="default" hidden>Genre (Facultatif)</option>
                                            <option value="male">Homme</option>
                                            <option value="female">Femme</option>
                                            <option value="others">Autres</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="input-block">
                                    <label for="username" class="hidden">Identifiant: </label>
                                    <input type="text" id="username" name="username" placeholder="Identifiant" required class="input">
                                </div>

                                

                                <div class="password">
                                    <div class="input-block">
                                        <label for="password" class="hidden">Mot de passe: </label>
                                        <input type="password" id="password" name="password" placeholder="Mot de passe" required class="input">
                                    </div>

                                    <div class="input-block">
                                        <label for="confirm-password" class="hidden">Confirmez mot de passe: </label>
                                        <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirmer le mot de passe" required class="input">
                                    </div>
                                </div>

                                <input type="submit" value="Suivant" name="submit" class="button">
                            </form>

                            <p class="paragraph">Déjà inscrit ? <a href="./login.php">Connectez-vous.</a></p>
                        </div>

                    </div>
                </div>
            </div>
            
        </body>
        </html><?php elseif($method =="POST" && $submit == 'Suivant' && ($username && $password && $confirm_password && ($password == $confirm_password))):
        /* La condition juste ci-dessus dechlenche la 2è partie du formulaire si l'utilisateur appuie sur le bouton dont la valeur  est Suivant, 
        que les champs username, mot de passe et confirmez mot de passe sont remplis et que mot de passe et confirmez mot  de passe se corresondent*/
            if ($username && $password) {
                $username = trim($username);
                $password = password_hash(trim($password), PASSWORD_DEFAULT);
                $_SESSION['username'] = $username;
                $_SESSION['password'] = $password;
                $_SESSION['firstname'] = $firstname;
                $_SESSION['lastname'] = $lastname;
                $_SESSION['mail'] = $mail;
                $_SESSION['birth_date'] = $birth_date;
                $_SESSION['gender'] = $gender;

            } ?>
            <!DOCTYPE html>
            <html lang="fr">
            <head>
                <meta charset="UTF-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <link href="../../assets/css/register.css" rel="stylesheet">
                <title>ClassLink - Inscription</title>
            </head>
            <body>
                <div class="container">
                    <a href="./landing_page.html"><img src="../../assets/img/logo.svg" alt="Logo de ClassLink" class="logo"></a>

                    <div class="white-block">

                    </div>

                    <img src="../../assets/img/Moon.svg" alt="Image représentant une lune entouré d'étoile" id="moon">

                    <div class="security-form-block">
                        <form method="POST">
                            <h2 class="page-title">Sécurité</h2>
                            <div class="input-block">
                                <label for="security-question" class="hidden">Question de sécurité: </label>
                                <select name="security-question" id="security-question" class="security-input">
                                    <option value="" selected disabled hidden id="default">Selectionner une question de sécurité.</option>
                                    <option value="first-pet-name">Quel était le nom de votre 1ère animal de compagnie ?</option>
                                    <option value="mother-birth-place">Quel est le lieux de naissance de votre mère.</option>
                                    <option value="first-school-name">Quel est le  nom de votre première école.</option>
                                    <option value="dream-work">Quel est le métier de vos rève ?</option>
                                    <option value="first-love-name">Quel est le nom de votre première amour ?</option>
                                </select>
                            </div>

                            <div class="input-block">
                                <label for="security-answer" class="hidden">Réponse</label>
                                <input type="text" id="security-answer" name="security-answer" placeholder="Réponse" class="security-input">
                            </div>

                            <input type="submit" name="submit" value="Inscription" class="button">
                        </form>
                    </div>
                </div>
                
            </body>
            </html><?php elseif($method =="POST" && $submit == 'Inscription'):
                if ($security_answer && $security_question) {
                    $client = new \GuzzleHttp\Client();
                    $security_question = trim(filter_input(INPUT_POST, 'security-question'));
                    $security_answer = trim(filter_input(INPUT_POST, 'security-answer', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
                    $data = array(
                        'username' => $_SESSION['username'],
                        'password' => $_SESSION['password'],
                        'firstname' => $_SESSION['firstname'],
                        'lastname' => $_SESSION['lastname'],
                        'mail' => $_SESSION['mail'],
                        'birth_date' => $_SESSION['birth_date'],
                        'gender' => $_SESSION['gender'],
                        'question' => $security_question,
                        'answer' => $security_answer,
                    );
                    $json = json_encode($data);
                    // $response = $client->post('http://localhost:8888/SocialNetwork-Fullstack-Project/classlink_authentification/sql/register.php', [
                    $response = $client->post('http://localhost/SocialNetwork-Fullstack-Project/classlink_authentification/sql/register.php', [
                        'body' => $json
                    ]);
                    $data = json_decode($response->getBody(), true);
                    if($data !== null && isset($data['statut']) && $data['statut'] === 'Succès' ){
                        $id = $data['id'];
                        $pp_default = 'default_pp.jpg';
                        $banner_default = 'default_banner.png';
                        $status = 'Actif';
                        $requete_recuperation_profile = $app_pdo->prepare("
                        INSERT INTO profiles (id, birth_date,first_name,last_name,mail,gender,pp_image,banner_image,username,status)
                        VALUES (:id,:birth_date,:first_name,:last_name,:mail,:gender,:pp_image,:banner_image,:username,:status);
                        ");

                        $requete_recuperation_profile->execute([
                        ":id" => $id ,
                        ":birth_date"=> $_SESSION['birth_date'],
                        ":first_name"=> $_SESSION['firstname'],
                        ":last_name"=> $_SESSION['lastname'],
                        ":mail"=> $_SESSION['mail'],
                        ":gender"=> $_SESSION['gender'],
                        ':pp_image' => $pp_default,
                        ':banner_image' => $banner_default,
                        ':username' => $_SESSION['username'],
                        ':status' => $status
                        ]);
                        header('Location: ../../classlink_app/connections/login.php');
                        var_dump($json);
                    }

                    elseif($data["statut"] == 'Erreur'){
                        $_SESSION['error'] = true ;
                        header('Location: ../../classlink_app/connections/register.php');
                    }
                } elseif ($security_question == false) {
                    $error = "Veuillez selectionnez une question de sécurité.";
                } elseif ($security_answer == false) {
                    $error = "Veuillez répondre à la question de sécurité";
                }?>
                <?php endif;
?>
