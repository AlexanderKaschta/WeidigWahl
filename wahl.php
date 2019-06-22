<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

include_once "core/config.php";
require "core/Database.php";

session_start();

$pageTitle = "Wahl";

if (isset($_SESSION['loggedin']) && isset($_GET['id']) && isset($_GET['nr']) && is_int((int)$_GET['id']) && is_int((int)$_GET['nr'])){
    if ($_SESSION['loggedin'] == 1 && isset($_SESSION['benutzer'])){

        $db = new Database();
        $pdo = $db->connect();

        if (is_null($pdo)){
            session_destroy();
            header("Location: index.php?errorCode=1 ");
            exit();
        }

        //Kontrolliere ob der Nutzer an der Wahl teilnehmen darf
        $check = $pdo->prepare("SELECT * FROM tbl_teilnehmer WHERE wahl_id = :wahl AND benutzer = :id;");
        $check->bindParam(":wahl", $_GET['id']);
        $check->bindParam(":id", $_SESSION['id']);

        $check->execute();

        $check_row = $check->rowCount();

        if ($check_row != 1){
            header("Location: main.php?errorCode=1 ");
            exit();
        }

        $check2 = $pdo->prepare("SELECT * FROM tbl_sportwahl WHERE id = :id AND ist_aktiv=1;");
        $check2->bindParam(":id", $_GET['id']);
        $check2->execute();
        $check2_rows = $check2->rowCount();

        if ($check2_rows != 1){
            header("Location: main.php?errorCode=1 ");
            exit();
        }
        $check2_row =$check2->fetch();

        if ($check2_row['ist_aktiv'] == 0){
            header("Location: main.php?errorCode=1 ");
            exit();
        }

        //Besorge das aktuelle Datum
        try {
            $aktuell = new DateTime(date('Y-m-d'));
            $date2 = new DateTime($check2_row['datum_ende']);
        } catch (Exception $e) {
            session_destroy();
            header("Location: index.php?errorCode=1 ");
            exit();
        }

        //Wenn date2 kleiner als aktuell ist,
        if ($date2 < $aktuell){
            //dann ist die Wahl abgelaufen
            header("Location: main.php?errorCode=4 ");
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
    <?php
    if (isset($_GET['errorCode'])) {
        $errorMessage = htmlspecialchars($_GET['errorCode']);
        if ($errorMessage == 1) {
            $errorMessage = '<div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        Eingabe konnten nicht gespeichert werden.
    </div>';
            echo $errorMessage;
        } else if($errorMessage == 2){
            $errorMessage = '<div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        Eingabe wurde erfolgreich gespeichert.
    </div>';
            echo $errorMessage;
        }
    }
    ?>
    <h1 class="title_style">Wähle deine Kurse</h1>
    <h2><?php echo $_GET['nr'].". Stimme"; ?></h2>
    <form method="post" action="core/insert_wahl.php?<?php echo "id=".$_GET['id']."&nr=".$_GET['nr']; ?>" class="login-form">
        <div class="form-group">
            <label for="kurs">Kurs:</label>
            <select id="kurs" name="kurs" class="form-control" required>
                <option value="-1">Bitte wählen...</option>
                <?php
                $kurs_query = $pdo->prepare("SELECT * FROM tbl_kurse WHERE sportwahl=:id; ");
                $kurs_query->bindParam(":id", $_GET['id']);
                $kurs_query->execute();

                $kursanzahl = $kurs_query->rowCount();

                if ($kursanzahl == 0){
                    header("Location: main.php?errorCode=1 ");
                    exit();
                } else{
                    while ($kurs_row = $kurs_query->fetch()){
                        echo '<option value="'.$kurs_row["id"].'">'.$kurs_row['name'].'</option>';
                    }
                }
                ?>
            </select>
        </div>
        <?php
            $sportwahl_query = $pdo->prepare("SELECT * FROM tbl_sportwahl WHERE id = :id;");
            $sportwahl_query->bindParam(":id", $_GET['id']);

            $sportwahl_query->execute();

            $sportwahl_lines = $sportwahl_query->rowCount();

            if ($sportwahl_lines != 1){
                session_destroy();
                header("Location: index.php?errorCode=1 ");
                exit();
            } else{
                $sportwahl_row = $sportwahl_query->fetch();
                $angaben_anzahl = $sportwahl_row['anzahl_wahl'];

                if ((int)$_GET['nr'] == $angaben_anzahl){
                    echo '<input type="submit" value="Fertigstellen" name="submit" class="btn btn-primary">';
                } else if ((int)$_GET['nr'] < $angaben_anzahl){
                    echo '<input type="submit" value="Weiter" name="submit" class="btn btn-primary">';
                } else{
                    //Throw an error
                    session_destroy();
                    header("Location: index.php?errorCode=1 ");
                    exit();
                }
            }
        ?>
    </form>
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
