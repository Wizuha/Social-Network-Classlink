<?php
session_start();
require '../inc/pdo.php';
require '../inc/functions/token_functions.php';
require '../inc/tpl/data_user.php';

$title = "Créer une groupe";

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

$id = $_SESSION['id'];

$recuperation_data_profiles = $app_pdo -> prepare('
    SELECT last_name, first_name, birth_date, gender, mail, pp_image FROM profiles
    WHERE id = :id;
');
$recuperation_data_profiles->execute([
    ":id" => $id
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

$method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');

if($method == 'POST'){
    $name_group = filter_input(INPUT_POST,"name");
    $description = filter_input(INPUT_POST,"description");
    $status = filter_input(INPUT_POST,"status");
    $banner_image = filter_input(INPUT_POST,"banner_image");

    $verify_existing_group_request = $app_pdo->prepare("
        SELECT * FROM groups_table 
        WHERE name = :name;
    ");
    $verify_existing_group_request->execute([
        ":name" => $name_group
    ]);

    $verify_existing_group = $verify_existing_group_request ->fetch(PDO::FETCH_ASSOC);

    if(!$verify_existing_group){
        if(isset($_SESSION['id'])){
            $request_groups_creator_profile_id = $app_pdo -> prepare('
            SELECT * FROM profiles WHERE id = :id
            ');
            $request_groups_creator_profile_id->execute([
            ':id' => $_SESSION['id']
            ]);
            }
       
        $creator_profile_id = $_SESSION['id'];

        $create_group_request = $app_pdo -> prepare('
        INSERT INTO groups_table (name,description, status, banner_image,creator_profile_id)
        VALUES (:name_group, :description, :status, :banner_image,:creator_profile_id);
        ');
        $create_group_request->execute([
            ':name_group' => $name_group,
            ':description' => $description,
            ':status' => $status,
            ':banner_image' => $banner_image,
            ':creator_profile_id' => $creator_profile_id
        ]);

        $new_group_id = $app_pdo->lastInsertId();
        
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
                // echo"ici";
                $add_image = $app_pdo->prepare("UPDATE groups_table SET banner_image = :image WHERE id = :id");
                $add_image->execute([
                    ":image" => $new_img_name,
                    ":id" => $new_group_id
                ]);
            }}
        $select_name_create_group = $app_pdo->prepare('
        SELECT id FROM groups_table
        WHERE name = :name_group
        ');
        $select_name_create_group->execute([
            ':name_group'=>$name_group
        ]);
        $ID_group = $select_name_create_group ->fetch(PDO::FETCH_ASSOC);
        $adding_member = $app_pdo->prepare('
        INSERT INTO group_members (profile_id,group_id)
        VALUES (:profile_id,:group_id);
        ');
        $adding_member->execute([
            ':profile_id' => $_SESSION['id'],
            ':group_id'=>$ID_group['id']
        ]);
        $create = true;
        header('Location: ./group.php?id=' . $new_group_id);
        }
    else{
        $name_already_exist = true;
    }
}

    $messaging_path = '../../messagerie_websocket/index_messagerie.php';
    $dashboard_path = '../dashboard.php';
    $settings_path = '../profiles/settings.php';
    $page_css_path = '../../assets/css/create_group.css';
    $header_css_path = '../../assets/css/header.css';
    $profile_path = '../profiles/profile.php';
    $page_title = 'Créer un groupe - Classlink';
    $page_dashboard_path = '../pages/page_dashboard.php';
    $logo = '../../assets/img/white_logo.svg';
    $tv = '../../assets/img/tv-header.svg';
    $bell = '../../assets/img/header-bell.svg';
    $messages = '../../assets/img/messages-icon.svg';

    include '../inc/tpl/header.php';

?>
    <main>
        <div class="left-side">
            <div class="informations">
                <div class="top">
                    <div class="img"><img src="<?= $header_pp_image ?>" alt=""></div>
                    <div class="name">
                        <p><?php echo  $first_name." ".$last_name ?></p>
                    </div>
                    <div class="separator"></div>
                </div>
                <div class="mid">
                    <div class="personnal-info">
                        <div><p>Age <span>: <?= $age ?></span></p></div>
                        <div><p>Genre <span>: <?= $gender ?></span></p></div>
                        <div><p>E-mail <span>: <?= $mail ?></span></p></div>
                    </div>
                </div>
                <div class="bottom">
                    <div class="btn2"><button id="modify-profile-btn">Modifier</button></div> <!-- Rajouter le lien vers modifier profil--> 
                </div>
            </div>
            <div class="btn">
                <button id="logout-btn">Déconnexion</button>
            </div>
        </div>
        <div class="create">
            <div class="header">
                <div><h2>Créer un groupe</h2></div>
            </div>
            <div class="main">
                <form method="POST" enctype="multipart/form-data">
                    <div>
                        <label for="name">Nom du groupe</label>
                        <input id="name" type="text" name="name">
                    </div>
                    <div>
                        <label for="subject">Sujet du groupe</label>
                        <input id="subject" type="text" name = "description">
                    </div>
                    <div>
                        <label for="statut">Statut du groupe</label>
                        <select name="status" id="status">
                            <option value="Public">Publique</option>
                            <option value="private">Privé</option>
                        </select>
                    </div>
                    <div>
                        <label for="image">Image du groupe</label>
                        <input type="file" id="fileInput" name = "banner_image" class="custom-file-input">
                        <label for="fileInput" class="custom-file-label">Choisir un fichier</label>
                    <div class="submit"><input class="input" name="submit" type="submit" value = "Créer un groupe"></div>
                    <?php if(isset($create)){ ?>
                        <p>Bien créer</p>
                  <?php  }elseif(isset($name_already_exist)){ ?>
                        <p>Ce nom de groupe existe déjà</p>
                    <?php } ?>
                    </div>
                </form>
                <div class="planet"><img src="../../assets/img/create_groups_planet.svg" alt=""></div>
            </div>
        </div>
    </main>
    <!-- <script src="../../assets/js/notifications.js"></script> -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>

let inputBox = document.querySelector(".input-box"),
        search = document.querySelector(".search"),
        closeIcon = document.querySelector(".close-icon")

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

        const modifyProfileBtn = document.getElementById('modify-profile-btn');
        modifyProfileBtn.addEventListener('click', () => {
            window.location.href = '../profiles/settings.php';
        })

        const messagingIconLink = document.getElementById('message-link');
        messagingIconLink.addEventListener('click', () => {
            window.location.href = '<?= $messaging_path ?>';
        })

        const profilePictures = document.querySelectorAll('.profile-link');
        for (let i = 0; i < profilePictures.length; i++) {
            profilePictures[i].addEventListener('click', () => {
                window.location.href = '../profiles/profile.php';
            });
        }   
        const logoutBtn = document.getElementById('logout-btn');
        logoutBtn.addEventListener('click', function(){
            window.location.href = '../connections/logout.php';
        })
</script>
</body>
</html>