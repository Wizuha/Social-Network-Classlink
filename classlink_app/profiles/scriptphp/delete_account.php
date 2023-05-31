<?php

    require '../../inc/pdo.php';
    session_start();

    try {
        $app_pdo->beginTransaction();
        $delete_account_request1 = $app_pdo->prepare("
            DELETE FROM asked_groups 
            WHERE profile_id = :id
        ");

        $delete_account_request1->execute([
            ':id' => 147
        ]);

        $delete_account_request2 = $app_pdo->prepare("
            DELETE FROM awaiting_relations
            WHERE seeker_profile_id = :id OR receiver_profile_id = :id
        ");

        $delete_account_request2->execute([
            ':id' => 147
        ]);

        $delete_account_request3 = $app_pdo->prepare("
            DELETE FROM comments
            WHERE creator_id = :id
        ");

        $delete_account_request3->execute([
            ':id' => 147
        ]);

        $delete_account_request4 = $app_pdo->prepare("
            DELETE FROM dislikes
            WHERE profile_id = :id
        ");

        $delete_account_request4->execute([
            ':id' => 147
        ]);

        $delete_account_request5 = $app_pdo->prepare("
            DELETE FROM group_members
            WHERE profile_id = :id
        ");

        $delete_account_request5->execute([
            ':id' => 147
        ]);

        $delete_account_request6 = $app_pdo->prepare("
            DELETE FROM groups_table
            WHERE creator_profile_id = :id
        ");

        $delete_account_request6->execute([
            ':id' => 147
        ]);

        $delete_account_request7 = $app_pdo->prepare("
            DELETE FROM likes
            WHERE profile_id = :id
        ");

        $delete_account_request7->execute([
            ':id' => 147
        ]);

        $delete_account_request8 = $app_pdo->prepare("
            DELETE FROM pages
            WHERE creator_profile_id = :id
        ");

        $delete_account_request8->execute([
            ':id' => 147
        ]);

        $delete_account_request9 = $app_pdo->prepare("
            DELETE FROM publications_group
            WHERE profile_id = :id
        ");

        $delete_account_request9->execute([
            ':id' => 147
        ]);

        $delete_account_request10 = $app_pdo->prepare("
            DELETE FROM publications_page
            WHERE profile_id = :id
        ");

        $delete_account_request10->execute([
            ':id' => 147
        ]);

        $delete_account_request11 = $app_pdo->prepare("
            DELETE FROM publications_profile
            WHERE profile_id = :id
        ");

        $delete_account_request11->execute([
            ':id' => 147
        ]);

        $delete_account_request12 = $app_pdo->prepare("
            DELETE FROM relation
            WHERE id_demandeur = :id OR id_receveur = :id
        ");

        $delete_account_request12->execute([
            ':id' => 147
        ]);

        $delete_account_request13 = $app_pdo->prepare("
            DELETE FROM test_publications
            WHERE profile_id = :id
        ");

        $delete_account_request13->execute([
            ':id' => 147
        ]);

        $delete_account_request14 = $app_pdo->prepare("
            DELETE FROM profile
            WHERE id = :id
        ");

        $delete_account_request14->execute([
            ':id' => 147
        ]);

        $app_pdo->commit();

        // $auth_pdo->beginTransaction();
        // $delete_account_request15 = $auth_pdo->prepare("
        //     DELETE FROM token
        //     WHERE user_id = :id
        // ");

        // $delete_account_request15->execute([
        //     ':id' => 147
        // ]);

        // $delete_account_request16 = $auth_pdo->prepare("
        //     DELETE FROM users
        //     WHERE id = :id
        // ");

        // $delete_account_request16->execute([
        //     ':id' => 147
        // ]);
        // $auth_pdo->commit();
        $response = array(
            'status' => 'success',
            'message' => 'Suppression validez'
        );
        $json = json_encode($response);
        echo $json;
    } catch (PDOException $exception) {
        $app_pdo->rollBack();
        // $auth_pdo->rollBack();
        $response = array(
            'status' => 'error',
            'message' => "Une erreur s'est produite : " . $exception->getMessage()
        );
        $json = json_encode($response);
        echo $json;
    }

        