<?php
    session_start();
    require './inc/functions/token_functions.php';
    require './inc/pdo.php';
    require './inc/tpl/data_user.php';

    if(isset($_SESSION['token'])){
        $check = token_check($_SESSION["token"], $auth_pdo);
        if($check == 'false'){
            header('Location: ./connections/login.php');
            exit();
        } elseif($_SESSION['profile_status'] == 'Inactif') {
            header('Location: ./profiles/settings.php');
            exit();        
        }
    }elseif(!isset($_SESSION['token'])){
        header('Location: ./connections/login.php');
        exit();
    }

    $path_img = 'http://localhost/SocialNetwork-Fullstack-Project/classlink_app/profiles/uploads/';

    // Préparation de la requète permettant de récupérer toute les infos lié à ce profile via l'id
    $account_info_request =  $app_pdo->prepare("
        SELECT * FROM profiles
        WHERE id = :id;
    ");

    // Execution de la requète avec l'id passez en session
    $account_info_request->execute([
        ":id" => $_SESSION['id']
    ]);

    // Récupération du résultat de la requète
    $result = $account_info_request->fetch(PDO::FETCH_ASSOC);

    // Variables contenant les informations du compte, suivi d'une condition qui permettra d'afficher non renseigné si la variable contient null
    $lastname = $result['last_name'];
    if ($lastname == null) {
        $lastname = 'Non renseigné';
    }
    $firstname = $result['first_name'];
    if ($firstname == null) {
        $firstname = 'Non renseigné';
    }
    $username = $result['username'];
    $birth_date = $result['birth_date'];

    if ($birth_date == null) {
        $age = 'Non renseignée';
    } else {
        $current_date = new DateTime();
        $birth_date = new DateTime($birth_date);
        $diff = $current_date->diff($birth_date);
        $age = $diff->y;
    }
    $gender = $result['gender'];
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

    $mail = $result['mail'];
    if ($mail == null) {
        $mail = 'Non renseigné';
    }

    $banner_image = $result['banner_image'];

    $profile_activity_request = $app_pdo->prepare("
        SELECT 
        (SELECT COUNT(creator_profile_id) FROM pages WHERE creator_profile_id = :id) AS `numbers_of_pages`,
        (SELECT COUNT(profile_id) FROM publications_profile WHERE profile_id = :id) AS `numbers_of_publications`,
        (SELECT COUNT(id) FROM relations WHERE user_profile_id = :id) AS `numbers_of_relations` 
    ");

    $profile_activity_request->execute([
        ":id" => $_SESSION['id']
    ]);

    $profile_activity_result = $profile_activity_request->fetch(PDO::FETCH_ASSOC);
    $numbers_of_pages = $profile_activity_result["numbers_of_pages"];
    $numbers_of_publications = $profile_activity_result["numbers_of_publications"];
    $numbers_of_relations = $profile_activity_result["numbers_of_relations"];

    $group_count_request = $app_pdo->prepare("
        SELECT COUNT(group_id) AS 'number_of_groups' FROM profiles
        LEFT JOIN group_members ON profile_id = profiles.id
        WHERE profile_id = :id
    ");

    $group_count_request->execute([
        ':id' => $_SESSION['id']
    ]);

    $group_count_result = $group_count_request->fetch(PDO::FETCH_ASSOC);

    $numbers_of_groups = $group_count_result['number_of_groups'];
    



    // afficher chaque publications
    if(isset($_SESSION['id'])) {
        $afficher_publications = $app_pdo->prepare("SELECT * FROM test_publications WHERE id <> ?;");
        $afficher_publications->execute(array($_SESSION['id']));

        $result_pub = $afficher_publications->fetchAll(PDO::FETCH_ASSOC);
    }

    $likes = $app_pdo->prepare("SELECT id FROM likes WHERE test_publications_id = ?");
        $likes->execute(array($result_pub[0]['id']));
        $likes = $likes->rowCount();


        $dislikes = $app_pdo->prepare("SELECT id FROM dislikes WHERE test_publications_id = ?");
        $dislikes->execute(array($result_pub[0]['id']));
        $dislikes = $dislikes->rowCount();

        $comment = $app_pdo->prepare('SELECT * FROM comments_publications WHERE id_publication = ?');
        $comment->execute(array($result_pub[0]['id']));


    // Suggestions profiles 

    if(isset($_SESSION['id'])) {
        $afficher_membres = $app_pdo->prepare("SELECT * FROM profiles WHERE id <> ?;");
        $afficher_membres->execute(array($_SESSION['id']));
    } else {
        $afficher_membres = $app_pdo->prepare("SELECT * FROM profiles;");
        $afficher_membres->execute();
    }
    $afficher_membres_result = $afficher_membres->fetchAll(PDO::FETCH_ASSOC);

    $afficher_page = $app_pdo->prepare("
    SELECT * from pages 
    ");
    $afficher_page->execute();
    $afficher_page_result = $afficher_page->fetchAll(PDO::FETCH_ASSOC);


    $afficher_group = $app_pdo->prepare("
    SELECT * from groups_table 
    ");
    $afficher_group->execute();
    $afficher_group_result = $afficher_group->fetchAll(PDO::FETCH_ASSOC);
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <script src="../assets/js/script.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <title>Classlink-Dashboard</title>
</head>
<body>

<section class="header">
        <a class="logo" href="">
            <img src="../assets/img/white_logo.svg" alt="logo">
        </a>
        <div class="input-box">
            <input id="input" type="text" placeholder="Recherche...">
            <span class="search">
                <i class="uil uil-search search-icon"></i>
                <ul class="dropdown" id="dropdown"></ul>
            </span>
            <i class="uil uil-times close-icon"></i>
        </div>
        <a class="profile-icon-header" href="../profile.php">
            <img src="<?= $path_img . $result['pp_image'] ?>" alt="profile-icon">
        </a>
        <a class="tv-icon" href="">
            <img src="../assets/img/tv-header.svg" alt="tv-icon">
        </a>
        <a class="notifications" href="">
            <img src="../assets/img/header-bell.svg" alt="bell-icon">
        </a>
        <a class="messages-icon" href="">
            <img src="../assets/img/messages-icon.svg" alt="messages-icon">
        </a>
</section>
    <section class="profil">
        <div id="profile-head" class="profile-head profile-link">
            <img class="profile-pic" src="<?= $path_img . $result['pp_image'] ?>" alt="profile-pic">
            <h3><?= "$firstname $lastname" ?></h3>
        </div><hr>
        <div class="profile-infos">
            <p>Age: <span><?= $age ?></span></p>
            <p>Genre: <span><?= $gender ?></span></p>
            <p>Email: <span> <?= $mail ?></span></p>
        </div>
        <div>
            <button id="modify-profile-btn" class="profile-button">Modifier</button>
        </div>
    </section>
    <section class="create-post">
        <img class="post-profile-pic profile-link" src="<?= $path_img . $result['pp_image'] ?>" alt="profile-icon">
        <button class="post-button">Bienvenue sur votre fil d'actualité</button>
    </section>
    <section class="informations">
        <p><a href="./profiles/membre.php">Relations</a><span><?= $numbers_of_relations ?></span></p><hr>
        <p><a href="./groups/group_dashboard.php">Groupes</a><span><?= $numbers_of_groups ?></span></p><hr>
        <p><a href="./pages/page_dashboard.php">Pages</a><span><?= $numbers_of_pages ?></span></p>
    </section>
    <section class="suggestions">
        <h3 class="suggestions-title">Suggestions</h3>
        <h4>Personnes:</h4>
        <?php if($afficher_membres_result){
            for($i=0; $i < 4; $i++ ){ ?>
                <div class="suggestion-line">
                <img src="<?= $path_img . $afficher_membres_result[$i]['pp_image'] ?>" alt="profile-icon">
                <p><?= $afficher_membres_result[$i]['first_name'] . $afficher_membres_result[$i]['last_name'] ?></p>
                <a href="./profiles/voir_profile.php? id=<?= $afficher_membres_result[$i]['id']?>"><button class="quick-add">+</button></a>
            </div>
            <?php }
        } ?>
        <a href="./pages/create_page.php"><h3 class="pages-title">Pages</h3></a>
        <div class="pages-container">
            <div class="item1"><a href="./pages/page.php?id=<?= $afficher_page_result[43]['id'] ?>"><img src="<?php echo $path_img . $afficher_page_result[43]['banner_image'] ?>" alt=""></a></div>
            <div class="item2"><a href="./pages/page.php?id=<?= $afficher_page_result[44]['id'] ?>"><img src="<?php echo $path_img . $afficher_page_result[44]['banner_image'] ?>" alt=""></a></div>
            <div class="item3"><a href="./pages/page.php?id=<?= $afficher_page_result[45]['id'] ?>"><img src="<?php echo $path_img . $afficher_page_result[45]['banner_image'] ?>" alt=""></a></div>
            <div class="item4"><a href="./pages/page.php?id=<?= $afficher_page_result[46]['id'] ?>"><img src="<?php echo $path_img . $afficher_page_result[46]['banner_image'] ?>" alt=""></a></div>
        </div>

        <a href="./groups/create_group2.php"><h3 class="pages-title">Groupes</h3></a>
        <div class="pages-container">
            <div class="item1"><a href="./groups/group.php?id=<?= $afficher_group_result[50]['id'] ?>"><img src="<?php echo $path_img . $afficher_group_result[50]['banner_image'] ?>" alt=""></a></div>
            <div class="item2"><a href="./groups/group.php?id=<?= $afficher_group_result[53]['id'] ?>"><img src="<?php echo $path_img . $afficher_group_result[53]['banner_image'] ?>" alt=""></a></div>
        </div>

    </section>
    <section class="timeline">
        <?php if($result_pub) { 
            foreach($result_pub as $pub){ ?>
                <div class="post-information">
            <img class="post-profile-pic profile-link" src="../assets/img/ellipse.svg" alt="profile-icon">
            <h3 class="post-name"><?= "$firstname $lastname" ?></h3>
        </div>
        <p class="post-description"><?= $pub['text'] ?>
            <span id="dots">...</span>
            <span id="more"><?= $pub['date_time_publication'] ?></span>
        </p>
        <a class="read-more" onclick="readMore()" id="myBtn">Voir plus</a>
        <div class="div-post-image">
            <img class="post-image" src="<?= $path_img . $pub['image'] ?>" alt="post">
        </div>
        <div>
        <a href="profiles/action.php?t=1&id=<?= $_SESSION['id'] ?>">J'aime (<?= $likes ?>)</a>
        <br>    
        </div>
        </div>
        <h2>Commentaires</h2>
        <form method="POST">
            <input type="text" name="pseudo" placeholder="Votre pseudo"/><br>
            <textarea name="commentaire" placeholder="Votre commentaire..."></textarea><br>
            <input type="submit" value="Commenter" name="submit_commentaire"/>
        </form>
        <?php if(isset($c_msg)) { echo $c_msg;} ?>
        <?php foreach ($comment as $af_c) {?>
            <b><?= $af_c['pseudo']?></b> <?= $af_c['commentaire'] ?><br />
        <?php }
            }
        } ?>
    </section>
    <button id="logout" class="disconnect">Se déconnecter</button> 

    <script>
        let inputBox = document.querySelector(".input-box"),
            search = document.querySelector(".search"),
            closeIcon = document.querySelector(".close-icon")

        search.addEventListener("click", () => inputBox.classList.add("open"))
        closeIcon.addEventListener("click", () => inputBox.classList.remove("open"))
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function(){
          
          function fetchData(){
            let value = $("#input").val(); // Permet de récupérer la valeur de l'input
            console.log(value)
            if (value == '') { // if la valeur de value est null
               $('#dropdown').css('display', 'none');
            }
            $.post("index.php", 
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

      const logoutButton = document.getElementById('logout');
        logoutButton.addEventListener('click', () => {
            window.location.href = './connections/logout.php';
        })

        const modifyProfileBtn = document.getElementById('modify-profile-btn');
        modifyProfileBtn.addEventListener('click', () => {
            window.location.href = './profiles/settings.php';
        })

        const profilePictures = document.querySelectorAll('.profile-link');
        for (let i = 0; i < profilePictures.length; i++) {
            profilePictures[i].addEventListener('click', () => {
                window.location.href = './profiles/profile.php';
            });
        }
        
    </script>
</body>
</html>