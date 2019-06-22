<?php

/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

include_once "core/config.php";

session_start();

$pageTitle = "Neue Wahl";

if (isset($_SESSION['loggedin']) && isset($_SESSION['admin']) && isset($_SESSION['benutzer'])) {
    if ($_SESSION['loggedin'] != 1 || $_SESSION['admin'] != 1) {
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
        <?php
        if (isset($_GET['errorCode'])) {
            $errorMessage = htmlspecialchars($_GET['errorCode']);

            if ($errorMessage == 1) {
                $errorMessage = '<div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        Es liegt ein Fehler bei den Angaben zur Stimmeneingabe und Auswertung vor.
    </div>';
                echo $errorMessage;
            } else if ($errorMessage == 2) {
                $errorMessage = '<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
        Die Wahl muss einen Tag später enden als sie beginnen soll.</div>';
                echo $errorMessage;
            }
        }
        ?>
        <h1 style="margin-top: 36px; margin-bottom: 8px;">Erstelle eine neue Wahl!</h1>
        <p>Hier kannst du eine Wahl erstellen. Fülle dazu einfach das Formular aus.</p>
        <a href="main.php" class="btn btn-primary" style="margin-bottom: 16px;"><i
                    class="fas fa-chevron-left fa-sm fa-fw"></i>&nbsp;Zurück zur Übersicht</a>
        <form action="core/create_wahl.php" method="post">
            <div class="form-group">
                <label for="name">Name der Wahl:</label>
                <input id="name" type="text" name="name" class="form-control" required autofocus>
            </div>
            <div class="form-group">
                <label for="beschreibung">Beschreibung der Wahl:</label>
                <input id="beschreibung" type="text" name="beschreibung" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="wahl_nr">Anzahl der Wahlmöglichkeiten der Schüler:</label>
                <input id="wahl_nr" type="number" name="wahl_nr" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="auswertung">Anzahl der Kurse für die Schüler:</label>
                <input id="auswertung" type="number" name="auswertung" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="date_start">Beginn der Wahl:</label>
                <input id="date_start" type="date" name="date_start" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="date_end">Ende der Wahl:</label>
                <input id="date_end" type="date" name="date_end" class="form-control" required>
            </div>
            <input type="submit" value="Erstellen" name="submit" class="btn btn-primary">
        </form>
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
