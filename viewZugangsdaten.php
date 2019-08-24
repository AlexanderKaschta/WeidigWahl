<?php

/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "core/Database.php";
include_once "core/config.php";

session_start();

$pageTitle = "Zugangsdaten auslesen";

if (isset($_SESSION['loggedin']) && isset($_SESSION['benutzer']) && isset($_GET['id'])) {
    if ($_SESSION['loggedin'] == 1 && $_SESSION['admin'] == 1) {

        $db = new Database();
        $pdo = $db->connect();

        if (is_null($pdo)) {
            session_destroy();
            header("Location: index.php?errorCode=1 ");
            exit();
        }

        $dataQuery = $pdo->prepare("SELECT * FROM tbl_users, tbl_teilnehmer WHERE tbl_users.id = tbl_teilnehmer.benutzer AND tbl_teilnehmer.wahl_id = :id ORDER BY tbl_users.klasse, tbl_users.nachname, tbl_users.vorname;");
        $dataQuery->bindParam(":id", $_GET['id']);
        $dataQuery->execute();

        $datensatze = $dataQuery->rowCount();


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
        <h1 class="title_style">Zugangsdaten der Teilnehmer</h1>
        <p>Hier ist Übersicht der Daten, welche die Schüler über diese Wahl benötigen.</p>
        <a class="btn btn-primary" href="config_wahl.php?id=<?php echo $_GET['id'];?>" style="margin-bottom: 16px;"><i
                    class="fas fa-chevron-left fa-sm fa-fw"></i>&nbsp;Zurück zur Übersicht</a>


        <div class="card">
            <div class="card-body p-0">
                <?php

                if ($datensatze == 0){
                    echo "<p>Es gibt keine Daten, die ausgegeben werden können.</p>";
                }else{

                    echo "<div class='table-responsive'><table class='table'><thead><tr><th>Klasse</th><th>Nachname</th><th>Vorname</th><th>Passwort</th></tr></thead><tbody>";
                    while ($data = $dataQuery->fetch()){
                        echo "<tr><td>".$data['klasse']."</td><td>".$data['nachname']."</td><td>".$data['vorname']."</td><td>".$data['passwort']."</td></tr>";
                    }
                    echo "</tbody></table></div>";
                }
                ?>
            </div>
            <div class="card-footer">
                <a class="btn btn-light" target="_blank" href="core/dataExport.php?action=1&id=<?php echo $_GET['id']; ?>"><i class="fas fa-file-download fa-sm fa-fw"></i>&nbsp;Daten als .csv-Datei herunterladen</a>
                <a class="btn btn-light" target="_blank" href="core/Zugangsdaten.php?id=<?php echo $_GET['id']; ?>"><i class="fas fa-file-download fa-sm fa-fw"></i>&nbsp;Daten als .pdf-Datei herunterladen</a>
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
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/popper.min.js"></script>
<script type="text/javascript">
    $('.alert').alert()
</script>
</body>
</html>
