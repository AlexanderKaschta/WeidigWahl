<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require_once "core/config.php";

$pageTitle = "Login";

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
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <title><?php echo PROJECT_NAME . " | " . $pageTitle; ?></title>
</head>
<body>
<header>
    <div class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container d-flex justify-content-between">
            <a href="index.php" class="navbar-brand d-flex align-items-center">
                <strong><?php echo PROJECT_NAME;?></strong>
            </a>
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
        }
    }
    ?>
    <h1 class="text-center" style="margin-top: 48px;">Login</h1>
    <form method="post" action="core/check_login.php" class="login-form">
        <div class="form-group">
            <label for="user">Benutzername:</label>
            <input id="user" type="text" name="user" class="form-control" required autofocus>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <input type="submit" value="Absenden" name="submit" class="btn btn-primary login-button">
    </form>
    <br>
    <div class="text-center">
        <a data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">Accountdaten vergessen</a>
    </div>
    <div class="collapse password login-button" id="collapseExample">
        <div class="card card-body">
            Solltest du dein Passwort vergessen, dann bitte wende dich an den Lehrer, der dir deine Zugangsdaten ausgehändigt hat. Alternativ kann dir auch <?php echo ADMINISTRATOR; ?> helfen.
        </div>
    </div>
</div>

<script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
<script type="text/javascript" src="js/popper.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript">
    //Aktiviere die Benachrichtigungen
    $('.alert').alert()
</script>
</body>
</html>
