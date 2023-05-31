<?php
require_once 'pdo.php';
//delete message in the database
$sql = "DELETE FROM private_chat_messages WHERE id = :id";
$stmt = $messaging_pdo->prepare($sql);
$stmt->execute(
    array(
        ':id' => $_POST['id']
    )
);
//send response to client
if($stmt->rowCount() > 0){
    $response = array(
        'status' => 'success',
        'msg' => 'Message deleted successfully'
    );
}else{
    $response = array(
        'status' => 'error',
        'msg' => 'Message not deleted'
    );
}
echo json_encode($response);
exit();
?>