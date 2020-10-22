<?php

/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "core/Database.php";
include_once "core/config.php";

session_start();

$pageTitle = "Kurs erstellen";

if (isset($_SESSION['loggedin']) && isset($_SESSION['admin'])) {
    if ($_SESSION['loggedin'] == 1 && isset($_SESSION['benutzer']) && $_SESSION['admin'] == 1) {

        if (isset($_GET['id'])) {
            //Lade die Daten der aktuellen Wahl
            $db = new Database();
            $pdo = $db->connect();

        } else {
            header("Location: main.php?errorCode=3 ");
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
<body class="d-flex flex-column min-vh-100">
<header>
    <div class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container d-flex justify-content-between">
            <a href="main.php" class="navbar-brand d-flex align-items-center">
                <!-- Optional svg logo-->
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
            $errorMessage = '<div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        Der Kurs muss mindestens einen Teilnehmer haben.</div>';
            echo $errorMessage;
        } else if ($errorMessage == 2) {
            $errorMessage = '<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
        Die Maximalanzahl müss größer oder mindestens gleich der Minimalanzahl der Teilnehmer sein.</div>';
            echo $errorMessage;
        } else if ($errorMessage == 3) {
            $errorMessage = '<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
        Der Startzeitpunkt muss vor dem Endzeitpunkt liegen.</div>';
            echo $errorMessage;
        }
    }
    ?>
    <main role="main">
        <h1 class="title_style">Neuer Kurs</h1>
        <a class="btn btn-primary" href="config_wahl.php?id=<?php echo $_GET['id'];?>"><i
                    class="fas fa-chevron-left fa-sm fa-fw"></i>&nbsp;Zurück zur Kursübersicht</a>
        <form action="core/create_kurs.php<?php echo "?id=" . $_GET['id']; ?>" method="post">
            <div class="form-group">
                <label for="name">Name des Kurses:</label>
                <input id="name" type="text" name="name" class="form-control" required autofocus>
            </div>
            <div class="form-group">
                <label for="beschreibung">Beschreibung des Kurses:</label>
                <input id="beschreibung" type="text" name="beschreibung" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="lehrer">Lehrkraft:</label>
                <input id="lehrer" type="text" name="lehrer" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="min">Minimale Teilnehmerzahl:</label>
                <input id="min" type="number" name="min" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="max">Maximale Teilnehmerzahl:</label>
                <input id="max" type="number" name="max" class="form-control" required>
            </div>
            <p>Alle Kurse, die auf einer Zeitleiste liegen, müssen den gleichen Start- und Endzeitpunkt haben. Die Kurse
                müssen nicht zwingend auch in diesem Zeitraum stattfinden. Diese Daten werden nur Intern zur Kursbelegung
                benutzt und werden nicht angezeigt, sodass auch zu keinerlei Verwirrung kommen kann. Bei der klassischen
                Sportwahl müssten daher alle Kurse die gleichen Zeitangaben haben.
            </p>
            <div class="form-group">
                <label for="date_start">Beginn des Kurses:</label>
                <input id="date_start" type="datetime-local" name="date_start" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="date_end">Ende des Kurses:</label>
                <input id="date_end" type="datetime-local" name="date_end" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="alias">Alias:</label>
                <input id="alias" type="text" name="alias" class="form-control" required>
            </div>
            <input type="submit" value="Erstellen" name="submit" class="btn btn-primary">
        </form>
    </main>
</div>

<div class="mt-auto">
    <?php include "core/include/footer.php"; ?>
</div>

<script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
<script type="text/javascript" src="js/popper.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript">
    $('.alert').alert()
</script>
</body>
</html>
