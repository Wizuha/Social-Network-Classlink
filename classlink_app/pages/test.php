<?php
session_start();
require '../inc/pdo.php';
require '../inc/functions/token_functions.php';


$title = "Créer une page";

if(isset($_SESSION['token'])){
    $check = token_check($_SESSION["token"], $auth_pdo);
    if($check == 'false'){
        header('Location: ../connections/login.php');
        exit();
    } elseif($_SESSION['profile_status'] == 'Inactif') {
        header('Location: ../profiles/settings.php');
        exit();        
    }
}elseif(!isset($_SESSION['token'])){
    header('Location: ../connections/login.php');
    exit();
}


if(isset($_SESSION['id'])) {
$recuperation_data_profiles = $app_pdo -> prepare('
    SELECT last_name, first_name, birth_date, gender, mail, pp_image FROM profiles
    WHERE id = :id;
');
$recuperation_data_profiles->execute([
    ":id" => $_SESSION['id']
]);

$profile_data = $recuperation_data_profiles ->fetch(PDO::FETCH_ASSOC);

if($profile_data){
    $last_name = $profile_data['last_name'];
    $first_name = $profile_data['first_name'];
    $birth_date = $profile_data['birth_date'];
    if ($birth_date == null) {
        $age = 'Non renseignée';
    } else {
        $current_date = new DateTime();
        $birth_date = new DateTime($birth_date);
        $diff = $current_date->diff($birth_date);
        $age = $diff->y;
    }
    $gender = $profile_data['gender'];
    switch ($gender) {
        case 'male':
            $gender = 'Homme';
            break;
        case 'female':
            $gender = 'Femme';
            break;
        case 'other':
            $gender =  'Autre';
            break;
        default:
            $gender = 'Non renseigné';
            break;
    }
    $mail = $profile_data['mail'];
    $pp_image = $profile_data['pp_image'];
} else {
    echo'erreur';
}

}
$method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');

if($method == 'POST'){
    $name_page = filter_input(INPUT_POST,"name_page");
    $description = filter_input(INPUT_POST,"description");
    $banner_image = filter_input(INPUT_POST,"banner_image");

    if ($name_page && $description){
        $verify_existing_page_request = $app_pdo->prepare("
            SELECT * FROM pages 
            WHERE name = :name_page;
        ");
        $verify_existing_page_request->execute([
            ":name_page" => $name_page
        ]);

        $verify_existing_pages = $verify_existing_page_request ->fetch(PDO::FETCH_ASSOC);

        if(!$verify_existing_pages){
            if(isset($_SESSION['id'])){
                $request_pages_creator_profile_id = $app_pdo -> prepare('
                SELECT * FROM profiles WHERE id = :id
                ');
                $request_pages_creator_profile_id->execute([
                ':id' => $_SESSION['id']
                ]);
                }
        
            $creator_profile_id = $_SESSION['id'];
            $create_page_request = $app_pdo -> prepare('
            INSERT INTO pages (name,description, banner_image,creator_profile_id)
            VALUES (:name_page, :description, :banner_image,:creator_profile_id);
            ');
            $create_page_request->execute([
                ':creator_profile_id' => $creator_profile_id,
                ':name_page' => $name_page,
                ':description' => $description,
                ':banner_image' => $banner_image
            ]);

            $last_insert_id = $app_pdo -> lastInsertId();

            $create_page_request = $app_pdo->prepare('
            INSERT INTO subscribers_page (page_id, profile_id, admin)
            VALUES (:last_insert_id, :id, :admin);
            ');

            $create_page_request->execute([
                ":last_insert_id" => $last_insert_id,
                ":id" => $_SESSION['id'],
                ":admin" => 1
            ]);
            
            $create = true;
            }
            else{
                $error = true;
            }
    }else{
        $empty = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/create_group.css">
    <link rel="stylesheet" href="../../assets/css/header.css">
    <title>Pages</title>
</head>
<body>
    <?php include '../inc/tpl/header.php'; ?>
    <main>
        <div class="left-side">
            <div class="informations">
                <div class="top">
                    <div class="img"><img src="../../assets/img/default_pp.jpg" alt=""></div>
                    <div class="name">
                        <p><?= $last_name." ". $first_name ?></p>
                    </div>
                    <div class="separator"></div>
                </div>
                <div class="mid">
                    <div class="personnal-info">
                        <div><p>Anniversaire <span>: <?php echo $profile_data['birth_date'] ?></span></p></div>
                        <div><p>Genre <span>: <?php echo $profile_data['gender'] ?> </span></p></div>
                        <div><p>E-mail <span>: <?php echo $profile_data['mail'] ?></span></p></div>                        
                    </div>
                </div>
                <div class="bottom">
                    <div class="btn2"><a href=""><button>Modifier</button></a></div> <!-- Rajouter le lien vers modifier profil--> 
                </div>
            </div>
            <div class="btn">
                <a href="../connections/logout.php"><button>Déconnexion</button></a> <!-- Rajouter le lien vers logout-->  
            </div>
        </div>
        <div class="create">
            <div class="header">
                <div><h2>Créer une page</h2></div>
            </div>
            <div class="main">
                <form method="POST">
                    <div>
                        <label for="name_page">Nom de la page</label>
                        <input id="name" type="text" name = "name_page" required>
                    </div>
                    <div>
                        <label for="description">Sujet de la page</label>
                        <input id="description" type="text" name = "description" required>
                    </div>
                    <div>
                        <label for="image">Photo de bannière de la page</label>
                        <input type="file" id="fileInput" name = "banner_image" class="custom-file-input">
                        <label for="fileInput" class="custom-file-label">Choisir un fichier</label>
                    

                        <?php if(isset($create)) : ?>
                            <p>Page bien créé.</p>
                        <?php elseif (isset($error)) : ?>
                            <p>Ce nom de page est déjà utilisé. Veuillez réessayer.</p>
                        <?php elseif (isset($empty)) : ?>
                            <p>Veuillez remplir les champs "Nom" et "Sujet".</p>
                        <?php endif; ?>

                        <div class="submit"><input class="input" type="submit" value = "Créer une page"></div>
                    </div>
                </form>
                <div class="planet"><img src="../../assets/img/create_groups_planet.svg" alt=""></div>
            </div>
        </div>
    </main>
    <script src="../../assets/js/notifications.js"></script>
    <script>
        const headerPp = document.getElementById('header-pp');
        headerPp.addEventListener('click', () => {
            window.location.href = 'http://localhost/SocialNetwork-Fullstack-Project/classlink_app/profiles/profile.php';
        })
    </script>
</body>
</html>