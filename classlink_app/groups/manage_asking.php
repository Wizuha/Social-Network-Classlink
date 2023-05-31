<?php
session_start();
require '../../classlink_app/inc/pdo.php'; //Besoin du pdo pour se connecter à la bdd'

$groupid = $_POST['group_id'];
$userid = $_POST['user_id'];
$status = $_POST['status'];
var_dump($groupid);
var_dump($userid);
var_dump($status);



if($status == 'accept'){
    $request_accept = $app_pdo->prepare('
    INSERT INTO group_members (group_id, profile_id)
    VALUES (:group_id, :profile_id);
    ');
    $request_accept->execute([
        ':group_id' => $groupid,
        ':profile_id' => $userid
    ]);

    $request_supp_asked_groups = $app_pdo->prepare('
    DELETE FROM asked_groups WHERE group_id = :group_id
    AND profile_id = :profile_id;
    ');

    $request_supp_asked_groups->execute([
        ':group_id' => $groupid,
        ':profile_id' => $userid
    ]);

    echo json_encode('je suis la');
}elseif($status == 'reject'){
    $request_supp_asked_groups2 = $app_pdo->prepare('
        DELETE FROM asked_groups WHERE group_id = :group_id
        AND profile_id = :profile_id;
        ');

        $request_supp_asked_groups2->execute([
            ':group_id' => $groupid,
            ':profile_id' => $userid
        ]);
    echo json_encode($groupid);
}