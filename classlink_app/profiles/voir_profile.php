<?php
session_start();
require '../inc/pdo.php';
require '../../vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

$path_img = 'http://localhost/SocialNetwork-Fullstack-Project/classlink_app/profiles/uploads/';


$utilisateur_id = (int) trim($_GET['id']);


if(empty($utilisateur_id)) {
    header('Location: /membre.php');
    exit;
} 

$requete = $app_pdo->prepare(
    
    "SELECT p.*, r.id_demandeur, r.id_receveur, r.statut, r.id_bloqueur
    FROM profiles p
    LEFT JOIN relation r ON (id_receveur = p.id AND id_demandeur = :id2) OR (id_receveur = :id2 AND id_demandeur = p.id)
    WHERE p.id = :id1"

);
$requete->execute(array('id1' => $utilisateur_id, 'id2'=>$_SESSION['id']));
echo $utilisateur_id;
echo $_SESSION['id'];
$voir_utilisateur = $requete->fetch();

if(!isset($voir_utilisateur['id'])){
    header('Location: /membre.php');
    exit;
}

if(!empty($_POST)) {
    extract($_POST);
    $valid = (boolean) true;

    if(isset($_POST['ajouter_user'])){
        $requete = $app_pdo->prepare('
        SELECT id FROM relation WHERE (id_receveur = ? AND id_demandeur = ?) OR (id_receveur = ? AND id_demandeur = ?)');
        $requete->execute(array($voir_utilisateur['id'], $_SESSION['id'], $_SESSION['id'], $voir_utilisateur['id']));

        $verif_relation = $requete->fetch();
      
        if(isset($verif_relation['id'])) {
            $valid = false;
        }

        if($valid) {
            $requete = $app_pdo->prepare("INSERT INTO relation (id_demandeur, id_receveur, statut) VALUES (?, ?, ?)");
            $requete->execute(array($_SESSION['id'], $voir_utilisateur['id'], 1));
        }
        header('Location: ./voir_profile.php?id=' . $voir_utilisateur['id']);
        exit;

        }elseif(isset($_POST['supprimer_user'])) {

            $requete = $app_pdo->prepare("DELETE FROM relation
            WHERE (id_receveur = ? AND id_demandeur = ?)
            OR (id_receveur = ? AND id_demandeur = ?)"
            );
            $requete->execute(array($voir_utilisateur['id'], $_SESSION['id'], $_SESSION['id'], $voir_utlisateur['id']));
            
            header('Location: ./voir_profile.php?id=' . $voir_utilisateur['id']);
            exit;

    }elseif(isset($_POST['bloquer_user'])) {

        $requete = $app_pdo->prepare("SELECT id 
        FROM relation
        WHERE (id_receveur = :id1 AND id_demandeur = :id2) OR (id_receveur = :id2 AND id_demandeur = :id1)"
            );
            $requete->execute(array('id1' => $voir_utilisateur['id'], 'id2' => $_SESSION['id']));
            
            $verif_relation = $requete->fetch();

            if(isset($verif_relation['id'])){
                $requete = $app_pdo->prepare("UPDATE relation SET id_bloqueur = ? WHERE id = ?");
                $requete->execute(array($voir_utilisateur['id'], $verif_relation['id']));
            }else {
                $requete = $app_pdo->prepare("INSERT INTO relation (id_demandeur, id_receveur, statut, id_bloqueur) VALUES (?,?,?,?)");
                $requete->execute(array($_SESSION['id'], $voir_utilisateur['id,'], 3, $voir_utilisateur['id']));
            }

            header('Location: ./voir_profile.php?id=' . $voir_utilisateur['id']);
            exit;
        }elseif(isset($_POST['debloquer_user'])){

            $requete = $app_pdo->prepare("SELECT id, statut
            FROM relation
            WHERE (id_receveur = :id1 AND id_demandeur = :id2) OR (id_receveur = :id2 AND id_demandeur = :id1)"
            );
    
                $requete->execute(array('id1' => $voir_utilisateur['id'], 'id2' => $_SESSION['id']));
                
                $verif_relation = $requete->fetch();

                if(isset($verif_relation['id'])){
                    if($verif_relation['statut'] == 3){
                        $req = $app_pdo->prepare("DELETE FROM relation WHERE id = ?");
                        $requete->execute(array($verif_relation['id']));
                    }else{
                        $requete = $app_pdo->prepare("UPDATE relation SET id_bloqueur = ? WHERE id = ?");
                        $requete->execute(array(NULL, $verif_relation['id']));
                    }

                }
                header('Location: ./voir_profile.php?id=' . $voir_utilisateur['id']);
                exit;
        }

}


?>

<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de<?= $voir_utilisateur['firt_name']?></title>
</head>
<body>
    
<div class='container'>

   
        <div>
            <?= $voir_utilisateur['id'] ?>
        </div>
        <div>
            <?= $voir_utilisateur['last_name'] ?>
        </div>
        <div>
            <?= $voir_utilisateur['first_name'] ?>
        </div>
        <div>
            <?= $voir_utilisateur['birth_date'] ?>
        </div>
        <div>
            <?= $voir_utilisateur['gender'] ?>
        </div>
        <div>
            <?= $voir_utilisateur['mail'] ?>
        </div>
        <div>
            <?= $voir_utilisateur['pp_image'] ?>
        </div>
        <div>
            <?= $voir_utilisateur['banner_image'] ?>
        </div>


            

       

</body>
</html> -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../../assets/css/profile.css" rel="stylesheet"></link>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <title>Document</title>
</head>
<body>
<header>
        <li>
            <a href="" data-bs-toggle="modal" data-bs-target="#exampleModal">Menu</a>
            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">MENU</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <a href="./profile.php">Mon profil</a>
                    <a href="./demandes.php.php">Demandes</a>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
                <div>
                <button type="button" class="btn btn-primary">Save changes</button>
                </div>
                </div>
            </div>
            </div>
        </li>
    </header>


    <div class='header-profile'>
        <div class='banner'  id="mabanner" style="background: url('<?= $path_img.$banner_image ?>')">>
            <div class="btn"><button>Modifier le profil</button></div>
            <div class="name"><p><?= $voir_utilisateur['last_name'] . $voir_utilisateur['first_name'] ?></p></div>
        </div>

        <div class="pp"><img src="<?= $path_img . $voir_utilisateur['pp_image'] ?>" alt=""></div>

        <div class="link-list">
            <div class="empty"></div>
            <div><a href=""><p>Relations</p></a></div>
            <div><a href=""><p>Groupes</p></a></div>
            <div><a href=""><p>Pages</p></a></div>
            <div><a href=""><p>Paramètres</p></a></div>
        </div>
    </div>

    <div>   
        <form method="POST">
            <?php 
                if(!isset($voir_utilisateur['statut'])){
            ?>
            <input type="submit" name="ajouter_user" value="Ajouter">
            <?php 
                }elseif(isset($voir_utilisateur['statut']) && $voir_utilisateur['id_demandeur'] == $_SESSION['id'] && !isset($voir_utilisateur['id_bloqueur']) && $voir_utilisateur['statut'] <> 2){
            ?>
            <div>Demande en attente</div>
            <?php
                }elseif(isset($voir_utilisateur['statut']) && $voir_utilisateur['id_receveur'] == $_SESSION['id'] && !isset($voir_utilisateur['id_bloqueur']) && $voir_utilisateur['statut'] <> 2){
            ?>
            <div>Vous avez une demande à accepter</div>
            <?php
                }elseif(isset($voir_utilisateur['statut']) && $voir_utilisateur['statut'] == 2 && !isset($voir_utilisateur['id_bloqueur'])){
            ?>
            <div>Vous êtes amis</div>
            <?php
                }
                if(isset($voir_utilisateur['statut']) && !isset($voir_utilisateur['id_bloqueur']) && $voir_utilisateur['id_demandeur'] == $_SESSION['id'] && $voir_utilisateur['statut'] <> 2){
            ?>
            <input type="submit" name="supprimer_user" value="Supprimer">
            <?php
                }
                if((isset($voir_utilisateur['statut']) || $voir_utilisateur['statut'] == NULL) && !isset($voir_utilisateur['id_bloqueur'])){
            ?>
            <input type="submit" name="bloquer_user" value="Bloquer">
            <?php
                }elseif($voir_utilisateur['id_bloqueur'] <> $_SESSION['id']){
            ?>
            <input type="submit" name="debloquer_user" value="Débloquer">
            <?php 
                }else{
            ?>
            <div>Vous avez été bloqué par cette utilisateur</div>
            <?php
                }
            ?>
        </form>
    </div>


    <div class="options-left">
        <div class="personnal">
            <div class="title"><h4>Informations personelles</h4></div>
            <div class="separator"></div>
            <div><p>Age <span>: <?= $voir_utilisateur['birth_date'] ?></span></p></div>
            <div><p>Genre <span>: <?= $voir_utilisateur['gender'] ?></span></p></div>
            <div><p>E-mail<span>: <?= $voir_utilisateur['mail'] ?></span></p></div>
        </div>
    </div>
    <div class="btn2"><a href=""><button>Se déconnecter</button></a></div>
    <!-- <div class="create-post" id='create-post'>
        <div class="pp-post"><img src="<?= $path_img . $voir_utilisateur['pp_image'] ?>" alt=""></div>
        <div class="fake-input"><p>Exprimez-vous...</p></div>
    </div>
    <div class="post">
        <div class="top-post">
            <div class="post-pp"><img src="<?= $path_img . $voir_utilisateur['pp_image'] ?>" alt=""></div>
            <div class="post-name"><p>Djedje Gboble</p></div>
            <div class="post-text"><p><?= $voir_publi['text']?></p></div>
            <div class="post-date"><p>Le 27/06/2023, à 22:47</p></div>
            <div class="plus"><p>Afficher plus...</p></div>
        </div>
        <div class="img-post" id="picspost" style="background-image: url('<?= $path_img.$image ?>')"></div>
        <div class="bottom-post">
            <div class="up">
                <div class="nb-like">
                    <div><img src="../../assets/img/thumbs-up.svg" alt=""></div>
                    <div><img src="../../assets/img/heart.svg" alt=""></div>
                    <div><img src="../../assets/img/smile.svg" alt=""></div>
                    <div class="nb"><p>3125</p></div>
                </div>
                <div class="nb-comments"><p>134 Commentaires</p></div>
            </div>
            <div class="down">
                <div class='left'>
                    <div><img src="../../assets/img/thumbs-up.svg" alt=""></div>
                    <div class="text"><p>J'aime</p></div>
                </div>
                <div class="right">
                    <div><img src="../../assets/img/comment.svg" alt=""></div>
                    <div class="text"><p>Commenter</p></div>
                </div>
            </div>
        </div>
    </div> -->
    <div class="footer">
        <p>Infos    Assistance   Accessibilité  
            Conditions générales 
            Confidentalité
            Contacter l’équipe
            Solutions professionelles  
            
            ClassLink Corporation © 2023</p>
    </div>
    <!-- <div class='overlay'>
        <div class="photo_text">
            <div class="overlay_pp"><img src="../../assets/img/default_pp.jpg" alt="Profile Picture"></div>
            <textarea name="message" id="" placeholder="Exprimez vous...">
            </textarea>
        </div>
        <div class='separator'></div>
        <div class='send'>
            <div class="upload"><input type="file"></div>
            <div class="post"><button>Poster</button></div>
        </div>
    </div> -->

  
    </div>
    <script src="../../assets/js/profile.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>