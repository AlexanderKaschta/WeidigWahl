<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "core/Database.php";
include_once "core/config.php";

session_start();

$pageTitle = "Wahl verwalten";

if (isset($_SESSION['loggedin']) && isset($_SESSION['admin'])) {
    if ($_SESSION['loggedin'] == 1 && isset($_SESSION['benutzer']) && $_SESSION['admin'] == 1) {

        if (isset($_GET['id'])) {
            //Lade die Daten der aktuellen Wahl
            $db = new Database();
            $pdo = $db->connect();

            if (is_null($pdo)) {
                session_destroy();
                header("Location: index.php?errorCode=1 ");
                exit();
            }

            $query = $pdo->prepare("SELECT * FROM tbl_sportwahl WHERE id = :id;");
            $id = $_GET['id'];
            $query->bindParam(":id", $id);
            $id = $_GET['id'];

            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount == 0) {
                header("Location: main.php?errorCode=1 ");
                exit();
            } else {
                while ($r = $query->fetch()) {
                    $row = $r;
                }
            }
        } else {
            header("Location: main.php?errorCode=1 ");
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
        <?php
        if (isset($_GET['errorCode'])) {
            $errorMessage = htmlspecialchars($_GET['errorCode']);

            if ($errorMessage == 1) {
                $errorMessage = '<div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        Ein technischer Fehler ist passiert!
    </div>';
                echo $errorMessage;
            } else if ($errorMessage == 2) {
                $errorMessage = '<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
        Die Wahl wurde erfolgreich aktiviert.</div>';
                echo $errorMessage;
            } else if ($errorMessage == 3) {
                $errorMessage = '<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
        Die Wahl wurde erfolgreich deaktiviert.</div>';
                echo $errorMessage;
            } else if ($errorMessage == 4) {
                $errorMessage = '<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
        Es kann nicht eingeteilt werden, da es weniger Plätze gibt als das System verteilen soll.</div>';
                echo $errorMessage;
            } else if ($errorMessage == 5) {
                $errorMessage = '<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
        Es kann nicht eingeteilt werden, da es mehr Zeitleisten gibt als angegeben zun einteilen der Teilnehmer.</div>';
                echo $errorMessage;
            } else if ($errorMessage == 6) {
                $errorMessage = '<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
        Der Teilnehmer wurde erfolgreich entfernt.</div>';
                echo $errorMessage;
            } else if ($errorMessage == 7) {
                $errorMessage = '<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
        Die Wahl muss beendet sein, damit sie ausgewertet werden kann.</div>';
                echo $errorMessage;
            } else if ($errorMessage == 8) {
                $errorMessage = '<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
       Die Mindestteilnehmer zahl muss größer gleich 0 sein und die maximale Teilnehmerzahl darf nicht
       kleiner als die Mindestteilnehmerzahl sein.</div>';
                echo $errorMessage;
            } else if ($errorMessage == 9) {
                $errorMessage = '<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
       Der Kurs wurde erfolgreich gelöscht!</div>';
                echo $errorMessage;
            }

        }
        ?>
        <h1 class="title_style"><?php echo $row['name_wahl']; ?></h1>

        <a href="main.php" class="btn btn-primary" style="margin-bottom: 32px;"><i
                    class="fas fa-chevron-left fa-sm fa-fw"></i>&nbsp;Zurück zur Übersicht</a>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Eigenschaften der Wahl</h4>
                <p><b>Name:</b>&nbsp;<?php echo $row['name_wahl']; ?></p>
                <p><b>Beschreibung:</b>&nbsp;<?php echo $row['beschreibung']; ?></p>
                <p><b>Aktiviert:</b>&nbsp; <?php if ($row['ist_aktiv'] == 0) {
                        echo "Nein";
                    } else {
                        echo "Ja";
                    } ?></p>
                <p><b>Beginn der Wahl:</b>&nbsp;
                    <?php try {
                        $date = new DateTime($row['datum_beginn']);
                    } catch (Exception $e) {
                        session_destroy();
                        header("Location: index.php?errorCode=1 ");
                        exit();
                    }
                    echo $date->format("d.m.Y"); ?></p>
                <p><b>Ende der Wahl:</b>&nbsp; <?php try {
                        $date2 = new DateTime($row['datum_ende']);
                    } catch (Exception $e) {
                        session_destroy();
                        header("Location: index.php?errorCode=1 ");
                        exit();
                    }
                    echo $date2->format("d.m.Y"); ?></p>
            </div>
            <div class="card-footer">
                <?php
                if ($row['ist_aktiv'] == 1) {
                    echo '<a class="btn btn-light" href="core/wahlAktivieren.php?action=2&id=' . $id . '">Wahl deaktivieren!</a>';
                } else {
                    echo '<a class="btn btn-light" href="core/wahlAktivieren.php?action=1&id=' . $id . '">Wahl aktivieren!</a>';
                }
                ?>
                <a class='btn btn-danger' href='core/deleteWahl.php?wahl=<?php echo $id; ?>'><i class="fas fa-trash fa-sm fa-fw"></i>&nbsp;Wahl löschen</a>
            </div>
        </div>
        <br>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Zusammenfassung der Wahl</h4>
                <?php
                $file = "docs/" . $id . ".pdf";
                if (file_exists($file)) {
                    echo "<p><a class='btn btn-primary' href='" . $file . "' class='btn btn-primary'>Zusammenfassung ansehen!</a></p>";
                } else {
                    echo "<p>Es ist aktuell keine Zusammenfassung der Wahl hinterlegt.</p>";
                }
                ?>
            </div>
            <div class="card-footer">
                <?php
                $file = "docs/" . $id . ".pdf";
                if (file_exists($file)) {
                    echo "<a class='btn btn-light' href='core/deleteFile.php?id=" . $file . "'><i class=\"fas fa-trash fa-sm fa-fw\"></i>&nbsp;Zusammenfassung löschen</a>";
                } else {
                    echo "<a class='btn btn-light' href='uploadFile.php?action=2&extra=" . $id . "'><i class=\"fas fa-file-upload fa-sm fa-fw\"></i>&nbsp;Zusammenfassung hochladen</a>";
                }
                ?>
            </div>
        </div>
        <br>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Kurse</h4>
                <?php
                $query2 = $pdo->prepare("SELECT * FROM tbl_kurse WHERE sportwahl = :id ORDER BY alias;");
                $query2->bindParam(":id", $id);
                $query2->execute();

                $rowCount2 = $query2->rowCount();
                if ($rowCount2 == 0) {
                    echo "<p>Es sind keine Kurse da.</p>";
                } else {
                    echo "<p>Folgende Kurse gibt es für diese Wahl:</p>";
                    echo "<div class='table-responsive'><table style='width: 100%; margin-bottom: 0 !important;' class='table table-hover'><thead><tr><th>Kürzel</th><th>Kurs</th><th>Lehrer</th><th>Min</th><th>Max</th><th>Funktionen</th></tr></thead><tbody>";
                    while ($row2 = $query2->fetch()) {
                        echo "<tr><td>" . $row2['alias'] . "</td><td>" . $row2['name'] . "</td><td>" . $row2['lehrer'] . "</td><td>" . $row2['min'] . "</td><td>" . $row2['max'] . "</td><td><a href='editKurs.php?id=" . $row2['id'] . "&wahl=" . $id . "' data-toggle=\"tooltip\" data-placement=\"left\" title=\"Bearbeiten\"><i class=\"fas fa-edit fa-sm fa-fw\"></i></a>&nbsp;<a href='core/deleteKurs.php?wahl=" . $id . "&id=" . $row2['id'] . "' data-toggle=\"tooltip\" data-placement=\"left\" title=\"Löschen\"><i class=\"fas fa-trash fa-sm fa-fw\"></i></a></td></tr>";
                    }
                    echo "</tbody></table></div>";
                }
                ?>
            </div>
            <div class="card-footer">
                <a class="btn btn-light" href="new_kurs.php?id=<?php echo $id; ?>"><i class="fas fa-plus fa-sm fa-fw"></i>&nbsp;Kurs anlegen!</a>
            </div>
        </div>
        <br>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Teilnehmer der Wahl</h4>
                <?php
                //Anmerkung: Dies ist irgendein unwichtiger Code
                $teilnehmerQuery = $pdo->prepare("SELECT * FROM tbl_teilnehmer, tbl_users where tbl_teilnehmer.benutzer = tbl_users.id AND tbl_teilnehmer.wahl_id = :wahl;");
                $teilnehmerQuery->bindParam(":wahl", $_GET['id']);
                $teilnehmerQuery->execute();
                $anzahl_teilnehmer = $teilnehmerQuery->rowCount();
                if ($anzahl_teilnehmer == 0) {
                    echo '<p>Es sind der Wahl noch keine Teilnehmer zugeteilt.</p>';
                } else {
                    echo '<p>Folgende Teilnehmer hat die Wahl:</p>';
                    echo "<div class='table-responsive'><table style='width: 100%; margin-bottom: 0' class='table'><thead><tr><th>Vorname</th><th>Nachname</th><th>Jahrgang</th><th>Klasse</th><th>Funktionen</th></tr></thead><tbody>";
                    while ($teilnehmer = $teilnehmerQuery->fetch()) {
                        $link = "core/teilnehmer_entfernen.php?id=" . $teilnehmer['benutzer'] . "&wahl=" . $_GET['id'];
                        echo "<tr><td>" . $teilnehmer['vorname'] . "</td><td>" . $teilnehmer['nachname'] . "</td><td>" . $teilnehmer['jahrgang'] . "</td><td>" . $teilnehmer['klasse'] . "</td><td><a href='" . $link . "' data-toggle=\"tooltip\" data-placement=\"left\" title=\"Entfernen\"><i class=\"fas fa-trash\"></i></a></td></tr>";
                    }
                    echo "</tbody></table></div>";
                }
                ?>
            </div>
            <div class="card-footer">
                <a class="btn btn-light" href="nutzer_zuteilen.php?id=<?php echo $id; ?>"><i class="fas fa-plus fa-sm fa-fw"></i>&nbsp;Teilnehmer hinzufügen</a>
            </div>
        </div>
        <br>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Statistik der Wahl</h4>
                <p>Hier können vor dem Beginn der Auswertung Daten über das Wahlverhalten eingesehen werden.</p>
            </div>
            <div class="card-footer">
                <a class="btn btn-light" href="statistik.php?id=<?php echo $id; ?>"><i class="fas fa-chart-bar fa-sm fa-fw"></i>&nbsp;Statistik ansehen</a>
            </div>
        </div>
        <br>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Zugangsdaten der Wahl</h4>
                <p>Damit die Schüler gleich mit der Wahl beginnen können, können hier die Zugangsdaten ausgegeben werden.</p>
            </div>
            <div class="card-footer">
                <a class="btn btn-light" href="viewZugangsdaten.php?id=<?php echo $id; ?>"><i class="fas fa-plus fa-sm fa-fw"></i>&nbsp;Zugangsdaten ausgeben</a>
            </div>
        </div>
        <br>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Wahlauswertung</h4>
                <p>Hier kann die Wahl ausgewertet werden. Es wird abgeraten, dies zu machen, wenn die Wahl noch nicht beendet ist. </p>
            </div>
            <div class="card-footer">
                <?php
                $auswertungsQuery = $pdo->prepare("SELECT * FROM tbl_ergebnisse WHERE sportwahl=:wahl AND akzeptiert = 1;");
                $auswertungsQuery->bindParam(":wahl", $id);
                $auswertungsQuery->execute();
                $auswertungsRows = $auswertungsQuery->rowCount();
                if ($auswertungsRows > 0) {
                    echo '<a class="btn btn-light" href="viewEinteilung.php?id=' . $id . '"><i class="fas fa-plus fa-sm fa-fw"></i>&nbsp; Auswerung der Wahl ansehen</a>';
                } else {
                    echo '<a class="btn btn-light" href="core/nutzer_einteilen.php?id=' . $id . '"><i class="fas fa-plus fa-sm fa-fw"></i>&nbsp;Wahl auswerten</a>';
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
