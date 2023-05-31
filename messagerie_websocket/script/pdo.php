<?php
    $app_engine = "mysql";
    $host_app = "containers-us-west-61.railway.app";

    $app_port = 7074; // port MAMP
    $app_bdd = "railway";
    $app_user = "root";
    $app_password = "2vrfBWRHnpNCUEiurGtb";

    $app_dsn = "$app_engine:host=$host_app:$app_port;dbname=$app_bdd";
    $app_pdo = new PDO($app_dsn, $app_user, $app_password);


    $messaging_engine = "mysql";
    $messaging_host = "containers-us-west-205.railway.app";

    $massaging_port = 7474; // port MAMP
    $messaging_bdd = "railway";
    $messaging_user = "root";
    $messaging_password = "4iVR3uhnNfgQFC3RwXmV";

    $messaging_dsn = "$messaging_engine:host=$messaging_host:$massaging_port;dbname=$messaging_bdd";
    $messaging_pdo = new PDO($messaging_dsn, $messaging_user, $messaging_password);


    $auth_engine = "mysql";
    $host = "containers-us-west-82.railway.app";

    $auth_port = 6741; // port MAMP
    $auth_bdd = "railway";
    $user = "root";
    $password_bdd = "uG7gyx2rT5a8Inw5FrNd";

    $auth_dsn = "$auth_engine:host=$host:$auth_port;dbname=$auth_bdd";
    $auth_pdo = new PDO($auth_dsn, $user, $password_bdd);

  