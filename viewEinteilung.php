<?php

/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "core/Database.php";
include_once "core/config.php";

session_start();

$pageTitle = "Einteilung auslesen";

if (isset($_SESSION['loggedin']) && isset($_SESSION['benutzer']) && isset($_GET['id'])) {
    if ($_SESSION['loggedin'] == 1 && $_SESSION['admin'] == 1) {

        $db = new Database();
        $pdo = $db->connect();

        if (is_null($pdo)){
            session_destroy();
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
    <main role="main">
        <h1 class="title_style">Übersicht</h1>
        <a class="btn btn-primary" style="margin-top: 16px; margin-bottom: 16px;" href="config_wahl.php?id=<?php echo $_GET['id']; ?>"><i
                    class="fas fa-chevron-left fa-sm fa-fw"></i>&nbsp;Zurück zur Übersicht</a>


        <?php
            $kursQuery = $pdo->prepare("SELECT * FROM tbl_kurse WHERE sportwahl=:wahl;");
            $kursQuery->bindParam(":wahl", $_GET['id']);
            $kursQuery->execute();

            while ($kurs = $kursQuery->fetch()){
                echo "<div class='card'><div class='card-body'>";
                echo "<h4 class='card-title'>".$kurs['alias']." ".$kurs['name']."</h4>";
                $teilnehmerQuery = $pdo->prepare("SELECT * FROM tbl_ergebnisse, tbl_users WHERE tbl_ergebnisse.sportwahl=:wahl AND tbl_ergebnisse.benutzer = tbl_users.id AND tbl_ergebnisse.kurs = :kurs AND tbl_ergebnisse.akzeptiert = 1;");
                $teilnehmerQuery->bindParam(":wahl", $_GET['id']);
                $teilnehmerQuery->bindParam(":kurs", $kurs['id']);
                $teilnehmerQuery->execute();
                $anzahl = $teilnehmerQuery->rowCount();
                if ($anzahl == 0){
                    echo "<p>Es gibt keine Teilnehmer</p>";
                }else{
                    $index = 0;
                    while ($user = $teilnehmerQuery->fetch()){
                        $index = $index + 1;
                        echo "<p>".$index.". ".$user['vorname']." ".$user['nachname']." - ".$user['klasse']."</p>";
                    }
                }
                echo "</div></div><br>";
            }
        ?>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Weitere Aktionen</h4>
                <a class="btn btn-primary" href="core/dataExport.php?action=2&id=<?php echo $_GET['id']; ?>">Einteilung als .csv-Datei exportieren</a><br><br>
                <a class="btn btn-primary" href="core/Teilnehmer.php?id=<?php echo $_GET['id']; ?>">Einteilung als .pdf-Datei exportieren</a><br><br>
                <a class="btn btn-primary" href="core/resetEinteilung.php?id=<?php echo $_GET['id']; ?>">Einteilung zurücksetzen</a>
            </div>
        </div>
        <br>


    </main>
</div>

<footer class="text-muted">
    <div class="container">
        <p>WeidigWahl | Ein Wahlsystem für die Weidigschule</p>
    </div>
</footer>

<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
<script type="text/javascript" src="js/popper.min.js"></script>
<script type="text/javascript">
    $('.alert').alert()
</script>
</body>
</html>