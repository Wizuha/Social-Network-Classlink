<?php
    session_start();
    require '../inc/pdo.php';
    require '../inc/functions/token_functions.php';
    require '../inc/tpl/data_user.php';
    $method = filter_input(INPUT_SERVER, "REQUEST_METHOD");

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

    $get_profile_images = $app_pdo->prepare("
            SELECT pp_image,banner_image FROM profiles WHERE id = :id;
        ");
    $get_profile_images->execute([
        ":id" => $_SESSION['id']
    ]);

    $profile_images_result = $get_profile_images->fetch(PDO::FETCH_ASSOC);

    $pp_image = $profile_images_result['pp_image'];
    $banner_image = $profile_images_result['banner_image'];

    if($method == 'POST'){
        $new_pp_image = filter_input(INPUT_POST,"new_pp_image");
        $new_banner_image = filter_input(INPUT_POST,"new_banner_image");

        if(!$new_pp_image && !$new_banner_image){
            $empty = true;
        }
        
        if (isset($_POST['submit']) && isset($_FILES['new_pp_image'])) {
            var_dump($_POST['submit']);
            echo "upload succes";
            $pp_img_name = $_FILES['new_pp_image']['name'];
            $pp_img_size = $_FILES['new_pp_image']['size'];
            $pp_tmp_name = $_FILES['new_pp_image']['tmp_name'];
            $pp_error = $_FILES['new_pp_image']['error'];
            $pp_img_ex = pathinfo($pp_img_name, PATHINFO_EXTENSION);
            $pp_img_ex_lc = strtolower($pp_img_ex);
        
            $new_pp_img_name = uniqid("IMG-", true) . '.' . $pp_img_ex_lc;
            $pp_img_upload_path = './uploads/' . $new_pp_img_name;
            var_dump($new_pp_img_name);
            var_dump($pp_img_upload_path);
        
            if (move_uploaded_file($pp_tmp_name, $pp_img_upload_path)) {
                echo"ici";                    
                $add_image = $app_pdo->prepare("UPDATE profiles SET pp_image = :new_pp_image WHERE id = :id");
                $add_image->execute([
                    ":new_pp_image" => $new_pp_img_name,
                    ":id" => $_SESSION['id']
                ]);
                echo 'Succès de la mise à jour de limage';
            } else {
                echo 'Erreur lors du déplacement du fichier';
            }

            $modify_pp_image = true;
        }else{
            echo "upload fail";
        }

        if (isset($_POST['submit']) && isset($_FILES['new_banner_image'])) {
            echo "upload succes";
            $img_name = $_FILES['new_banner_image']['name'];
            $img_size = $_FILES['new_banner_image']['size'];
            $tmp_name = $_FILES['new_banner_image']['tmp_name'];
            $error = $_FILES['new_banner_image']['error'];
            $img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
            $img_ex_lc = strtolower($img_ex);
        
            $new_img_name = uniqid("IMG-", true) . '.' . $img_ex_lc;
            $img_upload_path = './uploads/' . $new_img_name;
            var_dump($new_img_name);
            var_dump($img_upload_path);
        
            if (move_uploaded_file($tmp_name, $img_upload_path)) {
                echo"ici";                    
                $add_image = $app_pdo->prepare("UPDATE profiles SET banner_image = :new_banner_image WHERE id = :id");
                $add_image->execute([
                    ":new_banner_image" => $new_img_name,
                    ":id" => $_SESSION['id']
                ]);
                echo 'Succès de la mise à jour de limage';

            } else {
                echo 'Erreur lors du déplacement du fichier';
            }

            $modify_banner_image = true;
        }else{
            echo "upload fail";
        }

        if(($new_pp_image && $new_banner_image) || $new_pp_image || $new_banner_image){
            $modify = true;
        }

        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }

    $messaging_path = '../../messagerie_websocket/index_messagerie.php';
    $dashboard_path = '../dashboard.php';
    $settings_path = './settings.php';
    $page_css_path = '../../assets/css/settings.css';
    $header_css_path = '../../assets/css/header.css';
    $profile_path = './profile.php';
    $page_title = 'Paramètre - ClassLink';
    $page_dashboard_path = '../pages/page_dashboard.php';
    $logo = '../../assets/img/white_logo.svg';
    $tv = '../../assets/img/tv-header.svg';
    $bell = '../../assets/img/header-bell.svg';
    $messages = '../../assets/img/messages-icon.svg';

    include '../inc/tpl/header.php';
?>

    <main>
        <div class="settings-profil">
            <div class="banner_button">
                <div class="banner">
                    <img src="<?= "$path_img$banner_image"?>" alt="" />
                </div>
                <div class="pp">
                    <img src="<?= "$path_img$pp_image"?>" alt="">
                </div>
            </div>
        </div>
        <div>
            <form method="POST" enctype="multipart/form-data">
                <div>
                    <label for="new_pp_image">Photo de profil</label>
                    <label for="fileInput">Choisir un fichier</label>
                    <input type="file" id="fileInput" name="new_pp_image">
                </div>
                <div>
                    <label for="new_banner_image">Bannière</label>
                    <label for="fileInput">Choisir un fichier</label>
                    <input type="file" id="fileInput2" name="new_banner_image">

                    <div class="submit"><input class="input" type="submit" name="submit" value = "Valider"></div>
                    <!-- <php if(isset($empty)) : ?>
                        <p>Veuillez remplir au moins un des champs.</p> -->
                    <?php if(isset($modify_pp_image)) : ?>
                        <p>La photo de profil a bien été modifiée.</p>
                    <?php endif; ?>
                    <?php if(isset($modify_banner_image)) : ?>
                        <p>La bannière a bien été modifiée.</p>
                    <?php endif; ?>
                </div>
            </form>
            <div class="back-to-settings-btn"><button onclick="redirectToPage()">Retour au menu des paramètres</button></div>
        </div>
    </main>
    <script>
        function redirectToPage() {
            window.location.href = "http://localhost/SocialNetwork-Fullstack-Project/classlink_app/profiles/settings.php";
        }

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
            $.post("./inc/tpl/index.php", 
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

        const messagingIconLink = document.getElementById('message-link');
        messagingIconLink.addEventListener('click', () => {
            window.location.href = '<?= $messaging_path ?>';
        })
    </script>
</body>
</html>