<?php 
session_start();
require '../inc/pdo.php';
require '../inc/functions/token_functions.php';
require '../inc/tpl/data_user.php';


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



if(isset($_GET['id'])){
    $group_ID = $_GET['id'];
}

// Vérifie si l'utilisateur est abonné au groupe si celui si est privé 


$check_status_group = $app_pdo->prepare("
SELECT status
FROM groups_table
WHERE id = :id ;
");
$check_status_group->execute ([
    ":id" => $group_ID
]);

$check_status_group_result = $check_status_group->fetch(PDO::FETCH_ASSOC);

if($check_status_group_result['status'] == 'prive' || $check_status_group_result['status'] == 'private'){
    // Check si lutilisateur est bien abonné au groupe
    $check_user_following = $app_pdo->prepare("
    SELECT * FROM group_members WHERE group_id = :group_id AND profile_id = :profile_id
    ");

    $check_user_following->execute([
        'group_id' => $group_ID,
        'profile_id' => $_SESSION['id']
    ]);
    
    $check_user_following_result = $check_user_following->fetch(PDO::FETCH_ASSOC);
    if(!$check_user_following_result && ($check_status_group_result['status'] == 'prive' || $check_status_group_result['status'] == 'private')){
        $forbidden = true;
    }

}


// Liste de tous les membres abonnés qui suivent le groupe

$list_folowers = $app_pdo->prepare('
SELECT profiles.id, last_name, first_name, pp_image
FROM profiles
LEFT JOIN group_members ON profiles.id = group_members.profile_id
WHERE group_members.group_id = :group_id
');

$list_folowers->execute([
    ':group_id' => $group_ID
]);

$list_folowers_result = $list_folowers->fetchAll(PDO::FETCH_ASSOC);


$id = $_SESSION['id'];
$method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
if($method == 'POST'){
    $add = filter_input(INPUT_POST,"add");
    $group_ID = $_GET['id'];
    $verify_member = $app_pdo -> prepare('
        SELECT * FROM group_members WHERE group_id = :group_id 
        AND profile_id = :profile_id;
    ');
    $verify_member->execute([
        ':profile_id'=>$_SESSION['id'],
        ':group_id'=>$id
    ]);
    $verify = $verify_member->fetch(PDO::FETCH_ASSOC);


    if(!$verify){
            
        $add_member_query = $app_pdo->prepare('
        INSERT INTO group_members (group_id, profile_id)
        VALUES (:group_id, :profile_id);');
        $add_member_query->execute([
            ':group_id' => $group_ID,
            ':profile_id' => $_SESSION['id']
        ]);
        echo 'Vous avez rejoint le groupe.';
    }
    else{
        echo 'Vous êtes déjà dans le groupe.';
        }
}


// Vérification du statut de l'utilisateur qui se connecte sur la page
$verify_creator = $app_pdo->prepare('
SELECT * FROM groups_table 
WHERE id = :id AND creator_profile_id = :creator_profile_id
');
$verify_creator->execute([
    ':id'=>$_GET['id'],
    ':creator_profile_id'=>$_SESSION['id']
]);
$verify_creator_result = $verify_creator->fetch(PDO::FETCH_ASSOC);



if(isset($_SESSION['id'])){    
    
    $request_page_data = $app_pdo->prepare("
    SELECT name, banner_image, description,creator_profile_id,status
    FROM groups_table
    WHERE id = :id ;
    ");
    $request_page_data->execute ([
        ":id" => $group_ID
    ]);

    $result = $request_page_data->fetch(PDO::FETCH_ASSOC);
    if($result){
        $name = $result['name'];
        $banner_image = $result['banner_image'];
        $description = $result['description'];
        $creator_profile_id = $result['creator_profile_id'];
        $statut = $result['status'];

        if(!$banner_image){
            $banner_image = 'default_banner.jpg';
        }

        $request_creator_data = $app_pdo->prepare('
        SELECT last_name, first_name
        FROM profiles
        WHERE id = :creator_profile;
        ');
        $request_creator_data->execute([
            ":creator_profile" => $creator_profile_id
        ]);
        $result2 = $request_creator_data->fetch(PDO::FETCH_ASSOC);
        $last_name = $result2 ['last_name'];
        $first_name = $result2['first_name'];
    }else {
        echo 'error';
    }

}

if($method == 'POST'){
    $input = trim(filter_input(INPUT_POST, "input",));
    $publication_submit = filter_input(INPUT_POST, "publication_submit");
    $publication_content = filter_input(INPUT_POST, "publication_content");        

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
    INSERT INTO publications_group (profile_id, group_id, image, text)
    VALUES (:user_id, :group_id, :image, :publication_content)
    ");

    $add_page_publication -> execute([
        ":user_id" => $_SESSION['id'],
        ":group_id" => $group_ID,
        ":publication_content" => $publication_content,
        ":image" => $new_img_name
]);

    header('Location: ' . $_SERVER['PHP_SELF'] . "?id=" . $group_ID);
    exit();
}


}

//Récupérer les publications du groupe

$publication_group = $app_pdo->prepare('
SELECT publications_group.*, pp_image, last_name, first_name FROM profiles 
JOIN publications_group ON publications_group.profile_id = profiles.id
WHERE group_id = :group_id
');

$publication_group->execute([
    ':group_id' => $group_ID
]);

$publication_group_result = $publication_group->fetchAll(PDO::FETCH_ASSOC);


$logo = '../../assets/img/white_logo.svg';
$header_pp_image = $path_img . $my_pp_image;
$tv = '../../assets/img/tv-header.svg';
$bell = '../../assets/img/bell.svg';
$messages = '../../assets/img/messages.svg';

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/group.css">
    <link rel="stylesheet" href="../../assets/css/header.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">

    <title>Document</title>
</head>
<body>
<?php include '../inc/tpl/header.php' ?>
    <main>
        <div class="container">
            <div class="top-container">
                <div class="banner_button" style="background-image : url('<?= $path_img . $banner_image ?>')">
                    <div class="button">
                        <div class="name"><p><?php echo $name ?></p></div>
                        <div class="list-button">
                        <?php if($verify_creator_result){ ?>
                            <div class="duo-btn"><a href="./modify_group.php?id=<?= $group_ID ?>"><button>Modifier le groupe</button></a><a href="./asked_member.php?id=<?php echo $group_ID?> "><button>Voir demande en attente</button></a></div>
                        <?php }elseif(isset($forbidden)){ ?>
                            <div class="duo-btn"><a href="./asked_group.php?id=<?php echo $group_ID ?>"><button>Suivre</button></a></div>
                        <?php }else{ ?>
                            <div class="duo-btn"><button>Ne plus suivre</button></div>
                         <?php } ?>

                        </div>
                    </div>
                </div>
                <div class="info">
                    <ul>
                        <li>Membre</li>
                        <li>Evenements</li>
                        <li>Actualite</li>
                        <li>Media</li>
                    </ul> </div>
            </div>
            <?php if(!isset($forbidden)){ ?>
            <div class="mid-container">
                <div class="apropos">
                    <h1>A propos</h1>
                    <p><?php echo $description ?></p>
                    <div class="detail">
                        <p><span style="font-weight: 500;">Statut: </span> <?php echo $statut ?></p>
                        <p><span style="font-weight: 500;">Créer par : </span><?php echo $first_name ." ". $last_name  ?></p>
                        <p><span style="font-weight: 500;">Statut:</span> <?php echo $check_status_group_result['status'] ?></p>
                    </div>
                    
                </div>
                <div class="fil" >
                    <div class="exprimez-vous">
                        <div class="profile" >
                            <img src="<?php echo $header_pp_image ?>" alt="">
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data">
                                <input type="text" id="publication_content" name="publication_content" placeholder="Exprimez-vous..."> 
                                <input type="file" id="publication_image" name="publication_image">
                                <input type="submit" id="publication_submit" name="publication_submit" value="Publier">
                        </form>
                    </div>
                    <div class="filpost" id="fil"> 
                    <?php if ($publication_group_result){
                            foreach($publication_group_result as $post){?>
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
                </div>
                <div class="suggestion">
                    <h1>Membre</h1>
                    <?php if($list_folowers_result){ ?>
                    <?php foreach ($list_folowers_result as $follower){?>
                    <div class="people">
                        <div class="profile" >
                            <img src="<?= $path_img . $follower['pp_image'] ?>" alt="">
                        </div>
                        <p>
                         <?= $follower['first_name'] . ' ' . $follower['last_name']; ?>
                        </p>
                    <?php }}else{ ?>
                        <p>Ce groupe ne contient aucun membre</p>
                   <?php }}else{?>
                    <p>Ce groupe est privé, abonnez-vous pour le rejoindre</p>
                    <?php } ?>
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
    </script>
</body>
</html>