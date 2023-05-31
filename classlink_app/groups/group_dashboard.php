<?php 
    session_start();
    require '../inc/pdo.php';
    require '../inc/functions/token_functions.php';
    require '../inc/tpl/data_user.php';

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


    $requete = $app_pdo->prepare('
        SELECT group_id 
        FROM group_members
        WHERE profile_id = :profile_id;
    ');
    $requete -> execute([
        ':profile_id'=> $_SESSION['id']
    ]);
    $result = $requete->fetchAll(PDO::FETCH_ASSOC);

    $group_id = array();

    for($i = 0; $i < count($result);$i++){
        array_push($group_id, $result[$i]['group_id']);
    }

    $get_my_groups = $app_pdo->prepare('
        SELECT DISTINCT gm.group_id, g.name, g.description, g.status, g.id, g.banner_image
        FROM group_members gm
        JOIN groups_table g ON gm.group_id = g.id
        WHERE gm.profile_id = :profile_id;
    ');
    
    $get_my_groups->execute([
        ':profile_id' => $_SESSION['id']
    ]);

    $get_my_groups_result = $get_my_groups->fetchAll(PDO::FETCH_ASSOC);
    $get_other_groups = $app_pdo->prepare('
    SELECT DISTINCT gm.group_id, g.name, g.description, g.status, g.id, g.banner_image, g.status
    FROM group_members gm
    JOIN groups_table g ON gm.group_id = g.id
    WHERE gm.profile_id != :id;
    ');

    $get_other_groups->execute([
        ':id' => $_SESSION['id']
    ]);

    $get_other_groups_result = $get_other_groups->fetchAll(PDO::FETCH_ASSOC);

    $messaging_path = '../../messagerie_websocket/index_messagerie.php';
    $dashboard_path = '../dashboard.php';
    $settings_path = './settings.php';
    $page_css_path = '../../assets/css/group_dashboard.css';
    $header_css_path = '../../assets/css/header.css';
    $profile_path = '../profiles/profile.php';
    $page_title = 'Groupes - Classlink';
    $page_dashboard_path = '../pages/page_dashboard.php';
    $logo = '../../assets/img/white_logo.svg';
    $tv = '../../assets/img/tv-header.svg';
    $bell = '../../assets/img/header-bell.svg';
    $messages = '../../assets/img/messages-icon.svg';

    include '../inc/tpl/header.php';
?>
    <main>
    <div class='dashboard-leftside'>
        <div class="informations">
            <div class="top">
                    <div class="img"><img src="<?= $header_pp_image ?>" alt="photo de profil navigateur"></div>
                    <div class="name">
                        <p><?= $firstname . $lastname ?></p>
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
        <div class='relations-groups-pages'>
            <div>
                <div class='text-number'>
                    <div class='txt'><p><a href="../profiles/membre.php">Relations</a></p></div>
                    <div><p><?= $numbers_of_relations ?></p></div>
                </div>
                <div class='separator2'></div>
            </div>
            <div>
                <div class='text-number'>
                    <div class='txt'><p><a href="./group_dashboard.php">Groupes</a></p></div>
                    <div><p><?= $numbers_of_groups ?></p></div>
                </div>
                <div class='separator2'></div>
            </div>
            <div>
                <div class='text-number'>
                    <div class='txt'><p><a href="../pages/page_dashboard.php">Pages</a></p></div>
                    <div><p><?= $numbers_of_pages ?></p></div>
                </div>
            </div>
            <div class="btn">
                <button id="logout-btn" class="btn3">Se déconnecter</button> <!-- Rajouter le lien vers logout--> 
            </div>
        </div>
    </div>
    <div class="container-right">
    <div class="suggest-groups">
        <div class="header">
            <div class="header-content">
                <div class="title"><p>Groupes rejoints</p></div>
                <div><p><?= $numbers_of_groups ?></p></div>
            </div>
        </div>
        <div class="main">
        <?php  if ($get_my_groups_result) {
                foreach($get_my_groups_result as $row){ ?>
            <div class="card-banner">
                <div class="banner"><img src="../../assets/img/default_banner.jpg" alt=""></div>
                <div class="title"><p><?php echo $row['name'] ?></p></div>
                <div class="info">
                    <div class="info-grp"><p><?= $row['description'] ?></p></div>
                    <div class="link"><a href="./group.php?id=<?= $row['id'] ?>">Voir le groupe</a></div>
                </div>
            </div>
            <?php } }
                else {
                echo "Vous n'êtes dans aucun groupe.";
            } ?>
        </div>
        <div class="create"><a href="./create_group.php"><button>Créer un groupe</button></a></div>
    </div>
        <div class="suggest-groups">
        <div class="header">
            <div class="header-content">
                <div class="title"><p>Suggestion de groupes</p></div>
                <div><p>6</p></div>
            </div>
        </div>
        <div class="main">
            <?php  if ($get_other_groups_result) {
                    $j = 0;
                for($i = 0; $j <= 5; $i++){
                    if(!in_array($get_other_groups_result[$i] , $get_my_groups_result)){ ?>
            <div class="card-banner">
                <div class="banner"><img src="./assets/img/default_banner.jpg" alt=""></div>
                <div class="title"><p><?= $get_other_groups_result[$i]['name'] ?></p></div>
                <div class="info">
                    <div class="info-grp"><p><?= $get_other_groups_result[$i]['description'] ?></p></div>
                    <?php if ($get_other_groups_result[$i]['status'] == 'prive' ||$get_other_groups_result[$i]['status'] == 'private'){ ?>
                    <div class="link"><a href="./asked_group.php?id=<?= $get_other_groups_result[$i]['id']?>">Envoyer une demande</a></div>
                    <?php }elseif($get_other_groups_result[$i]['status'] == 'Public' ||$get_other_groups_result[$i]['status'] == 'public'){ ?>
                    <div class="link"><a href="./group.php?id=<?= $get_other_groups_result[$i]['id']?>">Voir le groupe</a></div>
                    <?php } ?>
                </div>
            </div>
            <?php 
            $j++;}}}
                else {
                echo 'Pas de groupe';
            } ?>
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
















