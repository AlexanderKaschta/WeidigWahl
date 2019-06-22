<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "core/Database.php";
include_once "core/config.php";

session_start();

$pageTitle = "Hauptseite";

if (isset($_SESSION['loggedin'])){
    if ($_SESSION['loggedin'] == 1 && isset($_SESSION['benutzer'])){
        $db = new Database();
        $pdo = $db->connect();

        if (is_null($pdo)){
            header("Location: index.php?errorCode=1 ");
            exit();
        }

    } else{
        session_destroy();
        header("Location: index.php?errorCode=1 ");
        exit();
    }

} else{
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
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <meta name="theme-color" content="#212529">
    <title><?php echo PROJECT_NAME . " | " . $pageTitle; ?></title>
</head>
<body>
<header>
    <div class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container d-flex justify-content-between">
            <a href="main.php" class="navbar-brand d-flex align-items-center">
                <!-- Optional svg logo-->
                <strong><?php echo PROJECT_NAME;?></strong>
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
        Ein technischer Fehler ist passiert!
    </div>';
            echo $errorMessage;
        } else if ($errorMessage == 2) {
            $errorMessage = '<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
        Unbekanntes Passwort oder Benutzername!</div>';
            echo $errorMessage;
        } else if ($errorMessage == 3) {
            $errorMessage = '<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
        Es fehlt eine Sportwahl für den der Kurs erstellt werden soll.</div>';
            echo $errorMessage;
        } else if ($errorMessage == 4) {
            $errorMessage = '<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
        Die Wahl ist mittlerweile beendet.</div>';
            echo $errorMessage;
        }
    }
    ?>
</div>

<main role="main">

    <section class="jumbotron text-center">
        <div class="container">
            <h1 class="jumbotron-heading">Wahlen</h1>
            <p class="lead text-muted">Hier sieht man alle Wahlen die aktuell zur Verfügung stehen.</p>
        </div>
    </section>

    <div class="album py-5 bg-light">
        <div class="container">

            <div class="row">
                <?php

                //TODO: Add an easter-egg, that creates random background colors

                //Wenn es ein Administrator ist
                if ($_SESSION['admin'] == 1){

                    //Display all Wahlen
                    $query = $pdo->prepare("SELECT * FROM tbl_sportwahl");

                    $query->execute();

                    while ($row = $query->fetch()) {
                        echo '
                    <div class="col-md-4">
                    <div class="card mb-4 shadow-sm">
                        <svg class="card-img-top" width="100%" height="225" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice" focusable="false">
                            <title>'.$row["name_wahl"].'</title>
                            <rect fill="#19355c" width="100%" height="100%"></rect>
                            <text fill="#eceeef" dy=".3em" x="50%" y="50%" text-anchor="middle">'.$row["name_wahl"].'</text>
                        </svg>
                        <div class="card-body">
                            <p class="card-text">'.$row["beschreibung"].'</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="btn-group">
                                    <a class="btn btn-sm btn-outline-secondary" href="config_wahl.php?id='.$row['id'].'">Bearbeiten</a>
                                </div>
                                <small class="text-muted">#'.$row["id"].'</small>
                            </div>
                        </div>
                    </div>
                    </div>';
                    }

                    echo '
                    <div class="col-md-4">
                    <div class="card mb-4 shadow-sm">
                        <svg class="card-img-top" width="100%" height="225" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice" focusable="false" >
                            <title>Neue Wahl</title>
                            <rect fill="#19355c" width="100%" height="100%"></rect>
                            <text fill="#eceeef" dy=".3em" x="50%" y="50%" text-anchor="middle">Neue Wahl</text>
                        </svg>
                        <div class="card-body">
                            <p class="card-text">Erstelle eine neue Wahl!</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="btn-group">
                                    <a class="btn btn-sm btn-outline-secondary" href="new_wahl.php">Los geht\'s!</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>';

                    echo '
                    <div class="col-md-4">
                    <div class="card mb-4 shadow-sm">
                        <svg class="card-img-top" width="100%" height="225" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice" focusable="false">
                            <title>Schüler</title>
                            <rect fill="#19355c" width="100%" height="100%"></rect>
                            <text fill="#eceeef" dy=".3em" x="50%" y="50%" text-anchor="middle">Schüler</text>
                        </svg>
                        <div class="card-body">
                            <p class="card-text">Verwalte die Teilnehmer</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="btn-group">
                                    <a class="btn btn-sm btn-outline-secondary" href="manage_students.php">Los geht\'s!</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>';

                } else{
                    //wenn sonst ein normaler Benutzer ist
                    $query = $pdo->prepare("SELECT tbl_teilnehmer.benutzer, tbl_sportwahl.id, tbl_sportwahl.name_wahl,
tbl_sportwahl.beschreibung FROM tbl_teilnehmer, tbl_sportwahl WHERE 
tbl_teilnehmer.wahl_id = tbl_sportwahl.id AND tbl_teilnehmer.benutzer = :id AND tbl_sportwahl.ist_aktiv = 1 AND tbl_sportwahl.datum_beginn <= CURRENT_DATE AND tbl_sportwahl.datum_ende >= CURRENT_DATE();");
                    $query->bindParam(":id", $_SESSION['id']);

                    $query->execute();

                    $rowCount = $query->rowCount();
                    if ($rowCount == 0){
                        echo "<p style='text-align: center; width: 100%'>Es sind keine Wahlen verfügbar!</p>";
                    }

                    while ($row = $query->fetch()) {
                        echo '
                    <div class="col-md-4">
                    <div class="card mb-4 shadow-sm">
                        <svg class="card-img-top" width="100%" height="225" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice" focusable="false">
                            <title>'.$row["name_wahl"].'</title>
                            <rect fill="#19355c" width="100%" height="100%"></rect>
                            <text fill="#eceeef" dy=".3em" x="50%" y="50%" text-anchor="middle">'.$row["name_wahl"].'</text>
                        </svg>
                        <div class="card-body">
                            <p class="card-text">'.$row["beschreibung"].'</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="btn-group">
                                    <a class="btn btn-sm btn-outline-secondary" href="view_wahl.php?id='.$row['id'].'">Wähl!</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>';
                    }
                }
                ?>

            </div>
        </div>
    </div>

</main>

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