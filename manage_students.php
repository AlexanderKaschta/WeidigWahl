<?php

/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "core/Database.php";
include_once "core/config.php";

session_start();

$pageTitle = "Teilnehmerverwaltung";

if (isset($_SESSION['loggedin']) && isset($_SESSION['benutzer'])) {
    if ($_SESSION['loggedin'] == 1 && $_SESSION['admin'] == 1) {
        //Connect to a database

        $db = new Database();
        $pdo = $db->connect();

        if (is_null($pdo)) {
            session_destroy();
            header("Location: index.php?errorCode=1 ");
            exit();
        }

    } else {
        session_destroy();
        header("Location: index.php?errorCode=1 ");
        exit();
    }

} else {
    session_destroy();
    header("Location: index.php?errorCode=1 ");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <meta name="theme-color" content="#212529">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/all.min.css">
    <title><?php echo PROJECT_NAME . " | " . $pageTitle; ?></title>
</head>
<body>
<header>
    <div class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container d-flex justify-content-between">
            <a href="main.php" class="navbar-brand d-flex align-items-center">
                <strong><?php echo PROJECT_NAME; ?></strong>
            </a>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>

        </div>
    </div>
</header>
<div class="container">

    <?php
    if (isset($_GET['errorCode'])) {
        $errorMessage = htmlspecialchars($_GET['errorCode']);

        if ($errorMessage == 1) {
            $errorMessage = '    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        Alle Benutzer wurden erfolgreich gelöscht!
    </div>';
            echo $errorMessage;
        } else if ($errorMessage == 2) {
            $errorMessage = '<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
        Der ausgewählte Benutzer wurde gelöscht!</div>';
            echo $errorMessage;
        } else if ($errorMessage == 3) {
            $errorMessage = '<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
        Ein interner Fehler bei der Auswertung der hochgeladenen Daten ist aufgetreten.</div>';
            echo $errorMessage;
        } else if ($errorMessage == 4) {
            $errorMessage = '<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
        Der Nutzer wurde erfogreich hinzugefügt.</div>';
            echo $errorMessage;
        } else if ($errorMessage == 4) {
            $errorMessage = '<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
        Der Nutzer wurde erfogreich bearbeitet.</div>';
            echo $errorMessage;
        }

    }
    ?>

    <main role="main">
        <h1 class="title_style">Nutzer</h1>

        <a class="btn btn-primary" href="main.php"><i class="fas fa-chevron-left fa-sm fa-fw"></i>&nbsp;Zurück zur Hauptseite</a>
        <br><br>
        <a class="btn btn-danger" href="core/manageUsers.php?action=1">Alle Nutzer löschen</a>
        <br><br>

        <!-- Liste hier alle Nutzer auf -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover" style="width: 100%; margin-bottom: 0 !important;">
                        <thead>
                        <tr>
                            <th>Nr.</th>
                            <th>Vorname</th>
                            <th>Nachname</th>
                            <th>Benutzername</th>
                            <th>Passwort</th>
                            <th>Aktiviert</th>
                            <th>Admin</th>
                            <th>Jahrgang</th>
                            <th>Klasse</th>
                            <th>Aktionen</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $user_query = $pdo->prepare("SELECT * FROM tbl_users;");
                        $user_query->execute();

                        $user_count = $user_query->rowCount();

                        while ($user = $user_query->fetch()) {
                            if ($user['ist_aktiv']) {
                                $aktiv = "<i class=\"fas fa-check fa-sm fa-fw\"></i>";
                            } else {
                                $aktiv = "<i class=\"fas fa-times fa-sm fa-fw\"></i>";
                            }

                            if ($user['ist_admin']) {
                                $rechte = "<i class=\"fas fa-check fa-sm fa-fw\"></i>";
                            } else {
                                $rechte = "";
                            }

                            echo '<tr><td>' . $user['id'] . '</td><td>' . $user['vorname'] . '</td><td>' . $user['nachname'] . '</td><td>' . $user['benutzername'] . '</td><td>'. $user['passwort'] . '</td><td>' . $aktiv . '</td><td>' . $rechte . '</td><td>' . $user['jahrgang'] . '</td><td>' . $user['klasse'] . '</td><td><a href="create_User.php?action=2&id=' . $user['id'] . '" data-toggle="tooltip" data-placement="bottom" title="Nutzer bearbeiten"><i class="fas fa-edit fa-sm"></i></a> &nbsp; <a href="core/manageUsers.php?action=2&id=' . $user['id'] . '" data-toggle="tooltip" data-placement="bottom" title="Nutzer löschen"><i class="fas fa-trash fa-sm"></i></a></td></tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <a class="btn btn-light" href="create_User.php?action=1"><i class="fas fa-plus fa-sm fa-fw"></i>&nbsp;Neuen Nutzer hinzufügen</a>
                <a class="btn btn-light" href="uploadFile.php?action=1"><i class="fas fa-file-import fa-sm fa-fw"></i>&nbsp;Nutzer importieren</a>
            </div>
        </div>


    </main>
</div>

<footer class="text-muted">
    <div class="container">
        <p>WeidigWahl | Ein Wahlsystem für die Weidigschule</p>
    </div>
</footer>

<script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
<script type="text/javascript" src="js/popper.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript">
    $('.alert').alert()
</script>
</body>
</html>
