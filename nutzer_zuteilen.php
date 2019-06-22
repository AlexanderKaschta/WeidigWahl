<?php

/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "core/Database.php";
include_once "core/config.php";

session_start();

$pageTitle = "Nutzer einteilen";

//id = ID der Wahl, zu welcher Teilnehmer hinzugefügt werden sollen
if (isset($_SESSION['loggedin']) && isset($_SESSION['benutzer']) && isset($_GET['id'])) {
    if ($_SESSION['loggedin'] == 1 && $_SESSION['admin'] == 1) {

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
    <main role="main">
        <h1 class="title_style">Nutzer hinzufügen</h1>
        <a class="btn btn-primary" style="margin-bottom: 16px;" href="config_wahl.php?id=<?php echo $_GET['id']; ?>"><i
                    class="fas fa-chevron-left fa-sm fa-fw"></i>&nbsp;Abbrechen</a>
        <br>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Gruppe hinzufügen</h4>
                <div class='table-responsive'>
                    <table style="width: 100%;" class="table">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Zuteilen</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $data_query = $pdo->prepare("SELECT id, jahrgang FROM tbl_users GROUP BY tbl_users.jahrgang;");
                        $data_query->execute();

                        while ($data_row = $data_query->fetch()) {
                            echo '<tr><td>Jahrgang ' . $data_row["jahrgang"] . '</td><td><a href="core/addTeilnehmer.php?action=2&wahl=' . $_GET["id"] . '&id='. $data_row["id"] . '">Hinzufügen</a></td></tr>';
                        }

                        $data1_query = $pdo->prepare("SELECT id, klasse FROM tbl_users GROUP BY tbl_users.klasse;");
                        $data1_query->execute();

                        while ($data1_row = $data1_query->fetch()) {
                            echo '<tr><td>Klasse ' . $data1_row["klasse"] . '</td><td><a href="core/addTeilnehmer.php?action=3&wahl=' . $_GET["id"] . '&id='. $data1_row["id"] . '">Hinzufügen</a></td></tr>';
                        }

                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <br>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Einzelpersonen hinzufügen</h4>
                <p>Alle Personen, die hier nicht auftauchen, sind schon der Wahl zugeteilt.</p>
                <div class='table-responsive'>
                    <table style="width: 100%;" class="table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Vorname</th>
                            <th>Nachname</th>
                            <th>Zuteilen</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $data2_query = $pdo->prepare("SELECT tbl_users.id, tbl_users.vorname, tbl_users.nachname FROM tbl_users LEFT JOIN tbl_teilnehmer ON tbl_users.id = tbl_teilnehmer.benutzer WHERE tbl_teilnehmer.id IS NULL;");
                        $data2_query->execute();

                        while ($data2_row = $data2_query->fetch()) {
                            echo '<tr><td>' . $data2_row["id"] . '</td><td>' . $data2_row["vorname"] . '</td><td>' . $data2_row["nachname"] . '</td><td><a href="core/addTeilnehmer.php?action=1&wahl=' . $_GET['id'] . '&id=' . $data2_row["id"] . '">Hinzufügen</a></td></tr>';
                        }

                        ?>
                        </tbody>
                    </table>
                </div>
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
</body>
</html>