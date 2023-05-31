<?php
session_start();
require '../../classlink_app/inc/pdo.php'; //Besoin du pdo pour se connecter Ã  la bdd
require '../inc/tpl/data_user.php';


$group_ID = $_GET['id'];
$erreur = "";


$requete2 = $app_pdo->prepare('
SELECT ag.profile_id, p.*
FROM asked_groups ag
JOIN profiles p ON ag.profile_id = p.id
WHERE ag.group_id = :group_id;
');
$requete2->execute([
    ':group_id'=>$group_ID
]);
$result2 = $requete2->fetchAll(PDO::FETCH_ASSOC);

if (!empty($result2)) {
    $id_asker = $result2[0]['id'];
} else {
    $erreur = "Il n'y a pas de demande !";
}

$logo = '../../assets/img/white_logo.svg';
$header_pp_image = $path_img . $my_pp_image;
$tv = '../../assets/img/tv-header.svg';
$bell = '../../assets/img/bell.svg';
$messages = '../../assets/img/messages.svg';

$method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
$input = filter_input(INPUT_SERVER, 'input');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/asked.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <link rel="stylesheet" href="../../assets/css/header.css">
    <title>Ajouts de membres</title>
</head>
<body>
<?php include '../inc/tpl/header.php' ?>
    <div class = "asked">
    <div class = "container"> 
    <div class = "titre"><h2>Ajouts de membres</h2></div>    
        <?php if($result2){
            foreach($result2 as $row2){?>
            <div class = "info-member">
                <p id = "member"><?php echo $row2['last_name']." ". $row2['first_name'] ?></p><p id = "requete"> souhaite rejoindre votre groupe. </p>
                <div class = "button">
                        <div class='div-button-accept' ><input class="input" type="submit" id ="<?php echo $row2['id'] ?>" value = "accept" name = "accept"></div>
                        <div class='div-button-reject' ><input class="input" type="submit" id = "<?php echo $row2['id'] ?>" value = "reject" name = "reject"></div>
                </div>
                <?php }}
                else { ?>
                <p><?php echo $erreur ?></p>
                <?php } ?>
            </div>
            
    </div>
</div>
    <br>
    <a href="./group.php?id=<?php echo $group_ID ?>"><button>Retour</button></a>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        const divButtonsAccept = document.getElementsByClassName("div-button-accept");
    Array.from(divButtonsAccept).forEach(divButton => {
    divButton.addEventListener("click", () => {
        const statusValue = 'accept';
        const id_btn = divButton.firstElementChild.id;
        console.log(id_btn);
        $.ajax({
            url: 'manage_asking.php',
            method: 'POST',
            data: { group_id: <?php echo $group_ID ?>, user_id: id_btn, status: statusValue },
            success: function(response) {
                location.reload()
            },
        });
    });
});

const divButtonsReject = document.getElementsByClassName("div-button-reject");
Array.from(divButtonsReject).forEach(divButton => {
    divButton.addEventListener("click", () => {
        const statusValue = 'reject';
        console.log('la');
        const id_btn = divButton.firstElementChild.id;
        $.ajax({
            url: 'manage_asking.php',
            method: 'POST',
            data: { group_id: <?php echo $group_ID ?>, user_id: id_btn, status: statusValue },
            success: function(response) {
                location.reload()
            },
        });
    });
});

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
    </script>
</body>
</html>