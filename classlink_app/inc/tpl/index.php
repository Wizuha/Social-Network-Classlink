<?php
include '../pdo.php';


if (isset($_POST['value'])) {
    
    $value = "%{$_POST['value']}%";

    $sql = "
    SELECT * FROM profiles WHERE username LIKE :value
    ";

    $stmt = $app_pdo->prepare($sql);
    $stmt->execute([
        ':value' => $value
     ]);

    if ($stmt->rowCount() > 0) { // Si le nombre de ligne est supérieur à 0
        $results = $stmt->fetchAll();
        for($i = 0; $i < 5; $i++) {
            if(isset($results[$i]['username'])){ ?>    
        <li>
          <a href="http://localhost/SocialNetwork-Fullstack-Project/classlink_app/profiles/profile.php?id=<?= $results[$i]['id'] ?>"><?=$results[$i]['username']?></a>
        </li>
       <?php
            }}
    }else echo "not found";
}
?>