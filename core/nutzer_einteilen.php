<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require_once "Database.php";
include_once "config.php";

session_start();

if (isset($_SESSION['loggedin']) && isset($_SESSION['admin']) && isset($_GET['id'])) {
    if ($_SESSION['loggedin'] == 1 && isset($_SESSION['benutzer']) && $_SESSION['admin'] == 1) {

        $db = new Database();
        $pdo = $db->connect();

        if (is_null($pdo)) {
            session_destroy();
            header("Location: ../index.php?errorCode=1 ");
            exit();
        }

        //Check if there exists a wahl
        //$checkQuery = $pdo->prepare("SELECT * FROM tbl_sportwahl WHERE id = :id AND datum_ende < NOW();");
        $checkQuery = $pdo->prepare("SELECT * FROM tbl_sportwahl WHERE id = :id;");
        $checkQuery->bindParam(":id", $_GET['id']);
        $checkQuery->execute();

        $checkQuery_rowCount = $checkQuery->rowCount();

        if ($checkQuery_rowCount != 1) {
            header("Location: ../config_wahl.php?errorCode=7 ");
            exit();
        }

        $checkQueryRow = $checkQuery->fetch();

        $anzahl_Benutzereingaben = $checkQueryRow["anzahl_wahl"];
        $anzahl_Auswertung = $checkQueryRow["anzahl_auswertung"];

        $teilnehmerQuery = $pdo->prepare("SELECT * FROM tbl_teilnehmer WHERE wahl_id= :id;");
        $teilnehmerQuery->bindParam(":id", $_GET['id']);
        $teilnehmerQuery->execute();

        $anzahl_teilnehmer = $teilnehmerQuery->rowCount();

        $kursCheckQuery = $pdo->prepare("SELECT SUM(tbl_kurse.max) AS summe FROM tbl_kurse WHERE sportwahl= :id;");
        $kursCheckQuery->bindParam(":id", $_GET['id']);
        $kursCheckQuery->execute();

        $kursCheckRow = $kursCheckQuery->fetch();

        $verfugbarePlatze = $kursCheckRow['summe'];
        $benotigtePlatze = $anzahl_teilnehmer * $anzahl_Auswertung;

        if ($benotigtePlatze > $verfugbarePlatze) {
            //Gib einen Fehler aus
            header("Location: ../config_wahl.php?errorCode=4&id=" . $_GET['id']);
            exit();
        }

        //Gruppiere die Kurse in Zeitleisten

        $kontrolliere_die_Zeitgruppen = $pdo->prepare("SELECT von, bis FROM tbl_kurse WHERE sportwahl = :id GROUP BY von, bis;");
        $kontrolliere_die_Zeitgruppen->bindParam(":id", $_GET['id']);
        $kontrolliere_die_Zeitgruppen->execute();

        $zeitgruppen = $kontrolliere_die_Zeitgruppen->rowCount();

        if ($zeitgruppen != $anzahl_Auswertung) {
            //Es liegt ein Fehler vor
            header("Location: ../config_wahl.php?errorCode=5&id=" . $_GET['id']);
            exit();
        }
        //echo $zeitgruppen." Zeitgruppen";
        $zeitleistenIndex = 0;

        //Handhabe jede Zeitleiste
        while ($zeitleiste = $kontrolliere_die_Zeitgruppen->fetch(PDO::FETCH_ASSOC)) {
            //Gehe durch jeden Teilnehmer und stelle sicher, dass er einem Kurs zugeteilt werden kann
            //echo "Bearbeite Zeitleiste";
            $zeitleistenIndex += 1;

            //Erstelle einen Kurs für die überflüssigen Schüler
            $createKursQuery = $pdo->prepare("INSERT INTO tbl_kurse (name, beschreibung, lehrer, min, max, von, bis, sportwahl, alias) VALUES (:name, :beschreibung, 'Unbekannt', 0, :max, :von, :bis, :id, :alias);");
            $createKurs_name = "Übrige Schüler #" . $zeitleistenIndex;
            $createKurs_beschreibung = "Hier sind alle Schüler, die nicht eingeteilt werden konnten.";
            //max entspricht der Teilnehmerzahl
            //von aus der zeitleiste auslesen
            //bis aus der zeitleiste auslesen
            //id wie immer
            $createKurs_alias = "-" . $zeitleistenIndex;
            $createKursQuery->bindParam(":name", $createKurs_name);
            $createKursQuery->bindParam(":beschreibung", $createKurs_beschreibung);
            $createKursQuery->bindParam(":max", $anzahl_teilnehmer);
            $createKursQuery->bindParam(":von", $zeitleiste['von']);
            $createKursQuery->bindParam(":bis", $zeitleiste['bis']);
            $createKursQuery->bindParam(":id", $_GET['id']);
            $createKursQuery->bindParam(":alias", $createKurs_alias);
            $createKursQuery->execute();

            $resteKurs_Index = $pdo->lastInsertId();

            $teilnehmerQuery1 = $pdo->prepare("SELECT * FROM tbl_teilnehmer WHERE wahl_id= :id;");
            $teilnehmerQuery1->bindParam(":id", $_GET['id']);
            $teilnehmerQuery1->execute();

            while ($teilnehmer = $teilnehmerQuery1->fetch(PDO::FETCH_ASSOC)) {
                //echo "Analysiere den Nutzer " . $teilnehmer['benutzer'] . "\n";
                $kurs_gefunden = false;

                //Gehe durch alle abgegebene Stimmen
                for ($i = 1; $i <= $anzahl_Benutzereingaben; $i++) {

                    $checkKursQuery = $pdo->prepare("SELECT * FROM tbl_ergebnisse, tbl_kurse WHERE tbl_ergebnisse.kurs = tbl_kurse.id AND tbl_ergebnisse.stimmnummer = :nr AND
                                              tbl_kurse.sportwahl = :wahl AND tbl_kurse.von = :von AND tbl_kurse.bis = :bis AND tbl_ergebnisse.benutzer = :benutzer;");
                    $checkKursQuery->bindParam(":nr", $i);
                    $checkKursQuery->bindParam(":wahl", $_GET['id']);
                    $checkKursQuery->bindParam(":von", $zeitleiste['von']);
                    $checkKursQuery->bindParam(":bis", $zeitleiste['bis']);
                    $checkKursQuery->bindParam(":benutzer", $teilnehmer['benutzer']);
                    $checkKursQuery->execute();

                    $checkKursRows = $checkKursQuery->rowCount();

                    if ($checkKursRows == 1) {
                        //Akzeptiere die Wahl
                        $updateKursQuery = $pdo->prepare("UPDATE tbl_ergebnisse SET akzeptiert = 1 WHERE benutzer= :benutzer AND sportwahl=:wahl AND stimmnummer = :nr;");
                        $updateKursQuery->bindParam(":wahl", $_GET['id']);
                        $updateKursQuery->bindParam(":benutzer", $teilnehmer['benutzer']);
                        $updateKursQuery->bindParam(":nr", $i);
                        $updateKursQuery->execute();

                        //echo "\nKurs gefunden";

                        $kurs_gefunden = true;
                        break;
                    }
                }

                //Wenn kein Kurs gefunden wurde, dann füge ihn dem Reste-Kurs hinzu
                if ($kurs_gefunden == false) {

                    $insertIntoReste = $pdo->prepare("INSERT INTO tbl_ergebnisse (sportwahl, stimmnummer, kurs, benutzer, akzeptiert) VALUES (:wahl, :nr, :kurs, :benutzer, 1);");
                    $insertIntoReste->bindParam(":wahl", $_GET['id']);
                    $wert = $anzahl_Benutzereingaben + 1;
                    $insertIntoReste->bindParam(":nr", $wert);
                    $insertIntoReste->bindParam(":kurs", $resteKurs_Index);
                    $insertIntoReste->bindParam(":benutzer", $teilnehmer['benutzer']);
                    $insertIntoReste->execute();
                }
            }
            //Kontrolliere, ob man innerhalb der Zeitleiste noch etwas optimieren kann

            //Kontrolliere, ob es überfüllte Kurse gibt

            $kursControllQuery = $pdo->prepare("SELECT * FROM tbl_kurse WHERE sportwahl= :wahl AND von = :von AND bis = :bis;");
            $kursControllQuery->bindParam(":wahl", $_GET['id']);
            $kursControllQuery->bindParam(":von", $zeitleiste['von']);
            $kursControllQuery->bindParam(":bis", $zeitleiste['bis']);
            $kursControllQuery->execute();

            //Probiere die Ergebnisse zu optimieren

            //Durchgehe alle Kurse aus der Abfrage
            while ($kurs = $kursControllQuery->fetch(PDO::FETCH_ASSOC)) {

                if (istUeberfullt($pdo, $kurs['id'], false) == true) {
                    //Wenn er überfüllt ist, dann suche nach Teilnehmer, die noch andere Kurse besuchen möchten

                    for ($i = 1; $i < $anzahl_Benutzereingaben; $i++) {

                        $nutzerAbfrage = $pdo->prepare("SELECT * FROM tbl_ergebnisse WHERE sportwahl=:id AND kurs = :kurs AND akzeptiert = 1;");
                        $nutzerAbfrage->bindParam(":id", $_GET['id']);
                        $nutzerAbfrage->bindParam(":kurs", $kurs['id']);
                        $nutzerAbfrage->execute();

                        while ($nutzer = $nutzerAbfrage->fetch()) {

                            // Wenn der Kurs überfüllt ist
                            if (istUeberfullt($pdo, $kurs['id'], false)) {
                                //Schaue, ob die nächste Stimme ein Kurs ist, der noch nicht überfüllt ist.

                                $nextKurs = $pdo->prepare("SELECT * FROM tbl_ergebnisse, tbl_kurse WHERE tbl_ergebnisse.kurs = tbl_kurse.id AND tbl_ergebnisse.sportwahl = :id AND tbl_ergebnisse.stimmnummer = :nr AND tbl_ergebnisse.benutzer = :nutzer AND tbl_kurse.von = :von AND tbl_kurse.bis = :bis;");
                                $nextKurs->bindParam(":id", $_GET['id']);
                                $zahl = $i + 1;
                                $nextKurs->bindParam(":nr", $zahl);
                                $nextKurs->bindParam(":nutzer", $nutzer['benutzer']);
                                $nextKurs->bindParam(":von", $zeitleiste['von']);
                                $nextKurs->bindParam(":bis", $zeitleiste['bis']);
                                $nextKurs->execute();

                                $nextKursCount = $nextKurs->rowCount();

                                if ($nextKursCount == 1) {
                                    //Spalte mit den entsprechenden Infos zum alternativen Kurs
                                    $nextKursRow = $nextKurs->fetch();

                                    if (istUeberfullt($pdo, (int)$nextKursRow['kurs'], true) == false) {
                                        //Verschiebe den Schüler in den passenden Kurs

                                        kursAndernTeilnehmer($pdo, (int)$_GET['id'], (int)$nutzer['benutzer'], (int)$kurs['id'], 0);
                                        kursAndernTeilnehmer($pdo, (int)$_GET['id'], (int)$nutzer['benutzer'], (int)$nextKursRow['kurs'], 1);
                                    }
                                    $nextKurs->closeCursor();
                                    $nextKursRow = null;
                                }
                                $nextKurs = null;
                            }
                        }
                        $nutzerAbfrage->closeCursor();
                        $nutzerAbfrage = null;
                    }

                }
            }
            if ($zeitleistenIndex == $zeitgruppen) {
                //Fertig mit der Einteilung
                header("Location: ../viewEinteilung.php?id=" . $_GET['id']);
                exit();
            }
        }

        //Fertig mit der Einteilung
        header("Location: ../viewEinteilung.php?id=" . $_GET['id']);
        exit();

    } else {
        session_destroy();
        header("Location: ../index.php?errorCode=1 ");
        exit();
    }
} else {
    session_destroy();
    header("Location: ../index.php?errorCode=1 ");
    exit();
}


function istUeberfullt(PDO $pdo, int $kurs, bool $wantToAdd)
{

    $kursAbfrage = $pdo->prepare("SELECT * FROM tbl_kurse WHERE id = :kurs;");
    $kursAbfrage->bindParam(":kurs", $kurs);
    $kursAbfrage->execute();
    $kursRow = $kursAbfrage->fetch();

    $kursTeilnehmerAnzahlQuery = $pdo->prepare("SELECT COUNT(tbl_ergebnisse.id) As anzahl FROM tbl_ergebnisse WHERE kurs= :kurs AND akzeptiert = 1;");
    $kursTeilnehmerAnzahlQuery->bindParam(":kurs", $kursRow['id']);
    $kursTeilnehmerAnzahlQuery->execute();

    $kursTeilnehmerRow = $kursTeilnehmerAnzahlQuery->fetch();
    $teilnehmerAnzahl = $kursTeilnehmerRow['anzahl'];

    $max_platz = (int)$kursRow['max'];

    $kursAbfrage->closeCursor();
    $kursAbfrage = null;

    $kursTeilnehmerAnzahlQuery->closeCursor();
    $kursTeilnehmerAnzahlQuery = null;

    if ($wantToAdd == true) {
        if ($max_platz <= $teilnehmerAnzahl) {
            return true;
        } else {
            return false;
        }
    } else {
        if ($max_platz < $teilnehmerAnzahl) {
            return true;
        } else {
            return false;
        }
    }

}

function kursAndernTeilnehmer(PDO $pdo, int $wahl, int $benutzer, int $kurs, int $status)
{

    $updateQuery = $pdo->prepare("UPDATE tbl_ergebnisse SET akzeptiert = :eingabe WHERE sportwahl=:wahl AND benutzer=:benutzer AND kurs=:kurs;");
    $updateQuery->bindParam(":wahl", $wahl);
    $updateQuery->bindParam(":eingabe", $status);
    $updateQuery->bindParam(":kurs", $kurs);
    $updateQuery->bindParam(":benutzer", $benutzer);
    $updateQuery->execute();

}
