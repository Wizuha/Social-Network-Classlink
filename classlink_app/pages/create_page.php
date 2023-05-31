<?php
session_start();
require '../inc/pdo.php';
require '../inc/functions/token_functions.php';
require '../../vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

$method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
$title = "Créer une page";

$path_img = 'http://localhost/SocialNetwork-Fullstack-Project/classlink_app/profiles/uploads/';

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
        $gender = $profile_data['gender'];
        $mail = $profile_data['mail'];
        $pp_image = $profile_data['pp_image'];
    } else {
        echo'erreur';
    }
}

if($method == 'POST'){
    $name_page = filter_input(INPUT_POST,"name_page");
    $description = filter_input(INPUT_POST,"description");
    $banner_image = filter_input(INPUT_POST,"banner_image");
    if(!$banner_image){
        $banner_image = 'default_banner.jpg';
    }

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
            $creator_profile_id = $_SESSION['id'];
            $create_page_request = $app_pdo -> prepare('
            INSERT INTO pages (name,description, banner_image, creator_profile_id)
            VALUES (:name_page, :description, :banner_image, :creator_profile_id);
            ');
            $create_page_request->execute([
                ':creator_profile_id' => $creator_profile_id,
                ':name_page' => $name_page,
                ':description' => $description,
                ':banner_image' => $banner_image
            ]);
            
            $last_insert_id = $app_pdo -> lastInsertId();

            if (isset($_POST['submit']) && isset($_FILES['banner_image'])) {
                $img_name = $_FILES['banner_image']['name'];
                $img_size = $_FILES['banner_image']['size'];
                $tmp_name = $_FILES['banner_image']['tmp_name'];
                $error = $_FILES['banner_image']['error'];
                $img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
                $img_ex_lc = strtolower($img_ex);
            
                $new_img_name = uniqid("IMG-", true) . '.' . $img_ex_lc;
                $img_upload_path = '../profiles/uploads/' . $new_img_name;
            
                if (move_uploaded_file($tmp_name, $img_upload_path)) {
                    $add_image = $app_pdo->prepare("UPDATE pages SET banner_image = :image WHERE id = :id");
                    $add_image->execute([
                        ":image" => $new_img_name,
                        ":id" => $last_insert_id
                    ]);
                }}

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
        }else{
            $error = true;
        }
    }else{
        $empty = true;
    }
}

$logo = '../../assets/img/white_logo.svg';
$header_pp_image = $path_img . $pp_image;
$tv = '../../assets/img/tv-header.svg';
$bell = '../../assets/img/bell.svg';
$messages = '../../assets/img/messages.svg';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/create_group.css">
    <link rel="stylesheet" href="../../assets/css/header.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
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
                <form method="POST" enctype="multipart/form-data">
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

                        <div class="submit"><input class="input" type="submit" name= "submit" value = "Créer une page"></div>
                    </div>
                </form>
                <div class="planet"><img src="../../assets/img/create_groups_planet.svg" alt=""></div>
            </div>
        </div>
    </main>
    <script src="../../assets/js/notifications.js"></script>
    <script>
    let inputBox = document.querySelector(".input-box"),
        search = document.querySelector(".search"),
        closeIcon = document.querySelector(".close-icon")

    search.addEventListener("click", () => inputBox.classList.add("open"))
    closeIcon.addEventListener("click", () => inputBox.classList.remove("open"))
    $(document).ready(function(){
          
          function fetchData(){
            let value = $("#input").val(); // Permet de récupérer la valeur de l'input
            console.log(value)
            if (value == '') { // if la valeur de value est null
               $('#dropdown').css('display', 'none');
            }
            $.post("../inc/tpl/index.php", 
                  {
                    'value' : value
                  },
                  function(data, status){
                      if (data != "not found") {
                        $('#dropdown').css('display', 'block');
                        $('#dropdown').html(data);
                      }
                  });
          }
          $('#input').on('input', fetchData);
          $("body").on('click', () => {
            $('#dropdown').css('display', 'none');
          });
          $('#input').on('click', fetchData);
      });
    </script>
</body>
</html>