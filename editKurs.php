<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "core/Database.php";
include_once "core/config.php";

session_start();

$pageTitle = "Kurs bearbeiten";

if (isset($_SESSION['loggedin']) && isset($_SESSION['benutzer']) && isset($_GET['id']) && isset($_GET['wahl'])) {
    if ($_SESSION['loggedin'] == 1 && $_SESSION['admin'] == 1) {

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
    <main role="main">
        <h1 class="title_style">Kurs bearbeiten</h1>
        <a class="btn btn-primary" href="config_wahl.php?id=<?php echo $_GET['wahl']; ?>">Zurück zur Übersicht</a>
        <form method="post" action="core/changeKursSize.php?wahl=<?php echo $_GET['wahl']; ?>&id=<?php echo $_GET['id'];?>">
            <div class="form-group">
                <label for="min">Minimale Größe des Kurses:</label>
                <input id="min" type="number" name="min" class="form-control" required autofocus>
            </div>
            <div class="form-group">
                <label for="max">Maximale Größe des Kurses:</label>
                <input id="max" type="number" name="max" class="form-control" required>
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
<script type="text/javascript">
    $('.alert').alert()
</script>
</body>
</html>
