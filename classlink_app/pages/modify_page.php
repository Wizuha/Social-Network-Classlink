<?php
session_start();
require '../inc/pdo.php'; 
require '../inc/functions/token_functions.php';
require '../inc/tpl/data_user.php';

$method = filter_input(INPUT_SERVER, "REQUEST_METHOD");

$page_id = $_GET['id'];
require '../inc/tpl/check_permissions.php';


if($check_permissions_page_result['admin'] == '0'){
    header('Location: http://localhost/SocialNetwork-Fullstack-Project/classlink_app/dashboard.php');
};


if(isset($_SESSION['token'])){
    $check = token_check($_SESSION["token"], $auth_pdo);
    if($check == 'false'){
        header('Location: ./connections/login.php');
    }
}elseif(!isset($_SESSION['token'])){
    header('Location: ./connections/login.php');
}

$verify_existing_page_request = $app_pdo->prepare("
            SELECT * FROM pages 
            WHERE id = :page_id;
        ");
        $verify_existing_page_request->execute([
            ":page_id" => $_SESSION['page_id']
        ]);

$verify_existing_pages = $verify_existing_page_request ->fetch(PDO::FETCH_ASSOC);

$default_name = $verify_existing_pages['name'];
$default_description=$verify_existing_pages['description'];
$default_banner_image=$verify_existing_pages['banner_image'];


$check_admin = $app_pdo->prepare('
        SELECT admin FROM subscribers_page WHERE profile_id = :id
        AND page_id = :page_id
        ');

        $check_admin->execute([
            ":id" => $_SESSION['id'],
            ":page_id" => $page_id
        ]);

        $check_admin_result=$check_admin->fetch(PDO::FETCH_ASSOC);
        if(isset($check_admin_result['admin'])){
            $statut = $check_admin_result['admin'];
        }elseif(!isset($check_admin_result['admin'])){
            $statut = 'visitor';
        }else {
        echo 'error';
    }

    $display_subscribers_request = $app_pdo->prepare('
    SELECT profiles.id, last_name, first_name, admin
    FROM profiles
    LEFT JOIN subscribers_page ON profiles.id = subscribers_page.profile_id
    WHERE subscribers_page.page_id = :page_id
    ');

    $display_subscribers_request->execute([
        ":page_id" => $_SESSION['page_id']
    ]);


if($method == 'POST'){
    $name_page = filter_input(INPUT_POST,"name_page");
    $description = filter_input(INPUT_POST,"description");
    $banner_image = filter_input(INPUT_POST,"banner_image");
    
    if(!$name_page){
        $name_page = $default_name;
    }

    if(!$description){
        $description = $default_description;
    }

    if ($_FILES['banner_image']['error'] === UPLOAD_ERR_NO_FILE){
        $banner_image = $default_banner_image;
    }


    if (isset($_POST['submit']) && isset($_FILES['banner_image'])) {
        $img_name = $_FILES['banner_image']['name'];
        $img_size = $_FILES['banner_image']['size'];
        $tmp_name = $_FILES['banner_image']['tmp_name'];
        $error = $_FILES['banner_image']['error'];

        $new_img_name = uniqid("IMG-", true) . '.' . $img_ex_lc;
        $img_upload_path = '../profiles/uploads/' . $new_img_name;
        $banner_image = $new_img_name;

        if (move_uploaded_file($tmp_name, $img_upload_path)) {
            $add_image = $app_pdo->prepare("UPDATE pages SET banner_image = :image WHERE id = :id");
            $add_image->execute([
                ":image" => $new_img_name,
                ":id" => $page_id
            ]);
    }
}

    if($name_page != $default_name){
        $verify_existing_page_request = $app_pdo->prepare("
            SELECT * FROM pages 
            WHERE name = :name_page;
        ");
        $verify_existing_page_request->execute([
            ":name_page" => $name_page
        ]);

        $verify_existing_pages = $verify_existing_page_request ->fetch(PDO::FETCH_ASSOC);
        if($verify_existing_pages){
            $error = true;
        }elseif(!$verify_existing_pages){
            $modify_page_request = $app_pdo -> prepare('
            UPDATE pages
            SET name = :name_page, description = :description, banner_image = :banner_image
            WHERE id = :page_id
            ');
            $modify_page_request->execute([
                ':name_page' => $name_page,
                ':description' => $description,
                ':banner_image' => $banner_image,
                ':page_id' => $_SESSION['page_id']
            ]);  
            $create = true;
        }
    }elseif($name_page == $default_name && ($description != $default_description || $banner_image != $default_banner_image)){
        $modify_page_request = $app_pdo -> prepare('
        UPDATE pages
        SET name = :name_page, description = :description, banner_image = :banner_image
        WHERE id = :page_id
        ');
        $modify_page_request->execute([
            ':name_page' => $name_page,
            ':description' => $description,
            ':banner_image' => $banner_image,
            ':page_id' => $_SESSION['page_id']

        ]);       
        $create = true;
    }elseif ($name_page == $default_name && $description == $default_description && $banner_image == $default_banner_image){
        $empty = true;
    }
}

$logo = '../../assets/img/white_logo.svg';
$header_pp_image = $path_img . $my_pp_image;
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
    <link rel="stylesheet" href="../../assets/css/modify_page.css">
    <link rel="stylesheet" href="../../assets/css/header.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <title>Modifier une page</title>
</head>
<body>
    <?php include '../inc/tpl/header.php'; ?>
    <main>
        <div class="create">
            <div class="header">
                <div><h2>Modifier la page</h2></div>
            </div>
            <div class="main">
                <form method="POST" enctype="multipart/form-data">
                    <div>
                        <label for="name_page">Nom de la page</label>
                        <input id="name" type="text" name = "name_page">
                    </div>
                    <div>
                        <label for="description">Sujet de la page</label>
                        <input id="description" type="text" name = "description">
                    </div>
                    <div>
                        <label for="image">Bannière de la page</label>
                        <input type="file" id="fileInput" name = "banner_image" class="custom-file-input">
                        <label for="fileInput" class="custom-file-label">Choisir un fichier</label>
                        <div class="submit"><input class="input" type="submit" name="submit" value = "Modifier la page"></div>
                        <?php if(isset($create)) : ?>
                            <p>Page bien modifiée.</p>
                        <?php elseif (isset($error)) : ?>
                            <p>Ce nom de page est déjà utilisé. Veuillez réessayer.</p>
                        <?php elseif (isset($empty)) : ?>
                            <p>Veuillez remplir au moins un des champs.</p>
                        <?php endif; ?>
                    </div>
                </form>
                <div class="planet"><img src="../../assets/img/create_groups_planet.svg" alt=""></div>
            </div>
        </div>
        <div class="suggestion">
            <h1>Abonnées</h1>
            <?php foreach ($display_subscribers_request as $subscriber){?>
            <div class="people">
                <p><?= $subscriber['first_name'] . ' ' . $subscriber['last_name']; ?></p>
                <?php if($statut == 1){ ?>
                    <?php if($subscriber['admin'] == 0){ ?>
                        <div class="div-button"><button id="<?= $subscriber['id'] ?>">Ajouter comme admin</button></div>
                    <?php }elseif($subscriber['admin'] == 1){ ?>
                    <div class="div-button"><button id="<?= $subscriber['id'] ?>">Retirer l'admin</button></div>
                <?php }} ?>
            </div>
            <?php } ?>
        </div>
    </main>
    <script src="../../assets/js/notifications.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
    const divButtons = document.getElementsByClassName("div-button");
    Array.from(divButtons).forEach(divButton => {
        divButton.addEventListener("click", () => {
            const id_btn = divButton.firstElementChild.id;
            console.log(id_btn);
            $.ajax({
                url:'script.php',
                method: 'POST',
                data: { page_id: <?php echo $_SESSION['page_id']?>, user_id: id_btn},
                success: function(response){
                location.reload();
                },
            })
            
        });
    })
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
            $.post("../inc/tpl/script_search_header.php", 
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