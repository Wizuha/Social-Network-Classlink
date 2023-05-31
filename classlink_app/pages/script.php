<?php 
session_start();
require '../inc/pdo.php'; 
require '../inc/functions/token_functions.php';
$page_id = $_POST['page_id'];
$user_id = $_POST['user_id'];

$check_admin = $app_pdo->prepare("
SELECT admin FROM subscribers_page
WHERE profile_id = :id
AND page_id = :page_id
");

$check_admin->execute([
    ":id" => $user_id,
    ":page_id" => $page_id
]);

$check_admin_result = $check_admin->fetch(PDO::FETCH_ASSOC);

if($check_admin_result['admin'] == 0){
    $set_page_admin = $app_pdo->prepare("
        UPDATE subscribers_page
        SET admin = :admin
        WHERE profile_id = :id
        AND page_id = :page_id
        ");

    $set_page_admin->execute ([
        ":id" => $user_id,
        ":page_id" => $page_id,
        ":admin" => 1
    ]);

}elseif($check_admin_result['admin'] == 1){
    $set_page_admin = $app_pdo->prepare("
        UPDATE subscribers_page
        SET admin = :admin
        WHERE profile_id = :id
        AND page_id = :page_id
        ");

    $set_page_admin->execute ([
        ":id" => $user_id,
        ":page_id" => $page_id,
        ":admin" => 0
    ]);

}