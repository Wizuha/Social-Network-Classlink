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

$title = "Pages suivies";
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

    $get_my_pages = $app_pdo->prepare('
        SELECT DISTINCT subscribers_page.page_id, pages.name, pages.banner_image, pages.id, pages.description
        FROM subscribers_page 
        JOIN pages ON subscribers_page.page_id = pages.id
        WHERE subscribers_page.profile_id = :profile_id;
    ');
    
    $get_my_pages->execute([
        ':profile_id' => $_SESSION['id']
    ]);

    $get_my_pages_result = $get_my_pages->fetchAll(PDO::FETCH_ASSOC);
    $get_other_pages = $app_pdo->prepare('
    SELECT DISTINCT subscribers_page.page_id, pages.name, pages.banner_image, pages.id, pages.description
    FROM subscribers_page 
    JOIN pages ON subscribers_page.page_id = pages.id
    WHERE subscribers_page.profile_id != :id;
    ');

    $get_other_pages->execute([
        ':id' => $_SESSION['id']
    ]);

    $get_other_pages_result = $get_other_pages->fetchAll(PDO::FETCH_ASSOC);

    $messaging_path = '../../messagerie_websocket/index_messagerie.php';
    $dashboard_path = '../dashboard.php';
    $settings_path = './settings.php';
    $page_css_path = '../../assets/css/page_dashboard.css';
    $header_css_path = '../../assets/css/header.css';
    $profile_path = './profile.php';
    $page_title = 'Profil - Classlink';
    $page_dashboard_path = '../pages/page_dashboard.php';
    $logo = '../../assets/img/white_logo.svg';
    $tv = '../../assets/img/tv-header.svg';
    $bell = '../../assets/img/header-bell.svg';
    $messages = '../../assets/img/messages-icon.svg';

    require '../inc/tpl/header.php';
?>
    <main>
    <div class='dashboard-leftside'>
        <div class="informations">
            <div class="top">
                    <div class="img"><img src="<?= $header_pp_image ?>" alt=""></div>
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
                <div class="btn2"><a href="../profile.php"><button>Modifier</button></a></div> <!-- Rajouter le lien vers modifier profil--> 
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
                    <div class='txt'><p><a href="../groups/group_dashboard.php">Groupes</a></p></div>
                    <div><p><?= $numbers_of_groups ?></p></div>
                </div>
                <div class='separator2'></div>
            </div>
            <div>
                <div class='text-number'>
                    <div class='txt'><p><a href="./page_dashboard.php">Pages</a></p></div>
                    <div><p><?= $numbers_of_pages ?></p></div>
                </div>
            </div>
            <div class="btn">
                <button id="logout-btn">Se déconnecter</button> <!-- Rajouter le lien vers logout--> 
            </div>
        </div>
    </div>
    <div class="container-right">
    <div class="suggest-groups">
        <div class="header">
            <div class="header-content">
                <div class="title"><p>Pages suivies</p></div>
                <div><p><?= $numbers_of_pages?></p></div>
            </div>
        </div>
        <div class="main">
        <?php  if ($get_my_pages_result) {
                foreach($get_my_pages_result as $row){ ?>
            <div class="card-banner">
                <div class="banner"><img src="<?= $path_img . $row['banner_image'] ?>" alt=""></div>
                <div class="title"><p><?php echo $row['name'] ?></p></div>
                <div class="info">
                    <div class="info-grp"><p><?= $row['description'] ?></p></div>
                    <div class="link"><a href="./page.php?id=<?= $row['id'] ?>">Voir le groupe</a></div>
                </div>
            </div>
            <?php } }
                else {
                echo 'Vous ne suivez aucunes pages.';
            } ?>
        </div>
        <div class="create"><a href="./create_page.php"><button>Créer une page</button></a></div>
    </div>
        <div class="suggest-groups">
        <div class="header">
            <div class="header-content">
                <div class="title"><p>Suggestion de pages</p></div>
                <div><p>6</p></div>
            </div>
        </div>
        <div class="main">
            <?php  if ($get_other_pages_result) {
                    $j = 0;
                    for($i = 0; $j <= 5; $i++){
                        if(!in_array($get_other_pages_result[$i] , $get_my_pages_result)){ ?>
            <div class="card-banner">
                <div class="banner"><img src="<?= $path_img . $get_other_pages_result[$i]['banner_image'] ?>" alt=""></div>
                <div class="title"><p><?= $get_other_pages_result[$i]['name'] ?></p></div>
                <div class="info">
                    <div class="info-grp"><p><?= $get_other_pages_result[$i]['description'] ?></p></div>
                    <div class="link"><a href="./page.php?id=<?= $get_other_pages_result[$i]['id'] ?>">Voir le groupe</a></div>
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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
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


    const logoutBtn = document.getElementById('logout-btn');
    logoutBtn.addEventListener('click', function(){
        window.location.href = '../connections/logout.php';
    })
</script>
</body>
</html>