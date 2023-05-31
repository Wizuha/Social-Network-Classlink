<?php
require '../inc/pdo.php';
    session_start();
    $id = $_SESSION['id'];

    if (isset($_POST['submit']) && isset($_FILES['file'])) {
        $img_name = $_FILES['file']['name'];
        $img_size = $_FILES['file']['size'];
        $tmp_name = $_FILES['file']['tmp_name'];
        $error = $_FILES['file']['error'];
        $img_ex =  pathinfo($img_name, PATHINFO_EXTENSION);
        $img_ex_lc = strtolower($img_ex);

        $new_img_name = uniqid("IMG-", true).'.'.$img_ex_lc;
        $img_upload_path = 'uploads/'. $new_img_name;
        // echo $img_upload_path;
        move_uploaded_file($tmp_name,$img_upload_path);
        // print_r($_FILES['file']);
        // echo $new_img_name;

        $add_image = $app_pdo->prepare("
                    UPDATE test_publications SET image = :img WHERE id = :id;
                    ");
                    $add_image->execute([
                        ":id" => $id,
                        ":img" => $new_img_name
                    ]);
        $data = array(
                'statut' => 'Succès', 
                'id' => $id
            );
        $json = json_encode($data);
        echo $json;
        header('location: profile.php?json=<?php echo $json ?>')

        ?>
        
        
        
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Document</title>
        </head>
        <body>
            <img src="<?= $tmp_name ?>" alt="">
        </body>
        </html><?php }
?> 
