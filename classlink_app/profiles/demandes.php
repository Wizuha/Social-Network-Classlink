<?php
    session_start();
    require '../inc/pdo.php';



    $requete = $app_pdo->prepare("SELECT r.id, p.last_name, p.id id_utilisateur
    FROM relation r
    INNER JOIN profiles p ON p.id = r.id_demandeur
    WHERE r.id_receveur = ? AND r.statut = ?"

    );
    $requete->execute(array($_SESSION['id'], 1));


    $afficher_demandes = $requete->fetchAll();

    if(!empty($_POST)) {
        extract($_POST);
        $valid = (boolean) true;

        if(isset($_POST['accepter'])){


            $id_relation = (int) $id_relation;
                
                
                if($id_relation > 0){
                
                $requete = $app_pdo->prepare('
                SELECT id 
                FROM relation 
                WHERE id = ? AND statut = 1');
                $requete->execute(array($id_relation));

                $verif_relation = $requete->fetch();

                if(!isset($verif_relation['id'])){
                $valid = false;  
                }
                if($valid){
                    $requete = $app_pdo->prepare("UPDATE relation SET statut = 2 WHERE id = ? AND id_receveur = ?");
                    $requete->execute(array($id_relation, $_SESSION['id']));
                }
            }
            header('Location: ./demandes.php');
            exit;
        }

            }elseif(isset($_POST['refuser'])) {

                $id_relation = (int) $id_relation;
                
                
                if($id_relation > 0){

                    $requete = $app_pdo->prepare("DELETE FROM relation
                    WHERE id = ? AND id_receveur = ?"
                    );
                    $requete->execute(array($id_relation, $_SESSION['id']));
                }

                
                header('Location: ./demandes.php');
                exit;
            }



?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../../assets/css/profile.css" rel="stylesheet"></link>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <title>Demandes d'amis</title>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
                </div>
            </div>
            </div>
        </li>
    </header>

        <div class="link-list">
            <div class="empty"></div>
            <div><a href=""><p>Relations</p></a></div>
            <div><a href=""><p>Groupes</p></a></div>
            <div><a href=""><p>Pages</p></a></div>
            <div><a href=""><p>Paramètres</p></a></div>
        </div>
    </div>

   
    <div>
        <?php 
        foreach($afficher_demandes as $ad){
        ?>
        <div>
            <?= $ad['last_name'] ?>
        </div>
        <div>
            <a href="voir_profile.php?id=<?= $ad['id_utilisateur']?>">Voir</a>
        </div>
        <div>
            <form method="POST">
            <input type="hidden" name="id_relation" value="<?= $ad['id'] ?>"/>
            <input type="submit" name="accepter" value="Accepter"/>
            <input type="submit" name="refuser" value="Refuser"/>
            </form>
        </div>
    </div>
        <?php
        }
        ?>


    <div class="btn2"><a href=""><button>Se déconnecter</button></a></div>
   
    <div class="footer">
        <p>Infos    Assistance   Accessibilité  
            Conditions générales 
            Confidentalité
            Contacter l’équipe
            Solutions professionelles  
            
            ClassLink Corporation © 2023</p>
    </div>
  
  
    </div>
    <script src="../../assets/js/profile.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>