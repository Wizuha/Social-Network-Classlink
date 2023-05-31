<?php 
session_start();
require '../inc/pdo.php'; 
require '../inc/functions/token_functions.php';
require '../inc/tpl/data_user.php';
$method = filter_input(INPUT_SERVER, "REQUEST_METHOD");

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


    $page_id = $_GET['id'];
    $_SESSION['page_id'] = $page_id; 
    $request_page_data = $app_pdo->prepare("
    SELECT name, banner_image, description,creator_profile_id
    FROM pages
    WHERE id = :id ;
    ");
    $request_page_data->execute ([
        ":id" => $page_id
    ]);

    $result = $request_page_data->fetch(PDO::FETCH_ASSOC);

    if($result){
        $name = $result['name'];
        $banner_image = $result['banner_image'];
        $description = $result['description'];
        $creator_profile_id = $result['creator_profile_id'];

        $request_creator_data = $app_pdo->prepare('
        SELECT last_name, first_name, pp_image
        FROM profiles
        WHERE id = :creator_profile;
        ');
        $request_creator_data->execute([
            ":creator_profile" => $creator_profile_id
        ]);

        $result2 = $request_creator_data->fetch(PDO::FETCH_ASSOC);
        $last_name = $result2['last_name'];
        $first_name = $result2['first_name'];
        $pp_image = $result2['first_name'];

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
        }
    }else {
        echo 'error';
    }

    $display_subscribers_request = $app_pdo->prepare('
    SELECT profiles.id, last_name, first_name, admin, pp_image
    FROM profiles
    LEFT JOIN subscribers_page ON profiles.id = subscribers_page.profile_id
    WHERE subscribers_page.page_id = :page_id
    ');

    $display_subscribers_request->execute([
        ":page_id" => $_SESSION['page_id']
    ]);

        // Afficher les posts

        $display_post = $app_pdo->prepare('
        SELECT publications_page.*, pp_image, last_name, first_name FROM profiles 
        JOIN publications_page ON publications_page.profile_id = profiles.id 
        WHERE page_id = :page_id
        ');
        $display_post->execute([
            ':page_id' => $page_id 
        ]);
    
        $display_post_result = $display_post->fetchAll(PDO::FETCH_ASSOC);


    if($method == 'POST'){
        $input = trim(filter_input(INPUT_POST, "input",));
        $publication_submit = filter_input(INPUT_POST, "publication_submit");
        $publication_content = filter_input(INPUT_POST, "publication_content");        

        if($input == 'Suivre'){
            $request_creator_data = $app_pdo->prepare('
            INSERT INTO subscribers_page (page_id, profile_id, admin) VALUES (:page_id, :profile_id, :admin)
        ');
        $request_creator_data->execute([
            ":page_id" => $_SESSION['page_id'],
            ":profile_id" => $_SESSION['id'],
            ":admin" => 0
        ]);
        header('Location: ' . $_SERVER['PHP_SELF'] . "?id=" . $_SESSION['page_id']);
        exit();
        }elseif ($input == 'Ne plus suivre'){
        $request_creator_data = $app_pdo->prepare('
        DELETE FROM subscribers_page WHERE page_id = :page_id
        AND profile_id = :profile_id
        ');
        $request_creator_data->execute([
            ":page_id" => $_SESSION['page_id'],
            ":profile_id" => $_SESSION['id'],
        ]);
        header('Location: ' . $_SERVER['PHP_SELF'] . "?id=" . $_SESSION['page_id']);
        exit();

        }elseif ($input == 'Gérer la page'){
            header('Location: ./modify_page.php?id=' . $page_id);
            exit();
        } 
        
        if(isset($publication_submit)){
                if (isset($_POST['publication_submit']) && isset($_FILES['publication_image'])) {
                    var_dump($_POST['publication_submit']);
                    $img_name = $_FILES['publication_image']['name'];
                    $img_size = $_FILES['publication_image']['size'];
                    $tmp_name = $_FILES['publication_image']['tmp_name'];
                    $error = $_FILES['publication_image']['error'];
                    $img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
                    $img_ex_lc = strtolower($img_ex);
                
                    $new_img_name = uniqid("IMG-", true) . '.' . $img_ex_lc;
                    $img_upload_path = '../profiles/uploads/' . $new_img_name;
                
                    if (move_uploaded_file($tmp_name, $img_upload_path)) {
                        $add_image = $app_pdo->prepare("UPDATE test_publications SET image = :image WHERE id = :id");
                        $add_image->execute([
                            ":image" => $new_img_name,
                            ":id" => $_SESSION['id']
                        ]);
                
                        echo 'Succès de la mise à jour de limage';
                        echo $new_img_name;
                    } else {
                        echo 'Erreur lors du déplacement du fichier';
                    }
                }

            $add_page_publication = $app_pdo -> prepare("
            INSERT INTO publications_page (profile_id, page_id, image, text)
            VALUES (:user_id, :page_id, :image, :publication_content)
            ");
        
            $add_page_publication -> execute([
                ":user_id" => $_SESSION['id'],
                ":page_id" => $_SESSION['page_id'],
                ":publication_content" => $publication_content,
                ":image" => $new_img_name
        ]);

            header('Location: ' . $_SERVER['PHP_SELF'] . "?id=" . $_SESSION['page_id']);
            exit();
        }


    }

    $messaging_path = '../../messagerie_websocket/index_messagerie.php';
    $dashboard_path = '../dashboard.php';
    $settings_path = '../profiles/settings.php';
    $page_css_path = '../../assets/css/page.css';
    $header_css_path = '../../assets/css/header.css';
    $profile_path = '../profiles/profile.php';
    $page_title = "$name - Classlink";
    $page_dashboard_path = '../pages/page_dashboard.php';
    $logo = '../../assets/img/white_logo.svg';
    $tv = '../../assets/img/tv-header.svg';
    $bell = '../../assets/img/header-bell.svg';
    $messages = '../../assets/img/messages-icon.svg';

    require '../inc/tpl/header.php';
    
?>
    <main>
        <div class="container">
            <div class="top-container">
                <div class="banner_button" style="background-image : url('<?= $path_img . $banner_image ?>') ">
                    <div class="button">
                        <div class="name"><p><?= $result['name']?></p></div>
                        <div class="list-button">
                            <div class="duo-btn">
                            <?php if ($statut == 1) :?>
                                <form method="POST">
                                <input type="submit" name='input' value="Gérer la page">   
                                </form>
                            <?php elseif ($statut == 0) : ?>
                                <form method="POST">
                                <input type="submit" name='input' value="Ne plus suivre" >   
                                </form>
                            <?php elseif ($statut == 'visitor') : ?>
                                <form method="POST">
                                <input type="submit" name='input' value='Suivre' >   
                                </form>
                            <?php endif ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="info">
                    <ul>
                        <li>Abonnés</li>
                        <li>Evènements</li>
                        <li>Actualité</li>
                        <li>Media</li>
                    </ul> </div>
            </div>
            <div class="mid-container">
                <div class="apropos">
                    <h1>A propos</h1>
                    <div class="detail">
                        <p><span style="font-weight: 500;">Sujet: </span><?php echo $description ?></p>
                        <p><span style="font-weight: 500;">Créer par : </span><?php echo $first_name ." ". $last_name  ?></p>
                    </div>
                </div>

                    <div class="fil" >
                    <?php if($statut == 1) : ?>
                        <div class="exprimez-vous">
                            <div class="profile" >
                                <img src="<?= $header_pp_image ?>" alt="photo de profil" >
                            </div>

                            <form method="POST" enctype="multipart/form-data">
                                <input type="text" id="publication_content" name="publication_content" placeholder="Exprimez-vous..."> 
                                <input type="file" id="publication_image" name="publication_image">
                                <input type="submit" id="publication_submit" name="publication_submit" value="Publier">
                            </form>
                        </div>
                        <?php endif ?>

                        <div class="filpost" id="fil"> 
                        <?php if ($display_post_result){
                            foreach($display_post_result as $post){?>
                            <div class='post'>
                                <div class='people'>
                                    <div class='profile' >
                                        <img src='<?= $path_img . $post['pp_image'] ?>' alt=''>
                                    </div>
                                    <p><?= $post['first_name'] . $post['last_name'] ?></p>
                                </div>
                                <p><?= $post['text'] ?></p>
                                <img src='<?= $path_img . $post['image'] ?>' alt=''>
                                <div class='reaction'><p>like</p><p>commentaire</p></div>
                            </div>
                            <?php }}else{ ?>
                                <p>Cette page ne contient pas de post</p>
                            <?php } ?>  
                        </div>

                        <br>
                        <br>
                        <br>
                    </div>
                <div class="suggestion">
                    <h1>Membre</h1> 
                    <?php foreach ($display_subscribers_request as $subscriber){?>
                    <div class="people">
                        <div class="profile" >
                            <img src="<?= $path_img . $subscriber['pp_image'] ?>" alt="">
                        </div>
                        <p>
                        <?= $subscriber['first_name'] . ' ' . $subscriber['last_name']; ?>
                        </p>
                    </div>
                    <?php } ?>
                </div>  
            </div>
        </div>
    </main>
    
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

        search.addEventListener("click", () => inputBox.classList.add("open"))
        closeIcon.addEventListener("click", () => inputBox.classList.remove("open"))

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



