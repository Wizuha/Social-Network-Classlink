<?php 

require '../inc/pdo.php'; 

if(isset($page_id)){
$check_permissions_page = $app_pdo->prepare("
    SELECT admin FROM subscribers_page JOIN pages ON subscribers_page.page_id = pages.id WHERE subscribers_page.page_id = :page_id AND subscribers_page.profile_id = :profile_id
");

$check_permissions_page->execute([
    ':profile_id' => $_SESSION['id'],
    ':page_id' => $page_id
]);

$check_permissions_page_result = $check_permissions_page->fetch(PDO::FETCH_ASSOC);
}


if(isset($group_id)){
$check_permissions_group = $app_pdo->prepare("
    SELECT * FROM groups_table WHERE creator_profile_id = :profile_id AND id = :group_id
");

$check_permissions_group->execute([
    ':profile_id' => $_SESSION['id'],
    ':group_id' => $group_id
]);

$check_permissions_group_result = $check_permissions_group->fetch(PDO::FETCH_ASSOC);
}