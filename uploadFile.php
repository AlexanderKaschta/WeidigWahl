<?php

/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "core/Database.php";
include_once "core/config.php";

session_start();

$pageTitle = "Datei hochladen";

if (isset($_SESSION['loggedin']) && isset($_SESSION['admin']) && isset($_GET['action'])) {
    if ($_SESSION['loggedin'] == 1 && isset($_SESSION['benutzer']) && $_SESSION['admin'] == 1) {

        if (is_int((int)$_GET['action'])){

            if ($_GET['action'] == 1 || $_GET['action'] == 2){
                $value = $_GET['action'];
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
    <!-- Fehlermeldungen -->
    <?php
    if (isset($_GET['errorCode'])) {
        $errorMessage = htmlspecialchars($_GET['errorCode']);

        if ($errorMessage == 1) {
            $errorMessage = '<div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        Ungültiger Dateityp. Es werden nur .txt, .csv und .pdf angenommen.</div>';
            echo $errorMessage;
        } else if ($errorMessage == 2) {
            $errorMessage = '<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
        Die Datei ist zu groß!</div>';
            echo $errorMessage;
        } else if ($errorMessage == 3) {
            $errorMessage = '<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
        Beim Hochladen ist ein unbekannter Fehler aufgetreten.</div>';
            echo $errorMessage;
        } else if ($errorMessage == 3) {
            $errorMessage = '<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    Das System kann die hochgeladene Datei nicht verarbeiten und wurde vom Server wieder gelöscht.</div>';
            echo $errorMessage;
        }
    }
    ?>
    <!-- Ende der Fehlermeldungen -->
    <h1 class="title_style">Dateiupload</h1>
    <a style="margin-bottom: 16px;" href="main.php" class="btn btn-primary">Zurück zur Übersicht</a>
    <form action="core/uploadFile.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="datei">Bitte wählen die Datei zum Upload aus.</label>
            <input type="file" class="form-control-file" id="datei" name="datei">
        </div>
        <?php
         if (isset($value)){
             echo '<input type="hidden" id="action" name="action" value="'.$value.'">';
         }
        if (isset($_GET['extra'])){
            echo '<input type="hidden" id="extra" name="extra" value="'.$_GET['extra'].'">';
        }
        ?>
        <input type="submit" value="Hochladen" name="submit" class="btn btn-primary">
    </form>
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
