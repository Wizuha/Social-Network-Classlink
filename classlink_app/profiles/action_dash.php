<?php
    session_start();
    require '../inc/pdo.php';
    require '../../vendor/autoload.php';
    use GuzzleHttp\Client;
    use GuzzleHttp\RequestOptions;

    // $check = $app_pdo->prepare('SELECT id FROM likes WHERE test_publications_id = ? ');
    // $check->execute(array($pub_id));


    if(isset($_GET['t'],$_GET['id']) AND !empty($_GET['t']) AND !empty($_GET['id'])) {
    
        $getid =  $_GET['id'];
        $gett = (int) $_GET['t'];
    
        $check = $app_pdo->prepare('SELECT id FROM test_publications WHERE profile_id = :id');
        $check->execute([
            'id' => $_SESSION['id']
        ]);
        $result_check = $check->fetch(PDO::FETCH_ASSOC);

        $pub_id = $result_check['id'];

        
        if($result_check) {
            if($gett == 0) {
                echo 'rien';
            }
            elseif($gett == 1) {

            $check_like = $app_pdo->prepare('SELECT id FROM likes WHERE test_publications_id = ? AND
            profile_id = ?');
            $check_like->execute(array($pub_id, $_SESSION['id']));
            
            $del = $app_pdo->prepare('DELETE FROM dislikes WHERE test_publications_id = ? AND
            profile_id = ?');
            $del->execute(array($pub_id, $_SESSION['id']));

            if($check_like->rowCount() == 1){
                    
                    $del = $app_pdo->prepare('DELETE FROM likes WHERE test_publications_id = ? AND
                    profile_id = ?');
                    $del->execute(array($pub_id, $_SESSION['id']));


            }else {

                $ins = $app_pdo->prepare("INSERT INTO likes (test_publications_id,profile_id) VALUES (?,?)");
                $ins->execute(array($pub_id,$_SESSION['id'] ));
            }
            header('Location: ../dashboard.php?id='. $_SESSION['id']);
            exit;
            
        } elseif($gett == 2) {

            $check_like = $app_pdo->prepare('SELECT id FROM dislikes WHERE test_publications_id = ? AND
            profile_id = ?');
            $check_like->execute(array($pub_id, $_SESSION['id']));

            $del = $app_pdo->prepare('DELETE FROM likes WHERE test_publications_id = ? AND
            profile_id = ?');
            $del->execute(array($pub_id, $_SESSION['id']));

                if($check_like->rowCount() == 1){
                    
                    $del = $app_pdo->prepare('DELETE FROM dislikes WHERE test_publications_id = ? AND
                    profile_id = ?');
                    $del->execute(array($pub_id, $_SESSION['id']));

            }else {

                $ins = $app_pdo->prepare("INSERT INTO dislikes (test_publications_id,profile_id) VALUES (?,?)");
                $ins->execute(array($pub_id, $_SESSION['id']));
            }
        }
        header('Location: ../dashboard.php?id='. $_SESSION['id']);
        exit;
    }
}
?>
