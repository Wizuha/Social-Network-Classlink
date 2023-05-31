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
            header('Location: ./settings.php');
            exit();        
        }
    }elseif(!isset($_SESSION['token'])){
        header('Location: ../connections/login.php');
        exit();
    }

    $activate_reactivate_button = "";

    if ($_SESSION['profile_status'] == 'Actif') {
        $activate_reactivate_button = "Désactiver le compte";
    } elseif ($_SESSION['profile_status'] == 'Inactif') {
        $activate_reactivate_button = "Réactiver le compte";
        $deactivate = true;
    }

    $messaging_path = '../../messagerie_websocket/index_messagerie.php';
    $dashboard_path = '../dashboard.php';
    $settings_path = './settings.php';
    $page_css_path = '../../assets/css/settings.css';
    $header_css_path = '../../assets/css/header.css';
    $profile_path = './profile.php';
    $page_title = 'Paramètre - ClassLink';
    $page_dashboard_path = '../pages/page_dashboard.php';
    $path_img = 'http://localhost/SocialNetwork-Fullstack-Project/classlink_app/profiles/uploads/';
    $header_pp_image = $path_img.$pp_image;
    $logo = '../../assets/img/white_logo.svg';
    $tv = '../../assets/img/tv-header.svg';
    $bell = '../../assets/img/header-bell.svg';
    $messages = '../../assets/img/messages-icon.svg';

    require '../inc/tpl/header.php';
?>

    <main class="settings-page-content">
        <div class="settings-infos">
            <div class="profile-info-head">
                <div><p>Informations du profil</p></div>
                <?php if(isset($deactivate)): ?>
                    <div><h3 class="error">Votre compte est inactif veuillez le réactiver.</h3></div>
                <?php endif; ?>
            </div>
            
            <div class="main">
                <div class="user-infos">
                    <div><p>Nom : </p></div>
                    <div><p>Prénom : </p></div>
                    <div><p>Age : </p></div>
                    <div><p>Genre : </p></div>
                    <div><p>Email : </p></div>
                    <div><p>Identifiant : </p></div>
                    <div><p>Mot de passe : </p></div>
                </div>
                <div class='user-response'>
                    <div><p><?= $lastname ?></p></div>
                    <div><p><?= $firstname ?></p></div>
                    <div><p><?= $age ?> ans</p></div>
                    <div><p><?= $gender ?></p></div>
                    <div><p><?= $mail ?></p></div>
                    <div><p><?= $username ?></p></div>
                    <div><p>******</p></div>
                </div>
            </div>
            <div class='footer'>
                <div><button id="modify-profile-btn">Modifier les informations</button></div>
            </div>
        </div>
        <div class="settings-profil">
            <div class="banner_button">
                <div class="banner">
                    <img src="<?= "$path_img$banner_image"?>" alt="" />
                </div>
                <div class="buttons">
                    <div class="div-buttons">
                        <div class="btn2"><button id="modify-profile-images">Modifier les photos de profil</button></div>
                    </div>
                </div>
                <div class="pp"><img src="<?= "$path_img$pp_image"?>" alt=""></div>
            </div>
            <div class="activity">
                <div class="profile-info-head"><div><p>Activité du profil</p></div></div>
                <div class="main">
                    <div class="list">
                        <div class="element-list"><div class=><p>Relations :</p></div><div><span><?= $numbers_of_relations ?></span></div></div>
                        <div class="element-list"><div><p>Groupes :</p></div><div><span><?= $numbers_of_groups ?></span></div></div>
                        <div class="element-list"><div><p>Pages :</p></div><div><span><?= $numbers_of_pages ?></span></div></div>
                        <div class="element-list"><div><p>Nombre de posts :</p></div><div><span><?= $numbers_of_publications ?></span></div></div>
                    </div>
                </div>
                <div class="bottom">
                    <div class="button-list">
                        <div  class="btn1"><button id="account-deactivation-btn"><?= $activate_reactivate_button ?></button></div>
                        <div  class="btn2"><button id="account-delete-btn">Supprimer le compte</button></div>
                    </div>
                <div class="btn3"><button id="logout-btn">Se déconnecter</button></div>
                </div>
            </div>
        </div>
    </main>
    <script>

        let inputBox = document.querySelector(".input-box"),
            search = document.querySelector(".search"),
            closeIcon = document.querySelector(".close-icon")

        search.addEventListener("click", () => inputBox.classList.add("open"))
        closeIcon.addEventListener("click", () => inputBox.classList.remove("open"))

        const modifyProfileBtn = document.getElementById('modify-profile-btn');
        modifyProfileBtn.addEventListener('click', () => {
            window.location.href = './settings_edition_mode.php';
        })

        const modifyProfileImages = document.getElementById('modify-profile-images');
        modifyProfileImages.addEventListener('click', () => {
            window.location.href = './modify_profile_images.php';
        })

        const logoutBtn = document.getElementById('logout-btn');
        logoutBtn.addEventListener('click', () => {
            window.location.href = '../connections/logout.php';
        })

        $(document).ready( () => {
            $('#account-deactivation-btn').click(function() {
                $.ajax({
                    url: './scriptphp/deactivate_account.php',
                    type: 'POST',
                    success: function(response) {
                        response = JSON.parse(response);
                        if (response.status == 'success' && response.response == 'Inactif') {
                            window.location.href =  '../connections/logout.php';
                        } else if (response.status == 'success' && response.response == 'Actif') {
                            window.location.href = '../dashboard.php';
                        }
                    },
                    error: function() {
                        console.log("Une erreur s'est produite lors de la requete");
                    }
                })
            })
            
            $('#account-delete-btn').click(function() {
                $.ajax({
                    url: './scriptphp/delete_account.php',
                    type: 'POST',
                    success: function(response) {
                        response = JSON.parse(response);
                        if (response.status == 'success') {
                            window.location.href = "../connections/logout.php";
                        } else if (response.status == 'error') {
                            console.log(response.message)
                        }
                    },
                    error: function() {
                        console.log("Une erreur s'est produite lors de la requete");
                    }
                })
            })
        })

        const messagingIconLink = document.getElementById('message-link');
        messagingIconLink.addEventListener('click', () => {
            window.location.href = '<?= $messaging_path ?>';
        })
    </script>
</body>
</html>