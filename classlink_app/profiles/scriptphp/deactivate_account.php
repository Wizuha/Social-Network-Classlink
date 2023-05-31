<?php
    require '../../inc/pdo.php';
    session_start();
    $account_id = $_SESSION['id'] ;
    

    $account_status_request = $app_pdo->prepare('
        SELECT status FROM profiles
        WHERE id = :id
    ');

    $account_status_request->execute([
        ':id' => $account_id
    ]);

    $account_status_request_result = $account_status_request->fetch(PDO::FETCH_ASSOC);
    
    $status = "";
    if($account_status_request_result['status'] == 'Actif') {
        $status = 'Inactif';
    }

    if ($account_status_request_result['status'] == 'Inactif') {
        $status = 'Actif';
        $_SESSION['profile_status'] = $status;
    }
    
    $account_activation_request = $app_pdo->prepare('
        UPDATE profiles
        SET status = :status
        WHERE id = :id
    ');

    $account_activation_request->execute([
        ':id' => $account_id,
        ':status' => $status
    ]);    

    $response = array(
        'status' => 'success',
        'response' => $status
    );
    echo json_encode($response);
?>