<?php 
session_start();
require '../inc/pdo.php';
require '../inc/functions/token_functions.php';

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

}?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/group.css">
    <title>Document</title>
</head>
<body>
    <header>
    </header>
    <main>
        <div class="container">
            <div class="top-container">
                <div class="banner_button">
                    <div class="button">
                        <div class="name"><p><?php echo $name ?></p></div>
                        <div class="list-button">
                            <div class="duo-btn"><button>Inviter</button><button id='join'>Rejoindre</button></div>
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
            <div class="mid-container">
                <div class="apropos">
                    <h1>A propos</h1>
                    <p><?php echo $description ?></p>
                    <div class="detail">
                        <p><span style="font-weight: 500;">Statut: </span> <?php echo $statut ?></p>
                        <p><span style="font-weight: 500;">Créer le : </span>XX/XX/XX</p>
                        <p><span style="font-weight: 500;">Créer par : </span><?php echo $first_name ." ". $last_name  ?></p>
                        <p><span style="font-weight: 500;">Statut: </span>Public</p>
                    </div>
                    
                </div>
                <div class="fil" >
                    <div class="exprimez-vous">
                        <div class="profile" >
                            <img src="https://i.pinimg.com/originals/0b/8c/08/0b8c081b7b05dcc0aad6238856ea87d2.gif" alt="">
                        </div>
                        <input type="text" name="" id="" placeholder="Exprimez-vous">
                    </div>
                    <div class="filpost" id="fil"> 
                        <div class='post'>
                            <div class='people'>
                                <div class='profile' >
                                    <img src='https://i.pinimg.com/originals/0b/8c/08/0b8c081b7b05dcc0aad6238856ea87d2.gif' alt=''>
                                </div>
                                <p>Lucas</p>
                            </div>
                            <p>orem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque risus ex, mattis et erat nec, eleifend eleifend lacus</p>
                            <img src='https://www.slate.fr/sites/default/files/photos/gif11_0.gif' alt=''>
                            <div class='reaction'><p>like</p><p>commentaire</p></div>
                        </div>    
                    </div>
                </div>
                <div class="suggestion">
                    <h1>Membre</h1>
                    <div class="people">
                        <div class="profile" >
                            <img src="https://i.pinimg.com/originals/0b/8c/08/0b8c081b7b05dcc0aad6238856ea87d2.gif" alt="">
                        </div>
                        <p>Lucas</p>
                    </div>
                    <div class="people">
                        <div class="profile" >
                            <img src="https://i.pinimg.com/originals/0b/8c/08/0b8c081b7b05dcc0aad6238856ea87d2.gif" alt="">
                        </div>
                        <p>Lucas</p>
                    </div>
                    <div class="people">
                        <div class="profile" >
                            <img src="https://i.pinimg.com/originals/0b/8c/08/0b8c081b7b05dcc0aad6238856ea87d2.gif" alt="">
                        </div>
                        <p>Lucas</p>
                    </div>
                    <div class="people">
                        <div class="profile" >
                            <img src="https://i.pinimg.com/originals/0b/8c/08/0b8c081b7b05dcc0aad6238856ea87d2.gif" alt="">
                        </div>
                        <p>Lucas</p>
                    </div>
                    <div class="people">
                        <div class="profile" >
                            <img src="https://i.pinimg.com/originals/0b/8c/08/0b8c081b7b05dcc0aad6238856ea87d2.gif" alt="">
                        </div>
                        <p>Lucas</p>
                    </div>
                    <div class="people">
                        <div class="profile" >
                            <img src="https://i.pinimg.com/originals/0b/8c/08/0b8c081b7b05dcc0aad6238856ea87d2.gif" alt="">
                        </div>
                        <p>Lucas</p>
                    </div>
                    <div class="people">
                        <div class="profile" >
                            <img src="https://i.pinimg.com/originals/0b/8c/08/0b8c081b7b05dcc0aad6238856ea87d2.gif" alt="">
                        </div>
                        <p>Lucas</p>
                    </div>
                </div>
                
            </div>
        </div>
    </main>
    <script>
        for(var i=0;i<100;i++){
            document.getElementById("fil").innerHTML+="    <div class='post'><div class='people'><div class='profile' >    <img src='https://i.pinimg.com/originals/0b/8c/08/0b8c081b7b05dcc0aad6238856ea87d2.gif' alt=''> </div>    <p>Lucas</p>  </div>  <p>orem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque risus ex, mattis et erat nec, eleifend eleifend lacus</p>   <img src='https://www.slate.fr/sites/default/files/photos/gif11_0.gif' alt=''>    <div class='reaction'><p>like</p><p>commentaire</p></div> </div>"
        }

        const join = document.getElementById('join');
        join.addEventListener('click', () => {
            console.log('je passe ici')
        })
    </script>
</body>
</html>