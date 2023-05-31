<?php
    session_start();
    require '../inc/pdo.php';
    require '../inc/functions/token_functions.php';
    require '../../vendor/autoload.php';
    require '../inc/tpl/data_user.php';

    use GuzzleHttp\Client;
    use GuzzleHttp\RequestOptions;
    if(isset($_SESSION['token'])){
        $check = token_check($_SESSION["token"], $auth_pdo);
        if($check == 'false'){
            header('Location: ./connections/login.php');
            exit();
        } elseif($_SESSION['profile_status'] == 'Inactif') {
            header('Location: ./settings.php');
            exit();        
        }
    }elseif(!isset($_SESSION['token'])){
        header('Location: ./connections/login.php');
        exit();
    }

    $method = filter_input(INPUT_SERVER, "REQUEST_METHOD");

    // $_SESSION['id'] = 78;
    $client = new \GuzzleHttp\Client();
    if(isset($_SESSION['id'])) {
        
    $response = $client->post('http://localhost/SocialNetwork-Fullstack-Project/classlink_app/profiles/upload.php');


        $requete = $app_pdo->prepare("
            SELECT last_name, first_name, birth_date, gender, mail, pp_image,banner_image, username FROM profiles WHERE id = :id;
        ");

        $requete->execute([
            ":id" => $_SESSION['id']
        ]);

        $result = $requete->fetch(PDO::FETCH_ASSOC);
        if($result){
            $last_name = $result['last_name'];
            if ($last_name == null) {
                $last_name = 'Non renseigné';
            }
            $first_name = $result['first_name'];
            if ($first_name == null) {
                $first_name = 'Non renseigné';
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
        } else {
            echo'erreur';
        }
    }

    if(isset($_SESSION['id'])) {
        $requete = $app_pdo->prepare(
        // SELECT image FROM profiles LEFT JOIN publications_profile ON profiles.id = profile_id WHERE id = :id;
        "SELECT profile_id, image, text FROM profiles LEFT JOIN publications_profile ON profiles.id = publications_profile.profile_id WHERE profiles.id = :id;
        ");
        $requete->execute([
            ":id" => $_SESSION['id']
        ]);
        $result = $requete->fetch(PDO::FETCH_ASSOC);
        if($result){
        $profile_id = $result['profile_id'];
        $text = $result['text'];
        $image = $result['image'];
        $text =  $result['text'];

    } else {
        echo'erreur publications';
    }
}   
//recup les img apres les upload
if (isset($_POST['submit']) && isset($_FILES['file'])) {
    $img_name = $_FILES['file']['name'];
    $img_size = $_FILES['file']['size'];
    $tmp_name = $_FILES['file']['tmp_name'];
    $error = $_FILES['file']['error'];
    $img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
    $img_ex_lc = strtolower($img_ex);

    $new_img_name = uniqid("IMG-", true) . '.' . $img_ex_lc;
    $img_upload_path = 'uploads/' . $new_img_name;

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
// recup les publication

if(isset($_POST['publication_titre'], $_POST['publication_contenu']))
    if(!empty($_POST['publication_titre']) AND !empty($_POST['publication_contenu'])) {
        
        $publication_titre = ($_POST['publication_titre']);
        $publication_contenu = ($_POST['publication_contenu']);
       
        $requete = $app_pdo->prepare("INSERT INTO test_publications (titre, text, image, date_time_publication, profile_id)
        VALUES (?, ?, ?, NOW(), ?)");
        $requete->execute(array($publication_titre, $publication_contenu,$new_img_name, $_SESSION['id']));
        var_dump($requete);
        $message = "Votre article a bien été posté";

    } else {
        $message = 'Veuillez remplir tout les champs';
    }

    // afficher chaque publications
    if(isset($_SESSION['id'])) {
        $afficher_publications = $app_pdo->prepare("SELECT * FROM test_publications WHERE id <> ?;");
        $afficher_publications->execute(array($_SESSION['id']));

        $result_pub = $afficher_publications->fetch(PDO::FETCH_ASSOC);
        if($result_pub){
            $id_pub = $result_pub['id'];
            var_dump($result);
        }
        
        $likes = $app_pdo->prepare("SELECT id FROM likes WHERE test_publications_id = ?");
        $likes->execute(array($id_pub));
        $likes = $likes->rowCount();
        var_dump($likes);


        $dislikes = $app_pdo->prepare("SELECT id FROM dislikes WHERE test_publications_id = ?");
        $dislikes->execute(array($id_pub));
        $dislikes = $dislikes->rowCount();

        $comment = $app_pdo->prepare('SELECT * FROM comments_publications WHERE id_publication = ?');
        $comment->execute(array($id_pub));

        if(isset($_POST['submit_commentaire'])){
            if(isset($_POST['pseudo'],$_POST['commentaire']) AND !empty($_POST['pseudo']) AND !empty($_POST['commentaire'])){
                $pseudo = $_POST['pseudo'];
                $commentaire = $_POST['commentaire'];

                if(strlen($pseudo) < 25 ) {

                    $ins = $app_pdo->prepare('INSERT INTO comments_publications(pseudo, commentaire, id_publication) VALUES (?,?,?)');
                    $ins->execute(array($pseudo, $commentaire,$id_pub));
                    
                    $c_msg = 'Votre commentaire a était poster';

                    
                }else {
                $c_msg = "Erreur : Le pseudo doit faire moins de 25 caractères";
                }


            }else{
                $c_msg = "Erreur : Tous les champs doivent être complétés";
            }
        }
        
    } else {
        $afficher_publications = $app_pdo->prepare("SELECT * FROM test_publications;");
        $afficher_publications->execute();
    }


    if($_SESSION['profile_status'] == 'Inactif') {
        header('Location: ./profiles/settings.php');
        exit();        
    }

    $dashboard_path = '../dashboard.php';
    $settings_path = './settings.php';
    $page_css_path = '../../assets/css/profilecopie.css';
    $header_css_path = '../../assets/css/header.css';
    $profile_path = './profile.php';
    $page_title = 'Profil - Classlink';
    $page_dashboard_path = '../pages/page_dashboard.php';
    $logo = '../../assets/img/white_logo.svg';
    $tv = '../../assets/img/tv-header.svg';
    $bell = '../../assets/img/header-bell.svg';
    $messages = '../../assets/img/messages-icon.svg';

    include '../inc/tpl/header.php';
?>
    <a href="./profile.php">Mon profil</a><br>
    <a href="./demandes.php">Mes demandes</a>
    <div class='header-profile'>
        <div class='banner'  id="mabanner" style="background: url('<?= $path_img.$banner_image ?>')">
            <!-- <img src="" alt="banner"> -->
            <div id="modify-btn" class="btn"><button>Modifier le profil</button></div>
            <div class="name"><p><?php echo $first_name.' '.$last_name?></p></div>
        </div>
        <div class="pp"><img src="<?php echo $path_img . $pp_image ?>" alt=""></div>
        <div class="link-list">
            <div class="empty"></div>
            <div><a href="./membre.php"><p>Membres</p></a></div>
            <div><a href=""><p>Groupes</p></a></div>
            <div><a href=""><p>Pages</p></a></div>
            <div><a href="./settings.php"><p>Paramètres</p></a></div>
        </div>
    </div>
    <div class="options-left">
        <div class="personnal">
            <div class="title"><h4>Informations personelles</h4></div>
            <div class="separator"></div>
            <div><p>Age <span>: <?php echo $age ?></span></p></div>
            <div><p>Genre <span>: <?php echo $gender ?></span></p></div>
            <div><p>E-mail<span>: <?php echo $mail ?></span></p></div>
        </div>
    </div>
    <!-- <div class="btn2"><a href=""><button>Se déconnecter</button></a></div> -->
    <div class="create-post" id='create-post'>
        <div class="pp-post"><img src="<?php echo $path_img . $pp_image ?>" alt=""></div>
        <!-- <div class="fake-input"><p>Exprimez-vous...</p></div> -->
    </div>

    <div>
       
        <form method="POST" enctype="multipart/form-data">

            <input type="text" name="publication_titre" placeholder="Titre" />

            <textarea name="publication_contenu" placeholder="Exprimez-vous..."></textarea>

            <input type="file" name="file">

            <input type="submit" value="Publier" name="submit">

        </form>
        <?php if(isset($message)) { echo $message; } ?>
    </div>
    <div class="actualite">
    <?php
                foreach($afficher_publications as $afp) 
            ?>
            <div class="post">
                <div class="top-post">
                    <div class="post-pp"><img src="<?php echo $path_img . $pp_image ?>" alt=""></div>
                    <div class="post-text"><p><?= $afp['text']?></p></div>
                    <div class="post-date"><p>Le <?= $afp['date_time_publication']?></p></div>
                
                </div>
                <div class="img-post" id="picspost" style="background-image: url('<?= $path_img.$afp['image'] ?>')"></div>
            
                <div class="bottom-post">
                    <div class="up">
                        <div class="nb-like">

                        </div>

                    </div>
                </div>
            </div>
        <?php
                    
            ?>
    </div>
    
     <div class='overlay'>
        <div class="photo_text">
            <div class="overlay_pp"><img src="../../assets/img/default_pp.jpg" alt="Profile Picture"></div>
            <textarea name="message" id="" placeholder="Exprimez vous...">
            </textarea>
        </div>
        
        
    </div> 

    <div>
        <a href="./action.php?t=1&id=<?= $_SESSION['id'] ?>"> </a> (<?= $likes ?>)
        <br>    
        <a href="./action.php?t=2&id=<?= $_SESSION['id'] ?>">Je n'aime pas</a>  (<?= $dislikes?>)
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
    <?php }?>    

    <script src="../../assets/js/profile.js"></script>
    <script src="../../assets/js/notifications.js"></script>
    <script>
        const modifyInfoBtn = document.getElementById('modify-btn');
        modifyInfoBtn.addEventListener('click', () => {
            window.location.href = './change_settings.php';
        })

        const logoutbtn = document.getElementById('logout-btn');
        logoutbtn.addEventListener('click', () => {
            window.location.href = '../logout.php'
        })
    </script>
</body>
</html>

