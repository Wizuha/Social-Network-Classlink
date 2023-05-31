<?php

function token()
{
    $token = [];
    for ($i = 1; $i <= 30; $i++) {
        $random = rand(48, 122);
        $str = chr($random);
        $token[] = $str;
    }
    $token = implode($token);
    return $token;
}


function token_check($token, $pdo)
{
    $requete = $pdo->prepare("
    SELECT token FROM token WHERE token = :token;
    ");
    $requete->execute([
        ":token" => $token
    ]);
    $check_token = $requete->fetch(PDO::FETCH_ASSOC);

    if (isset($check_token)){
        return 'true';
    }else{
        return 'false';
    }
}
?>