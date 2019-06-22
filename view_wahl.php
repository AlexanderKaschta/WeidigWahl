<?php

/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "core/Database.php";
include_once "core/config.php";

session_start();

$pageTitle = "Wahlübersicht";

if (isset($_SESSION['loggedin']) && isset($_GET['id']) && is_int((int)$_GET['id'])){
    if ($_SESSION['loggedin'] == 1 && isset($_SESSION['benutzer'])){
        $db = new Database();
        $pdo = $db->connect();

        if (is_null($pdo)){
            header("Location: index.php?errorCode=1 ");
            exit();
        }

        //Kontrolliere ob der Nutzer an der Wahl teilnehmen darf
        $check = $pdo->prepare("SELECT * FROM tbl_teilnehmer WHERE wahl_id = :wahl AND benutzer = :id;");
        $check->bindParam(":wahl", $_GET['id']);
        $check->bindParam(":id", $_SESSION['id']);

        $check->execute();

        $rows = $check->rowCount();

        if ($rows != 1){
            header("Location: main.php?errorCode=1 ");
            exit();
        }

        //Lade die Daten

        $wahl_query = $pdo->prepare("SELECT * FROM tbl_sportwahl WHERE id = :wahl AND ist_aktiv=1;");
        $wahl_query->bindParam(":wahl", $_GET['id']);

        $wahl_query->execute();

        $rowCount = $wahl_query->rowCount();


        if ($rowCount == 0) {
            header("Location: main.php?errorCode=1 ");
            exit();
        } else {
            while ($r = $wahl_query->fetch()) {
                $sportwahl = $r;
            }
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

<main role="main">
    <section class="jumbotron text-center">
        <div class="container">
            <h1 class="jumbotron-heading"><?php echo $sportwahl['name_wahl']; ?></h1>
            <p class="lead text-muted"><?php echo $sportwahl['beschreibung']; ?></p>
            <p>Aktionen:</p>
            <p>
                <?php
                $query = $pdo->prepare("SELECT * FROM tbl_sportwahl WHERE id = :wahl AND ist_aktiv=1 AND tbl_sportwahl.datum_ende >= CURDATE();");
                $query->bindParam(":wahl", $_GET['id']);
                $query->execute();
                $query_anzahl = $query->rowCount();
                if ($query_anzahl > 0){
                    echo '<a href="wahl.php?nr=1&id='.$_GET['id'].'" class="btn btn-primary">Jetzt wählen</a>';
                }
                ?>
                <a href="main.php" class="btn btn-secondary my-2">Zurück zur Übersicht</a>
                <?php
                $file = "docs/".$_GET['id'].".pdf";
                if (file_exists($file)){
                    echo "<p><a href='".$file."' class='btn btn-primary'>Zusammenfassung ansehen!</a></p>";
                }
                ?>
            </p>
        </div>
    </section>
    <div class="album py-5 bg-light">
        <div class="container">
            <h4>Folgende Kurse stehen zur Auswahl:</h4>
            <div class="row">
                <?php
                $kurs_query = $pdo->prepare("SELECT * FROM tbl_kurse WHERE sportwahl = :wahl ORDER BY alias ASC;");
                $kurs_query->bindParam(":wahl", $_GET['id']);

                $kurs_query->execute();

                $kursanzahl = $kurs_query->rowCount();

                if ($kursanzahl == 0){
                    header("Location: main.php?errorCode=1 ");
                    exit();
                } else{
                    while($kurs_row = $kurs_query->fetch()){
                        echo '
                    <div class="col-md-4">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">'.$kurs_row['name'].'</h5>
                            <p class="card-text">'.$kurs_row["beschreibung"].'</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">'.$kurs_row["alias"].'</small>
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
</body>
</html>
