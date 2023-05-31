<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <script src="../assets/js/script.js"></script>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="<?= $page_css_path ?>">
        <link rel="stylesheet" href="<?= $header_css_path ?>">
        <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <title><?= $page_title ?></title>
    </head>

    <body>

        <div class="sectionheader">
            <section class="newheader">
                <a class="logo" href="<?= $dashboard_path ?>">
                    <img src="<?= $logo ?>" alt="logo">
                </a>
                <div class="input-box">
                    <input id="input" type="text" placeholder="Recherche...">
                    <span class="search">
                        <i class="uil uil-search search-icon"></i>
                        <ul class="dropdown" id="dropdown"></ul>
                    </span>
                    <i class="uil uil-times close-icon"></i>
                </div>
                <a class="profile-icon-header" href="<?= $profile_path ?>">
                    <img src="<?= $header_pp_image ?>" alt="profile-icon">
                </a>
                <a class="tv-icon" href="">
                    <img src="<?= $tv ?>" alt="tv-icon">
                </a>
                <a class="notifications" href="">
                    <img src="<?= $bell ?>" alt="bell-icon">
                </a>
                
                <img class="messages-icon" src="<?= $messages ?>" alt="messages-icon" id="message-link">

            </section>
        </div>