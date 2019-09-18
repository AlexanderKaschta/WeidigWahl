<?php

/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "core/Database.php";
include_once "core/config.php";

session_start();

$pageTitle = "Wahlstatistik ansehen";

if (isset($_SESSION['loggedin']) && isset($_SESSION['admin']) && isset($_GET['id'])) {
    if ($_SESSION['loggedin'] == 1 && isset($_SESSION['benutzer']) && $_SESSION['admin'] == 1) {

        //Lade die Daten der aktuellen Wahl
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
    <script type="text/javascript" src="js/chart.js"></script>
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
        <h1 class="title_style">Statistik zur Wahl</h1>
        <a href="config_wahl.php?id=<?php echo $_GET['id']; ?>" class="btn btn-primary" style="margin-bottom: 32px;"><i
                    class="fas fa-chevron-left fa-sm fa-fw"></i>&nbsp;Zurück zur Übersicht</a>
        <p>Dies ist die Statistik zum Zeitpunkt <?php echo date("d.m.Y H:i:s", time()); ?>. Zum Aktualisieren einfach
            <kbd>F5</kbd> drücken.</p>
        <canvas id="votesChart" width="400" height="400"></canvas>
        <?php
        //First data query
        $kurse = $pdo->prepare("SELECT * FROM tbl_kurse WHERE sportwahl = :id;");
        $kurse->bindParam(":id", $_GET['id']);
        $kurse->execute();

        $kursNamen = array();
        $kursAnzahl = array();

        //Durchgehe jeden Kurs
        while ($kurs = $kurse->fetch()) {
            array_push($kursNamen, $kurs['name']);

            $anzahlAbfrage = $pdo->prepare("SELECT COUNT(tbl_ergebnisse.id) AS Anzahl FROM tbl_ergebnisse WHERE sportwahl = :id AND stimmnummer = 1 AND kurs = :kurs;");
            $anzahlAbfrage->bindParam(":id", $_GET['id']);
            $anzahlAbfrage->bindParam(":kurs", $kurs['id']);
            $anzahlAbfrage->execute();
            $anzahl = $anzahlAbfrage->fetch();
            array_push($kursAnzahl, (int)$anzahl['Anzahl']);
            $anzahlAbfrage->closeCursor();
            $anzahlAbfrage = null;
            $anzahl = null;
        }

        ?>
        <script>
            var ctx = document.getElementById('votesChart');
            var votesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($kursNamen); ?>,
                    datasets: [{
                        label: 'Anzahl der Erststimmen',
                        data: <?php echo json_encode($kursAnzahl); ?>,
                        backgroundColor: "rgba(56,110,255, 0.5)",
                        borderWidth: 0
                    }]
                },
                options: {
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                }
            });
        </script>
        <br>
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Fakten</h4>
                <?php
                $stimmenAnzahl = $pdo->prepare("SELECT COUNT(tbl_ergebnisse.id) AS Anzahl FROM tbl_ergebnisse WHERE sportwahl = :id;");
                $stimmenAnzahl->bindParam(":id", $_GET['id']);
                $stimmenAnzahl->execute();
                $stimmen = $stimmenAnzahl->fetch();
                ?>
                <p>Abgegebene Stimmen: <?php echo $stimmen['Anzahl']; ?></p>
                <p>Nutzer, die noch keine Stimme abgegeben haben:</p>
                <?php
                $nutzerOhneEingabe = $pdo->prepare("SELECT tbl_teilnehmer.id, tbl_users.id, tbl_users.vorname, tbl_users.nachname FROM tbl_teilnehmer LEFT JOIN tbl_ergebnisse ON tbl_ergebnisse.benutzer = tbl_teilnehmer.benutzer LEFT JOIN tbl_users ON tbl_teilnehmer.benutzer = tbl_users.id
WHERE tbl_ergebnisse.id IS NULL AND tbl_teilnehmer.wahl_id = :nr;");
                $nutzerOhneEingabe->bindParam(":nr", $_GET['id']);
                $nutzerOhneEingabe->execute();

                while ($row = $nutzerOhneEingabe->fetch()) {
                    echo "<p>" . $row['vorname'] . " " . $row['nachname'] . "</p>";
                }

                ?>
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

<script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
<script type="text/javascript" src="js/popper.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript">
    $('.alert').alert()
</script>

</body>
</html>

