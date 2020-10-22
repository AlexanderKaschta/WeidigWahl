<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "core/Database.php";
include_once "core/config.php";

session_start();

$pageTitle = "Teilnehmer verwalten";

if (isset($_SESSION['loggedin']) && isset($_SESSION['benutzer']) && isset($_GET['action'])) {
    if ($_SESSION['loggedin'] == 1 && $_SESSION['admin'] == 1 && is_int((int)$_GET['action'])) {

        $db = new Database();
        $pdo = $db->connect();

        if (is_null($pdo)) {
            session_destroy();
            header("Location: index.php?errorCode=1 ");
            exit();
        }

        //Create user
        if ($_GET['action'] == 1) {
            $pageTitle = "Nutzer erstellen";
            $action = $_GET['action'];
        } //Else update user
        else if ($_GET['action'] == 2) {
            $pageTitle = "Nutzer bearbeiten";
            //Verbinde dich mit der Datenbank

            if (!isset($_GET['id'])) {
                session_destroy();
                header("Location: index.php?errorCode=1 ");
                exit();
            }

            $id = $_GET['id'];
            $action = $_GET['action'] . "&id=" . $id;

            $info_query = $pdo->prepare("SELECT * FROM tbl_users WHERE id = :id;");
            $info_query->bindParam(":id", $id);
            $info_query->execute();

            $info_row_count = $info_query->rowCount();

            if ($info_row_count != 1) {
                session_destroy();
                header("Location: index.php?errorCode=1 ");
                exit();
            } else {
                $is_data_there = true;
                $data_row = $info_query->fetch();
            }

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
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/all.min.css">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <meta name="theme-color" content="#212529">
    <title><?php echo PROJECT_NAME . " | " . $pageTitle; ?></title>
</head>
<body class="d-flex flex-column min-vh-100">
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
        <h1 style="margin-top: 36px;"><?php echo $pageTitle; ?></h1>
        <p>Hier können neue Nutzer erstellt werden.</p>
        <a class="btn btn-primary" style="margin-bottom: 16px;" href="manage_students.php"><i class="fas fa-chevron-left fa-sm fa-fw"></i>&nbsp;Zurück zur Übersicht</a>
        <form method="post" autocomplete="off" action="core/insertStudents.php?action=<?php echo $action; ?>">
            <div class="form-group">
                <label for="vorname">Vorname:</label>
                <input id="vorname" type="text" name="vorname" class="form-control" <?php if (isset($is_data_there)) {
                    echo 'value="' . $data_row["vorname"] . '"';
                } ?> required autofocus>
            </div>
            <div class="form-group">
                <label for="nachname">Nachname:</label>
                <input id="nachname" type="text" name="nachname" class="form-control" <?php if (isset($is_data_there)) {
                    echo 'value="' . $data_row["nachname"] . '"';
                } ?> required>
            </div>
            <div class="form-group">
                <label for="username">Benutzername:</label>
                <input id="username" type="text" name="username" class="form-control" <?php if (isset($is_data_there)) {
                    echo 'value="' . $data_row["benutzername"] . '"';
                } ?> required>
            </div>
            <div class="form-group">
                <label for="birth">Geburtsdatum:</label>
                <input id="birth" type="date" name="birth" class="form-control" <?php if (isset($is_data_there)) {
                    try {
                        $datum_birth = new DateTime($data_row["geburtsdatum"]);
                        echo 'value="' . $datum_birth->format("Y-m-d") . '"';
                    } catch (Exception $e) {
                        session_destroy();
                        header("Location: index.php?errorCode=1 ");
                        exit();
                    }
                } ?> required>
            </div>
            <div class="form-group">
                <label for="passwort">Passwort:</label>
                <input id="passwort" type="text" name="passwort" class="form-control" <?php if (isset($is_data_there)) {
                    echo 'value="' . $data_row["passwort"] . '"';
                } ?> required>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="ist_aktiv"
                       name="ist_aktiv" <?php if (isset($is_data_there) && $data_row["ist_aktiv"] == 1) {
                    echo "checked";
                } ?> >
                <label class="form-check-label" for="ist_aktiv">Ist aktiv?</label>
            </div>
            <div class="form-group">
                <label for="jahrgang">Jahrgang:</label>
                <input id="jahrgang" type="text" name="jahrgang" class="form-control" <?php if (isset($is_data_there)) {
                    echo 'value="' . $data_row["jahrgang"] . '"';
                } ?> required>
            </div>
            <div class="form-group">
                <label for="klasse">Klasse:</label>
                <input id="klasse" type="text" name="klasse" class="form-control" <?php if (isset($is_data_there)) {
                    echo 'value="' . $data_row["klasse"] . '"';
                } ?> required>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="ist_admin"
                       name="ist_admin" <?php if (isset($is_data_there) && $data_row["ist_admin"] == 1) {
                    echo "checked";
                } ?>>
                <label class="form-check-label" for="ist_admin">Ist Admin?</label>
            </div>
            <input style="margin-top: 16px;" type="submit" value="Absenden" name="submit" class="btn btn-primary">
        </form>
    </main>
</div>

<div class="mt-auto">
    <?php include "core/include/footer.php"; ?>
</div>

<script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
<script type="text/javascript" src="js/popper.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
</body>
</html>
